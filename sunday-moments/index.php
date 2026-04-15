<?php
$uploadsBasePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
$uploadsWebPath = '../uploads';
$allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$allowedVideoExtensions = ['mp4', 'webm', 'mov'];
$allowedExtensions = array_merge($allowedImageExtensions, $allowedVideoExtensions);

// Optional manual themes by service date (Y-m-d)
$serviceThemes = [
    '2026-04-07' => 'Faith in Action',
    '2026-04-14' => 'Living by Grace',
];

$serviceDirectories = [];
if (is_dir($uploadsBasePath)) {
    $serviceDirectories = glob($uploadsBasePath . DIRECTORY_SEPARATOR . 'sunday-*', GLOB_ONLYDIR) ?: [];
}

$services = [];
$heroImage = '';

foreach ($serviceDirectories as $directoryPath) {
    $directoryName = basename($directoryPath);

    if (!preg_match('/^sunday-(\d{4}-\d{2}-\d{2})$/', $directoryName, $matches)) {
        continue;
    }

    $serviceDate = $matches[1];
    $displayDate = $serviceDate;
    $dateObject = DateTime::createFromFormat('Y-m-d', $serviceDate);
    if ($dateObject instanceof DateTime) {
        $displayDate = $dateObject->format('F j, Y');
    }

    $files = scandir($directoryPath) ?: [];
    $mediaItems = [];

    foreach ($files as $fileName) {
        if ($fileName === '.' || $fileName === '..') {
            continue;
        }

        $filePath = $directoryPath . DIRECTORY_SEPARATOR . $fileName;
        if (!is_file($filePath)) {
            continue;
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            continue;
        }

        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $prefixCategory = strtolower((string) strtok($baseName, '_'));
        $validCategories = ['worship', 'sermon', 'fellowship', 'outreach'];
        $category = in_array($prefixCategory, $validCategories, true) ? $prefixCategory : 'fellowship';

        $captionSeed = preg_replace('/^(worship|sermon|fellowship|outreach)_/i', '', $baseName);
        $captionSeed = str_replace(['-', '_'], ' ', (string) $captionSeed);
        $caption = trim(ucwords($captionSeed));
        if ($caption === '') {
            $caption = ucfirst($category) . ' Moment';
        }

        $isVideo = in_array($extension, $allowedVideoExtensions, true);
        $encodedFileName = rawurlencode($fileName);
        $mediaUrl = $uploadsWebPath . '/' . $directoryName . '/' . $encodedFileName;

        $mediaItems[] = [
            'url' => $mediaUrl,
            'category' => $category,
            'caption' => $caption,
            'isVideo' => $isVideo,
            'mimeType' => $isVideo ? 'video/' . ($extension === 'mov' ? 'quicktime' : $extension) : '',
        ];

        if ($heroImage === '' && !$isVideo) {
            $heroImage = $mediaUrl;
        }
    }

    if (empty($mediaItems)) {
        continue;
    }

    $services[] = [
        'dateRaw' => $serviceDate,
        'dateDisplay' => $displayDate,
        'theme' => $serviceThemes[$serviceDate] ?? 'Gathering in Grace',
        'media' => $mediaItems,
    ];
}

usort($services, static function (array $a, array $b): int {
    return strcmp($b['dateRaw'], $a['dateRaw']);
});
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sunday Moments | CrossLife</title>
    <meta name="description" content="Sunday Moments captures worship, sermons, fellowship, and outreach stories from each service.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="hero" style="<?php echo $heroImage !== '' ? 'background-image: linear-gradient(165deg, rgba(18,18,18,0.72) 0%, rgba(18,18,18,0.38) 45%, rgba(18,18,18,0.8) 100%), url(\'' . htmlspecialchars($heroImage, ENT_QUOTES) . '\');' : ''; ?>">
        <div class="hero__grain" aria-hidden="true"></div>
        <div class="hero__content reveal-up is-visible">
            <p class="hero__kicker">CrossLife Gallery</p>
            <h1>Sunday Moments</h1>
            <p>Capturing God&apos;s presence in every gathering</p>
        </div>
    </header>

    <main class="page-wrap">
        <section class="filters reveal-up" aria-label="Gallery categories">
            <button class="filter-btn is-active" type="button" data-filter="all">All</button>
            <button class="filter-btn" type="button" data-filter="worship">Worship</button>
            <button class="filter-btn" type="button" data-filter="sermon">Sermon</button>
            <button class="filter-btn" type="button" data-filter="fellowship">Fellowship</button>
            <button class="filter-btn" type="button" data-filter="outreach">Outreach</button>
        </section>

        <?php if (empty($services)): ?>
            <section class="empty-state reveal-up is-visible">
                <h2>No Sunday moments yet</h2>
                <p>Add folders like <strong>/uploads/sunday-2026-04-07/</strong> and place images such as <strong>worship_1.jpg</strong> or <strong>sermon_word.mp4</strong> inside.</p>
            </section>
        <?php else: ?>
            <?php foreach ($services as $service): ?>
                <section class="service-group reveal-up" data-service-group>
                    <div class="service-header">
                        <h2>Sunday Service - <?php echo htmlspecialchars($service['dateDisplay'], ENT_QUOTES); ?></h2>
                        <p>Theme: <?php echo htmlspecialchars($service['theme'], ENT_QUOTES); ?></p>
                    </div>

                    <div class="media-grid" data-media-grid>
                        <?php foreach ($service['media'] as $item): ?>
                            <article class="media-card reveal-up" data-category="<?php echo htmlspecialchars($item['category'], ENT_QUOTES); ?>">
                                <button
                                    type="button"
                                    class="media-trigger"
                                    data-src="<?php echo htmlspecialchars($item['url'], ENT_QUOTES); ?>"
                                    data-type="<?php echo $item['isVideo'] ? 'video' : 'image'; ?>"
                                    data-caption="<?php echo htmlspecialchars($item['caption'], ENT_QUOTES); ?>"
                                    <?php if ($item['isVideo']): ?>
                                        data-mime="<?php echo htmlspecialchars($item['mimeType'], ENT_QUOTES); ?>"
                                    <?php endif; ?>
                                    aria-label="Open <?php echo htmlspecialchars($item['caption'], ENT_QUOTES); ?>"
                                >
                                    <?php if ($item['isVideo']): ?>
                                        <video class="media-preview" preload="metadata" muted playsinline>
                                            <source src="<?php echo htmlspecialchars($item['url'], ENT_QUOTES); ?>" type="<?php echo htmlspecialchars($item['mimeType'], ENT_QUOTES); ?>">
                                        </video>
                                        <span class="video-pill">Video</span>
                                    <?php else: ?>
                                        <img
                                            class="media-preview"
                                            src="<?php echo htmlspecialchars($item['url'], ENT_QUOTES); ?>"
                                            alt="<?php echo htmlspecialchars($item['caption'], ENT_QUOTES); ?>"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    <?php endif; ?>
                                    <span class="media-caption"><?php echo htmlspecialchars($item['caption'], ENT_QUOTES); ?></span>
                                </button>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <div class="lightbox" id="lightbox" aria-hidden="true" role="dialog" aria-label="Media lightbox">
        <div class="lightbox__backdrop" data-close-lightbox></div>
        <div class="lightbox__dialog" role="document">
            <button class="lightbox__close" type="button" aria-label="Close" data-close-lightbox>&times;</button>
            <div class="lightbox__content" id="lightboxContent"></div>
            <p class="lightbox__caption" id="lightboxCaption"></p>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
