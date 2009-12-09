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
  $filename = intval($_GET['cache_id'] . '.cache.json';
  // ----------------------------------------------------
  echo _header("Statistics Viewer");
  
  echo <<<DIVMAP
  <div id='yui-main'>
  <h2>Statistical results</h2>
  <div id='my_chart'>Loading...</div>
  <div><p>Notice: These numbers are the <em>closest estimation</em> generated based on scattered results.</p></div>

  <script type='text/javascript' src='js/swfobject.js'></script>
  <script type='text/javascript'>

  swfobject.embedSWF(
  'open-flash-chart.swf', 'my_chart',
  '100%', '600', '9.0.0', 'expressInstall.swf',
  {'data-file":"reports-cache/$filename'} );

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
    // Computer Statistics Generation
    $dbTrackHandler = connectDb();
    $resultBag = generateStatsBag($_POST, $dbTrackHandler);

    // ----------------------------------------------------
    // Generate charts

      $animation_1= isset($_POST['animation_1'])?$_POST['animation_1']:'pop';
      $delay_1    = isset($_POST['delay_1'])?$_POST['delay_1']:0.5;
      $cascade_1    = isset($_POST['cascade_1'])?$_POST['cascade_1']:1;

      $title = new title( "Genie Report: " . $_POST['reportName'] . " - Generated on " . date('Y/m/d h:i:s A'));
      $title->set_style( "{font-size: 12px; color: #000000; text-align: center;}" );

      $bar_stack = new bar_stack();

    if ($_POST['reportType'] == 'computerStats') {
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

    } else {

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
      $filename = time() . '.cache.json';
      $myFile = "reports-cache/" . $filename;
      $fh = fopen($myFile, 'w') or die("can't open file");
      fwrite($fh, $chart->toPrettyString());
      fclose($fh);


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




    //echo "<pre>";
    //print_r($resultBag);
    //echo "</pre>";

    echo _footer();



    die();
    }


// This is really nice :-)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Computer Statistics Generation
	$dbTrackHandler = connectDb();
	$resultBag = generateStatsBag($_POST, $dbTrackHandler);
	
	// ----------------------------------------------------
	// Generate charts

	  $animation_1= isset($_POST['animation_1'])?$_POST['animation_1']:'pop';
	  $delay_1    = isset($_POST['delay_1'])?$_POST['delay_1']:0.5;
	  $cascade_1    = isset($_POST['cascade_1'])?$_POST['cascade_1']:1;

	  $title = new title( "Genie Report: " . $_POST['reportName'] . " - Generated on " . date('Y/m/d h:i:s A'));
	  $title->set_style( "{font-size: 12px; color: #000000; text-align: center;}" );

	  $bar_stack = new bar_stack();
	
	if ($_POST['reportType'] == 'computerStats') {
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
		
	} else {
	  
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
	  $filename = time() . '.cache.json';
	  $myFile = "reports-cache/" . $filename;
	  $fh = fopen($myFile, 'w') or die("can't open file");
	  fwrite($fh, $chart->toPrettyString());
	  fclose($fh);
	  
	  
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




	//echo "<pre>";
	//print_r($resultBag);
	//echo "</pre>";
	
	echo _footer();
	
	
	
	die();
    }


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
