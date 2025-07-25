<?php
set_time_limit(300); // allow up to 5 minutes
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

        $xml = @simplexml_load_file("https://boardgamegeek.com/xmlapi2/thing?id=" . urlencode($id));
        if ($xml && isset($xml->item->image)) {
            $imageUrl = (string)$xml->item->image;
            $imageData = @file_get_contents($imageUrl);
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