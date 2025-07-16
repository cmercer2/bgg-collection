<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("bgg.php");

$games = [];
$playerCount = $_GET['players'] ?? null;

$games = fetch_bgg_collection();

if ($playerCount !== null && is_numeric($playerCount)) {
    $games = array_filter($games, function($game) use ($playerCount) {
        return $playerCount >= $game['minplayers'] && $playerCount <= $game['maxplayers'];
    });
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My BGG Collection</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>BoardGameGeek Collection Viewer</h1>
    <form method="get">
        <label for="players">Number of Players:</label>
        <input type="number" name="players" id="players" min="1" value="<?= htmlspecialchars($_GET['players'] ?? '') ?>">
        <button type="submit">Load Collection</button>
    </form>

    <?php if ($games): ?>
        <div class="collection">
            <?php foreach ($games as $game): ?>
                <div class="game">
                    <img src="<?=htmlspecialchars($game['image'])?>" alt="" width="300">
                    <div class="info">
                        <strong><?=htmlspecialchars($game['name'])?></strong>
                        <span>(<?=htmlspecialchars($game['year'])?>)</span>
                        <div><?=htmlspecialchars($game['minplayers'])?>-<?=htmlspecialchars($game['maxplayers'])?> players</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>