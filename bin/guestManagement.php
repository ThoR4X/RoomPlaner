<?php

session_start();
include_once('functions.php');

$action = $_POST['action'];
$guestPath = 'guests.json';
$occupancyPath = 'occupancy.json';

if($action == 'addGuests'){

  $guestName = $_POST['guestName'];
  $guestVorname = $_POST['guestVorname'];
  $guestAddress = $_POST['guestAddress'];

  $elements = count($guestName);
  $count = 0;

  $guest_array = array();
  $guest_array = json_decode(file_get_contents($guestPath), true);

  while($count < $elements) {
    $i = 0;
    $inArray = true;
    while($inArray == true){
      $inArray = false;
      foreach($guest_array as $element){
        while($element['id'] == $i){
          $inArray = true;
          $i++;
        }
      }
    }

    $upload_info = array('id'=>$i,'name'=>$guestName[$count],'vorname'=>$guestVorname[$count],'adresse'=>$guestAddress[$count]);
    array_push($guest_array, $upload_info);
    $count++;
  }

  file_put_contents($guestPath, json_encode($guest_array));
  unset($guest_array);

  return;
}

else if($action == 'addSingleGuest'){
  $guestName = $_POST['guestName'];
  $guestVorname = $_POST['guestVorname'];
  $guestAddress = $_POST['guestAddress'];

  $guest_array = array();
  $guest_array = json_decode(file_get_contents($guestPath), true);

  $i = 0;
  $inArray = true;
  while($inArray == true){
    $inArray = false;
    foreach($guest_array as $element){
      while($element['id'] == $i){
        $inArray = true;
        $i++;
      }
    }
  }

  $upload_info = array('id'=>$i,'name'=>$guestName,'vorname'=>$guestVorname,'adresse'=>$guestAddress);
  array_push($guest_array, $upload_info);

  file_put_contents($guestPath, json_encode($guest_array));
  unset($guest_array);
  echo $i;
  return;
}

else if($action == 'deleteGuests'){
  $toRemove = $_POST['guestIDs'];

  $guest_array = array();
  $guest_array = json_decode(file_get_contents($guestPath), true);
  $occupancy_array = array();
  $occupancy_array = json_decode(file_get_contents($occupancyPath), true);

  $elements = count($toRemove);
  $count = 0;

  while($count < $elements){
    $thisRemove = explode('_',$toRemove[$count]);
    foreach($guest_array as $element){
      if($element['id'] == $thisRemove[1]){
        $id = $element['id'];
        $toUnset = getIndex($id, $guest_array);
        unset($guest_array[$toUnset]);
      }
    }

    foreach($occupancy_array as $element){
      if($element['guestID'] == $thisRemove[1]){
        $id = $element['id'];
        $toUnset = getIndex($id, $occupancy_array);
        unset($occupancy_array[$toUnset]);
      }
    }

    $count++;
  }

  file_put_contents($guestPath, json_encode($guest_array));
  file_put_contents($occupancyPath, json_encode($occupancy_array));
  unset($guest_array);
  unset($occupancy_array);
  return;
}

else if($action == 'editGuests') {
  $guestID = $_POST['guestID'];
  $guestName = $_POST['guestName'];
  $guestVorname = $_POST['guestVorname'];
  $guestAddress = $_POST['guestAddress'];

  $guest_array = array();
  $guest_array = json_decode(file_get_contents($guestPath), true);

  foreach ($guest_array as $key => $element) {
    if (array_key_exists($element['id'], $guestID)) {
      $guest_array[$key]['name'] = $guestName[$element['id']];
      $guest_array[$key]['vorname'] = $guestVorname[$element['id']];
      $guest_array[$key]['adresse'] = $guestAddress[$element['id']];
    }
  }

  file_put_contents($guestPath, json_encode($guest_array));
  unset($guest_array);
  return;
}

else if($action == 'getGuest'){
  $guestID = $_POST['guestID'];
  $guest_array = array();
  $guest_array = json_decode(file_get_contents($guestPath), true);

  foreach ($guest_array as $key => $element) {
    if ($element['id'] == $guestID) {
      $myIndex = getIndex($guestID, $guest_array);
      $myOutput = $guest_array[$myIndex];
    }
  }
  echo json_encode($myOutput);
  return;
}

else {
  return;
}

?>
