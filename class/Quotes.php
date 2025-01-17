<?php

#[AllowDynamicProperties] class Quotes extends Bdd
{

    public function __construct()
    {
        try {
            $this->bdd = new PDO($this->getPdo(), $this->getUsername());
            $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function createQuote()
    {
        $response = ['success' => '', 'err' => ''];
        $user = new Users();
        $infoUser = $user->isUser();

        if ($infoUser['is_user'] === true) {
            $date = time();

            if (!empty($_POST['quote']) && strlen($_POST['quote']) < 1000) {
                $stmtInsert = $this->bdd->prepare('INSERT INTO quotes (quote,user_id,date) VALUES(?,?,?)');
                $stmtInsert->execute([$_POST['quote'], $infoUser['infoUser']->user_id, $date]);
                $response['success'] .= 'Citation bien enregistré !';

            } elseif (strlen($_POST['quote']) > 1000) {
                $response['err'] .= 'Votre citation a plus de 1000 caractères!';
            } elseif (empty($_POST['quote'])) {
                $response['err'] .= 'Votre citation est vide...';
            }

        }
        return $response;

    }

    public function modifyQuote()
    {

        $response = ['success' => [], 'err' => []];
        $user = new Users();
        $infoUser = $user->isUser();
        if ($infoUser && $infoUser['is_user'] === true) {
            if (isset($_POST['quote']) && !empty($_POST['idQuoteToEdit'])) {
                if (strlen($_POST['quote']) === 0) {
                    $response['err'] = 'Aucune citation !';
                } else {
                    $stmt = $this->bdd->prepare('SELECT * FROM quotes WHERE id = ?');
                    $stmt->execute([$_POST['idQuoteToEdit']]);
                    if ($stmt->rowCount() === 0) {
                        $response['err'] = 'Aucune citation existe !';
                    } else {
                        $stmtInsert = $this->bdd->prepare('UPDATE quotes SET is_modified = ? ,quote = ? WHERE id = ? AND user_id = ? ');
                        $stmtInsert->execute([1, $_POST['quote'], $_POST['idQuoteToEdit'], $infoUser['infoUser']->user_id]);
                        $response['success'] = 'Citation bien mis à jour !';

                    }
                }
            }
        }

        return $response;
    }

    public function showAllQuotes()
    {

        $stmt = $this->bdd->prepare('SELECT * FROM quotes');
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function showQuotes()
    {
        $oneDayinSeconds = time() - 86400;
        $stmt = $this->bdd->prepare('SELECT quotes.*, users.login AS username FROM quotes INNER JOIN users ON quotes.user_id = users.id WHERE quotes.date > ? ORDER BY quotes.date DESC');
        $stmt->execute([$oneDayinSeconds]);
        if ($stmt->rowCount() !== 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    }

    public function showQuotesByUser()
    {
        $userToken = new UserToken();
        $user = $userToken->AuthBearerTokenVerify();
        $token = $userToken->getAuthorizationToken();

        //Verification TOKEN
        if ($user && $token) {
            $stmt = $this->bdd->prepare('SELECT * FROM quotes WHERE user_id = ? ORDER BY date DESC');
            $stmt->execute([$user['infoUser']->user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function deleteQuote()
    {
        $response = [];
        $user = new Users();
        $infoUser = $user->isUser();
        if ($infoUser && $infoUser['is_user'] === true) {
            $stmt = $this->bdd->prepare('DELETE FROM quotes WHERE id = ? AND user_id = ?');
            $stmt->execute([$_POST['idQuoteToDelete'], $infoUser['infoUser']->user_id]);
            $response['status'] = "Citation supprimé";
        }
        return $response;

    }

}