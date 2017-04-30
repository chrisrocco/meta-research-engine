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

    if( is_int($token) ){
        if($token == User::INVALID){
            return $response
                ->write(json_encode("No account with that email and password in the database", JSON_PRETTY_PRINT))
                ->withStatus(401);
        }
    }

    return $response
        ->write(json_encode($token, JSON_PRETTY_PRINT));
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

    if($result instanceof User ){
        $user = $result;
        $user->rehash();

        $validation_email = \Email\Email::validationEmail(
            $user->get('email'),
            $user->get('first_name') . $user->get('last_name'),
            $user->key(),
            $user->get('hash_code')
        );
        $validation_email->send();

        return $response
            ->write("Account created successfully.")
            ->withStatus(200);
    }

    if($result === User::EXISTS){
        return $response
            ->write("An account with that email already exists")
            ->withStatus(409);
    }

});

/**
 * POST usersValidateGet
 * Summary: Registers user
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/users/validate', function ($request, $response, $args) {

    $formData = $request->getParams();

    $user = User::retrieve( $formData['_key'] );
    $result = $user->validate( $formData['hash_code'] );

    if($result){
        return $response
            ->write("Account has been validated. You may now login.")
            ->withStatus(200);
    }

    return $response
        ->write("Invalid hash")
        ->withStatus(400);

});