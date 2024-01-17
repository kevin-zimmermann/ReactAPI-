<?php

#[AllowDynamicProperties] class Quotes extends Bdd
{
    private $id;
    public $quote;
    private $date;
    private $user_id;

    public function __construct()
    {
        try {
            $this->bdd = new PDO($this->getPdo(), $this->getUsername());
            $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

//    /**
//     * @return mixed
//     */
//    public function getDate()
//    {
//        return $this->date;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getQuote()
//    {
//        return $this->quote;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getId()
//    {
//        return $this->id;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getUserId()
//    {
//        return $this->user_id;
//    }
//
//    /**
//     * @param mixed $quote
//     */
//    public function setQuote($quote): void
//    {
//        $this->quote = $quote;
//    }
//
//    /**
//     * @param mixed $date
//     */
//    public function setDate($date): void
//    {
//        $this->date = $date;
//    }
//
//    /**
//     * @param mixed $user_id
//     */
//    public function setUserId($user_id): void
//    {
//        $this->user_id = $user_id;
//    }

    public function createQuote()
    {
        $response = ['success' => '', 'err' => ''];
        $userToken = new UserToken();
        $userToken->AuthBearerTokenVerify();
        $user_id = intval($userToken->AuthBearerTokenVerify()->user_id);
        $date = time();

        if(!empty($_POST['quote']) && strlen($_POST['quote']) < 1000){
            $stmtInsert = $this->bdd->prepare('INSERT INTO quotes (quote,user_id,date) VALUES(?,?,?)');
            $stmtInsert->execute([$_POST['quote'], $user_id, $date]);
            $response['success'] .= 'Citation bien enregistré !';

        }elseif (strlen($_POST['quote']) > 1000){
            $response['err'] .= 'Votre citation a plus de 1000 caractères!';
        }elseif (empty($_POST['quote'])){
            $response['err'] .= 'Votre citation est vide...';
        }
        return $response;
    }
    public function modifyQuote(){

        $response = [];
        $userToken = new UserToken();
        $id = intval($userToken->AuthBearerTokenVerify()->user_id);

        if (isset($_POST['quote'])) {

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

                        $stmtInsert = $this->bdd->prepare('UPDATE quotes SET is_modified = ? ,quote = ? WHERE id = ? AND user_id = ? ');
                        $stmtInsert->execute([1, $password, $email, $id]);
                        $response['err'] = 'Citation bien mis à jour !';

                    }
                }
            }

        }
        return $response;
    }
    public function showAllQuotes(){

        $stmt = $this->bdd->prepare('SELECT * FROM quotes');
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function showQuotes(){
        $oneDayinSeconds = time() + 86400;
        $stmt = $this->bdd->prepare('SELECT * FROM quotes WHERE date < ?');
        $stmt->execute([$oneDayinSeconds]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    public function showQuotesByUser($id){
        $stmt = $this->bdd->prepare('SELECT * FROM quotes WHERE id_user = ?');
        $stmt->execute([$id]);
    }
    public function deleteQuote($id){

        $stmt = $this->bdd->prepare('DELETE FROM quotes WHERE id = ?');
        $stmt->execute([$id]);

        $response['status'] = "Citation supprimé";
    }

}