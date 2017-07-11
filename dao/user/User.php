<?php
namespace uab\MRE\dao;

use Firebase\JWT\JWT;
use vector\ArangoORM\DB\DB;
use vector\ArangoORM\Models\Core\VertexModel;

class User extends VertexModel
{
    static $collection = "users";
    static $schema = [
        'first_name'    =>  "string",
        'last_name'     =>  "string",
        'email'         =>  "string",
        'password'      =>  "string",
        'active'        =>  "boolean"
    ];

    const EXISTS = 20;
    const INVALID = 25;
    const INACTIVE = 69;

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

        $passhash = password_hash($password, PASSWORD_DEFAULT);

        $user = User::create([
            'first_name'    =>  $first_name,
            'last_name'     =>  $last_name,
            'email'         =>  $email,
            'password'      =>  $passhash,
            'active'        =>  false,
            'role'          =>  "user",
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
        $user_set = User::getByExample([
                'email' =>  $email
            ]);

        if( count($user_set) === 0 ){
            return User::INVALID;
        }

        $user = $user_set[0];

        $password_check = password_verify($password, $user->get('password'));
        if(!$password_check) return User::INVALID;

        if (!$user->get('active')) {
            return User::INACTIVE;
        }

        $userDetails = [
            "_key" => $user->key(),
            "first_name" => $user->get("first_name"),
            "last_name" => $user->get("last_name"),
            "email" => $user->get('email'),
            "role"  => $user->get('role')
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

        $settings = require __DIR__ . '/../../app/settings.php';
        $token = JWT::encode($data, $settings['settings']['JWT_secret']);

        return [
            "token" => $token,
            "user" => $userDetails
        ];
    }

    public function getAssignments( $flat = false ){
        $AQL = "FOR vertex, edge IN INBOUND @root @@edge_collection
                    return edge";
        $bindings = [
            "root"  =>  $this->id(),
            "@edge_collection"  =>  Assignment::$collection
        ];

        if($flat){
            return DB::query($AQL, $bindings)->getAll();
        }
        return DB::queryModel($AQL, $bindings, Assignment::class);
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