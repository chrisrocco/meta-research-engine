<?php
use Models\Vertices\User as User;

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
                ->write(json_encode([
                    "reason"    =>  "invalid"
                ]))
                ->withStatus(401);
        }
        if ($token == User::INACTIVE) {
            return $response
                ->write(json_encode([
                        "reason"    =>  "inactive"
                    ]))
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

/**
 * params: email and callback url to send the hash to
 */
$app->POST('/users/recover', function ($request, $response, $args) {
    $formData = $request->getParams();
    $email = $formData['email'];
    $callback = $formData['callback'];

    $user = User::getByExample([
        'email' => $email
    ])[0];
    if( !$user ){
        return $response
            ->withStatus( 404 );
    }
    $hash = $user->rehash();

    $email = \Email\Email::resetPasswordEmail( $email, "", $callback, $hash);
    $result = $email->send();

    if($result){
        return $response
            ->write("An email with recovery instructions has been sent.")
            ->withStatus(200);
    }

    return $response
        ->write("Email failed to send")
        ->withStatus( 500 );
});

$app->POST('/users/reset', function ($request, $response, $args) {
    $newPassword    = $request->getParam('newPassword');
    $hash           = $request->getParam('hash_code');

    var_dump( $hash );

    $resultSet = User::getByExample([
        'hash_code' => $hash
    ]);

    if( count($resultSet) == 0){
        return $response
            ->write( "invalid hash" )
            ->withStatus( 400 );
    }

    $user = $resultSet[0];
    $passhash = password_hash($newPassword, PASSWORD_DEFAULT);
    $user->update('password', $passhash);

    return $response->write("Successfully reset password");

});