<?php
function fetch_bgg_collection($username = null) {
    $csvPath = __DIR__ . '/collection.csv';
    if (!file_exists($csvPath)) {
        return false;
    }

    $games = [];
    if (($handle = fopen($csvPath, 'r')) !== false) {
        $headers = fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle)) !== false) {
            $games[] = [
                'id' => $data[0] ?? '',
                'name' => $data[1] ?? '',
                'year' => $data[2] ?? '',
                'image' => $data[3] ?? '',
                'minplayers' => $data[4] ?? '',
                'maxplayers' => $data[5] ?? ''
            ];
        }
        fclose($handle);
    }

    return $games;
}
?>