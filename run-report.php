<?php
$database = new \PDO('sqlite:spider.db');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$statement = $database->prepare("SELECT * FROM jobs WHERE uuid = ?");
$statement->execute([$_GET['jobid']]);
$job = json_decode($statement->fetch(PDO::FETCH_OBJ)->data);
$remainingTargets = $job->targets;
usort($remainingTargets, function ($a, $b) { return strcmp(md5($a), md5($b)); });

$multiHandle = curl_multi_init();
$targets = [];
while (count($targets) < 30 && count($remainingTargets) > 0) {
  $target = array_shift($remainingTargets);
  $statement = $database->prepare('SELECT * FROM spideredPages WHERE url = ?');
  $statement->execute([$target]);
  $page = $statement->fetch();
  if ($page !== false) {
    continue;
  }
	$curl = curl_init($target);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36');
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 4);
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
  curl_multi_add_handle($multiHandle, $curl);
  $targets[$target] = $curl;
}

$running = null;
do {
  curl_multi_exec($multiHandle, $running);
} while ($running);

foreach ($targets as $target => $curl) {
  $html = curl_multi_getcontent($curl);
  curl_multi_remove_handle($multiHandle, $curl);
	preg_match_all('/https?\:\/\/[^\"\' <]+/i', $html, $matches);
	$all_urls = array_unique($matches[0]);
  $statement = $database->prepare("REPLACE INTO spideredPages (url, date, data) VALUES (?, date('now'), ?)");
  $statement->execute([$target, json_encode($all_urls)]);
}
curl_multi_close($multiHandle);

if (count($remainingTargets) > 0) {
  	header("refresh:2;?jobid={$_GET['jobid']}");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    <title>Analisi Backlinks - SEO Tool Gratuito: Backlink Checker</title>
    <meta name="description" content="Analizza e verifica i backlinks verso il tuo sito web in modo semplice e veloce grazie a questo tool." />
<link rel="canonical" href="https://www.lorenzcrood.com/analisibacklinks/" />
<meta name="keywords" content="analizza backlinks, analisi backlinks, backlink checker, seo tool gratis, seo tool gratuito, tool backlinks, verifica backlinks, verifica backlink">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@croodlorenz">
	<meta name="twitter:creator" content="@croodlorenz">
	<meta name="twitter:title" content="SEO Tool Gratuito - Analizza e verifica i backlinks">
	<meta name="twitter:description" content="Analizza e verifica i backlinks verso il tuo sito web in modo semplice.">
	<meta name="twitter:image" content="https://www.lorenzcrood.com/wp-content/uploads/tool-per-analisi-backlinks.png">
	<meta property="og:image" content="https://www.lorenzcrood.com/wp-content/uploads/tool-per-analisi-backlinks.png" />
	<meta property="og:description" content="Analizza e verifica i backlinks verso il tuo sito web in modo semplice." />
	<meta property="og:url"content="http://www.lorenzcrood.com/analisibacklinks/" />
	<meta property="og:title" content="Analizza e verifica i backlinks verso il tuo sito web in modo semplice." />
	<link rel="icon" type="image/png" sizes="16x16" href="https://cdn-ba05.kxcdn.com/wp-content/uploads/cropped-favicon.png">

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-71219869-2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-7121XXXX-2');
</script>


  </head>

  <body>
    <div style="background:#4e81d8" class="jumbotron">
      <div class="container">
      
            <div style="text-align:center">

        <h1 class="display-3" style="color:white"><strong>Analisi Backlinks e Verifica: SEO Tool Gratis (Backlinks Checker) &#x1F50D;</strong></h1>
        <br>
	<img alt="Backlinks Checker gratis" src="https://www.lorenzcrood.com/wp-content/uploads/gif.gif">

	</div>
      	<br>
        <p style="color:white">Progresso: <?= count($job->targets) - count($remainingTargets) ?> di <?= count($job->targets) ?> pagine completate.</p>
        <div class="progress">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?= 100 * (count($job->targets) - count($remainingTargets)) / count($job->targets) ?>%"></div>
        </div>
      <br>
      
  <h3 style="color:white"><a style="color:white" href="https://goo.gl/giHUWc" title="Esegui un altra verifica">> Esegui un'altra analisi gratuita!</a></h3>    



</div>        

      
      
      </div>
    </div>

    <div class="container">

<?php
if (count($remainingTargets) > 0)	{
	exit;
}

foreach ($job->targets as $target) {
  $statement = $database->prepare('SELECT * FROM spideredPages WHERE url = ?');
  $statement->execute([$target]);
  $foundLinks = json_decode($statement->fetch(PDO::FETCH_OBJ)->data);
  if (empty($foundLinks)) $foundLinks = [];
	$matchedLinks = array();
	foreach ($foundLinks as $foundLink) {
		foreach ($job->searchTerms as $searchTerm => $category) {
			if (false !== stripos($foundLink, $searchTerm)) {
				$matchedLinks[$foundLink] = $category;
			}
		}
	}
	if (count($matchedLinks) > 0) {
		echo '<p class="lead">Controllando '.htmlspecialchars($target).'</p>' . PHP_EOL;
		echo '<ul>' . PHP_EOL;
		foreach ($matchedLinks as $link => $category) {
			if ($category == 'mine') {
				echo '<li><span class="badge badge-success">Punta al tuo sito</span> '.htmlspecialchars($link).'</li>' . PHP_EOL;
			} else {
				echo '<li><span class="badge badge-danger">Punta al tuo competitor</span> '.htmlspecialchars($link).'</li>' . PHP_EOL;
			}
		}
		echo '</ul>' . PHP_EOL;
	}
	flush();
}
?>

    </div>
  </body>
</html>
