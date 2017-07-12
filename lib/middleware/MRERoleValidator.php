<?php

namespace vector\MRE\Middleware;


class MRERoleValidator extends RoleValidator {


    /**
     * Iterates over the calling scopes and returns true iff any of them match the signature of this route.
     * Returns false otherwise.
     * @param $userScopes string[]
     * @return bool
     */
    public function hasValidScope ($tokenScopes) {
        foreach ($tokenScopes as $userScope) {
            if (in_array($userScope, $this->validScopes)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Encapsulation for retrieving the scopes array from the jwt token
     * @param $token
     * @return string[]
     */
    public static function getScopesFromJWT($token) {
        return array($token->data->role);
    }
}