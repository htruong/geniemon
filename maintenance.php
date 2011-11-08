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

/* Various maintainance tasks */

include_once ("includes/config.php");
include_once ("includes/utils.php");


initSession();
header("Content-Type: text/plain");

if (!($_SESSION['loggedinUserPerms'] & EDIT_ZONES)) {
    die('You do not have permission to view this page!');
}

$act = $_GET['act'];

$dbTrackHandler = connectDb();

/* This function automatically collects computers to their respective 'regions' */
function auto_collect($agressive = false) {
  global $dbTrackHandler;

  // Query All Zones with autocollect patterns
  $zonesQuery = $dbTrackHandler->query(
    'SELECT * FROM `zones` WHERE `auto_collect`=1;'
    );

  foreach ($zonesQuery as $entry) {
    $thisZone['id'] = $entry['id'];
    $thisZone['collect_pattern'] = $entry['collect_pattern'];
    $thisZone['region_name'] = $entry['region_name'];

    $autoCollectRegions[] = $thisZone;
  }

  foreach ($autoCollectRegions as $autoCollectRegion) {
    $totalComputersAffected =
      $dbTrackHandler->query(
        'UPDATE `computers` SET `computers`.`region`=' . $autoCollectRegion['id'] . '
         WHERE '. ($agressive ? '' : '`computers`.`region` = -1 AND ') . '
         `computers`.`name` LIKE "' . $autoCollectRegion['collect_pattern'] . '";'
      )->fetch(PDO::FETCH_NUM);
    echo "Collected $totalComputersAffected to zone $autoCollectRegion['region_name']...\n";
  }
}

switch ($act) {
  case 'autocollect':
    auto_collect(($_GET['agressive'] == '1') ? true : false);
    break;
  default:
    die('tell me something to do');
    break;
}
?>