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
        $query = "FOR var IN INBOUND @root @@to_root
                    RETURN var";
        $bindings = [
            'root'  =>  $this->id(),
            '@to_root'  =>  VariableOf::$collection
        ];
        $cursor = DB::query( $query, $bindings );
        return Variable::wrapAll( $cursor );
    }

    function getSubdomains(){
        $id = $this->id();
        $subdomain_of = SubdomainOf::$collection;
        $query = "FOR domain in INBOUND '$id' $subdomain_of
                    RETURN domain";

        $cursor = DB::query($query);
        return Domain::wrapAll( $cursor );
    }
}