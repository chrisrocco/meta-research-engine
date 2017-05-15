<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 4:05 PM
 */

namespace Models\Vertices;


use DB\DB;
use Models\Core\VertexModel;
use Models\Edges\VariableOf;
use Models\Edges\SubdomainOf;

class Domain extends VertexModel
{
    static $collection = 'domains';

    /**
     * @param $domain Domain
     */
    function addSubdomain( $domain ){
        SubdomainOf::create(
            $this->id(), $domain->id(), []
        );
    }

    /**
     * @param $variable Variable
     */
    function addVariable( $variable ){
        VariableOf::create(
            $this->id(), $variable->id(), []
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