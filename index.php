<?php
require_once("bgg.php");

$username = $_GET['username'] ?? '';
$games = [];

if ($username) {
    $games = fetch_bgg_collection($username);
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
        <input type="text" name="username" placeholder="BGG Username" value="<?=htmlspecialchars($username)?>">
        <button type="submit">Load Collection</button>
    </form>

    <?php if ($games): ?>
        <div class="collection">
            <?php foreach ($games as $game): ?>
                <div class="game">
                    <img src="<?=htmlspecialchars($game['image'])?>" alt="">
                    <div class="info">
                        <strong><?=htmlspecialchars($game['name'])?></strong>
                        <span>(<?=htmlspecialchars($game['year'])?>)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($username): ?>
        <p>Failed to load collection for user <strong><?=htmlspecialchars($username)?></strong>.</p>
    <?php endif; ?>
</body>
</html>