<?php
/**
 * Discipleship Module - Admin
 * Programs, modules, resources, questions. Process POST (and redirects) before any output.
 */
require_once __DIR__ . '/config/config.php';
requireLogin();


$pageTitle = 'Discipleship Programs';
$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$program_id = isset($_GET['program_id']) ? (int) $_GET['program_id'] : null;
$module_id = isset($_GET['module_id']) ? (int) $_GET['module_id'] : null;

// Discipleship media upload: subfolder under main uploads, path stored in DB
$discipleship_upload_subdir = 'discipleship';
$discipleship_upload_dir = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $discipleship_upload_subdir . DIRECTORY_SEPARATOR;
$discipleship_upload_relative = (defined('UPLOAD_PATH_RELATIVE') ? UPLOAD_PATH_RELATIVE : 'assets/img/uploads/') . $discipleship_upload_subdir . '/';

// ---------- POST: all form submissions and redirects (no output before this block ends) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // ---- Program: delete
        if (isset($_POST['delete_program']) && !empty($_POST['id'])) {
            $stmt = $db->prepare("DELETE FROM discipleship_programs WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            if ($stmt->rowCount() > 0) {
                redirect('discipleship.php', 'Program deleted successfully.');
            }
            redirect('discipleship.php', 'Program not found or already deleted.', 'warning');
        }

        // ---- Program: save (add/edit)
        if (isset($_POST['form_program'])) {
            if (empty($_POST['program_name']) || empty($_POST['description'])) {
                redirect('discipleship.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Program Name and Description are required.', 'danger');
            }
            $features = implode("\n", array_filter(array_map('trim', explode("\n", $_POST['features'] ?? ''))));
            $data = [
                'program_name' => sanitize($_POST['program_name'] ?? ''),
                'description' => sanitize($_POST['description'] ?? ''),
                'features' => $features,
                'image_url' => sanitize($_POST['image_url'] ?? ''),
                'duration' => sanitize($_POST['duration'] ?? ''),
                'requirements' => sanitize($_POST['requirements'] ?? ''),
                'status' => in_array($_POST['status'] ?? 'active', ['active', 'inactive', 'upcoming']) ? $_POST['status'] : 'active',
                'display_order' => (int) ($_POST['display_order'] ?? 0)
            ];
            $programId = $id ?: (isset($_POST['id']) ? (int) $_POST['id'] : null);
            if ($programId) {
                $stmt = $db->prepare("UPDATE discipleship_programs SET program_name=?, description=?, features=?, image_url=?, duration=?, requirements=?, status=?, display_order=? WHERE id=?");
                $stmt->execute([$data['program_name'], $data['description'], $data['features'], $data['image_url'], $data['duration'], $data['requirements'], $data['status'], $data['display_order'], $programId]);
                redirect('discipleship.php', $stmt->rowCount() > 0 ? 'Program updated successfully.' : 'No changes made.', $stmt->rowCount() > 0 ? 'success' : 'info');
            }
            $stmt = $db->prepare("INSERT INTO discipleship_programs (program_name, description, features, image_url, duration, requirements, status, display_order) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$data['program_name'], $data['description'], $data['features'], $data['image_url'], $data['duration'], $data['requirements'], $data['status'], $data['display_order']]);
            redirect('discipleship.php', 'Program added successfully.');
        }

        // ---- Module: delete
        if (isset($_POST['delete_module']) && !empty($_POST['module_id'])) {
            $mid = (int) $_POST['module_id'];
            $stmt = $db->prepare("SELECT program_id FROM discipleship_modules WHERE id = ?");
            $stmt->execute([$mid]);
            $row = $stmt->fetch();
            $stmt = $db->prepare("DELETE FROM discipleship_modules WHERE id = ?");
            $stmt->execute([$mid]);
            $pid = $row ? (int) $row['program_id'] : null;
            // Renumber remaining modules after deletion
            if ($pid && $stmt->rowCount() > 0) {
                $reorder = $db->prepare("SELECT id FROM discipleship_modules WHERE program_id = ? ORDER BY display_order ASC, id ASC");
                $reorder->execute([$pid]);
                $remaining = $reorder->fetchAll(PDO::FETCH_COLUMN);
                foreach ($remaining as $pos => $modId) {
                    $db->prepare("UPDATE discipleship_modules SET display_order = ? WHERE id = ?")->execute([$pos + 1, $modId]);
                }
            }
            redirect('discipleship.php?action=modules&id=' . ($pid ?: ''), $stmt->rowCount() > 0 ? 'Module deleted.' : 'Module not found.', $stmt->rowCount() > 0 ? 'success' : 'warning');
        }

        // ---- Module: save (add/edit)
        if (isset($_POST['form_module'])) {
            $pid = (int) ($_POST['program_id'] ?? 0);
            $mid = isset($_POST['module_id']) ? (int) $_POST['module_id'] : 0;
            if (empty($_POST['title'])) {
                redirect('discipleship.php?action=' . ($mid ? 'module_edit&id=' . $mid : 'module_add&program_id=' . $pid), 'Module title is required.', 'danger');
            }
            $title = sanitize($_POST['title'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $display_order = (int) ($_POST['display_order'] ?? 0);
            // Auto-calculate next display_order for new modules if left at 0
            if (!$mid && $display_order <= 0) {
                $maxStmt = $db->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 AS next_order FROM discipleship_modules WHERE program_id = ?");
                $maxStmt->execute([$pid]);
                $display_order = (int) $maxStmt->fetch()['next_order'];
            }
            $pass_mark_pct = max(0, min(100, (int) ($_POST['pass_mark_pct'] ?? 70)));
            if ($mid) {
                $stmt = $db->prepare("UPDATE discipleship_modules SET title=?, description=?, display_order=?, pass_mark_pct=? WHERE id=?");
                $stmt->execute([$title, $description, $display_order, $pass_mark_pct, $mid]);
                redirect('discipleship.php?action=modules&id=' . $pid, $stmt->rowCount() > 0 ? 'Module updated.' : 'No changes made.', 'success');
            }
            $stmt = $db->prepare("INSERT INTO discipleship_modules (program_id, title, description, display_order, pass_mark_pct) VALUES (?,?,?,?,?)");
            $stmt->execute([$pid, $title, $description, $display_order, $pass_mark_pct]);
            redirect('discipleship.php?action=modules&id=' . $pid, 'Module added.');
        }

        // ---- Resource: delete
        if (isset($_POST['delete_resource']) && !empty($_POST['resource_id'])) {
            $rid = (int) $_POST['resource_id'];
            $stmt = $db->prepare("SELECT module_id, file_path, resource_type FROM discipleship_module_resources WHERE id = ?");
            $stmt->execute([$rid]);
            $res = $stmt->fetch();
            $modId = $res ? (int) $res['module_id'] : null;
            if ($res && !empty($res['file_path']) && in_array($res['resource_type'], ['audio', 'video', 'pdf'])) {
                $base = dirname(dirname(__DIR__));
                $diskPath = $base . '/' . ltrim($res['file_path'], '/');
                if (file_exists($diskPath)) {
                    @unlink($diskPath);
                }
            }
            $stmt = $db->prepare("DELETE FROM discipleship_module_resources WHERE id = ?");
            $stmt->execute([$rid]);
            redirect('discipleship.php?action=resources&module_id=' . ($modId ?: ''), $stmt->rowCount() > 0 ? 'Resource deleted.' : 'Resource not found.', 'success');
        }

        // ---- Resource: save (add/edit)
        if (isset($_POST['form_resource'])) {
            $modId = (int) ($_POST['module_id'] ?? 0);
            $resId = isset($_POST['resource_id']) ? (int) $_POST['resource_id'] : 0;
            $resource_type = in_array($_POST['resource_type'] ?? '', ['text', 'audio', 'video', 'pdf']) ? $_POST['resource_type'] : 'text';
            $title = sanitize($_POST['title'] ?? '');
            $display_order = (int) ($_POST['display_order'] ?? 0);
            $content = '';
            $file_path = '';

            if ($resource_type === 'text') {
                $content = $_POST['content'] ?? ''; // allow HTML for formatted notes
            } else {
                // audio/video/pdf: handle file upload
                if (!is_dir($discipleship_upload_dir)) {
                    mkdir($discipleship_upload_dir, 0755, true);
                }
                $allowed_audio = ['mp3', 'wav', 'ogg', 'm4a'];
                $allowed_video = ['mp4', 'webm', 'ogg'];
                $allowed_pdf = ['pdf'];
                $allowed = $resource_type === 'audio' ? $allowed_audio : ($resource_type === 'video' ? $allowed_video : $allowed_pdf);
                $max_size = $resource_type === 'pdf' ? (50 * 1024 * 1024) : (100 * 1024 * 1024); // 50MB for PDF, 100MB for media

                if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed)) {
                        redirect('discipleship.php?action=' . ($resId ? 'resource_edit&id=' . $resId : 'resource_add&module_id=' . $modId), 'Invalid file type. Allowed: ' . implode(', ', $allowed), 'danger');
                    }
                    if ($_FILES['media_file']['size'] > $max_size) {
                        redirect('discipleship.php?action=' . ($resId ? 'resource_edit&id=' . $resId : 'resource_add&module_id=' . $modId), 'File too large (max 100MB).', 'danger');
                    }
                    $filename = $resource_type . '_' . time() . '_' . uniqid() . '.' . $ext;
                    $fullPath = $discipleship_upload_dir . $filename;
                    if (move_uploaded_file($_FILES['media_file']['tmp_name'], $fullPath)) {
                        $file_path = $discipleship_upload_relative . $filename;
                        // delete old file if editing
                        if ($resId) {
                            $old = $db->prepare("SELECT file_path FROM discipleship_module_resources WHERE id = ?");
                            $old->execute([$resId]);
                            $oldRow = $old->fetch();
                            if ($oldRow && !empty($oldRow['file_path'])) {
                                $base = dirname(dirname(__DIR__));
                                $oldDisk = $base . '/' . ltrim($oldRow['file_path'], '/');
                                if (file_exists($oldDisk)) {
                                    @unlink($oldDisk);
                                }
                            }
                        }
                    }
                } elseif ($resId) {
                    $old = $db->prepare("SELECT file_path, content FROM discipleship_module_resources WHERE id = ?");
                    $old->execute([$resId]);
                    $oldRow = $old->fetch();
                    if ($oldRow) {
                        $file_path = $oldRow['file_path'] ?? '';
                        $content = $oldRow['content'] ?? '';
                    }
                }
                if (!$resId && empty($file_path) && $resource_type !== 'text') {
                    redirect('discipleship.php?action=resource_add&module_id=' . $modId, 'Please upload a file (audio, video, or PDF).', 'danger');
                }
            }

            if ($resource_type === 'text') {
                $content = $_POST['content'] ?? '';
                if (!$resId && trim($title) === '' && trim($content) === '') {
                    redirect('discipleship.php?action=resources&module_id=' . $modId, 'Please enter a title or content for the text resource.', 'warning');
                }
            }

            if ($resId) {
                $stmt = $db->prepare("UPDATE discipleship_module_resources SET resource_type=?, title=?, content=?, file_path=?, display_order=? WHERE id=?");
                $stmt->execute([$resource_type, $title, $content, $file_path ?: null, $display_order, $resId]);
                redirect('discipleship.php?action=resources&module_id=' . $modId, 'Resource updated.');
            }
            $stmt = $db->prepare("INSERT INTO discipleship_module_resources (module_id, resource_type, title, content, file_path, display_order) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$modId, $resource_type, $title, $content, $file_path ?: null, $display_order]);
            if (!empty($_POST['save_and_add_another'])) {
                redirect('discipleship.php?action=resource_add&module_id=' . $modId, 'Resource added. Add another below.', 'success');
            }
            redirect('discipleship.php?action=resources&module_id=' . $modId, 'Resource added.');
        }

        // ---- Question: delete
        if (isset($_POST['delete_question']) && !empty($_POST['question_id'])) {
            $qid = (int) $_POST['question_id'];
            $stmt = $db->prepare("SELECT module_id FROM discipleship_questions WHERE id = ?");
            $stmt->execute([$qid]);
            $row = $stmt->fetch();
            $modId = $row ? (int) $row['module_id'] : null;
            $stmt = $db->prepare("DELETE FROM discipleship_questions WHERE id = ?");
            $stmt->execute([$qid]);
            redirect('discipleship.php?action=questions&module_id=' . ($modId ?: ''), 'Question deleted.', 'success');
        }

        // ---- Question: save (add/edit) with optional inline options
        if (isset($_POST['form_question'])) {
            $modId = (int) ($_POST['module_id'] ?? 0);
            $qid = isset($_POST['question_id']) ? (int) $_POST['question_id'] : 0;
            if (empty(trim($_POST['question_text'] ?? ''))) {
                redirect('discipleship.php?action=' . ($qid ? 'question_edit&id=' . $qid : 'question_add&module_id=' . $modId), 'Question text is required.', 'danger');
            }
            $question_text = $_POST['question_text'];
            $display_order = (int) ($_POST['display_order'] ?? 0);
            $options_raw = isset($_POST['options']) && is_array($_POST['options']) ? $_POST['options'] : [];
            $correct_index = isset($_POST['correct_option']) ? (int) $_POST['correct_option'] : -1;

            $questionId = $qid;
            if ($qid) {
                $stmt = $db->prepare("UPDATE discipleship_questions SET question_text=?, display_order=? WHERE id=?");
                $stmt->execute([$question_text, $display_order, $qid]);
            } else {
                $stmt = $db->prepare("INSERT INTO discipleship_questions (module_id, question_text, display_order) VALUES (?,?,?)");
                $stmt->execute([$modId, $question_text, $display_order]);
                $questionId = (int) $db->lastInsertId();
            }

            // Sync options if provided (non-empty option_text rows)
            $options = [];
            foreach ($options_raw as $i => $row) {
                $text = isset($row['option_text']) ? trim($row['option_text']) : '';
                if ($text === '') continue;
                $options[] = [
                    'option_text' => $text,
                    'feedback_text' => isset($row['feedback_text']) ? trim($row['feedback_text']) : '',
                    'is_correct' => ((int) $i === $correct_index) ? 1 : 0,
                    'display_order' => count($options)
                ];
            }
            if (!empty($options)) {
                $has_correct = false;
                foreach ($options as $o) { if ($o['is_correct']) { $has_correct = true; break; } }
                if (!$has_correct && count($options) > 0) {
                    $options[0]['is_correct'] = 1;
                }
                $db->prepare("DELETE FROM discipleship_question_options WHERE question_id = ?")->execute([$questionId]);
                $ins = $db->prepare("INSERT INTO discipleship_question_options (question_id, option_text, is_correct, feedback_text, display_order) VALUES (?,?,?,?,?)");
                foreach ($options as $o) {
                    $ins->execute([$questionId, $o['option_text'], $o['is_correct'], $o['feedback_text'], $o['display_order']]);
                }
            }

            redirect('discipleship.php?action=questions&module_id=' . $modId, $qid ? 'Question and options updated.' : 'Question and options added.');
        }

        // ---- Option: delete
        if (isset($_POST['delete_option']) && !empty($_POST['option_id'])) {
            $oid = (int) $_POST['option_id'];
            $stmt = $db->prepare("SELECT question_id FROM discipleship_question_options WHERE id = ?");
            $stmt->execute([$oid]);
            $row = $stmt->fetch();
            $qid = $row ? (int) $row['question_id'] : null;
            $stmt = $db->prepare("SELECT module_id FROM discipleship_questions WHERE id = ?");
            $stmt->execute([$qid]);
            $qrow = $stmt->fetch();
            $modId = $qrow ? (int) $qrow['module_id'] : null;
            $stmt = $db->prepare("DELETE FROM discipleship_question_options WHERE id = ?");
            $stmt->execute([$oid]);
            redirect('discipleship.php?action=questions&module_id=' . ($modId ?: ''), 'Option deleted.', 'success');
        }

        // ---- Option: save (add/edit)
        if (isset($_POST['form_option'])) {
            $qid = (int) ($_POST['question_id'] ?? 0);
            $oid = isset($_POST['option_id']) ? (int) $_POST['option_id'] : 0;
            if (empty(trim($_POST['option_text'] ?? ''))) {
                redirect('discipleship.php?action=' . ($oid ? 'option_edit&id=' . $oid : 'option_add&question_id=' . $qid), 'Option text is required.', 'danger');
            }
            $option_text = sanitize($_POST['option_text'] ?? '');
            $is_correct = isset($_POST['is_correct']) ? 1 : 0;
            $feedback_text = sanitize($_POST['feedback_text'] ?? '');
            $display_order = (int) ($_POST['display_order'] ?? 0);
            $stmt = $db->prepare("SELECT module_id FROM discipleship_questions WHERE id = ?");
            $stmt->execute([$qid]);
            $qrow = $stmt->fetch();
            $modId = $qrow ? (int) $qrow['module_id'] : null;
            if ($oid) {
                $stmt = $db->prepare("UPDATE discipleship_question_options SET option_text=?, is_correct=?, feedback_text=?, display_order=? WHERE id=?");
                $stmt->execute([$option_text, $is_correct, $feedback_text, $display_order, $oid]);
                redirect('discipleship.php?action=questions&module_id=' . ($modId ?: ''), 'Option updated.');
            }
            $stmt = $db->prepare("INSERT INTO discipleship_question_options (question_id, option_text, is_correct, feedback_text, display_order) VALUES (?,?,?,?,?)");
            $stmt->execute([$qid, $option_text, $is_correct, $feedback_text, $display_order]);
            redirect('discipleship.php?action=questions&module_id=' . ($modId ?: ''), 'Option added.');
        }
    } catch (PDOException $e) {
        redirect('discipleship.php', handleDBError($e, 'A database error occurred.'), 'danger');
    } catch (Exception $e) {
        error_log("discipleship.php: " . $e->getMessage());
        redirect('discipleship.php', 'An error occurred.', 'danger');
    }
}

// ---------- Output: include header and render view ----------
require_once __DIR__ . '/includes/header.php';

// Breadcrumb and wrapper for ELMS-style layout
$discBreadcrumb = [['Discipleship', 'discipleship.php']];
if ($action === 'list' || $action === '') {
    $discBreadcrumb[] = ['Programs', ''];
} elseif ($action === 'add' || $action === 'edit') {
    $discBreadcrumb[] = [$id ? 'Edit program' : 'Add program', ''];
} elseif ($action === 'modules' && $currentProgram) {
    $discBreadcrumb[] = [htmlspecialchars($currentProgram['program_name']), 'discipleship.php?action=modules&id=' . (int)$currentProgram['id']];
    $discBreadcrumb[] = ['Modules', ''];
} elseif (in_array($action, ['module_add', 'module_edit'])) {
    if ($program_id) {
        $p = $db->query("SELECT program_name FROM discipleship_programs WHERE id = " . (int)$program_id)->fetch();
        if ($p) {
            $discBreadcrumb[] = [htmlspecialchars($p['program_name']), 'discipleship.php?action=modules&id=' . $program_id];
            $discBreadcrumb[] = [$module ? 'Edit module' : 'Add module', ''];
        }
    }
} elseif (in_array($action, ['resources', 'resource_add', 'resource_edit']) && $currentModule) {
    $discBreadcrumb[] = [htmlspecialchars($currentProgram['program_name'] ?? ''), 'discipleship.php?action=modules&id=' . (int)$currentModule['program_id']];
    $discBreadcrumb[] = ['Modules', 'discipleship.php?action=modules&id=' . (int)$currentModule['program_id']];
    $discBreadcrumb[] = [htmlspecialchars($currentModule['title']), ''];
} elseif (in_array($action, ['questions', 'question_add', 'question_edit', 'option_add', 'option_edit']) && $currentModule) {
    $discBreadcrumb[] = [htmlspecialchars($currentProgram['program_name'] ?? ''), 'discipleship.php?action=modules&id=' . (int)$currentModule['program_id']];
    $discBreadcrumb[] = ['Modules', 'discipleship.php?action=modules&id=' . (int)$currentModule['program_id']];
    $discBreadcrumb[] = [htmlspecialchars($currentModule['title']), 'discipleship.php?action=questions&module_id=' . (int)$currentModule['id']];
    $discBreadcrumb[] = ['Questions', ''];
}
echo '<div class="admin-discipleship">';
if (count($discBreadcrumb) > 1) {
    echo '<nav class="disc-breadcrumb"><ol class="breadcrumb mb-0">';
    foreach ($discBreadcrumb as $i => $item) {
        $label = $item[0];
        $url = $item[1] ?? '';
        $active = ($i === count($discBreadcrumb) - 1) || $url === '';
        echo '<li class="breadcrumb-item' . ($active ? ' active' : '') . '">';
        if (!$active && $url) echo '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($label) . '</a>'; else echo htmlspecialchars($label);
        echo '</li>';
    }
    echo '</ol></nav>';
}

// Load program for modules/resources/questions context
$currentProgram = null;
$currentModule = null;
// When viewing modules for a program, URL uses id=program_id (e.g. ?action=modules&id=1)
if ($action === 'modules' && $id && !$program_id) {
    $program_id = $id;
}
if ($program_id) {
    $stmt = $db->prepare("SELECT * FROM discipleship_programs WHERE id = ?");
    $stmt->execute([$program_id]);
    $currentProgram = $stmt->fetch();
}
if ($module_id) {
    $stmt = $db->prepare("SELECT * FROM discipleship_modules WHERE id = ?");
    $stmt->execute([$module_id]);
    $currentModule = $stmt->fetch();
    if ($currentModule && !$currentProgram) {
        $program_id = (int) $currentModule['program_id'];
        $stmt = $db->prepare("SELECT * FROM discipleship_programs WHERE id = ?");
        $stmt->execute([$program_id]);
        $currentProgram = $stmt->fetch();
    }
}

if ($action === 'add' || $action === 'edit') {
    $program = null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM discipleship_programs WHERE id = ?");
        $stmt->execute([$id]);
        $program = $stmt->fetch();
        if (!$program) {
            echo '<div class="alert alert-danger">Program not found.</div>';
            require_once __DIR__ . '/includes/footer.php';
            exit;
        }
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Discipleship Program</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="form_program" value="1">
                <?php if (!empty($program['id'])): ?><input type="hidden" name="id" value="<?php echo (int) $program['id']; ?>"><?php endif; ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Program Name *</label>
                            <input type="text" class="form-control" name="program_name" value="<?php echo htmlspecialchars($program['program_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="5" required><?php echo htmlspecialchars($program['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Features (one per line)</label>
                            <textarea class="form-control" name="features" rows="6"><?php echo htmlspecialchars($program['features'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" value="<?php echo htmlspecialchars($program['image_url'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="duration" value="<?php echo htmlspecialchars($program['duration'] ?? ''); ?>" placeholder="e.g., 12 weeks">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Requirements</label>
                            <textarea class="form-control" name="requirements" rows="3"><?php echo htmlspecialchars($program['requirements'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="active" <?php echo ($program['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($program['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="upcoming" <?php echo ($program['status'] ?? '') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="<?php echo (int) ($program['display_order'] ?? 0); ?>" min="0">
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Program</button>
                    <a href="discipleship.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
} elseif ($action === 'modules' && $id && $currentProgram) {
    $pageTitle = 'Modules: ' . htmlspecialchars($currentProgram['program_name']);
    $stmt = $db->prepare("SELECT * FROM discipleship_modules WHERE program_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$id]);
    $modules = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="discipleship.php" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i>Programs</a>
            <h2 class="mb-0"><?php echo htmlspecialchars($currentProgram['program_name']); ?> – Modules</h2>
            <p class="text-muted small mb-0 mt-1">Add learning modules in order. For each module you can add resources (text, audio, video) and quiz questions at the end.</p>
        </div>
        <a href="discipleship.php?action=module_add&program_id=<?php echo (int) $id; ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Module</a>
    </div>
    <div class="card">
        <div class="card-body">
            <?php if (empty($modules)): ?>
                <div class="alert alert-info mb-0">
                    <strong>No modules yet.</strong> Students will see “No modules have been added” until you add at least one.
                    <a href="discipleship.php?action=module_add&program_id=<?php echo (int) $id; ?>" class="alert-link">Add the first module</a> (then add resources and questions for it).
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Title</th>
                                <th>Pass mark %</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $m): ?>
                                <tr>
                                <td><?php echo (int) $m['display_order']; ?></td>
                                    <td><?php echo htmlspecialchars($m['title']); ?></td>
                                    <td><?php echo (int) $m['pass_mark_pct']; ?>%</td>
                                    <td>
                                        <a href="discipleship.php?action=module_edit&id=<?php echo (int) $m['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit module"><i class="bi bi-pencil me-1"></i>Edit</a>
                                        <a href="discipleship.php?action=resources&module_id=<?php echo (int) $m['id']; ?>" class="btn btn-sm btn-outline-info" title="Text, audio, video"><i class="bi bi-file-earmark me-1"></i>Resources</a>
                                        <a href="discipleship.php?action=questions&module_id=<?php echo (int) $m['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Quiz at end of module"><i class="bi bi-question-circle me-1"></i>Questions</a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this module and all its resources and questions?');">
                                            <input type="hidden" name="module_id" value="<?php echo (int) $m['id']; ?>">
                                            <button type="submit" name="delete_module" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
} elseif (($action === 'module_add' && $program_id) || ($action === 'module_edit' && $id)) {
    $module = null;
    if ($action === 'module_edit') {
        $stmt = $db->prepare("SELECT * FROM discipleship_modules WHERE id = ?");
        $stmt->execute([$id]);
        $module = $stmt->fetch();
        if ($module) {
            $program_id = (int) $module['program_id'];
        }
    }
    if (!$program_id) {
        echo '<div class="alert alert-danger">Program not found.</div>';
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }
    $stmt = $db->prepare("SELECT * FROM discipleship_programs WHERE id = ?");
    $stmt->execute([$program_id]);
    $prog = $stmt->fetch();
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $module ? 'Edit' : 'Add'; ?> Module</h5>
        </div>
        <div class="card-body">
            <a href="discipleship.php?action=modules&id=<?php echo $program_id; ?>" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Back to Modules</a>
            <form method="POST">
                <input type="hidden" name="form_module" value="1">
                <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">
                <?php if (!empty($module['id'])): ?><input type="hidden" name="module_id" value="<?php echo (int) $module['id']; ?>"><?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($module['title'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($module['description'] ?? ''); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Display Order</label>
                        <?php
                        $defaultOrder = (int) ($module['display_order'] ?? 0);
                        if (empty($module['id'])) {
                            // Auto-calculate next order for new modules
                            $nextStmt = $db->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 AS next_order FROM discipleship_modules WHERE program_id = ?");
                            $nextStmt->execute([$program_id]);
                            $defaultOrder = (int) $nextStmt->fetch()['next_order'];
                        }
                        ?>
                        <input type="number" class="form-control" name="display_order" value="<?php echo $defaultOrder; ?>" min="1">
                        <small class="form-text text-muted">Position in the module list. New modules auto-increment.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pass Mark %</label>
                        <input type="number" class="form-control" name="pass_mark_pct" value="<?php echo (int) ($module['pass_mark_pct'] ?? 70); ?>" min="0" max="100">
                        <small class="text-muted">Learner must score at least this % to unlock the next module.</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Module</button>
                <a href="discipleship.php?action=modules&id=<?php echo $program_id; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
} elseif ($action === 'resources' && $module_id && $currentModule) {
    $pageTitle = 'Resources: ' . htmlspecialchars($currentModule['title']);
    $stmt = $db->prepare("SELECT * FROM discipleship_module_resources WHERE module_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$module_id]);
    $resources = $stmt->fetchAll();
    $maxOrderStmt = $db->prepare("SELECT COALESCE(MAX(display_order), -1) + 1 AS next_order FROM discipleship_module_resources WHERE module_id = ?");
    $maxOrderStmt->execute([$module_id]);
    $quickOrder = (int) $maxOrderStmt->fetch()['next_order'];
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="discipleship.php?action=modules&id=<?php echo (int) $currentModule['program_id']; ?>" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i>Modules</a>
            <h2 class="mb-0"><?php echo htmlspecialchars($currentModule['title']); ?></h2>
            <p class="text-muted small mb-0 mt-1">Learning resources (text, audio, video) shown to learners in order.</p>
        </div>
        <a href="discipleship.php?action=resource_add&module_id=<?php echo (int) $module_id; ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Resource</a>
    </div>

    <!-- Quick add text note -->
    <div class="card mb-4">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-card-text me-2"></i>Quick add text note</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Add a text-only resource without leaving this page. For audio or video, use <strong>Add Resource</strong> above.</p>
            <form method="POST">
                <input type="hidden" name="form_resource" value="1">
                <input type="hidden" name="resource_type" value="text">
                <input type="hidden" name="module_id" value="<?php echo (int) $module_id; ?>">
                <input type="hidden" name="display_order" value="<?php echo $quickOrder; ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Title</label>
                        <input type="text" class="form-control form-control-sm" name="title" placeholder="e.g. Key points">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-0">Content</label>
                        <textarea class="form-control form-control-sm" name="content" rows="2" placeholder="Notes or short description (plain text or HTML in full form)"></textarea>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">Add text note</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resources list -->
    <div class="card">
        <div class="card-header py-2">
            <h6 class="mb-0"><i class="bi bi-collection me-2"></i>Resources (<?php echo count($resources); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (empty($resources)): ?>
                <p class="text-muted mb-0">No resources yet. Use <strong>Quick add text note</strong> above or <strong>Add Resource</strong> for text, audio, video, or PDF.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:4rem;">Order</th>
                                <th style="width:6rem;">Type</th>
                                <th>Title / Preview</th>
                                <th style="width:10rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $r):
                                $type = $r['resource_type'];
                                $badgeClass = $type === 'text' ? 'bg-info' : ($type === 'audio' ? 'bg-warning text-dark' : 'bg-primary');
                                $preview = '';
                                if ($type === 'text' && !empty($r['content'])) {
                                    $preview = trim(strip_tags($r['content']));
                                    $preview = strlen($preview) > 80 ? substr($preview, 0, 80) . '…' : $preview;
                                } elseif (in_array($type, ['audio', 'video']) && !empty($r['file_path'])) {
                                    $preview = basename($r['file_path']);
                                }
                                $titleDisplay = $r['title'] ? htmlspecialchars($r['title']) : '<span class="text-muted fst-italic">(no title)</span>';
                            ?>
                                <tr>
                                    <td><?php echo (int) $r['display_order']; ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($type); ?></span></td>
                                    <td>
                                        <div class="fw-medium"><?php echo $titleDisplay; ?></div>
                                        <?php if ($preview): ?><div class="small text-muted mt-0"><?php echo htmlspecialchars($preview); ?></div><?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="discipleship.php?action=resource_edit&id=<?php echo (int) $r['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil me-1"></i>Edit</a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this resource?');">
                                            <input type="hidden" name="resource_id" value="<?php echo (int) $r['id']; ?>">
                                            <button type="submit" name="delete_resource" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0 mt-2">Tip: Use <strong>Add Resource</strong> then <strong>Save and add another</strong> to add several resources in a row. Display order is set automatically.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
} elseif (($action === 'resource_add' && $module_id) || ($action === 'resource_edit' && $id)) {
    $resource = null;
    if ($action === 'resource_edit') {
        $stmt = $db->prepare("SELECT * FROM discipleship_module_resources WHERE id = ?");
        $stmt->execute([$id]);
        $resource = $stmt->fetch();
        if ($resource) {
            $module_id = (int) $resource['module_id'];
        }
    }
    if (!$module_id) {
        echo '<div class="alert alert-danger">Module not found.</div>';
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }
    $stmt = $db->prepare("SELECT * FROM discipleship_modules WHERE id = ?");
    $stmt->execute([$module_id]);
    $mod = $stmt->fetch();
    $resType = $resource['resource_type'] ?? 'text';
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?php echo $resource ? 'Edit' : 'Add'; ?> Resource</h5>
            <a href="discipleship.php?action=resources&module_id=<?php echo $module_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Resources</a>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="resourceForm">
                <input type="hidden" name="form_resource" value="1">
                <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
                <?php if (!empty($resource['id'])): ?><input type="hidden" name="resource_id" value="<?php echo (int) $resource['id']; ?>"><?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Resource type *</label>
                            <select class="form-select" name="resource_type" id="resource_type" required>
                                <option value="text" <?php echo $resType === 'text' ? 'selected' : ''; ?>>Text – formatted notes (HTML allowed)</option>
                                <option value="audio" <?php echo $resType === 'audio' ? 'selected' : ''; ?>>Audio – voice note (MP3, WAV, OGG, M4A)</option>
                                <option value="video" <?php echo $resType === 'video' ? 'selected' : ''; ?>>Video – instructional clip (MP4, WebM, OGG)</option>
                                <option value="pdf" <?php echo $resType === 'pdf' ? 'selected' : ''; ?>>PDF – document (PDF)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($resource['title'] ?? ''); ?>" placeholder="e.g. Introduction, Key points, Summary">
                        </div>

                        <div class="mb-3" id="content_block">
                            <label class="form-label">Content</label>
                            <textarea class="form-control font-monospace" name="content" rows="10" placeholder="Write or paste your notes. You can use simple HTML (e.g. &lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;) for formatting."><?php echo htmlspecialchars($resource['content'] ?? ''); ?></textarea>
                            <div class="form-text">HTML is allowed for headings, lists, and emphasis.</div>
                        </div>
                        <div class="mb-3" id="file_block" style="display:none;">
                            <label class="form-label">Upload file <?php echo empty($resource['id']) ? '*' : ''; ?></label>
                            <input type="file" class="form-control" name="media_file" id="media_file" accept=".mp3,.wav,.ogg,.m4a,.mp4,.webm,.pdf">
                            <div class="form-text" id="file_block_hint">Max 100MB. Audio: MP3, WAV, OGG, M4A. Video: MP4, WebM, OGG. PDF: 50MB.</div>
                            <?php if (!empty($resource['file_path'])): ?>
                                <div class="alert alert-light border mt-2 mb-0 py-2 small">
                                    <strong>Current file:</strong> <?php echo htmlspecialchars(basename($resource['file_path'])); ?>
                                    <span class="text-muted">– Upload a new file to replace.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Display order</label>
                            <?php
                            $nextOrder = (int) ($resource['display_order'] ?? 0);
                            if (empty($resource['id'])) {
                                $maxStmt = $db->prepare("SELECT COALESCE(MAX(display_order), -1) + 1 AS next_order FROM discipleship_module_resources WHERE module_id = ?");
                                $maxStmt->execute([$module_id]);
                                $nextOrder = (int) $maxStmt->fetch()['next_order'];
                            }
                            ?>
                            <input type="number" class="form-control" name="display_order" value="<?php echo $nextOrder; ?>" min="0">
                            <div class="form-text">Order in which learners see this resource (0 = first).</div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Resource</button>
                            <?php if (empty($resource['id'])): ?>
                            <button type="submit" name="save_and_add_another" value="1" class="btn btn-outline-primary"><i class="bi bi-plus-circle me-2"></i>Save and add another</button>
                            <?php endif; ?>
                            <a href="discipleship.php?action=resources&module_id=<?php echo $module_id; ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
    (function() {
        var typeSelect = document.getElementById('resource_type');
        var contentBlock = document.getElementById('content_block');
        var fileBlock = document.getElementById('file_block');
        var mediaFile = document.getElementById('media_file');
        var fileHint = document.getElementById('file_block_hint');
        function toggle() {
            var type = typeSelect.value;
            var isText = type === 'text';
            contentBlock.style.display = isText ? 'block' : 'none';
            fileBlock.style.display = isText ? 'none' : 'block';
            if (mediaFile) {
                mediaFile.accept = type === 'audio' ? '.mp3,.wav,.ogg,.m4a' : (type === 'video' ? '.mp4,.webm,.ogg' : '.pdf');
            }
            if (fileHint) fileHint.textContent = type === 'pdf' ? 'PDF only. Max 50MB.' : (type === 'audio' ? 'Audio: MP3, WAV, OGG, M4A. Max 100MB.' : 'Video: MP4, WebM, OGG. Max 100MB.');
        }
        typeSelect.addEventListener('change', toggle);
        toggle();
    })();
    </script>
    <?php
} elseif ($action === 'questions' && $module_id && $currentModule) {
    $pageTitle = 'Questions: ' . htmlspecialchars($currentModule['title']);
    $stmt = $db->prepare("SELECT * FROM discipleship_questions WHERE module_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$module_id]);
    $questions = $stmt->fetchAll();
    foreach ($questions as &$q) {
        $opt = $db->prepare("SELECT * FROM discipleship_question_options WHERE question_id = ? ORDER BY display_order ASC, id ASC");
        $opt->execute([$q['id']]);
        $q['options'] = $opt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($q);
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="discipleship.php?action=modules&id=<?php echo (int) $currentModule['program_id']; ?>" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i>Modules</a>
            <h2 class="mb-0"><?php echo htmlspecialchars($currentModule['title']); ?> – Quiz Questions</h2>
            <p class="text-muted small mb-0 mt-1">Multiple-choice questions at the end of this module. Add a question and all answer options in one form; mark one option as correct and add optional feedback.</p>
        </div>
        <a href="discipleship.php?action=question_add&module_id=<?php echo (int) $module_id; ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Question</a>
    </div>
    <div class="card">
        <div class="card-body">
            <?php if (empty($questions)): ?>
                <div class="alert alert-info mb-0">
                    <strong>No questions yet.</strong> Add a question and its answer options in one go—no need to add options separately.
                    <a href="discipleship.php?action=question_add&module_id=<?php echo (int) $module_id; ?>" class="alert-link">Add the first question</a>.
                </div>
            <?php else: ?>
                <?php foreach ($questions as $q): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?php echo htmlspecialchars($q['question_text']); ?></strong>
                                <div class="mt-2 ms-3">
                                    <?php foreach ($q['options'] as $o): ?>
                                        <span class="badge <?php echo $o['is_correct'] ? 'bg-success' : 'bg-light text-dark'; ?> me-1"><?php echo htmlspecialchars($o['option_text']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div>
                                <a href="discipleship.php?action=question_edit&id=<?php echo (int) $q['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="discipleship.php?action=option_add&question_id=<?php echo (int) $q['id']; ?>" class="btn btn-sm btn-outline-info">Add option</a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this question?');">
                                    <input type="hidden" name="question_id" value="<?php echo (int) $q['id']; ?>">
                                    <button type="submit" name="delete_question" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
} elseif (($action === 'question_add' && $module_id) || ($action === 'question_edit' && $id)) {
    $question = null;
    if ($action === 'question_edit') {
        $stmt = $db->prepare("SELECT * FROM discipleship_questions WHERE id = ?");
        $stmt->execute([$id]);
        $question = $stmt->fetch();
        if ($question) {
            $module_id = (int) $question['module_id'];
        }
    }
    if (!$module_id) {
        echo '<div class="alert alert-danger">Module not found.</div>';
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }
    $stmt = $db->prepare("SELECT * FROM discipleship_modules WHERE id = ?");
    $stmt->execute([$module_id]);
    $mod = $stmt->fetch();
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $question ? 'Edit' : 'Add'; ?> Question</h5>
        </div>
        <div class="card-body">
            <a href="discipleship.php?action=questions&module_id=<?php echo $module_id; ?>" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Back to Questions</a>
            <form method="POST" id="questionOptionsForm">
                <input type="hidden" name="form_question" value="1">
                <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
                <?php if (!empty($question['id'])): ?><input type="hidden" name="question_id" value="<?php echo (int) $question['id']; ?>"><?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Question text *</label>
                    <textarea class="form-control" name="question_text" rows="3" required><?php echo htmlspecialchars($question['question_text'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" class="form-control" name="display_order" value="<?php echo (int) ($question['display_order'] ?? 0); ?>" min="0">
                </div>

                <hr class="my-4">
                <h6 class="mb-2">Answer options</h6>
                <p class="text-muted small mb-3">Add at least two options. Mark one as correct. Feedback is shown to the learner after they answer.</p>
                <div id="options_container">
                    <?php
                    $opts = [];
                    if (!empty($question['id'])) {
                        $optStmt = $db->prepare("SELECT * FROM discipleship_question_options WHERE question_id = ? ORDER BY display_order ASC, id ASC");
                        $optStmt->execute([$question['id']]);
                        $opts = $optStmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    if (empty($opts)) {
                        $opts = [['option_text' => '', 'feedback_text' => '', 'is_correct' => 0], ['option_text' => '', 'feedback_text' => '', 'is_correct' => 0]];
                    }
                    foreach ($opts as $idx => $o):
                    ?>
                    <div class="option-row card mb-2">
                        <div class="card-body py-2">
                            <div class="row align-items-start">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small mb-0">Option <?php echo $idx + 1; ?></label>
                                    <input type="text" class="form-control form-control-sm" name="options[<?php echo $idx; ?>][option_text]" value="<?php echo htmlspecialchars($o['option_text'] ?? ''); ?>" placeholder="Answer text">
                                </div>
                                <div class="col-md-5 mb-2">
                                    <label class="form-label small mb-0">Feedback (optional)</label>
                                    <input type="text" class="form-control form-control-sm" name="options[<?php echo $idx; ?>][feedback_text]" value="<?php echo htmlspecialchars($o['feedback_text'] ?? ''); ?>" placeholder="Shown after answer">
                                </div>
                                <div class="col-md-1 mb-2 pt-4">
                                    <label class="form-check small mb-0">
                                        <input type="radio" class="form-check-input" name="correct_option" value="<?php echo $idx; ?>" <?php echo !empty($o['is_correct']) ? 'checked' : ''; ?>>
                                        Correct
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="addOptionBtn"><i class="bi bi-plus me-1"></i>Add option</button>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Question &amp; Options</button>
                    <a href="discipleship.php?action=questions&module_id=<?php echo $module_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script>
    (function() {
        var container = document.getElementById('options_container');
        var addBtn = document.getElementById('addOptionBtn');
        if (!container || !addBtn) return;
        var nextIndex = container.querySelectorAll('.option-row').length;
        addBtn.addEventListener('click', function() {
            var row = document.createElement('div');
            row.className = 'option-row card mb-2';
            row.innerHTML = '<div class="card-body py-2"><div class="row align-items-start"><div class="col-md-6 mb-2"><label class="form-label small mb-0">Option ' + (nextIndex + 1) + '</label><input type="text" class="form-control form-control-sm" name="options[' + nextIndex + '][option_text]" placeholder="Answer text"></div><div class="col-md-5 mb-2"><label class="form-label small mb-0">Feedback (optional)</label><input type="text" class="form-control form-control-sm" name="options[' + nextIndex + '][feedback_text]" placeholder="Shown after answer"></div><div class="col-md-1 mb-2 pt-4"><label class="form-check small mb-0"><input type="radio" class="form-check-input" name="correct_option" value="' + nextIndex + '"> Correct</label></div></div></div>';
            container.appendChild(row);
            nextIndex++;
        });
    })();
    </script>
    <?php
} elseif (($action === 'option_add' && !empty($_GET['question_id'])) || ($action === 'option_edit' && $id)) {
    $question_id = isset($_GET['question_id']) ? (int) $_GET['question_id'] : null;
    $option = null;
    if ($action === 'option_edit') {
        $stmt = $db->prepare("SELECT * FROM discipleship_question_options WHERE id = ?");
        $stmt->execute([$id]);
        $option = $stmt->fetch();
        if ($option) {
            $question_id = (int) $option['question_id'];
        }
    }
    if (!$question_id) {
        echo '<div class="alert alert-danger">Question not found.</div>';
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }
    $stmt = $db->prepare("SELECT * FROM discipleship_questions WHERE id = ?");
    $stmt->execute([$question_id]);
    $qrow = $stmt->fetch();
    $module_id = $qrow ? (int) $qrow['module_id'] : null;
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $option ? 'Edit' : 'Add'; ?> Option</h5>
        </div>
        <div class="card-body">
            <a href="discipleship.php?action=question_edit&id=<?php echo $question_id; ?>" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Back to Question</a>
            <form method="POST">
                <input type="hidden" name="form_option" value="1">
                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                <?php if (!empty($option['id'])): ?><input type="hidden" name="option_id" value="<?php echo (int) $option['id']; ?>"><?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Option text *</label>
                    <input type="text" class="form-control" name="option_text" value="<?php echo htmlspecialchars($option['option_text'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_correct" value="1" <?php echo !empty($option['is_correct']) ? 'checked' : ''; ?>>
                        Correct answer
                    </label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Feedback / explanation (shown after quiz)</label>
                    <textarea class="form-control" name="feedback_text" rows="2"><?php echo htmlspecialchars($option['feedback_text'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" class="form-control" name="display_order" value="<?php echo (int) ($option['display_order'] ?? 0); ?>" min="0">
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Option</button>
                <a href="discipleship.php?action=questions&module_id=<?php echo $module_id; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
} else {
    // List programs (default)
    $stmt = $db->query("SELECT * FROM discipleship_programs ORDER BY display_order ASC, program_name ASC");
    $programs = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Discipleship Programs</h2>
        <a href="discipleship.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add New Program</a>
    </div>
    <div class="card">
        <div class="card-body">
            <?php if (empty($programs)): ?>
                <p class="text-muted">No programs found. <a href="discipleship.php?action=add">Add your first program</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable" data-dt-options='{"order":[[2,"asc"]]}'>
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Duration</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programs as $program): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($program['program_name']); ?></td>
                                    <td><?php echo htmlspecialchars($program['duration']); ?></td>
                                    <td><?php echo (int) $program['display_order']; ?></td>
                                    <td><span class="badge bg-<?php echo $program['status'] === 'active' ? 'success' : ($program['status'] === 'upcoming' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($program['status']); ?></span></td>
                                    <td>
                                        <a href="discipleship.php?action=modules&id=<?php echo (int) $program['id']; ?>" class="btn btn-sm btn-outline-info" title="Manage modules, resources &amp; questions"><i class="bi bi-collection me-1"></i>Modules</a>
                                        <a href="discipleship.php?action=edit&id=<?php echo (int) $program['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit program"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this program and all its modules?');">
                                            <input type="hidden" name="id" value="<?php echo (int) $program['id']; ?>">
                                            <button type="submit" name="delete_program" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

echo '</div>'; /* .admin-discipleship */
require_once __DIR__ . '/includes/footer.php';
