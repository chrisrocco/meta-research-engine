<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/16/2017
 * Time: 10:07 PM
 */

namespace uab\MRE\dao;


use vector\ArangoORM\Models\Core\VertexModel;

class SerializedProjectStructure extends VertexModel {
    static $collection = 'serialized_project_structures';

    /**
     * Retrieves a SerializedProjectStructure by a project, or creates it if it doesn't already exist
     * @param $project Project
     * @return self
     */
    public static function getByProject ($project) {
        $result = self::retrieve($project->key());
        if (!$result) {
            $result = SerializedProjectStructure::create(['_key' => $project->key()]);
            $result->refresh();
        }
        return $result;
    }

    /**
     * @return bool|Project
     */
    public function getProject () {
        return Project::retrieve($this->key());
    }

    /**
     * @param $project Project
     */
    public function refresh () {
        $project = $this->getProject();
        $structure = self::generate($project);

        $this->update('structure', $structure);
        $this->update('version', $project->get('version'));
    }

    /**
     * @param $project Project
     */
    public static function generate ($project) {
        $resultDomains = [];
        $resultQuestions = [];

        $topLevelDomains = $project->getTopLevelDomains();

        foreach ($topLevelDomains as $topLevelDomain) {
            self::generateFromDomainRecursive($resultDomains, $resultQuestions, $topLevelDomain);
        }

        return [
            'domains' => $resultDomains,
            'questions' => $resultQuestions
        ];
    }

    /**
     * @param $domain Domain
     */
    public static function generateFromDomainRecursive (&$domains, &$questions, $domain, $parentKey = "#") {

        $questions = array_merge($questions, self::generateQuestionsFromDomain($domain));

        $domains[] = [
            'id' => $domain->key(),
            'parent' => $parentKey,
            'name' => $domain->get('name'),
            'description' => $domain->get('description'),
            'tooltip' => $domain->get('tooltip'),
            'icon' => $domain->get('icon'),
        ];

        foreach ($domain->getSubdomains() as $subdomain) {
            self::generateFromDomainRecursive($domains, $questions, $subdomain, $domain->key());
        }
    }

    /**
     * @param $domain Domain
     */
    public static function generateQuestionsFromDomain ($domain) {
        $result = [];
        $variables = $domain->getVariables();
        foreach ($variables as $variable) {
            $question = $variable->toArray();
            unset (
                $question['_key'],
                $question['_id'],
                $question['_rev'],
                $question['date_created']
            );
            $result[] = array_merge(
                [
                    'id' => $variable->key(),
                    'parent' => $domain->key(),
                ],
                $question
            );
        }
        return $result;
    }
}