<?php
/**
 * Events Page - CrossLife Mission Network
 * Display upcoming and past events from database
 */
require_once 'includes/db-functions.php';

$events = getAllEvents();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Events - CrossLife Mission Network</title>
  <meta name="description" content="Join us for upcoming services, programs, and special events at CrossLife Mission Network.">
  <meta name="keywords" content="CrossLife, Events, Calendar, Services, Bible Study, Prayer Meetings, Church Events">

  <!-- Favicons -->
  <link href="assets/img/logo.jpeg" rel="icon">
  <link href="assets/img/logo.jpeg" rel="apple-touch-icon">

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
          <img src="assets/img/logo.jpeg" alt="CrossLife Mission Network Logo">
          <h1 class="sitename">CrossLife Mission Network</h1>
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
            <li><a href="index.html">Home</a></li>
            <li><a href="index.html#about">About</a></li>
            <li><a href="index.html#features">Core Beliefs</a></li>
            <li><a href="index.html#leadership">Leadership</a></li>
            <li><a href="ministries.html">Ministries</a></li>
            <li><a href="sermons.html">Sermons</a></li>
            <li><a href="discipleship.html">Discipleship</a></li>
            <li><a href="events.php" class="active">Events</a></li>
            <li><a href="contacts.html">Contact</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
      </div>
    </div>

  </header>

  <main class="main">

    <!-- Page Header -->
    <section class="page-header section dark-background" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/_MG_5282.jpg') center/cover;">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h1 data-aos="fade-up">Upcoming Events</h1>
            <p data-aos="fade-up" data-aos-delay="100">Join us for our upcoming services, programs, and special events</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Events Calendar Section -->
    <section id="events" class="events section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4">
          <?php if (empty($events)): ?>
            <div class="col-12 text-center">
              <p class="lead">No events scheduled at the moment. Please check back soon!</p>
            </div>
          <?php else: ?>
            <?php foreach ($events as $index => $event): ?>
              <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo 150 + ($index * 50); ?>">
                <div class="event-card">
                  <div class="event-date">
                    <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                    <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                  </div>
                  <div class="event-content">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <?php if ($event['event_time']): ?>
                      <p class="event-time">
                        <i class="bi bi-clock me-2"></i><?php echo date('g:i A', strtotime($event['event_time'])); ?>
                        <?php if ($event['end_date'] && $event['end_date'] != $event['event_date']): ?>
                          - <?php echo date('M d, Y', strtotime($event['end_date'])); ?>
                        <?php endif; ?>
                      </p>
                    <?php endif; ?>
                    <?php if ($event['location']): ?>
                      <p class="event-location"><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($event['location']); ?></p>
                    <?php endif; ?>
                    <?php if ($event['event_type']): ?>
                      <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($event['event_type']); ?></span>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                    <?php if ($event['image_url']): ?>
                      <div class="event-image mt-3">
                        <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="img-fluid rounded">
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="row mt-5">
          <div class="col-lg-12 text-center" data-aos="fade-up" data-aos-delay="350">
            <h3 class="mb-3">Stay Connected</h3>
            <p class="lead mb-4">For more information about specific events, please contact us or check our social media pages for updates.</p>
            <a href="contacts.html" class="btn btn-primary me-2">Contact Us for Event Details</a>
            <a href="https://www.facebook.com/crosslife_tz" target="_blank" rel="noopener noreferrer" class="btn btn-outline">
              <i class="bi bi-facebook me-2"></i>Follow on Facebook
            </a>
          </div>
        </div>

      </div>

    </section><!-- /Events Calendar Section -->

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
              <span class="sitename">CrossLife Mission Network</span>
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
              <li><a href="index.html#about"><i class="bi bi-chevron-right"></i> About Us</a></li>
              <li><a href="index.html#features"><i class="bi bi-chevron-right"></i> Core Beliefs</a></li>
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

  <!-- Enhanced Global Search Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchForm = document.getElementById('globalSearchForm');
      const searchInput = document.getElementById('globalSearchInput');
      const searchResults = document.getElementById('searchResults');
      const searchResultsList = document.getElementById('searchResultsList');
      let searchTimeout;

      if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
          e.preventDefault();
          performSearch();
        });

        searchInput.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          if (this.value.length > 2) {
            searchTimeout = setTimeout(() => performSearch(), 300);
          } else {
            searchResults.style.display = 'none';
          }
        });
      }

      function performSearch() {
        const query = searchInput.value.trim();
        if (query.length < 2) {
          searchResults.style.display = 'none';
          return;
        }

        searchResultsList.innerHTML = '<li class="text-muted"><i class="bi bi-hourglass-split me-2"></i>Searching...</li>';
        searchResults.style.display = 'block';

        fetch(`api/search.php?q=${encodeURIComponent(query)}`)
          .then(response => response.json())
          .then(data => {
            if (data.success && data.results) {
              displayResults(data.results);
            } else {
              searchResultsList.innerHTML = '<li class="text-muted">No results found. Try different keywords.</li>';
            }
          })
          .catch(error => {
            console.error('Search error:', error);
            searchResultsList.innerHTML = '<li class="text-danger"><i class="bi bi-exclamation-circle me-2"></i>Search error. Please try again.</li>';
          });
      }

      function displayResults(results) {
        searchResultsList.innerHTML = '';
        
        if (results.length === 0) {
          searchResultsList.innerHTML = '<li class="text-muted">No results found. Try different keywords.</li>';
          return;
        }

        results.forEach(result => {
          const li = document.createElement('li');
          li.className = 'mb-3 pb-2 border-bottom';
          
          const link = document.createElement('a');
          link.href = result.url;
          link.className = 'search-result-link text-decoration-none d-block';
          link.setAttribute('data-bs-dismiss', 'modal');
          
          // Add click handler for smooth scrolling to anchors
          link.addEventListener('click', function(e) {
            const url = new URL(result.url, window.location.origin);
            const currentPage = window.location.pathname.split('/').pop() || 'index.php';
            const targetPage = url.pathname.split('/').pop() || 'index.php';
            
            // If same page and has hash, prevent default and smooth scroll
            if (currentPage === targetPage && url.hash) {
              e.preventDefault();
              const target = document.querySelector(url.hash);
              if (target) {
                // Close modal first
                const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
                if (modal) modal.hide();
                
                // Then smooth scroll to target
                setTimeout(() => {
                  target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                  // Add temporary highlight
                  target.style.transition = 'background-color 0.3s';
                  target.style.backgroundColor = 'rgba(200, 87, 22, 0.1)';
                  setTimeout(() => {
                    target.style.backgroundColor = '';
                  }, 2000);
                }, 300);
              }
            }
            // Otherwise let the link navigate normally
          });
          
          link.innerHTML = `
            <div class="d-flex align-items-start">
              <div class="me-3">
                <i class="bi bi-${result.icon} fs-5" style="color: var(--accent-color);"></i>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                  <strong class="d-block">${result.title}</strong>
                  <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">${result.type}</span>
                </div>
                <small class="text-muted d-block mt-1">${result.description}</small>
              </div>
            </div>
          `;
          
          li.appendChild(link);
          searchResultsList.appendChild(li);
        });

        searchResults.style.display = 'block';
      }
    });
  </script>

</body>

</html>

