<?php
namespace Models;

use Firebase\JWT\JWT;

class User extends VertexModel
{
    const EXISTS = 20;
    const INVALID = 25;

    /**
     * @param $first_name
     * @param $last_name
     * @param $email
     * @param $password
     * @return int|mixed
     */
    public static function register($first_name, $last_name, $email, $password){
        $exist_arr = User::getByExample([ 'email' =>  $email ]);
        if( count($exist_arr) > 0 ){
            return User::EXISTS;
        }

        $user = User::create([
            'first_name'    =>  $first_name,
            'last_name'     =>  $last_name,
            'email'         =>  $email,
            'password'      =>  $password,
            'active'        =>  false,
            'hash_code'     =>  null
        ]);

        return $user;
    }

    /**
     * @param $email
     * @param $password
     * @return array|int A JWT authentication token
     */
    public static function login($email, $password){
        $exist_arr = User::getByExample([
            'email' =>  $email,
            'password'  =>  $password
            ]);
        if( count($exist_arr) === 0 ){
            return User::INVALID;
        }

        $user = $exist_arr[0];
        $userDetails = [
            "_key" => $user->key(),
            "first_name" => $user->get("first_name"),
            "last_name" => $user->get("last_name"),
            "email" => $user->get('email')
        ];

        // Building the JWT
        $tokenId = base64_encode(random_bytes(64));
        $issuedAt = time();
        $expire = $issuedAt + 60 * 30;            // Adding 60 seconds
        $data = [
            'iat' => $issuedAt,         // Issued at: time when the token was generated
            'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss' => "dev",       // Issuer
            'exp' => $expire,           // Expire
            'data' => $userDetails
        ];

        $settings = require __DIR__ . '/../src/settings.php';
        $token = JWT::encode($data, $settings['settings']['JWT_secret']);

        return [
            "token" => $token,
            "user" => $userDetails
        ];
    }



    public function validate($hash_code){
        if(!$this->checkHash($hash_code)) return false;

        $this->update('active', true);

        return true;
    }
    function rehash(){
        $hash_code = bin2hex(random_bytes(22));

        $this->update('hash_code', $hash_code);

        return $hash_code;
    }
    function checkHash($hash_code){
        return ( $this->get('hash_code')  === $hash_code );
    }
}