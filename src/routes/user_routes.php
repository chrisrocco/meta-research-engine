<?php
use Models\User as User;

/**
 * POST usersLoginPost
 * Summary: Logs in user
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/users/login', function ($request, $response, $args) {
    $formData = $request->getParams();

    $token = User::login($formData['email'], $formData['password']);

    if($token === User::INVALID){
        return $response
            ->write(json_encode("No account with that email and password in the database", JSON_PRETTY_PRINT))
            ->withStatus(401);
    }

    return $response
        ->write(json_encode($token, JSON_PRETTY_PRINT))
        ->withStatus(200);
});

/**
 * POST usersRegisterPost
 * Summary: Registers user
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/users/register', function ($request, $response, $args) {

    $formData = $request->getParams();

    $result = User::register(
        $formData['first_name'],
        $formData['last_name'],
        $formData['email'],
        $formData['password']
    );

    if(is_string($result)){     // returned a user _key
        return $response
            ->write("Account created successfully.")
            ->withStatus(200);
    }

    if($result == User::EXISTS){
        return $response
            ->write("An account with that email already exists")
            ->withStatus(409);
    }
});