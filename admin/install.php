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

if (PHP_VERSION_ID  < 70400) {
   die("You need php version 7.4.0 or later");
}

require_once("../config.php");
require_once('../classes/Version.php');
//require_once("../schema.php");
require_once("../ipplanlib.php");
require_once("../layout/class.layout");

// check for latest variable added to config.php file, if not there
// user did not upgrade properly
if (!defined('CONFIG_DIR')) die("Your config.php file is inconsistent - you cannot copy over your old config.php file during upgrade");

// set language
myLanguage(getUserLanguage());


newhtml($p);
insert($p,block("<script type=\"text/javascript\">
</script>
<noscript>
<p><b>
<font size=4 color=\"#FF0000\">
Your browser must be JavaScript capable to use this application. Please turn JavaScript on.
</font>
</b>
</noscript>
"));

$w=myheading($p, my_("Install/Upgrade IPplan-NG"), false);
insert($w, $t=container("div"));

insert($t, heading(3, my_('IPplan-NG '.IPplan_NG\Version::VERSION_NAME.' Installation System')));

// BEGIN INSTALLER LANGUAGE SUPPORT
if(extension_loaded("gettext") and LANGCHOICE) {

    if ($_POST) {

        // set language cookie if language changed by user
        // language includes path of ipplan root seperated by :
        if (isValidLanguage($lang)) {
            setcookie('IPplanNG_Language',$lang,time() + 10000000, '/');
            $_COOKIE['IPplanNG_Language']=$lang;
        }
    }

    myLanguage(getUserLanguage());

    insert($w, $con=container("fieldset",array("class"=>"fieldset")));
    insert($con, $legend=container("legend",array("class"=>"legend")));
    insert($legend, text(my_("Language")));
    insert($con,  $f=form(array("method"=>"post","action"=>$_SERVER["PHP_SELF"])));
    insert($f,  textbr(my_("Please choose your language:")));

    $current_lang=null;

    $user_lang=getUserLanguage();

        if (isValidLanguage($user_lang)) {
        $current_lang=$user_lang;
        }

    insert($f,selectbox($iso_codes,array('name' => 'lang'),$current_lang));
    insert($f,submit(array("value"=>my_("  Change  "))));
    insert($w,generic("br"));
    insert($w,generic("br"));

}
// END INSTALLER LANGUAGE SUPPORT

insert($w, $r=container("fieldset",array("class"=>"fieldset")));
insert($r, $q=container("div",array("class"=>"textErrorBig")));
insert($q,textbr(my_("IF YOU ARE UPGRADING IPPLAN, BACKUP YOUR DATABASE NOW")));
insert($q,textbr(my_("THERE IS NO WAY TO RECOVER YOUR DATA IF SOMETHING GOES WRONG.")));

insert($q,generic("p"));
insert($q,textbr(my_("THE DISPLAY TEMPLATES HAVE MOVED TO A DIFFERENT DIRECTORY - READ THE CHANGELOG AND UPGRADE DOC")));

insert($w, $t=container("div", array("class"=>"MrMagooInstall")));
insert($t, $s=container("ul"));

insert($s, $l1=container("li"));
insert($l1,textb(my_("For security purposes, it is highly recomended that IPPlan is installed on an SSL Webserver.")));
insert($s, generic("br"));
insert($s, $l2=container("li"));
insert($l2,textb(my_("Production systems need to use a transaction-aware database table. Do not use MYISAM (use INNODB) and enable it in config.php")));
insert($s, generic("br"));
insert($s, $l3=container("li"));
insert($l3,textb(my_("Read all Instructions carefully before proceeding!")));

insert($w, generic("br"));
insert($w,block(my_("Have you read the <a href=\"http://iptrack.sourceforge.net/doku.php?id=faq\">FAQ</a>? How about the <a href=\"http://iptrack.sourceforge.net/documentation/\">User Manual</a>? ")));
insert($w,text(my_("Have you read the UPGRADE document if upgrading?")));
insert($w, generic("br"));
insert($w, generic("br"));
insert($w,textbrbr(my_("What would you like to do today?")));

insert($w, $f = form(array("name"=>"THISFORM","method"=>"POST","action"=>"schemacreate.php")));
insert($f,selectbox(array("0"=>"Upgrade","1"=>"New Installation"),array("name"=>"new")));
insert($f, generic("br"));
insert($f, generic("br"));

insert($f,textbr(my_("Would you like us to run the SQL against the database defined in config.php or would you rather print it to the screen so you can do it yourself?")));
 
insert($f,selectbox(array("0"=>my_("Run the SQL Now"),
                          "1"=>my_("Just print it to the screen")),
                          array("name"=>my_("display"))));
insert($f, generic("br"));

insert($f,textbr(my_("If you are displaying the schema, please remove the comments with a text editor before executing into your database.")));
insert($f,generic("br"));
insert($f,submit(array("value"=>my_("Go!"))));

printhtml($p);
?>
