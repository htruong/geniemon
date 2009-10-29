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
// check if the record is a dupe (too near to last record)



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
    //echo "First time, so NOPE. <br />";
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

     $sql_cmd = 'FROM trackrecords, computers, zones ' .
		'WHERE ' . $recordTableName . '.name = computers.name AND computers.region = zones.id ';

     // Now select time frame
     if (intval($args['timeFrame']) != 0) {
       $sql_cmd .= 'AND ' . $recordTableName . '.time > ' . (time() - intval($args['timeFrame'])) . " ";
     }

     // Now select the computer conditions
     switch ($args['computerStatus']) {
	  case 'occupied':
	    $sql_cmd .= 'AND ' . $recordTableName . '.status = \'' . AVAIBILITY_TYPE_BUSY . '\' ';
	    break;
	  case 'available':
	    $sql_cmd .= 'AND ' . $recordTableName . '.status = \'' . AVAIBILITY_TYPE_AVAILABLE . '\' ';
	    break;
	  case 'offline':
	    $sql_cmd .= 'AND ' . $recordTableName . '.status = \'' . AVAIBILITY_TYPE_OFFLINE . '\' ';
	    break;
	  default:
     }

   break;
   ////////////////////////////////////////////////////////////////////////////////////////////////////
   case 'programUsage':
     $recordTableName = 'miscrecords';

     $sql_cmd = 'FROM ' . $recordTableName . ', computers, zones ' .
		'WHERE ' . $recordTableName . '.name = computers.name ' .
		'AND ' . $recordTableName . '.recordtype = ' . RECORDTYPE_PROGRAMS . ' ' .
		'AND computers.region = zones.id ';

      // Now select time frame
      if (intval($args['timeFrame']) != 0) {
	$sql_cmd .= 'AND ' . $recordTableName . '.timestamp > ' . (time() - intval($args['timeFrame'])) . " ";
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
   case 'named':
     $nameArray = explode(',', $args['computersRangeParam']); 
     $sql_cmd .= "AND (";
     for ($i=0; $i < count($nameArray); $i++) {
       $sql_cmd .= ($i==0 ? '' : ' OR ') . $recordTableName . ".name == '" . $nameArray[$i] . "' ";
     }
     $sql_cmd .= ") ";
     break;
   case 'zones':
     $sql_cmd .= "AND zones.region_name = '" . $args['computersRangeParam'] . "' ";
     break;
   default:
 }
 

   
 $sql_cmd .= '';
 
 
 // ----------------------------------------------------
 // Connect to the database and query it



 $resultBag = array();
 
  
  $results = &$dbHandler->unbufferedQuery('SELECT ' . $recordTableName . '.* ' . $sql_cmd . ';');
  
  
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
   default:
     $sortMode = 0;
     break;
 }
 
 $computerRecords = array();
 
 while($results->valid()) {
   $result = $results->current();
   $currentComputer = $result[$recordTableName . '.name']; 

   $currentRecordedStatus = null;

   // this is going to be ugly. whatever.
   switch ($args['reportType']) {
    case 'computerStats':
      $currentRecordedTime = $result[$recordTableName . '.time'];
      $currentRecordedStatus = $result[$recordTableName . '.status'];
      break;
    case 'programUsage':
      $currentRecordedTime = $result[$recordTableName . '.timestamp'];
      $currentRecordedStatus = explode("|",$result[$recordTableName . '.data']);
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

	default: // Computer Name
	  $resultGroup = &$currentComputer;
	break;

      } // switch $sortmode

      addToResultBag($resultBag,$resultGroup,$currentRecordedStatus);
    } // if dupe records
  

    $results->next();
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