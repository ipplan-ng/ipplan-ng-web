<?php

// IPplan v4.49
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
// Bcracknell 03/11/2005 - Added tree menu display of Customers/Areas/Ranges & Subnets

require_once("../ipplanlib.php");
require_once("../adodb/adodb.inc.php");
require_once("../class.dbflib.php");
require_once("../layout/class.layout");
require_once("../auth.php");
require_once '../menus/lib/PHPLIB.php';
require_once '../menus/lib/layersmenu-common.inc.php';
require_once '../menus/lib/treemenu.inc.php';

$auth = new SQLAuthenticator(REALM, REALMERROR);

// And now perform the authentication
$grps=$auth->authenticate();

// set language
isset($_COOKIE["ipplanLanguage"]) && myLanguage($_COOKIE['ipplanLanguage']);

$title=my_("Display subnet information");
newhtml($p);

$myWwwPath='../menus/';
$w=myheading($p, $title);

insert($w, generic("link",array("rel"=>"stylesheet","href"=>"$myWwwPath"."layerstreemenu.css")));
insert($w, generic("link",array("rel"=>"stylesheet","href"=>"$myWwwPath"."layerstreemenu-hidden.css")));
insert($w, script("",array("language"=>"JavaScript","type"=>"text/javascript","src"=> $myWwwPath . 'libjs/layerstreemenu-cookies.js')));

// display opening text
insert($w,heading(3, my_("Display subnets.")));

insert($w,text(my_("Click on customer/AS to display all associated subnets, click on the area to display all subnets in area and contained ranges, click on a range to display only subnets associated with that range. Subnets not within an area or range can be viewed by selecting the customer/AS."))); 

insert($w,block("<p><hr>"));

insert($w, $t=table(
array(
     "cols"		=>"1",
     "width"		=>"100%",
     "border"		=>"1",
     "cellspacing"	=>"2",
     "frame"		=>"void",
     "rules"		=>"ALL",
     "cellpadding"  =>"5")));

insert($t, $leftmenu   = cell(array("align"=>"left" ,"width"=>"100%" ,"valign"=>"top")));

//read the database and create the strings containing the menus 
$ds= new IPplanDbf() or myError($w,$p, my_("Could not connect to database"));

@set_time_limit(90);

// default is collapsed, change to "1" for expanded tree
// value is stored in a cookie so clear cookies to see effect
$expanded="";  
$displayall=FALSE;
$menustring="";
if ($custresult=$ds->GetCustomerGrp(0)){
	$adminuser=$ds->TestGrpsAdmin($grps);
	//customer
	while ($custrow=$custresult->Fetchrow()) {
      		// remove all from list if global searching is not available
      		if (!$displayall and strtolower($custrow["custdescrip"])=="all")
         		continue;

		// strip out customers user may not see due to not being member
      	// of customers admin group.
		if(!$adminuser) {
         		if(!empty($grps)) {
            		if(!in_array($custrow["admingrp"], $grps))
               			continue;
        		}
      	}
		$menustring = $menustring . ".|" . htmlspecialchars($custrow["custdescrip"]) . 
            "|displaybase.php?cust=".$custrow["customer"].
            "||||$expanded\n";

			$menustring = $menustring . "..|" . my_("All subnets not part of range") .
                "|displaybase.php?cust=" . $custrow["customer"] . 
                "&areaindex=-1||||\n";

  		$arearesult=$ds->GetArea($custrow["customer"], 0);
		//area
		while ($arearow=$arearesult->Fetchrow()) {
			$menustring = $menustring . "..|" . htmlspecialchars(inet_ntoa($arearow["areaaddr"]) . 
                " (" . $arearow["descrip"] . ")") . 
                "|displaybase.php?cust=" . $custrow["customer"] . 
                "&areaindex=" . $arearow["areaindex"] . "||||\n";

 			$rangeresult = $ds->GetRangeInArea($custrow["customer"], $arearow["areaindex"]);

			//range
			while ($rangerow=$rangeresult->Fetchrow()) {
				$menustring = $menustring . "...|" . htmlspecialchars(inet_ntoa($rangerow["rangeaddr"]).
                    " (".$rangerow["descrip"].")") . 
                    "|displaybase.php?cust=".$custrow["customer"].
                    "&areaindex=". $arearow["areaindex"].
                    "&rangeindex=".$rangerow["rangeindex"].
                    "&descrip=&sortby=Base+Address" ."||||\n";

				$baseresult = $ds->GetBase($rangerow["rangeaddr"], 
                    $rangerow["rangeaddr"]+$rangerow["rangesize"]-1, "", $custrow["customer"]);

				//subnet (base)
				while ($baserow=$baseresult->Fetchrow()) {
					$menustring = $menustring . "....|" . htmlspecialchars(inet_ntoa($baserow["baseaddr"])." /".
                        inet_bits($baserow["subnetsize"]).
                        " (". $baserow["descrip"] . ")")."|displaysubnet.php?baseindex=".
                        $baserow["baseindex"]."||||\n";
				}
			}
		}
  	}

	$mid = new TreeMenu();
	$mid->setDirroot('../menus');
	$mid->setLibjsdir('../menus/libjs/');
	$mid->setImgdir('../menus/menuimages/');
	$mid->setImgwww('../menus/menuimages/');
	$mid->setIcondir('../menus/menuicons/');
	$mid->setIconwww('../menus/menuicons/');
	if(!$menustring) myError($w,$p, my_("No customers"));
	$mid->setMenuStructureString($menustring);
	$mid->setIconsize(16, 16);
	$mid->parseStructureForMenu('treemenu1');
	$mid->newTreeMenu('treemenu1');
	insert($leftmenu, block('<br><br>'));
	insert($leftmenu, block($mid->getTreeMenu('treemenu1')));
	insert($leftmenu, block('<br><br>'));
}

printhtml($p);

?> 
