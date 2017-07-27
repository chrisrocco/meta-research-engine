<?php
namespace uab\mre\app;

class Assignment {
    public $done;
    public $completion;
    public $branches = [];
    public $constants;
    function setConstants(Branch $branch){
        $this->constants = $branch;
    }
    function addBranch(Branch $branch){
        $this->branches[] = $branch;
    }
    static function parseFromJson( $jsonData ){
        $assignment = new Assignment();
        $assignment->done = $jsonData['done'];
        $assignment->completion = $jsonData['completion'];
        $constants = new Branch();
        $constants->responses = extractResponses($jsonData['constants']);
        $assignment->setConstants($constants);
        foreach ( $jsonData['branches'] as $_branch ){
            $branch = new Branch();
            $branch->responses = extractResponses($_branch);
            $assignment->addBranch($branch);
        }

        function extractResponses( $_branch ){
            $responses = [];
            foreach ($_branch as $_response){
                $responses[] = new Response($_response);
            }
            return $responses;
        }
        return $assignment;
    }
}

class Branch {
    public $responses = [];
    protected $name;
    function addResponse(Response $response){
        $this->responses[] = $response;
    }
}

class Response {
    protected $question;
    protected $value;
    protected $min, $max;
    protected $selections = [];
    public function __construct( $data ) {
        $this->question = $data['question'];
        $this->value = $data['value'];
        $this->min = $data['min'];
        $this->max = $data['max'];
        $this->selections = $data['selections'];
    }
}