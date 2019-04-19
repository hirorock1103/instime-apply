<?php
include_once("../../../lib/convert.class.php");
include_once("../../../lib/getter.class.php");

$address = array();

if( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){

    $zipcode = (isset($_POST['zipcode']) ? Converter::trim_all($_POST['zipcode']) : "");

    if( !empty($zipcode) ){
      $address = Getter::address($zipcode);
    }
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($address, JSON_UNESCAPED_UNICODE);
