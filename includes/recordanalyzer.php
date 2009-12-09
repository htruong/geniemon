<?php 
/* 
* Copyright (C) 2009 Huan Truong
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions
* are met:
* 1. Redistributions of source code must retain the above copyright
*    notice, this list of conditions and the following disclaimer.
* 2. Redistributions in binary form must reproduce the above copyright
*    notice, this list of conditions and the following disclaimer in the
*    documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY AUTHOR AND CONTRIBUTORS ``AS IS'' AND
* ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
* ARE DISCLAIMED.  IN NO EVENT SHALL AUTHOR OR CONTRIBUTORS BE LIABLE
* FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
* DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
* OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
* HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
* OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
* SUCH DAMAGE.
*/
// check if the record is a dupe (too near to the last record)

function isDupeRecord($name, $timeStm, &$computerRecords) {
  global $check_interval; 
 
  //echo "checking for dupe $name at $timeStm...";
  
  // see if the computer already has a record recorded before
  if (array_key_exists($name, $computerRecords)) {
    // if so, need to check the last record timestamp
    // to see if it's very near
    $timeDiffLimit = $check_interval * 1.6;
    if (abs($computerRecords[$name] - $timeStm) < $timeDiffLimit) {
      //echo " - dupe $timeStm <br />";
      return true;
    } else {
      $computerRecords[$name] = $timeStm;
      //echo "$timeDiff > $timeDiffLimit - NOPE dupe  <br />";
      $computerRecords[$name] = $timeStm;
      return false;
    }
  } else {
    $computerRecords[$name] = $timeStm;
    return false;
  }
}

function generateStatsBag($args, &$dbHandler) {
 
//$batchRecordProcess = 10000; // analyse 10000 at a time
 $recordTableName = '';
 $sql_cmd = '';

 switch ($args['reportType']) {
   ////////////////////////////////////////////////////////////////////////////////////////////////////
   case 'computerStats':
     $recordTableName = 'trackrecords';

     $sql_cmd =
        'SELECT trackrecords.time, trackrecords.status, trackrecords.compid, computers.name, computers.id, computers.region FROM trackrecords ' .
        'LEFT OUTER JOIN computers ON trackrecords.compid = computers.id ';
     $firstWHERE = true;

     // Now select zone
     if ($args['computerRange'] == 'zone') {
      $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . ' computers.region = ' . intval($args['computersRangeParam'])  . ' ';
      $firstWHERE = false;
     }
     
     // Now select time frame
     if (intval($args['timeFrame']) != 0) {
      $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . $recordTableName . '.time > ' . (time() - intval($args['timeFrame'])) . " ";
      $firstWHERE = false;
     }

     if (intval($args['endTimeFrame']) != 0) {
      $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . $recordTableName . '.time < ' . (time() - intval($args['endTimeFrame'])) . " ";
      $firstWHERE = false;
     }

    // Now select week days
    if (intval($args['filterWeekends']) != 0) {
      $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . ' DAYOFWEEK(FROM_UNIXTIME(' . $recordTableName . '.time)) > 1 AND ' . 
      'DAYOFWEEK(FROM_UNIXTIME(' . $recordTableName . '.time)) < 7 ';
      $firstWHERE = false;
    }

    // Now select daytime only
    if (intval($args['filterNights']) != 0) {
      $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . ' HOUR(FROM_UNIXTIME(' . $recordTableName . '.time)) > 7 ' .
      'OR ((HOUR(FROM_UNIXTIME(' . $recordTableName . '.time)) > 0) AND (HOUR(FROM_UNIXTIME(' . $recordTableName . '.time)) < 1)) ';
      $firstWHERE = false;
    }
    
     // Now select the computer conditions
     switch ($args['computerStatus']) {
	  case 'occupied':
        $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . $recordTableName . '.status = \'' . AVAIBILITY_TYPE_BUSY . '\' ';
        $firstWHERE = false;
	    break;
	  case 'available':
        $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . $recordTableName . '.status = \'' . AVAIBILITY_TYPE_AVAILABLE . '\' ';
        $firstWHERE = false;
	    break;
	  case 'offline':
        $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . $recordTableName . '.status = \'' . AVAIBILITY_TYPE_OFFLINE . '\' ';
        $firstWHERE = false;
	    break;
	  default:
     }


   break;
   ////////////////////////////////////////////////////////////////////////////////////////////////////
   case 'programUsage':
     $recordTableName = 'miscrecords';

     $sql_cmd =
     'SELECT miscrecords.time, miscrecords.data, miscrecords.compid, computers.name, computers.id FROM miscrecords ' .
        'LEFT OUTER JOIN computers ON miscrecords.compid = computers.id ' .
		'WHERE ' . $recordTableName . '.recordtype = ' . RECORDTYPE_PROGRAMS . ' ';

        
        // Now select zone
        if ($args['computerRange'] == 'zone') {
          $sql_cmd .= 'AND computers.region = ' . intval($args['computersRangeParam'])  . ' ';
          $firstWHERE = false;
        }
        
      // Now select time frame
      if (intval($args['timeFrame']) != 0) {
        $sql_cmd .= 'AND ' . $recordTableName . '.time > ' . (time() - intval($args['timeFrame'])) . " ";
      }

      if (intval($args['endTimeFrame']) != 0) {
        $sql_cmd .= 'AND ' . $recordTableName . '.time < ' . (time() - intval($args['endTimeFrame'])) . " ";
      }


   break;
   ////////////////////////////////////////////////////////////////////////////////////////////////////
   default:
     die('invalid arguments');
   break;
   ////////////////////////////////////////////////////////////////////////////////////////////////////
 }
 
 
 // Now select the computer range
 switch ($args['computerRange']) {
 }
 
 $sql_cmd .= '';
 
 // ----------------------------------------------------
 // Connect to the database and query it

 $resultBag = array();
 
  
 $results = $dbHandler->query($sql_cmd . ';');
  
 $sortMode = 0; // group by computer name
 switch ($args['groupType']) {
   case 'hourByHour':
     $sortMode = 1;
     break;
   case 'dayofWeek':
     $sortMode = 2;
     break;
   case 'individualDay':
     $sortMode = 3;
     break;
   case 'oneBigChart':
     $sortMode = 4;
     break;
   case 'hourDayCombined':
     $sortMode = 5;
     break;
   default:
     $sortMode = 0;
     break;
 }
 
 $computerRecords = array();

 foreach ($results as $result) {
   $currentComputer = $result['name'];

   $currentRecordedStatus = null;

   $currentRecordedTime = $result['time'];
   switch ($args['reportType']) {
     case 'computerStats':
       $currentRecordedStatus = $result['status'];
       break;
     default:
       $currentRecordedStatus = explode("|",$result['data']);
       break;
   }

  if (!isDupeRecord($currentComputer,$currentRecordedTime,$computerRecords)) {

      switch ($sortMode) {
	case 1: // Hour by Hour
	    $resultGroup = date("H", $currentRecordedTime) . ":00";
	break;

	case 2: // Day of Week
	  $resultGroup = date("N:l", $currentRecordedTime);
	break;
	
	case 3: // Individual Days
	  $resultGroup = date("y/m/d", $currentRecordedTime);
	break;
	    
	case 4: // One Big Chart
	  $resultGroup = "Big Chart";
	break;

    case 5: // Hour by Hour Day-by-Day of Week
      $resultGroup = date("N:l H:00", $currentRecordedTime);
      break;

	default: // Computer Name
	  $resultGroup = &$currentComputer;
	break;

      } // switch $sortmode

      addToResultBag($resultBag,$resultGroup,$currentRecordedStatus);
    } // if dupe records
  
  } // while results are valid
 
  // Sort results
  ksort($resultBag);
  return $resultBag;
}


function addToResultBag(&$resultBag, &$groupType, &$resultElement) {

  if (!array_key_exists($groupType,$resultBag)) {
    $resultBag[$groupType] = array();
  }

  if (!is_array($resultElement)) {
    $resultBag[$groupType][$resultElement] += 1;
  } else {
    foreach ($resultElement as &$resultSubElement) {
      $resultBag[$groupType][$resultSubElement] += 1;
    }
  }

}
?>
