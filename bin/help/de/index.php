
<html lang="de">
<head>
  <meta charset="utf-8" />
  <link rel="stylesheet" type="text/css" href="../../../style/css/normalize.css">
  <link rel="stylesheet" type="text/css" href="../../../style/css/help.css">
</head>

<div class="sidebar">
  <div id="content">
    <p id="navHead">Inhalte</p>

<?php
$pages = array();
$i = 0;
if ($handle = opendir('.')) {
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != ".." && $entry != "index.php") {
      if( ($sHTML = file_get_contents($entry)) && preg_match("/<title>(.+)<\/title>/i", $sHTML, $aTitle)) {
        $pages[$i]['file'] = $entry;
        $pages[$i]['title'] = $aTitle[1];
        $i++;
      }
    }
  }
  closedir($handle);
  uasort($pages, function ($a, $b) { return $a['title'] - $b['title']; });
  foreach($pages as $page){
    echo '<a href="index.php?page='.$page['file'].'">'.$page['title'].'</a><br/>';
  }
}
?>

  </div>
</div>
<div class="mainframe">

<?php
if(isset($_GET['page'])){
  if(file_exists($_GET['page'])){
    $sHTML = file_get_contents($_GET['page']);
    echo $sHTML;
  }
  else {
    echo 'Bitte eine Hilfe-Seite ausw&auml;hlen';
  }
}

else {
  $sHTML = file_get_contents('introduction.htm');
  echo $sHTML;
}
?>

</div>
<div class="clear"></div>
