<?php 

 

// make sure browsers see this page as utf-8 encoded HTML 
header('Content-Type: text/html; charset=utf-8');  

$limit = 10; 
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false; 
$results = false;  
$baseURL = "E:\\Work\\Studies\\IR\\IR Homework\\IR HW 04 Solr\\crawl_data\\";

if ($query) 
{ 
  // The Apache Solr Client library should be on the include path 
  // which is usually most easily accomplished by placing in the 
  // same directory as this script ( . or current directory is a default 
  // php include path entry in the php.ini) 
  require_once('Apache/Solr/Service.php'); 

  if (!isset($urlFileMap)) {
    // Parse the list of URLs into an associative array
    $urlFileMap = array();
  
    $row = 1;
    if (($handle = fopen("UrlToHtml_NBCNews.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE /* && $row < 5 */ ) {
  
            $fileName = $baseURL.$data[0];
            $url = $data[1];
  
            $urlFileMap[$fileName] = $url;
  
            // echo $fileName."->".$url."<br>";
            $row ++;
        }
        fclose($handle);
    }
  }

  // create a new solr service instance - host, port, and corename 
  // path (all defaults in this example) 
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/hw4/'); 

 

  // if magic quotes is enabled then stripslashes will be needed 
  if (get_magic_quotes_gpc() == 1) 
  { 
    $query = stripslashes($query); 
  } 


  // in production code you'll always want to use a try /catch for any 
  // possible exceptions emitted  by searching (i.e. connection 
  // problems or a query parsing error) 
  try 
  { 
    if(isset($_REQUEST['algorithm'])) {
      $additionalParameters = array( 
        'sort' => $_REQUEST['algorithm'], 
      ); 
      $results = $solr->search($query, 0, $limit, $additionalParameters); 
    }       
    else {
      $results = $solr->search($query, 0, $limit); 
    }        
  } 
  catch (Exception $e) 

  { 
    // in production you'd probably log or email this error to an admin 
    // and then show a special message to the user but for this example 
    // we're going to show the full exception 
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>"); 
  } 
} 

?> 
<html> 
<head> 
  <title>Solr Search Plus</title> 

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

  <link href="https://fonts.googleapis.com/css?family=Roboto|Capriola" rel="stylesheet">

  <style>
    body {
      font-family: "Roboto";
    }

    .logo {
      font-family: "Capriola";
    }
  </style>
</head> 
<body> 

  <div class="container">

    <div class="text-center mt-5 mb-3">
      <h1 class="logo">
        <span class="text-primary">S</span><span class="text-danger">e</span><span class="text-warning">a</span><span class="text-primary">r</span><span class="text-success">c</span><span class="text-danger">h</span>
      </h1>
    </div>

    <div class="row">
      <div class="col">
        <form  accept-charset="utf-8" method="get"> 
          <div class="form-group form-row">
            <!-- <label for="q">Search:</label>  -->
            <div class="col-9">
            <input class="form-control mb-3" id="q" name="q" type="text" placeholder="Enter search term here" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/> 
            </div>

            <div class="col-2">
            <select class="form-control" name="algorithm" id="algorithm">
              <option value="score desc">Lucene</option>
              <option value="pageRankFile desc" <?php if(isset($_REQUEST['algorithm']) && $_REQUEST['algorithm']=="pageRankFile desc") { echo "selected"; } ?> >PageRank</option>
            </select>
            </div>

            <div class="col-1">
            <input class="btn btn-primary" type="submit"/> 
            </div> 

          </div>
        </form> 
      </div>
    </div>

    <div class="row">
      <div class="col">

  <?php 
  // display results 
  if ($results) 
  { 
    $total = (int) $results->response->numFound; 
    $start = min(1, $total); 
    $end = min($limit, $total); 
  ?> 
    <hr>
      <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div> 
      <ul class="list-unstyled"> 
  <?php 
    // iterate result documents 
    $i = 1;
    foreach ($results->response->docs as $doc) 
    { 
  ?> 
        <li> 

        <div class="result mt-4 table-responsive">

        <?php
          if($doc->og_url) {
            $url = $doc->og_url;
          }
          else {
            $url = $urlFileMap[$doc->id];
          }
        ?>
        
          
          <h6 class="text-primary mb-0">
            <strong>Title: </strong><a href="<?php echo $url ?>"><?php echo $doc->title ?></a>
          </h6>
          
          <p class="text-success mb-0">
            <strong>URL:</strong>
            <a href="<?php echo $url ?>" class="text-success"><?php echo $url ?></a>
          </p>          

          <small class="text-secondary mb-0">
            <strong>ID: </strong><?php echo $doc->id ?> 
          </small>

          <?php if($doc->og_description) { ?> 
          <p class="text mb-0">
            <strong>Description: </strong><?php echo $doc->og_description ?>          
          </p>
          <?php } ?> 
        </div>

        </li> 
  <?php 
    $i = $i + 1;
    } 
    ?> 
    </ul> 
  <?php 
  } 
  ?> 

      </div><!-- col -->
    </div><!-- row -->
  </div><!-- container -->
</body> 
</html> 