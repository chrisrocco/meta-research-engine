<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 7/1/2017
 * Time: 11:34 AM
 */

namespace vector\MRE\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class RoleValidator {


    /**
     * @param $req ServerRequestInterface
     * @param $res ResponseInterface
     * @param $next callable
     * @return ResponseInterface
     */
    public function __invoke($req, $res, $next) {
        $userScopes = static::getScopesFromJWT( $req->getAttribute($this->tokenAttribute) );
        $isValid = $this->hasValidScope($userScopes);

        if ($isValid) {
            //Call the next middleware and return
            return $next($req, $res);
        }

        //Bail out
        $res->getBody()->write(json_encode([
            'status' => $this->errorCode,
            'msg' => "You do not have necessary permission to access this resource."
        ], JSON_PRETTY_PRINT) );
        return $res->withStatus(403);
    }

    /**
     * Iterates over the calling scopes and returns true iff any of them match the signature of this route.
     * Returns false otherwise.
     * @param $userScopes string[]
     * @return bool
     */
    abstract function hasValidScope ($tokenScopes);

    /**
     * Encapsulation for retrieving the scopes array from the jwt token
     * @param $token
     * @return string[]
     */
    abstract static function getScopesFromJWT($token);


    /** @var string[] */
    protected $validScopes = [];
    protected $tokenAttribute = "jwt";
    protected $errorCode = "USER_INVALID_SCOPE";

    /**
     * RoleValidator constructor.
     * @param $scopes array Required scopes
     * @param $options array Optional settings
     */
    public function __construct($scopes, $options = []) {
        $this->validScopes = $scopes;
        //Fill out options
        $this->hydrate($options);
    }

    /**
     * Hydrate options from given array
     * Credit: github.com/tuupola
     * @param array $data Array of options.
     * @return self
     */
    private function hydrate(array $data = [])
    {
        foreach ($data as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method), $value);
            }
        }
        return $this;
    }

    private function setTokenAttribute ($value) {
        $this->tokenAttribute = $value;
    }

    private function setErrorCode ($value){
        $this->errorCode = $value;
    }

}