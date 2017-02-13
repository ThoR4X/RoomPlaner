
$(document).ready(function(){

  $.ajaxSetup({ cache: false });

  $(window).scroll(function(){
    if ($(this).scrollTop() > 80) {
      $('.scrollButton').fadeIn(100);
    } else {
      $('.scrollButton').fadeOut(100);
    }
  });

  $('.scrollButton').click(function(){
    $("html, body").animate({ scrollTop: 0 }, 500);
    return false;
  });

  $('div#overlay').on('click', function(e) {
    if (e.target !== this) return;
    removeOverlay();
  });

  $('a#noLink').click(function(event){
    event.preventDefault();
    event.stopPropagation();
  });

  $('form#switchPW').submit(function() {
      if($('input#userpass1').val() != $('input#userpass2').val()) {
        alert(unescape('Die Kennw%F6rter stimmen nicht %FCberein'));
        return false;
      }
      else if ($('input#userpass1').val() == '' || $('input#userpass2').val() == '') {
        alert(unescape('Die Kennw%F6rter d%FCrfen nicht leer sein'));
        return false;
      }
  });

  $("form#addUser").bind('ajax:complete', function() {
    alert('It works!');
    addLog('Benutzer "'+$('input#userName').val()+'" wurde hinzugef&uuml;gt.');
  });

  $( 'input#roomName' ).on('change paste keyup', function() {
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
  });

  $( 'input#roomType' ).on('change paste keyup', function() {
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
  });

  $( 'input#roomPrice' ).on('change paste keyup', function() {
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
    if(!this.value.match(/^\d{1,3}(\.\d{3})*\,\d{2}$/)){
      $('form[name="roomManager"] input[name="submit"]').prop('disabled', true);
      $( 'span.anouncement' ).html( 'Bitte nur g&uuml;ltige Preise mit zwei Nachkommastellen verwenden (z.B. 50,00)' );
    }
    else {
      $('form[name="roomManager"] input[name="submit"]').prop('disabled', false);
    }
  });

  $( 'input#roomColor' ).on('change paste keyup', function() {
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
  });

  $('.input-daterange').datepicker({
    format: 'dd.mm.yyyy',
    language: 'de',
    clearBtn: 'linked',
    calendarWeeks: true,
    orientation: "top auto",
    autoclose: true,
    todayHighlight: true
  });

  $('input#occDelete').on('click', function(){
    var id = $('input[name="occID"]').val();
    occDelete(id);
  });

  if($('table.roomtable').length > 0){
    var ind = 0;
    var toDay = new Date();
    var thisMonth = toDay.getMonth()+1;
    var thisYear = toDay.getFullYear();

    while($('tr[name="'+ind+'"]').length > 0){
      var roomID = $('tr[name="'+ind+'"]').attr('id');
      markOccupancies(thisMonth,thisYear,roomID);
      ind++;
    }
  }


  $('#livesearch').click(function(event){
      $(this).data('clicked', true);
  });

  $(window).click(function() {
    if(!$('#livesearch').data('clicked') && $('#livesearch').length > 0){
      document.getElementById('livesearch').innerHTML="";
      document.getElementById('livesearch').style.border="0px";
      $('#occSave').attr('style','');
      $('#occAbort').attr('style','');
      $('#occDelete').attr('style','');
      $('#livesearch').data('clicked', false);
    }
    return;
  });

  $(document).on('click', 'form[name="occupancy"] input[type=submit]', function(e) {
    if($('input[name="knownGuest"]').val() == '') {
      alert('Bitte vorhandenen Gast auswählen oder einen neuen anlegen.');
      e.preventDefault(); //prevent the default action
    }
  });

  $(document).on('click', 'form[name="roomManager"] input[name="submit"]', function(e) {
    /*$('#roomPrice').each(function(){*/
    return false;
    alert('test');
    /*});
    if(document.getElementById('roomPrice').value.match(/^\d{1,3}(\.\d{3})*\,\d{2}$/) == false){
      alert('Bitte gültige Preise eingeben');
      e.preventDefault();
    }*/
  });


});


/* Main-Functions */
/******************/

function closeCookielaw(){
  $( "div" ).remove( ".cookielaw" );
  $.post("index.php", {"cookielaw": true});
}

function followURL(url,id) {
  window.location.href = url;
  return;
}

function openWindow(url) {
  window.open(url,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=350,width=1024,height=800');
  return;
}

function switchActiveSub(id) {
  $('.subtab#active').attr('id','');
  $('li[name="'+id+'"]').attr('id','active');
  $('div.adminOption').hide();
  $('div#subcontent_'+id).fadeIn("fast");;
  return;
}

/* Loading-Overlay */
/*******************/

function displayOverlay() {
    $('<div id="loadOverlay"><div class="spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div></div>').appendTo("body");
}

function removeOverlay() {
  $("div#loadOverlay").remove();
  $("div#overlay").remove();
}

/* Window-Overlay */
/******************/

function windowOverlay() {
  $('<div id="overlay"><div class="windowOverlay" onclick="nothing()"><div id="closer" title="Schliessen" onclick="removeOverlay()"></div></div></div>').appendTo("body");
}

/* Kalender-Stuff */
/******************/

function getMonthDays(monthID,year){
	if(monthID == '4' || monthID == '6' || monthID == '9' || monthID == '11'){
		maxDays = 30;
	}
	else if(monthID == '2'){
		if((year % 4) == 0 && (year % 100) != 0){
			maxDays = 29;
		}
		else {
			maxDays = 28;
		}
	}

	else {
		maxDays = 31;
	}

	return maxDays;
}

function getWeekDay(dd,mm,yyyy) {
  var month = "312831303130313130313031";
  var days = (yyyy-1)*365 + (dd-1);
  for(var i=0;i<mm-1;i++) days += month.substr(i*2,2)*1;

  if(yyyy>1582 || yyyy==1582 && (mm>10 || mm==10 && dd >4)) days -= 10;

  var leapyears = Math.floor(yyyy / 4);
  if(yyyy%4==0 && mm<3) leapyears--;
  if(yyyy>=1600) {
    leapyears -= Math.floor((yyyy-1600) / 100);
    leapyears += Math.floor((yyyy-1600) / 400);
    if(yyyy%100==0 && mm<3) {
      leapyears++;
      if(yyyy%400==0) leapyears--;
    }
  }
  days += leapyears;

  var week = "SaSoMoDiMiDoFr";
  return week.substr(days%7*2,2);
}


function switchDate(type,currentValue,targetValue,dateVal){

  var monthNames = new Array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");

  if(type == 'm'){
    currentMonth = parseInt(currentValue);
    month = parseInt(targetValue);
    year = parseInt(dateVal);
    nextMonth = parseInt(month) + 1;
    prevMonth = parseInt(month) - 1;
    prevYear = parseInt(year)-1;
    nextYear = parseInt(year)+1;

    if(nextMonth > 12){
      nextMonth = 1;
    }

    if(prevMonth < 1){
      prevMonth = 12;
    }


    $('#backButtonYear').attr('onclick','switchDate(\'y\','+year+','+prevYear+','+month+')');
    $('#foreButtonYear').attr('onclick','switchDate(\'y\','+year+','+nextYear+','+month+')');

    if(currentMonth == 1 && month == 12 || currentMonth == 12 && month == 1){
      if(currentMonth == 12){
        year = parseInt(year)+1;
        prevYear = parseInt(year)-1;
        nextYear = parseInt(year)+1;
        $('#backButtonYear').attr('onclick','switchDate(\'y\','+year+','+prevYear+','+month+')');
        $('#foreButtonYear').attr('onclick','switchDate(\'y\','+year+','+nextYear+','+month+')');
      }
      else {
        year = parseInt(year)-1;
      }
    }

    $('#currentYear').text(year);
    $('#activeMonth').text(monthNames[month - 1]);
    $('#backButton').attr('onclick','switchDate(\'m\','+month+','+prevMonth+','+year+')');
    $('#foreButton').attr('onclick','switchDate(\'m\','+month+','+nextMonth+','+year+')');
    $('td').remove('.calendarDays');

    var monthDays = getMonthDays(parseInt(month),year);
    var prevDays = getMonthDays(parseInt(month)-1,year);
    var k = prevDays-1;

    for (i = 1; i <= monthDays; i++) {
      var weekDay = getWeekDay(i,month,year);
      if(weekDay == 'Sa' || weekDay == 'So'){
        var newDay = $( '<td class="calendarDays" id="cDay'+i+'" style="background-color: #c8c8c8;">'+i+'<br />'+weekDay+'</td>' );
        $( "#cDayRow" ).append( newDay );
      }
      else {
        var newDay = $( '<td class="calendarDays" id="cDay'+i+'">'+i+'<br />'+weekDay+'</td>' );
        $( "#cDayRow" ).append( newDay );
      }
    }

    m = 33-monthDays;
    $('th#monthRow').attr('colspan',monthDays);

    var ind = 0;
    while($('tr[name="'+ind+'"]').length > 0){

      while($('tr[name="'+ind+'"] > td').length < monthDays+1){
        $('tr[name="'+ind+'"]').find('td:last').after('<td></td>');
      }

      var k = 0;
      $('tr[name="'+ind+'"] > td').each(function(){
        if(!$(this).hasClass('roomName')){
          if(k < 10){
            var current = '0'+k;
          }
          else {
            var current = k;
          }
          if(current <= monthDays){

            var attributes = $.map(this.attributes, function(item) {
              return item.name;
            });
            var coll = $(this);
            $.each(attributes, function(i, item) {
              coll.removeAttr(item);
            });

            $(this).empty();
            $(this).attr('name',current+'.'+month+'.'+year);

            if(getWeekDay(current,month,year) == 'Sa' || getWeekDay(current,month,year) == 'So'){
              $(this).attr('style','background: #c8c8c8;');
            }
          }
          else{
            $(this).remove();
          }
        }
        k++
      });
      var roomID = $('tr[name="'+ind+'"]').attr('id');
      markOccupancies(month,year,roomID);
      ind++;
    }

    return;
  }

  else if(type == 'y'){
    var targetYear = targetValue;
    var currentYear = currentValue;
    var prevYear = parseInt(targetYear)-1;
    var nextYear = parseInt(targetYear)+1;
    var month = dateVal;
    var prevMonth = parseInt(month)-1;
    var nextMonth = parseInt(month)+1;

    $('#currentYear').text(targetYear);
    $('#backButtonYear').attr('onclick','switchDate(\'y\','+targetYear+','+prevYear+','+month+')');
    $('#foreButtonYear').attr('onclick','switchDate(\'y\','+targetYear+','+nextYear+','+month+')');
    $('#backButton').attr('onclick','switchDate(\'m\','+month+','+prevMonth+','+year+')');
    $('#foreButton').attr('onclick','switchDate(\'m\','+month+','+nextMonth+','+year+')');
    $('td').remove('.calendarDays');

    var monthDays = getMonthDays(parseInt(month),targetYear);
    var prevDays = getMonthDays(parseInt(month)-1,targetYear);
    var k = prevDays-1;

    for (i = 1; i <= monthDays; i++) {
      var weekDay = getWeekDay(i,month,targetYear);
      if(weekDay == 'Sa' || weekDay == 'So'){
        var newDay = $( '<td class="calendarDays" id="cDay'+i+'" style="background-color: #c8c8c8;">'+i+'<br />'+weekDay+'</td>' );
        $( "#cDayRow" ).append( newDay );
      }
      else {
        var newDay = $( '<td class="calendarDays" id="cDay'+i+'">'+i+'<br />'+weekDay+'</td>' );
        $( "#cDayRow" ).append( newDay );
      }
    }

    m = 33-monthDays;
    $('th#monthRow').attr('colspan',monthDays);

    var ind = 0;
    while($('tr[name="'+ind+'"]').length > 0){

      while($('tr[name="'+ind+'"] > td').length < monthDays+1){
        $('tr[name="'+ind+'"]').find('td:last').after('<td></td>');
      }

      var k = 0;
      $('tr[name="'+ind+'"] > td').each(function(){
        if(!$(this).hasClass('roomName')){
          if(k < 10){
            var current = '0'+k;
          }
          else {
            var current = k;
          }
          if(current <= monthDays){

            var attributes = $.map(this.attributes, function(item) {
              return item.name;
            });
            var coll = $(this);
            $.each(attributes, function(i, item) {
              coll.removeAttr(item);
            });

            $(this).empty();
            $(this).attr('name',current+'.'+month+'.'+targetYear);

            if(getWeekDay(current,month,targetYear) == 'Sa' || getWeekDay(current,month,targetYear) == 'So'){
              $(this).attr('style','background: #c8c8c8;');
            }
          }
          else{
            $(this).remove();
          }
        }
        k++
      });
      var roomID = $('tr[name="'+ind+'"]').attr('id');
      markOccupancies(month,targetYear,roomID);
      ind++;
    }

    return;
  }
  else {
    alert('Systemfehler');
  }

};

/* Room-Management */
/*******************/

function addRoom () {
  i = 0;
  if($('tr#noRoomsHere').length > 0){
    $('tr#noRoomsHere').remove();
  }
  while(i <= $('tr.newRoom').length && $('tr#newRoom_'+i).length > 0){
    i++;
  }
  if($('tr.newRoom').length < 5){
    var randomColor = Math.floor(Math.random()*16777215).toString(16);
    var newRoom = $( '<tr class="newRoom" id="newRoom_'+i+'"><td><input type="text" id="newName_'+i+'" title="Anzeige-Name" maxlength="20" class="hiddenInput" value="" placeholder="Neues Zimmer" name="roomName" required /></td><td><input id="roomType" type="text" class="hiddenInput" maxlength="2" value="" placeholder="Art" name="newType_'+i+'" title="Zimmer-Art" /></td><td><input id="roomPrice" type="text" class="hiddenInput" value="" placeholder="Preis" name="newPrice_'+i+'" title="Preis pro Nacht" /></td><td><center><input title="Anzeige-Farbe" id="roomColor" name="newColor_'+i+'" class="jscolor" value="#'+randomColor+'" readonly /></center></td><td><center><div title="Entfernen" class="removeicon" onclick="remRow(\''+i+'\');"></div></center></td></tr>' );
    $( 'table#manageRooms' ).append( newRoom );
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
  }
  else {
    $( 'span.anouncement' ).html( 'Nicht mehr als 5 neue Zimmer gleichzeitig m&ouml;glich' );
  }

  $( 'input#roomPrice' ).on('change paste keyup', function() {
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
    if(!this.value.match(/^\d{1,3}(\.\d{3})*\,\d{2}$/)){
      $('form[name="roomManager"] input[name="submit"]').prop('disabled', true);
      $( 'span.anouncement' ).html( 'Bitte nur g&uuml;ltige Preise mit zwei Nachkommastellen verwenden (z.B. 50,00)' );
    }
    else {
      $('form[name="roomManager"] input[name="submit"]').prop('disabled', false);
    }
  });
}

function remRow (id) {
  if($('tr.newRoom').length = 5){
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
  }

  $('tr').remove('#newRoom_'+id);

  if($('tr.newRoom').length == 0 && $('tr.oldRoom').length == 0){
    var emptyRow = '<tr id="noRoomsHere"><td colspan="5"><center><i>Keine Zimmer vorhanden</i></center></td></tr>';
    $('table#manageRooms').append(emptyRow);
  }
}

function delRoom (id) {
  $('tr#room_'+id).attr('class','delRoom');
  $('div#roomDelete_'+id).attr('class','toremoveicon');
  $('div#roomDelete_'+id).attr('onclick','resetRoom(\''+id+'\');');
  $('span.anouncement').html( '&Auml;nderungen wurden noch nicht gespeichert' );
}

function resetRoom(id) {
  $('tr#room_'+id).attr('class','oldRoom');
  $('div#roomDelete_'+id).attr('class','deleteicon');
  $('div#roomDelete_'+id).attr('onclick','delRoom(\''+id+'\');');
}

function saveRooms () {

  // Add Rooms
  if($('tr.newRoom').length > 0 &&  $('tr.newRoom').length != null) {
    var i = 0;
    roomName = new Array;
    roomColor = new Array;
    roomType = new Array;
    roomPrice = new Array;
    while(i <= $('tr.newRoom').length){
      roomName[i] = $('input#newName_'+i).val();
      roomColor[i] = $('input[name="newColor_'+i+'"]').val();
      roomType[i] = $('input[name="newType_'+i+'"]').val();
      roomPrice[i] = $('input[name="newPrice_'+i+'"]').val();
      i++;
    }

    $.ajax({
        url: 'bin/roomManagement.php',
        method: 'POST',
        data:{'action': 'addRooms', 'roomName': roomName, 'roomColor': roomColor, 'roomType': roomType, 'roomPrice': roomPrice},
        dataType: "text",
        success : function (output) {
          // Delete Rooms
          if($('div.toremoveicon').length > 0 &&  $('div.toremoveicon').length != null) {
            toRemove = new Array;
            var i = 0;
            $('div.toremoveicon').each(function() {
              toRemove[i] = $(this).attr('id');
              i++;
            });
            deleteOldRooms(toRemove);
          }

          else if($('tr.oldRoom').length > 0 && $('tr.oldRoom').length != null){
            var k = 0;
            roomName = new Array;
            roomColor = new Array;
            roomType = new Array;
            roomPrice = new Array;
            roomID = new Array;
            while(i <= $('tr.oldRoom').length){
              roomID[k] = k;
              roomName[k] = $('input[name="oldName_'+k+'"]').val();
              roomColor[k] = $('input[name="oldColor_'+k+'"]').val();
              roomType[k] = $('input[name="oldType_'+k+'"]').val();
              roomPrice[k] = $('input[name="oldPrice_'+k+'"]').val();
              k++;
            }
            $.ajax({
                url: 'bin/roomManagement.php',
                method: 'POST',
                data:{'action': 'editRooms', 'roomID': roomID, 'roomName': roomName, 'roomColor': roomColor, 'roomType': roomType, 'roomPrice': roomPrice},
                dataType: "text",
                success : function (output) {
                  location.reload();
                }
            });
          }

          else {
            location.reload();
          }
        }
    });
    return;
  }

  // Delete Rooms
  else if($('div.toremoveicon').length > 0 &&  $('div.toremoveicon').length != null) {
    var i = 0;
    toRemove = new Array;
    $('div.toremoveicon').each(function() {
      toRemove[i] = $(this).attr('id');
      i++;
    });
    deleteOldRooms(toRemove);
  }

  // Edit Rooms
  else if($('tr.oldRoom').length > 0 && $('tr.oldRoom').length != null) {
    var k = 0;
    roomName = new Array;
    roomColor = new Array;
    roomType = new Array;
    roomPrice = new Array;
    roomID = new Array;

    while(k <= $('tr.oldRoom').length){
      roomID[k] = k;
      roomName[k] = $('input[name="oldName_'+k+'"]').val();
      roomColor[k] = $('input[name="oldColor_'+k+'"]').val();
      roomType[k] = $('input[name="oldType_'+k+'"]').val();
      roomPrice[k] = $('input[name="oldPrice_'+k+'"]').val();
      k++;
    }

    $.ajax({
        url: 'bin/roomManagement.php',
        method: 'POST',
        data:{'action': 'editRooms', 'roomID': roomID, 'roomName': roomName, 'roomColor': roomColor, 'roomType': roomType, 'roomPrice': roomPrice},
        dataType: "text",
        success : function (output) {
          location.reload();
        }
    });
  }
}

function saveNewRooms (roomName,roomColor,roomType,roomPrice) {
  $.ajax({
      url: 'bin/roomManagement.php',
      method: 'POST',
      data:{'action': 'addRooms', 'roomName': roomName, 'roomColor': roomColor, 'roomType': roomType, 'roomPrice': roomPrice},
      dataType: "text",
      success : function (output) {
        location.reload();
      }
  });
}

function deleteOldRooms (roomIDs) {
  $.ajax({
      url: 'bin/roomManagement.php',
      method: 'POST',
      data:{'action': 'deleteRooms', 'roomIDs': roomIDs},
      dataType: "text",
      success : function (output) {
        if($('tr.oldRoom').length > 0 && $('tr.oldRoom').length != null) {
          var k = 0;
          roomName = new Array;
          roomColor = new Array;
          roomType = new Array;
          roomPrice = new Array;
          roomID = new Array;
          while(k <= $('tr.oldRoom').length){
            roomID[k] = k;
            roomName[k] = $('input[name="oldName_'+k+'"]').val();
            roomColor[k] = $('input[name="oldColor_'+k+'"]').val();
            roomType[k] = $('input[name="oldType_'+k+'"]').val();
            roomPrice[k] = $('input[name="oldPrice_'+k+'"]').val();
            k++;
          }

          $.ajax({
              url: 'bin/roomManagement.php',
              method: 'POST',
              data:{'action': 'editRooms', 'roomID': roomID, 'roomName': roomName, 'roomColor': roomColor, 'roomType': roomType, 'roomPrice': roomPrice},
              dataType: "text",
              success : function (output) {
                location.reload();
              }
          });
        }

        else {
          location.reload();
        }
      }
  });
}

/* Occupancy-Management */
/************************/

function editOccupancy(id,oldID='0') {
  if(oldID == '1'){
    $('td[id^=occupied_]').each(function(){
      var thisDay = ($(this).attr('name'));
      var dateElements = thisDay.split('.');
      var thisDay = getWeekDay(dateElements[0],dateElements[1],dateElements[2]);
      if(thisDay == 'Sa' || thisDay == 'So'){
        $(this).attr('style','background: #c8c8c8;');
      }
      else{
        $(this).attr('style','');
      }
    });
  }
  else {
    $('td[id^=occupied_]').each(function(){
      var thisid = $(this).attr('id');
      var thisid = thisid.split('_');
      $(this).attr('onclick','editOccupancy(\''+thisid[1]+'\',\'1\');');
    });
  }

  $('td#occupied_'+id).attr('style','background: #b4dd5e;');

  $('.newOccupancy').attr('style','border-color: #68b12f;');
  $('.newOccupancy > #headline').text('Belegung bearbeiten');
  $('.newOccupancy > form > #action').val('editOccupancy');
  $('.input-daterange > input[name="dateStart"]').val(/*occupancies[dateStart]*/);
  $('.buttonHolder > #occAbort').val('Abbrechen');
  $('.buttonHolder > #occAbort').attr('title','Abbrechen');
  $('.buttonHolder > #occAbort').attr('onclick','cancelEdit();');
  $('.buttonHolder > #occAbort').attr('type','button');

  $.ajax({
      url: 'bin/roomManagement.php',
      method: 'POST',
      data:{'action': 'getOccupancy', 'id': id},
      dataType: "text",
      success : function (output) {
        var duce = jQuery.parseJSON(output);
        var dateStart = duce.dateStart;
        var dateEnd = duce.dateEnd;
        var roomName = duce.roomID;
        var guestID = duce.guestID;
        var comment = duce.comment;
        var occid = duce.id;

        $.ajax({
            url: 'bin/guestManagement.php',
            method: 'POST',
            data:{'action': 'getGuest', 'guestID': guestID},
            dataType: "text",
            success : function (output) {
              var duce = jQuery.parseJSON(output);
              var guest = duce.name+', '+duce.vorname;
              $('.input-daterange > input[name="dateStart"]').val(dateStart);
              $('.input-daterange > input[name="dateEnd"]').val(dateEnd);
              $('.newOccupancy > form > .livesearchGroup > input[name="guest"]').val(guest);
              $('.newOccupancy > form > .livesearchGroup > input[name="knownGuest"]').val(guestID);
              $('.newOccupancy > form > input[name="comment"]').val(comment);
              $('.newOccupancy > form > select[name="roomID"]').val(roomName);
              $('.newOccupancy > form > input[name="occID"]').val(occid);
              $('.buttonHolder > #occDelete').prop('disabled', false);
            }
        });
      }
  });
}

function occDelete (id) {
  $.ajax({
      url: 'bin/roomManagement.php',
      method: 'POST',
      data:{'action': 'deleteOccupancy', 'occID': id},
      dataType: "text",
      success : function (output) {
        location.reload();
      }
  });
  return;
}

function cancelEdit () {
  $('td[id^=occupied_]').each(function(){
    var thisDay = ($(this).attr('name'));
    var dateElements = thisDay.split('.');
    var thisDay = getWeekDay(dateElements[0],dateElements[1],dateElements[2]);
    if(thisDay == 'Sa' || thisDay == 'So'){
      $(this).attr('style','background: #c8c8c8;');
    }
    else{
      $(this).attr('style','');
    }
  });

  $('.newOccupancy').attr('style','border-color: transparent;');
  $('.newOccupancy > #headline').text('Neue Belegung');
  $('.newOccupancy > form > #action').val('addOccupancy');
  $('.newOccupancy > form > input[name="occID"]').val('empty');
  $('.newOccupancy > form')[0].reset();
  $('.buttonHolder > #occAbort').val('Reset');
  $('.buttonHolder > #occAbort').attr('title','Reset');
  $('.buttonHolder > #occAbort').attr('onclick','');
  $('.buttonHolder > #occAbort').attr('type','reset');
  $('.buttonHolder > #occDelete').prop('disabled', true);
  $('.buttonHolder > #occDelete').attr('onclick', '');
}

function markOccupancies(month,year,roomID) {
  $.ajax({
      url: 'bin/roomManagement.php',
      method: 'POST',
      data:{'action': 'getOccupancies', 'month': month, 'year': year, 'roomID': roomID},
      dataType: "text",
      success : function (output) {
        $.each(JSON.parse(output), function(idx, obj) {
          var dateStart = obj.dateStart;
          var dateEnd = obj.dateEnd;
          var roomID = obj.roomID;
          var guest = obj.guestID;
          var comment = obj.comment;
          var color = obj.color;
          var occid = obj.id;

          $.ajax({
              url: 'bin/guestManagement.php',
              method: 'POST',
              data:{'action': 'getGuest', 'guestID': guest},
              dataType: "text",
              success : function (output) {
                var duce = jQuery.parseJSON(output);
                var guest = duce.name+', '+duce.vorname;

                $('tr#roomID_'+roomID+' > td').each(function(){
                  if(!$(this).hasClass('roomName')){
                    var dateCheck = $(this).attr('name');
                    var d1 = dateStart.split(".");
                    var d2 = dateEnd.split(".");
                    var c = dateCheck.split(".");

                    var from = new Date(d1[2], parseInt(d1[1])-1, d1[0]);
                    var to   = new Date(d2[2], parseInt(d2[1])-1, d2[0]);
                    var check = new Date(c[2], parseInt(c[1])-1, c[0]);
                    if(check >= from && check <= to){
                      $(this).attr('onclick','editOccupancy(\''+occid+'\');');
                      $(this).html('<center><div class="occupied" style="background-color: #'+color+';"></div></center>');
                      $(this).attr('id','occupied_'+occid);
                      $(this).attr('class','occupied');
                      $(this).attr('title','Gast: '+guest+' - Kommentar: '+comment);
                      if($('input[name="occID"]').val() == occid){
                        $(this).attr('style','background: #b4dd5e;');
                      }
                    }
                  }
                });
              }
            });
        });
      }
  });
}

/* User-Management */
/*******************/

function addUser() {
  windowOverlay();
  var myContent = '<center><span class="mainSpan">Neuer Benutzer</span></center><br /><br /><form action="bin/handleUsers.php" method="post" id="addUser" onsubmit="return checkSubmit()"><input type="text" class="definput" placeholder="Benutzername" id="userName" name="userName" required /><br /><br /><input type="password" class="definput" placeholder="Passwort" name="userpass1" id="userpass1" required /><br /><br /><input type="password" class="definput" placeholder="Passwort wiederholen" name="userpass2" id="userpass2" required /><br /><br /><input type="checkbox" id="c1" value="true" name="admin" /><label for="c1"><span id="checkbox"></span><span style="font-size: 14px;">Administrator</span></label><br /><br /><input type="hidden" name="action" value="add" /><input type="submit" class="defButton" value="Speichern"/>';
  $('div.windowOverlay').append(myContent);
}

function checkSubmit() {
  if($('input#userpass1').val() != $('input#userpass2').val()) {
    alert(unescape('Die Kennw%F6rter stimmen nicht %FCberein'));
    return false;
  }
  else if ($('input#userpass1').val() == '' || $('input#userpass2').val() == '') {
    alert(unescape('Die Kennw%F6rter d%FCrfen nicht leer sein'));
    return false;
  }
  else {
    addLog('Benutzer "'+$('input#userName').val()+'" wurde hinzugef&uuml;gt.');
    alert(unescape('Benutzer wurde hinzugef%FCgt'));
    return true;
  }
}

function deleteUser(id, user) {
  displayOverlay();
  $.ajax({
      url: 'bin/handleUsers.php',
      method: 'POST',
      data:{'action': 'delete','id': id},
      dataType: 'text',
      success : function (output) {
        removeOverlay();
        if(output == '0'){
          alert(unescape("L%F6schen nicht m%F6glich%3A Es w%FCrde keinen Benutzer mit Status %22Admin%22 mehr geben."));
          location.reload();
          /*addLog('Letzter Benutzer mit Admin-Rechten "'+user+'" wurde versucht zu entfernen.');*/
        }
        else if(output == '1') {
          alert(unescape('Benutzer "'+user+'" erfolgreich gel%F6scht.'));
          location.reload(true);
          /*addLog('Benutzer "'+user+'" wurde entfernt.');*/
        }
        else {
          alert('Oops, da ging etwas schief! '+output);
          location.reload();
        }
      }
  });
}

function editUser(id, user) {
  windowOverlay();
  var myContent = '<center><span class="mainSpan">Benutzer bearbeiten</span></center><br /><br /><form action="bin/handleUsers.php" method="post" id="addUser" onsubmit="return checkSubmit()"><input type="text" class="definput" placeholder="Benutzername" id="userName" name="userName" value="'+user+'" required /><br /><br /><div id="checkboxholder"><input type="checkbox" id="c2" value="true" name="pwd" /><label for="c2"><span id="checkbox"></span><span style="font-size: 14px;">Kennwort zur&uuml;cksetzen</span></label><br /><br /><input type="checkbox" id="c1" value="true" name="admin" /><label for="c1"><span id="checkbox"></span><span style="font-size: 14px;">Administrator</span></label></div><br /><br /><input type="hidden" name="action" value="edit" /><input type="hidden" name="userID" value="'+id+'" /><input type="submit" class="defButton" value="Speichern"/>';
  $('div.windowOverlay').append(myContent);
}

function getUsers(){
  $('#logRow').remove();
  $.getJSON('bin/users.json', function(data) {

      $.each(data, function(i, item){
        if(item.state == 'admin'){
          state = 'admin';
        }
        else {
          state = 'user';
        }

        if(item.wasHere == '0'){
          wasHere = 'Nie';
        }
        else {
          wasHere = 'Ja';
        }

        $('<tr id="userRow"><td>'+item.user+'</td><td>'+state+'</td><td>'+wasHere+'</td><td><center><a href="#" class="editicon" onclick="editUser(\''+item.id+'\',\''+item.user+'\')" alt="Editieren" title="Editieren"></a></center></td><td><center><a href="#" class="deleteicon" onclick="deleteUser(\''+item.id+'\',\''+item.user+'\')" alt="L&ouml;schen" title="L&ouml;schen"></a></center></td></tr>').appendTo('table#userList');
      });
    });
}

/* Guest-Management */
/********************/

function showResult(str,expand=false) {
  $('input[name="knownGuest"]').val('');
  if (str.length<=1) {
    document.getElementById("livesearch").innerHTML="";
    document.getElementById("livesearch").style.border="0px";
    $('#occSave').attr('style','');
    $('#occAbort').attr('style','');
    $('#occDelete').attr('style','');
    return;
  }

  getGuests(str);
}

function getGuests(str) {
  var txt = '';
  $.getJSON( "bin/guests.json", function( data ) {
    var items = {};
    $.each( data, function( key, val ) {
      $.each( val, function(i, item) {
        if(item != 'undefined'){
          items[i] = item;
        }
      });
      if(items['name'].toLowerCase().match("^"+str.toLowerCase()) || items['vorname'].toLowerCase().match("^"+str.toLowerCase()) || items['adresse'].toLowerCase().match("^"+str.toLowerCase())){
        txt += '<div id="element" title="&Uuml;bernehmen" onclick="applyGuest(\''+items['id']+'\',\''+items['name']+'\',\''+items['vorname']+'\')"><div id="elementContent"><span id="title">'+items['name']+', '+items['vorname']+'</span><br /><span id="subtitle">'+items['adresse']+'</span></div><div class="applyicon"></div></div><div class="clear"></div>';
      }
    });
    if(txt.length < 1){
      txt += '<div id="element" style="color: #696969; padding-left: 15px; cursor: default;"><i>Gast noch nicht vorhanden</i><div class="addGuesticon" title="Neuer Gast" onclick="addGuestOverlay();"></div></div>';
    }
    document.getElementById("livesearch").innerHTML = txt;
    $('#occSave').attr('style','pointer-events: none;');
    $('#occAbort').attr('style','pointer-events: none;');
    $('#occDelete').attr('style','pointer-events: none;');
  });
  return;
}

function applyGuest(id, name, vorname) {
  $('input[name="guest"]').val(name+', '+vorname);
  $('input[name="knownGuest"]').val(id);
  document.getElementById('livesearch').innerHTML="";
  document.getElementById('livesearch').style.border="0px";
  $('#occSave').attr('style','');
  $('#occAbort').attr('style','');
  $('#occDelete').attr('style','');
  $('#livesearch').data('clicked', false);
  return;
}

function addGuest () {
  i = 0;
  if($('tr#noGuestsHere').length > 0){
    $('tr#noGuestsHere').remove();
  }
  while(i <= $('tr.newGuest').length && $('tr#newGuest_'+i).length > 0){
    i++;
  }
  if($('tr.newGuest').length < 5){
    var newGuest = $( '<tr class="newGuest" id="newGuest_'+i+'"><td id="guestName"><input type="text" id="newGuestName_'+i+'" title="Nachname" maxlength="20" class="hiddenInput" value="" placeholder="Nachname" name="guestName" required /></td><td id="guestVorname"><input id="guestVorname" type="text" class="hiddenInput" maxlength="20" value="" placeholder="Vorname" name="newGuestVorname_'+i+'" title="Vorname" /></td><td id="guestAddress"><input id="guestAddress" type="text" class="hiddenInput" maxlength="150" value="" placeholder="Adresse" name="newGuestAddress_'+i+'" title="Adresse" /></td><td></td><td><center><div title="Entfernen" class="removeicon" onclick="remGuestRow(\''+i+'\');"></div></center></td></tr>' );
    $( 'table#manageRooms' ).append( newGuest );
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
  }
  else {
    $( 'span.anouncement' ).html( 'Nicht mehr als 5 neue G&auml;ste gleichzeitig m&ouml;glich' );
  }
}

function addGuestOverlay () {
  windowOverlay();
  var myContent = '<center><span class="mainSpan">Neuer Gast</span></center><br /><br /><form action="index.php?site=guests" method="post"><input type="hidden" name="action" value="addGuests"><input type="text" class="definput" placeholder="Name" id="guestAddress" name="guestName" required /><br /><br /><input type="text" class="definput" placeholder="Vorname" id="guestAddress" name="guestVorname" required /><br /><br /><input type="text" class="definput" placeholder="Adresse" id="guestAddress" name="guestAddress" required /><br /><br /><input type="buttton" onclick="saveSingleGuest();" class="defButton" value="Speichern"/>';
  $('div.windowOverlay').append(myContent);
}

function remGuestRow (id) {
  if($('tr.newGuest').length = 5){
    $( 'span.anouncement' ).html( '&Auml;nderungen wurden noch nicht gespeichert' );
  }

  $('tr').remove('#newGuest_'+id);

  if($('tr.newGuest').length == 0 && $('tr.oldGuest').length == 0){
    var emptyRow = '<tr id="noGuestsHere"><td colspan="5"><center><i>Keine G&auml;ste vorhanden</i></center></td></tr>';
    $('table#manageRooms').append(emptyRow);
  }
}

function delGuest (id) {
  $('tr#guest_'+id).attr('class','delGuest');
  $('div#guestDelete_'+id).attr('class','toremoveicon');
  $('div#guestDelete_'+id).attr('onclick','resetGuest(\''+id+'\');');
  $('span.anouncement').html( '&Auml;nderungen wurden noch nicht gespeichert' );
}

function resetGuest(id) {
  $('tr#guest_'+id).attr('class','oldGuest');
  $('div#guestDelete_'+id).attr('class','deleteicon');
  $('div#guestDelete_'+id).attr('onclick','delGuest(\''+id+'\');');
}

function saveGuests () {

  // Add Guests
  if($('tr.newGuest').length > 0 &&  $('tr.newGuest').length != null) {
    var i = 0;
    guestName = new Array;
    guestVorname = new Array;
    guestAddress = new Array;

    while(i <= $('tr.newGuest').length){
      guestName[i] = $('input#newGuestName_'+i).val();
      guestVorname[i] = $('input[name="newGuestVorname_'+i+'"]').val();
      guestAddress[i] = $('input[name="newGuestAddress_'+i+'"]').val();
      i++;
    }

    $.ajax({
        url: 'bin/guestManagement.php',
        method: 'POST',
        data:{'action': 'addGuests', 'guestName': guestName, 'guestVorname': guestVorname, 'guestAddress': guestAddress},
        dataType: "text",
        success : function (output) {
          // Delete Guests
          if($('div.toremoveicon').length > 0 &&  $('div.toremoveicon').length != null) {
            toRemove = new Array;
            var i = 0;
            $('div.toremoveicon').each(function() {
              toRemove[i] = $(this).attr('id');
              i++;
            });
            deleteOldGuests(toRemove);
          }

          else if($('tr.oldGuests').length > 0 && $('tr.oldGuests').length != null){
            var k = 0;
            guestName = new Array;
            guestVorname = new Array;
            guestAddress = new Array;

            while(i <= $('tr.oldGuests').length){
              guestID[k] = k;
              guestName[k] = $('input[name="oldName_'+k+'"]').val();
              guestVorname[k] = $('input[name="oldVorname_'+k+'"]').val();
              guestAddress[k] = $('input[name="oldAddress_'+k+'"]').val();
              k++;
            }
            $.ajax({
                url: 'bin/guestManagement.php',
                method: 'POST',
                data:{'action': 'editGuests', 'guestID': guestID, 'guestName': guestName, 'guestVorname': guestVorname, 'guestAddress': guestAddress},
                dataType: "text",
                success : function (output) {
                  location.reload();
                }
            });
          }

          else {
            location.reload();
          }
        }
    });
    return;
  }

  // Delete Guests
  else if($('div.toremoveicon').length > 0 &&  $('div.toremoveicon').length != null) {
    var i = 0;
    toRemove = new Array;
    $('div.toremoveicon').each(function() {
      toRemove[i] = $(this).attr('id');
      i++;
    });
    deleteOldGuests(toRemove);
  }

  // Edit Guests
  else if($('tr.oldGuest').length > 0 && $('tr.oldGuest').length != null) {
    var k = 0;
    guestName = new Array;
    guestVorname = new Array;
    guestAddress = new Array;
    guestID = new Array;

    while(k <= $('tr.oldGuest').length){
      guestID[k] = k;
      guestName[k] = $('input[name="oldName_'+k+'"]').val();
      guestVorname[k] = $('input[name="oldVorname_'+k+'"]').val();
      guestAddress[k] = $('input[name="oldAddress_'+k+'"]').val();
      k++;
    }

    $.ajax({
        url: 'bin/guestManagement.php',
        method: 'POST',
        data:{'action': 'editGuests', 'guestID': guestID, 'guestName': guestName, 'guestVorname': guestVorname, 'guestAddress': guestAddress},
        dataType: "text",
        success : function (output) {
          location.reload();
        }
    });
  }
}

function saveNewGuests (guestName,guestVorname,guestAddress) {
  $.ajax({
      url: 'bin/guestManagement.php',
      method: 'POST',
      data:{'action': 'addGuests', 'guestName': guestName, 'guestVorname': guestVorname, 'guestAddress': guestAddress},
      dataType: "text",
      success : function (output) {
        location.reload();
      }
  });
}

function saveSingleGuest() {
  var guestName = $('.windowOverlay > form > input[name="guestName"]').val();
  var guestVorname = $('.windowOverlay > form > input[name="guestVorname"]').val();
  var guestAddress = $('.windowOverlay > form > input[name="guestAddress"]').val();

  $.ajax({
      url: 'bin/guestManagement.php',
      method: 'POST',
      data:{'action': 'addSingleGuest', 'guestName': guestName, 'guestVorname': guestVorname, 'guestAddress': guestAddress},
      dataType: "text",
      success : function (output) {
        removeOverlay();
        $('input[name="guest"]').val(guestName+', '+guestVorname);
        $('input[name="knownGuest"]').val(output);
        document.getElementById('livesearch').innerHTML="";
        document.getElementById('livesearch').style.border="0px";
        $('#occSave').attr('style','');
        $('#occAbort').attr('style','');
        $('#occDelete').attr('style','');
        $('#livesearch').data('clicked', false);
        return;
      }
  });
}

function deleteOldGuests (guestIDs) {
  $.ajax({
      url: 'bin/guestManagement.php',
      method: 'POST',
      data:{'action': 'deleteGuests', 'guestIDs': guestIDs},
      dataType: "text",
      success : function (output) {
        if($('tr.oldGuest').length > 0 && $('tr.oldGuest').length != null) {
          var k = 0;
          guestName = new Array;
          guestVorname = new Array;
          guestAddress = new Array;
          guestID = new Array;
          while(k <= $('tr.oldGuest').length){
            guestID[k] = k;
            guestName[k] = $('input[name="oldName_'+k+'"]').val();
            guestVorname[k] = $('input[name="oldVorname_'+k+'"]').val();
            guestAddress[k] = $('input[name="oldAddress_'+k+'"]').val();
            k++;
          }

          $.ajax({
              url: 'bin/guestManagement.php',
              method: 'POST',
              data:{'action': 'editGuests', 'guestID': guestID, 'guestName': guestName, 'guestVorname': guestVorname, 'guestAddress': guestAddress},
              dataType: "text",
              success : function (output) {
                location.reload();
              }
          });
        }

        else {
          location.reload();
        }
      }
  });
}

function checkRoomValues() {

}


/* Diverses */
/************/
