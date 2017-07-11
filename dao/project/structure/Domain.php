<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 4:05 PM
 */

namespace uab\MRE\dao;

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

    /**
     * @return Variable[]
     */
    function getVariables(){
        $AQL = "FOR var IN INBOUND @root @@to_root
                    SORT var.name
                    RETURN var";
        $bindings = [
            'root'  =>  $this->id(),
            '@to_root'  =>  VariableOf::$collection
        ];
        return DB::queryModel( $AQL, $bindings, Variable::class );
    }

    function getSubdomains(){
        $AQL = "FOR domain in INBOUND @root @@domain_to_domain
                    SORT domain.name
                    RETURN domain";
        $bindings = [
            "root" => $this->id(),
            "@domain_to_domain" => SubdomainOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Domain::class);
    }


    public function addRawSubdomainsRecursive ($rawSubdomains) {
        foreach ($rawSubdomains as $rawSubdomain) {
            $domain = self::createFromRaw($rawSubdomain);
            $this->addSubdomain($domain);

            $domain->addRawVariables($rawSubdomain['variables']);

            $domain->addRawSubdomainsRecursive($rawSubdomain['subdomains']);
        }
    }

    public function addRawVariables($rawVariables){
        foreach ($rawVariables as $rawVariable) {
            $variable = Variable::createFromRaw($rawVariable);
            $this->addVariable($variable);
        }
    }

    public static function createFromRaw ($rawDomain) {
        $prospect = [];
        foreach ($rawDomain as $key=>$value) {
            if (in_array($key, self::ignored_raw_keys)) {
                continue;
            }
            $prospect[$key] = $value;
        }
        return Domain::create($prospect);
    }

    const ignored_raw_keys = [
        '_key',
        '_id',
        '_rev',
        'subdomains',
        'date_created',
        'variables',
    ];
}