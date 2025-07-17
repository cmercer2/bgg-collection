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
<meta name=“viewport” content=“width=device-width, initial-scale=1” />
<html>
<head>
    <title>My BGG Collection</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <h1><a href="index.php">My Boardgame Collection</a></h1>
    <form method="get">
        <div class="options">
            <div class="checkbox-wrapper-1">
                <input id="ex_checkbox" type="checkbox" class="substituted" name="include_expansions" value="1" <?= isset($_GET['include_expansions']) ? 'checked' : '' ?>>
                <label for="ex_checkbox">Include Expansions</label>
            </div>
            <div class="players-filter">
                <label for="players">Number of Players:</label>
                <select name="players" id="players">
                    <option value="">Any</option>
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="<?= $i ?>" <?= ($_GET['players'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="sort-options">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort">
                    <option value="">None</option>
                    <option value="rating-asc" <?= ($_GET['sort'] ?? '') === 'rating-asc' ? 'selected' : '' ?>>Rating ↑</option>
                    <option value="rating-desc" <?= ($_GET['sort'] ?? '') === 'rating-desc' ? 'selected' : '' ?>>Rating ↓</option>
                    <option value="weight-asc" <?= ($_GET['sort'] ?? '') === 'weight-asc' ? 'selected' : '' ?>>Weight ↑</option>
                    <option value="weight-desc" <?= ($_GET['sort'] ?? '') === 'weight-desc' ? 'selected' : '' ?>>Weight ↓</option>
                    <option value="year-asc" <?= ($_GET['sort'] ?? '') === 'year-asc' ? 'selected' : '' ?>>Year ↑</option>
                    <option value="year-desc" <?= ($_GET['sort'] ?? '') === 'year-desc' ? 'selected' : '' ?>>Year ↓</option>
                </select>
            </div>
        </div>
        <button class="button-load" role="button" type="submit">Load Collection</button>
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
    } elseif ($sort === 'year-asc') {
        usort($games, fn($a, $b) => intval($a['year']) <=> intval($b['year']));
    } elseif ($sort === 'year-desc') {
        usort($games, fn($a, $b) => intval($b['year']) <=> intval($a['year']));
    }
    ?>

    <?php if ($games): ?>
        <div class="collection">
            <?php foreach ($games as $game): ?>
                <div class="game">
                    <a href="https://boardgamegeek.com/boardgame/<?= htmlspecialchars($game['id']) ?>" target="_blank">
                        <img src="<?=htmlspecialchars($game['image'])?>" alt="" width="300">
                    </a>
                    <div class="info">
                        <strong>
                            <a href="https://boardgamegeek.com/boardgame/<?= htmlspecialchars($game['id']) ?>" target="_blank">
                                <?=htmlspecialchars($game['name'])?>
                            </a>
                        </strong>
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
                        <div><i class="fa-solid fa-clock"></i> <?=htmlspecialchars($game['playtime'])?> mins</div>
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