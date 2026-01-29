<?php
/**
 * YouTube Functions
 * Fetch videos from YouTube channels automatically (no API key required)
 */

/**
 * Extract YouTube video ID from URL (if not already defined)
 */
if (!function_exists('getYouTubeId')) {
    function getYouTubeId($url) {
        if (empty($url)) return null;
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }
}

/**
 * Fetch videos from YouTube channel by parsing the channel page
 * This works without an API key by scraping the public channel page
 * 
 * @param string $channelHandle Channel handle without @ (e.g., "PastorLenhardKyamba")
 * @param int $maxResults Maximum number of videos to fetch
 * @return array Array of video data
 */
function fetchYouTubeChannelVideos($channelHandle, $maxResults = 50) {
    // Remove @ if present
    $channelHandle = ltrim($channelHandle, '@');
    
    $videos = [];
    $channelUrl = "https://www.youtube.com/@{$channelHandle}/videos";
    
    // Set user agent to avoid blocking
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            ],
            'timeout' => 10
        ]
    ]);
    
    $html = @file_get_contents($channelUrl, false, $context);
    
    if (!$html) {
        return [];
    }
    
    // Extract ytInitialData JSON from the page
    // YouTube stores video data in a JSON object in the page
    if (preg_match('/var ytInitialData = ({.+?});/', $html, $matches) || 
        preg_match('/"ytInitialData":({.+?}),"ytInitialPlayerResponse"/', $html, $matches)) {
        
        $jsonData = $matches[1];
        $data = @json_decode($jsonData, true);
        
        if ($data) {
            // Navigate through YouTube's data structure to find videos
            // Path: contents -> twoColumnBrowseResultsRenderer -> tabs -> tabRenderer -> content -> richGridRenderer -> contents
            $tabs = $data['contents']['twoColumnBrowseResultsRenderer']['tabs'] ?? [];
            
            foreach ($tabs as $tab) {
                if (isset($tab['tabRenderer']['content']['richGridRenderer']['contents'])) {
                    $contents = $tab['tabRenderer']['content']['richGridRenderer']['contents'];
                    
                    foreach ($contents as $item) {
                        if (isset($item['richItemRenderer']['content']['videoRenderer'])) {
                            $video = $item['richItemRenderer']['content']['videoRenderer'];
                            
                            $videoId = $video['videoId'] ?? null;
                            
                            if ($videoId) {
                                $title = '';
                                if (isset($video['title']['runs'][0]['text'])) {
                                    $title = $video['title']['runs'][0]['text'];
                                } elseif (isset($video['title']['simpleText'])) {
                                    $title = $video['title']['simpleText'];
                                }
                                
                                // Get thumbnail (highest quality)
                                $thumbnail = '';
                                if (isset($video['thumbnail']['thumbnails']) && !empty($video['thumbnail']['thumbnails'])) {
                                    $thumbnails = $video['thumbnail']['thumbnails'];
                                    $thumbnail = end($thumbnails)['url'] ?? '';
                                }
                                
                                // Get published time
                                $publishedTime = $video['publishedTimeText']['simpleText'] ?? '';
                                
                                // Get view count
                                $views = '';
                                if (isset($video['viewCountText']['simpleText'])) {
                                    $views = $video['viewCountText']['simpleText'];
                                }
                                
                                $videos[] = [
                                    'video_id' => $videoId,
                                    'title' => $title ?: 'Untitled Video',
                                    'thumbnail' => $thumbnail ?: "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg",
                                    'url' => "https://www.youtube.com/watch?v={$videoId}",
                                    'published' => $publishedTime,
                                    'views' => $views,
                                    'description' => '',
                                    'speaker' => 'Pastor Lenhard Kyamba',
                                    'sermon_type' => 'video'
                                ];
                                
                                if (count($videos) >= $maxResults) {
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $videos;
}

/**
 * Fetch videos from multiple YouTube channels
 */
function fetchMultipleYouTubeChannels($channels, $maxResults = 50) {
    $allVideos = [];
    
    foreach ($channels as $channelHandle) {
        $videos = fetchYouTubeChannelVideos($channelHandle, $maxResults);
        $allVideos = array_merge($allVideos, $videos);
    }
    
    return $allVideos;
}

/**
 * Get combined videos from database and YouTube channels
 * Merges database sermons with live YouTube channel videos
 */
function getCombinedSermons($dbSermons, $youtubeChannels = [], $maxYouTubeVideos = 50) {
    $combined = [];
    
    // Add database sermons first
    foreach ($dbSermons as $sermon) {
        $combined[] = [
            'id' => $sermon['id'] ?? null,
            'title' => $sermon['title'],
            'description' => $sermon['description'] ?? '',
            'speaker' => $sermon['speaker'] ?? '',
            'sermon_type' => $sermon['sermon_type'],
            'youtube_url' => $sermon['youtube_url'] ?? '',
            'audio_url' => $sermon['audio_url'] ?? '',
            'thumbnail_url' => $sermon['thumbnail_url'] ?? '',
            'sermon_date' => $sermon['sermon_date'] ?? null,
            'category' => $sermon['category'] ?? '',
            'status' => $sermon['status'] ?? 'published',
            'source' => 'database'
        ];
    }
    
    // Add YouTube channel videos
    $youtubeVideoIds = [];
    foreach ($combined as $item) {
        if (!empty($item['youtube_url'])) {
            $videoId = getYouTubeId($item['youtube_url']);
            if ($videoId) {
                $youtubeVideoIds[] = $videoId;
            }
        }
    }
    
    foreach ($youtubeChannels as $channelHandle) {
        $youtubeVideos = fetchYouTubeChannelVideos($channelHandle, $maxYouTubeVideos);
        
        foreach ($youtubeVideos as $ytVideo) {
            // Skip if already in database
            if (in_array($ytVideo['video_id'], $youtubeVideoIds)) {
                continue;
            }
            
            $combined[] = [
                'id' => null,
                'title' => $ytVideo['title'],
                'description' => $ytVideo['description'] ?? '',
                'speaker' => $ytVideo['speaker'] ?? 'Pastor Lenhard Kyamba',
                'sermon_type' => 'video',
                'youtube_url' => $ytVideo['url'],
                'audio_url' => '',
                'thumbnail_url' => $ytVideo['thumbnail'],
                'sermon_date' => null,
                'category' => '',
                'status' => 'published',
                'source' => 'youtube',
                'views' => $ytVideo['views'] ?? '',
                'published' => $ytVideo['published'] ?? ''
            ];
            
            $youtubeVideoIds[] = $ytVideo['video_id'];
        }
    }
    
    return $combined;
}

/**
 * Get YouTube video details using oEmbed (no API key required)
 */
function getYouTubeVideoDetails($videoUrl) {
    $videoId = getYouTubeId($videoUrl);
    if (!$videoId) {
        return null;
    }
    
    $oembedUrl = "https://www.youtube.com/oembed?url=" . urlencode($videoUrl) . "&format=json";
    $data = @file_get_contents($oembedUrl);
    
    if ($data) {
        $info = json_decode($data, true);
        return [
            'title' => $info['title'] ?? 'Untitled',
            'thumbnail' => $info['thumbnail_url'] ?? '',
            'description' => ''
        ];
    }
    
    return null;
}
