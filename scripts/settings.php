<?php

$template = file_get_contents( __DIR__ . "/settings-template" );
$generated = fopen( __DIR__ . '/../app/settings.php', "w" );

$handle = fopen ("php://stdin","r");

$jwt_secret             =   prompt( "JWT Secret (_): " );
$google_api_key         =   prompt( "Google API Key (_): " );
$db_name                =   prompt( "Database Name (development) : " );
$db_endpoint            =   prompt( "Database Endpoint (tcp://localhost:8529): " );
$db_user                =   prompt( "Database Username (root): " );
$db_password            =   prompt( "Database Password (no password): " );
$mail_server_host       =   prompt( "Mail Server SMTP Host (smtp.gmail.com): " );
$mail_server_username   =   prompt( "Mail Server Username (_): " );
$mail_server_password   =   prompt( "Mail Server Password (_): " );
$smtp_secure            =   prompt( "SMTP Security method (tls): " );
$port                   =   prompt( "SMTP Port (587): " );

setDefault( $db_name, "development" );
setDefault( $db_endpoint, "tcp://localhost:8529" );
setDefault( $db_user, "root" );
setDefault( $mail_server_host, "smtp.gmail.com" );
setDefault( $smtp_secure, "tls" );
setDefault( $port, "587" );

write( "{{jwt_secret}}",            $jwt_secret );
write( "{{google_api_key}}",        $google_api_key );
write( "{{db_name}}",               $db_name );
write( "{{db_endpoint}}",           $db_endpoint );
write( "{{db_user}}",               $db_user );
write( "{{db_password}}",           $db_password );
write( "{{mail_server_host}}",      $mail_server_host );
write( "{{mail_server_username}}",  $mail_server_username );
write( "{{mail_server_password}}",  $mail_server_password );
write( "{{smtp_secure}}",           $smtp_secure );
write( "{{port}}",             $port );

fwrite( $generated, $template );
fclose($handle);
fclose($generated);
echo "\n";
echo "Settings Generated!\n";







function prompt( $msg ){
    global $handle;
    echo $msg;
    return trim( fgets($handle) );
}

function setDefault( &$var, $default_value ){
    if( $var === "" ){
        $var = $default_value;
    }
}

function write( $binding, $value ){
    global $template;
    $template = str_replace( $binding, $value, $template );
}