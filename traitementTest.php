<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'vendor/autoload.php';
require_once 'class/Autoloader.php';


use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

//$bdd = new PDO('mysql:dbname=test;host=localhost', 'root', '');
$secret_key = "KZ2023LpTM";
//
$message = [];
$message["err"] = [];
$quotes = new Quotes();


function getAuthorizationToken()
{
    $headers = getallheaders();

    if (in_array('Authorization', $headers)) {
        return $headers['Authorization'];
    }

    $token = $headers['Authorization'] ?? '';
    return $token;
}

function AuthBearerTokenVerify($secret_key)
{

    $token = getAuthorizationToken();

    if (!$token) {
        // Token not provided, return an error or redirect to the login page
        http_response_code(401); // Unauthorized
        exit;
    }

    try {

        $decodedToken = JWT::decode($token, new Key($secret_key, 'HS256'));

        // Check token expiration
        if ($decodedToken->login === '') {
            http_response_code(401); // Token expired
            exit;
        }

    } catch (Exception $e) {
        http_response_code(401); // Unauthorized (Invalid token or signature)
        exit;
    }
    return $decodedToken;

}

if (isset($_GET['users'])) {
    $stmt = $bdd->prepare('SELECT * FROM users');
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $message['data'] = $results;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentJson = file_get_contents('php://input');
    $_POST = json_decode($contentJson, true);

    //DELETE an user
    if (isset($_POST['deleteUser'])) {
        $idUserToDelete = intval($_POST['deleteUser']);
        $stmt = $bdd->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$idUserToDelete]);

        $message['status'] = "Utilisateur supprimé";

    }

//    if (isset($_POST['page'])) {
    // REGISTER
    if ($_POST['page'] === 'register') {
        if ($_POST['login'] === "" && $_POST['password'] === "" && $_POST['email'] === "") {
            $message["err"][] = "Veuillez remplir les champs";
//            $message['err'][] .= "Veuillez remplir les champs";
        } else {
            if (isset($_POST['login'])) {
                if (strlen($_POST['login']) <= 3) {
                    $message['err'][] .= 'login trop court';
                } else {
                    $login = htmlspecialchars($_POST['login']);
                    $stmt = $bdd->prepare('SELECT * FROM users WHERE login = ?');
                    $stmt->execute([$login]);
                    if ($stmt->rowCount() > 0) {
                        $message["err"][] = $login . ' existe déjà';

                    }
                    if ($_POST['email'] && $_POST['password']) {
                        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                            $email = htmlspecialchars($_POST['email']);
                            $password = $_POST['password'];

                            $password = password_hash($password, PASSWORD_BCRYPT);

                            $stmt = $bdd->prepare('SELECT * FROM users WHERE email = ?');
                            $stmt->execute([$email]);
                            if ($stmt->rowCount() > 0) {

                                $message['err'][] .= $email . ' existe déjà';

                            } else {
                                $stmtInsert = $bdd->prepare('INSERT INTO users (login,password,email) VALUES(?,?,?)');
                                $stmtInsert->execute([$login, $password, $email]);
                                $message['success']['status'] .= 'utilisateur bien enregistré !';
                            }

                        } else {
                            $message['err'][] .= "Vous nous avez pas fourni un email";
                        }

                    } else {
                        $message['err'][] .= 'Veuillez remplir le MDP et/ou l\'email';
                    }
                }
            }

        }
    }
    // LOGIN
    if ($_POST['page'] === 'connexion') {
        if (isset($_POST['login'])) {
            $login = htmlspecialchars($_POST['login']);
            $stmt = $bdd->prepare('SELECT * FROM users WHERE login = ?');
            $stmt->execute([$login]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() == 0) {
                $message['err'][] = 'Aucun utilisateur a été trouvé';

            } else {
                if ($_POST['password']) {
                    $password = $_POST['password'];
                    if (password_verify($password, $result['password'])) {
                        //GENERATE TOKEN
                        $payload = [
                            'user_id' => $result['id'],
                            'login' => $result['login'],
                            'email' => $result['email'],
//                            'timeToConnect' => new DateTime()
                        ];

                        // Clé secrète pour signer le token

                        // Génération du token
                        $token = JWT::encode($payload, $secret_key, 'HS256');
                        $message['token'] = $token;
                    } else {
                        $message['err'][] = 'Aucun utilisateur a été trouvé';
                    }

                }

            }
        }

    }
    // REGISTER
    if ($_POST['page'] === 'profil') {

        $id = intval(AuthBearerTokenVerify($secret_key)->user_id);


        if (isset($_POST['login'])) {

            if (strlen($_POST['login']) <= 3) {
                $message['err'] = 'login trop court';
            } else {
                $login = htmlspecialchars($_POST['login']);
                $stmt = $bdd->prepare('SELECT * FROM users WHERE login = ?');
                $stmt->execute([$login]);
                if ($stmt->rowCount() > 0) {
                    $message['err'] = $login . ' existe déjà';

                } else {
                    $message[] = $login . ' n existe pas';
                    if ($_POST['email'] && $_POST['password']) {

                        $email = htmlspecialchars($_POST['email']);
                        $password = $_POST['password'];


                        $password = password_hash($password, PASSWORD_BCRYPT);

                        $stmtInsert = $bdd->prepare('UPDATE users SET login = ? ,password = ?, email= ? WHERE id = ? ');
                        $stmtInsert->execute([$login, $password, $email, $id]);
                        $message['err'] = 'utilisateur bien mis à jour !';
                        $message['status'] = true;


                    }
                }
            }

        }
    }
    // Check si c'est un user
    if ($_POST['page'] === 'isUser') {
        AuthBearerTokenVerify($secret_key);
        //Verification TOKEN
        $token = getAuthorizationToken();
        if ($token) {
            $infoUser = JWT::decode($token, new Key($secret_key, 'HS256'));
            $stmt = $bdd->prepare('SELECT COUNT(*) FROM users WHERE login = ? AND id = ? AND email = ?');
            $stmt->execute([$infoUser->login, $infoUser->user_id, $infoUser->email]);
            $result = $stmt->fetch(PDO::FETCH_NUM);

            if ($result === 0) {
                $message['is_user'] = false;
            } else {
                $message['is_user'] = true;
                $message['infoUser'] = $infoUser;

            }
        } else {
            $message['is_user'] = false;
        }
    }
    if ($_POST['page'] === 'getUser') {
        AuthBearerTokenVerify($secret_key);

        $id = $_POST['id'];

        $stmt = $bdd->prepare('SELECT id,login,email FROM users WHERE id = ? ');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $message['result'] = "Aucun utilisateur ne correspond";
        } else {
            $message['result'] = $result;
        }
    }

//    if ($_POST['page'] === 'createQuote') {
////        AuthBearerTokenVerify($secret_key);
//        $message['err'] = "ah";
//    }

}

echo json_encode($message);