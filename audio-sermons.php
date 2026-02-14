<?php
/**
 * Audio Sermons Page - CrossLife Mission Network
 * Display audio sermons from database
 */
require_once 'includes/db-functions.php';

// Get filter parameters
$categoryFilter = $_GET['category'] ?? null;
$speakerFilter = $_GET['speaker'] ?? null;
$page = max(1, intval($_GET['page'] ?? 1));
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Get published audio sermons from database
$allSermons = getPublishedSermons(null, 'audio');

// Apply filters
$filteredSermons = $allSermons;
if ($categoryFilter) {
    $filteredSermons = array_filter($filteredSermons, function($sermon) use ($categoryFilter) {
        return !empty($sermon['category']) && strtolower($sermon['category']) === strtolower($categoryFilter);
    });
}
if ($speakerFilter) {
    $filteredSermons = array_filter($filteredSermons, function($sermon) use ($speakerFilter) {
        return !empty($sermon['speaker']) && strtolower($sermon['speaker']) === strtolower($speakerFilter);
    });
}

// Get unique categories and speakers for filter dropdowns
$categories = array_unique(array_filter(array_column($allSermons, 'category')));
$speakers = array_unique(array_filter(array_column($allSermons, 'speaker')));

// Paginate results
$totalSermons = count($filteredSermons);
$totalPages = ceil($totalSermons / $itemsPerPage);
$paginatedSermons = array_slice($filteredSermons, $offset, $itemsPerPage);

$settings = getSiteSettings();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Audio Sermons - <?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?></title>
  <meta name="description" content="Listen to audio sermons and teachings from CrossLife Mission Network.">
  <meta name="keywords" content="CrossLife, Audio Sermons, Teaching, Podcast, Pastor Lenhard Kyamba">

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
  
  <style>
    .audio-page .section-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem; color: #1a1a1a; }
    .audio-sermon-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      margin-bottom: 1.25rem;
      overflow: hidden;
    }
    .audio-sermon-card:hover { border-color: #c4b87b; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .audio-card-header {
      padding: 1rem 1.25rem;
      background: #f9fafb;
      border-bottom: 1px solid #e5e7eb;
    }
    .audio-card-header h2 { font-size: 1.15rem; font-weight: 600; margin: 0; color: #1a1a1a; }
    .audio-card-meta { font-size: 0.875rem; color: #6b7280; margin-top: 0.35rem; }
    .audio-card-meta span + span::before { content: " · "; color: #9ca3af; }
    .audio-card-body { padding: 1.25rem; }
    .audio-description { font-size: 0.9rem; color: #4b5563; line-height: 1.5; margin-bottom: 1rem; }
    .audio-player-wrap {
      background: #f3f4f6;
      border-radius: 8px;
      padding: 1rem;
    }
    .audio-player-wrap audio { width: 100%; height: 40px; }
    .audio-player-wrap .btn-dl { margin-top: 0.75rem; font-size: 0.875rem; }
    .filter-section {
      background: #f9fafb;
      padding: 1rem 1.25rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      border: 1px solid #e5e7eb;
    }
    .filter-section .form-label { font-size: 0.875rem; font-weight: 500; color: #374151; }
    .empty-state { text-align: center; padding: 3rem 1.5rem; color: #6b7280; }
    .empty-state i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; }
    .empty-state h3 { font-size: 1.1rem; color: #4b5563; margin-bottom: 0.5rem; }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header fixed-top">
    <div class="container-fluid container-xl position-relative">

      <div class="top-row d-flex align-items-center justify-content-between">
        <a href="index.php" class="logo d-flex align-items-center">
          <img src="assets/img/logo.png" alt="CrossLife Mission Network Logo">
          <h1 class="sitename"><?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?></h1>
        </a>

        <div class="d-flex align-items-center">
          <div class="search-toggle me-3">
            <button type="button" class="btn-search" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Search">
              <i class="bi bi-search"></i>
            </button>
          </div>
          <div class="social-links">
            <a href="https://www.facebook.com/crosslife_tz" class="facebook" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
            <a href="https://www.instagram.com/crosslife_tz" class="instagram" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
            <a href="https://www.youtube.com/@CrossLifeTV" class="youtube" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"></i></a>
          </div>
        </div>
      </div>

    </div>

    <div class="nav-wrap">
      <div class="container d-flex justify-content-center position-relative">
        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#about">About</a></li>
            <li><a href="index.php#features">Core Beliefs</a></li>
            <li><a href="index.php#leadership">Leadership</a></li>
            <li><a href="ministries.php">Ministries</a></li>
            <li><a href="sermons.php">Sermons</a></li>
            <li><a href="discipleship.html">Discipleship</a></li>
            <li><a href="events.html">Events</a></li>
            <li><a href="contacts.html">Contact</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
      </div>
    </div>

  </header>

  <main class="main">

    <!-- Page Header -->
    <section class="page-header section dark-background" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/_MG_5281.jpg') center/cover;">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h1 data-aos="fade-up">Audio Sermons</h1>
            <p data-aos="fade-up" data-aos-delay="100">Listen to our audio sermons and teachings to grow in your understanding of God's Word</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Audio Sermons Section -->
    <section id="audio-sermons" class="sermons section audio-page">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <!-- Filters -->
        <div class="filter-section">
          <form method="GET" action="audio-sermons.php" class="row g-3 align-items-end">
            <?php if (!empty($categories)): ?>
            <div class="col-md-4">
              <label for="categoryFilter" class="form-label">Filter by Category</label>
              <select class="form-select" id="categoryFilter" name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($speakers)): ?>
            <div class="col-md-4">
              <label for="speakerFilter" class="form-label">Filter by Speaker</label>
              <select class="form-select" id="speakerFilter" name="speaker" onchange="this.form.submit()">
                <option value="">All Speakers</option>
                <?php foreach ($speakers as $speaker): ?>
                  <option value="<?php echo htmlspecialchars($speaker); ?>" <?php echo $speakerFilter === $speaker ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($speaker); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
            
            <div class="col-md-4">
              <a href="audio-sermons.php" class="btn btn-outline-secondary w-100">Clear Filters</a>
            </div>
          </form>
        </div>

        <!-- Audio Sermons List -->
        <?php if (empty($paginatedSermons)): ?>
          <div class="empty-state">
            <i class="bi bi-headphones"></i>
            <h3>No audio sermons found</h3>
            <p>There are no audio sermons available<?php echo ($categoryFilter || $speakerFilter) ? ' for the selected filters' : ''; ?> at this time.</p>
            <p class="mt-3">Check back soon for new audio content!</p>
          </div>
        <?php else: ?>
          <div class="row">
            <?php foreach ($paginatedSermons as $sermon): ?>
              <div class="col-12" data-aos="fade-up" data-aos-delay="100">
                <div class="audio-sermon-card">
                  <div class="audio-card-header">
                    <h2><?php echo htmlspecialchars($sermon['title']); ?></h2>
                    <div class="audio-card-meta">
                      <?php if (!empty($sermon['speaker'])): ?><span><?php echo htmlspecialchars($sermon['speaker']); ?></span><?php endif; ?>
                      <?php if (!empty($sermon['sermon_date'])): ?><span><?php echo date('F j, Y', strtotime($sermon['sermon_date'])); ?></span><?php endif; ?>
                      <?php if (!empty($sermon['category'])): ?><span><?php echo htmlspecialchars($sermon['category']); ?></span><?php endif; ?>
                    </div>
                  </div>
                  <div class="audio-card-body">
                    <?php if (!empty($sermon['description'])): ?>
                      <p class="audio-description"><?php echo nl2br(htmlspecialchars($sermon['description'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($sermon['audio_url'])): ?>
                      <div class="audio-player-wrap">
                        <audio controls preload="metadata" class="w-100">
                          <source src="<?php echo htmlspecialchars($sermon['audio_url']); ?>" type="audio/mpeg">
                          <source src="<?php echo htmlspecialchars($sermon['audio_url']); ?>" type="audio/ogg">
                          <source src="<?php echo htmlspecialchars($sermon['audio_url']); ?>" type="audio/wav">
                          Your browser does not support the audio element.
                        </audio>
                        <a href="<?php echo htmlspecialchars($sermon['audio_url']); ?>" download class="btn btn-sm btn-outline-primary btn-dl">
                          <i class="bi bi-download me-1"></i>Download
                        </a>
                      </div>
                    <?php else: ?>
                      <p class="text-muted small mb-0">Audio not available.</p>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <nav aria-label="Audio sermons pagination" class="mt-5">
              <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $categoryFilter ? '&category=' . urlencode($categoryFilter) : ''; ?><?php echo $speakerFilter ? '&speaker=' . urlencode($speakerFilter) : ''; ?>">Previous</a>
                  </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                  <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $categoryFilter ? '&category=' . urlencode($categoryFilter) : ''; ?><?php echo $speakerFilter ? '&speaker=' . urlencode($speakerFilter) : ''; ?>"><?php echo $i; ?></a>
                  </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $categoryFilter ? '&category=' . urlencode($categoryFilter) : ''; ?><?php echo $speakerFilter ? '&speaker=' . urlencode($speakerFilter) : ''; ?>">Next</a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
          <?php endif; ?>
        <?php endif; ?>

      </div>

    </section><!-- /Audio Sermons Section -->

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
            <a href="index.php" class="logo d-flex align-items-center mb-4">
              <span class="sitename"><?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?></span>
            </a>
            <p class="mb-4">A non-denominational and inter-denominational Christian ministry in Dar es Salaam, Tanzania. We exist to manifest Sons of God who understand their identity in Christ and what Christ can accomplish through them.</p>

            <div class="newsletter-form">
              <h5>Stay Updated</h5>
              <form action="forms/newsletter.php" method="post" class="php-email-form">
                <div class="input-group">
                  <input type="email" name="email" class="form-control" placeholder="Enter your email" required="">
                  <button type="submit" class="btn-subscribe">
                    <i class="bi bi-send"></i>
                  </button>
                </div>
                <div class="loading">Loading</div>
                <div class="error-message"></div>
                <div class="sent-message">Thank you for subscribing!</div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-2 col-6">
          <div class="footer-links">
            <h4>Ministry</h4>
            <ul>
              <li><a href="index.php#about"><i class="bi bi-chevron-right"></i> About Us</a></li>
              <li><a href="index.php#features"><i class="bi bi-chevron-right"></i> Core Beliefs</a></li>
              <li><a href="contacts.html"><i class="bi bi-chevron-right"></i> Contact</a></li>
            </ul>
          </div>
        </div>

        <div class="col-lg-2 col-6">
          <div class="footer-links">
            <h4>Resources</h4>
            <ul>
              <li><a href="sermons.php"><i class="bi bi-chevron-right"></i> Video Sermons</a></li>
              <li><a href="audio-sermons.php"><i class="bi bi-chevron-right"></i> Audio Sermons</a></li>
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
              <p>© <span>Copyright</span> <strong class="px-1 sitename"><?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?></strong> <span>All Rights Reserved</span></p>
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
