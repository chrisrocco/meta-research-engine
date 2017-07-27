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
    function toArray(){
        $_branches = [];
        foreach ($this->branches as $branch){
            $_branches[] = $branch->toArray();
        }
        return [
            "done" => $this->done,
            "completion" => $this->completion,
            "constants" => $this->constants->toArray(),
            "branches" => $_branches
        ];
    }
    static function parse( $jsonData ){
        function extractResponses( $_branch ){
            $responses = [];
            foreach ($_branch as $_response){
                $responses[] = new Response($_response['question'], $_response['data']);
            }
            return $responses;
        }

        $assignment = new Assignment();
        $assignment->done = $jsonData['done'];
        $assignment->completion = $jsonData['completion'];
        $constants = new Branch();
        $constants->responses = extractResponses($jsonData['encoding']['constants']);
        $assignment->setConstants($constants);
        foreach ( $jsonData['encoding']['branches'] as $_branch ){
            $branch = new Branch();
            $branch->responses = extractResponses($_branch);
            $assignment->addBranch($branch);
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

    function toArray(){
        $_branch = [];
        foreach ($this->responses as $response){
            $_branch[] = $response->toArray();
        }
        return $_branch;
    }
}

class Response {
    protected $data;
    protected $question;
    public function __construct( $question, $data ) {
        $this->question = $question;
        $this->data = $data;
    }

    function toArray(){
        return [
            "question" => $this->question,
            "data" => $this->data
        ];
    }
}