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


///////////////////////////////////////////////////////////////////////////////
define('THIS_VERSION',		'0.10.01.081049');

///////////////////////////////////////////////////////////////////////////////
// PERMISSIONS DECLARATION
///////////////////////////////////////////////////////////////////////////////
define('GLOBAL_ADMIN',	    bindec('111111111111'));
define('EDIT_USER',		    bindec('000000000001'));
define('EDIT_COMPUTERS',    bindec('000000000010'));
define('VIEW_LOG',		    bindec('000000000100'));
define('VIEW_STATISTICS',   bindec('000000001000'));
define('START_STOP_BOT',	bindec('000000010000')); 
define('EDIT_ZONES',	    bindec('000000100000'));
define('GEN_STATISTICS',    bindec('000001000000'));
define('VIEW_MAP',		    bindec('100000000000'));
define('VIEW_ZONES',	    bindec('010000000000'));

// RECORD TYPES
define('AVAIBILITY_TYPE_OFFLINE',	0);
define('AVAIBILITY_TYPE_AVAILABLE',	1);
define('AVAIBILITY_TYPE_BUSY',		2);

define('RECORDTYPE_PROGRAMS',		1);



///////////////////////////////////////////////////////////////////////////////
// Connect DB
// Returns: DB Handler
function connectDb()
{
  try {
    $dbh = new PDO(DB_CONNSTRING, DB_USERNAME, DB_PASSWORD);
  } catch (PDOException $e) {
    die('DB CONNECTION FAILED - Genie Quits: ' . $e->getMessage());
  }
  return $dbh;
}


///////////////////////////////////////////////////////////////////////////////
// Escape Strings
function escape_string($str)
{
  if ($str !== null) {
    $str = str_replace(array('\\','\''),array('\\\\','\\\''),$str);
  } else {
    $str = "null";
  }
  return $str;
}

///////////////////////////////////////////////////////////////////////////////
// Salt Generator
function generateSalt ($saltLen)
{ 
  // Declare $salt
  // And create it with random chars
  $salt = '';
  
  for ($i = 0; $i < $saltLen; $i++)
    $salt .= chr(rand(35, 126));

  return $salt;
}

///////////////////////////////////////////////////////////////////////////////
function addNewUser ($dbHandler, $username, $password, $permission)
{
  $thisUserPasswordSalt = generateSalt(5);
  $thisUserPasswordHash = md5($password . $thisUserPasswordSalt);
  // create table and insert first root user
  $dbHandler->query(
      "INSERT INTO users (username, salt, password, permissions) VALUES('$username', '$thisUserPasswordSalt', '$thisUserPasswordHash', $permission);"
  );
	
}

///////////////////////////////////////////////////////////////////////////////
function statusTranslate ($status) 
{
  $retval = "No information";
  switch ($status) {
      case AVAIBILITY_TYPE_OFFLINE:
          $retval = "Offline";
          break;
      case AVAIBILITY_TYPE_AVAILABLE:
          $retval = "Available";
          break;
      case AVAIBILITY_TYPE_BUSY:
          $retval = "Occupied";
          break;
      default:
          $retval = "Unknown";
          break;
  }
  return $retval;
}


///////////////////////////////////////////////////////////////////////////////
function getLastTimeWithStatus($dbHandler, $compName, $status)
{
  /*
  $result2 = $dbHandler->query(
    'SELECT time '.
    'FROM trackrecords '.
    'WHERE name = "' . $compName . '" ' .
    'AND status = "' . $status . '" ' .
    'ORDER BY time DESC ' .
    'LIMIT 1; '
    )->fetch(PDO::FETCH_NUM);

  $lastTime = intval($result2[0]);
  return '<div>Last ' . statusTranslate($status) . ' Time: ' . nicetime($lastTime) . '</div>';
  
  unset($result2);
  */
}




///////////////////////////////////////////////////////////////////////////////
// This script is fetched from
// http://www.bin-co.com/php/scripts/array2json/
 
function array2json($arr) { 
  if(function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality.
  $parts = array();
  $is_list = false;

  //Find out if the given array is a numerical array
  $keys = array_keys($arr);
  $max_length = count($arr)-1;
  if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
      $is_list = true;
      for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
          if($i != $keys[$i]) { //A key fails at position check.
              $is_list = false; //It is an associative array.
              break;
          }
      }
  }

  foreach($arr as $key=>$value) {
      if(is_array($value)) { //Custom handling for arrays
          if($is_list) $parts[] = array2json($value); /* :RECURSION: */
          else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
      } else {
          $str = '';
          if(!$is_list) $str = '"' . $key . '":';

          //Custom handling for multiple data types
          if(is_numeric($value)) $str .= $value; //Numbers
          elseif($value === false) $str .= 'false'; //The booleans
          elseif($value === true) $str .= 'true';
          else $str .= '"' . addslashes($value) . '"'; //All other things
          // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

          $parts[] = $str;
      }
  }
  $json = implode(',',$parts);

  if($is_list) return '[' . $json . ']';//Return numerical JSON
  return '{' . $json . '}';//Return associative JSON 
} 


///////////////////////////////////////////////////////////////////////////////
// initSession
// Have to call before anything
function initSession()
{
  session_start();
  if (!$_SESSION['loggedinUserPerms'])
  {
    $_SESSION['loggedinUserPerms'] = GUEST_PERMISSIONS;
  }
}

///////////////////////////////////////////////////////////////////////////////


$allignored = explode('|', $ignored_programs);

function validProgram($program)
{
  global $allignored;

  $result = apc_fetch('ignored_' . strtolower($program), $success);

  if (!$success)
  {
    $result = !in_array(strtolower($program), $allignored);
    apc_add('ignored_' . strtolower($program), $result);
  }

  return $result;
  
}


///////////////////////////////////////////////////////////////////////////////
// FROM http://us.php.net/time

function nicetime($date)
{
  if(empty($date)) {
      return "Never";
  }

  $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
  $lengths         = array("60","60","24","7","4.35","12","10");

  $now             = time();
  $unix_date       =  $date;

      // check validity of date
  if(empty($unix_date)) {
      return "Bad date";
  }

  // is it future date or past date
  if($now > $unix_date) {
      $difference     = $now - $unix_date;
      $tense         = "ago";

  } else {
      $difference     = $unix_date - $now;
      $tense         = "from now";
  }

  for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
      $difference /= $lengths[$j];
  }

  $difference = round($difference);

  if($difference != 1) {
      $periods[$j].= "s";
  }

  return "$difference $periods[$j] {$tense}";
}


///////////////////////////////////////////////////////////////////////////////



function getCompNamesId($dbTrackHandler, $forceUpdate=false)
{
  $compNames = array();
  $compNames = apc_fetch('compNames', $success);

  // if the names are not cached, then cache them.
  if (!$success || $forceUpdate)
  {
    $computerNames = $dbTrackHandler->query('SELECT id, name FROM computers;');
    foreach ($computerNames as $entry)
    {
      $compNames[$entry['name']] = intval($entry['id']);
    }
    apc_add('compNames', $compNames);
  }
  return $compNames;
}



///////////////////////////////////////////////////////////////////////////////


function getRegionNamesId($dbTrackHandler, $forceUpdate=false)
{
  $regNames = array();
  $regNames = apc_fetch('regNames', $success);
  // if the names are not cached, then cache them.
  if (!$success || $forceUpdate)
  {
    $regionNames = $dbTrackHandler->query('SELECT id, region_name FROM zones;');
    foreach ($regionNames as $entry)
    {
      $regNames[$entry['region_name']] = intval($entry['id']);
    }
    apc_add('regNames', $regNames);
  }
  return $regNames;
}

///////////////////////////////////////////////////////////////////////////////

function getAllComputersIds($dbTrackHandler, $regionId, $forceUpdate=false)
{

}
?>
