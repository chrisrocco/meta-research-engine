<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:49 PM
 */

namespace Models;


use DB\DB;
use DB\Queries\QueryBank;
use Models\Core\VertexModel;

class Study extends VertexModel {

    static $collection = "studies";

    /**
     * @param $paper Paper
     */
    function addPaper( $paper ){
        PaperOf::create(
            $this->id(), $paper->id(), []
        );
    }

    /**
     * @param $domain Domain
     */
    function addDomain( $domain ){
        SubdomainOf::create(
            $this->id(), $domain->id(), []
        );
    }

    /**
     * @return array
     */
    public function getStructureFlat(){
        $domains = [];
        foreach( $this->getTopLevelDomains() as $subdomain ){
            $domains[] = $this->recursiveGetDomain( $subdomain );
        }
        return $domains;
    }
    public function getVariablesFlat(){
        $vars = [];
        foreach ( $this->getTopLevelDomains() as $top ){
            $this->recursiveGetVariables( $top, $vars );
        }
        return $vars;
    }

    private function getTopLevelDomains(){
        $id = $this->id();
        $subdomain_of = SubdomainOf::$collection;
        $query = "FOR domain in INBOUND '$id' $subdomain_of
                    RETURN domain";

        $cursor = DB::query($query);
        return Domain::wrapAll( $cursor );
    }
    private function recursiveGetDomain( $domain ){

        $variables  = $domain->getVariables();
        $subdomains = [];
        foreach ( $domain->getSubdomains() as $subdomain) {
            $subdomains[] = $this->recursiveGetDomain($subdomain);
        }

        $flat_vars = [];
        foreach ($variables as $var ){
            $flat_vars[] = $var->toArray();
        }

        return [
            'name'          =>  $domain->get('name'),
            'variables'     =>  $flat_vars,
            'subdomains'    =>  $subdomains
        ];
    }
    private function recursiveGetVariables( $domain, &$output ){
        foreach ( $domain->getVariables() as $variable ){
            $output[] = $variable->toArray();
        }

        foreach ( $domain->getSubdomains() as $sub ){
            $this->recursiveGetVariables( $sub, $output );
        }

        return $output;
    }
}