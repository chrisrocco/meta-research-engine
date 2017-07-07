<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 7/1/2017
 * Time: 5:27 PM
 */

namespace vector\MRE\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use uab\MRE\dao\Project;

class RequireProjectAdmin {

    /**
     * @param $req ServerRequestInterface
     * @param $res ResponseInterface
     * @param $next callable
     * @return ResponseInterface
     */
    public function __invoke($req, $res, $next) {
        $projectKey = self::getProjectKey($req);
        $project = Project::retrieve($projectKey);
        $user = $this->container['user'];
        $isValid = $project->isAdmin($user);

        if ($isValid) {
            //Call the next middleware and return
            return $next($req, $res);
        }

        //Bail out
        $res->getBody()->write(json_encode([
            'status' => "USER_NOT_ADMIN",
            'msg' => "You are not an admin of this project"
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
    private static function getProjectKey ($req) {
        return $req->getAttribute('routeInfo')[2]['key'];
    }
}