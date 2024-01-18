<?php


#[AllowDynamicProperties] class UserToken extends Bdd
{
    private string $secret_key;

    public function __construct()
    {
        $this->secret_key = SECRET_KEY;
        try {
            $this->bdd = new PDO($this->getPdo(), $this->getUsername());
            $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }


    public function getAuthorizationToken()
    {
        $headers = getallheaders();

        if (in_array('Authorization', $headers)) {
            return $headers['Authorization'];
        }

        return $this->deleteBearerFromToken($headers['Authorization']) ?? '';
    }

    private function deleteBearerFromToken($token)
    {

        if($token && str_starts_with($token,'Bearer ')){
            $token = substr($token, 7);
        }
        return $token;


    }

    public function AuthBearerTokenVerify()
    {
        $response = [];
        $token = $this->getAuthorizationToken();


        if (empty($token)) {
            // Token not provided, return an error or redirect to the login page
            http_response_code(401); // Unauthorized
            exit;
        }

        try {
            $decodedToken = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($this->secret_key, 'HS256'));
            $stmt = $this->bdd->prepare('SELECT COUNT(*) FROM users WHERE login = ? AND id = ? AND email = ?');
            $stmt->execute([$decodedToken->login, $decodedToken->user_id, $decodedToken->email]);
            $result = $stmt->fetch(PDO::FETCH_NUM);

            if ($result === 0) {
                $response['is_user'] = false;
            } else {
                $response['is_user'] = true;
                $response['infoUser'] = $decodedToken;

            }
            // Check token expiration
            if ($decodedToken->login === '') {

                http_response_code(401); // Token expired
                exit;
            }

        } catch (Exception $e) {
//          /* $e->getMessage();*/
            http_response_code(401); // Unauthorized (Invalid token or signature)
            exit;
        }
        return $response;

    }

}