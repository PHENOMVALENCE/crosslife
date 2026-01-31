<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
$db = getDB();

// ----- Admin Account Management: POST handlers (must run before any output) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_admin') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'admin';
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            redirect('settings.php', 'All fields are required.', 'danger');
        }
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            redirect('settings.php', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.', 'danger');
        }
        $allowed_roles = ['super_admin', 'admin', 'editor'];
        if (!in_array($role, $allowed_roles, true)) {
            $role = 'admin';
        }
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO admins (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$username, $email, $hashed, $full_name, $role]);
            redirect('settings.php', 'Admin account created successfully.');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                redirect('settings.php', 'Username or email already exists.', 'danger');
            }
            redirect('settings.php', 'Error creating admin: ' . $e->getMessage(), 'danger');
        }
    }

    if ($action === 'update_admin') {
        $id = (int) ($_POST['admin_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'admin';
        $status = $_POST['status'] ?? 'active';
        if ($id < 1 || empty($full_name) || empty($email)) {
            redirect('settings.php', 'Invalid data.', 'danger');
        }
        $allowed_roles = ['super_admin', 'admin', 'editor'];
        $allowed_status = ['active', 'inactive'];
        if (!in_array($role, $allowed_roles, true)) $role = 'admin';
        if (!in_array($status, $allowed_status, true)) $status = 'active';
        try {
            $stmt = $db->prepare("UPDATE admins SET full_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $role, $status, $id]);
            redirect('settings.php', 'Admin account updated.');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                redirect('settings.php', 'Email already in use by another account.', 'danger');
            }
            redirect('settings.php', 'Error updating admin.', 'danger');
        }
    }

    if ($action === 'change_password') {
        $id = (int) ($_POST['admin_id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($id < 1) {
            redirect('settings.php', 'Invalid admin.', 'danger');
        }
        if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            redirect('settings.php', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.', 'danger');
        }
        if ($new_password !== $confirm) {
            redirect('settings.php', 'Passwords do not match.', 'danger');
        }
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $id]);
        redirect('settings.php', 'Password updated successfully.');
    }
}

// Load admins for listing
$admins = [];
try {
    $stmt = $db->query("SELECT id, username, email, full_name, role, status, last_login, created_at FROM admins ORDER BY full_name ASC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // table may not exist
}

$pageTitle = 'User Manual & Admin Account Management';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <!-- User Manual -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-book me-2"></i>User Manual</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    This manual helps administrators use the CrossLife admin panel. Use the left sidebar to move between sections.
                </p>

                <h6 class="mt-3">1. Dashboard</h6>
                <p class="mb-2">The dashboard gives you a quick overview of ministry activity:</p>
                <ul>
                    <li>Summary cards for total ministries, messages, prayer requests, and feedback.</li>
                    <li>Shortcuts to view full tables for each area.</li>
                </ul>

                <h6 class="mt-3">2. Content Management</h6>
                <ul>
                    <li><strong>Sermons</strong>: Add, edit, and publish sermons (video or audio).</li>
                    <li><strong>Events</strong>: Manage upcoming, ongoing, and completed events.</li>
                    <li><strong>Ministries</strong>: Full CRUD for all church ministries (name, description, leader, image, status).</li>
                    <li><strong>Discipleship</strong>: Configure discipleship programs, modules, resources, and assessments.</li>
                    <li><strong>Leadership</strong>: Manage church leadership profiles and display order.</li>
                </ul>

                <h6 class="mt-3">3. User & Student Tracking</h6>
                <ul>
                    <li>View all <strong>admin accounts</strong> and <strong>students</strong> (discipleship learners).</li>
                    <li>See student enrollments and <strong>studying progress</strong> (modules passed, quiz attempts).</li>
                    <li>Use the Users menu item to open this section.</li>
                </ul>

                <h6 class="mt-3">4. Communications</h6>
                <ul>
                    <li><strong>Contacts</strong>: View messages from the website contact form, mark as read, and send email replies.</li>
                    <li><strong>Prayer Requests</strong>: Track prayer requests, update their status, and add admin notes.</li>
                    <li><strong>Feedback</strong>: Review visitor feedback and archive entries once processed.</li>
                    <li><strong>Newsletter</strong>: See all newsletter subscribers from the website footer form.</li>
                </ul>

                <h6 class="mt-3">5. Working with Tables (DataTables)</h6>
                <ul>
                    <li>Use the <strong>Search</strong> box above each table to find a name, email, or keyword.</li>
                    <li>Change the number of rows with the <strong>“Show X entries”</strong> dropdown.</li>
                    <li>Click column headers (e.g. Name, Date, Status) to sort.</li>
                    <li>Use <strong>CSV / Excel / Print</strong> buttons to export data.</li>
                </ul>

                <h6 class="mt-3">6. Security & Best Practices</h6>
                <ul>
                    <li>Always <strong>log out</strong> using the button in the top-right when you finish.</li>
                    <li>Only trusted admins should have access to this panel.</li>
                    <li>Use strong, unique passwords and never share your login details.</li>
                </ul>

                <h6 class="mt-3">7. When Something Goes Wrong</h6>
                <p class="mb-0">
                    If you see errors or missing data, refresh the page. If the issue continues, contact technical
                    support and share any error message shown at the top of the screen.
                </p>
            </div>
        </div>

        <!-- Admin Account Management -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Admin Account Management</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="bi bi-person-plus me-1"></i>Add Admin
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Manage admin users who can access this panel. You can add new admins, edit details, change passwords, or set status to inactive.
                </p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="adminsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th class="text-end">Actions</th>
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
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAdminModal" data-id="<?php echo (int)$a['id']; ?>" data-username="<?php echo htmlspecialchars($a['username']); ?>" data-email="<?php echo htmlspecialchars($a['email']); ?>" data-fullname="<?php echo htmlspecialchars($a['full_name']); ?>" data-role="<?php echo htmlspecialchars($a['role']); ?>" data-status="<?php echo htmlspecialchars($a['status']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#passwordModal" data-id="<?php echo (int)$a['id']; ?>" data-name="<?php echo htmlspecialchars($a['full_name']); ?>">
                                                <i class="bi bi-key"></i>
                                            </button>
                                        </div>
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

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_admin">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAdminModalLabel">Add Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span> (min <?php echo PASSWORD_MIN_LENGTH; ?> characters)</label>
                        <input type="password" class="form-control" name="password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_admin">
                <input type="hidden" name="admin_id" id="edit_admin_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAdminModalLabel">Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="edit_role">
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="admin_id" id="password_admin_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="password_admin_name"></p>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span> (min <?php echo PASSWORD_MIN_LENGTH; ?> characters)</label>
                        <input type="password" class="form-control" name="new_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="confirm_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editAdminModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (btn && btn.dataset.id) {
                document.getElementById('edit_admin_id').value = btn.dataset.id;
                document.getElementById('edit_username').value = btn.dataset.username || '';
                document.getElementById('edit_full_name').value = btn.dataset.fullname || '';
                document.getElementById('edit_email').value = btn.dataset.email || '';
                document.getElementById('edit_role').value = btn.dataset.role || 'admin';
                document.getElementById('edit_status').value = btn.dataset.status || 'active';
            }
        });
    }
    var pwdModal = document.getElementById('passwordModal');
    if (pwdModal) {
        pwdModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (btn && btn.dataset.id) {
                document.getElementById('password_admin_id').value = btn.dataset.id;
                document.getElementById('password_admin_name').textContent = 'Changing password for: ' + (btn.dataset.name || 'Admin');
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
