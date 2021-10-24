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
require_once("../layout/class.layout");
require_once("../auth.php");

$auth = new SQLAuthenticator(REALM, REALMERROR);

// And now perform the authentication
$grps=$auth->authenticate();

// set language
myLanguage(getUserLanguage());

//setdefault("window",array("bgcolor"=>"white"));
//setdefault("table",array("cellpadding"=>"0"));
//setdefault("text",array("size"=>"2"));

$title=my_("Display/Modify/Delete area information");
newhtml($p);
$w=myheading($p, $title, true);

// explicitly cast variables as security measure against SQL injection
list($ipplanCustomer) = myRegister("I:ipplanCustomer");

// display opening text
insert($w,heading(3, my_("Display/Modify/Delete areas and ranges.")));

$ds=new IPplan_NG\DBLib() or myError($w,$p, my_("Could not connect to database"));

// start form
insert($w, $f2 = form(array("name"=>"ENTRY",
                            "method"=>"get",
                            "action"=>"modifyarearange.php")));

// ugly kludge with global variable!
$cust=floor($ipplanCustomer);
$displayall=TRUE;
$cust=myCustomerDropDown($ds, $f2, $cust, $grps, FALSE) or myError($w,$p, my_("No customers"));

insert($f2,generic("br"));
insert($f2,submit(array("value"=>my_("Submit"))));
insert($f2,freset(array("value"=>my_("Clear"))));

printhtml($p);

?>
