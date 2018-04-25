<?php 

// make sure browsers see this page as JSON
header('Content-Type: application/json');

$preparedResponse = array();

if (isset($_REQUEST['q'])) {

  $query = $_REQUEST['q'];
  
  // optional query parameter
  $preparedResponse["query"] = $query;

  $keywords = explode(" ", $query); // Split phrase queries into single ones
  $quintuples = array(); // Stores the 5 suggestions for each query term

  // For each single word in the query
  for($i = 0; $i < count($keywords); $i++) {

    // Call the solr suggest API
    $keyword = $keywords[$i];
    $url = "http://localhost:8983/solr/hw4/suggest?q=$keyword";
    $response = file_get_contents($url);
    $responseJSON = json_decode($response, true);
    
    $suggestionObject = $responseJSON["suggest"]["suggest"][$keyword]["suggestions"];
    
    // Create an array of all the suggested terms
    $quintuple = array(); 
    for($j = 0; $j < count($suggestionObject); $j++) {
      $quintuple[] = $suggestionObject[$j]["term"];
    }

    $quintuples[] = $quintuple;
  }
  
  // Set the no. of suggestions to be the minimum number of suggestions among all quintuples
  $limit = count($quintuples[0]);
  for($i = 1; $i < count($quintuples); $i++) {
    $currentLimit = count($quintuples[$i]);
    if($currentLimit < $limit) {
      $limit = $currentLimit;
    }
  }
  
  // Create the response format
  $suggestions = [];
  for($i = 0; $i < $limit; $i++) {
    $suggestion = "";

    for($j = 0; $j < count($quintuples); $j++) {
      $suggestion = $suggestion.$quintuples[$j][$i];

      if($j < count($quintuples)-1) {
        $suggestion = $suggestion." ";
      }
    }
    // echo($suggestion . "<br>");

    $suggestions[] = $suggestion;
  }
  // var_dump($suggestions);
  // var_dump($response);

  $preparedResponse["suggestions"] = $suggestions;
}

$preparedResponseJSON = json_encode($preparedResponse, JSON_FORCE_OBJECT);

echo $preparedResponseJSON;
?>