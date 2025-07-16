<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("bgg.php");

$games = [];
$playerCount = $_GET['players'] ?? null;

$games = fetch_bgg_collection();

$includeExpansions = isset($_GET['include_expansions']);

if (!$includeExpansions) {
    $games = array_filter($games, function($game) {
        return empty($game['expansion']) || strtolower($game['expansion']) !== 'true';
    });
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <h1><a href="index.php">BoardGameGeek Collection Viewer</a></h1>
    <form method="get">
        <label for="players">Number of Players:</label>
        <input type="number" name="players" id="players" min="1" value="<?= htmlspecialchars($_GET['players'] ?? '') ?>">
        <label>
            <input type="checkbox" name="include_expansions" value="1" <?= isset($_GET['include_expansions']) ? 'checked' : '' ?>>
            Include Expansions
        </label>
        <label for="sort">Sort by:</label>
        <select name="sort" id="sort">
            <option value="">None</option>
            <option value="rating-asc" <?= ($_GET['sort'] ?? '') === 'rating-asc' ? 'selected' : '' ?>>Rating ↑</option>
            <option value="rating-desc" <?= ($_GET['sort'] ?? '') === 'rating-desc' ? 'selected' : '' ?>>Rating ↓</option>
            <option value="weight-asc" <?= ($_GET['sort'] ?? '') === 'weight-asc' ? 'selected' : '' ?>>Weight ↑</option>
            <option value="weight-desc" <?= ($_GET['sort'] ?? '') === 'weight-desc' ? 'selected' : '' ?>>Weight ↓</option>
        </select>
        <button type="submit">Load Collection</button>
    </form>
    <?php
    // Sorting logic
    $sort = $_GET['sort'] ?? '';

    if ($sort === 'rating-asc') {
        usort($games, fn($a, $b) => floatval($a['average']) <=> floatval($b['average']));
    } elseif ($sort === 'rating-desc') {
        usort($games, fn($a, $b) => floatval($b['average']) <=> floatval($a['average']));
    } elseif ($sort === 'weight-asc') {
        usort($games, fn($a, $b) => floatval($a['avgweight']) <=> floatval($b['avgweight']));
    } elseif ($sort === 'weight-desc') {
        usort($games, fn($a, $b) => floatval($b['avgweight']) <=> floatval($a['avgweight']));
    }
    ?>

    <?php if ($games): ?>
        <div class="collection">
            <?php foreach ($games as $game): ?>
                <div class="game">
                    <img src="<?=htmlspecialchars($game['image'])?>" alt="" width="300">
                    <div class="info">
                        <strong><?=htmlspecialchars($game['name'])?></strong>
                        <span><i class="fa-solid fa-calendar"></i> <?=htmlspecialchars($game['year'])?></span>
                        <?php
                            $rating = floatval($game['average']);
                            if ($rating < 6) {
                                $ratingClass = 'rating-low';
                            } elseif ($rating < 8) {
                                $ratingClass = 'rating-mid';
                            } else {
                                $ratingClass = 'rating-high';
                            }
                        ?>
                        <div class="<?= $ratingClass ?>"><i class="fa-solid fa-star"></i> <?=htmlspecialchars($game['average'])?></div>
                        <div><i class="fa-solid fa-user-group"></i> <?=htmlspecialchars($game['minplayers'])?>-<?=htmlspecialchars($game['maxplayers'])?> players</div>
                        <?php
                            $weight = floatval($game['avgweight']);
                            if ($weight < 2) {
                                $weightClass = 'weight-easy';
                            } elseif ($weight < 3) {
                                $weightClass = 'weight-medium';
                            } elseif ($weight < 4) {
                                $weightClass = 'weight-hard';
                            } else {
                                $weightClass = 'weight-extreme';
                            }
                        ?>
                        <div class="<?= $weightClass ?>"><i class="fa-solid fa-scale-balanced"></i> <?=htmlspecialchars($game['avgweight'])?> / 5</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>