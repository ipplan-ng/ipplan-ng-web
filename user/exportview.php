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
require_once("../class.templib.php");
require_once("../class.xptlib.php");

// script to create the file and serve it up as download
// $info = unserialize(urldecode($info));

// URL-passed null arguments get turned into ""; 
// convert $records back to null if it was previously
// this is so that isset() works later
// if ($nullrec == "TRUE") $records = NULL;
// else $records = unserialize(urldecode($records));
session_start();

if ($_SESSION['info'] != NULL) 
    $info = $_SESSION['info'];
else {
    newhtml($p);
    insert($p, block("Session variable info is null. Something unexpected happened."));
    printhtml($p);
    exit;
}
$records = $_SESSION['records'];

session_destroy();

$tempfile = exportForm::createFile($info, $records, $ftype, $page);
exportForm::serveFile($tempfile, $page, $ftype);
?>
