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
        'SELECT trackrecords.time, trackrecords.status, trackrecords.compid FROM
trackrecords ';
        /* 'SELECT trackrecords.time, trackrecords.status, trackrecords.compid,
computers.name, computers.id, computers.region FROM trackrecords ' .
        'LEFT OUTER JOIN computers ON trackrecords.compid = computers.id '; */
     $firstWHERE = true;

     // Now select zone
      if ($args['computerRange'] == 'zone') {

      $allZones = getAllComputersZones($dbHandler);
      $affectedComps = $allZones[intval($args['computersRangeParam'])];
      $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . 'compid IN (' .
implode(', ', $affectedComps) . ') ';
     
     // Now select computer
     if ($args['computerRange'] == 'computer') {
       $compId = getCompNamesId($dbTrackHandler);
       $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . ' compid = "'
. $compId[$args['computersRangeParam']] . '" ';
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
     'SELECT miscrecords.time, miscrecords.data, miscrecords.compid FROM
miscrecords ' .
      'WHERE ' . $recordTableName . '.recordtype = ' .
RECORDTYPE_PROGRAMS . ' ';

        
     // Now select zone
     if ($args['computerRange'] == 'zone') {

      $allZones = getAllComputersZones($dbHandler);
      $affectedComps = $allZones[intval($args['computersRangeParam'])];
      $sql_cmd .= (($firstWHERE)?'WHERE ':'AND ') . 'compid IN (' .
implode(', ', $affectedComps) . ') ';
     
        
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

  echo $sql_cmd;
 
 
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


function generateStats($params)
{
  global $reportCacheLocation;
  // Computer Statistics Generation
  $dbTrackHandler = connectDb();
  $resultBag = generateStatsBag($params, $dbTrackHandler);
  
  // ----------------------------------------------------
  // Generate charts
  
  $animation_1= isset($params['animation_1'])?$params['animation_1']:'pop';
  $delay_1    = isset($params['delay_1'])?$params['delay_1']:0.5;
  $cascade_1    = isset($params['cascade_1'])?$params['cascade_1']:1;
  
  $title = new title( "Genie Report: " . $params['reportName'] . " - Generated on " . date('Y/m/d h:i:s A'));
  $title->set_style( "{font-size: 12px; color: #000000; text-align: center;}" );
  
  $bar_stack = new bar_stack();
  
  if ($params['reportType'] == 'computerStats') {
    foreach ($resultBag as $elm) {

      $tmpTotal = $elm[AVAIBILITY_TYPE_OFFLINE] + $elm[AVAIBILITY_TYPE_AVAILABLE] + $elm[AVAIBILITY_TYPE_BUSY];
      if ($tmpTotal == 0) $tmpTotal = 1; // Only Chuck Norris can divide by Zero.

      $bar_stack->append_stack(array(
        $elm[AVAIBILITY_TYPE_OFFLINE] / $tmpTotal * 100,
        $elm[AVAIBILITY_TYPE_AVAILABLE] / $tmpTotal * 100,
        $elm[AVAIBILITY_TYPE_BUSY] / $tmpTotal * 100
      ));
  }
  
  $bar_stack->set_colours( array( '#FF0000', '#00FF00', '#A25B00'  ) );
  $bar_stack->set_keys(array(
  new bar_stack_key( '#FF0000', 'OFFLINE', 13 ),
    new bar_stack_key( '#00FF00', 'FREE', 13 ),
    new bar_stack_key( '#A25B00', 'BUSY', 13 )
  ));

}
else
{
  
  $allProgNames = array();
  
  foreach ($resultBag as $elm) {
    $progNames = array_keys($elm);
    foreach ($progNames as $progName) {
      if (validProgram($progName)) {
        if (!array_key_exists($progName, $allProgNames)) {
          $allProgNames[$progName] = "#" . dechex(rand(0,10000000));
      }}}
}

$progsArray = array();
foreach ($resultBag as $elm) {
  $tmpTotal = 0;
  $progNames = array_keys($elm);
  
  foreach ($elm as $programName => $programWeight) {
    if (validProgram($programName)) {
      $tmpTotal += $programWeight;
    }
  }
  
  //echo "<h1>$tmpTotal</h1>";
  
  if ($tmpTotal == 0) $tmpTotal = 1; // Only Chuck Norris can divide by Zero.
    
    $progs = array();
  
  foreach  ($elm as $programName => $programWeight) {
    if (validProgram($programName)) {
      $percentVal = $programWeight / $tmpTotal * 100;
      $progs[] = new bar_stack_value($percentVal, $allProgNames[$programName]);
      $progsArray[($percentVal*1000000)] = $programName;
    }
  }
  
  $bar_stack->append_stack($progs);
}


$legends = array();

//$strAllProgNames = array_keys($allProgNames);
  
  foreach  ($allProgNames as $programName => $programColor) {
    $legends[] = new bar_stack_key( $programColor, $programName, 13 );
  }
  
  $bar_stack->set_keys($legends);
  
  ksort($progsArray);
  echo "<pre>";
  while (list($key, $value) = each($progsArray)) {
    $kw = $key/1000000;
    echo "$kw: $value<br />\n";
  }
  echo "</pre>";
  
  }
  //$bar_stack->set_tooltip( 'In #x_label# you get #total# days holiday a year.<br>Number of days: #val#' );
  
  $bar_stack->set_on_show(new bar_on_show($animation_1, $cascade_1, $delay_1));
  
  $y = new y_axis();
  
  $y->set_range( 0, 100, 10 );
  //$y->set_range( 0, $tmpMax, $tmpMax/10 );
  
  $x_labels = new x_axis_labels();
  $x_labels->rotate(45);
  $x_labels->set_labels( array_keys($resultBag) );
  $x = new x_axis();
  $x->set_labels($x_labels);
  
  $tooltip = new tooltip();
  $tooltip->set_hover();
  
  $chart = new open_flash_chart();
  $chart->set_title( $title );
  $chart->add_element( $bar_stack );
  $chart->set_x_axis( $x );
  $chart->add_y_axis( $y );
  $chart->set_tooltip( $tooltip );
  
  
  // ----------------------------------------------------
  $cacheid = time();
  $filename = $cacheid . '.cache.json';
  $myFile = "$reportCacheLocation/reports-cache/" . $filename;
  $fh = fopen($myFile, 'w') or die("can't open file");
  fwrite($fh, $chart->toPrettyString());
  fclose($fh);
  return $cacheid;
}

?>
