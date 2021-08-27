#!/usr/local/bin/php -q
<?php
// IPplan-NG <% :version %>
//
// Original IPplan source (c) 2001-2011 Richard Ellerbrock (ipplan at gmail.com)
//
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


set_time_limit(0);

// can hardcode NMAP here - config.php will not overwrite existing defines
// so define them before reading config.php below

//define("NMAP", '/usr/bin/nmap');

//define("DBF_TYPE", "mysql");
//define("DBF_HOST", "localhost");
//define("DBF_USER", "ipplan");
//define("DBF_NAME", "ipplan");
//define("DBF_PASSWORD", "ipplan99");

// define the nmap command for normal scan
define("NMAP_CMD", "-sP -PE -q -n -oG");

// define the nmap command for scan with dns lookup
define("NMAP_CMDH", "-sP -PE -q -R -v -oG");

require_once("../adodb/adodb.inc.php");
require_once("../config.php");

$timestamp = FALSE;
$hostnames = FALSE;
$audit = FALSE;

// Set Defaults
// $options["h"]=TRUE;

parseArgs($argv,$options);

// options will be $options["FLAG"] (if not argument is given like -h the 
// $options["h"] will be set to bolean true

if (isset($options["a"]) && $options["a"] == TRUE) {
    $audit = TRUE;
}

if (isset($options["time"]) && $options["time"] == TRUE) {
    $timestamp = TRUE;
}

if (isset($options["hostnames"]) && $options["hostnames"] == TRUE) {
    $hostnames = TRUE;
}
     
if (!isset($options["q"])) {
    if (!empty($_REQUEST)) {
        echo "<br>Mmm, this is a command line utility not to be executed via a web browser! Try the -q option\n";
        exit(1);
    }
}
if (isset($options["d"]) && $options["d"] == true) {
    print_customers();
    exit(1);
}
else if (isset($options["f"]) && is_string($options["f"]) and 
         isset($options["c"]) && is_numeric($options["c"])) {
    if (!is_readable($options["f"])) {
        echo "\nCannot read file!\n";
    }
    else if (!is_executable(NMAP)) {
        echo NMAP;
        echo "\nCannot find NMAP!\n";
    }
    else {
        do_poll($options["f"], $options["c"]);
        exit(0);
    }
}

print_usage();
exit(1);

function parseArgs($a = array(), &$r) {
    $f=NULL;
    for($x = 0; $x < count($a); $x++) {

        if($a[$x]{0} == "-") {
            $f=$a[$x];
            $r[substr($f,1,strlen($f))]=true;
        }
        if ($f != NULL) {
            if (isset($a[$x+1]) && ($a[$x+1] != NULL) && 
                ($a[$x+1] != "") && ($a[$x+1] != "") && 
                ($a[$x+1]{0} != "-")) {
                $r[substr($f,1,strlen($f))]=$a[$x+1];
            } else {
                $f="";
                if (isset($a[$x+1])) {
                    $f=$a[$x+1];
                }
            }
        }
    }
}

function print_usage() {
       echo "
IPplan poller v1.0

  -h        this message!
  -q        suppress check if tool is executed from the command line
  -d        dump a list of customers and customer id's
  -f        filename containing list of subnets to scan, one per line in address/bits format
                see the NMAP manpage for examples
  -hostnames    resolve and populate hostnames 
  -time     timestamps the scan at start and completion
  -a        create auditlog entries for newly added records
            
  -c        customer id to update

  example:  php ipplan-poller.php -d 
            php ipplan-poller.php -time -hostnames -f /tmp/nmap.lst -c 1
       \n";

}

// NmapScan function modified to get hostnames 
// As list is parsed, each line is broken into its components and popped into an array
// The array elements of interest in $myhosts are 1 (ip addr), 2 (hostname) and 5 (ip addr 
// run through inet_aton) which are stuffed into $both and returned
  
function NmapScan ($range) {

    $NMAP = NMAP;
    global $hostnames;

    // Check the $hostnames variable to see which command line to use
    if ($hostnames == TRUE) {
        $NMAP_CMD = NMAP_CMDH;
    }
    elseif ($hostnames == FALSE) {
        $NMAP_CMD = NMAP_CMD;
    }

    // then continue on 

    $range=escapeshellarg($range);

    $nmap = `$NMAP $NMAP_CMD - $range`;
    $ret=array();

    foreach (explode("\n", $nmap) as $line) {
        if(preg_match ("/^Host: ([\d\.]*).*.*Status: Up$/", $line, $m)) {
            $ret[$m[1]] = "";   // host is active, no hostname defined

            if ($hostnames) { // remove brackets
                $myhosts = (preg_split('/[\s,]+/',$line));
                $ret[$m[1]] = preg_replace('/[()]/', '', $myhosts[2]);
            }
        }
    }
    if (DEBUG) {
        echo "For nmap range $range found the following active addresses\n";
        echo "Using this nmap command: $NMAP $NMAP_CMD - $range\n";
        var_dump($ret);
    }
    return $ret;
}

function inet_aton($a) {
    $inet = 0.0;
    $t = explode(".", $a);
    for ($i = 0; $i < 4; $i++) {
        $inet *= 256.0;
        $inet += $t[$i];
    };
    return $inet;
}

// the main poll function
function do_poll($filename, $cust) {

    // If the -time parameter exists, get the start time  
    global $timestamp, $starttime, $hostnames, $audit;

    if ($timestamp == TRUE) {
        $starttime = date("F j, Y, g:i:s a");  
    }

    $ds=open_dbf();

    $handle = fopen($filename, "r");
    while (!feof($handle)) {
        $buffer = chop(fgets($handle, 256));

        // skip empty lines
        if (empty($buffer)) {
            continue;
        }

        // We break up the $both variable passed back which ends up with the $ret value 
        // in $hosts1 and the $myhosts value in $names 

        $hosts=NmapScan($buffer);
        // got an error?
        if (empty($hosts)) {
            continue;
        }

        // each nmap run is a transaction - problem with scan an entire transaction
        // is dumped
        if (DBF_TRANSACTIONS)
            $ds->BeginTrans();
        // now loop through each address polled
        foreach($hosts as $key=>$hname) {
            $ipaddr=inet_aton($key);

            // find the subnet the address belongs to
            $result = $ds->Execute("SELECT baseindex
                    FROM base
                    WHERE $ipaddr BETWEEN baseaddr AND
                    baseaddr+subnetsize-1 AND
                    customer=$cust");

            // got a subnet, now try to update. if update fails, insert a new empty record
            if ($row=$result->FetchRow()) {
                $baseindex=$row["baseindex"];

                if ($hostnames) {
                    $result = $ds->Execute("UPDATE ipaddr
                            SET lastpol=".$ds->DBTimeStamp(time()).", hname=".$ds->qstr($hname)."
                            WHERE baseindex=$baseindex AND
                            ipaddr=$ipaddr");
                }
                else {
                    $result = $ds->Execute("UPDATE ipaddr
                            SET lastpol=".$ds->DBTimeStamp(time())."
                            WHERE baseindex=$baseindex AND
                            ipaddr=$ipaddr");
                }

                if ($ds->Affected_Rows() == 0) {
                    $ds->Execute("INSERT INTO ipaddr
                            (userinf, location, telno, descrip, hname,
                             baseindex, ipaddr, lastmod, lastpol, userid)
                            VALUES
                            (".$ds->qstr("").",
                             ".$ds->qstr("").",
                             ".$ds->qstr("").",
                             ".$ds->qstr("Unknown - added by IPplan command line poller").",
                             ".$ds->qstr($hname).",
                             $baseindex, 
                             $ipaddr,
                             ".$ds->DBTimeStamp(time()).",
                             ".$ds->DBTimeStamp(time()).",
                             ".$ds->qstr("POLLER").")");

                    if ($audit) {
                        $ds->Execute("INSERT INTO auditlog
                                (action, userid, dt)
                                VALUES
                                (".$ds->qstr(sprintf("User POLLER added ip record %s customer %u index %u", $key, $cust, $baseindex)).",
                                 ".$ds->qstr("POLLER").",
                                 ".$ds->DBTimeStamp(time()).")");
                    }
                }

            }
            // no subnet found!
            else {
                echo "No IPplan subnet found for address $key\n";
            }
        } // end foreach
        if (DBF_TRANSACTIONS)
            $ds->CommitTrans();
    } // end of while loop for each file line

    fclose($handle);
    close_dbf($ds);

    // If the -time parameter exists, timestamp it when done
    if ($timestamp == TRUE) {
        echo "Started:  $starttime";
        echo "\n";
        $today = date("F j, Y, g:i:s a");  
        echo "Finished: $today";
        echo "\n";
    }

}

// dump a list of customers -d command line option
function print_customers() {

   $ds=open_dbf();
   $result=$ds->Execute("SELECT customer, custdescrip
                         FROM customer");
   echo "ID\tDescription\n\n";
   while($row=$result->FetchRow()) {
       if ($row["custdescrip"]=="all")
           continue;
       echo sprintf("%d\t%s\n", $row["customer"], $row["custdescrip"]);
   }
   close_dbf($ds);
}

function open_dbf() {

    $ds = ADONewConnection(DBF_TYPE);
    $ds->debug = DBF_DEBUG;
    if ($ds-> Connect(DBF_HOST, DBF_USER, DBF_PASSWORD, DBF_NAME) != false) {
        $ds->SetFetchMode(ADODB_FETCH_ASSOC);
        return $ds;
    }
    exit(1);
}

function close_dbf($ds) {
        $ds->Close();
}

?>
