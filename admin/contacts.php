<?php
$pageTitle = 'Contact Inquiries';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_status'])) {
            if (empty($_POST['id'])) {
                redirect('contacts.php', 'Invalid inquiry ID.', 'danger');
            }
            
            $validStatuses = ['new', 'read', 'replied', 'archived'];
            $status = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : 'read';
            
            $stmt = $db->prepare("UPDATE contact_inquiries SET status = ?, admin_notes = ? WHERE id = ?");
            $stmt->execute([$status, sanitize($_POST['admin_notes'] ?? ''), $_POST['id']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('contacts.php', 'Inquiry updated successfully.');
            } else {
                redirect('contacts.php', 'No changes were made or inquiry not found.', 'info');
            }
        }
        
        // Admin reply via email
        if (isset($_POST['send_reply'])) {
            if (empty($_POST['id']) || empty($_POST['reply_message'])) {
                redirect('contacts.php', 'Reply message is required.', 'danger');
            }
            
            $stmt = $db->prepare("SELECT * FROM contact_inquiries WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $inquiry = $stmt->fetch();
            if (!$inquiry) {
                redirect('contacts.php', 'Inquiry not found.', 'danger');
            }
            
            $replyMessage = trim($_POST['reply_message']);
            
            // Send reply email using PHPMailer
            if (file_exists(__DIR__ . '/config/email.php')) {
                require_once __DIR__ . '/config/email.php';
                $to = $inquiry['email'];
                $subject = 'Re: ' . ($inquiry['subject'] ?: 'Your inquiry at CrossLife Mission Network');
                
                $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #c85716; color: white; padding: 20px; text-align: center; }
                        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                        .original { margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 0.9rem; color: #555; }
                        .label { font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Response from CrossLife Mission Network</h2>
                        </div>
                        <div class='content'>
                            <p>Grace and peace in Jesus' Name.</p>
                            <p>" . nl2br(htmlspecialchars($replyMessage)) . "</p>
                            <div class='original'>
                                <p class='label'>Your original message:</p>
                                <p><strong>Subject:</strong> " . htmlspecialchars($inquiry['subject']) . "</p>
                                <p>" . nl2br(htmlspecialchars($inquiry['message'])) . "</p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                $altBody = "Response from CrossLife Mission Network\n\n";
                $altBody .= $replyMessage . "\n\n";
                $altBody .= "----- Original message -----\n";
                $altBody .= "Subject: " . $inquiry['subject'] . "\n";
                $altBody .= $inquiry['message'] . "\n";
                
                try {
                    if (sendEmail($to, $subject, $body, $altBody)) {
                        // Mark as replied and store admin notes
                        $stmt = $db->prepare("UPDATE contact_inquiries SET status = 'replied', admin_notes = CONCAT(IFNULL(admin_notes, ''), :notes) WHERE id = :id");
                        $notesToAppend = "\n\n[" . date('Y-m-d H:i') . "] Reply sent:\n" . $replyMessage;
                        $stmt->bindValue(':notes', $notesToAppend, PDO::PARAM_STR);
                        $stmt->bindValue(':id', $inquiry['id'], PDO::PARAM_INT);
                        $stmt->execute();
                        
                        redirect('contacts.php?action=view&id=' . $inquiry['id'], 'Reply sent successfully.', 'success');
                    } else {
                        redirect('contacts.php?action=view&id=' . $inquiry['id'], 'Failed to send reply email. Please check email settings.', 'danger');
                    }
                } catch (Throwable $e) {
                    error_log('Error sending inquiry reply: ' . $e->getMessage());
                    redirect('contacts.php?action=view&id=' . $inquiry['id'], 'An error occurred while sending the reply.', 'danger');
                }
            } else {
                redirect('contacts.php', 'Email configuration not found. Cannot send reply.', 'danger');
            }
        }
        
        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('contacts.php', 'Invalid inquiry ID.', 'danger');
            }
            
            $stmt = $db->prepare("DELETE FROM contact_inquiries WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('contacts.php', 'Inquiry deleted successfully.');
            } else {
                redirect('contacts.php', 'Inquiry not found or already deleted.', 'warning');
            }
        }
    } catch (PDOException $e) {
        redirect('contacts.php', handleDBError($e, 'A database error occurred. Please try again.'), 'danger');
    } catch (Exception $e) {
        error_log("Error in contacts.php: " . $e->getMessage());
        redirect('contacts.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');
    }
}

if ($action === 'view' && $id) {
    try {
        $stmt = $db->prepare("SELECT * FROM contact_inquiries WHERE id = ?");
        $stmt->execute([$id]);
        $inquiry = $stmt->fetch();
        if (!$inquiry) {
            redirect('contacts.html', 'Inquiry not found.', 'danger');
        }
        
        // Mark as read if new
        if ($inquiry['status'] === 'new') {
            try {
                $updateStmt = $db->prepare("UPDATE contact_inquiries SET status = 'read' WHERE id = ?");
                $updateStmt->execute([$id]);
                $inquiry['status'] = 'read';
            } catch (PDOException $e) {
                error_log("Error updating inquiry status: " . $e->getMessage());
                // Continue even if status update fails
            }
        }
    } catch (PDOException $e) {
        redirect('contacts.html', handleDBError($e, 'Error loading inquiry.'), 'danger');
    }
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Contact Inquiry Details</h5>
            <a href="contacts.php" class="btn btn-sm btn-secondary">Back to List</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Contact Information</h6>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($inquiry['name']); ?></p>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>"><?php echo htmlspecialchars($inquiry['email']); ?></a></p>
                    <?php if ($inquiry['phone']): ?>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($inquiry['phone']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h6>Inquiry Details</h6>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($inquiry['subject']); ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-<?php echo $inquiry['status'] === 'new' ? 'primary' : ($inquiry['status'] === 'replied' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($inquiry['status']); ?></span></p>
                    <p><strong>Received:</strong> <?php echo formatDateTime($inquiry['created_at']); ?></p>
                </div>
            </div>
            
            <div class="mb-4">
                <h6>Message</h6>
                <div class="p-3 bg-light rounded">
                    <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                </div>
            </div>
            
            <form method="POST" class="mb-4">
                <input type="hidden" name="id" value="<?php echo $inquiry['id']; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="read" <?php echo $inquiry['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                            <option value="replied" <?php echo $inquiry['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                            <option value="archived" <?php echo $inquiry['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Updated</label>
                        <input type="text" class="form-control" value="<?php echo formatDateTime($inquiry['updated_at'] ?? $inquiry['created_at']); ?>" readonly>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Admin Notes</label>
                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Add notes about this inquiry..."><?php echo htmlspecialchars($inquiry['admin_notes'] ?? ''); ?></textarea>
                    <small class="form-text text-muted">These notes are only visible to admins.</small>
                </div>
                
                <button type="submit" name="update_status" class="btn btn-primary"><i class="bi bi-save me-2"></i>Update Status</button>
            </form>
            
            <hr>
            
            <h6 class="mb-3">Send Email Response</h6>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $inquiry['id']; ?>">
                <div class="mb-3">
                    <label class="form-label">Reply To</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($inquiry['email']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message *</label>
                    <textarea class="form-control" name="reply_message" rows="4" required placeholder="Write your response to this inquiry..."></textarea>
                </div>
                <button type="submit" name="send_reply" class="btn btn-success">
                    <i class="bi bi-send me-2"></i>Send Reply
                </button>
            </form>
        </div>
    </div>
    <?php
} else {
    try {
        $statusFilter = $_GET['status'] ?? 'all';
        $validStatuses = ['new', 'read', 'replied', 'archived'];
        if ($statusFilter !== 'all' && !in_array($statusFilter, $validStatuses)) {
            $statusFilter = 'all';
        }
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        
        // Use prepared statement to prevent SQL injection
        if ($statusFilter !== 'all') {
            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM contact_inquiries WHERE status = ?");
            $countStmt->execute([$statusFilter]);
            $total = $countStmt->fetch()['total'];
            
            $stmt = $db->prepare("SELECT * FROM contact_inquiries WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $statusFilter, PDO::PARAM_STR);
            $stmt->bindValue(2, ITEMS_PER_PAGE, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        } else {
            $countStmt = $db->query("SELECT COUNT(*) as total FROM contact_inquiries");
            $total = $countStmt->fetch()['total'];
            
            $stmt = $db->prepare("SELECT * FROM contact_inquiries ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, ITEMS_PER_PAGE, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $inquiries = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error loading inquiries: " . $e->getMessage());
        $inquiries = [];
        $total = 0;
        $totalPages = 0;
        $flash = getFlashMessage();
        if (!$flash) {
            $_SESSION['flash_message'] = 'Error loading inquiries. Please refresh the page.';
            $_SESSION['flash_type'] = 'danger';
        }
    }
    
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Contact Inquiries</h2>
        <div>
            <a href="?status=all" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'all' ? 'primary' : 'secondary'; ?>">All</a>
            <a href="?status=new" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'new' ? 'primary' : 'secondary'; ?>">New</a>
            <a href="?status=read" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'read' ? 'primary' : 'secondary'; ?>">Read</a>
            <a href="?status=replied" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'replied' ? 'primary' : 'secondary'; ?>">Replied</a>
            <a href="?status=archived" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'archived' ? 'primary' : 'secondary'; ?>">Archived</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($inquiries)): ?>
                <p class="text-muted">No inquiries found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                    <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                    <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                    <td><span class="badge bg-<?php echo $inquiry['status'] === 'new' ? 'primary' : ($inquiry['status'] === 'replied' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($inquiry['status']); ?></span></td>
                                    <td><?php echo formatDate($inquiry['created_at']); ?></td>
                                    <td>
                                        <a href="contacts.html?action=view&id=<?php echo $inquiry['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this inquiry?');">
                                            <input type="hidden" name="id" value="<?php echo $inquiry['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

require_once 'includes/footer.php';
?>

