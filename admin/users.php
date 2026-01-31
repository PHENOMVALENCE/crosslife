<?php
/**
 * User & Student Tracking
 * Lists all admins and discipleship students with studying progress.
 */
$pageTitle = 'Users & Students';
require_once 'includes/header.php';

require_once __DIR__ . '/../includes/discipleship-functions.php';

$db = getDB();
$view = $_GET['view'] ?? 'list';
$studentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ----- Student detail view -----
if ($view === 'student' && $studentId > 0) {
    $stmt = $db->prepare("SELECT id, email, full_name, phone, status, last_login, created_at FROM discipleship_students WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        redirect('users.php', 'Student not found.', 'danger');
    }
    $enrollments = discipleship_get_student_enrollments($studentId);
    $progressByEnrollment = [];
    foreach ($enrollments as $e) {
        $modules = discipleship_get_modules($e['program_id']);
        $passedIds = discipleship_get_passed_module_ids($e['id']);
        $progressByEnrollment[$e['id']] = [
            'modules' => $modules,
            'passed_ids' => $passedIds,
        ];
    }
    ?>
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="users.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Users</a>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Student: <?php echo htmlspecialchars($student['full_name']); ?></h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                    <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone'] ?? '—'); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?php echo $student['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo htmlspecialchars($student['status']); ?></span></p>
                    <p class="mb-1"><strong>Last login:</strong> <?php echo $student['last_login'] ? formatDateTime($student['last_login'], 'M j, Y g:i A') : '—'; ?></p>
                    <p class="mb-0"><strong>Registered:</strong> <?php echo formatDateTime($student['created_at'], 'M j, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-journal-check me-2"></i>Studying Progress</h5>
        </div>
        <div class="card-body">
            <?php if (empty($enrollments)): ?>
                <p class="text-muted mb-0">This student is not enrolled in any program yet.</p>
            <?php else: ?>
                <?php foreach ($enrollments as $e): ?>
                    <?php
                    $prog = $progressByEnrollment[$e['id']] ?? ['modules' => [], 'passed_ids' => []];
                    $modules = $prog['modules'];
                    $passedIds = $prog['passed_ids'];
                    $total = count($modules);
                    $passed = count($passedIds);
                    ?>
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-2"><?php echo htmlspecialchars($e['program_name']); ?></h6>
                        <p class="text-muted small mb-2">Enrolled <?php echo formatDateTime($e['enrolled_at'], 'M j, Y'); ?> · Status: <span class="badge bg-info"><?php echo htmlspecialchars($e['status']); ?></span></p>
                        <p class="mb-2"><strong>Progress:</strong> <?php echo $passed; ?> / <?php echo $total; ?> modules passed</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $m): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($m['title']); ?></td>
                                            <td>
                                                <?php if (in_array((int)$m['id'], $passedIds, true)): ?>
                                                    <span class="badge bg-success">Passed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not yet</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit;
}

// ----- List view: Admins + Students -----
$admins = [];
$students = [];
$studentProgress = []; // student_id => ['enrollments' => n, 'passed' => n]

try {
    $stmt = $db->query("SELECT id, username, email, full_name, role, status, last_login, created_at FROM admins ORDER BY full_name ASC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

try {
    $stmt = $db->query("SELECT id, email, full_name, phone, status, last_login, created_at FROM discipleship_students ORDER BY full_name ASC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Progress summary per student: enrollments count and total modules passed
foreach ($students as $s) {
    $sid = (int) $s['id'];
    $enrollments = discipleship_get_student_enrollments($sid);
    $totalPassed = 0;
    foreach ($enrollments as $e) {
        $passedIds = discipleship_get_passed_module_ids($e['id']);
        $totalPassed += count($passedIds);
    }
    $studentProgress[$sid] = [
        'enrollments' => count($enrollments),
        'passed' => $totalPassed,
    ];
}
?>

<ul class="nav nav-tabs mb-4" id="usersTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins-panel" type="button" role="tab">Admins</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students-panel" type="button" role="tab">Students</button>
    </li>
</ul>

<div class="tab-content" id="usersTabContent">
    <div class="tab-pane fade show active" id="admins-panel" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Admin Accounts</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Manage admin accounts from <a href="settings.php">User Manual & Account</a>.</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable" id="adminsTable" data-dt-options='{"order":[[0,"asc"]],"pageLength":25}'>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $a): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($a['username']); ?></td>
                                    <td><?php echo htmlspecialchars($a['email']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $a['role']))); ?></span></td>
                                    <td>
                                        <?php if ($a['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $a['last_login'] ? formatDateTime($a['last_login'], 'M j, Y g:i A') : '—'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="students-panel" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-mortarboard me-2"></i>Students (Discipleship)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Students registered for the School of Christ Academy. Click <strong>View progress</strong> to see enrollments and modules passed.</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="studentsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Enrollments</th>
                                <th>Modules Passed</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                                <?php $prog = $studentProgress[(int)$s['id']] ?? ['enrollments' => 0, 'passed' => 0]; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($s['email']); ?></td>
                                    <td><?php echo htmlspecialchars($s['phone'] ?? '—'); ?></td>
                                    <td>
                                        <?php if ($s['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $s['last_login'] ? formatDateTime($s['last_login'], 'M j, Y') : '—'; ?></td>
                                    <td><?php echo (int) $prog['enrollments']; ?></td>
                                    <td><?php echo (int) $prog['passed']; ?></td>
                                    <td class="text-end">
                                        <a href="users.php?view=student&id=<?php echo (int)$s['id']; ?>" class="btn btn-sm btn-outline-primary">View progress</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
