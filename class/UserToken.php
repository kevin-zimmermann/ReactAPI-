<?php


class UserToken
{
    private string $secret_key;

    public function __construct()
    {
        $this->secret_key = SECRET_KEY;

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

        return $headers['Authorization'] ?? '';
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

        $token = $this->getAuthorizationToken();


        if (empty($token)) {
            // Token not provided, return an error or redirect to the login page
            http_response_code(401); // Unauthorized
            exit;
        }

        try {
            $newToken = $this->deleteBearerFromToken($token);
            $decodedToken = \Firebase\JWT\JWT::decode($newToken, new \Firebase\JWT\Key($this->secret_key, 'HS256'));

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
        return $decodedToken;

    }

}