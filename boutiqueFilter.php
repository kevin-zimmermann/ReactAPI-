<?php
$bdd = new PDO('mysql:dbname=test;host=localhost', 'root', '');
$stmt = $bdd->prepare('SELECT * FROM categories');
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtPrice = $bdd->prepare('SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM products');
$stmtPrice->execute();
$resultsPrice= $stmtPrice->fetchAll(PDO::FETCH_ASSOC);



?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Title</title>
</head>
<body>
<form id="formFilter">
    <div id="filter">
        <div id="filterCat">
            <h2>Catégorie : </h2>

            <?php
                foreach ($results as $result) { ?>
                    <input type="checkbox" name="categorie[]" value="<?= $result['id'] ?>"><?= $result['name'] ?>
                <?php }
                ?>
        </div>
        <div id="filterPrice">
            <h2>Prix: </h2>
            <input type="number" name="prix[]" min="<?= intval($resultsPrice[0]['min_price'])?>" max="<?= intval($resultsPrice[0]['max_price'])?>">
            <input type="number" name="prix[]" min="<?= intval($resultsPrice[0]['min_price'])?>" max="<?= intval($resultsPrice[0]['max_price'])?>">
        </div>
    </div>
</form>


</body>
</html>
<script src="filter.js"></script>
