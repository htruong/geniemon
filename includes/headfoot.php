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
 
function _header($title,$embed = false) {
	
if ($embed) $display = "none";
if ($embed) $extrastyle = "<style> html { background: white !important; } </style>";
if (!($_SESSION['loggedinUserPerms'] & GEN_STATISTICS)) $displayrepmenu = "none";

return <<<HEADER
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Genie Computer Monitor Suite &bull; $title</title>
	
	<link rel="stylesheet" type="text/css" href="yui/build/reset-fonts-grids/reset-fonts-grids.css">

	<link rel="stylesheet" type="text/css" href="yui/build/base/base.css">
	
	<link rel="stylesheet" type="text/css" href="assets/genie-hack.css"> 
	
	<link rel="stylesheet" type="text/css" href="yui/build/menu/assets/skins/sam/menu.css"> 

	<!-- Dependency source files -->
	<script type="text/javascript" src="yui/build/yahoo-dom-event/yahoo-dom-event.js"></script>
	<script type="text/javascript" src="yui/build/container/container_core.js"></script>

	<!-- Menu source file -->
	<script type="text/javascript" src="yui/build/menu/menu.js"></script>
	
	<script type="text/javascript">
		/*
			 Initialize and render the MenuBar when its elements are ready 
			 to be scripted.
		*/
		YAHOO.util.Event.onContentReady("genieMainMenu", function () {
			/*
				 Instantiate a MenuBar:  The first argument passed to the 
				 constructor is the id of the element in the page 
				 representing the MenuBar; the second is an object literal 
				 of configuration properties.
			*/
			var oMenuBar = new YAHOO.widget.MenuBar("genieMainMenu", { 
														autosubmenudisplay: true, 
														hidedelay: 750, 
														lazyload: true });
			/*
				 Call the "render" method with no arguments since the 
				 markup for this MenuBar instance is already exists in 
				 the page.
			*/
			oMenuBar.render();
		});
	</script>

	$extrastyle

	<!-- jQuery and tooltip plugin -->
	<script type="text/javascript" src="assets/jquery.js"></script>
	<script type="text/javascript" src="assets/jquery.cluetip.js"></script>
	<link rel="stylesheet" href="assets/jquery.cluetip.css" type="text/css" />
	<script type="text/javascript">
	$(document).ready(function() {
		$('a.tips').cluetip({positionBy: "bottomTop", showTitle: false, ajaxCache: false});
		
	});
	</script>
</head>

<body class="yui-skin-sam">
<div id="doc3" class="yui-t5">
	<div id="hd" style="display: $display;">
		<h1><a href="./">The Genie Computer Monitor Suite</a></h1>
		
<!-- Menu -->


		<div id="genieMainMenu" class="yuimenubar yuimenubarnav">
			<div class="bd">
				<ul class="first-of-type">
					<li class="yuimenubaritem first-of-type"><a class="yuimenubaritemlabel" href="./">Home</a></li>

                    <li class="yuimenubaritem" style="display: none;"><a class="yuimenubaritemlabel" href="#">Reports</a>
						<div id="menureports" class="yuimenu">
							<div class="bd">                    
								<ul>
									<li class="yuimenuitem"><a class="yuimenuitemlabel" href="all-reports.php">View Generated Statistics</a></li>
									<li class="yuimenuitem"><a class="yuimenuitemlabel" href="generate-report.php">Request Statistics</a></li>
								</ul>
							</div>
						</div>
					</li>
					
					<li class="yuimenubaritem"><a class="yuimenubaritemlabel" href="#">Actions</a>
						<div id="menuadministration" class="yuimenu">
							<div class="bd">                    
								<ul>
									<li class="yuimenuitem"><a class="yuimenuitemlabel" href="login.php">Login</a></li>
													<li class="yuimenuitem"><a class="yuimenuitemlabel" href="about-genie.php">About...</a></li>
								</ul>
							</div>
						</div>
					</li>
					
				</ul>            
			</div>
		</div>


<!-- /Menu -->
	</div>
	
	<div id="bd">
	
HEADER;
}

function _footer($embed = false) {

	if ($embed) $display = "none";

	$version = THIS_VERSION;
	return <<<FOOTER

	</div>

	<div id="ft" style="display: $display;">
		<p>Powered by <a href="http://tnhh.net/geniemon.xml">Genie Lab Monitor</a>, v $version.</p>
	</div>

</div>

</body>
</html>
FOOTER;
}
