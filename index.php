<?php
/**
 * Home Page - CrossLife Mission Network
 * Dynamic content from database
 */
require_once 'includes/db-functions.php';

$settings = getSiteSettings();
$leadership = getActiveLeadership();
$upcomingEvents = getUpcomingEvents(4);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($settings['site_name'] ?? 'CrossLife Mission Network'); ?> - Manifesting Sons of God</title>
  <meta name="description" content="CrossLife Mission Network (CMN) is a non-denominational Christian ministry in Dar es Salaam, Tanzania, committed to manifesting Sons of God who understand their identity in Christ.">
  <meta name="keywords" content="CrossLife, Mission Network, Christian Ministry, Tanzania, Sons of God, Pastor Lenhard Kyamba">

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

  <!-- =======================================================
  * Template Name: LeadPage
  * Template URL: https://bootstrapmade.com/leadpage-bootstrap-landing-page-template/
  * Updated: Aug 12 2025 with Bootstrap v5.3.7
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <header id="header" class="header fixed-top">
    <div class="container-fluid container-xl position-relative">

      <div class="top-row d-flex align-items-center justify-content-between">
        <a href="index.php" class="logo d-flex align-items-center">
          <img src="assets/img/logo.jpeg" alt="CrossLife Mission Network Logo">
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
            <li><a href="index.php#hero" class="active">Home</a></li>
            <li><a href="index.php#about">About</a></li>
            <li><a href="index.php#features">Core Beliefs</a></li>
            <li><a href="index.php#leadership">Leadership</a></li>
            <li><a href="ministries.html">Ministries</a></li>
            <li><a href="sermons.html">Sermons</a></li>
            <li><a href="discipleship.html">Discipleship</a></li>
            <li><a href="events.php">Events</a></li>
            <li><a href="contacts.html">Contact</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
      </div>
    </div>

  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

      <div class="slideshow-background">
        <div class="slideshow-container">
          <div class="slide active">
            <img src="assets/img/slideshow/_MG_4997.jpg" alt="CrossLife Mission Network" class="slide-image">
          </div>
          <div class="slide">
            <img src="assets/img/slideshow/_MG_5080.jpg" alt="CrossLife Mission Network" class="slide-image">
          </div>
          <div class="slide">
            <img src="assets/img/slideshow/_MG_5217.jpg" alt="CrossLife Mission Network" class="slide-image">
          </div>
          <div class="slide">
            <img src="assets/img/slideshow/_MG_5243.jpg" alt="CrossLife Mission Network" class="slide-image">
          </div>
          <div class="slide">
            <img src="assets/img/slideshow/_MG_5266.jpg" alt="CrossLife Mission Network" class="slide-image">
          </div>
        </div>
        <div class="slideshow-overlay"></div>
        <div class="slideshow-controls">
          <button class="slideshow-prev" aria-label="Previous slide">
            <i class="bi bi-chevron-left"></i>
          </button>
          <button class="slideshow-next" aria-label="Next slide">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
        <div class="slideshow-indicators">
          <span class="indicator active" data-slide="0"></span>
          <span class="indicator" data-slide="1"></span>
          <span class="indicator" data-slide="2"></span>
          <span class="indicator" data-slide="3"></span>
          <span class="indicator" data-slide="4"></span>
        </div>
      </div>

      <div class="hero-content">

        <div class="container position-relative">
          <div class="row justify-content-center text-center">
            <div class="col-lg-8">
              <h1 data-aos="fade-up" data-aos-delay="100">Manifesting Sons of God</h1>
              <p data-aos="fade-up" data-aos-delay="200">We live in Zion, the realm of Christ. Eternal Life is our present reality. A community of Life, Love, Sonship, and Prayer, welcoming people from diverse backgrounds, ages, and walks of life.</p>
              <div class="hero-buttons" data-aos="fade-up" data-aos-delay="300">
                <a href="#about" class="btn btn-primary">Learn More</a>
                <a href="contacts.php" class="btn btn-outline-light">Contact Us</a>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span class="subtitle">About</span>
        <h2>About CrossLife Mission Network</h2>
        <p>CrossLife Mission Network (CMN) is a non-denominational and inter-denominational Christian ministry based in Dar es Salaam, Tanzania. We exist as a community of Life, Love, Sonship, and Prayer, welcoming people from diverse backgrounds, ages, and walks of life.</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center">
          <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
            <div class="content">
              <h2>Our Mandate</h2>
              <p class="lead">CMN exists to manifest Sons of God who understand their identity in Christ and what Christ can accomplish through them.</p>
              <p>We emphasize experiential knowledge of God, enabling believers to walk in New Creation realities and Eternal Ordinations for the work of the Ministry of Christ. CMN also seeks to establish a global network of manifested Sons of God unified by one message, one mandate, and one mission.</p>
              
              <h3 class="mt-4">Our Vision</h3>
              <p>To be a global network of awakened Sons of God living in New Creation realities, manifesting Christ through the preaching of the Gospel of the Cross, the Message of Sonship, the Gospel of the Kingdom of God, and the Gospel of Immortality.</p>
              
              <h3 class="mt-4">Our Mission</h3>
              <p>To reach the global community by showing the Way, revealing the Truth, and sharing Life through Christ, equipping believers to live from an eternal perspective and fulfill the mandate of Christ on earth.</p>
              
              <div class="cta-wrapper mt-4">
                <a href="contacts.php" class="btn-link">
                  Connect with us
                  <i class="bi bi-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
            <div class="image-wrapper">
              <img src="assets/img/_MG_5282.jpg" alt="CrossLife Mission Network" class="img-fluid">
              <div class="floating-element">
                <div class="quote-content">
                  <blockquote>
                    "We live in Zion, the realm of Christ. Eternal Life is our present reality."
                  </blockquote>
                  <cite>— CrossLife Motto</cite>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Statement of Faith Section -->
    <section id="statement-of-faith" class="statement-of-faith section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span class="subtitle">Our Faith</span>
        <h2>Statement of Faith</h2>
        <p>Our foundation is built upon the truth of God's Word</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="150">
            <div class="faith-item">
              <h4><i class="bi bi-cross me-2"></i>The Scriptures</h4>
              <p>We believe the Bible is the inspired, infallible, and authoritative Word of God, providing the foundation for our faith and practice.</p>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="faith-item">
              <h4><i class="bi bi-heart me-2"></i>The Godhead</h4>
              <p>We believe in the fullness of the Godhead: God the Father, God the Son (Jesus Christ), and God the Holy Spirit - three in one.</p>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="250">
            <div class="faith-item">
              <h4><i class="bi bi-person-check me-2"></i>Jesus Christ</h4>
              <p>We believe Jesus Christ is the Son of God, fully God and fully man, the Savior of the world from sin, and the Way, the Truth, and the Life.</p>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
            <div class="faith-item">
              <h4><i class="bi bi-wind me-2"></i>The Holy Spirit</h4>
              <p>We believe the Holy Spirit is the executive agent of God and the helper of believers, empowering us for ministry and leading us into all truth.</p>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="350">
            <div class="faith-item">
              <h4><i class="bi bi-people me-2"></i>Humanity & Salvation</h4>
              <p>We believe humanity is created in the image and likeness of God, and through faith in Christ, we become Sons of God (John 1:12-13).</p>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="400">
            <div class="faith-item">
              <h4><i class="bi bi-church me-2"></i>The Church</h4>
              <p>We believe the Church is commissioned to preach the Gospel and manifest Sons of God who understand their identity in Christ.</p>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="450">
            <div class="faith-item">
              <h4><i class="bi bi-music-note-beamed me-2"></i>Worship</h4>
              <p>We believe worship is central to the life of CrossLife, as we live in Zion, the realm of Christ, where Eternal Life is our present reality.</p>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="500">
            <div class="faith-item">
              <h4><i class="bi bi-arrow-return-left me-2"></i>Christ's Return</h4>
              <p>We believe in the return of Jesus Christ and the fulfillment of God's eternal purposes through His kingdom.</p>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Statement of Faith Section -->

    <!-- Features Section -->
    <section id="features" class="features section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span class="subtitle">Core Beliefs</span>
        <h2>Our Core Beliefs</h2>
        <p>Our foundation is built upon the truth of God's Word and the revelation of Christ in us</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="tabs-wrapper">
          <div class="tabs-header" data-aos="fade-up" data-aos-delay="200">
            <ul class="nav nav-tabs">
              <li class="nav-item">
                <a class="nav-link active show" data-bs-toggle="tab" data-bs-target="#features-tab-1">
                  <div class="tab-content-preview">
                    <span class="tab-number">01</span>
                    <div class="tab-text">
                      <h6>The Godhead</h6>
                      <small>Father, Son, Holy Spirit</small>
                    </div>
                  </div>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-2">
                  <div class="tab-content-preview">
                    <span class="tab-number">02</span>
                    <div class="tab-text">
                      <h6>Jesus Christ</h6>
                      <small>Savior and Lord</small>
                    </div>
                  </div>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-3">
                  <div class="tab-content-preview">
                    <span class="tab-number">03</span>
                    <div class="tab-text">
                      <h6>Holy Spirit</h6>
                      <small>Helper and Guide</small>
                    </div>
                  </div>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-4">
                  <div class="tab-content-preview">
                    <span class="tab-number">04</span>
                    <div class="tab-text">
                      <h6>Our Identity</h6>
                      <small>Sons of God</small>
                    </div>
                  </div>
                </a>
              </li>
            </ul>
          </div>

          <div class="tab-content" data-aos="fade-up" data-aos-delay="300">

            <div class="tab-pane fade active show" id="features-tab-1">
              <div class="row align-items-center">
                <div class="col-lg-6">
                  <div class="content-area">
                    <div class="content-badge">
                      <i class="bi bi-heart"></i>
                      <span>Foundation of Faith</span>
                    </div>
                    <h3>The Fullness of the Godhead</h3>
                    <p>We believe in the fullness of the Godhead: God the Father, God the Son, and God the Holy Spirit. This triune nature of God is the foundation of our faith and understanding of the divine nature.</p>

                    <div class="feature-points">
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>God the Father - Creator and Sustainer of all things</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>God the Son - Jesus Christ, fully God and fully man</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>God the Holy Spirit - The executive agent and helper</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="visual-content">
                    <img src="assets/img/_MG_4880.jpg" alt="The Godhead" class="img-fluid">
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="features-tab-2">
              <div class="row align-items-center">
                <div class="col-lg-6">
                  <div class="content-area">
                    <div class="content-badge">
                      <i class="bi bi-cross"></i>
                      <span>Our Foundation</span>
                    </div>
                    <h3>Jesus Christ: Savior and Lord</h3>
                    <p>Jesus Christ is the Savior of the world from sin and is Lord. He is the Son of God, fully God, and the Way, the Truth, and the Life. Through Him, we have access to the Father and eternal life.</p>

                    <div class="feature-points">
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>Jesus Christ is the Savior from sin</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>He is fully God and fully man</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>He is the Way, the Truth, and the Life (John 14:6)</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>We believe in His return</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="visual-content">
                    <img src="assets/img/_MG_4902.jpg" alt="Jesus Christ" class="img-fluid">
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="features-tab-3">
              <div class="row align-items-center">
                <div class="col-lg-6">
                  <div class="content-area">
                    <div class="content-badge">
                      <i class="bi bi-wind"></i>
                      <span>Our Helper</span>
                    </div>
                    <h3>The Holy Spirit: Our Guide</h3>
                    <p>The Holy Spirit is the executive agent of God and the helper of believers. He empowers us, guides us into all truth, and enables us to live according to God's will and purpose.</p>

                    <div class="feature-points">
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>The Holy Spirit is the executive agent of God</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>He is our helper and guide</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>He empowers believers for ministry</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>He leads us into all truth</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="visual-content">
                    <img src="assets/img/_MG_5021.jpg" alt="Holy Spirit" class="img-fluid">
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="features-tab-4">
              <div class="row align-items-center">
                <div class="col-lg-6">
                  <div class="content-area">
                    <div class="content-badge">
                      <i class="bi bi-person-heart"></i>
                      <span>Who We Are</span>
                    </div>
                    <h3>Our Identity in Christ</h3>
                    <p>We believe humanity is created in the image and likeness of God. As believers, we are Sons of God, Kings and Priests, bearers of the Image and Likeness of God, precedents of holiness, immortals, hosts of heaven, carriers of God's presence, and a pure and consecrated people.</p>

                    <div class="feature-points">
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>Created in the image and likeness of God</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>Sons of God through faith in Christ (John 1:12-13)</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>Kings and Priests in God's kingdom</span>
                      </div>
                      <div class="point-item">
                        <i class="bi bi-arrow-right"></i>
                        <span>Carriers of God's presence and glory</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="visual-content">
                    <img src="assets/img/_MG_5281.jpg" alt="Our Identity" class="img-fluid">
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

    </section><!-- /Features Section -->

    <!-- Features Cards Section -->
    <section id="features-cards" class="features-cards section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4">
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="150">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-book"></i>
              </div>
              <h3>Scriptural Foundation</h3>
              <p>Our ministry is built upon the truth of God's Word. Key scriptures that guide us include John 1:12–13, Romans 8:19, John 14:6, John 17:3, and Hebrews 12:22, which collectively emphasize sonship, eternal life, and life in Christ.</p>
              <ul class="feature-benefits">
                <li><i class="bi bi-check-circle-fill"></i> John 1:12-13 - Sonship through Christ</li>
                <li><i class="bi bi-check-circle-fill"></i> Romans 8:19 - Creation awaits Sons of God</li>
                <li><i class="bi bi-check-circle-fill"></i> John 14:6 - Jesus is the Way, Truth, Life</li>
                <li><i class="bi bi-check-circle-fill"></i> John 17:3 - Eternal Life is knowing God</li>
                <li><i class="bi bi-check-circle-fill"></i> Hebrews 12:22 - We live in Zion</li>
              </ul>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card highlighted">
              <div class="feature-icon">
                <i class="bi bi-globe"></i>
              </div>
              <h3>Language & Culture</h3>
              <p>The ministry functions in both English and Swahili. CMN emphasizes a spiritual language rooted in eternal life consciousness, speaking Spirit and Life. We welcome people from diverse backgrounds, ages, and walks of life.</p>
              <ul class="feature-benefits">
                <li><i class="bi bi-check-circle-fill"></i> Bilingual ministry (English & Swahili)</li>
                <li><i class="bi bi-check-circle-fill"></i> Spiritual language of eternal life</li>
                <li><i class="bi bi-check-circle-fill"></i> Speaking Spirit and Life</li>
                <li><i class="bi bi-check-circle-fill"></i> Inclusive and welcoming community</li>
              </ul>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="250">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-heart"></i>
              </div>
              <h3>Our Identity</h3>
              <p>CMN identifies its members as Sons of God, Kings and Priests, bearers of the Image and Likeness of God, precedents of holiness, immortals, hosts of heaven, carriers of God's presence, and a pure and consecrated people.</p>
              <ul class="feature-benefits">
                <li><i class="bi bi-check-circle-fill"></i> Sons of God</li>
                <li><i class="bi bi-check-circle-fill"></i> Kings and Priests</li>
                <li><i class="bi bi-check-circle-fill"></i> Carriers of God's presence</li>
                <li><i class="bi bi-check-circle-fill"></i> Pure and consecrated people</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="feature-testimonial" data-aos="fade-up" data-aos-delay="300">
          <div class="row align-items-center">
            <div class="col-lg-6" data-aos="zoom-in">
              <div class="testimonial-image">
                <img src="assets/img/_MG_5282.jpg" alt="CrossLife Mission Network" class="img-fluid">
              </div>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
              <div class="testimonial-content">
                <div class="quote-icon">
                  <i class="bi bi-quote"></i>
                </div>
                <p>"We live in Zion, the realm of Christ. Eternal Life is our present reality. The ministry is committed to creating an environment where believers experience the Life of God and grow in their identity in Christ."</p>
                <div class="testimonial-author">
                  <h4>CrossLife Mission Network</h4>
                  <span>Our Motto</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Features Cards Section -->

    <!-- Leadership Section -->
    <section id="leadership" class="leadership section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span class="subtitle">Leadership</span>
        <h2>Our Leadership</h2>
        <p>Meet the leaders who guide CrossLife Mission Network in manifesting Sons of God</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4 align-items-stretch">
          <?php if (empty($leadership)): ?>
            <!-- Default leadership if database is empty -->
            <div class="col-md-6 col-lg-4">
              <article class="leader-card h-100" data-aos="zoom-in" data-aos-delay="150">
                <figure class="leader-media">
                  <img src="assets/img/_MG_5266.jpg" class="img-fluid" alt="Pastor Lenhard Kyamba">
                </figure>
                <div class="leader-content">
                  <h3 class="leader-name">Pastor Lenhard Kyamba</h3>
                  <p class="leader-role">Senior Pastor</p>
                  <p class="leader-bio">Leading CrossLife Mission Network in manifesting Sons of God who understand their identity in Christ and what Christ can accomplish through them.</p>
                  <div class="leader-contact mt-3">
                    <p class="small mb-1"><i class="bi bi-envelope me-2"></i>lenhard.kyamba@crosslife.org</p>
                    <p class="small mb-0"><i class="bi bi-telegram me-2"></i>Pastor Lenhard Kyamba</p>
                  </div>
                </div>
              </article>
            </div>
          <?php else: ?>
            <?php 
            $delay = 150;
            foreach ($leadership as $leader): 
              $image = !empty($leader['photo']) ? 'assets/img/uploads/' . htmlspecialchars($leader['photo']) : 'assets/img/_MG_5266.jpg';
            ?>
              <div class="col-md-6 col-lg-4">
                <article class="leader-card h-100" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
                  <figure class="leader-media">
                    <img src="<?php echo $image; ?>" class="img-fluid" alt="<?php echo htmlspecialchars($leader['name']); ?>">
                  </figure>
                  <div class="leader-content">
                    <h3 class="leader-name"><?php echo htmlspecialchars($leader['name']); ?></h3>
                    <p class="leader-role"><?php echo htmlspecialchars($leader['role'] ?? 'Leader'); ?></p>
                    <p class="leader-bio"><?php echo htmlspecialchars($leader['bio'] ?? ''); ?></p>
                    <?php if (!empty($leader['email']) || !empty($leader['phone'])): ?>
                      <div class="leader-contact mt-3">
                        <?php if (!empty($leader['email'])): ?>
                          <p class="small mb-1"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($leader['email']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($leader['phone'])): ?>
                          <p class="small mb-0"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($leader['phone']); ?></p>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </article>
              </div>
            <?php 
              $delay += 50;
            endforeach; 
            ?>
          <?php endif; ?>
        </div>

      </div>

    </section><!-- /Leadership Section -->


    <!-- Feedback Section -->
    <section id="feedback" class="feedback section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span class="subtitle">Feedback</span>
        <h2>Share Your Feedback</h2>
        <p>We value your input, concerns, and suggestions. Your feedback helps us serve you better.</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row justify-content-center">
          <div class="col-lg-8">
            <form action="forms/feedback.php" method="post" class="php-email-form feedback-form">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-field">
                    <input type="text" name="name" class="form-input" id="feedbackName" placeholder="Your Name (Optional)">
                    <label for="feedbackName" class="field-label">Name (Optional - Anonymous submissions welcome)</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-field">
                    <input type="email" class="form-input" name="email" id="feedbackEmail" placeholder="Your Email (Optional)">
                    <label for="feedbackEmail" class="field-label">Email (Optional)</label>
                  </div>
                </div>
              </div>

              <div class="form-field">
                <select name="feedback_type" class="form-input" id="feedbackType">
                  <option value="">Select Feedback Type</option>
                  <option value="suggestion">Suggestion</option>
                  <option value="concern">Concern</option>
                  <option value="praise">Praise/Testimony</option>
                  <option value="other">Other</option>
                </select>
                <label for="feedbackType" class="field-label">Feedback Type</label>
              </div>

              <div class="form-field message-field">
                <textarea class="form-input message-input" name="message" id="feedbackMessage" rows="6" placeholder="Share your feedback, concerns, or suggestions" required=""></textarea>
                <label for="feedbackMessage" class="field-label">Your Feedback *</label>
              </div>

              <div class="my-3">
                <div class="loading">Loading</div>
                <div class="error-message"></div>
                <div class="sent-message">Thank you for your feedback! We appreciate your input.</div>
              </div>

              <button type="submit" class="send-button">
                Submit Feedback
                <span class="button-arrow">→</span>
              </button>
            </form>
          </div>
        </div>

      </div>

    </section><!-- /Feedback Section -->

    <!-- Call To Action Section -->
    <section id="call-to-action" class="call-to-action section">

      <div class="container" data-aos="zoom-out">

        <div class="row g-5">

          <div class="col-lg-8 col-md-6 content d-flex flex-column justify-content-center order-last order-md-first">
            <h3>Join Us in <em>Manifesting</em> Sons of God</h3>
            <p>We welcome you to be part of a global network of awakened Sons of God living in New Creation realities. Experience the Life of God and grow in your identity in Christ. Grace and Peace be multiplied to you in Jesus' Name. Receive Life in abundance.</p>
            <a class="cta-btn align-self-start" href="contacts.php">Connect With Us</a>
          </div>

          <div class="col-lg-4 col-md-6 order-first order-md-last d-flex align-items-center">
            <div class="img">
              <img src="assets/img/_MG_4859.jpg" alt="CrossLife Mission Network" class="img-fluid">
            </div>
          </div>

        </div>

      </div>

    </section><!-- /Call To Action Section -->

    <!-- What We Do Section -->
    <section id="what-we-do" class="what-we-do section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span class="subtitle">What We Do</span>
        <h2>Church Activities</h2>
        <p>Discover the various activities and programs that make up our church community</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4">
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="150">
            <div class="activity-card">
              <div class="activity-icon">
                <i class="bi bi-heart"></i>
              </div>
              <h3>Worship Services</h3>
              <p>Join us for weekly worship services where we gather to experience the Life of God and grow in our identity in Christ.</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="activity-card">
              <div class="activity-icon">
                <i class="bi bi-book"></i>
              </div>
              <h3>Teaching & Discipleship</h3>
              <p>Engage in structured discipleship programs through the School of Christ Academy, including Foundation Classes, Leadership Training, and Ministry Development.</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="250">
            <div class="activity-card">
              <div class="activity-icon">
                <i class="bi bi-people"></i>
              </div>
              <h3>Ministry Programs</h3>
              <p>Participate in various ministries designed to manifest Sons of God and establish a global network unified by one message, one mandate, and one mission.</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="activity-card">
              <div class="activity-icon">
                <i class="bi bi-pray"></i>
              </div>
              <h3>Prayer & Intercession</h3>
              <p>Join our community of prayer as we intercede for the church, the nation, and the global body of Christ.</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="350">
            <div class="activity-card">
              <div class="activity-icon">
                <i class="bi bi-globe"></i>
              </div>
              <h3>Global Outreach</h3>
              <p>Be part of our mission to reach the global community by showing the Way, revealing the Truth, and sharing Life through Christ.</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
            <div class="activity-card">
              <div class="activity-icon">
                <i class="bi bi-hand-heart"></i>
              </div>
              <h3>Fellowship & Community</h3>
              <p>Connect with believers from diverse backgrounds in a community of Life, Love, Sonship, and Prayer.</p>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /What We Do Section -->

    <!-- Offering & Giving Section -->
    <section id="giving" class="giving section dark-background">

      <div class="container" data-aos="fade-up">

        <div class="row align-items-center">
          <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
            <div class="content">
              <span class="subtitle">Giving</span>
              <h2>Support the Ministry</h2>
              <p class="lead">Your giving enables us to continue manifesting Sons of God and reaching the global community with the Gospel of the Cross, the Message of Sonship, and the Gospel of the Kingdom of God.</p>
              <p>We believe in cheerful giving as an act of worship and partnership in the ministry. Your contributions support our teaching, discipleship programs, outreach efforts, and the establishment of the global network of manifested Sons of God.</p>
              <div class="giving-methods mt-4">
                <h4>Ways to Give:</h4>
                <ul class="list-unstyled">
                  <li><i class="bi bi-check-circle me-2"></i> Bank Transfer</li>
                  <li><i class="bi bi-check-circle me-2"></i> Mobile Money</li>
                  <li><i class="bi bi-check-circle me-2"></i> In-Person Offering</li>
                </ul>
                <p class="mt-3"><em>For more information about giving, please contact us through our contact form or reach out to the church office.</em></p>
              </div>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
            <div class="image-wrapper">
              <img src="assets/img/_MG_4880.jpg" alt="Giving" class="img-fluid">
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Offering & Giving Section -->


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
              <li><a href="#about"><i class="bi bi-chevron-right"></i> About Us</a></li>
              <li><a href="#features"><i class="bi bi-chevron-right"></i> Core Beliefs</a></li>
              <li><a href="contacts.php"><i class="bi bi-chevron-right"></i> Contact</a></li>
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
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <!-- Global Search Script -->
  <script>
    // Global Search Functionality
    document.addEventListener('DOMContentLoaded', function() {
      const searchForm = document.getElementById('globalSearchForm');
      const searchInput = document.getElementById('globalSearchInput');
      const searchResults = document.getElementById('searchResults');
      const searchResultsList = document.getElementById('searchResultsList');

      // Searchable content sections
      const searchableSections = [
        { id: 'hero', title: 'Home', keywords: 'home welcome introduction vision mission', page: 'index.php' },
        { id: 'about', title: 'About Us', keywords: 'about mandate vision mission history', page: 'index.php' },
        { id: 'statement-of-faith', title: 'Statement of Faith', keywords: 'faith beliefs doctrine scripture godhead jesus holy spirit', page: 'index.php' },
        { id: 'features', title: 'Core Beliefs', keywords: 'beliefs core godhead jesus christ holy spirit identity', page: 'index.php' },
        { id: 'leadership', title: 'Leadership', keywords: 'leadership pastor lenhard kyamba executive board', page: 'index.php' },
        { id: 'ministries', title: 'Ministries', keywords: 'ministries teaching discipleship prayer outreach worship fellowship', page: 'ministries.php' },
        { id: 'sermons', title: 'Sermons', keywords: 'sermons teaching video audio youtube crosslife tv', page: 'sermons.php' },
        { id: 'discipleship', title: 'Discipleship', keywords: 'discipleship school of christ academy foundation leadership ministry sonship', page: 'discipleship.php' },
        { id: 'events', title: 'Events', keywords: 'events calendar services bible study prayer meetings', page: 'events.php' },
        { id: 'what-we-do', title: 'What We Do', keywords: 'activities programs worship teaching ministry outreach', page: 'index.php' },
        { id: 'giving', title: 'Giving', keywords: 'giving offering support ministry donation', page: 'index.php' },
        { id: 'contact', title: 'Contact', keywords: 'contact inquiry prayer request feedback', page: 'contacts.php' }
      ];

      if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
          e.preventDefault();
          performSearch();
        });

        searchInput.addEventListener('input', function() {
          if (this.value.length > 2) {
            performSearch();
          } else {
            searchResults.style.display = 'none';
          }
        });
      }

      function performSearch() {
        const query = searchInput.value.toLowerCase().trim();
        if (query.length < 2) {
          searchResults.style.display = 'none';
          return;
        }

        const results = searchableSections.filter(section => {
          return section.title.toLowerCase().includes(query) || 
                 section.keywords.toLowerCase().includes(query) ||
                 section.id.toLowerCase().includes(query);
        });

        displayResults(results, query);
      }

      function displayResults(results, query) {
        searchResultsList.innerHTML = '';
        
        if (results.length === 0) {
          searchResultsList.innerHTML = '<li class="text-muted">No results found. Try different keywords.</li>';
          searchResults.style.display = 'block';
          return;
        }

        results.forEach(result => {
          const li = document.createElement('li');
          li.className = 'mb-2';
          const href = result.page === window.location.pathname.split('/').pop() || (result.page === 'index.php' && (window.location.pathname === '/' || window.location.pathname.endsWith('index.php')))
            ? `#${result.id}` 
            : `${result.page}${result.id ? '#' + result.id : ''}`;
          li.innerHTML = `
            <a href="${href}" class="search-result-link text-decoration-none" data-bs-dismiss="modal">
              <i class="bi bi-arrow-right me-2"></i>
              <strong>${result.title}</strong>
            </a>
          `;
          searchResultsList.appendChild(li);
        });

        searchResults.style.display = 'block';
      }
    });
  </script>

  <!-- Slideshow Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const slides = document.querySelectorAll('.slideshow-container .slide');
      const indicators = document.querySelectorAll('.slideshow-indicators .indicator');
      const prevBtn = document.querySelector('.slideshow-prev');
      const nextBtn = document.querySelector('.slideshow-next');
      let currentSlide = 0;
      let slideInterval;
      
      if (slides.length > 0) {
        function showSlide(index) {
          // Remove active class from all slides and indicators
          slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === index) {
              slide.classList.add('active');
            }
          });
          
          indicators.forEach((indicator, i) => {
            indicator.classList.remove('active');
            if (i === index) {
              indicator.classList.add('active');
            }
          });
          
          currentSlide = index;
        }
        
        function nextSlide() {
          currentSlide = (currentSlide + 1) % slides.length;
          showSlide(currentSlide);
        }
        
        function prevSlide() {
          currentSlide = (currentSlide - 1 + slides.length) % slides.length;
          showSlide(currentSlide);
        }
        
        function startSlideshow() {
          slideInterval = setInterval(nextSlide, 6000);
        }
        
        function stopSlideshow() {
          clearInterval(slideInterval);
        }
        
        function resetSlideshow() {
          stopSlideshow();
          startSlideshow();
        }
        
        // Navigation buttons
        if (nextBtn) {
          nextBtn.addEventListener('click', function() {
            nextSlide();
            resetSlideshow();
          });
        }
        
        if (prevBtn) {
          prevBtn.addEventListener('click', function() {
            prevSlide();
            resetSlideshow();
          });
        }
        
        // Indicator clicks
        indicators.forEach((indicator, index) => {
          indicator.addEventListener('click', function() {
            showSlide(index);
            resetSlideshow();
          });
        });
        
        // Pause on hover
        const slideshowContainer = document.querySelector('.slideshow-background');
        if (slideshowContainer) {
          slideshowContainer.addEventListener('mouseenter', stopSlideshow);
          slideshowContainer.addEventListener('mouseleave', startSlideshow);
        }
        
        // Initialize
        showSlide(0);
        startSlideshow();
      }
    });
  </script>

</body>

</html>