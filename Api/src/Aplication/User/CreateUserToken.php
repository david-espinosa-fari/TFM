<?php


namespace App\Aplication\User;


//use App\Domain\Users\Error\UserErrorException;
use App\Domain\Users\User;
//use Firebase\JWT\JWT;
//use Firebase\JWT\SignatureInvalidException;

final class CreateUserToken
{
    private $tokenEncoded;
    private $key;

    public function __construct(User $user)
    {
        $this->key = $_SERVER['APP_SECRET'];

        /*$token = array(
            'ref' => 'meteosalle.local',
            'username' => (string)$user,
            'lastname' => $user->getLastname()
        );*/
        //$this->tokenEncoded = JWT::encode($token, $this->key);
        $this->tokenEncoded = base64_encode(password_hash((string)$user, PASSWORD_BCRYPT, array('cost'=>12)));
        $_SESSION['username'] = (string)$user;

    }

    public function __invoke()
    {
        return $this->tokenEncoded;
    }

    /*public static function getDecodeToken($jwt)
    {
       return JWT::decode($jwt,$_SERVER['APP_SECRET'], array('HS256'));
    }*/

    public static function checkToken($encodedToken):bool
    {
        if(isset($_SESSION['username']) && password_verify($_SESSION['username'],base64_decode($encodedToken)))
        {
            return true;
        }

        /*try {
            self::getDecodeToken($encodedToken);
        }
        catch (SignatureInvalidException $e) {
            return false;
        }*/
        return false;
    }


}