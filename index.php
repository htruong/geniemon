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
include_once ("includes/headfoot.php");

initSession();

if (!($_SESSION['loggedinUserPerms'] & VIEW_ZONES)) {
	die('You do not have permission to view this page!');
}

$dbTrackHandler = new SQLiteDatabase(DB_TRACK_FILE);
// Query All Zones
$zonesQuery = $dbTrackHandler->query(
	'SELECT * FROM zones;'
	);

while($zonesQuery->valid()) {
	$entry = $zonesQuery->current();
	
	$thisZone['id'] = $entry['id'];
	$thisZone['name'] = $entry['region_name']; 
	$zones[] = $thisZone;
	$zonesQuery->next();
}

echo _header("System Overview");
?>
		<div id="yui-main">
				<div class="yui-b">
			
				<h2>System Overview</h2>
				<h2>Sites</h2>
					<ul>
<?php
foreach ( $zones as $thisZone )
{
		echo "\t\t\t\t".'<li><a href="viewregion.php?id='.$thisZone['id'].'">'.$thisZone['name'].'</a></li>';
}
?>
					</ul>

				
				</div>
		</div>

		<div class="yui-b">
			<h2>Overview</h2>
<?php

	$result = $dbTrackHandler->query('SELECT count(*) FROM computers;');
	$totalComputers = intval($result->fetchSingle());
	
	$result = $dbTrackHandler->query('SELECT count(*) FROM computers WHERE laststatus=' . AVAIBILITY_TYPE_OFFLINE . ';');
	$offlineComputers = intval($result->fetchSingle());

?>
			<ul>
				<li>Total: <?php echo $totalComputers; ?> PC</li>
				<li>Offline: <font color="red"><?php echo $offlineComputers; ?></font> PC</li>
			</ul>
<?php
if ($_SESSION['loggedinUserPerms'] & EDIT_ZONES) {
?>
			<h2>Add a new Zone</h2>
			<div>
				<form action="zone-add.php" method="POST" enctype="multipart/form-data">
					<input name="newZoneName" id="newZoneName" value="Name"/>
					<input name="newZoneImg" id="newZoneImg" type="file" />
					<input type="Submit" />
				</form>
			</div>
<?php } ?>
		</div>	
<?php 
echo _footer();
?>
