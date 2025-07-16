<?php
function fetch_bgg_collection($username) {
    $url = "https://boardgamegeek.com/xmlapi2/collection?username=" . urlencode($username) . "&own=1";

    // BGG API may respond with 202 if data not ready
    for ($i = 0; $i < 5; $i++) {
        $xml = @simplexml_load_file($url);
        if ($xml && $xml->item) break;
        sleep(2); // wait and retry
    }

    if (!$xml) return false;

    $games = [];
    foreach ($xml->item as $item) {
        $games[] = [
            'id' => (string)$item['objectid'],
            'name' => (string)$item->name,
            'year' => (string)$item->yearpublished,
            'image' => (string)$item->image,
        ];
    }

    file_put_contents("collection.json", json_encode($games));
    return $games;
}
?>