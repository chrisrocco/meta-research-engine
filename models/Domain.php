<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 4:05 PM
 */

namespace Models;


use Models\Core\VertexModel;

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
}