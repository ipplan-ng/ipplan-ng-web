<?php
// IPplan-NG @version <@ :version @>
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

require_once("../ipplanlib.php");
require_once dirname(__FILE__).'/../classes/DBLib.php';
require_once("../auth.php");
require_once("../class.templib.php");

$auth = new BasicAuthenticator(ADMINREALM, REALMERROR);

$auth->addUser(ADMINUSER, ADMINPASSWD);

// And now perform the authentication
$auth->authenticate();

// save the last customer used
// must set path else Netscape gets confused!
setcookie("ipplanCustomer","$cust",time() + 10000000, "/");

// basic sequence is connect, search, interpret search
// result, close connection

// explicitly cast variables as security measure against SQL injection
list($cust) = myRegister("I:cust");

$ds=new IPplan_NG\DBLib() or die(my_("Could not connect to database"));

// force file download due to bad mime type
header("Content-Type: bad/type");
header("Content-Disposition: attachment; filename=base.txt");
header("Pragma: no-cache");
header("Expires: 0");

$startnum=inet_aton(DEFAULTROUTE);
$endnum=inet_aton(ALLNETS);

// if a specific network template exists, use that, else use generic template
$template=new IPplanIPTemplate("basetemplate", $cust);
$err=$template->is_error();

$result=$ds->GetBase($startnum, $endnum, '', $cust);

while($row = $result->FetchRow()) {
   echo inet_ntoa($row["baseaddr"]).FIELDS_TERMINATED_BY.$row["descrip"].FIELDS_TERMINATED_BY.
        inet_ntoa(inet_aton(ALLNETS)+1 - $row["subnetsize"]).FIELDS_TERMINATED_BY;

   if (!$err) {
        $result_template=$ds->ds->Execute("SELECT info, infobin
                FROM baseadd
                WHERE baseindex=".$row["baseindex"]);

        if ( $rowadd = $result_template->FetchRow()) {
            $template->Merge($template->decode($rowadd["info"]));

            foreach($template->userfld as $arr) {
                $tmpfield=csv_escape($arr["value"]);
                echo FIELDS_TERMINATED_BY.$arr["value"];
            }
        }
    }

   echo "\n";
}

// wrap any multiline string with quotes

    // this function only works with php 5 and above
function csv_escape($str) {
    if (PHP_VERSION >= 5) {
        $str = str_replace(array('"', ',', "\n", "\r"), array('""', ',', "\n", "\r"), $str, $count);
        if($count) {
            return '"' . $str . '"';
        } else {
            return $str;
        }
    }
    else {
        $replaced_str = str_replace(array('"', ',', "\n", "\r"), 
                array('""', ', ', " \n", " \r"), $str);
        if(strcmp($replaced_str,$str)) {
            return '"' . $replaced_str . '"';
        } else {
            return $str;
        }
    }
}
    /*}
else {
    // this function adds extra spaces - not ideal but is compatible with php4
    function csv_escape($str) {
        $replaced_str = str_replace(array('"', ',', "\n", "\r"), 
                array('""', ', ', " \n", " \r"), $str);
        if(strcmp($replaced_str,$str)) {
            return '"' . $replaced_str . '"';
        } else {
            return $str;
        }
    }
}*/

?>
