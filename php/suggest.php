<?php 

// make sure browsers see this page as JSON
header('Content-Type: application/json');

$response = array();

if ($isset($_REQUEST['q']) ;
{

}

$responseJSON = json_encode($response, JSON_FORCE_OBJECT);

echo $responseJSON;
?>