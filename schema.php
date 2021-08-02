<?php
// IPplan-NG <% :version %>
//
// Original IPplan source (c) 2001-2011 Richard Ellerbrock (ipplan at gmail.com)
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//

// when the database layout changes, bump up this value.
define("SCHEMA", 22);

require_once dirname(__FILE__).'/classes/DBLib.php';

// checks to see if user is using latest schema
function CheckSchema() {
   // check php version
   if (PHP_VERSION_ID < 70200) {
      die("You need php version 7.2.0 or later");
   }

   if (PHP_VERSION_ID >= 80000) {
      die("This version of IPplan-NG does not run on PHP 8 yet.");
   }

   // cant use myError here as we do not have access to layout yet!
   $ds=new IPplan_NG\DBLib();
   //$ds->ds->debug = true;
   if (!$ds) {
      die(my_("Could not connect to database"));
   }

   // check mysql version
   if (DBF_TYPE=="mysqli") {
      $result=$ds->ds->Execute("SELECT version() AS version");
      $row = $result->FetchRow();
      preg_match('@(\d+)\.(\d+)\.(\d+)@',$row['version'],$version);
	if (count($version) != 4) {
		die('Error getting MySQL version.');
	}
	$mysql_version_id = ( $version[1] * 10000 + $version[2] * 100 + $version[3] );
      if ($mysql_version_id < 50167) {
         die("You need mysql version 5.1.67 or later");
      }
   }

   // get schema version
   // schema is reserved word in mssql
   if (DBF_TYPE=="mssql" or DBF_TYPE=="ado_mssql" or DBF_TYPE=="odbc_mssql" or 
       DBF_TYPE=='mysqli') {
      $result=$ds->ds->Execute("SELECT version
                             FROM version");
   }
   else {
      $result=$ds->ds->Execute("SELECT version
                             FROM schema");
   }
   // could return error if schema table does not exist!
   if (!$result) {
      echo my_("Problem with database permission or database tables - have installation instructions been followed?")."<p>";
      die(my_("Could not connect to database"));
   }

   $row = $result->FetchRow();
   $version=$row["version"];

   // schema version did not change
   if ($version == SCHEMA)
      return;
   else if (SCHEMA < $version) {
      echo my_("You are trying to downgrade IPplan-NG - impossible");
      exit;
   }

   echo "<b>".my_("Schema version outdated")."</b>:<p>";
   echo sprintf(my_("The database structures need to be upgraded to accommodate new features. For this to happen, you require enough access rights on the %s database. If you get errors back from the upgrade process, you will need to temporarily grant more access rights to the %s database for the duration of the upgrade"), DBF_NAME, DBF_NAME);

   echo "<p>".my_("This process needs to be done by the administrator by executing the installation scripts. See information in the INSTALL and UPGRADE files.");

   exit;

}

?>
