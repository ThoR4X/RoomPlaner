<?php

session_start();

function mainLogin() {

	/* Configuration */
	/*===============*/

	$LogoSource			    = 'style/images/Logo_Main.png';		   /* Pfad zum Logo */
	$redirectPath		    = '../index.php';						        /* Pfad für Weiterleitung nach erfolgreichem Login */
	$rememberMe					= 'Vergiss mich nicht!';						/* Text fuer Session dauerhaft speichern */
	$sessionCryptKey 		= '6M_qH]R)h#nDGx[2h[5-M{EM)';			/* In Session gespeicherter Key, der bei Login gesetzt wird */
	$cookieName					= 'sessionLogged';									/* Name des zu speichernden Cookies, wenn Session dauerhaft gespeichert werden soll */
	$cookieLifetime			= 28800;														/* Cookie Gueltigkeit in Sekunden, 0 = bis Browser geschlossen wird */
	$cookieToken				= 'brainFuck';											/* Token, der in Cookie gespeichert wird, wenn Session dauerhaft gespeichert werden soll */
	$LoginButtonLabel		= 'Login';													/* Text auf Login-Button */
	$LoginLockTimeSecs	= 1;																/* Zeit in Sekunden fuer Sperrung nach X fehlgeschlagenen Anmeldungen - wird exponenziell erh &oumlht */
	$LoginLockTrys			= 3;																/* Anzahl Login-Versuche, bevor Login fuer X Sekunden gesperrt wird */
	$LoginLockText1			= 'Anmeldung noch';									/* 1. Teil des Sperr-Textes VOR dem Countdown */
	$LoginLockText2			= 'gesperrt.';						          /* 2. Teil des Sperr-Textes NACH dem Countdown */

	/* Script */
	/*========*/

	if(isset($_POST['userpass1']) && isset($_POST['userpass2']) && $_POST['userpass1'] == $_POST['userpass2'] && isset($_SESSION['switchPW']) && $_SESSION['switchPW'] == $_POST['checksum']){
		changePassword($_POST['username'],$_POST['userpass1']);
	}

	elseif (isset($_POST['userpass1']) != isset($_POST['userpass2'])) {
		echo 'Kennw&ouml;rter stimmen nicht &uuml;berein';
	}

	/* Wenn Logout-Link verwendet wurde */
	if (isset($_GET['act'])){

		if($_GET['act'] == 'lgt'){
			unset($_SESSION['logged']);
			if(isset($_COOKIE[$cookieName])){
				setcookie($cookieName, '', 1);
			}
			if(isset($_GET['ss']) && $_GET['ss'] == 'rst'){
				session_destroy();
			}
			header("Location: index.php?site=adm");
		}
	}

	$animation = '';
	$failmessage = '';

	/* Login erfolgreich */
	if (isset($_SESSION['logged']) and $_SESSION['logged'] or (isset($_COOKIE[$cookieName]) and $_COOKIE[$cookieName] == $cookieToken) and !isset($_GET['act'])){
		echo "<script type=\"text/javascript\">addLog('".$_POST['username']."','Erfolgreicher Login-Versuch');</script>";
		login();
	}

	else {

	}

	/* ueberpruefung des eingegebenen Passworts und ggf. Erh &oumlhung der Fehlversuche*/
	if(isset($_POST['userpass']) && isset($_POST['username'])){

	      if(!empty($_POST['userpass']) && !empty($_POST['username'])) {

						$userName = strtolower($_POST['username']);
						$checkUser = checkUser($userName);

					if($checkUser == true){
						$checkpw = $_POST['userpass'];
						$salt	=	getSpice($userName,'salt');
						$pepper =	getSpice($userName,'pepper');
						$pwHash = HashCalc($salt,$pepper,$checkpw);
						$loginPW = getpwd($userName);
					}

					else {
						$loginPW = 'dooh!';
						$pwHash = 'foobar';
					}

		  		if ($pwHash == $loginPW) {
						$changePassword = true;

						if(wasHere($_POST['username']) == true){
								echo '<div class="logindiv" style="'.$animation.'">';
								$failmessage = '<span class="loginError">Beim ersten Anmelden muss das Kennwort ge&auml;ndert werden</span><br /><br />';
								$_SESSION['switchPW'] = randomstring();
								echo		'<form id="switchPW" name="switchPW" method="post" action="index.php">
													<img src="'.$logoSource.'" /><br />';
								echo 			$failmessage;
								echo			'<input class="definput" tabindex="400" name="userpass1" type="password" id="userpass1" placeholder="Neues Passwort" required /><br /><br />
													<input class="definput" tabindex="500" name="userpass2" type="password" id="userpass2" placeholder="Passwort wiederholen" required /><br /><br />
													<input type="hidden" name="username" value="'.$_POST['username'].'" />
													<input type="hidden" name="checksum" value="'.$_SESSION['switchPW'].'" />
													<input class="defButton" name="login" type="submit" id="login" value="&Auml;ndern" />
												</form>';
								echo '</div>';
						}

						else{
							if($_POST['rememberMe'] == 'true'){
								setcookie($cookieName,$cookieToken, time()+$cookieLifetime);
							}
							else {
			  				$_SESSION['logged'] = $sessionCryptKey;
							}

							unset($_SESSION['remember']);
			  			unset($_SESSION['fails']);
			  			unset($_SESSION['trys']);
							$_SESSION['knownUser'] = $_POST['username'];

							header("Location: index.php");
						}


		  		}

		  		else {
						$animation = 'animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;';
						$failmessage = '<span class="loginError">Benutzername oder Kennwort falsch</span><br /><br />';
		  			if(isset($_SESSION['trys'])) {
		  				$_SESSION['trys'] = $_SESSION['trys'] + 1;
		  				unset($_POST['userpass']);			}
		  			else {
		  				$_SESSION['trys'] = 1;
		  			}
		  		}
	  	}
  }

	/* Ueberpruefung Anzahl Fehlversuche und ggf. Sperrung der Anmeldung*/
	if ((!isset($_SESSION['logged']) or $_SESSION['logged'] != $sessionCryptKey) and (!isset($_COOKIE[$cookieName]) or $_COOKIE[$cookieName] != $cookieToken)){

		$deniesecs = $LoginLockTimeSecs;
		$logintry = $LoginLockTrys;
		echo "<script type=\"text/javascript\">addLog('Unknown','Fehlerhafter Login-Versuch. Anmeldedaten waren ".$_POST['username']." mit Passwort ".$_POST['userpass']."');</script>";

		if(isset($_SESSION['trys'])){

			$_SESSION['fails'] = $_SESSION['trys'];

			if($_SESSION['fails'] >= $logintry){

				if(!isset($_SESSION['remember'])){
					$_SESSION['remember'] = 0;
				}

				$_SESSION['remember'] = $_SESSION['remember']+$_SESSION['fails'];
				$_SESSION['denied'] = time()+($_SESSION['remember']*$deniesecs);
				unset($_SESSION['fails']);
				unset($_SESSION['trys']);
			}
		}

		if(isset($_SESSION['denied'])){

			$countdown = ceil($_SESSION['denied']-time());
			if ($countdown<0){
				$countdown = 0;
			}



			/* Anmelde-Formular, wenn gesperrt */
			echo '<div class="logindiv" style="'.$animation.'">';
			echo		'<form method="post" action="index.php">';
			echo 			'<img src="'.$LogoSource.'" /><br />';
	  	echo			'<input class="definput" name="username" type="text" placeholder="Benutzername" disabled /><br /><br />';
	  	echo			'<input class="definput" name="userpass" type="password" id="userpass" placeholder="Passwort" disabled /><br /><br />';
			echo 			'<span style="font-size: 14px;" id="lockText">'.$LoginLockText1.' <span id="time"></span> '.$LoginLockText2.'</span><br /><br />';
			echo			'<input class="defButton" name="login" type="submit" id="login" value="'.$LoginButtonLabel.'" disabled />';
	  	echo		'</form>';

			echo "<script type=\"text/javascript\">startTimer('".$countdown."', '#time');</script>";
			echo '</div>';
			if(isset($_POST['userpass'])){
				header("Location: index.php");
			}

			if($_SESSION['denied']-time() <= 0){
				unset($_SESSION['denied']);
			}
		}

		else{
			if(!isset($changePassword) && $changePassword == false){
				/* Anmelde-Formular */
				echo '<div class="logindiv" style="'.$animation.'">';
				echo		'<form name="loginForm" method="post" action="index.php">';
				echo 			'<img src="'.$LogoSource.'" /><br />';
				echo 			$failmessage;
				echo			'<input class="definput" tabindex="400" name="username" type="text" placeholder="Benutzername" /><br /><br />';
				echo			'<input class="definput" tabindex="500" name="userpass" type="password" id="userpass" placeholder="Passwort" /><br /><br />';
				echo 			'<input type="checkbox" id="c1" value="true" name="rememberMe" /><label for="c1"><span id="checkbox"></span><span style="font-size: 14px;">'.$rememberMe.'</span></label><br /><br />';
				echo			'<input class="defButton" name="login" type="submit" id="login" value="'.$LoginButtonLabel.'" />';
				echo		'</form>';
				echo '</div>';
			}

		}
	}
}


function login () {
	if(!isset($_SESSION['loggedin'])){
		$_SESSION['loggedin'] = session_id();
	}
	return;
}

function logged_in () {

		if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == session_id()){
			return true;
		}

		else {
			return false;
		}
}

function logout () {
	unset($_SESSION['logged']);
	unset($_SESSION['loggedin']);
	session_destroy();
	header("Refresh:0");
	return;
}

function getPage($currentpage) {
  switch ($currentpage) {
    case "main":
      $thispage = '&Uuml;bersicht';
      break;
    case "rooms":
      $thispage = 'Zimmer';
      break;
		case "guests":
	    $thispage = 'G&auml;ste';
	    break;
		case "checkin":
		  $thispage = 'Check-In';
		  break;
    case "settings":
      $thispage = 'Einstellungen';
      break;
    default:
      $thispage = '404';
  }

  return $thispage;
}

function generateNavi($active) {

  echo '<div class="navigation">
    <ul>
      <a href="index.php?site=main"><li id="'.$active[0].'">Belegungs-Plan</li></a>
			<a href="index.php?site=checkin"><li id="'.$active[3].'">Check-In</li></a>
      <a href="index.php?site=rooms"><li id="'.$active[1].'">Zimmer</li></a>
			<a href="index.php?site=guests"><li id="'.$active[2].'">G&auml;ste</li></a>
      <a href="index.php?site=settings"><li id="'.$active[4].'">Einstellungen</li></a>
    </ul>
		<div id="logoutButton" title="Logout" onclick="followURL(\'index.php?fct=logout\');"></div>
		<div id="helpButton" title="Hilfe" onclick="openWindow(\'index.php?fct=help\');"></div>
  </div>';
}

function randomstring($length = 30) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890@&%#?+"; //Bitte kein !-Zeichen -> Absturz!!!!!!!!
	srand((double)microtime()*1000000);
	$i = 0; // Counter auf null
	$ranstring = '';
	while ($i < $length) {
		$num = rand() % strlen($chars);
		$tmp = substr($chars, $num, 1);
		$ranstring = $ranstring . $tmp;
		$i++;
	}

	return $ranstring;
}

function getpwd($pwdtype) {
		$data = array();
	  $data = json_decode(file_get_contents('bin/users.json'), true);
	  foreach($data as $element){
	    if($element['user'] == $pwdtype){
				return $element['cryptKey'];
	    }
	  }
}

function getSpice($pwdtype,$spice) {
	$data = array();
  $data = json_decode(file_get_contents('bin/users.json'), true);
  foreach($data as $element){
    if($element['user'] == $pwdtype){
      $mySpice = $element[$spice];
			return $mySpice;
    }
  }
}

function HashCalc($salt,$pepper,$key) {
	$myHash = hash('sha512',hash('sha512',hash('sha512',hash('sha512',$salt).hash('sha512',$pepper))).hash('sha512',$key));
	return $myHash;
}

function getIndex($id, $array){
    foreach($array as $key => $value){
      if(is_array($value) && $value['id'] == $id){
      	return $key;
			}
    }
    return null;
}

function checkUser($user) {

  $data = array();
  $data = json_decode(file_get_contents('bin/users.json'), true);
	$isUser = false;
  foreach($data as $element){
    if($element['user'] == $user){
      $isUser = true;
    }
  }
  unset($data);
  return $isUser;
}

function wasHere($user){
	$data = array();
	$data = json_decode(file_get_contents('bin/users.json'), true);
	$myReturn = false;

	foreach ($data as $key => $entry) {
		if ($entry['user'] == $user) {
			if($data[$key]['wasHere'] == '0'){
				$myReturn = true;
			}
		}
	}

	unset($data);
  return $myReturn;
}

function changePassword($user,$newPW){

		$data = array();
	  $data = json_decode(file_get_contents('bin/users.json'), true);
		$changedIt = false;

		foreach ($data as $key => $entry) {
	    if ($entry['user'] == $user) {
				if($data[$key]['wasHere'] == '0'){
	        $data[$key]['wasHere'] = '1';
					$data[$key]['cryptKey'] = HashCalc($data[$key]['salt'],$data[$key]['pepper'],$newPW);
					$changedIt = true;
				}
	    }
		}

	  file_put_contents('bin/users.json', json_encode($data));
	  unset($data);
		return;
}

function getOccupancy() {
  $weekNames = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
  $monthNames = array('','Januar','Februar','M&auml;rz','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember');

  if(isset($_SESSION['roomDate'])){
    $myDate = explode('.',$_SESSION['roomDate']);
  }

  else{
    $month = date('m');
    $year = date('Y');
    $myDate = array($month,$year);
    $_SESSION['roomDate'] = $month.'.'.$year;
  }

  $jsonRooms = readJSON('bin/rooms.json');
  $jsonDates = readJSON('bin/occupancy.json');

  $month = $myDate[0];
  $year = $myDate[1];
  $i = 1;

  $time = strtotime($year.'-'.$month.'-01');
  $day = date("w", $time);

  $daySize = cal_days_in_month(CAL_GREGORIAN, $month, $year);
	$prevMonth = intval($month)-1;
	$nextMonth = intval($month)+1;
	$prevYear = intval($year)-1;
	$nextYear = intval($year)+1;

  echo '
    <table class="roomtable">
      <tr>
				<th>Zimmer</th>
				<th id="monthRow" colspan="'.$daySize.'">
					<center>
					<div class="monthSwitchHolder">
						<div id="backButtonYear" class="arrowLeftdouble" title="Jahr zur&uuml;ck" onclick="switchDate(\'y\',\''.intval($year).'\',\''.$prevYear.'\',\''.intval($month).'\');"></div>
						<div id="backButton" class="arrowLeft" title="Monat zur&uuml;ck" onclick="switchDate(\'m\',\''.intval($month).'\',\''.$prevMonth.'\',\''.$year.'\');"></div>
						<div class="monthName">
							<span id="activeMonth">'.$monthNames[intval($month)].'</span>
							<span id="currentYear">'.$year.'</span>
						</div>
						<div id="foreButton" class="arrowRight" title="Monat vor" onclick="switchDate(\'m\',\''.intval($month).'\',\''.$nextMonth.'\',\''.$year.'\');"></div>
						<div id="foreButtonYear" class="arrowRightdouble" title="Jahr vor" onclick="switchDate(\'y\',\''.intval($year).'\',\''.$nextYear.'\',\''.intval($month).'\');"></div>
						<div class="clear"></div>
					</div>
					</center>
				</th>
			</tr>
  ';

  echo '<tr id="cDayRow"><td></td>';
  while($i<=$daySize){
    if($day == 6 or $day == 0){
      echo '<td style="background-color: #c8c8c8;" class="calendarDays">'.$i.'<br />'.$weekNames[$day].'</td>';
    }
    else {
      echo '<td class="calendarDays">'.$i.'<br />'.$weekNames[$day].'</td>';
    }
    if($day == 6){
      $day = 0;
    }
    else {
      $day++;
    }
    $i++;
  }
  echo '</tr>';

	$toMark = array();
	$k = 0;
  foreach($jsonRooms as $rooms) {
    echo '<tr id="roomID_'.$rooms['id'].'" name="'.$k.'"><td class="roomName">'.$rooms['name'].'</td>';
		$k++;
    $i = 1;
    $day = date("w", $time);
    $occupancies = array();
    foreach($jsonDates as $occupancy){
      if($occupancy['roomID'] == $rooms['id']){
        $occupancies[] = $occupancy;
      }
    }

    while($i<=$daySize){
      if($day == 6 or $day == 0){
        $weekEndsColor = '#c8c8c8';
      }
      else{
        $weekEndsColor = 'none';
      }

      $thisDay = sprintf("%02d", $i);
      $thisDate = $thisDay.'.'.$month.'.'.$year;

      /*foreach($occupancies as $occupancy){
        if (($thisDate >= $occupancy['dateStart']) && ($thisDate <= $occupancy['dateEnd']))
        {
          $temp = explode('.',$thisDate);
          $toMark[$occupancy['roomID']][$temp[0]] = $temp[0];
					$toMark['occupied'.$i] = $occupancy['id'];
					$toMark['guest'.$i] = $occupancy['guest'];
					$toMark['comment'.$i] = $occupancy['comment'];
        }
      }

      if(sprintf("%02d", $i) == $toMark[$rooms['id']][sprintf("%02d", $i)]){
        $occupied = '<div class="occupied" style="background-color:'.$rooms['color'].'"></div>';
      }
      else {
        $occupied = '';
      }*/

			if($i<10){
				$tableDay = '0'.$i;
			}

			else {
				$tableDay = $i;
			}

      echo '<td name="'.$tableDay.'.'.$month.'.'.$year.'" style="background-color: '.$weekEndsColor.';"></td>';
			/*	if(strlen($occupied) > 0){
					echo 'id="occupied_'.$toMark['occupied'.$i].'" class="occupied" title="Gast: '.$toMark['guest'.$i].' - Kommentar: '.$toMark['comment'.$i].'" onclick="editOccupancy(\''.$toMark['occupied'.$i].'\');"';
				}
			echo '><center>'.$occupied.'</center></td>';*/

      if($day == 6){
        $day = 0;
      }
      else {
        $day++;
      }
      $i++;
    }
    echo '</tr>';
  }
  echo '
      <tr><th>Zimmer</th><th colspan="31"></th></tr>
    </table>
  ';
}

function manageRooms() {
  $jsonRooms = readJSON('bin/rooms.json');
  $jsonDates = readJSON('bin/occupancy.json');

  echo '<form name="roomManager"><table id="manageRooms" class="roomtable"><tr><th width="190px">Zimmer</th><th width="25px">Art</th><th>Preis</th><th>Farbe</th><th width="20px"><div title="Neues Zimmer" class="addicon" onclick="addRoom();"></div></th></tr>';
	if(count($jsonRooms) == 0){
		echo '<tr id="noRoomsHere"><td colspan="5"><center><i>Keine Zimmer vorhanden</i></center></td></tr>';
	}
	else {
		foreach($jsonRooms as $rooms){
	    echo '<tr id="room_'.$rooms['id'].'" class="oldRoom">
	            <td><input id="roomName" type="text" title="Anzeige-Name" maxlength="20" class="hiddenInput" value="'.$rooms['name'].'" placeholder="Zimmername '.$rooms['id'].'" name="oldName_'.$rooms['id'].'" required /></td>
	            <td><input id="roomType" type="text" class="hiddenInput" maxlength="2" placeholder="Art" name="oldType_'.$rooms['id'].'" title="Zimmer-Art" value="'.$rooms['size'].'" required /></td>
							<td><input id="roomPrice" type="text" class="hiddenInput" name="oldPrice_'.$rooms['id'].'" value="'.$rooms['price'].'" required /></td>
							<td><center><input title="Anzeige-Farbe" id="roomColor" name="oldColor_'.$rooms['id'].'" class="jscolor" value="'.$rooms['color'].'" readonly required /></center></td>
	            <td><div title="Entfernen" id="roomDelete_'.$rooms['id'].'" class="deleteicon" onclick="delRoom(\''.$rooms['id'].'\');"></div></td>
						</tr>
						';
							/*<div class="roomColor" title="'.$rooms['color'].'" style="background-color:'.$rooms['color'].';"></div>*/
	  }
	}
	echo '</table><center><span class="anouncement"></span></center>';
	echo '<center><input style="margin-top: 10px;" type="button" name="submit" class="defButton" value="Speichern" onclick="saveRooms();" /> <input type="button" class="defButton" value="Reset" onclick="followURL(\'index.php?site=rooms\');" /></center>';
}

function manageGuests() {
	$jsonGuests = readJSON('bin/guests.json');
  $jsonDates = readJSON('bin/occupancy.json');

  echo '<form ><table id="manageRooms" class="roomtable"><tr><th width="120px">Name</th><th width="120px">Vorname</th><th width="250px">Adresse</th><th>Belegungen</th><th width="20px"><div title="Neuer Gast" class="addicon" onclick="addGuest();"></div></th></tr>';
	if(count($jsonGuests) == 0){
		echo '<tr id="noGuestsHere"><td colspan="5"><center><i>Keine G&auml;ste vorhanden</i></center></td></tr>';
	}
	else {
		foreach($jsonGuests as $guests){

			$occCount = 0;
			foreach($jsonDates as $element){
	      if($element['guestID'] == $guests['id']){
	        $occCount++;
	      }
	    }

			echo '<tr id="guest_'.$guests['id'].'" class="oldGuest">
	            <td id="guestName"><input id="guestName" type="text" title="Nachname" maxlength="20" class="hiddenInput" value="'.$guests['name'].'" placeholder="Nachname '.$guests['id'].'" name="oldName_'.$guests['id'].'" required /></td>
	            <td id="guestVorname"><input id="guestVorname" type="text" class="hiddenInput" maxlength="20" placeholder="Vorname" name="oldVorname_'.$guests['id'].'" title="Vorname" value="'.$guests['vorname'].'" required /></td>
	            <td id="guestAddress"><input title="Adresse" class="hiddenInput" id="guestAddress" name="oldAddress_'.$guests['id'].'" value="'.$guests['adresse'].'" required /></td>
							<td id="guestVisits">'.$occCount.'</td>
							<td id="guestDelete"><div title="Entfernen" id="guestDelete_'.$guests['id'].'" class="deleteicon" onclick="delGuest(\''.$guests['id'].'\');"></div></td>
						</tr>
						';
							/*<div class="roomColor" title="'.$rooms['color'].'" style="background-color:'.$rooms['color'].';"></div>*/
	  }
	}
	echo '</table><center><span class="anouncement"></span></center>';
	echo '<center><input style="margin-top: 10px;" type="button" class="defButton" value="Speichern" onclick="saveGuests();" /> <input type="button" class="defButton" value="Reset" onclick="followURL(\'index.php?site=guests\');" /></center>';
	return;
}

function readJSON($jsonPath) {

	$jsonData = array();
  $jsonData = json_decode(file_get_contents($jsonPath), true);

	return $jsonData;
}

function occupancyAdder() {
	echo '
	<center><div class="newOccupancy">
		<span id="headline"><center>Neue Belegung</center></span>
		<form method="post" action="bin/roomManagement.php" name="occupancy"><br />
		<input type="hidden" id="action" name="action" value="addOccupancy" />
		<input type="hidden" id="action" name="occID" value="empty" />
		<div class="input-daterange">
			<input type="text" name="dateStart" id="datepicker" class="definput" placeholder="Datum von" required />
			<input type="text" name="dateEnd" id="datepicker" class="definput" placeholder="Datum bis" required />
		</div>
		<select class="definput" name="roomID" id="roompicker" placeholder="Zimmer" title="Zimmer w&auml;hlen" required ><option disabled selected>Zimmer</option>';

	$myRooms = readJSON('bin/rooms.json');
	foreach($myRooms as $room){
		echo '<option value="'.$room['id'].'">'.$room['name'].'</option>';
	}

	echo '
		</select>
		<div class="livesearchGroup">
			<input type="text" name="guest" class="definput" placeholder="Gast - Name oder Adresse suchen" onkeyup="showResult(this.value)" required />
			<input type="hidden" name="knownGuest" value="" />
			<div id="livesearch" class="livesearch"></div>
		</div>
		<input type="text" name="comment" class="definput" placeholder="Bemerkungen" />
		<div class="clear"></div>
		<center><div class="buttonHolder">
			<input id="occSave" class="defButton" type="submit" value="Speichern" title="Speichern" />
			<input disabled="disabled" type="button" id="occDelete" class="defButton" value="L&ouml;schen" />
			<input id="occAbort" type="reset" value="Reset" title="Reset" onclick="" class="defButton" />
		</div></center></form>
	</div></center>';
}

function startsWith($check, $startStr) {
	if (!is_string($check) || !is_string($startStr) || strlen($check)<strlen($startStr)) {
	  return false;
	}

  return (substr($check, 0, strlen($startStr)) === $startStr);
}

function endsWith($check, $endStr) {
  if (!is_string($check) || !is_string($endStr) || strlen($check)<strlen($endStr)) {
    return false;
  }

  return (substr($check, strlen($check)-strlen($endStr), strlen($endStr)) === $endStr);
}

function editSettings () {

	echo '<div class="subtabs">
					<ul>
						<li class="subtab" name="1" onclick="switchActiveSub(\'1\');" id="active" title="Benutzer-Verwaltung">Benutzer</li>
						<li class="subtab" name="2" onclick="switchActiveSub(\'2\');" id="" title="Informationen">Info</li>
					</ul>
				</div>
				<div class="clear"></div><br /><br />';

	echo '
	<div id="subcontent_1" class="adminOption" style="display: block;">
		<table class="logtable" id="userList">
		<colgroup>
			<col width="150px">
			<col width="150px">
			<col width="50px">
			<col width="10px">
			<col width="10px">
		</colgroup>
		<tr><th>Benutzer</th><th>Typ</th><th>Angemeldet</th><th></th><th><div class="addUser" title="Hinzuf&uuml;gen" onclick="addUser()"><center><img src="style/images/mastericons/add.png" /></center></div></th></tr>';
	echo "<script type=\"text/javascript\">getUsers();</script>";
	echo '</table></div>';

	echo '<div id="subcontent_2" class="adminOption" style="display: none;">';
					swInfo();
	echo '</div>';
}

/* Check-In Panel */
/******************/

function checkinPanel() {
	echo '<div class="subtabs">
					<ul>
						<li class="subtab" name="1" onclick="switchActiveSub(\'1\');" id="active" title="Alle">Alle</li>
						<li class="subtab" name="2" onclick="switchActiveSub(\'2\');" id="" title="Check-In">Check-In</li>
						<li class="subtab" name="3" onclick="switchActiveSub(\'3\');" id="" title="Check-Out">Check-Out</li>
						<li class="subtab" name="4" onclick="switchActiveSub(\'4\');" id="" title="Archiv">Archiv</li>
					</ul>
				</div>
				<div class="clear"></div><br /><br />';

	echo '
	<div id="subcontent_1" class="adminOption" style="display: block;">
		Check-In und -Out
	</div>';

	echo '
	<div id="subcontent_2" class="adminOption" style="display: none;">
		Check-In
	</div>';

	echo '
	<div id="subcontent_3" class="adminOption" style="display: none;">
		Check-Out
	</div>';

	echo '
	<div id="subcontent_4" class="adminOption" style="display: none;">
		Archiv
	</div>';
}

function getHelp($lang){
	echo 'this help is in language '.$lang;
	header( 'Location: bin/help/'.$lang.'/index.php' );
	return;
}

function swInfo(){
	echo '<span class="headline1">Aktuelle Version: <i>undefined</i></span><br /><br />';
	echo '<span class="headline2">Änderungen in dieser Version</span><br />
				<ul>
					<li>Hilfe hinzugefügt</li>
					<li>Versions-Historie begonnen</li>
				</ul>
	';
}

?>
