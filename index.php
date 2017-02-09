<html lang="de">

<!-- Eingebundene Style-Dokumente -->
<link rel="stylesheet" type="text/css" href="style/css/normalize.css">
<link rel="stylesheet" type="text/css" href="style/css/main.css">
<link rel="stylesheet" type="text/css" href="style/css/datepicker.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Condensed">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<!-- ============================ -->


<!-- Eingebundene Skripts -->
<script src="bin/js/jquery.js"></script>
<script src="bin/js/main.js"></script>
<script src="bin/js/jscolor.js"></script>
<script src="bin/js/datepicker.js"></script>
<script src="bin/js/datepicker.de.min.js"></script>
<!-- ==================== -->

<?php

# Eingebundene PHP-Skripte #
include_once('bin/functions.php');
############################

$lang = 'DE';

if ( !logged_in() ) {
    mainLogin();
}

if ( logged_in() ) {

  if(isset($_GET['fct'])){
    if($_GET['fct'] == 'logout'){
      logout();
    }
    else if($_GET['fct'] == 'help'){
      getHelp($lang);
    }
  }

  if (isset($_GET['site'])) {
  	$currentpage = $_GET['site'];

  	switch ($currentpage) {
  		case "main":
  			$active = array('active','normal','normal','normal','normal');
  			break;
  		case "rooms":
  			$active = array('normal','active','normal','normal','normal');
  			break;
      case "guests":
    		$active = array('normal','normal','active','normal','normal');
    		break;
      case "checkin":
      	$active = array('normal','normal','normal','active','normal');
      	break;
  		case "settings":
  			$active = array('normal','normal','normal','normal','active');
  			break;
  		default:
        $currentpage = '404';
  			$active = array('normal','normal','normal','normal','normal');
  		break;
  	}
  }
  else {
  	$currentpage = 'main';
  	$active = array('active','normal','normal','normal','normal');
  }

  echo '<head><meta charset="utf-8"><title>roomPlaner - '.getPage($currentpage).'</title></head><body>';

  echo '<div class="naviholder">';
      generateNavi($active);
  echo '</div>';

  echo '
    <div class="contentHolder">
      <div class="content">';

  if($currentpage){
    if($currentpage == 'main'){
      getOccupancy();
    }
    else if($currentpage == 'rooms'){
      manageRooms();
    }

    else if($currentpage == 'guests'){
      manageGuests();
    }

    else if($currentpage == 'checkin'){
      checkinPanel();
    }

    else if($currentpage == 'settings'){
      editSettings();
    }
    else if($currentpage == '404'){
      echo '<center><img src="style/images/na.png" /><br />Seite nicht vorhanden</center>';
    }
  }

  else {
    echo 'Swoffle';
  }

  echo '</div><div class="clear"></div>
    </div>
    <div class="footer">';
    if($currentpage == 'main'){
      occupancyAdder();
    }
  echo '</div>
  ';
}

?>
