<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 4:05 PM
 */

namespace Models\Vertices;


use Models\Edges\VariableOf;
use Models\Edges\SubdomainOf;
use vector\ArangoORM\DB\DB;
use vector\ArangoORM\Models\Core\VertexModel;

class Domain extends VertexModel
{
    static $collection = 'domains';

    /**
     * @param $domain Domain
     */
    function addSubdomain( $domain ){
        SubdomainOf::createEdge(
            $this, $domain, []
        );
    }

    /**
     * @param $variable Variable
     */
    function addVariable( $variable ){
        VariableOf::createEdge(
            $this, $variable, []
        );
    }

    function getVariables(){
        $AQL = "FOR var IN INBOUND @root @@to_root
                    RETURN var";
        $bindings = [
            'root'  =>  $this->id(),
            '@to_root'  =>  VariableOf::$collection
        ];
        return DB::queryModel( $AQL, $bindings, Variable::class );
    }

    function getSubdomains(){
        $AQL = "FOR domain in INBOUND @root @@domain_to_domain
                    RETURN domain";
        $bindings = [
            "root" => $this->id(),
            "@domain_to_domain" => SubdomainOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Domain::class);
    }
}