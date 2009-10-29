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

$compid = intval($_GET['id']);

// Now connect to the db
$dbTrackHandler = new SQLiteDatabase(DB_TRACK_FILE);

// and see if we need to check any computer?
$computersQuery = $dbTrackHandler->query(
	'SELECT * '.
	'FROM computers '.
	'WHERE id = ' . $compid . '; '
	);
	
while($computersQuery->valid()) {
	$result = $computersQuery->current();
	$computersQuery->next();
}

if (!$result) die('Computer information unavailable!');

$computerName = $result['name'];
$computerStatus = intval($result['laststatus']);

// Fetch Last time online/offline info... when applicable.

if ($computerStatus != AVAIBILITY_TYPE_OFFLINE) 
	$lastInfo .=  getLastTimeWithStatus($dbTrackHandler, $computerName, AVAIBILITY_TYPE_OFFLINE);
	
if ($computerStatus != AVAIBILITY_TYPE_AVAILABLE) 
	$lastInfo .=  getLastTimeWithStatus($dbTrackHandler, $computerName, AVAIBILITY_TYPE_AVAILABLE);
	
if ($computerStatus != AVAIBILITY_TYPE_BUSY) 
	$lastInfo .=  getLastTimeWithStatus($dbTrackHandler, $computerName, AVAIBILITY_TYPE_BUSY);


echo '<h4>' . $result['name'] . ': Currently ' . statusTranslate($computerStatus) . '</h4>';
echo '<div>Updated ' . nicetime($result['lastsignal']) . '</div>';
echo $lastInfo;

?>
