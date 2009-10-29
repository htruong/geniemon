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

  define('DB_TRACK_FILE_TMP',	'/var/genie/tmp/genietrack.db');
  define('DB_TRACK_FILE',		'/var/genie/dbs/genietrack.db');
  define('DB_CONTROL_FILE',	'/var/genie/dbs/geniecontrol.db');

  // Guest Permissions, defaulted to view maps only
  define('GUEST_PERMISSIONS',	bindec('110011000000'));
  define('ICON_SIZE_X',	16);
  define('ICON_SIZE_Y',	16);

  $check_interval = 8 * 60; 
  // in seconds.
  // active check interval
  // active check interval should be twice of 
  // the settings of active sending interval from the client.

  $check_packets = 2;
  $check_timeout = 1;

  $check_suffix = '';
  // domain to ping, e.g. .truman.edu
  // will automatically add .truman.edu to computername

  // How many computers to ping at once.
  $check_batch = 15;

  $update_transaction = false;

  $ignored_programs = "svchost.exe|explorer.exe|ctfmon.exe|agent.exe|udaterui.exe|mctray.exe|smax4pnp.exe|ituneshelper.exe|acrotray.exe|hkcmd.exe|igfxpers.exe|isuspm.exe|genieupdclient.exe|rundll32.exe|logon.src";

?>
