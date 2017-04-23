<?php

/**
 * POST usersLoginPost
 * Summary: Logs in user
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/users/login', function ($request, $response, $args) {
    $formData = $request->getParams();

    $token = UserHandler::login($formData['email'], $formData['password']);

    if($token === UserHandler::INVALID){
        return $response
            ->write(json_encode("No account with that email and password in the database", JSON_PRETTY_PRINT))
            ->withStatus(403);
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

    $result_code = UserHandler::register($formData['name'], $formData['email'], $formData['password'], $formData['role']);

    switch ($result_code){
        case UserHandler::SUCCESS :
            return $response
                ->write("Account created successfully.")
                ->withStatus(200);
        case UserHandler::ERROR :
            return $response
                ->write("Could not create account")
                ->withStatus(500);
        case UserHandler::ALREADY_EXISTS :
            return $response
                ->write("An account with that email already exists")
                ->withStatus(409);
    }
});