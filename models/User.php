<?php
namespace Models;



use DB\DB;
use Firebase\JWT\JWT;

class User extends Model
{
    const EXISTS = 20;
    const INVALID = 25;

    public static function register($first_name, $last_name, $email, $password){
        $exist_arr = User::findByExample([ 'email' =>  $email ]);
        if( count($exist_arr) > 0 ){
            return User::EXISTS;
        }

        return User::createOrUpdate([
            'first_name'    =>  $first_name,
            'last_name'     =>  $last_name,
            'email'         =>  $email,
            'password'      =>  $password,
            'active'        =>  false
        ]);
    }

    public static function login($email, $password){
        $exist_arr = User::findByExample([
            'email' =>  $email,
            'password'  =>  $password
            ]);
        if( count($exist_arr) === 0 ){
            return User::INVALID;
        }

        $user = $exist_arr[0];
        $userDetails = [
            "_key" => $user->get("_key"),
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

        global $settings;
        $token = JWT::encode($data, $settings['settings']['JWT_secret']);

        return [
            "token" => $token,
            "user" => $userDetails
        ];
    }

}