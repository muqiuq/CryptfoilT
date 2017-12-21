<?php
/**
 * Created by PhpStorm.
 * User: philipp
 * Date: 21.12.2017
 * Time: 23:36
 */


function get_database() {
    $database = false;

    if(file_exists(FILE_DB)) {
        $database = json_decode(file_get_contents(FILE_DB),true);
    }

    if($database === false) {
        $database = array(
            "environment" => array(
                "last" => ""
            ),
            "history" => array()
        );
    }

    return $database;
}
