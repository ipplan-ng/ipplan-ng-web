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

$auth = new SQLAuthenticator(REALM, REALMERROR);

// And now perform the authentication
$grps=$auth->authenticate();

// set language
isset($_COOKIE["ipplanLanguage"]) && myLanguage($_COOKIE['ipplanLanguage']);

//setdefault("window",array("bgcolor"=>"white"));
//setdefault("table",array("cellpadding"=>"0"));
//setdefault("text",array("size"=>"2"));

$title=my_("Display registrar information");
newhtml($p);

insert($p, $h = wheader("IPPlan - $title"));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../cookies.js")));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../phpserializer.js")));
insert($h, script("", array("type"=>"text/javascript", "src"=>"../ipplanlib.js")));

$w=myheading($p, $title, true);

// explicitly cast variables as security measure against SQL injection
list($cust, $areaindex) = myRegister("I:cust I:areaindex");

// display opening text
insert($w,heading(3, my_("Display subnets.")));

$ds=new IPplanDbf() or myError($w,$p, my_("Could not connect to database"));

// start form
insert($w, $f1 = form(array("name"=>"THISFORM",
                            "method"=>"post",
                            "action"=>$_SERVER["PHP_SELF"])));

$cust=myCustomerDropDown($ds, $f1, $cust, $grps) or myError($w,$p, my_("No customers"));
$areaindex=myAreaDropDown($ds, $f1, $cust, $areaindex);

insert($w, $f2 = form(array("name"=>"ENTRY",
                            "method"=>"get",
                            "action"=>"displayswip.php")));

// save customer name for actual post of data
insert($f2,hidden(array("name"=>"cust",
                        "value"=>"$cust")));
insert($f2,hidden(array("name"=>"areaindex",
                        "value"=>"$areaindex")));

myRangeDropDown($ds, $f2, $cust, $areaindex);
insert($f2, block("<p>"));

insert($f2, $con=container("fieldset",array("class"=>"fieldset")));
insert($con, $legend=container("legend",array("class"=>"legend")));
insert($legend, text(my_("Search criteria")));

insert($con,textbr(my_("Subnet network address (leave blank if range selected)")));
insert($con,span(my_("Partial subnet addresses can be used eg. 172.20"), array("class"=>"textSmall")));
insert($con,input_text(array("name"=>"ipaddr",
                            "size"=>"15",
                            "maxlength"=>"15")));

if (DBF_TYPE=="mysql" or DBF_TYPE=="maxsql" or DBF_TYPE=="postgres7")
   insert($con,textbrbr(my_("Description (only display networks matching the regular expression)")));
else
   insert($con,textbrbr(my_("Description (only display networks containing)")));
insert($con,input_text(array("name"=>"descrip",
                            "size"=>"80",
                            "maxlength"=>"80")));

// get filenames with xsl extension from templates directory
$files=array();
if ($dir = @opendir("../templates")) {
   while($filename = readdir($dir)) {
      if (preg_match("/\.xsl$/", $filename))
         $files["$filename"]=$filename;
   }  
   closedir($dir);
}
insert($con,textbrbr(my_("Template")));
insert($con,selectbox($files,
                        array("name"=>"filename")));

insert($con,textbrbr(my_("Network name (ntname field on registrar template)")));
insert($con,radio(array("name"=>"ntnameopt",
                         "value"=>"0"),
                         my_("Generate automatically"), "checked"));
insert($con,radio(array("name"=>"ntnameopt",
                         "value"=>"1"),
                         my_("Use subnet description")));

insert($con,generic("br"));
insert($f2,submit(array("value"=>my_("Submit"))));
insert($f2,freset(array("value"=>my_("Clear"))));
myCopyPaste($f2, "ipplanCPswipform", "ENTRY");

printhtml($p);

?>
