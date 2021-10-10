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

require_once("../config.php");
require_once("../ipplanlib.php");
require_once dirname(__FILE__).'/../classes/DBLib.php';
require_once("../layout/class.layout");
require_once("../auth.php");
require_once("../class.xptlib.php");

$auth = new BasicAuthenticator(ADMINREALM, REALMERROR);

$auth->addUser(ADMINUSER, ADMINPASSWD);

// And now perform the authentication
$grps=$auth->authenticate();

// set language
myLanguage(getUserLanguage());

//setdefault("window",array("bgcolor"=>"white"));
//setdefault("table",array("cellpadding"=>"0"));
//setdefault("text",array("size"=>"2"));

$title=my_("IPplan Maintenance");
newhtml($p);
$w=myheading($p, $title);

// explicitly cast variables as security measure against SQL injection
list($action, $cust, $block, $requestindex, $requestdesc) = myRegister("S:action I:cust I:block I:requestindex S:requestdesc");

$ds=new IPplan_NG\DBLib() or myError($w,$p, my_("Could not connect to database"));

if ($action=="deletecustomer") {
    if (DBF_TYPE=="mysqli") {
        $version=$ds->ds->GetOne("SELECT version() AS version");

        if ($version < "4.0.0") {
            myError($w,$p, my_("You need MySQL v4.0.0 or higher for this function!"));
        }
    }

    $ds->DbfTransactionStart();
    $result=$ds->ds->Execute("DELETE FROM customer
            WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM custinfo
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM ipaddr 
                WHERE baseindex IN (SELECT baseindex FROM base WHERE customer=$cust)") and
        $result=$ds->ds->Execute("DELETE FROM ipaddradd
                WHERE baseindex IN (SELECT baseindex FROM base WHERE customer=$cust)") and
        $result=$ds->ds->Execute("DELETE FROM base
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM custadd
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM revdns
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM area
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM netrange
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM fwdzone
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM fwdzoneadd
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM fwdzonerec
                WHERE customer=$cust") and
        $result=$ds->ds->Execute("DELETE FROM zones
                WHERE customer=$cust") and
        $ds->AuditLog(array("event"=>182, "action"=>"delete customer", 
                    "user"=>getAuthUsername(), "cust"=>$cust));

    if ($result) {
        $ds->DbfTransactionEnd();
        insert($w,text(my_("Customer deleted")));
    }
    else {
        insert($w,text(my_("Customer could not be deleted")));
    }
}

if ($action=="deleterequest") {
    $ds->DbfTransactionStart();
    $result=$ds->ds->Execute("DELETE FROM requestip");

    $ds->AuditLog(my_("Requested IP addresses cleared"));

    if ($result) {
        $ds->DbfTransactionEnd();
        insert($w,text(my_("Requested IP addresses cleared!")));
    }
    else {
        insert($w,text(my_("Requested IP addresses could not be cleared.")));
    }
}

// delete one requested ip address
if ($action=="deleterequestidx") {
    $ds->DbfTransactionStart();
    $result=$ds->ds->Execute("DELETE FROM requestip 
                               WHERE requestindex=$requestindex");

    $ds->AuditLog(my_("Requested IP address deleted").": ".$requestdesc);

    if ($result) {
        $ds->DbfTransactionEnd();
        insert($w,textbr(my_("Requested IP address deleted!")));
    }
    else {
        insert($w,textbr(my_("Requested IP address could not be deleted.")));
    }

    $action="reqindex";
}

if ($action=="deleteaudit") {
    $ds->DbfTransactionStart();
    $result=$ds->ds->Execute("DELETE FROM auditlog");

    $ds->AuditLog(my_("Audit log cleared"));

    if ($result) {
        $ds->DbfTransactionEnd();
        insert($w,text(my_("Audit log cleared!")));
    }
    else {
        insert($w,text(my_("Audit log could not be cleared.")));
    }
}

if ($action=="custindex") {
    $result=$ds->GetCustomer();

    $totcnt=0;
    $vars="";
    // fastforward till first record if not first block of data
    while ($block and $totcnt < $block*MAXTABLESIZE and
            $row = $result->FetchRow()) {
        $vars=DisplayBlock($w, $row, $totcnt, "&action=custindex", "custdescrip");
        $totcnt++;
    }

    // create a table
    insert($w,$t = table(array("cols"=>"4",
                    "class"=>"outputtable")));
    // draw heading
    setdefault("cell",array("class"=>"heading"));
    insert($t,$c = cell());
    if (!empty($vars))
        insert($c,anchor($vars, "<<"));
    insert($c,text(my_("Customer description")));
    insert($t,$c = cell());
    insert($c,text(my_("Group name")));
    insert($t,$c = cell());
    insert($c,text(my_("Customer index")));
    insert($t,$ck = cell());
    insert($ck,text(my_("Action")));

    //capture data for the export view functionality
    $export = new exportForm();
    $export->addRow(array("customer_description", "group_name", "customer_ID"));
    $export->saveRow();
    
    $cnt=0;
    while($row = $result->FetchRow()) {
        if (strtolower($row["custdescrip"]) == "all") {
            continue;
        }
        $export->addRow(NULL);
        
        setdefault("cell",array("class"=>color_flip_flop()));

        insert($t,$c = cell());
        insert($c,text($row["custdescrip"]));
        $export->addCell($row["custdescrip"]);
        
        insert($t,$c = cell());
        insert($c,text($row["admingrp"]));
        $export->addCell($row["admingrp"]);

        insert($t,$c = cell());
        insert($c,text($row["customer"]));
        $export->addCell($row["customer"]);
        $export->saveRow();

        insert($t,$c = cell());
        insert($c,block("<small>"));
        insert($c,anchor($_SERVER["PHP_SELF"]."?action=deletecustomer&cust=".$row["customer"], 
                    my_("Delete customer/AS"),
                    array("onclick"=>"return confirm('".my_("Are you sure? THIS ACTION WILL DELETE ALL TRACES OF THE CUSTOMER INCLUDING ALL SUBNETS, AREAS, RANGES AND DNS ZONES!")."')")));
        insert($c,block("</small>"));

        if ($totcnt % MAXTABLESIZE == MAXTABLESIZE-1)
            break;
        $cnt++;
        $totcnt++;

    }

    insert($w,block("<p>"));

    $vars="";
    $printed=0;
    while ($row = $result->FetchRow()) {
        $totcnt++;
        $vars=DisplayBlock($w, $row, $totcnt, "&action=custindex", "custdescrip" );
        if (!empty($vars) and !$printed) {
            insert($ck,anchor($vars, ">>"));
            $printed=1;
        }
    }
    
   // create the export view form
   $export->setInfo(array(array("maintenance_page"),
                          array("customer index")));
   $export->createExportForm($w, NULL);
}

if ($action=="reqindex") {
    $result=$ds->ds->Execute("SELECT requestip.requestindex, requestip.requestdesc, 
                requestip.lastmod, requestip.userinf, requestip.descrip, customer.custdescrip
            FROM requestip, customer
            WHERE customer.customer=requestip.customer
            ORDER BY customer.custdescrip");
            
    $totcnt=0;
    $vars="";
    // fastforward till first record if not first block of data
    while ($block and $totcnt < $block*MAXTABLESIZE and
            $row = $result->FetchRow()) {
        $vars=DisplayBlock($w, $row, $totcnt, "&action=reqindex", "requestdesc");
        $totcnt++;
    }

    // create a table
    insert($w,$t = table(array("cols"=>"6",
                    "class"=>"outputtable")));
    // draw heading
    setdefault("cell",array("class"=>"heading"));
    insert($t,$c = cell());
    if (!empty($vars))
        insert($c,anchor($vars, "<<"));
    insert($c,text(my_("Customer description")));
    insert($t,$c = cell());
    insert($c,text(my_("Request description")));
    insert($t,$c = cell());
    insert($c,text(my_("User")));
    insert($t,$c = cell());
    insert($c,text(my_("User description")));
    insert($t,$c = cell());
    insert($c,text(my_("Request date")));
    insert($t,$ck = cell());
    insert($ck,text(my_("Action")));

    //capture data for the export view functionality
    $export = new exportForm();
    $export->addRow(array("customer_description", "request_description", "user", "user_description", "request_date"));
    $export->saveRow();
    
    $cnt=0;
    while($row = $result->FetchRow()) {
	    $export->addRow(NULL);
	    
        setdefault("cell",array("class"=>color_flip_flop()));

        insert($t,$c = cell());
        insert($c,text($row["custdescrip"]));
        $export->addCell($row["custdescrip"]);
        
        insert($t,$c = cell());
        insert($c,text($row["requestdesc"]));
        $export->addCell($row["requestdesc"]);

        insert($t,$c = cell());
        insert($c,text($row["userinf"]));
        $export->addCell($row["userinf"]);

        insert($t,$c = cell());
        insert($c,text($row["descrip"]));
        $export->addCell($row["descrip"]);

        insert($t,$c = cell());
        insert($c,block($result->UserTimeStamp($row["lastmod"], "M d Y H:i:s")));
        $export->addCell($row["lastmod"]);
        $export->saveRow();

        insert($t,$c = cell());
        insert($c,block("<small>"));
        insert($c,anchor($_SERVER["PHP_SELF"]."?action=deleterequestidx&block=$block&requestindex=".$row["requestindex"]."&requestdesc=".urlencode($row["requestdesc"]), 
                    my_("Delete request")));
        insert($c,block("</small>"));

        if ($totcnt % MAXTABLESIZE == MAXTABLESIZE-1)
            break;
        $cnt++;
        $totcnt++;

    }

    insert($w,block("<p>"));

    $vars="";
    $printed=0;
    while ($row = $result->FetchRow()) {
        $totcnt++;
        $vars=DisplayBlock($w, $row, $totcnt, "&action=reqindex", "requestdesc" );
        if (!empty($vars) and !$printed) {
            insert($ck,anchor($vars, ">>"));
            $printed=1;
        }
    }
    
   //create the export view form
   $export->setInfo(array(array("maintenance_page"),
                          array("request index")));
   $export->createExportForm($w, NULL);
}


// display opening text
insert($w,heading(3, "$title."));

insert($w,textbr(my_("Perform the selected IPplan database maintenance.")));

// start form
insert($w, $f = form(array("method"=>"post",
                "action"=>$_SERVER["PHP_SELF"])));

insert($f,hidden(array("name"=>"action",
                "value"=>"custindex")));

insert($f,generic("p"));
insert($f,submit(array("value"=>my_("Display list of customer indexes"))));
 
// start form
insert($w, $f = form(array("method"=>"post",
                "action"=>$_SERVER["PHP_SELF"])));

insert($f,hidden(array("name"=>"action",
                "value"=>"reqindex")));

insert($f,generic("p"));
insert($f,submit(array("value"=>my_("View request list"))));

// start form
insert($w, $f = form(array("method"=>"post",
                "action"=>$_SERVER["PHP_SELF"])));

insert($f,hidden(array("name"=>"action",
                "value"=>"deleterequest")));

insert($f,submit(array("value"=>my_("Clear IP address request list"))));
 
// start form
insert($w, $f = form(array("method"=>"post",
                "action"=>$_SERVER["PHP_SELF"])));

insert($f,hidden(array("name"=>"action",
                "value"=>"deleteaudit")));

insert($f,generic("p"));
insert($f,submit(array("value"=>my_("Clear Audit Log"))));
 
printhtml($p);

?>
