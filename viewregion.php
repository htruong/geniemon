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

// no sql injection!
$regid = intval($_GET['id']);
$iconOffsetX = ICON_SIZE_X / 2;
$iconOffsetY = ICON_SIZE_Y / 2;
$embedded = (intval($_GET['embedded']) == 1) ? true : false ;

$dbTrackHandler = connectDb();

// Query Selected Zone
$zoneQuery = $dbTrackHandler->query(
  'SELECT * ' .
  'FROM zones ' .
  'WHERE id = ' . $regid . ';');

//if (! ($zoneQuery->nextRowset()))
//  die('This zone ID is invalid!');


$entry = $zoneQuery->fetch();

$regionName = $entry['region_name'];
$regionWidth = $entry['region_width']; 
$regionHeight = $entry['region_height']; 
$regionOverlay = $entry['region_map_img']; 

$zoneQuery->closeCursor();
unset($zoneQuery);

$hasMap = (($regionWidth > 0) && ($regionHeight > 0));

$regionCss = '';
$regionCssClass = (!$hasMap) ? 'abspos' : '';
if ($hasMap)
{
  // Construct CSS for the region
  $regionCss = "display: block; width: $regionWidth"."px; height: $regionHeight"."px;";
  if ($regionOverlay != "") $regionCss .= " background: url($regionOverlay) top left no-repeat;";
}

$zoneEditable = ( $_SESSION['loggedinUserPerms'] & EDIT_ZONES ) ? "true" : "false";

// Fetch available computers in the region

$computersQuery = $dbTrackHandler->query(
  'SELECT * ' .
  'FROM computers ' .
  'WHERE region = ' . $regid . ' '.
  'ORDER BY id;');

$regionHTML = '';
  
foreach ($computersQuery as $entry)
{
  $regionHTML .= "\t\t\t\t".'<div class="computerbit computerbit-noinfo" id="computer'.$entry['id'].'" style="';
  if ($hasMap) $regionHTML .= 'left: '.($entry['x']-$iconOffsetX).'px; top: '.($entry['y']-$iconOffsetY).'px; ';
  $regionHTML .= '" onClick="editComputerDetails(this, '.$entry['id'].',\''.$entry['name'].'\','.$entry['x'].','.$entry['y'].');" >';
  $regionHTML .= '<a class="acomputer tips" rel="tip-computerdetails.php?id='.$entry['id'].'">&nbsp;';
  if (!$hasMap) $regionHTML .= $entry['name'];
  $regionHTML .= '</a></div>'."\n";
}

unset($computersQuery);


echo _header("Viewing $regionName", $embedded);

echo <<<DIVMAP
<div id="yui-main">
		<h2>Overview Map</h2>
		<div class="mapscontainer">
		<div class="regionmap" id="regionmap" class="$regionCssClass" style="$regionCss">
		<div class="transparentregionlayer" id="regiontransparent" style="display: block; cursor: crosshair; width: 100%; height: 100%; background: transparent url(assets/bullet_placeholder.png) -16px -16px no-repeat;">
			$regionHTML
		</div>
		</div>
                <div id="legends" style="text-align: center">
                Move mouse over a computer for more information.<br />
                Machine name (<font color="darkred">WCS</font><font color="darkblue">200</font><font color="darkgreen">03</font>) = <font color="darkred">BuildingAbbreviation</font><font color="darkblue">RoomNumber</font><font color="darkgreen">MachineNumber</font>.<br />
                <strong>Legends</strong>: <div class="computerbit computerbit-busy" style="position: static; display: inline">&nbsp; &nbsp;</div> Computer in use <div class="computerbit computerbit-available" style="position: static; display: inline">&nbsp; &nbsp;</div> Computer is available for use <div class="computerbit computerbit-noresponse" style="position: static; display: inline">&nbsp; &nbsp;</div> No information or computer is down.
                </div>

		<div>
		<h2>Status</h2>
		<div id="statusbar">Initializing...</div>
		<script type="text/javascript" src="yui/build/yahoo/yahoo-min.js"></script>
		<script type="text/javascript" src="yui/build/connection/connection-min.js"></script>
		<script type="text/javascript" src="assets/updater.js"></script>
		<script type="text/javascript" src="assets/editor.js"></script>
		<div id="adminInterface" style="display:none;" >
		<h2>Add new computer/Edit Existing Computer</h2>
		<form id="frmAdd" name="frmAdd" method="POST" action="regionaddpc.php">
			<input type="hidden" id="region_id" name="region_id" value="$regid" />
			<input type="hidden" id="id" name="id" value="0" />
			<div style="display: none">
			X: <input name="x" id="x" value="1" />
			Y: <input name="y" id="y" value="1" /><br />
			</div>
			Computer name: <input name="computer_name" id="computer_name" value="$regionName" />
			<input type="submit" />
			<div id="editWarning" style="display: none;">This will move / edit the selected computer, instead of creating a new one. <a href="#" onClick="return createNewInstead()" id="addNewInsteadLink">Add a new computer instead</a>.</div>
		</form>
		</div>
		
		<script>
			var zoneEditable = $zoneEditable;
			var isConfirmed = false;
			var relativeX, relativeY;
			
			updatePcStatus($regid);
			updaterRegion = document.getElementById("regiontransparent");
			adminInterfc = document.getElementById("adminInterface");
			
			if (zoneEditable) {
				updaterRegion.onclick = confirmMouse;
				updaterRegion.onmousemove = getMouse;
				adminInterfc.style.display = "";
			} else {
			}
			
			function getMouse(e) {
				if (!isConfirmed) {
					var posx = 0, posy = 0;
					var e=(!e)?window.event:e;//IE:Moz
					if (e.pageX){//Moz
						posx=e.pageX;//+window.pageXOffset;
						posy=e.pageY;//+window.pageYOffset;
					}
					else if(e.clientX){//IE
						if(document.documentElement){//IE 6+ strict mode
							posx=e.clientX;//+document.documentElement.scrollLeft;
							posy=e.clientY;//+document.documentElement.scrollTop;
						}
						else if(document.body){//Other IE
							posx=e.clientX;//+document.body.scrollLeft;
							posy=e.clientY;//+document.body.scrollTop;
						}
					}
					
					else{return false}//old browsers
					relativeX = posx - findPos(updaterRegion)[0];
					relativeY = posy - findPos(updaterRegion)[1];

					updaterRegion.style.backgroundPosition = (relativeX - $iconOffsetX) + "px " + (relativeY - $iconOffsetY) + "px";
				}
			}

			function confirmMouse(e) {
				getMouse(e);
				isConfirmed = !isConfirmed;
				document.getElementById('x').value = relativeX;
				document.getElementById('y').value = relativeY;
			}
			
		</script>
		
</div>
DIVMAP;

echo _footer($embedded);
?>
