<?php
session_start();

//Pas obligatoire

//header('Access-Control-Allow-Origin: http://localhost:3000');
//header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
//header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');


//Connexion à la BDD
$bdd = new PDO('mysql:dbname=test;host=localhost', 'root', '');
//Cette variable va contenir tout ce qui sera contenu dans le data

$message = [];

// Partie de la requête qui est commune peu importe les paramètres choisis
$query = "SELECT products.name, products.id, products.price FROM products INNER JOIN categories ON products.category_id = categories.id ";
// Array qui va contenir tous les $_GET
$params = [];

//Ajout des éléments de la requête en fonction de ce que l'utilisateur veut filtrer
if ($_GET != []) {
    $query .= " WHERE";
    if (isset($_GET['prix'])) {
        $prix = $_GET['prix'];

        if (count($prix) == 2) {
            $query .= " products.price BETWEEN ? AND ?";
            $params[] = $prix[0];
            $params[] = $prix[1];
        } else {
            $query .= " products.price <= ?";
            $params[] = $prix[0];
        }
    }
    if (isset($_GET['categorie']) && isset($_GET['prix'])) {
        $query .= "AND";
    }

    if (isset($_GET['categorie'])) {
        $catIds = $_GET['categorie'];
        $query .= "(";

        if (count($catIds) === 1) {
            $query .= " categories.id = ?";
            $params[] = $catIds[0];
        } else {
            foreach ($catIds as $key => $catId) {
                if ($key == 0) {
                    $query .= " categories.id = ?";
                } else {
                    $query .= " OR categories.id = ?";
                }
                $params[] = $catId;
            }
        }
        $query .= ")";
    }
}
$stmt = $bdd->prepare($query);
$stmt->execute($params);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$message['products'] = $results;

//// On renvoie le contenu de notre array ($message) sous format JSON pour être interprétable par la réponse et pouvoir exploiter les informations qui y sont contenues
echo json_encode($message);
