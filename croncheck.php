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

require_once "Net/Ping.php";

include_once ("includes/config.php");
include_once ("includes/utils.php");

header('Content-type: text/plain');

// -------------------------------------------
$ping = Net_Ping::factory();

// Check for error initializing the factory
// Will die if fail.
if(PEAR::isError($ping)) {
	echo $ping->getMessage();
	die();
} 

// -------------------------------------------

// Calculate lower bound of last ping
$timeNow = time();
$time_limit = $timeNow - $check_interval;

// Now connect to the db
$dbTrackHandler = new SQLiteDatabase(DB_TRACK_FILE);

// and see if we need to check any computer?
$computersQuery = $dbTrackHandler->query(
	'SELECT id, name, lastsignal '.
	'FROM computers '.
	'WHERE lastsignal < ' . $time_limit . ' OR lastsignal IS NULL '.
	'ORDER BY lastsignal '.
	'LIMIT ' . $check_batch . ';'
	);
/*	
echo (
	'SELECT id, name, lastsignal '.
	'FROM computers '.
	'WHERE lastsignal < ' . $time_limit . ' OR lastsignal IS NULL '.
	'ORDER BY lastsignal '.
	'LIMIT ' . $check_batch . ";\n"
	);
*/

// For now I think it's better not to use transactions
//     as the script can timeout.

while($computersQuery->valid()) {

	$entry = $computersQuery->current();
	$computerName = $entry['name'] . $check_suffix;
	
	// echo 'Querying ' . $computerName . ' ';
	
	$ping->setArgs(array('count' => $check_packets, 'timeout' => $check_timeout));
	
	$pingResult = $ping->ping($computerName);
	//var_dump($pingResult);
	if (PEAR::isError($pingResult)) {
		$failed = true;
	} else {
		$failed = ($pingResult->getLoss() > 0);
	}
	
	//echo '[' . $pingResult->getTargetIp() . ']';
	
	//if ($failed) {
	//	echo "\t FAILED.\n" ;
	//} else {
	//	echo "\t SUCCESS.\n" ; 
	//}
	
	$batch_queries .=
		'UPDATE computers ' .
		'SET lastsignal=' . $timeNow . ', '.
			'laststatus = ' . ($failed?AVAIBILITY_TYPE_OFFLINE:AVAIBILITY_TYPE_AVAILABLE) . ' ' .
		'WHERE id=' . $entry['id'] . '; ' . 
		'INSERT INTO trackrecords ' .
		'(name, time, status) ' .
		'VALUES '.
		'("' . $computerName . '",' . $timeNow . ',' . ($failed?AVAIBILITY_TYPE_OFFLINE:AVAIBILITY_TYPE_AVAILABLE) . '); ';
	
	$computersQuery->next();
	$computerscount += 1;
}


if ($computerscount > 0) {

	$dbTrackHandler->query(
		'BEGIN TRANSACTION; ' . 
		$batch_queries .
		'COMMIT;');
	/*	
	echo("\n\n" .
		'BEGIN TRANSACTION; ' . 
		$batch_queries .
		'COMMIT; '."\n\n");

	echo "Updated " . $computerscount . " since " . nicetime($time_limit) . "." ;
	*/
} else {
	/*
	echo "Whoa! Nothing to check.";
	*/
}
	
?>

