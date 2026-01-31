<?php
/**
 * Sermons Page - CrossLife Mission Network
 * Display video and audio sermons - YouTube videos in modal
 */
require_once 'includes/db-functions.php';
require_once 'includes/youtube-functions.php';

// AJAX endpoint to fetch videos
if (isset($_GET['ajax_fetch_videos']) || isset($_GET['ajax_videos'])) {
    header('Content-Type: application/json');
    try {
        $videos = fetchYouTubeChannelVideos('PastorLenhardKyamba', 50);
        echo json_encode(['success' => true, 'videos' => $videos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'videos' => []]);
    }
    exit;
}

$settings = getSiteSettings();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Sermons & Teaching - <?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?></title>
  <meta name="description" content="Access video and audio sermons from CrossLife Mission Network to grow in your understanding of God's Word.">
  <meta name="keywords" content="CrossLife, Sermons, Teaching, Video Sermons, Audio Sermons, YouTube, Pastor Lenhard Kyamba">

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
    /* Video Modal Styles */
    .video-modal-content {
      max-height: 80vh;
      overflow-y: auto;
    }
    .video-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 1.5rem;
    }
    .video-item {
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      background: white;
    }
    .video-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    .video-thumbnail {
      position: relative;
      width: 100%;
      padding-top: 56.25%;
      background: #000;
      overflow: hidden;
    }
    .video-thumbnail img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .video-thumbnail .play-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(0,0,0,0.7);
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 24px;
      transition: all 0.3s ease;
    }
    .video-item:hover .play-overlay {
      background: rgba(255,0,0,0.9);
      transform: translate(-50%, -50%) scale(1.1);
    }
    .video-info {
      padding: 1rem;
    }
    .video-title {
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #333;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .video-meta {
      font-size: 0.8rem;
      color: #666;
    }
    .video-filter-section {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    .youtube-embed {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: 0;
    }
    /* View Audio Sermons: always clickable and responsive */
    .view-audio-wrap {
      position: relative;
      z-index: 2;
    }
    .view-audio-btn {
      position: relative;
      z-index: 2;
      pointer-events: auto !important;
      cursor: pointer !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 0.5rem 1.25rem;
    }
    @media (max-width: 768px) {
      .view-audio-btn {
        width: 100%;
        min-height: 48px;
      }
    }
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
            <li><a href="sermons.php" class="active">Sermons</a></li>
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
    <section class="page-header section dark-background" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/_MG_4902.jpg') center/cover;">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h1 data-aos="fade-up">Sermons & Teaching</h1>
            <p data-aos="fade-up" data-aos-delay="100">Access our video and audio sermons to grow in your understanding of God's Word</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Sermons Section -->
    <section id="sermons" class="sermons section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4 mb-5">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="150">
            <div class="sermon-type-card h-100">
              <div class="sermon-icon">
                <i class="bi bi-play-circle"></i>
              </div>
              <h3>Video Sermons</h3>
              <p>Watch our video sermons on YouTube. Subscribe to CrossLife TV and Pastor Lenhard Kyamba's channel for regular updates.</p>
              <div class="sermon-links mt-3">
                <a href="https://www.youtube.com/@CrossLifeTV" target="_blank" rel="noopener noreferrer" class="btn btn-primary me-2 mb-2">
                  <i class="bi bi-youtube me-2"></i>CrossLife TV
                </a>
                <button type="button" class="btn btn-outline mb-2" id="pastorVideosBtn" onclick="if(typeof window.openPastorVideosModal === 'function') { window.openPastorVideosModal(); } else { alert('Function not loaded yet. Please wait a moment and try again.'); } return false;" style="cursor: pointer; position: relative; z-index: 10; pointer-events: auto !important;">
                  <i class="bi bi-youtube me-2"></i>Pastor Lenhard Kyamba
                </button>
                <script>
                  // Test button immediately
                  console.log('Button HTML loaded');
                  setTimeout(function() {
                    const btn = document.getElementById('pastorVideosBtn');
                    if (btn) {
                      console.log('Button found in DOM:', btn);
                      btn.style.pointerEvents = 'auto';
                      btn.style.cursor = 'pointer';
                    } else {
                      console.error('Button NOT found!');
                    }
                  }, 100);
                </script>
              </div>
              <div class="sermon-image mt-4">
                <img src="assets/img/_MG_5021.jpg" alt="Video Sermons" class="img-fluid rounded">
              </div>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="sermon-type-card h-100">
              <div class="sermon-icon">
                <i class="bi bi-headphones"></i>
              </div>
              <h3>Audio Sermons</h3>
              <p>Listen to our audio sermons and teachings. Stream or download audio content to listen on the go.</p>
              <div class="sermon-links mt-3 view-audio-wrap">
                <a href="audio-sermons.php" class="btn btn-outline view-audio-btn" role="button">
                  <i class="bi bi-headphones me-2"></i>View Audio Sermons
                </a>
              </div>
              <div class="sermon-image mt-4">
                <img src="assets/img/_MG_5281.jpg" alt="Audio Sermons" class="img-fluid rounded">
              </div>
            </div>
          </div>
        </div>

        <div class="sermon-categories" data-aos="fade-up" data-aos-delay="250">
          <div class="container section-title">
            <h3 class="text-center mb-4">Sermon Categories</h3>
          </div>
          <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
              <div class="category-card text-center p-4">
                <i class="bi bi-collection display-4 mb-3 text-primary"></i>
                <h4>Sermon Series</h4>
                <p>Explore our teaching series covering various topics including the Gospel of the Cross, Sonship, the Kingdom of God, and Immortality.</p>
              </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="350">
              <div class="category-card text-center p-4">
                <i class="bi bi-person display-4 mb-3 text-primary"></i>
                <h4>By Speaker</h4>
                <p>Browse sermons by different speakers including Senior Pastor Lenhard Kyamba and other ministry leaders.</p>
              </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
              <div class="category-card text-center p-4">
                <i class="bi bi-calendar display-4 mb-3 text-primary"></i>
                <h4>By Date</h4>
                <p>Access sermons organized by date to find recent teachings or explore our archive of past messages.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-5">
          <div class="col-lg-12 text-center" data-aos="fade-up" data-aos-delay="450">
            <h3 class="mb-3">Stay Connected</h3>
            <p class="lead">Subscribe to our YouTube channels to receive notifications when new sermons are uploaded.</p>
            <div class="mt-4">
              <a href="https://www.youtube.com/@CrossLifeTV" target="_blank" rel="noopener noreferrer" class="btn btn-primary me-2">
                <i class="bi bi-youtube me-2"></i>Subscribe to CrossLife TV
              </a>
              <button type="button" class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#pastorVideosModal">
                <i class="bi bi-youtube me-2"></i>Subscribe to Pastor Lenhard Kyamba
              </button>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Sermons Section -->

  </main>

  <!-- Pastor Lenhard Kyamba Videos Modal -->
  <div class="modal fade" id="pastorVideosModal" tabindex="-1" aria-labelledby="pastorVideosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pastorVideosModalLabel">
            Pastor Lenhard Kyamba - Video Sermons
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body video-modal-content">
          <div id="videosLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading videos from YouTube...</p>
          </div>
          
          <div id="videosContent" style="display: none;">
            <!-- Filter Section -->
            <div class="video-filter-section">
              <div class="row g-3 align-items-end">
                <div class="col-md-6">
                  <label for="videoSearch" class="form-label">Search Videos</label>
                  <input type="text" class="form-control" id="videoSearch" placeholder="Search by title...">
                </div>
                <div class="col-md-6 text-end">
                  <a href="https://www.youtube.com/@PastorLenhardKyamba" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger">
                    <i class="bi bi-youtube me-2"></i>View on YouTube
                  </a>
                </div>
              </div>
            </div>
            
            <!-- Videos Grid -->
            <div id="videosGrid" class="video-grid"></div>
            
            <!-- Empty State -->
            <div id="noVideos" class="text-center py-5" style="display: none;">
              <i class="bi bi-inbox display-1 text-muted"></i>
              <p class="mt-3 text-muted">No videos found. Please check your internet connection.</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Video Player Modal -->
  <div class="modal fade" id="videoPlayerModal" tabindex="-1" aria-labelledby="videoPlayerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="videoPlayerModalLabel">Video Sermon</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
            <iframe id="youtubePlayerFrame" class="youtube-embed" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>

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

  <!-- YouTube Videos Script -->
  <script>
    // Global variables - make them window properties
    window.allVideos = [];
    window.modalBackdrop = null;
    
    // Make sure function is available immediately
    window.openPastorVideosModal = function() {
      console.log('openPastorVideosModal called!');
      const modal = document.getElementById('pastorVideosModal');
      if (!modal) {
        alert('Modal not found! Check console for errors.');
        console.error('Modal element not found!');
        return;
      }
      
      // Show modal
      modal.style.display = 'block';
      modal.classList.add('show');
      document.body.classList.add('modal-open');
      document.body.style.overflow = 'hidden';
      document.body.style.paddingRight = '0px';
      
      // Add backdrop
      if (!window.modalBackdrop) {
        window.modalBackdrop = document.createElement('div');
        window.modalBackdrop.className = 'modal-backdrop fade show';
        window.modalBackdrop.id = 'modalBackdrop';
        window.modalBackdrop.style.position = 'fixed';
        window.modalBackdrop.style.top = '0';
        window.modalBackdrop.style.left = '0';
        window.modalBackdrop.style.width = '100%';
        window.modalBackdrop.style.height = '100%';
        window.modalBackdrop.style.backgroundColor = 'rgba(0,0,0,0.5)';
        window.modalBackdrop.style.zIndex = '1040';
        document.body.appendChild(window.modalBackdrop);
      }
      
      // Set modal z-index
      modal.style.zIndex = '1050';
      modal.style.display = 'block';
      
      // Setup close handlers
      setupModalCloseHandlers(modal);
      
      // Load videos if not loaded
      if (window.allVideos.length === 0) {
        loadVideos();
      } else {
        displayVideos(window.allVideos);
      }
      
      // Setup search filter after modal is shown
      setTimeout(function() {
        if (typeof setupVideoSearch === 'function') {
          setupVideoSearch();
        }
      }, 100);
    };
    
    function setupModalCloseHandlers(modal) {
      function closeModal() {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        if (window.modalBackdrop) {
          window.modalBackdrop.remove();
          window.modalBackdrop = null;
        }
      }
      
      // Close button in header
      const closeBtn = modal.querySelector('.btn-close');
      if (closeBtn) {
        closeBtn.onclick = closeModal;
      }
      
      // Close button in footer
      const footerClose = modal.querySelector('.modal-footer button');
      if (footerClose) {
        footerClose.onclick = closeModal;
      }
      
      // Close on backdrop click
      if (window.modalBackdrop) {
        window.modalBackdrop.onclick = closeModal;
      }
      
      // Close on Escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
          closeModal();
        }
      });
    }
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM loaded, initializing video modal...');
      
      // Get modal element
      const modal = document.getElementById('pastorVideosModal');
      if (!modal) {
        console.error('Modal element not found! Check if modal HTML exists.');
        return;
      }
      
      console.log('Modal element found:', modal);
      
      // Get button
      const btn = document.getElementById('pastorVideosBtn');
      if (btn) {
        console.log('Button found:', btn);
        // Button already has onclick, but add event listener as backup
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          window.openPastorVideosModal();
        });
      } else {
        console.error('Button not found!');
      }
      
      // Videos will load when button is clicked (handled in openPastorVideosModal function)
    });
    
    // Load videos function (global so it can be called from openPastorVideosModal)
    function loadVideos() {
        const loadingEl = document.getElementById('videosLoading');
        const contentEl = document.getElementById('videosContent');
        const noVideosEl = document.getElementById('noVideos');
        
        if (loadingEl) loadingEl.style.display = 'block';
        if (contentEl) contentEl.style.display = 'none';
        if (noVideosEl) noVideosEl.style.display = 'none';
        
        // Fetch via AJAX endpoint
        fetch('sermons.php?ajax_fetch_videos=1')
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            console.log('Videos data:', data);
            if (data.success && data.videos && data.videos.length > 0) {
              allVideos = data.videos;
              displayVideos(allVideos);
            } else {
              throw new Error(data.error || 'No videos found');
            }
          })
          .catch(error => {
            console.error('Error fetching videos:', error);
            if (loadingEl) loadingEl.style.display = 'none';
            if (noVideosEl) noVideosEl.style.display = 'block';
          });
      }
    
    // Display videos in grid (global function)
    function displayVideos(videos) {
        const loadingEl = document.getElementById('videosLoading');
        const contentEl = document.getElementById('videosContent');
        const grid = document.getElementById('videosGrid');
        const noVideosEl = document.getElementById('noVideos');
        
        if (loadingEl) loadingEl.style.display = 'none';
        if (contentEl) contentEl.style.display = 'block';
        if (grid) grid.innerHTML = '';
        
        if (videos.length === 0) {
          if (noVideosEl) noVideosEl.style.display = 'block';
          return;
        }
        
        videos.forEach(video => {
          const videoItem = createVideoCard(video);
          if (grid) grid.appendChild(videoItem);
        });
      }
      
    // Create video card element (global function)
    function createVideoCard(video) {
        const div = document.createElement('div');
        div.className = 'video-item';
        
        const videoId = video.video_id || video.videoId || '';
        const title = video.title || 'Untitled Video';
        const thumbnail = video.thumbnail || `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
        const published = video.published || video.publishedTime || '';
        const views = video.views || '';
        
        div.onclick = () => playVideo(videoId, title);
        
        div.innerHTML = `
          <div class="video-thumbnail">
            <img src="${thumbnail}" alt="${title}" loading="lazy">
            <div class="play-overlay">
              <i class="bi bi-play-fill"></i>
            </div>
          </div>
          <div class="video-info">
            <div class="video-title">${escapeHtml(title)}</div>
            <div class="video-meta">
              ${published ? '<i class="bi bi-clock"></i> ' + escapeHtml(published) : ''}
              ${views ? (published ? ' • ' : '') + '<i class="bi bi-eye"></i> ' + escapeHtml(views) : ''}
            </div>
          </div>
        `;
        
        return div;
      }
      
    // Escape HTML to prevent XSS (global function)
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }
      
    // Play video in modal (global function)
    function playVideo(videoId, title) {
        const playerModal = document.getElementById('videoPlayerModal');
        const frame = document.getElementById('youtubePlayerFrame');
        const modalTitle = document.getElementById('videoPlayerModalLabel');
        
        if (!playerModal || !frame) {
          console.error('Video player modal not found');
          return;
        }
        
        if (modalTitle) modalTitle.textContent = title;
        frame.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1';
        
        const bsModal = new bootstrap.Modal(playerModal);
        bsModal.show();
        
        // Clean up when modal is hidden
        playerModal.addEventListener('hidden.bs.modal', function() {
          frame.src = '';
        }, { once: true });
      }
    
    // Setup search filter when modal is opened (global function)
    function setupVideoSearch() {
      const searchInput = document.getElementById('videoSearch');
      if (searchInput) {
        searchInput.oninput = function(e) {
          const searchTerm = e.target.value.toLowerCase();
          const filtered = window.allVideos.filter(video => 
            (video.title || '').toLowerCase().includes(searchTerm)
          );
          displayVideos(filtered);
        };
      }
    }
  </script>

</body>

</html>
