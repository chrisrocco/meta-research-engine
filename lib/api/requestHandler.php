<?php
namespace SlimRequestHandler;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:29 PM
 */
abstract class requestHandler {
    function __invoke($request, $response, $args) {
        $this->handle($request, $response, $args);
    }

    /**
     * @param $request \Slim\Http\Request
     * @param $response \Slim\Http\Response
     * @param $args
     * @return \Slim\Http\Response
     */
    abstract function handle($request, $response, $args);
}