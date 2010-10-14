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

include_once ("includes/config.php");
include_once ("includes/utils.php");

if($_SERVER["REQUEST_METHOD"] != "POST") {
	die('Invalid request.');
}

// some computers return funny names such as pml31201.truman.edu
// we must truncate to pml31201
function cleanCompName($comName)
{
  $pieces = explode(".", $comName);
  return strtoupper($pieces[0]);
}

function getCompRegion($compName, $regNames)
{
  foreach ($regNames as $regId => $regName)
  {
    if (substr($compName, 0, strlen($regName)) ==  $regName) return $regId;
  }
  return -1;
}

$compName = cleanCompName(sqlite_escape_string($_POST['computerName'])); 
//$compProcesses = explode("|", $_POST['processes']);
if ($_POST['processes']) {
	$compProcesses =  sqlite_escape_string($_POST['processes']);
}
$timeNow = time();



if ($compName != "")
{
	$dbTrackHandler = connectDb();
    
    // Translate from computer name to Id
    $compNames = getCompNamesId($dbTrackHandler);
    $compId = $compNames[$compName];

    if (intval($compId)==0)
    { 
      // If the computer has not been seen before
      // Then we have to figure out what to do with it.
      
      // Let's see if the prefix of the computer matches any known region name
      // if that's the case, then we create a new entry in the computer table to track it.
      if ($add_unknown_computers)
      {
	$dbTrackHandler = connectDb();
	
	$regNames = getRegionNamesId($dbTrackHandler, true);
	
	$thisCompRegId = getCompRegion($compName, $regNames);
	
	$dbTrackHandler->query(
	'INSERT INTO computers '.
	'(name,x,y,region) '.
	'VALUES ("'. $compName .'", 0, 0, ' . $thisCompRegId . ');');
	
	$compNames = getCompNamesId($dbTrackHandler, true);
      }
    }


    if (intval($compId)!=0)
    {
      $dbTrackHandler->query(
          'UPDATE computers '.
          'SET laststatus='.AVAIBILITY_TYPE_BUSY.', lastsignal='.$timeNow.' '.
          'WHERE id=' . $compId . '; '.
          'INSERT INTO trackrecords (`compid`, `time`, `status`) '.
          'VALUES ("'.$compId.'", '.$timeNow.', '.AVAIBILITY_TYPE_BUSY.'); '.
          (($compProcesses != "")?'INSERT INTO miscrecords (`compid`, `time`, `recordtype`, `data`) '.
          'VALUES ("'.$compId.'", '.$timeNow.', '.RECORDTYPE_PROGRAMS.', "'.$compProcesses.'"); ':'')
          );
    }

}
?>
