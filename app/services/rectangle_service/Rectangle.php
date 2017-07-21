<?php
namespace uab\mre\app;
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/21/2017
 * Time: 12:10 AM
 */
class Rectangle
{
    private $headers;

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    private $rows;

    function __construct( $headers )
    {
        $this->headers = $headers;
    }

    public function recordRow( $rowData ){
        $row = [];
        foreach ( $this->headers as $column ){
            if( isset($rowData[$column]) ){
                $row[$column] = str_replace(", ", "", $rowData[$column]);
                continue;
            }
            $row[$column] = '#';
        }
        $this->rows[] = $row;
    }

    public function writeCSV( $f_handle ){
        fwrite( $f_handle, implode(", ", $this->headers).PHP_EOL);
        foreach ( $this->rows as $row ){
            fwrite( $f_handle, implode(", ", $row).PHP_EOL);
        }
    }

    public function exportCSV(){
        $string = "";
        $string .= implode(", ", $this->headers).PHP_EOL;
        foreach ( $this->rows as $row ){
            $string .= implode(", ", $row).PHP_EOL;
        }
        return $string;
    }
}