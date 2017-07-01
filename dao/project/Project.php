<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:49 PM
 */

namespace uab\MRE\dao;

use vector\ArangoORM\DB\DB;
use vector\ArangoORM\Models\Core\VertexModel;

class Project extends VertexModel {

    static $collection = "projects";

    static $schema = [
        "name"              =>  "string",
        "description"       =>  "string",
        "registrationCode"  =>  "string",
        "version"           =>  "number",
        "assignmentTarget"  =>  "number"
    ];

    /**
     * @param $paper Paper
     */
    function addPaper( $paper , $priority = 0){
        PaperOf::createEdge(
            $this, $paper, ['priority' => $priority]
        );
    }

    /**
     * @return int
     */
    public function updateVersion () {
        $version = $this->get('version') + 1;
        $this->update('version', $version);
        return $version;
    }

    public function getPaperAssignmentTarget() {
        return intval($this->get('assignmentTarget'));
    }

    public function getUserAssignmentCap () {
        //TODO
        return 5;
    }

    public function setPaperAssignmentTarget($newTarget) {
        $this->update('assignmentTarget', $newTarget);
    }

    public function setUserAssignmentCap ($newTarget) {
        //TODO
    }

    /**
     * @param $domain Domain
     */
    function addDomain( $domain ){
        SubdomainOf::createEdge(
            $this, $domain, []
        );
    }

    /**
     * @param $user User
     * @param $registrationCode
     */
    public function addUser ($user, $registrationCode) {
        if ($this->get('registrationCode') !== $registrationCode) {
            return 400;
        }

        $alreadyEnrolled = EnrolledIn::getByExample(['_from' => $user->id(), '_to' => $this->id()]);

        if ($alreadyEnrolled) {
            return 409;
        }

        EnrolledIn::createEdge($this, $user);

        return 200;
    }

    /**
     * Wipes this Study's structure
     * @param $searchDepth int the depth of the graph traversal
     */
    public function removeStructure ($searchDepth = 6) {
        DB::query(
            'FOR domain IN 1..@depth INBOUND @study_ID @@subdomain_of
                    FOR question, questionEdge IN INBOUND domain._id @@variable_of
                    REMOVE question IN @@variables OPTIONS { ignoreErrors: true }
                    REMOVE questionEdge IN @@variable_of OPTIONS { ignoreErrors: true }',
            [
                'depth' => $searchDepth,
                'study_ID' => $this->id(),
                '@subdomain_of' => SubdomainOf::$collection,
                '@variable_of' => VariableOf::$collection,
                '@variables' => Variable::$collection
            ]
        );

        DB::query(
            'FOR domain, subdomainEdge IN 1..@depth INBOUND @study_ID @@subdomain_of
                    REMOVE domain IN @@domains OPTIONS {ignoreErrors : true}
                    REMOVE subdomainEdge IN @@subdomain_of OPTIONS {ignoreErrors : true}',
            [
                'depth' => $searchDepth,
                'study_ID' => $this->id(),
                '@subdomain_of' => SubdomainOf::$collection,
                '@domains' => Domain::$collection
            ]
        );
    }

    /**
     * @return User[]
     */
    public function getAdmins () {
        $aql = '
            FOR admin IN INBOUND @projectID @@admin_to_market
                RETURN admin
        ';
        $bindVars = [
            'projectID' => $this->id(),
            '@admin_to_market' => AdminOf::$collection
        ];
        return DB::queryModel($aql, $bindVars, User::class);
    }

    /**
     * @param $user User
     * @return bool
     */
    public function isAdmin ($user) {
        $admins = $this->getAdmins();
        foreach ($admins as $admin) {
            if ($admin->key() === $user->key()) {
                return true;
            }
        }
        return false;
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

    public function getPapersFlat(){
        $AQL = "FOR paper IN INBOUND @study @@paper_to_study
                    RETURN paper";
        $bindings = [
            'study'     =>  $this->id(),
            '@paper_to_study'   =>  PaperOf::$collection
        ];
        return DB::query( $AQL, $bindings, true)->getAll();
    }

    private function getTopLevelDomains(){
        $AQL = "FOR domain in INBOUND @root @@domain_to_domain
                    SORT domain.name
                    RETURN domain";
        $bindings = [
            "root" => $this->id(),
            "@domain_to_domain" => SubdomainOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Domain::class);
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

        $d = $domain->toArray();
        $v = [
            'variables'     =>  $flat_vars,
            'subdomains'    =>  $subdomains
        ];

        return array_merge( $d, $v );
    }

    /**
     * @param $length int
     * @return string
     */
    public static function generateRegistrationCode ($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRZTUVWXYZ123456789';
        $registrationCode = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $registrationCode .= $characters[mt_rand(0, $max)];
        }
        return $registrationCode;
    }
}