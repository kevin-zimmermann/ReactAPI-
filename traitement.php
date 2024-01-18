<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
const SECRET_KEY = 'KZ2023LpTM';
require_once 'vendor/autoload.php';
require_once 'Autoloader.php';
//
//use Firebase\JWT\Key;
//use Firebase\JWT\JWT;

$bdd = new Bdd();
//$bdd = new PDO('mysql:dbname=reactapi;host=localhost', 'root', '');

//
$response = [];
$response["err"] = [];
$response["success"] = [];

$quotes = new Quotes();
$userToken = new UserToken();
$user = new Users();


//if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//    if (isset($_GET['users'])) {
//        $response = $user->getAllUsers();
//    }
//}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentJson = file_get_contents('php://input');
    $_POST = json_decode($contentJson, true);
    $bdd->clearInput();

    // REGISTER
    if ($_POST['page'] === 'register') {
        $response = $user->register();
    }
    // LOGIN
    if ($_POST['page'] === 'connexion') {
        $response = $user->login();

    }
    // REGISTER
    if ($_POST['page'] === 'profil') {
        $response = $user->editProfil();
    }
    // Check si c'est un user
    if ($_POST['page'] === 'isUser') {
        $response = $user->isUser();
    }
    //Avoir les infos d'un utilisateur
    if ($_POST['page'] === 'getUser') {
        $response = $user->getUserById();
    }
    //DELETE an user
    if (isset($_POST['deleteUser'])) {
        $response = $user->deleteUser();
    }

    //Poster une citation
    if ($_POST['page'] === 'createQuote') {
        $response = $quotes->createQuote();
    }

    //Get last 24hours citations
    if ($_POST['page'] === 'getOneDayQuotes') {
        $response = $quotes->showQuotes();
    }
    if ($_POST['page'] === 'showQuotesByUser') {
        $response = $quotes->showQuotesByUser();
    }
    if ($_POST['page'] === 'deleteQuote') {
        $response = $quotes->deleteQuote();
    }

}

echo json_encode($response);