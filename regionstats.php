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

$regid = intval($_GET['id']);
// Fetch available computers in the region

// Now connect to the db
$dbTrackHandler = connectDb();

// and see if we need to check any computer?
$computersQuery = $dbTrackHandler->query(
	'SELECT id, lastsignal, name, laststatus '.
	'FROM computers '.
	'WHERE region = ' . $regid . ' '.
	'ORDER BY id; '
	);
	
//while($computersQuery->valid()) {
//	$computers[] = $computersQuery->current();
//	$computersQuery->next();
//}

$computers = $computersQuery->fetchAll();

header('Content-type: text/plain');
echo array2json($computers);
?>
