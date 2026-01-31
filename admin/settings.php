<?php
$pageTitle = 'Settings';
require_once 'includes/header.php';

$db = getDB();

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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Site Settings</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
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
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Settings</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

