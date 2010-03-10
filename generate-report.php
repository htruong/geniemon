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

set_time_limit ( 300 );

include_once ("includes/config.php");
include_once ("includes/utils.php");
include_once ("includes/headfoot.php");

include_once ("includes/recordanalyzer.php");

include_once ('includes/php-ofc-library/open-flash-chart.php');



initSession();

if (!($_SESSION['loggedinUserPerms'] & GEN_STATISTICS)) {
	die('You do not have permission to view this page!');
}


if (intval($_GET['cache_id']) != 0)
{
  $filename = intval($_GET['cache_id']) . '.cache.json';
  // ----------------------------------------------------
  echo _header("Statistics Viewer");
  
  echo <<<DIVMAP
  <div id="yui-main">
  <h2>Statistical results</h2>
  <div id="my_chart">Loading...</div>
  <div><p>Notice: These numbers are the <em>closest estimation</em> generated based on scattered results.</p></div>
  
  <script type="text/javascript" src="js/swfobject.js"></script>
  <script type="text/javascript">
  
  swfobject.embedSWF(
  "open-flash-chart.swf", "my_chart",
  "100%", "600", "9.0.0", "expressInstall.swf",
  {"data-file":"reports-cache/$filename"} );
  
  </script>
  </div>
DIVMAP;

  echo _footer();
  die();
}

// If this is a request, then construct
// the SQL command.

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  $retCacheId = generateStats($_POST);
  header("location: generate-report.php?cache_id=$retCacheId");

  die();
}


// else, please display the request form.

echo _header("Statistics Generation");

echo <<<DIVMAP
<div id="yui-main">
	<h2>Report Set-up</h2>
	
	<form action="generate-report.php" method="post">
	<p>
	Please generate the report named 
		<input id="reportName" name="reportName" value="Genie"> <br /> <br />
		
	about
		<select id="reportType" name="reportType">
		  <option value="computerStats">Computer Availability</option>
		  <option value="programUsage">Program Usage</option>
		</select> <br /> <br />
		
	grouped 
		<select id="groupType" name="groupType">
		  <option value="hourByHour">Hour-by-Hour</option>
		  <option value="dayofWeek">Weekday-by-Weekday</option>
          <option value="individualDay">Day-by-Day</option>
          <option value="hourDayCombined">Hour-by-Hour Day-by-Day</option>
		  <option value="computerName">Computer Name</option>
		  <option value="oneBigChart">One Big Pie Chart</option>
		</select> <br /> <br />
	
	for 
		<select id="computerRange" name="computerRange">
		  <option value="all">- (all computers) -</option>
		  <option value="zone">zone id=</option>
		  <option value="computer">computer name=</option>
		</select>
		<input id="computersRangeParam" name="computersRangeParam" value=""> <br /> <br />
	
	when they are
		<select id="computerStatus" name="computerStatus">
		  <option value="all">- (don't care) -</option>
		  <option value="occupied">busy</option>
		  <option value="available">available</option>
		  <option value="offline">offline</option>
		</select>
	<br /> <br />
	
	beginning
		<select id="timeFrame" name="timeFrame">
		  <option value="0">- (the dawn of time) -</option>
		  <option value="15552000">the last 6 months</option>
		  <option value="10368000">the last 4 months</option>
		  <option value="7776000">the last 3 months</option>
		  <option value="2592000">the last 30 days</option>
		  <option value="1296000">the last 15 days</option>
		  <option value="604800">the last 7 days</option>
		  <option value="86400">the last 24 hours</option>
		</select>
	till
		<select id="endTimeFrame" name="endTimeFrame">
		  <option value="0">- (now) -</option>
		  <option value="15552000">the last 6 months</option>
		  <option value="10368000">the last 4 months</option>
		  <option value="7776000">the last 3 months</option>
		  <option value="2592000">the last 30 days</option>
		  <option value="1296000">the last 15 days</option>
		  <option value="604800">the last 7 days</option>
		  <option value="86400">the last 24 hours</option>
        </select>
        and <select id="filterNights" name="filterNights">
        <option value="1" selected>Filter out Nighttime</option>
        <option value="0">Do not count Nights</option>
        </select>
        
        <select id="filterWeekends" name="filterWeekends">
        <option value="1" selected>Filter out Weekends</option>
        <option value="0">Do not count Weekends</option>
        </select>
        

	<br />
	</p>
	
	<p align="center"><input type="submit" value="Thanks!" /> <br />
	This might take an insane amount of time. Please be patient!</p>
	</form>
</div>
DIVMAP;

echo _footer();
?>
