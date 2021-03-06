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

$dbTrackHandler = connectDb();

// Query All Zones
$zonesQuery = $dbTrackHandler->query(
	'SELECT DISTINCT zones.id, zones.region_name, zones.hidden COUNT(computers.id) AS availablecount FROM zones
	LEFT JOIN computers ON zones.id = computers.region AND computers.laststatus = ' . AVAIBILITY_TYPE_AVAILABLE . ' 
	WHERE zones.hidden = 0 AND zones.parent_id=0
	GROUP BY zones.id, zones.region_name ORDER BY region_name;'
	);

foreach ($zonesQuery as $entry) {
	$thisZone['id'] = $entry['id'];
	$thisZone['name'] = $entry['region_name']; 
	$thisZone['availablecount'] = $entry['availablecount']; 
	$zones[] = $thisZone;
}


//echo _header("System Overview");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="en-us" />
<title>Available Computers</title>
<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=1.0; user-scalable=no;"> 
<style type="text/css">
	body {
		font-family: Arial, Helvetica, sans-serif;
		margin: 0;
		padding: 0;
		font-size: x-large;
	}

	ul {
		width: 100%;
		margin: 0;
		padding: 0;	
		list-style-type: none;
	}
	ul li {
		width: 100%;
		background-color: #fff;
		margin: 0;
		padding: 3px 5px;
		border-top: 1px solid #666;
		border-bottom: 1px solid #999;
		float: left;
	}
	span.zonename {
		width: 75%;
		display: block;
		float: left;
	}
	span.count {
		width: 15%;
		text-align: right;
		display: block;
		float: left;
	}
	a.details {
		width: 100%;
		display: block;
		text-decoration: none;
		color: #000;
		padding: 10px 10px;
		float: left;
}

</style>

</head>

					<ul>
<?php
foreach ( $zones as $thisZone )
{
		if (intval($thisZone['availablecount']) > 0) {
			$statuscolor = "#C1F3B7";
		} else {
			$statuscolor = "#ffffff";
		}
		echo "\t\t\t\t".'<li style="background-color: '.$statuscolor .'"><a class="details" href="embeddedregion.php?id='.$thisZone['id'].'"><span class="zonename">'.$thisZone['name'].'</span><span class="count">'.$thisZone['availablecount'].'</span></a></li>';
}
?>
					</ul>

				
				</div>
		</div>
	<body>
</html>