<?php
// IPplan-NG @version <@ :version @>
//
// Original IPplan source (c) 2001-2011 Richard Ellerbrock (ipplan at gmail.com)
//
// Modified by Tony D. Koehn Feburary 2003
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
require_once("../layout/class.layout");
require_once("../auth.php");
require_once("../class.templib.php");
require_once("../class.xptlib.php");

$auth = new SQLAuthenticator(REALM, REALMERROR);

// And now perform the authentication
$grps=$auth->authenticate();

// save the last customer used
// must set path else Netscape gets confused!
setcookie("ipplanCustomer","$cust",time() + 10000000, "/");

// set language
myLanguage(getUserLanguage());

//setdefault("window",array("bgcolor"=>"white"));
//setdefault("table",array("cellpadding"=>"0"));
//setdefault("text",array("size"=>"2"));

if ($action=='add') {
    $title=my_("Create DNS Zones");
}
else {
    $title=my_("Edit DNS Zones");
}
newhtml($p);

insert($p, $h = wheader("IPPlan - $title"));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../cookies.js")));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../phpserializer.js")));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../ipplanlib.js")));

$w=myheading($p, $title, true);

// explicitly cast variables as security measure against SQL injection
list($cust, $dataid, $action, $domain, $responsiblemail, $serialdate, $serialnum, $ttl, $retry, $refresh, $expire, $minimum, $slaveonly, $zonepath, $seczonepath) = myRegister("I:cust I:dataid S:action S:domain S:responsiblemail I:serialdate I:serialnum I:ttl I:retry I:refresh I:expire I:minimum S:slaveonly S:zonepath S:seczonepath");

if (!$_GET) {
   myError($w,$p, my_("You cannot reload or bookmark this page!"));
}

// basic sequence is connect, search, interpret search
// result, close connection
$ds=new IPplan_NG\DBLib() or myError($w,$p, my_("Could not connect to database"));

insert($w, $f = form(array("name"=>"ENTRY",
                           "method"=>"post",
                           "action"=>"modifydns.php")));

// Use the same form for adding or editing.  Setup page & variables based on action.
if ($action=='add') {
    $now = getdate();
    $serialdate = $now["year"] . str_pad($now["mon"], 2, '0', STR_PAD_LEFT) . str_pad( $now["mday"], 2, '0', STR_PAD_LEFT);
    $serialnum=0;

    $zone="";

    $ttl=DNSTTL;
    $refresh=DNSREFRESH;
    $retry=DNSRETRY;
    $expire=DNSEXPIRE;
    $minimum=DNSMINTTL;   
    $slaveonly=DNSSLAVEONLY;
    $responsiblemail=REGADMINEMAIL;
    $zonepath="/var/named/test.zone";
    $seczonepath="";
    insert($f,hidden(array("name"=>"action", "value"=>"add")));
    $myTitle="Add";
}else{
   insert($f,hidden(array("name"=>"action", "value"=>"edit")));
   insert($f,hidden(array("name"=>"dataid", "value"=>"$dataid")));
   $myTitle="Edit";
}
// strip @ from email address if it exists
$responsiblemail=str_replace("@", ".", $responsiblemail);

insert($f,hidden(array("name"=>"cust", "value"=>"$cust")));
insert($f,hidden(array("name"=>"serialdate", "value"=>"$serialdate")));
insert($f,hidden(array("name"=>"serialnum", "value"=>"$serialnum")));

insert($f,heading(3, my_("$myTitle a Zone")));
insert($f,textbr(my_("Maintain forward zone SOA information.")));

insert($f, $con=container("fieldset",array("class"=>"fieldset")));
insert($con, $legend=container("legend",array("class"=>"legend")));
insert($legend, text(my_("Forward zone information")));

insert($con,textbr(my_("Zone (Domain Name)")));
if ($action=="add") {
    insert($con,span(my_("Separate multiple domain names with ;"), array("class"=>"textSmall")));
}
myFocus($p, "ENTRY", "domain");
insert($con,input_text(array("name"=>"domain",
                           "value"=>"$domain",
                           "size"=>"30",
                           "maxlength"=>"253")));

insert($con,checkbox(array("name"=>"slaveonly"),
                   "Slave Zone?",
                   ($slaveonly == "Y" ? "on" : "")));

// if creating new zone, get dns servers from revdns table
if ($action=="add") {
    // give option of reading zone from existing DNS server via zone transfer
    insert($con,textbrbr(my_("Zone transfer from DNS server")));
    insert($con,span(my_("Blank for no zone transfer"), array("class"=>"textSmall")));
    insert($con,span(my_("Slave zones only import SOA information, not zone records"), array("class"=>"textSmall")));
    insert($con,input_text(array("name"=>"server",
                    "size"=>"30",
                    "maxlength"=>"30")));

    $result2=$ds->ds->Execute("SELECT hname,horder
            FROM revdns
            WHERE customer=$cust");

    if (DBF_TYPE=="mysqli") {
        $version=$ds->ds->GetOne("SELECT version() AS version");

        if ($version >= "4.0.14") {
            insert($con,textbrbr(my_("--- OR ---")));
            insert($con,checkbox(array("name"=>"clone"),
                        "Clone zone records from template.com zone?"));
        }
    }
    $createyear = $createmonth =  $createday = "";
    $expireyear = $expiremonth = $expireday = "";
    $regyear = $regmonth = $regday = "";

}
else {
    $sqlcreatemod = $ds->ds->SQLDate("Y-m-d", 'createmod');
    $sqlexpiremod = $ds->ds->SQLDate("Y-m-d", 'expiremod');
    $sqlregmod = $ds->ds->SQLDate("Y-m-d", 'regmod');
    $row = $ds->ds->GetRow("SELECT data_id, domain, responsiblemail, 
                                serialdate, serialnum, ttl, refresh, retry, expire, minimum, 
                                zonefilepath1 AS zonepath, zonefilepath2 AS seczonepath, 
                                customer, admingrp, slaveonly,
                                $sqlcreatemod AS createmod, $sqlexpiremod AS expiremod, 
                                $sqlregmod AS regmod
                             FROM fwdzone 
                             WHERE customer=$cust AND data_id=$dataid");

    $domain=$row["domain"];
    $responsiblemail=$row["responsiblemail"];
    $serialdate=$row["serialdate"];
    $serialnum=$row["serialnum"];
    $ttl=$row["ttl"];
    $retry=$row["retry"];
    $refresh=$row["refresh"];
    $expire=$row["expire"];
    $minimum=$row["minimum"];
    $slaveonly=$row["slaveonly"];
    $zonepath=$row["zonepath"];
    $seczonepath=$row["seczonepath"];
    if (!empty($row["createmod"])) {
        list($createyear, $createmonth, $createday) = preg_split('/[/.-]/', $row["createmod"]);
    }
    else {
        $createyear = $createmonth =  $createday = "";
    }
    if (!empty($row["expiremod"])) {
        list($expireyear, $expiremonth, $expireday) = preg_split('/[/.-]/', $row["expiremod"]);
    }
    else {
        $expireyear = $expiremonth = $expireday = "";
    }
    if (!empty($row["regmod"])) {
        list($regyear, $regmonth, $regday) = preg_split('/[/.-]/', $row["regmod"]);
    }
    else {
        $regyear = $regmonth = $regday = "";
    }

    $result2=$ds->ds->Execute("SELECT hname,horder 
            FROM fwddns
            WHERE id=$dataid");
}

$days=array("1"=>"1",
            "2"=>"2",
            "3"=>"3",
            "4"=>"4",
            "5"=>"5",
            "6"=>"6",
            "7"=>"7",
            "8"=>"8",
            "9"=>"9",
            "10"=>"10",
            "11"=>"11",
            "12"=>"12",
            "13"=>"13",
            "14"=>"14",
            "15"=>"15",
            "16"=>"16",
            "17"=>"17",
            "18"=>"18",
            "19"=>"19",
            "20"=>"20",
            "21"=>"21",
            "22"=>"22",
            "23"=>"23",
            "24"=>"24",
            "25"=>"25",
            "26"=>"26",
            "27"=>"27",
            "28"=>"28",
            "29"=>"29",
            "30"=>"30",
            "31"=>"31");
$months=array("1"=>my_("January"),
              "2"=>my_("February"),
              "3"=>my_("March"),
              "4"=>my_("April"),
              "5"=>my_("May"),
              "6"=>my_("June"),
              "7"=>my_("July"),
              "8"=>my_("August"),
              "9"=>my_("September"),
              "10"=>my_("October"),
              "11"=>my_("November"),
              "12"=>my_("December"));

$years=array();
$i = 1990;
while ($i < date('Y')+15) $years[$i++] = $i;

// create a table
insert($f, $con=container("fieldset",array("class"=>"fieldset")));
insert($con, $legend=container("legend",array("class"=>"legend")));
insert($legend, text(my_("Registrar information")));

insert($con,$t = table(array("cols"=>"2")));

insert($t,$c = cell());
insert($c,text(my_("Date zone created")));
insert($t,$c = cell());
insert($c,text(my_("Day")));
insert($c,selectbox($days, array("name"=>"createday"), $createday));

insert($c,text(my_("Month")));
insert($c,selectbox($months, array("name"=>"createmonth"), $createmonth));

insert($c,text(my_("Year")));
insert($c,selectbox($years, array("name"=>"createyear"), $createyear));

insert($t,$c = cell());
insert($c,text(my_("Last registration modification")));
insert($t,$c = cell());
insert($c,text(my_("Day")));
insert($c,selectbox($days, array("name"=>"regday"), $regday));

insert($c,text(my_("Month")));
insert($c,selectbox($months, array("name"=>"regmonth"), $regmonth));

insert($c,text(my_("Year")));
insert($c,selectbox($years, array("name"=>"regyear"), $regyear));

insert($t,$c = cell());
insert($c,text(my_("Date zone expires")));
insert($t,$c = cell());
insert($c,text(my_("Day")));
insert($c,selectbox($days, array("name"=>"expireday"), $expireday));

insert($c,text(my_("Month")));
insert($c,selectbox($months, array("name"=>"expiremonth"), $expiremonth));

insert($c,text(my_("Year")));
insert($c,selectbox($years, array("name"=>"expireyear"), $expireyear));


insert($f, $con=container("fieldset",array("class"=>"fieldset")));
insert($con, $legend=container("legend",array("class"=>"legend")));

if ($action=="add") {
    insert($legend, text(my_("Zone SOA information if zone not created via zone transfer")));
}
else {
    insert($legend, text(my_("Zone SOA information")));
}
 
$i=1;
while($row2 = $result2->FetchRow()) {
   $hname[$row2["horder"]]=$row2["hname"];
   $i++;
}

// capture data for the export view functionality
$export = new exportForm();
$export->addRow(array("name_server_1", "name_server_2", "name_server_3", "name_server_4", "name_server_5", "name_server_6", "name_server_7", "name_server_8", "name_server_9", "name_server_10"));
$export->saveRow();
$export->addRow(NULL);

// space for 10 reverse entries
for ($i=1; $i < 11; $i++) {
    insert($con,textbr(sprintf(my_("Name server %u:"), $i)));
    insert($con,input_text(array("name"=>"hname[".$i."]",
                               "value"=>isset($hname[$i]) ? $hname[$i] : "",
                               "size"=>"80",
                               "maxlength"=>"100")));
    insert($con,textbr());
 
    $export->addCell(isset($hname[$i]) ? $hname[$i] : "");
}
$export->saveRow();

insert($f, $con=container("fieldset",array("class"=>"fieldset")));
insert($con, $legend=container("legend",array("class"=>"legend")));
insert($legend, text(my_("Forward zone SOA header information")));

insert($con,textbr(my_("Technical contact email address")));
insert($con,span(my_("No @ allowed - replace with ."), array("class"=>"textSmall")));
insert($con,input_text(array("name"=>"responsiblemail",
                           "value"=>"$responsiblemail",
                           "size"=>"64",
                           "maxlength"=>"64")));
insert($con,textbrbr(my_("TTL")));
insert($con,input_text(array("name"=>"ttl",
                           "value"=>"$ttl",
                           "size"=>"10",
                           "maxlength"=>"10")));

insert($con,textbrbr(my_("Refresh")));
insert($con,input_text(array("name"=>"refresh",
                           "value"=>"$refresh",
                           "size"=>"5",
                           "maxlength"=>"10")));

insert($con,textbrbr(my_("Retry")));
insert($con,input_text(array("name"=>"retry",
                           "value"=>"$retry",
                           "size"=>"5",
                           "maxlength"=>"10")));

insert($con,textbrbr(my_("Expire")));
insert($con,input_text(array("name"=>"expire",
                           "value"=>"$expire",
                           "size"=>"5",
                           "maxlength"=>"10")));

insert($con,textbrbr(my_("Minimum TTL")));
insert($con,input_text(array("name"=>"minimum",
                           "value"=>"$minimum",
                           "size"=>"5",
                           "maxlength"=>"10")));

$dbfinfo=$ds->ds->GetOne("SELECT info FROM fwdzoneadd 
                          WHERE customer=$cust AND data_id=$dataid");

// use base template (for additional subnet information)
$template=new IPplanIPTemplate("fwdzonetemplate", $cust);

if ($template->is_error() == FALSE) {
    insert($f, $con=container("fieldset",array("class"=>"fieldset")));
    insert($con, $legend=container("legend",array("class"=>"legend")));
    insert($legend, text(my_("Additional information")));

    $template->Merge($template->decode($dbfinfo));
    $template->DisplayTemplate($con);
}

insert($f, $con=container("fieldset",array("class"=>"fieldset")));
insert($con, $legend=container("legend",array("class"=>"legend")));
insert($legend, text(my_("Forward zone location")));

insert($con,textbr(my_("Zone File Path")));
insert($con,span(my_("The path where the zone file will be written once exported and processed. Examples:"), array("class"=>"textSmall")));
insert($con,span(my_("ftp://myhost.com/var/named/test.zone - if you want to transfer the zone using ncftput"), array("class"=>"textSmall")));
insert($con,span(my_("user@myhost.com:/var/named/test.zone - if you want to transfer the zone using scp"), array("class"=>"textSmall")));
insert($con,input_text(array("name"=>"zonepath",
                           "value"=>"$zonepath",
                           "size"=>"80",
                           "maxlength"=>"254")));

insert($con,textbrbr(my_("Secondary Zone File Path")));
insert($con,input_text(array("name"=>"seczonepath",
                           "value"=>"$seczonepath",
                           "size"=>"80",
                           "maxlength"=>"254")));

insert($f,submit(array("value"=>my_("Save"))));
insert($f,freset(array("value"=>my_("Clear"))));
myCopyPaste($f, "ipplanCPdnsrecord", "ENTRY");

// create the export view form
$export->setInfo(array(array("customer_ID", "customer_description", "data_ID", "domain", "email", "serialdate", "serialnum", "ttl", "retry", "refresh", "expire", "minimum_ttl", "slave_zone", "zone_path", "second_zone_path"),
                       array($cust, $ds->getCustomerDescrip($cust), $dataid, $domain, $responsiblemail, $serialdate, $serialnum, $ttl, $retry, $refresh, $expire, $minimum, $slaveonly, $zonepath, $seczonepath)));                      
$export->createExportForm($w, $template);

printhtml($p);
?>
