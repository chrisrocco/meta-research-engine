<?php
/**
 * User: chris
 * Date: 5/1/17
 * Time: 11:46 AM
 */

namespace Papers;


class Encoding {

    public $data = [
        /**
         * branch_key   =>  [
         *      variable_key    =>  [
         *          Response
         *          Response
         *      ]
         *      variable_key    =>  [
         *          Response
         *          Response
         *      ]
         * ]
         * branch_key   =>  [
         *      variable_key    =>  [
         *          Response
         *          Response
         *      ]
         *      variable_key    =>  [
         *          Response
         *          Response
         *      ]
         * ]
         */
    ];

    /**
     * Encoding constructor.
     * @param $responses    Response[]      array of response objects can make up an encoding
     */
    function __construct( $responses ) {

    }

    /**
     * @param $response Response
     */
    public function recordResponse( $response ){

    }

    /**
     * @param $encoding Encoding    another encoding
     * @return Encoding             the new encoding
     */
    public function merge( $encoding ){
        return new Encoding();
    }


    /**
     * @param $user_submission  mixed           The user-submitted data from the front-end
     * @return Response[]                       The array of responses extracted from the submission
     */
    public static function parse( $user_submission ){
        $responses = [];

        $responses[] = new Response();

        return $responses;
    }
}