<?php 

// make sure browsers see this page as JSON
header('Content-Type: application/json');

// Spell corrector
ini_set("memory_limit", -1);
set_time_limit(0);
include "SpellCorrector.php";

$preparedResponse = array();

if (isset($_REQUEST['q'])) {

  $query = $_REQUEST['q'];
  
  // optional query parameter
  $preparedResponse["query"] = $query;

  $keywords = explode(" ", $query); // Split phrase queries into single ones
  $n = count($keywords);            // The number of keywords
  $quintuples = array();            // Stores the 5 suggestions for each query term

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

  // Add autocomplete suggestions: alternate method, interleave best suggestions
  if($n > 3 || $n <= 1) {
    
    // Combine i'th suggestion for each keyword
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
  } else if($n == 3) {
    $suggestions[] = $quintuples[0][0]." ".$quintuples[1][0]." ".$quintuples[2][0];
    $suggestions[] = $quintuples[0][1]." ".$quintuples[1][0]." ".$quintuples[2][0];
    $suggestions[] = $quintuples[0][1]." ".$quintuples[1][1]." ".$quintuples[2][0];
    $suggestions[] = $quintuples[0][1]." ".$quintuples[1][0]." ".$quintuples[2][1];
    $suggestions[] = $quintuples[0][2]." ".$quintuples[1][1]." ".$quintuples[2][1];
  } 
  else if ($n == 2) {
    $suggestions[] = $quintuples[0][0]." ".$quintuples[1][0];
    $suggestions[] = $quintuples[0][1]." ".$quintuples[1][0];
    $suggestions[] = $quintuples[0][0]." ".$quintuples[1][1];
    $suggestions[] = $quintuples[0][1]." ".$quintuples[1][1];
    $suggestions[] = $quintuples[0][2]." ".$quintuples[1][0];
  }

  // Add output of spelling checker
  $spellsuggestion = "";
  for($i = 0; $i < $n; $i++) {
    $spellsuggestion = $spellsuggestion.SpellCorrector::correct($keywords[$i]);

    if($i < ($n-1)) {
      $spellsuggestion = $spellsuggestion." ";
    }
  }

  // Check if the first suggestion is already the same
  if($suggestions[0] != $spellsuggestion) {
    array_unshift($suggestions, $spellsuggestion);
  }

  // var_dump($suggestions);
  // var_dump($preparedResponse);

  $preparedResponse["suggestions"] = $suggestions;
}

$preparedResponseJSON = json_encode($preparedResponse, JSON_FORCE_OBJECT);

echo $preparedResponseJSON;
?>