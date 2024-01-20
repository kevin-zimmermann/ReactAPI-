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
        $response = ['success' => [], 'err' => [], 'status' => [], 'data' => []];

        if ($_POST['login'] === "" && $_POST['password'] === "" && $_POST['email'] === "") {
            $response["err"][] = "Veuillez remplir les champs";
        } else {
            if (!empty($_POST['login'])) {
                if (strlen($_POST['login']) <= 3) {
                    $response['err'][] .= 'Login trop court';
                } else {
                    $login = $_POST['login'];
                    $stmt = $this->bdd->prepare('SELECT * FROM users WHERE login = ?');
                    $stmt->execute([$login]);
                    if ($stmt->rowCount() > 0) {
                        $response["err"][] = $login . ' existe déjà';

                    }
                }
            } else {
                $response["err"][] = 'Vous n\'avez pas rentré de login';
            }

        }
        if (!empty($_POST['email'])) {
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $email = $_POST['email'];
                $stmt = $this->bdd->prepare('SELECT * FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {

                    $response['err'][] .= $email . ' existe déjà';

                }
            } else {
                $response['err'][] .= "Email au mauvais format !";
            }
        } else {
            $response['err'][] .= "Vous nous avez pas fourni un email";
        }

        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            $password_regex = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-+]).{8,}$/";
            if (!preg_match($password_regex, $password)) {
                $response['err'][] .= 'Votre mot de passe doit contenir au moins 8 caractères, une lettre en majuscule, une lettre en minuscule, un chiffre et un caractère spécifique';
            } else {
                $password = password_hash($password, PASSWORD_BCRYPT);
            }
        } else {
            $response['err'][] .= "Vous nous avez pas fourni de mot de passe";
        }

        if (empty($response['err'])) {

            $stmtInsert = $this->bdd->prepare('INSERT INTO users (login,password,email) VALUES(?,?,?)');
            $stmtInsert->execute([$login, $password, $email]);
            $response['success'] .= 'utilisateur bien enregistré !';
        }


        return $response;

    }


    public
    function login()
    {

        $response = ['success' => [], 'err' => [], 'status' => [], 'data' => []];
        $userToken = new UserToken();

        if (!empty($_POST['login'])) {
            $login = $_POST['login'];
            $stmt = $this->bdd->prepare('SELECT * FROM users WHERE login = ?');
            $stmt->execute([$login]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() == 0) {
                $response['err'][] = 'Aucun utilisateur a été trouvé';

            } else {
                if (!empty($_POST['password'])) {
                    $password = $_POST['password'];

                    if (password_verify($password, $result['password']) && empty($response['err'])) {
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

                } else {
                    $response['err'][] = 'Vous n\'avez entré aucun MDP';
                }
            }

        } else {
            $response['err'][] = 'Vous n\'avez entré aucun login';

        }

        return $response;
    }

    public
    function editProfil()
    {
        $response = ['success' => [], 'err' => [], 'status' => [], 'data' => []];
        $infoUser = $this->isUser();
        if ($infoUser && $infoUser['is_user'] === true) {
            $id = $infoUser['infoUser']->user_id;

            if (isset($_POST['login']) && isset($_POST['email'])) {
                if (!empty($_POST['email'])) {
                    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        $email = $_POST['email'];
                        $stmt = $this->bdd->prepare('SELECT * FROM users WHERE email = ? AND id != ?');
                        $stmt->execute([$_POST['email'], $id]);
                        if ($stmt->rowCount() != 0) {
                            $response['err'][] .= $_POST['email'] . ' existe déjà';
                        }

                    } else {
                        $response['err'][] .= $_POST['email'] . ' n\'est pas un mail';
                    }
                } else {
                    $response['err'][] .= 'Aucun mail présent';
                }


                if (!empty($_POST['login'])) {
                    if (strlen($_POST['login']) <= 3) {
                        $response['err'][] .= 'Login trop court';
                    } else {
                        $login = $_POST['login'];
                        $stmt = $this->bdd->prepare('SELECT * FROM users WHERE login = ? AND id != ?');
                        $stmt->execute([$login, $id]);
                        if ($stmt->rowCount() > 0) {
                            $response['err'][] .= $login . ' existe déjà';
                        }
                    }

                } else {
                    $response['err'][] .= 'Aucun Login n\'est présent';
                }

                if (empty($response['err'])) {
                    $stmtInsert = $this->bdd->prepare('UPDATE users SET login = ?, email= ? WHERE id = ? ');
                    $stmtInsert->execute([$login, $email, $id]);
                    $response['status'][] .= 'Utilisateur bien mis à jour !';
                }

            }
        } else {
            $response['err'][] .= 'Un problème a été detecté, veuillez réessayer !';
        }

        return $response;
    }

    public function changePassword()
    {
        $response = ['success' => [], 'err' => [], 'status' => [], 'data' => []];
        $infoUser = $this->isUser();
        if ($infoUser && $infoUser['is_user'] === true) {
            $id = intval($infoUser['infoUser']->user_id);
            if (!empty($_POST["password"]) && !empty($_POST["confPassword"])) {
                $password = $_POST['password'];
                $confPassword = $_POST['confPassword'];
                if ($password === $confPassword) {
                    $password_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/";
                    if (!preg_match($password_regex, $password)) {
                        $response['err'][] .= 'Votre mot de passe doit contenir au moins 8 caractères, une lettre en majuscule, une lettre en minuscule, un chiffre et un caractère spécifique';
                    } else {
                        $password = password_hash($password, PASSWORD_BCRYPT);

                    }
                } else {
                    $response['err'][] .= 'Vos MDP ne correspondent pas !';
                }

            } else {
                $response['err'][] .= 'Aucun MDP n\'est présent';
            }
            if (empty($response['err'])) {
                $stmtInsert = $this->bdd->prepare('UPDATE users SET password = ? WHERE id = ? ');
                $stmtInsert->execute([$password, $id]);
                $response['status'][] .= 'Utilisateur bien mis à jour !';
            }
        } else {
            $response['err'][] .= 'Un problème a été detecté, veuillez réessayer !';
        }
        return $response;
    }

    public
    function isUser()
    {
        $userToken = new UserToken();
        return $userToken->AuthBearerTokenVerify();
    }

    public
    function getUserById()
    {
        $response = [];
        $userToken = new UserToken();
        $userToken->AuthBearerTokenVerify();

        if ($_POST['id']) {
            $id = $_POST['id'];
            $stmt = $this->bdd->prepare('SELECT id,login,email FROM users WHERE id = ? ');
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result === false) {
                $response['data'] = "Aucun utilisateur ne correspond";
            } else {
                $response['data'] = $result;
            }
        }
        return $response;
    }

    public
    function getAllUsers()
    {
        $stmt = $this->bdd->prepare('SELECT id,login,email FROM users');
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['data'] = $results;
        return $response;
    }

    public
    function deleteUser()
    {
        $response = ['success' => [], 'err' => [], 'status' => [], 'data' => []];

        if (!empty($_POST['deleteUser'])) {
            $idUserToDelete = intval($_POST['deleteUser']);
            $stmt = $this->bdd->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$idUserToDelete]);

            $response['status'] = "Utilisateur supprimé";
        }


        return $response;
    }

}