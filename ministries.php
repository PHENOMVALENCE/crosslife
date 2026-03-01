<?php
/**
 * Ministries Page - CrossLife Mission Network
 * Displays active ministries from the database (managed in Admin → Ministries).
 * Data: ministries table; only status = 'active' is shown, ordered by display_order, name.
 */
require_once __DIR__ . '/admin/config/config.php'; // Load config first so SITE_NAME etc. defined once
require_once __DIR__ . '/includes/db-functions.php';

$settings = getSiteSettings();
$ministries = getActiveMinistries();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Ministries - <?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?></title>
  <meta name="description" content="Explore the various ministries of CrossLife Mission Network working together to manifest Sons of God.">
  <meta name="keywords" content="CrossLife, Ministries, Teaching, Discipleship, Prayer, Outreach, Worship, Fellowship">

  <!-- Favicons -->
  <link href="assets/img/logo.png" rel="icon">
  <link href="assets/img/logo.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="index-page">

  <header id="header" class="header fixed-top">
    <div class="container-fluid container-xl position-relative">

      <div class="top-row d-flex align-items-center justify-content-between">
        <a href="index.html" class="logo d-flex align-items-center">
          <img src="assets/img/logo.png" alt="CrossLife Mission Network Logo">
          <h1 class="sitename">CROSSLIFE</h1>
        </a>
      </div>

    </div>

    <div class="nav-wrap">
      <div class="container d-flex justify-content-center position-relative">
        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="index.html#about">About</a></li>
            <li><a href="index.html#statement-of-faith">Statement of Faith</a></li>
            <li><a href="leadership.php">Leadership</a></li>
            <li><a href="ministries.php" class="active">Ministries</a></li>
            <li><a href="sermons.php">Sermons</a></li>
            <li><a href="discipleship.html">Discipleship</a></li>
            <li><a href="events.php">Events</a></li>
            <li><a href="contacts.html">Contact</a></li>
            <li><a href="galley.html">Gallery</a></li>
            <li class="nav-social-search">
              <div class="nav-icons">
                <button type="button" class="btn-search" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Search">
                  <i class="bi bi-search"></i>
                </button>
                <a href="https://www.facebook.com/crosslife_tz" class="facebook" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/crosslife_tz" class="instagram" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
                <a href="https://www.youtube.com/@CrossLifeTV" class="youtube" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"></i></a>
                <a href="https://www.tiktok.com/@CrossLife" class="tiktok" target="_blank" rel="noopener noreferrer"><i class="bi bi-tiktok"></i></a>
              </div>
            </li>
          </ul>
        </nav>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </div>
    </div>

  </header>

  <main class="main">

    <!-- Page Header -->
    <section class="page-header section dark-background" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/_MG_4859.jpg') center/cover;">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h1 data-aos="fade-up">Our Ministries</h1>
            <p data-aos="fade-up" data-aos-delay="100">Various ministries working together to manifest Sons of God and establish a global network</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Ministries Section -->
    <section id="ministries" class="ministries section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <?php if (empty($ministries)): ?>
          <!-- Empty state: only database content is shown; add ministries in Admin → Ministries -->
          <div class="row justify-content-center" data-aos="fade-up">
            <div class="col-lg-8 text-center py-5">
              <p class="lead text-muted">No ministries are currently listed. Content here is managed from the Cross Admin (Admin → Ministries). Add and publish ministries there to display them on this page.</p>
            </div>
          </div>
        <?php else: ?>
          <!-- Dynamic ministries from database -->
          <div class="row g-4">
            <?php 
            $delay = 150;
            foreach ($ministries as $ministry): 
              // Resolve image URL using central helper (handles relative, localhost legacy, and external URLs)
              if (!empty($ministry['image_url'])) {
                $image = image_url_for_display($ministry['image_url']);
              } else {
                $image = image_url_for_display('assets/img/_MG_4880.jpg'); // Fallback when admin does not set an image
              }
              $image = htmlspecialchars($image);
            ?>
              <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <div class="ministry-card">
                  <h3><?php echo htmlspecialchars($ministry['name']); ?></h3>
                  <p><?php echo nl2br(htmlspecialchars($ministry['description'])); ?></p>
                  
                  <?php if (!empty($ministry['leader_name']) || !empty($ministry['contact_email'])): ?>
                    <div class="ministry-info mt-3">
                      <?php if (!empty($ministry['leader_name'])): ?>
                        <p class="mb-1"><strong>Leader:</strong> <?php echo htmlspecialchars($ministry['leader_name']); ?></p>
                      <?php endif; ?>
                      <?php if (!empty($ministry['contact_email'])): ?>
                        <p class="mb-0"><strong>Contact:</strong> <a href="mailto:<?php echo htmlspecialchars($ministry['contact_email']); ?>"><?php echo htmlspecialchars($ministry['contact_email']); ?></a></p>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                  
                  <div class="ministry-image mt-3">
                    <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($ministry['name']); ?>" class="img-fluid rounded">
                  </div>
                </div>
              </div>
            <?php 
              $delay += 50;
            endforeach; 
            ?>
          </div>
        <?php endif; ?>

        <div class="row mt-5">
          <div class="col-lg-12 text-center" data-aos="fade-up" data-aos-delay="450">
            <h3 class="mb-3">Get Involved</h3>
            <p class="lead">We welcome you to be part of any of our ministries. Each ministry is designed to help you grow in your identity in Christ and fulfill the mandate of Christ on earth.</p>
            <a href="contacts.html" class="btn btn-primary mt-3">Contact Us to Get Involved</a>
          </div>
        </div>

      </div>

    </section><!-- /Ministries Section -->

  </main>

  <!-- Search Modal -->
  <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="searchModalLabel">Search</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="globalSearchForm" class="search-form">
            <div class="input-group input-group-lg">
              <input type="text" class="form-control" id="globalSearchInput" placeholder="Search sermons, events, ministries, discipleship programs..." autocomplete="off">
              <button class="btn btn-primary" type="submit">
                <i class="bi bi-search"></i>
              </button>
            </div>
          </form>
          <div id="searchResults" class="search-results mt-4" style="display: none;">
            <h6 class="mb-3">Search Results:</h6>
            <ul id="searchResultsList" class="list-unstyled">
              <!-- Search results will be populated here -->
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer id="footer" class="footer dark-background">

    <div class="container">
      <div class="row gy-5">

        <div class="col-lg-4">
          <div class="footer-content">
            <a href="index.html" class="logo d-flex align-items-center mb-4">
              <span class="sitename"><?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?></span>
            </a>
            <p class="mb-4">A non-denominational and inter-denominational Christian ministry in Dar es Salaam, Tanzania. We exist to manifest Sons of God who understand their identity in Christ and what Christ can accomplish through them.</p>

            <div class="newsletter-form">
              <h5>Stay Updated</h5>
              <form action="forms/newsletter.php" method="post" class="newsletter-form-submit">
                <div class="input-group">
                  <input type="email" name="email" class="form-control" placeholder="Enter your email" required="">
                  <button type="submit" class="btn-subscribe">
                    <i class="bi bi-send"></i>
                  </button>
                </div>
                <div class="loading" style="display: none;">Loading</div>
                <div class="error-message" style="display: none;"></div>
                <div class="sent-message" style="display: none;">You have been subscribed to the CrossLife newsletter. Thank you for staying connected!</div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-2 col-6">
          <div class="footer-links">
            <h4>Ministry</h4>
            <ul>
              <li><a href="index.html#about"><i class="bi bi-chevron-right"></i> About Us</a></li>
              <li><a href="index.html#statement-of-faith"><i class="bi bi-chevron-right"></i> Statement of Faith</a></li>
              <li><a href="contacts.html"><i class="bi bi-chevron-right"></i> Contact</a></li>
            </ul>
          </div>
        </div>

        <div class="col-lg-2 col-6">
          <div class="footer-links">
            <h4>Resources</h4>
            <ul>
              <li><a href="https://www.youtube.com/@CrossLifeTV" target="_blank" rel="noopener noreferrer"><i class="bi bi-chevron-right"></i> CrossLife TV</a></li>
              <li><a href="https://www.youtube.com/@PastorLenhardKyamba" target="_blank" rel="noopener noreferrer"><i class="bi bi-chevron-right"></i> Pastor Lenhard Kyamba</a></li>
            </ul>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="footer-contact">
            <h4>Get in Touch</h4>
            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi bi-geo-alt"></i>
              </div>
              <div class="contact-info">
                <p>Dar es Salaam<br>Tanzania</p>
              </div>
            </div>

            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi bi-telephone"></i>
              </div>
              <div class="contact-info">
                <p>+255 (0)6 531 265 83<br>+255 (0)7 100 738 60</p>
              </div>
            </div>

            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi bi-envelope"></i>
              </div>
              <div class="contact-info">
                <p>karibu@crosslife.org<br>lenhard.kyamba@crosslife.org</p>
              </div>
            </div>

            <div class="social-links">
              <a href="https://www.facebook.com/crosslife_tz" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
              <a href="https://www.instagram.com/crosslife_tz" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
              <a href="https://www.youtube.com/@CrossLifeTV" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"></i></a>
              <a href="https://t.me/PastorLenhardKyamba" target="_blank" rel="noopener noreferrer"><i class="bi bi-telegram"></i></a>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="copyright">
              <p>© <span>Copyright</span> <strong class="px-1 sitename">CrossLife Mission Network</strong> <span>All Rights Reserved</span></p>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="footer-bottom-links">
              <a href="admin/login.php"><i class="bi bi-shield-lock me-1"></i>Admin</a>
              <a href="#">Privacy Policy</a>
              <a href="#">Terms of Service</a>
              <a href="#">Cookie Policy</a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>

