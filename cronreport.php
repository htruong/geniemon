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

$params = array();
  $params['reportName'] = 'Genie All Computers Weekly Report';
  $params['reportType'] = 'computerStats';
  $params['groupType']  = 'hourDayCombined';
  $params['computerRange']  = 'all';
  $params['timeFrame']  = '604800';
  $params['endTimeFrame']  = '0';
  $params['filterWeekends']  = '1';
  $params['filterNights']  = '1';

$retCacheId = generateStats($params);

$mailsubject = "Report(s) generated!";
$mailbody = "Hi,\nThe report " . $params['reportName'] . " is ready to view\ngenerate-report.php?cache_id=$retCacheId \nHave fun!";

$reportmails = explode('|', $cronReportEmails);

foreach ($reportmails  as $reportmail) {
 if (mail($reportmail, $mailsubject, $mailbody))
  echo "Mail sent to $reportmail \n";
}

die();

?>
