<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:49 PM
 */

namespace Models\Vertices;


use DB\DB;
use Models\Core\VertexModel;
use Models\Edges\PaperOf;
use Models\Edges\SubdomainOf;
use Models\Edges\VariableOf;

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
        $AQL = "FOR domain IN 0..3 INBOUND @study_root @@domain_to_domain
                    FOR var IN INBOUND domain @@var_to_domain
                        RETURN var";
        $bindings = [
            'study_root'    =>  $this->id(),
            '@domain_to_domain'  =>  SubdomainOf::$collection,
            '@var_to_domain'     =>  VariableOf::$collection
        ];
        return DB::query($AQL, $bindings, true)->getAll();
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
}