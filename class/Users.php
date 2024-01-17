<?php

#[AllowDynamicProperties] class Users extends Bdd
{
    private $id;
    public $login;
    public $email;
    private $password;

    public function __construct()
    {
        try {
            $this->bdd = new PDO($this->getPdo(), $this->getUsername());
            $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
    public function register()
    {
        $response= [];
        if ($_POST['login'] === "" && $_POST['password'] === "" && $_POST['email'] === "") {
            $response["err"][] = "Veuillez remplir les champs";
//            $response['err'][] .= "Veuillez remplir les champs";
        } else {
            if (isset($_POST['login'])) {
                if (strlen($_POST['login']) <= 3) {
                    $response['err'][] .= 'login trop court';
                } else {
                    $login = $_POST['login'];
                    $stmt = $this->bdd->prepare('SELECT * FROM users WHERE login = ?');
                    $stmt->execute([$login]);
                    if ($stmt->rowCount() > 0) {
                        $response["err"][] = $login . ' existe déjà';

                    }
                    if ($_POST['email'] && $_POST['password']) {
                        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                            $email = $_POST['email'];
                            $password = $_POST['password'];

                            $password = password_hash($password, PASSWORD_BCRYPT);

                            $stmt = $this->bdd->prepare('SELECT * FROM users WHERE email = ?');
                            $stmt->execute([$email]);
                            if ($stmt->rowCount() > 0) {

                                $response['err'][] .= $email . ' existe déjà';

                            } else {
                                $stmtInsert = $this->bdd->prepare('INSERT INTO users (login,password,email) VALUES(?,?,?)');
                                $stmtInsert->execute([$login, $password, $email]);
                                $response['success']['status'] .= 'utilisateur bien enregistré !';
                            }

                        } else {
                            $response['err'][] .= "Vous nous avez pas fourni un email";
                        }

                    } else {
                        $response['err'][] .= 'Veuillez remplir le MDP et/ou l\'email';
                    }
                }
            }

        }
        return $response;

    }
    public function login(){

        $response= [];
        $userToken = new UserToken();
        if (isset($_POST['login'])) {
            $login = $_POST['login'];
            $stmt =  $this->bdd->prepare('SELECT * FROM users WHERE login = ?');
            $stmt->execute([$login]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() == 0) {
                $response['err'][] = 'Aucun utilisateur a été trouvé';

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
                        $token = \Firebase\JWT\JWT::encode($payload, $userToken->getSecretKey(), 'HS256');
                        $response['token'] = $token;

                    } else {
                        $response['err'][] = 'Aucun utilisateur a été trouvé';
                    }

                }

            }
        }
        return $response;
    }

    public function editProfil(){
        $response= [];
        $userToken = new UserToken();
        $id = intval($userToken->AuthBearerTokenVerify()->user_id);

        if (isset($_POST['login'])) {

            if (strlen($_POST['login']) <= 3) {
                $response['err'] = 'login trop court';
            } else {
                $login = $_POST['login'];
                $stmt = $this->bdd->prepare('SELECT * FROM users WHERE login = ?');
                $stmt->execute([$login]);
                if ($stmt->rowCount() > 0) {
                    $response['err'] = $login . ' existe déjà';

                } else {
                    $response[] = $login . ' n existe pas';
                    if ($_POST['email'] && $_POST['password']) {

                        $email = ($_POST['email']);
                        $password = $_POST['password'];


                        $password = password_hash($password, PASSWORD_BCRYPT);

                        $stmtInsert = $this->bdd->prepare('UPDATE users SET login = ? ,password = ?, email= ? WHERE id = ? ');
                        $stmtInsert->execute([$login, $password, $email, $id]);
                        $response['err'] = 'utilisateur bien mis à jour !';
                        $response['status'] = true;

                    }
                }
            }

        }
        return $response;
    }
    
    public function isUser(){
        $response= [];
        $userToken = new UserToken();
        $userToken->AuthBearerTokenVerify();
        //Verification TOKEN
        $token = $userToken->getAuthorizationToken();
        if ($token) {
            $infoUser = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($userToken->getSecretKey(), 'HS256'));
            $stmt = $this->bdd->prepare('SELECT COUNT(*) FROM users WHERE login = ? AND id = ? AND email = ?');
            $stmt->execute([$infoUser->login, $infoUser->user_id, $infoUser->email]);
            $result = $stmt->fetch(PDO::FETCH_NUM);

            if ($result === 0) {
                $response['is_user'] = false;
            } else {
                $response['is_user'] = true;
                $response['infoUser'] = $infoUser;

            }
        } else {
            $response['is_user'] = false;
        }
        return $response;
    }

    public function getUserById(){
        $response= [];
        $userToken = new UserToken();
        $userToken->AuthBearerTokenVerify();

        $id = $_POST['id'];

        $stmt = $this->bdd->prepare('SELECT id,login,email FROM users WHERE id = ? ');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $response['result'] = "Aucun utilisateur ne correspond";
        } else {
            $response['result'] = $result;
        }
        return $response;
    }
    public function getAllUsers(){
        $stmt = $this->bdd->prepare('SELECT id,login,email FROM users');
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['data'] = $results;
        return $response;
    }
    public function deleteUser(){
        $idUserToDelete = intval($_POST['deleteUser']);
        $stmt = $this->bdd->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$idUserToDelete]);

        $response['status'] = "Utilisateur supprimé";

        return $response;
    }
    
}