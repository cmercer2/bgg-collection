<?php
set_time_limit(300); // allow up to 5 minutes
// Helper to fetch URLs using cURL with a proper User-Agent and TLS support.
function fetch_url_curl(string $url, int $timeout = 15) {
    if (!function_exists('curl_version')) {
        return false;
    }
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'bgg-collection/1.0 (+https://github.com/cmercer2/bgg-collection)'
    ]);
    $data = curl_exec($ch);
    $err = curl_errno($ch);
    curl_close($ch);
    if ($err || $data === false) return false;
    return $data;
}
function fetch_bgg_collection() {
    $csvPath = __DIR__ . '/collection.csv';
    if (!file_exists($csvPath)) {
        return false;
    }

    $games = [];
    if (($handle = fopen($csvPath, 'r')) !== false) {
        $headers = fgetcsv($handle, 0, ",", '"', "\\"); // Read header row
        $headerMap = array_flip($headers);
        while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
            $games[] = [
                'name' => $data[$headerMap['name']] ?? '',
                'id' => $data[$headerMap['objectid']] ?? '',
                'year' => $data[$headerMap['year']] ?? '',
                'minplayers' => $data[$headerMap['minplayers']] ?? '',
                'maxplayers' => $data[$headerMap['maxplayers']] ?? '',
                'playtime' => $data[$headerMap['playingtime']] ?? '',
                'expansion' => $data[$headerMap['expansion']] ?? '',
                'average' => $data[$headerMap['average']] ?? '',
                'avgweight' => $data[$headerMap['avgweight']] ?? '',
            ];
        }
        fclose($handle);
    }

    // Image caching logic
    $cacheDir = __DIR__ . '/cache';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    foreach ($games as &$game) {
        $id = $game['id'];
        if (!$id) continue;

        $cachedPath = "cache/{$id}.jpg";
        $fullCachedPath = __DIR__ . "/$cachedPath";

        if (file_exists($fullCachedPath)) {
            $game['image'] = $cachedPath;
            continue;
        }

        // Try to fetch XML using cURL first (some servers require a proper User-Agent).
        $xml = false;
        $xmlStr = @fetch_url_curl("https://boardgamegeek.com/xmlapi2/thing?id=" . urlencode($id), 10);
        if ($xmlStr !== false) {
            $xml = @simplexml_load_string($xmlStr);
        } else {
            // Fallback: try simplexml_load_file in case allow_url_fopen is enabled
            $xml = @simplexml_load_file("https://boardgamegeek.com/xmlapi2/thing?id=" . urlencode($id));
        }
        if ($xml && isset($xml->item->image)) {
            $imageUrl = (string)$xml->item->image;
            // Fetch image using cURL (more reliable for headers/TLS)
            $imageData = @fetch_url_curl($imageUrl, 20);
            if ($imageData) {
                $imageInfo = @getimagesizefromstring($imageData);
                $mime = $imageInfo['mime'] ?? '';

                switch ($mime) {
                    case 'image/jpeg':
                        $srcImage = @imagecreatefromjpeg("data://image/jpeg;base64," . base64_encode($imageData));
                        break;
                    case 'image/png':
                        $srcImage = @imagecreatefrompng("data://image/png;base64," . base64_encode($imageData));
                        break;
                    case 'image/gif':
                        $srcImage = @imagecreatefromgif("data://image/gif;base64," . base64_encode($imageData));
                        break;
                    default:
                        $srcImage = @imagecreatefromstring($imageData);
                }

                if ($srcImage !== false) {
                    $width = imagesx($srcImage);
                    $height = imagesy($srcImage);
                    $newWidth = 300;
                    $newHeight = intval(($height / $width) * $newWidth);

                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($resized, $fullCachedPath, 85);

                    imagedestroy($srcImage);
                    imagedestroy($resized);

                    $game['image'] = $cachedPath;
                } else {
                    $game['image'] = '';
                }
            } else {
                $game['image'] = '';
            }
        } else {
            $game['image'] = '';
        }

        usleep(250000); // 0.25 second delay
    }
    unset($game);

    return $games;
}
?>