<?php

session_start();
include_once('functions.php');

$action = $_POST['action'];
$roomPath = 'rooms.json';
$occupancyPath = 'occupancy.json';

if($action == 'addRooms'){

  $roomName = $_POST['roomName'];
  $roomColor = $_POST['roomColor'];
  $roomType = $_POST['roomType'];
  $roomPrice = $_POST['roomPrice'];

  $elements = count($roomName);
  $count = 0;

  $room_array = array();
  $room_array = json_decode(file_get_contents($roomPath), true);

  while($count < $elements) {
    $i = 0;
    $inArray = true;
    while($inArray == true){
      $inArray = false;
      foreach($room_array as $element){
        while($element['id'] == $i){
          $inArray = true;
          $i++;
        }
      }
    }

    $upload_info = array('id'=>$i,'name'=>$roomName[$count],'color'=>$roomColor[$count],'size'=>$roomType[$count],'price'=>$roomPrice[$count]);
    var_dump($upload_info);
    array_push($room_array, $upload_info);
    $count++;
  }

  file_put_contents($roomPath, json_encode($room_array));
  unset($room_array);

  return;
}

else if($action == 'deleteRooms'){
  $toRemove = $_POST['roomIDs'];

  $room_array = array();
  $room_array = json_decode(file_get_contents($roomPath), true);
  $occupancy_array = array();
  $occupancy_array = json_decode(file_get_contents($occupancyPath), true);

  $elements = count($toRemove);
  $count = 0;

  while($count < $elements){
    $thisRemove = explode('_',$toRemove[$count]);
    foreach($room_array as $element){
      if($element['id'] == $thisRemove[1]){
        $id = $element['id'];
        $toUnset = getIndex($id, $room_array);
        unset($room_array[$toUnset]);
      }
    }

    foreach($occupancy_array as $element){
      if($element['roomID'] == $thisRemove[1]){
        $id = $element['id'];
        $toUnset = getIndex($id, $occupancy_array);
        unset($occupancy_array[$toUnset]);
      }
    }

    $count++;
  }

  file_put_contents($roomPath, json_encode($room_array));
  file_put_contents($occupancyPath, json_encode($occupancy_array));
  unset($room_array);
  unset($occupancy_array);
  return;
}

else if($action == 'editRooms') {
  $roomID = $_POST['roomID'];
  $roomName = $_POST['roomName'];
  $roomColor = $_POST['roomColor'];
  $roomType = $_POST['roomType'];
  $roomPrice = $_POST['roomPrice'];

  $room_array = array();
  $room_array = json_decode(file_get_contents($roomPath), true);

  foreach ($room_array as $key => $element) {
    if (array_key_exists($element['id'], $roomID)) {
      $room_array[$key]['name'] = $roomName[$element['id']];
      $room_array[$key]['color'] = $roomColor[$element['id']];
      $room_array[$key]['size'] = $roomType[$element['id']];
      $room_array[$key]['price'] = $roomPrice[$element['id']];
    }
  }

  file_put_contents($roomPath, json_encode($room_array));
  unset($room_array);
  return;
}

else if($action == 'addOccupancy') {
  $roomID = $_POST['roomID'];
  $dateStart = $_POST['dateStart'];
  $dateEnd = $_POST['dateEnd'];
  $guest = $_POST['knownGuest'];
  $comment = $_POST['comment'];

  $occupancy_array = array();
  $occupancy_array = json_decode(file_get_contents($occupancyPath), true);

  $i = 0;
  $inArray = true;
  while($inArray == true){
    $inArray = false;
    foreach($occupancy_array as $element){
      while($element['id'] == $i){
        $inArray = true;
        $i++;
      }
    }
  }

  $upload_info = array('id'=>$i,'roomID'=>$roomID,'dateStart'=>$dateStart,'dateEnd'=>$dateEnd,'guestID'=>$guest,'comment'=>$comment);
  var_dump($upload_info);
  array_push($occupancy_array, $upload_info);

  file_put_contents($occupancyPath, json_encode($occupancy_array));
  unset($occupancy_array);

  header("location: ../index.php");

  return;
}

else if($action == 'editOccupancy') {
  $id = $_POST['occID'];
  $dateStart = $_POST['dateStart'];
  $dateEnd = $_POST['dateEnd'];
  $roomID = $_POST['roomID'];
  $guest = $_POST['knownGuest'];
  $comment = $_POST['comment'];

  $occupancy_array = array();
  $occupancy_array = json_decode(file_get_contents($occupancyPath), true);

  foreach ($occupancy_array as $key => $element) {
    if ($element['id'] == $id) {
      $occupancy_array[$key]['dateStart'] = $dateStart;
      $occupancy_array[$key]['dateEnd'] = $dateEnd;
      $occupancy_array[$key]['roomID'] = $roomID;
      $occupancy_array[$key]['guestID'] = $guest;
      $occupancy_array[$key]['comment'] = $comment;
    }
  }

  file_put_contents($occupancyPath, json_encode($occupancy_array));
  unset($occupancy_array);
  header("location: ../index.php");
  return;
}

else if($action == 'deleteOccupancy') {
  $toRemove = $_POST['occID'];

  $occupancy_array = array();
  $occupancy_array = json_decode(file_get_contents($occupancyPath), true);

  foreach($occupancy_array as $element){
    if($element['id'] == $toRemove){
      $id = $element['id'];
      $toUnset = getIndex($id, $occupancy_array);
      unset($occupancy_array[$toUnset]);
    }
  }

  file_put_contents($occupancyPath, json_encode($occupancy_array));
  unset($occupancy_array);
  return;
}

else if($action == 'getOccupancy') {
  $occupancy_array = array();
  $occupancy_array = json_decode(file_get_contents($occupancyPath), true);
  $id = $_POST['id'];

  foreach ($occupancy_array as $key => $element) {
    if ($element['id'] == $id) {
      $myIndex = getIndex($id, $occupancy_array);
      $myOutput = $occupancy_array[$myIndex];
    }
  }
  echo json_encode($myOutput);
  return;
  /*echo '['.json_encode($myOutput).']';*/
}

else if($action == 'getOccupancies') {
  $occupancy_array = array();
  $occupancy_array = json_decode(file_get_contents($occupancyPath), true);
  $room_array = array();
  $room_array = json_decode(file_get_contents($roomPath), true);

  $roomID = explode('_',$_POST['roomID']);
  $month = $_POST['month'];
  $year = $_POST['year'];
  $myOutput = array();

  $i = 0;
  foreach ($occupancy_array as $key => $element){
    if($element['roomID'] == $roomID[1]) {
      if(endsWith($element['dateStart'], $month.'.'.$year) || endsWith($element['dateEnd'], $month.'.'.$year)){
        $myIndex = getIndex($element['id'], $occupancy_array);
        $myOutput[$i] = $occupancy_array[$myIndex];
        foreach ($room_array as $room){
          if($room['id'] == $roomID[1]){
            $myOutput[$i]['color'] = $room['color'];
          }
        }
        $i++;
      }
    }
  }

  /*$myOutput['color'] = 'ff0000';*/

  echo json_encode($myOutput);;
  return;
}

else {
  return;
}

?>
