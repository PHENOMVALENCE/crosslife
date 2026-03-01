<?php
/**
 * Sermons Page - CrossLife Mission Network
 * Displays published sermons (video + audio) from the database (managed in Admin → Sermons).
 * Structure & styling based on the original sermons.html; dynamic content pulled from the DB.
 */
require_once __DIR__ . '/admin/config/config.php';
require_once __DIR__ . '/includes/db-functions.php';

$settings = getSiteSettings();

// Fetch published sermons
$videoSermons = getPublishedSermons(null, 'video');
$audioSermons = getPublishedSermons(null, 'audio');
$allSermons   = getPublishedSermons();

/**
 * Safely format a sermon date, handling NULL and 0000-00-00
 */
function sermonFormatDate($date, $format = 'F j, Y') {
    if (empty($date) || $date === '0000-00-00') return '';
    $ts = strtotime($date);
    return $ts && $ts > 0 ? date($format, $ts) : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Sermons &amp; Teaching - <?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?></title>
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

  <!-- Sermon-specific styles -->
  <style>
    .sermon-card { border-radius: 12px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; }
    .sermon-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
    .sermon-card .card-body { padding: 1.25rem; }
    .sermon-card .sermon-meta { font-size: 0.85rem; color: #6c757d; }
    .sermon-card .sermon-meta i { margin-right: 4px; }
    .sermon-card .sermon-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; }
    .sermon-card .sermon-desc { font-size: 0.9rem; color: #555; }
    .sermon-video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; background: #000; border-radius: 8px; }
    .sermon-video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
    .sermon-audio-player { background: linear-gradient(135deg, #1a1a2e, #16213e); border-radius: 12px; padding: 1.5rem; color: #fff; }
    .sermon-audio-player audio { width: 100%; margin-top: 0.75rem; }
    .sermon-thumbnail { width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; }
    .sermon-type-badge { position: absolute; top: 12px; right: 12px; z-index: 2; }
    .sermon-category-badge { font-size: 0.75rem; }
    .no-sermons-message { padding: 3rem 1rem; text-align: center; }
    .no-sermons-message i { font-size: 3rem; color: #ccc; display: block; margin-bottom: 1rem; }
    .filter-tabs .nav-link { border-radius: 20px; padding: 0.4rem 1.2rem; margin: 0 0.25rem 0.5rem; font-size: 0.9rem; }
    .filter-tabs .nav-link.active { background-color: var(--accent-color, #e84545); border-color: var(--accent-color, #e84545); color: #fff; }
  </style>
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
            <li><a href="ministries.php">Ministries</a></li>
            <li><a href="sermons.php" class="active">Sermons</a></li>
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
    <section class="page-header section dark-background" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/videosermon.png') center/cover;">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h1 data-aos="fade-up">Sermons &amp; Teaching</h1>
            <p data-aos="fade-up" data-aos-delay="100">Access our video and audio sermons to grow in your understanding of God's Word</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Sermons Section -->
    <section id="sermons" class="sermons section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <!-- Sermon Type Cards (Video & Audio) -->
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
                <a href="https://www.youtube.com/@PastorLenhardKyamba" target="_blank" rel="noopener noreferrer" class="btn btn-outline mb-2">
                  <i class="bi bi-youtube me-2"></i>Pastor Lenhard Kyamba
                </a>
              </div>
              <div class="sermon-image mt-4">
                <img src="assets/img/videosermon.png" alt="Video Sermons" class="img-fluid rounded">
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
              <div class="sermon-links mt-3">
                <a href="contacts.html" class="btn btn-outline">
                  <i class="bi bi-envelope me-2"></i>Contact for Audio Access
                </a>
              </div>
              <div class="sermon-image mt-4">
                <img src="assets/img/podcast.png" alt="Audio Sermons" class="img-fluid rounded">
              </div>
            </div>
          </div>
        </div>

        <?php if (!empty($allSermons)): ?>
        <!-- Dynamic Sermons from Database -->
        <div class="container section-title" data-aos="fade-up">
          <h3 class="text-center mb-2">Recent Sermons</h3>
          <p class="text-center text-muted mb-4">Browse our latest video and audio sermon uploads</p>
        </div>

        <!-- Filter Tabs -->
        <div class="text-center mb-4" data-aos="fade-up">
          <ul class="nav nav-pills filter-tabs justify-content-center" id="sermonFilter" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all-sermons" type="button" role="tab" aria-selected="true">
                <i class="bi bi-grid me-1"></i>All (<?php echo count($allSermons); ?>)
              </button>
            </li>
            <?php if (!empty($videoSermons)): ?>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="video-tab" data-bs-toggle="pill" data-bs-target="#video-sermons" type="button" role="tab" aria-selected="false">
                <i class="bi bi-play-circle me-1"></i>Video (<?php echo count($videoSermons); ?>)
              </button>
            </li>
            <?php endif; ?>
            <?php if (!empty($audioSermons)): ?>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="audio-tab" data-bs-toggle="pill" data-bs-target="#audio-sermons" type="button" role="tab" aria-selected="false">
                <i class="bi bi-headphones me-1"></i>Audio (<?php echo count($audioSermons); ?>)
              </button>
            </li>
            <?php endif; ?>
          </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content mb-5" id="sermonTabContent">

          <!-- All Sermons -->
          <div class="tab-pane fade show active" id="all-sermons" role="tabpanel">
            <div class="row g-4">
              <?php
              $delay = 150;
              foreach ($allSermons as $sermon):
                $youtubeId = getYouTubeId($sermon['youtube_url'] ?? '');
                $isVideo = $sermon['sermon_type'] === 'video';
                $dateStr = sermonFormatDate($sermon['sermon_date'] ?? '');
                $thumbnail = '';
                if (!empty($sermon['thumbnail_url'])) {
                    $thumbnail = $sermon['thumbnail_url'];
                } elseif ($youtubeId) {
                    $thumbnail = 'https://img.youtube.com/vi/' . $youtubeId . '/hqdefault.jpg';
                }
              ?>
              <div class="col-lg-6 col-xl-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <div class="card sermon-card h-100 shadow-sm">
                  <?php if ($isVideo && $youtubeId): ?>
                    <div class="sermon-video-wrapper">
                      <iframe src="https://www.youtube-nocookie.com/embed/<?php echo htmlspecialchars($youtubeId); ?>"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen loading="lazy"
                        title="<?php echo htmlspecialchars($sermon['title']); ?>"></iframe>
                    </div>
                  <?php elseif ($thumbnail): ?>
                    <div style="position:relative;">
                      <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($sermon['title']); ?>" class="sermon-thumbnail">
                      <span class="sermon-type-badge badge bg-<?php echo $isVideo ? 'danger' : 'warning text-dark'; ?>">
                        <i class="bi bi-<?php echo $isVideo ? 'play-circle' : 'headphones'; ?> me-1"></i><?php echo ucfirst($sermon['sermon_type']); ?>
                      </span>
                    </div>
                  <?php else: ?>
                    <div style="position:relative; background: linear-gradient(135deg, #1a1a2e, #16213e); height: 180px; display: flex; align-items: center; justify-content: center; border-radius: 8px 8px 0 0;">
                      <i class="bi bi-<?php echo $isVideo ? 'camera-video' : 'headphones'; ?>" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                      <span class="sermon-type-badge badge bg-<?php echo $isVideo ? 'danger' : 'warning text-dark'; ?>">
                        <i class="bi bi-<?php echo $isVideo ? 'play-circle' : 'headphones'; ?> me-1"></i><?php echo ucfirst($sermon['sermon_type']); ?>
                      </span>
                    </div>
                  <?php endif; ?>

                  <div class="card-body">
                    <h5 class="sermon-title"><?php echo htmlspecialchars($sermon['title']); ?></h5>
                    <?php if (!empty($sermon['description'])): ?>
                      <p class="sermon-desc"><?php echo htmlspecialchars(mb_strimwidth($sermon['description'], 0, 120, '...')); ?></p>
                    <?php endif; ?>

                    <div class="sermon-meta d-flex flex-wrap gap-2 mt-2">
                      <?php if (!empty($sermon['speaker'])): ?>
                        <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($sermon['speaker']); ?></span>
                      <?php endif; ?>
                      <?php if ($dateStr): ?>
                        <span><i class="bi bi-calendar3"></i> <?php echo htmlspecialchars($dateStr); ?></span>
                      <?php endif; ?>
                      <?php if (!empty($sermon['category'])): ?>
                        <span class="badge bg-light text-dark sermon-category-badge"><?php echo htmlspecialchars($sermon['category']); ?></span>
                      <?php endif; ?>
                    </div>

                    <?php if (!$isVideo && !empty($sermon['audio_url'])): ?>
                      <div class="sermon-audio-player mt-3">
                        <audio controls preload="none" style="width:100%;">
                          <source src="<?php echo htmlspecialchars($sermon['audio_url']); ?>" type="audio/mpeg">
                          Your browser does not support the audio element.
                        </audio>
                      </div>
                    <?php endif; ?>

                    <?php if ($isVideo && !empty($sermon['youtube_url']) && !$youtubeId): ?>
                      <a href="<?php echo htmlspecialchars($sermon['youtube_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger mt-3">
                        <i class="bi bi-youtube me-1"></i>Watch on YouTube
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php
                $delay += 50;
              endforeach;
              ?>
            </div>
          </div>

          <!-- Video Sermons Only -->
          <?php if (!empty($videoSermons)): ?>
          <div class="tab-pane fade" id="video-sermons" role="tabpanel">
            <div class="row g-4">
              <?php
              $delay = 150;
              foreach ($videoSermons as $sermon):
                $youtubeId = getYouTubeId($sermon['youtube_url'] ?? '');
                $dateStr = sermonFormatDate($sermon['sermon_date'] ?? '');
              ?>
              <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <div class="card sermon-card h-100 shadow-sm">
                  <?php if ($youtubeId): ?>
                    <div class="sermon-video-wrapper">
                      <iframe src="https://www.youtube-nocookie.com/embed/<?php echo htmlspecialchars($youtubeId); ?>"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen loading="lazy"
                        title="<?php echo htmlspecialchars($sermon['title']); ?>"></iframe>
                    </div>
                  <?php endif; ?>
                  <div class="card-body">
                    <h5 class="sermon-title"><?php echo htmlspecialchars($sermon['title']); ?></h5>
                    <?php if (!empty($sermon['description'])): ?>
                      <p class="sermon-desc"><?php echo htmlspecialchars(mb_strimwidth($sermon['description'], 0, 150, '...')); ?></p>
                    <?php endif; ?>
                    <div class="sermon-meta d-flex flex-wrap gap-2 mt-2">
                      <?php if (!empty($sermon['speaker'])): ?>
                        <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($sermon['speaker']); ?></span>
                      <?php endif; ?>
                      <?php if ($dateStr): ?>
                        <span><i class="bi bi-calendar3"></i> <?php echo htmlspecialchars($dateStr); ?></span>
                      <?php endif; ?>
                      <?php if (!empty($sermon['category'])): ?>
                        <span class="badge bg-light text-dark sermon-category-badge"><?php echo htmlspecialchars($sermon['category']); ?></span>
                      <?php endif; ?>
                    </div>
                    <?php if (!empty($sermon['youtube_url']) && !$youtubeId): ?>
                      <a href="<?php echo htmlspecialchars($sermon['youtube_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger mt-3">
                        <i class="bi bi-youtube me-1"></i>Watch on YouTube
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php $delay += 50; endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Audio Sermons Only -->
          <?php if (!empty($audioSermons)): ?>
          <div class="tab-pane fade" id="audio-sermons" role="tabpanel">
            <div class="row g-4">
              <?php
              $delay = 150;
              foreach ($audioSermons as $sermon):
                $dateStr = sermonFormatDate($sermon['sermon_date'] ?? '');
              ?>
              <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <div class="sermon-audio-player">
                  <h5 class="mb-1"><?php echo htmlspecialchars($sermon['title']); ?></h5>
                  <div class="d-flex flex-wrap gap-2 mb-2" style="font-size:0.85rem; opacity:0.8;">
                    <?php if (!empty($sermon['speaker'])): ?>
                      <span><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($sermon['speaker']); ?></span>
                    <?php endif; ?>
                    <?php if ($dateStr): ?>
                      <span><i class="bi bi-calendar3 me-1"></i><?php echo htmlspecialchars($dateStr); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($sermon['category'])): ?>
                      <span class="badge bg-light text-dark sermon-category-badge"><?php echo htmlspecialchars($sermon['category']); ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($sermon['description'])): ?>
                    <p style="font-size:0.9rem; opacity:0.85;" class="mb-2"><?php echo htmlspecialchars(mb_strimwidth($sermon['description'], 0, 150, '...')); ?></p>
                  <?php endif; ?>
                  <?php if (!empty($sermon['audio_url'])): ?>
                    <audio controls preload="none" style="width:100%;">
                      <source src="<?php echo htmlspecialchars($sermon['audio_url']); ?>" type="audio/mpeg">
                      Your browser does not support the audio element.
                    </audio>
                  <?php else: ?>
                    <p class="small mt-2 mb-0" style="opacity:0.6;">Audio file not available. <a href="contacts.html" class="text-light">Contact us</a> for access.</p>
                  <?php endif; ?>
                </div>
              </div>
              <?php $delay += 50; endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

        </div><!-- /.tab-content -->
        <?php endif; ?>

        <!-- Sermon Categories -->
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

        <!-- Stay Connected -->
        <div class="row mt-5">
          <div class="col-lg-12 text-center" data-aos="fade-up" data-aos-delay="450">
            <h3 class="mb-3">Stay Connected</h3>
            <p class="lead">Subscribe to our YouTube channels to receive notifications when new sermons are uploaded.</p>
            <div class="mt-4">
              <a href="https://www.youtube.com/@CrossLifeTV" target="_blank" rel="noopener noreferrer" class="btn btn-primary me-2">
                <i class="bi bi-youtube me-2"></i>Subscribe to CrossLife TV
              </a>
              <a href="https://www.youtube.com/@PastorLenhardKyamba" target="_blank" rel="noopener noreferrer" class="btn btn-outline">
                <i class="bi bi-youtube me-2"></i>Subscribe to Pastor Lenhard Kyamba
              </a>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Sermons Section -->

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
              <p>&copy; <span>Copyright</span> <strong class="px-1 sitename">CrossLife Mission Network</strong> <span>All Rights Reserved</span></p>
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
