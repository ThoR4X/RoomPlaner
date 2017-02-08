<?php
session_start();
include_once('functions.php');

$action = $_POST['action'];
$filePath = 'users.json';

if($action == 'delete'){
  $id = $_POST['id'];

  $data = array();
  $data = json_decode(file_get_contents($filePath), true);

  foreach($data as $element){
    if($element['id'] == $id){
      if($element['state'] == 'admin'){
        $i = 0;
        foreach($data as $element){
          if($element['state'] == 'admin'){
            $i++;
          }
        }
        if($i <= 1){
          echo '0';
          return;
        }
      }
      $toUnset = getIndex($id, $data);
      unset($data[$toUnset]);
      echo '1';
    }
  }

  file_put_contents($filePath, json_encode($data));
  unset($data);
  return;
}

else if($action == 'add'){

  $myUser = strtolower($_POST['userName']);
  $mySalt = randomstring();
  $myPepper = randomstring();

  if($_POST['userpass1'] != $_POST['userpass2']){
    header("location: ../index.php?site=settings");

  }

  else {
    $myCrypt = HashCalc($mySalt,$myPepper,$_POST['userpass1']);

    if($_POST['admin'] == 'true'){
      $myState = 'admin';
    }

    else {
      $myState = 'user';
    }

    $temp_array = array();
    $temp_array = json_decode(file_get_contents($filePath), true);
    $i = 0;

    $inArray = true;
    while($inArray == true){
      $inArray = false;
      foreach($temp_array as $element){
        while($element['id'] == $i){
          $inArray = true;
          $i++;
        }
      }
    }

    $upload_info = array('id'=>$i,'user'=>$myUser,'cryptKey'=>$myCrypt,'salt'=>$mySalt,'pepper'=>$myPepper,'state'=>$myState,'wasHere'=>'0');
    array_push($temp_array, $upload_info);

    file_put_contents($filePath, json_encode($temp_array));
    unset($temp_array);
    header("location: ../index.php?site=settings");
  }
  return;
}

?>
