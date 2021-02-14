<?php

// IPplan v4.92b
// Aug 24, 2001
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
$auth->authenticate();

// save the last customer used
// must set path else Netscape gets confused!
setcookie("ipplanCustomer","$cust",time() + 10000000, "/");

// set language
isset($_COOKIE["ipplanLanguage"]) && myLanguage($_COOKIE['ipplanLanguage']);

//setdefault("window",array("bgcolor"=>"white"));
//setdefault("table",array("cellpadding"=>"0"));
//setdefault("text",array("size"=>"2"));

$title=my_("Results of your search for areas");
newhtml($p);
$w=myheading($p, $title, true);

// explicitly cast variables as security measure against SQL injection
list($cust, $ipplanParanoid) = myRegister("I:cust I:ipplanParanoid");

if (!$_GET) {
   myError($w,$p, my_("You cannot reload or bookmark this page!"));
}

// basic sequence is connect, search, interpret search
// result, close connection
$ds=new IPplanDbf() or myError($w,$p, my_("Could not connect to database"));

$custdescrip=$ds->GetCustomerDescrip($cust);

insert($w,heading(3, sprintf(my_("Search for areas and ranges for customer '%s'"), $custdescrip)));

$result=&$ds->ds->Execute("SELECT area.areaaddr, area.descrip AS adescrip, 
                          netrange.rangeaddr,
                          netrange.rangesize, netrange.descrip AS rdescrip,
                          netrange.rangeindex, area.areaindex
                        FROM netrange
                        LEFT JOIN area
                        ON netrange.areaindex=area.areaindex
                        WHERE netrange.customer=$cust
                        ORDER BY area.areaaddr, netrange.rangeaddr, netrange.rangesize");

// create a table
insert($w,$t = table(array("cols"=>"8",
                           "class"=>"outputtable")));
// draw heading
setdefault("cell",array("class"=>"heading"));
insert($t,$c = cell());
insert($c,text(my_("Area address")));
insert($t,$c = cell());
insert($c,text(my_("Description")));
insert($t,$c = cell());
insert($c,text(my_("Action")));
insert($t,$c = cell());
insert($c,text(my_("Range address")));
insert($t,$c = cell());
insert($c,text(my_("Range size")));
insert($t,$c = cell());
insert($c,text(my_("Range mask")));
insert($t,$c = cell());
insert($c,text(my_("Description")));
insert($t,$c = cell());
insert($c,text(my_("Action")));

$export = new exportForm();
$export->addRow(array("area_addr", "area_description", "range_addr", "range_size", "range_mask", "range_description"));
$export->saveRow();

$cnt=0;
$savearea=0;
while($row=$result->FetchRow()) {
	$export->addRow(Null);
    setdefault("cell",array("class"=>color_flip_flop()));

    // first record could be NULL due to left join
    if ($savearea==$row["areaaddr"] and $cnt!=0) {
        insert($t,$c = cell());
        insert($t,$c = cell());
        insert($t,$c = cell());
        $export->addCell(inet_ntoa($row["areaaddr"]));
        $export->addCell($row["adescrip"]);
    }
    else {
        if (is_numeric($row["areaaddr"])) {
            insert($t,$c = cell());
            insert($c,anchor("displaybase.php?&cust=".$cust."&rangeindex=0".
                     "&areaindex=".$row["areaindex"]."&ipaddr=&descrip=&size=0",
                     inet_ntoa($row["areaaddr"])));
            $export->addCell(inet_ntoa($row["areaaddr"]));
                     
            insert($t,$c = cell());
            insert($c,text($row["adescrip"]));
            $export->addCell($row["adescrip"]);

            insert($t,$c = cell());
            insert($c,block("<small>"));
            insert($c,anchor("deletearea.php?areaindex=".$row["areaindex"]."&cust=".$cust, 
                        my_("Delete Area"),
                        $ipplanParanoid ? array("onclick"=>"return confirm('".my_("Are you sure?")."')") : FALSE));

            insert($c,block(" | "));

            insert($c,anchor("createarea.php?action=modify&areaindex=".$row["areaindex"].
                        "&ipaddr=".inet_ntoa($row["areaaddr"]).
                        "&cust=".$cust."&descrip=".urlencode($row["adescrip"]),
                        my_("Modify Area")));
            insert($c,block("</small>"));
        }
        else {
            insert($t,$c = cell());
            insert($c,text(my_("No area")));
            $export->addCell(my_("No area"));

            insert($t,$c = cell());
            insert($c,text(my_("Range not part of area")));
            $export->addCell(my_("Range not part of area"));
            insert($t,$c = cell());
        }
    }

    insert($t,$c = cell());
    insert($c,anchor("displaybase.php?&cust=".$cust."&areaindex=0".
                     "&rangeindex=".$row["rangeindex"]."&ipaddr=&descrip=&size=0",
                     inet_ntoa($row["rangeaddr"])));
    $export->addCell(inet_ntoa($row["rangeaddr"]));

    insert($t,$c = cell());
    insert($c,text($row["rangesize"]));
    $export->addCell($row["rangesize"]);

    insert($t,$c = cell());
    insert($c,text(inet_ntoa(inet_aton(ALLNETS)+1 -
                    $row["rangesize"])."/".inet_bits($row["rangesize"])));
    $export->addCell(inet_ntoa(inet_aton(ALLNETS)+1 - $row["rangesize"])."/".inet_bits($row["rangesize"]));

    insert($t,$c = cell());
    insert($c,text($row["rdescrip"]));
    $export->addCell($row["rdescrip"]);
    
    $export->saveRow();

    insert($t,$c = cell());
    insert($c,block("<small>"));
    insert($c,anchor("deleterange.php?rangeindex=".$row["rangeindex"]."&cust=".$cust, 
                my_("Delete Range"),
                $ipplanParanoid ? array("onclick"=>"return confirm('".my_("Are you sure?")."')") : FALSE));

    insert($c,block(" | "));

    insert($c,anchor("createrange.php?action=modify&rangeindex=".$row["rangeindex"].
                "&areaindex=".$row["areaindex"].
                "&size=".$row["rangesize"].
                "&ipaddr=".inet_ntoa($row["rangeaddr"]).
                "&cust=".$cust."&descrip=".urlencode($row["rdescrip"]),
                my_("Modify Range")));
    insert($c,block("</small>"));

    $savearea=$row["areaaddr"];
    $cnt++;
}
insert($w,block("<p>"));
insert($w,textb(sprintf(my_("Total records: %u"), $cnt)));
$temp1 = $cnt;

$result=&$ds->ds->Execute("SELECT area.areaaddr,
                             area.descrip AS adescrip, area.areaindex
                           FROM area
                           LEFT JOIN netrange
                           ON area.areaindex=netrange.areaindex
                           WHERE area.customer=$cust AND
                               netrange.areaindex IS NULL
                           ORDER BY area.areaaddr");

insert($w,heading(3, my_("Areas that have no ranges defined")));

setdefault("cell", FALSE);
// create a table
insert($w,$t = table(array("cols"=>"3",
                           "class"=>"outputtable")));
// draw heading
setdefault("cell",array("class"=>"heading"));
insert($t,$c = cell());
insert($c,text(my_("Area address")));
insert($t,$c = cell());
insert($c,text(my_("Description")));
insert($t,$c = cell());
insert($c,text(my_("Action")));

$cnt=0;
while($row=$result->FetchRow()) {
	$export->addRow(NULL);
	
    setdefault("cell",array("class"=>color_flip_flop()));

    insert($t,$c = cell());
    // no point in making this a hyperlink - there are no ranges so search will
    // always return nothing!
    insert($c,text(inet_ntoa($row["areaaddr"])));
    $export->addCell(inet_ntoa($row["areaaddr"]));
    
    insert($t,$c = cell());
    insert($c,text($row["adescrip"]));
    $export->addCell($row["adescrip"]);
    $export->addCell(my_("No range"));
    $export->addCell("");
    $export->addCell("");
    $export->addCell(my_("No range in this area"));
    $export->saveRow();
    
    insert($t,$c = cell());
    insert($c,block("<small>"));
    insert($c,anchor("deletearea.php?areaindex=".$row["areaindex"]."&cust=".$cust,
                my_("Delete Area"),
                $ipplanParanoid ? array("onclick"=>"return confirm('".my_("Are you sure?")."')") : FALSE));
    insert($c,block(" | "));

    insert($c,anchor("createarea.php?action=modify&areaindex=".$row["areaindex"].
                "&ipaddr=".inet_ntoa($row["areaaddr"]).
                "&cust=".$cust."&descrip=".urlencode($row["adescrip"]),
                my_("Modify Area")));
    insert($c,block("</small>"));

    $cnt++;

}
insert($w,block("<p>"));
insert($w,textb(sprintf(my_("Total records: %u"), $cnt)));
$temp2 = $cnt;

$result->Close();

// create the export view form
$export->setInfo(array(array("customer_ID", "customer_description", "total_ranges_and_areas_with_ranges", "total_areas_without_ranges"),
                       array($cust, $ds->getCustomerDescrip($cust), $temp1, $temp2)));
$export->createExportForm($w, NULL);

printhtml($p);

?>
