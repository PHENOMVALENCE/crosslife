<?php
$pageTitle = 'Admin Guide & Settings';
require_once 'includes/header.php';

$db = getDB();

// Handle basic site settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = str_replace('setting_', '', $key);
            $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$settingKey, sanitize($value), sanitize($value)]);
        }
    }
    redirect('settings.php', 'Settings updated successfully.');
}

$stmt = $db->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-compass me-2"></i>Cross Admin Guide</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    This page is a quick manual to help administrators understand how to use the CrossLife Cross Admin.
                    Use the left sidebar menu to move between sections.
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
                    <li><strong>Discipleship</strong>: Configure discipleship programs and details.</li>
                    <li><strong>Leadership</strong>: Manage church leadership profiles and display order.</li>
                </ul>

                <h6 class="mt-3">3. Communications</h6>
                <ul>
                    <li><strong>Contacts</strong>: View messages from the website contact form, mark as read, and send email replies.</li>
                    <li><strong>Prayer Requests</strong>: Track prayer requests, update their status, and add admin notes.</li>
                    <li><strong>Feedback</strong>: Review visitor feedback and archive entries once processed.</li>
                    <li><strong>Newsletter</strong>: See all newsletter subscribers collected from the website footer form.</li>
                </ul>

                <h6 class="mt-3">4. Working with Tables (DataTables)</h6>
                <ul>
                    <li>Use the <strong>Search</strong> box above each table to quickly find a name, email, or keyword.</li>
                    <li>Change the number of rows using the <strong>“Show X entries”</strong> dropdown.</li>
                    <li>Click column headers (e.g. <strong>Name</strong>, <strong>Date</strong>, <strong>Status</strong>) to sort.</li>
                    <li>Use the <strong>CSV / Excel / Print</strong> buttons to export data for reports or backups.</li>
                </ul>

                <h6 class="mt-3">5. Security & Best Practices</h6>
                <ul>
                    <li>Always <strong>log out</strong> using the button in the top-right when you finish.</li>
                    <li>Only trusted admins should have access to this panel.</li>
                    <li>Use strong, unique passwords and never share your login details.</li>
                </ul>

                <h6 class="mt-3">6. When Something Goes Wrong</h6>
                <p class="mb-0">
                    If you see errors or missing data, first refresh the page. If the issue continues, contact technical
                    support and share any error message shown at the top of the screen.
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Basic Site Settings</h5>
                <small class="text-muted d-none d-md-inline">For super admins</small>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    These fields control how the site name and contact details appear on the public website.
                </p>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Site Name</label>
                        <input type="text" class="form-control" name="setting_site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Site Email</label>
                        <input type="email" class="form-control" name="setting_site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? 'karibu@crosslife.org'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Site Phone</label>
                        <input type="text" class="form-control" name="setting_site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? '+255 (0)6 531 265 83'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Site Location</label>
                        <input type="text" class="form-control" name="setting_site_location" value="<?php echo htmlspecialchars($settings['site_location'] ?? 'Dar es Salaam, Tanzania'); ?>">
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
