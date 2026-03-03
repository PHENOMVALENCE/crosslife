<?php
/**
 * Certificate of Completion – Issued by Admin
 * Shows an elegantly designed certificate when admin has issued it for a completed enrollment.
 * Includes download-as-PDF functionality via html2canvas + jsPDF.
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';
requireStudentLogin();

$student = getCurrentStudent();
$enrollmentId = isset($_GET['enrollment_id']) ? (int) $_GET['enrollment_id'] : 0;

if (!$enrollmentId) {
    $_SESSION['flash_message'] = 'Invalid certificate request.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

$enrollment = discipleship_get_enrollment($enrollmentId, $student['id']);
if (!$enrollment) {
    $_SESSION['flash_message'] = 'Enrollment not found.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

if ((isset($enrollment['status']) ? $enrollment['status'] : '') !== 'completed') {
    $_SESSION['flash_message'] = 'Certificate is available when you complete all modules.';
    $_SESSION['flash_type'] = 'info';
    header('Location: program.php?enrollment_id=' . $enrollmentId);
    exit;
}

// Check if certificate has been issued by admin
$db = getDB();
$stmt = $db->prepare("SELECT e.*, a.full_name AS issued_by_name FROM discipleship_enrollments e LEFT JOIN admins a ON a.id = e.certificate_issued_by WHERE e.id = ?");
$stmt->execute([$enrollmentId]);
$certData = $stmt->fetch(PDO::FETCH_ASSOC);

$certificateIssued = !empty($certData['certificate_issued']);
$certificateNumber = $certData['certificate_number'] ?? null;
$certificateIssuedAt = $certData['certificate_issued_at'] ?? null;
$issuedByName = $certData['issued_by_name'] ?? null;
$certificateRemarks = $certData['certificate_remarks'] ?? null;
$completedAt = $certData['completed_at'] ?? null;

$program = discipleship_get_program($enrollment['program_id']);

// Count modules completed
$stmt = $db->prepare("SELECT COUNT(*) FROM discipleship_modules WHERE program_id = ?");
$stmt->execute([$enrollment['program_id']]);
$totalModules = (int)$stmt->fetchColumn();

$siteName = defined('SITE_NAME') ? SITE_NAME : 'CrossLife Mission Network';

$pageTitle = 'Certificate – ' . $program['program_name'];
$breadcrumb = [
    ['Dashboard', 'dashboard.php'],
    ['Certificate', '']
];
require_once __DIR__ . '/includes/header.php';
?>

<?php if (!$certificateIssued): ?>
<!-- Certificate Pending -->
<div class="text-center py-5">
    <div class="mb-4">
        <i class="bi bi-hourglass-split" style="font-size: 4rem; color: var(--accent);"></i>
    </div>
    <h1 class="h3 mb-3" style="color: var(--text-primary);">Certificate Pending</h1>
    <p class="text-muted mb-2">Congratulations on completing <strong><?php echo htmlspecialchars($program['program_name']); ?></strong>!</p>
    <p class="text-muted mb-4">Your certificate is currently being reviewed by our administration team.<br>You will be notified once it has been issued.</p>
    <div class="card mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>All <?php echo $totalModules; ?> modules completed</span>
            </div>
            <?php if ($completedAt): ?>
            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                <i class="bi bi-calendar-check text-primary"></i>
                <span>Completed on <?php echo date('F j, Y', strtotime($completedAt)); ?></span>
            </div>
            <?php endif; ?>
            <div class="d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-clock text-warning"></i>
                <span>Awaiting admin approval</span>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <a href="dashboard.php" class="btn btn-outline-elms"><i class="bi bi-arrow-left me-2"></i>Back to Dashboard</a>
    </div>
</div>

<?php else: ?>
<!-- Certificate Issued -->
<div class="text-center mb-4 no-print">
    <h1 class="h3 mb-2" style="color: var(--text-primary);">
        <i class="bi bi-award-fill me-2" style="color: var(--accent);"></i>Congratulations!
    </h1>
    <p class="text-muted">Your certificate for <strong><?php echo htmlspecialchars($program['program_name']); ?></strong> has been officially issued.</p>
</div>

<!-- Certificate Card (this is what gets captured for PDF) -->
<div id="certificateContainer" class="certificate-container mb-4">
    <div class="certificate-frame">
        <!-- Decorative corner ornaments -->
        <div class="cert-corner cert-corner-tl"></div>
        <div class="cert-corner cert-corner-tr"></div>
        <div class="cert-corner cert-corner-bl"></div>
        <div class="cert-corner cert-corner-br"></div>

        <!-- Watermark -->
        <div class="cert-watermark">
            <img src="../assets/img/logo.png" alt="" class="cert-watermark-img">
        </div>

        <div class="certificate-inner">
            <!-- Header -->
            <div class="cert-header">
                <img src="../assets/img/logo.png" alt="" width="64" height="64" class="rounded cert-logo">
                <div class="cert-org-name"><?php echo htmlspecialchars($siteName); ?></div>
                <div class="cert-subtitle">School of Christ Academy</div>
            </div>

            <!-- Title -->
            <div class="cert-title-section">
                <div class="cert-decorative-line"></div>
                <h2 class="cert-title">Certificate of Completion</h2>
                <div class="cert-decorative-line"></div>
            </div>

            <!-- Body -->
            <div class="cert-body">
                <p class="cert-preamble">This is to certify that</p>
                <h3 class="cert-student-name"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                <p class="cert-description">
                    has successfully completed all <?php echo $totalModules; ?> modules and requirements of
                </p>
                <h4 class="cert-program-name"><?php echo htmlspecialchars($program['program_name']); ?></h4>
                <?php if (!empty($program['duration'])): ?>
                <p class="cert-duration">Duration: <?php echo htmlspecialchars($program['duration']); ?></p>
                <?php endif; ?>
                <?php if ($certificateRemarks): ?>
                <p class="cert-remarks"><em>&ldquo;<?php echo htmlspecialchars($certificateRemarks); ?>&rdquo;</em></p>
                <?php endif; ?>
            </div>

            <!-- Footer details -->
            <div class="cert-footer">
                <div class="cert-footer-columns">
                    <div class="cert-footer-col">
                        <div class="cert-signature-line"></div>
                        <div class="cert-label"><?php echo $issuedByName ? htmlspecialchars($issuedByName) : 'Administrator'; ?></div>
                        <div class="cert-sublabel">Authorized Signatory</div>
                    </div>
                    <div class="cert-footer-col cert-seal-col">
                        <div class="cert-seal">
                            <div class="cert-seal-inner">
                                <i class="bi bi-award-fill"></i>
                                <span>CERTIFIED</span>
                            </div>
                        </div>
                    </div>
                    <div class="cert-footer-col">
                        <div class="cert-signature-line"></div>
                        <div class="cert-label"><?php echo $certificateIssuedAt ? date('F j, Y', strtotime($certificateIssuedAt)) : date('F j, Y'); ?></div>
                        <div class="cert-sublabel">Date Issued</div>
                    </div>
                </div>

                <!-- Certificate metadata -->
                <div class="cert-metadata">
                    <?php if ($certificateNumber): ?>
                    <span class="cert-meta-item">
                        <i class="bi bi-hash"></i> Certificate No: <strong><?php echo htmlspecialchars($certificateNumber); ?></strong>
                    </span>
                    <?php endif; ?>
                    <?php if ($completedAt): ?>
                    <span class="cert-meta-item">
                        <i class="bi bi-calendar-check"></i> Completed: <?php echo date('F j, Y', strtotime($completedAt)); ?>
                    </span>
                    <?php endif; ?>
                    <span class="cert-meta-item">
                        <i class="bi bi-person-badge"></i> Student ID: <?php echo str_pad($student['id'], 5, '0', STR_PAD_LEFT); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="d-flex flex-wrap justify-content-center gap-2 mb-4 no-print">
    <button type="button" class="btn btn-elms-accent" id="downloadPdfBtn" onclick="downloadCertificatePDF()">
        <i class="bi bi-file-earmark-pdf me-2"></i>Download as PDF
    </button>
    <button type="button" class="btn btn-outline-elms" id="downloadImageBtn" onclick="downloadCertificateImage()">
        <i class="bi bi-image me-2"></i>Download as Image
    </button>
    <button type="button" class="btn btn-outline-elms" onclick="window.print();">
        <i class="bi bi-printer me-2"></i>Print Certificate
    </button>
    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<!-- Certificate Styles -->
<style>
/* Certificate Container */
.certificate-container {
    max-width: 900px;
    margin: 0 auto;
}

.certificate-frame {
    position: relative;
    background: linear-gradient(135deg, #fffef5 0%, #fff9e6 30%, #fffef5 50%, #fff9e6 70%, #fffef5 100%);
    border: 3px solid #b8860b;
    border-radius: 4px;
    padding: 8px;
    box-shadow:
        0 0 0 1px #daa520,
        0 0 0 4px #f5f0e0,
        0 0 0 5px #b8860b,
        0 8px 30px rgba(0,0,0,0.15);
}

/* Corner ornaments */
.cert-corner {
    position: absolute;
    width: 60px;
    height: 60px;
    border-color: #b8860b;
    z-index: 2;
}
.cert-corner-tl { top: 12px; left: 12px; border-top: 3px solid; border-left: 3px solid; }
.cert-corner-tr { top: 12px; right: 12px; border-top: 3px solid; border-right: 3px solid; }
.cert-corner-bl { bottom: 12px; left: 12px; border-bottom: 3px solid; border-left: 3px solid; }
.cert-corner-br { bottom: 12px; right: 12px; border-bottom: 3px solid; border-right: 3px solid; }

/* Watermark */
.cert-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.04;
    pointer-events: none;
    z-index: 0;
}
.cert-watermark-img {
    width: 300px;
    height: 300px;
    object-fit: contain;
}

/* Inner content */
.certificate-inner {
    position: relative;
    z-index: 1;
    padding: 2.5rem 3rem;
    text-align: center;
}

/* Header */
.cert-header {
    margin-bottom: 1.5rem;
}
.cert-logo {
    margin-bottom: 0.75rem;
    border: 2px solid #daa520;
    padding: 2px;
}
.cert-org-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 0.1rem;
}
.cert-subtitle {
    font-size: 0.95rem;
    color: #666;
    letter-spacing: 3px;
    text-transform: uppercase;
}

/* Title section */
.cert-title-section {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
}
.cert-decorative-line {
    flex: 1;
    max-width: 120px;
    height: 1px;
    background: linear-gradient(90deg, transparent, #b8860b, transparent);
}
.cert-title {
    font-size: 2rem;
    font-weight: 300;
    color: #b8860b;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0;
    white-space: nowrap;
    font-family: Georgia, 'Times New Roman', serif;
}

/* Body */
.cert-body {
    margin-bottom: 2rem;
}
.cert-preamble {
    font-size: 1rem;
    color: #555;
    margin-bottom: 0.5rem;
    font-style: italic;
}
.cert-student-name {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.75rem;
    font-family: Georgia, 'Times New Roman', serif;
    border-bottom: 2px solid #daa520;
    display: inline-block;
    padding: 0 2rem 0.3rem;
}
.cert-description {
    font-size: 1rem;
    color: #555;
    margin-bottom: 0.5rem;
    line-height: 1.7;
}
.cert-program-name {
    font-size: 1.5rem;
    font-weight: 600;
    color: #b8860b;
    margin-bottom: 0.5rem;
    font-family: Georgia, 'Times New Roman', serif;
}
.cert-duration {
    font-size: 0.9rem;
    color: #777;
    margin-bottom: 0.5rem;
}
.cert-remarks {
    font-size: 0.9rem;
    color: #555;
    max-width: 500px;
    margin: 0.75rem auto 0;
}

/* Footer */
.cert-footer {
    margin-top: 2rem;
}
.cert-footer-columns {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.cert-footer-col {
    flex: 1;
    text-align: center;
}
.cert-seal-col {
    flex: 0 0 auto;
}
.cert-signature-line {
    width: 80%;
    margin: 0 auto 0.5rem;
    border-bottom: 1px solid #b8860b;
}
.cert-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
}
.cert-sublabel {
    font-size: 0.75rem;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Seal */
.cert-seal {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid #b8860b;
    background: linear-gradient(135deg, #fff9e6, #f5edd4);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(184, 134, 11, 0.3);
}
.cert-seal-inner {
    text-align: center;
    color: #b8860b;
}
.cert-seal-inner i {
    font-size: 1.5rem;
    display: block;
    margin-bottom: 0.1rem;
}
.cert-seal-inner span {
    font-size: 0.55rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
}

/* Metadata */
.cert-metadata {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e0d5c0;
}
.cert-meta-item {
    font-size: 0.75rem;
    color: #888;
}
.cert-meta-item i {
    color: #b8860b;
    margin-right: 0.2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .certificate-inner {
        padding: 1.5rem 1rem;
    }
    .cert-title {
        font-size: 1.3rem;
        letter-spacing: 1px;
    }
    .cert-student-name {
        font-size: 1.5rem;
        padding: 0 1rem 0.2rem;
    }
    .cert-program-name {
        font-size: 1.15rem;
    }
    .cert-org-name {
        font-size: 1.15rem;
        letter-spacing: 1px;
    }
    .cert-footer-columns {
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
    }
    .cert-footer-col { width: 80%; }
    .cert-corner { width: 30px; height: 30px; }
    .cert-watermark-img { width: 180px; height: 180px; }
    .cert-decorative-line { max-width: 50px; }
    .cert-metadata {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
}

/* Print styles */
@media print {
    .no-print { display: none !important; }
    body, .student-portal .elms-main-wrap, .student-portal .elms-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .certificate-container {
        max-width: 100%;
        margin: 0;
    }
    .certificate-frame {
        box-shadow: none;
        border: 3px solid #b8860b;
        page-break-inside: avoid;
    }
}
</style>

<!-- html2canvas + jsPDF for PDF/Image download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function showDownloadProgress(btn, text) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + text;
}
function resetButton(btn, html) {
    btn.disabled = false;
    btn.innerHTML = html;
}

function downloadCertificatePDF() {
    var btn = document.getElementById('downloadPdfBtn');
    var originalHtml = btn.innerHTML;
    showDownloadProgress(btn, 'Generating PDF...');

    var element = document.getElementById('certificateContainer');

    html2canvas(element, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#fffef5',
        logging: false
    }).then(function(canvas) {
        var { jsPDF } = window.jspdf;
        var imgData = canvas.toDataURL('image/png');
        var imgWidth = canvas.width;
        var imgHeight = canvas.height;

        // A4 landscape dimensions in mm
        var pdfWidth = 297;
        var pdfHeight = 210;

        // Calculate scaling to fit
        var ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
        var finalW = imgWidth * ratio;
        var finalH = imgHeight * ratio;
        var offsetX = (pdfWidth - finalW) / 2;
        var offsetY = (pdfHeight - finalH) / 2;

        var pdf = new jsPDF('landscape', 'mm', 'a4');
        pdf.addImage(imgData, 'PNG', offsetX, offsetY, finalW, finalH);

        var fileName = 'Certificate-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $program['program_name']); ?>-<?php echo $certificateNumber ?: 'cert'; ?>.pdf';
        pdf.save(fileName);

        resetButton(btn, originalHtml);
    }).catch(function(err) {
        console.error('PDF generation error:', err);
        alert('Failed to generate PDF. Please try printing instead.');
        resetButton(btn, originalHtml);
    });
}

function downloadCertificateImage() {
    var btn = document.getElementById('downloadImageBtn');
    var originalHtml = btn.innerHTML;
    showDownloadProgress(btn, 'Generating Image...');

    var element = document.getElementById('certificateContainer');

    html2canvas(element, {
        scale: 3,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#fffef5',
        logging: false
    }).then(function(canvas) {
        var link = document.createElement('a');
        link.download = 'Certificate-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $program['program_name']); ?>-<?php echo $certificateNumber ?: 'cert'; ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();

        resetButton(btn, originalHtml);
    }).catch(function(err) {
        console.error('Image generation error:', err);
        alert('Failed to generate image. Please try printing instead.');
        resetButton(btn, originalHtml);
    });
}
</script>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
