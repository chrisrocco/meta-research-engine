<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 7/3/2017
 * Time: 3:31 PM
 */

namespace vector\MRE\Middleware;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use uab\MRE\dao\Assignment;

class RequireAssignmentOwner {

    /**
     * @param $req ServerRequestInterface
     * @param $res ResponseInterface
     * @param $next callable
     * @return ResponseInterface
     */
    public function __invoke($req, $res, $next) {
        $assignmentKey = self::getAssignmentKey($req);
        $assignment = Assignment::retrieve($assignmentKey);
        $user = $this->container['user'];
        $isValid = $assignment->getTo() === $user->id();

        if ($isValid) {
            //Call the next middleware and return
            return $next($req, $res);
        }

        //Bail out
        $res->getBody()->write(json_encode([
            'status' => "NOT_ASSIGNED_USER",
            'msg' => "This assignment is not assigned to you."
        ], JSON_PRETTY_PRINT) );
        return $res->withStatus(403);
    }

    private $container;

    /**
     * RequireProjectAdmin constructor.
     * @param $container ContainerInterface
     */
    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * @param $req ServerRequestInterface
     * @return string
     */
    public static function getAssignmentKey ($req) {
        return $req->getAttribute('routeInfo')[2]['key'];
    }
}