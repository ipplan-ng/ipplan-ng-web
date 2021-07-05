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

require_once("../ipplanlib.php");
require_once("../adodb/adodb.inc.php");
require_once("../class.dbflib.php");
require_once("../layout/class.layout");
require_once("../auth.php");
require_once("../class.xptlib.php");

$auth = new SQLAuthenticator(REALM, REALMERROR);

// And now perform the authentication
$grps=$auth->authenticate();

// set language
myLanguage(getUserLanguage());

//setdefault("window",array("bgcolor"=>"white"));
//setdefault("table",array("cellpadding"=>"0"));
//setdefault("text",array("size"=>"2"));

$title=my_("Display customer/autonomous system information");
newhtml($p);
$w=myheading($p, $title, true);

// explicitly cast variables as security measure against SQL injection
list($search, $expr, $block, $ipplanParanoid) = myRegister("S:search S:expr I:block I:ipplanParanoid");

// basic sequence is connect, search, interpret search
// result, close connection
$ds=new IPplanDbf() or myError($w,$p, my_("Could not connect to database"));

// what is the additional search SQL?
$sql=$ds->mySearchSql("custdescrip", $expr, $search, FALSE);
$result=$ds->GetCustomer($sql);

insert($w,heading(3, my_("All customer/autonomous system info")));

// draw the search box
$srch = new mySearch($w, array(), $search, "search");
$srch->legend=my_("Refine Search on Description");
$srch->expr=$expr;
$srch->expr_disp=TRUE;
$srch->Search();  // draw the sucker!

$totcnt=0;
$vars="";
// fastforward till first record if not first block of data
while ($block and $totcnt < $block*MAXTABLESIZE and
       $row = $result->FetchRow()) {
    $vars=DisplayBlock($w, $row, $totcnt, "&expr=$expr&search=".urlencode($search), "custdescrip");
    $totcnt++;
}

insert($w,textbr());

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
insert($c,text(my_("CRM")));
insert($t,$c = cell());
insert($c,text(my_("Group name")));
insert($t,$ck = cell());
insert($ck,text(my_("Action")));


// do this here else will do extra queries for every customer
$adminuser=$ds->TestGrpsAdmin($grps);

//capture data for the export view functionality
$export = new exportForm();
$export->addRow(array("customer_description", "CRM", "group_name"));
$export->saveRow();

$cnt=0;
while($row = $result->FetchRow()) {
setdefault("cell",array("class"=>color_flip_flop()));
   $export->addRow(NULL);
   
   // strip out customers user may not see due to not being member
   // of customers admin group. $grps array could be empty if anonymous
   // access is allowed!
   if(!$adminuser) {
      if(!empty($grps)) {
         if(!in_array($row["admingrp"], $grps))
            continue;
      }
   }

   insert($t,$c = cell());
   insert($c,text($row["custdescrip"]));
   $export->addCell($row["custdescrip"]);
   
   insert($t,$c = cell());
   insert($c,text($row["crm"]));
   $export->addCell($row["crm"]);

   insert($t,$c = cell());
   insert($c,text($row["admingrp"]));
   $export->addCell($row["admingrp"]);
   
   $export->saveRow();

   insert($t,$c = cell());
   insert($c,block("<small>"));
   insert($c,anchor("deletecustomer.php?cust=".$row["customer"], 
                         my_("Delete customer/AS"),
                         $ipplanParanoid ? array("onclick"=>"return confirm('".my_("Are you sure?")."')") : FALSE));
   insert($c,block(" | "));
   insert($c,anchor("modifycustomer.php?cust=".$row["customer"].
                         "&custdescrip=".urlencode($row["custdescrip"]).
                         "&crm=".urlencode($row["crm"]).
                         "&grp=".urlencode($row["admingrp"]), 
                         my_("Modify customer/AS details")));
   if (DHCPENABLED) {
      insert($c,block(" | "));
      insert($c,anchor("exportdhcp.php?cust=".$row["customer"],
                            my_("Export DHCP details")));
   }
   insert($c,block(" | "));
   insert($c,anchor("../admin/usermanager.php?action=groupeditform&grp=".urlencode($row["admingrp"]), 
                    my_("Group membership")));
   insert($c,block("</small>"));

    if ($totcnt % MAXTABLESIZE == MAXTABLESIZE-1)
        break;
    $cnt++;
    $totcnt++;
}

insert($w,block("<p>"));
//insert($w,textb(sprintf(my_("Total records: %u"), $cnt)));

$vars="";
$printed=0;
while ($row = $result->FetchRow()) {
    $totcnt++;
    $vars=DisplayBlock($w, $row, $totcnt, "&expr=$expr&search=".urlencode($search), "custdescrip" );
    if (!empty($vars) and !$printed) {
        insert($ck,anchor($vars, ">>"));
        $printed=1;
    }
}

$result->Close();

// create the export view form
$expression = $export->translateExpr($expr);
$export->setInfo(array(array("search_criterion", "search_expression"),
                 array($expression, $search)));
$export->createExportForm($w, $template);

printhtml($p);

?>
