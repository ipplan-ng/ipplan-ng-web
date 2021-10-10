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
require_once dirname(__FILE__).'/../classes/DBLib.php';
require_once("../layout/class.layout");
require_once("../auth.php");

require_once("../class.templib.php");

$auth = new SQLAuthenticator(REALM, REALMERROR);

// And now perform the authentication
$grps=$auth->authenticate();

// set language
myLanguage(getUserLanguage());

//setdefault("window",array("bgcolor"=>"white"));
//setdefault("table",array("cellpadding"=>"0"));
//setdefault("text",array("size"=>"2"));

$title=my_("Search for user info");
newhtml($p);

insert($p, $h = wheader("IPPlan - $title"));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../cookies.js")));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../phpserializer.js")));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../ipplanlib.js")));

$w=myheading($p, $title, true);

// explicitly cast variables as security measure against SQL injection
list($cust, $areaindex, $field, $tmplfield, $rangeindex) = myRegister("I:cust I:areaindex S:field S:tmplfield I:rangeindex");

// display opening text
insert($w,heading(3, "$title."));

insert($w,textbrbr(my_("Search for user info searches the individual IP address records.")));

$ds=new IPplan_NG\DBLib() or myError($w,$p, my_("Could not connect to database"));

// start form
insert($w, $f1 = form(array("name"=>"THISFORM",
                           "method"=>"post",
                           "action"=>$_SERVER["PHP_SELF"])));

// ugly kludge with global variable!
$displayall=TRUE;
$cust=myCustomerDropDown($ds, $f1, $cust, $grps) or myError($w,$p, my_("No customers"));
$areaindex=myAreaDropDown($ds, $f1, $cust, $areaindex);
$rangeindex=searchRangeDropDown($ds, $f1, $cust, $areaindex, $rangeindex);
//$rangeindex=myRangeDropDown($ds, $f1, $cust, $areaindex);
insert($f1, block("<p>"));

insert($f1, $con2=container("fieldset",array("class"=>"fieldset")));
insert($con2, $legend=container("legend",array("class"=>"legend")));
insert($legend, text(my_("Search field")));

insert($con2,text(my_("Field to search")));
if (empty($field)) $field="userinf";
$lst=array("userinf"=>my_("User"),
        "location"=>my_("Location"),
        "descrip"=>my_("Description"),
        "hname"=>my_("Host Name"),
        "telno"=>my_("Telephone Number"),
        "macaddr"=>my_("MAC Address"),
        "template"=>my_("Search in Template"));
insert($con2,selectbox($lst,
            array("name"=>"field","onChange"=>"submit()"),
            $field));

$template=new IPplanIPTemplate("iptemplate", $cust);
if ($template->is_error() == FALSE) {
    // The function retruns part of the template definietions
    $tmpldef=$template->return_templ_name();
    if ($field == "template") {
        // Search for specific template fields only with regex support
        if (DBF_TYPE=="mysqli" or DBF_TYPE=="postgres9") {
            if (empty($tmplfield)) {
                $tmplfield="any";
            }
            insert($con2,selectbox($tmpldef,
                        array("name"=>"tmplfield","onChange"=>"submit()"),
                        $tmplfield));
        }
    }
}

insert($w, $f2 = form(array("name"=>"ENTRY",
                            "method"=>"get",
                            "action"=>"searchall.php")));

// save customer name for actual post of data
insert($f2,hidden(array("name"=>"cust",
                        "value"=>"$cust")));
insert($f2,hidden(array("name"=>"areaindex",
                        "value"=>"$areaindex")));
insert($f2,hidden(array("name"=>"field",
                        "value"=>"$field")));
insert($f2,hidden(array("name"=>"rangeindex",
                        "value"=>"$rangeindex")));
insert($f2,hidden(array("name"=>"tmplfield",
                        "value"=>"$tmplfield")));
//$rangeindex=myRangeDropDown($ds, $f2, $cust, $areaindex);

insert($f2, block("<p>"));

insert($f2, $con=container("fieldset",array("class"=>"fieldset")));
insert($con, $legend=container("legend",array("class"=>"legend")));
insert($legend, text(my_("Search criteria")));

//insert($con,textbr(my_("Field to search")));

//$lst=array("userinf"=>my_("User"),
//           "location"=>my_("Location"),
//           "descrip"=>my_("Description"),
//           "hname"=>my_("Host Name"),
//           "telno"=>my_("Telephone Number"),
//           "template"=>my_("Search in Template"));

//insert($con,selectbox($lst,
//                 array("name"=>"field")));

//myFieldToSearch($con, $fieldtosearch);
//insert($con,hidden(array("name"=>"fieldtosearch",
//                         "value"=>"$fieldtosearch")));

insert($con,text(my_("Date to search from")));
insert($con,text(my_("Day")));
insert($con,selectbox(array("0"=>my_("Any"),
                           "1"=>"1",
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
                           "31"=>"31"),
                 array("name"=>"day")));

insert($con,text(my_("Month")));
insert($con,selectbox(array("0"=>my_("Any"),
                           "1"=>my_("January"),
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
                           "12"=>my_("December")),
                 array("name"=>"month")));

insert($con,text(my_("Year")));
$years=array("0"=>my_("Any"));
$i = 1990;
while ($i < date('Y')+15) $years[$i++] = $i;

insert($con,selectbox($years, array("name"=>"year")));

if (DBF_TYPE=="mysqli" or DBF_TYPE=="postgres9")
   insert($con,textbrbr(my_("Search criteria (only display records matching the regular expression)")));
else
   insert($con,textbrbr(my_("Search criteria (only display records containing)")));
insert($con,input_text(array("name"=>"search",
                           "size"=>"80",
                           "maxlength"=>"80")));

insert($con,generic("br"));
insert($f2,submit(array("value"=>my_("Submit"))));
insert($f2,freset(array("value"=>my_("Clear"))));
myCopyPaste($f2, "ipplanCPsearchallform", "ENTRY");

printhtml($p);


// displays range drop down box - requires a working form
function searchRangeDropDown($ds, $f2, $cust, $areaindex, $rangeindex=0) {

    $cust=floor($cust);   // dont trust $cust as it could 
    // come from form post
    $areaindex=floor($areaindex);

    // display range drop down list
    if ($areaindex)
        $result=$ds->GetRangeInArea($cust, $areaindex);
    else
        $result=$ds->GetRange($cust, 0);

    // don't bother if there are no records, will always display "No range"
    insert($f2,textbrbr(my_("Range (optional)")));
    $lst=array();
    $lst["0"]=my_("No range selected");
    while($row = $result->FetchRow()) {
        $col=$row["rangeindex"];
        $lst["$col"]=inet_ntoa($row["rangeaddr"])."/".inet_ntoa(inet_aton(ALLNETS)-$row["rangesize"]+1).
            "/".inet_bits($row["rangesize"])." - ".$row["descrip"];
    }

    insert($f2,selectbox($lst,
                array("name"=>"rangeindex","onChange"=>"submit()"),
                $rangeindex));
    return $rangeindex;

}

?>
