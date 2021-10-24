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
// workaround for funky behaviour of nested includes with older
// versions of php

require_once(dirname(__FILE__)."/config.php");


// used for the data export functionality
// its functions are used by php files that display records
// and ipplan/user/exportview.php
class exportForm {	
   var $xprtRecords;
   var $rowArray;
   var $xprtInfo;
   	
   function exportForm(){
	   if (EXPORTENABLED == TRUE)
	      session_start();
	   $this->xprtRecords = NULL;
	   $this->rowArray = NULL;
	   $this->xprtInfo = NULL;
   }
 
   // set the xprtInfo array
   function setInfo($array){
	   $this->xprtInfo = $array;
   }
   
   // add row to the xprtrecords array
   function addRow($array){
	   if ($array == NULL) $this->rowArray = array();
	   else $this->rowArray = $array;
   }
   
   // add a cell to a row
   function addCell($val){
	   array_push($this->rowArray, $val);
   }
   
   // save a row
   function saveRow(){
	   if ($this->xprtRecords == NULL) $this->xprtRecords = array();
	   array_push($this->xprtRecords, $this->rowArray);   
   }
   
   // make search criterion more readable if page includes one
   function translateExpr($expr){
	   if ($expr == "START") $expression = "starts with";
       elseif ($expr == "END") $expression = "ends with";
       elseif ($expr == "LIKE") $expression = "contains";
       elseif ($expr == "NLIKE") $expression = "does not contain";
       elseif ($expr == "EXACT") $expression = "is";
       elseif ($expr == "RLIKE") $expression = "contains regular expression";
       elseif ($expr == "NRLIKE") $expression = "does not contain regular expression";      
       else $expression = "";
       
       return $expression;
   }
   
   // initialize the class. pass in info and records arrays of variables from the page; 
   // $w holds the block where the export form will be inserted. creates the "export view" form
   function createExportForm($w, $template=NULL) {
	   if (EXPORTENABLED == FALSE) return;
	   
	   $page = $_SERVER['SCRIPT_NAME'];                              
                         
       //template handling
       if ($template != NULL){
          $userfld = $template->return_userfld();
          foreach ($userfld as $field=>$val) {
	         array_push($this->xprtInfo[0], $field);
             array_push($this->xprtInfo[1], isset($userfld["$field"]["value"]) ? $userfld["$field"]["value"] : $userfld["$field"]["default"]);
          }                         
       }

       $_SESSION['info'] = $this->xprtInfo;
       $_SESSION['records'] = $this->xprtRecords;
       
	   insert($w, $f = form(array("name"=>"EXPORT",
                           "method"=>"post",
                           "action"=>"../user/exportview.php"))); 	                                            

       insert($f,hidden(array("name"=>"page", "value"=>"$page")));
       // what other formats should we have?
       $formatoptions = array("plain"=>my_("Plain text"),
                              "csv"=>my_("Comma delimited"),
                              "tab"=>my_("Tab delimited"),
                              "xml"=>my_("XML"));                                                      
       insert($f,  selectbox($formatoptions, array("name"=>"ftype")));

       insert($f,submit(array("value"=>my_("Export view"), "onClick"=>"javascript:window.location.reload()")));
   }
   
   // writes data to the file in CSV format
   function writeCSV($info, $records, $page) {
	  $tempfile="$page\n\n";
	  
	  $first = 0;
      foreach ($info as $info1) {
         foreach ($info1 as $info2) {
	        if ($first != 0) $tempfile.=", ";
            $data = "$info2";
            $tempfile.=$data;
            $first = 1;
         }
         $tempfile.="\n";
         $first =  0;
      }
      
      if (isset($records)) {
         $tempfile.="\n";
      
         foreach ($records as $records1) {
            foreach ($records1 as $records2) {
	           if ($first != 0) $tempfile.=", ";
               $data = "$records2";
               $tempfile.=$data;
               $first = 1;
            }
            $tempfile.="\n";
            $first =  0;
         }
      }
      return $tempfile;
   }
   
   function writePlain($info, $records, $page) {
	  $tempfile="$page\n\n";
	  
	  $temparray1 = $info[0];
	  unset($info[0]);
	  $count = 0;
	  
      foreach ($info as $info1) {
         foreach ($info1 as $info2) {
	        $data = "$temparray1[$count]: $info2\n";
	        $tempfile.=$data;
            $count = $count + 1;
         }
         $tempfile.="\n";
         $count = 0;
      }
      
      if (isset($records)) {
	     $temparray2 = $records[0];
	     unset($records[0]);
	     
         foreach ($records as $records1) {
            foreach ($records1 as $records2) {
	           $data = "$temparray2[$count]: $records2\n";
	           $tempfile.=$data;
               $count = $count + 1;
            }
            $tempfile.="\n";
            $count = 0;
         }
      }
      return $tempfile;
   }
   
   function writeTab($info, $records, $page) {
	  $tempfile="$page\n\n";
	  
	  $first = 0;
      foreach ($info as $info1) {
         foreach ($info1 as $info2) {
	        if ($first != 0) $tempfile.="\t";
            $data = "$info2";
            $tempfile.=$data;
            $first = 1;
         }
         $tempfile.="\n";
         $first =  0;
      }
      if (isset($records)) {
         $tempfile.="\n";
         foreach ($records as $records1) {
            foreach ($records1 as $records2) {
	           if ($first != 0) $tempfile.="\t";
               $data = "$records2";
               $tempfile.=$data;
               $first = 1;
            }
            $tempfile.="\n";
            $first =  0;
         }
      }
      return $tempfile;
   }
   
   function writeXML($info, $records, $page) {
	  $page = str_replace("/", "_", $page); 
	  
	  $tempfile="<?xml version='1.0'?>\n";	  
	  $tempfile.="<$page>\n";
	  
	  $temparray1 = $info[0];
	  unset($info[0]);
	  $count = 0;  
	  
      foreach ($info as $info1) {
	     $tempfile.="<info>\n";
         foreach ($info1 as $info2) {
	        $data = "<".$temparray1[$count].">".$info2."</".$temparray1[$count].">\n";
	        $tempfile.=$data;
            $count = $count + 1;
         }
         $tempfile.="</info>\n";
         $count = 0;
      }
      
      if (isset($records)) {
	     $temparray2 = $records[0];
	     unset($records[0]);
	     
         foreach ($records as $records1) {
	        $tempfile.="<record>\n";
            foreach ($records1 as $records2) {
	           $data = "<".$temparray2[$count].">".$records2."</".$temparray2[$count].">\n";
	           $tempfile.=$data;
               $count = $count + 1;
            }
            $tempfile.="</record>\n";
            $count = 0;
         }
      }
      $tempfile.="</$page>\n";
      return $tempfile;
   }
   
   // creates the file to be exported, calls the appropriate write function, and returns the file name
   function createFile($info, $records, $ftype, $page) { 
      if ($ftype == "csv") $tempfile = exportForm::writeCSV($info, $records, $page);
      elseif ($ftype == "plain") $tempfile = exportForm::writePlain($info, $records, $page);
      elseif ($ftype == "tab") $tempfile = exportForm::writeTab($info, $records, $page);
      elseif ($ftype == "xml") $tempfile = exportForm::writeXML($info, $records, $page);

      return $tempfile;
   }

   // serve the file as a download to the user
   function serveFile($file, $page, $ftype) {
	  $filename = getAuthUsername().$page.time();
	  // append file extension type
	  if ($ftype == "csv") $filename .= ".csv";
	  elseif ($ftype == "plain") $filename .= ".txt";
	  elseif ($ftype == "tab") $filename .= ".txt";
	  elseif ($ftype == "xml") $filename .= ".xml";
	  
      //$file = file_get_contents($tempfname);
      
      // change content type based on file extension
      if ($ftype == "csv") header('Content-Type: text/csv');
      elseif ($ftype == "plain") header('Content-Type: text/plain');
      elseif ($ftype == "tab") header('Content-Type: text/plain');
      elseif ($ftype == "xml") header('Content-Type: application/xml');
            
      header("Content-Disposition: attachment; filename=$filename");
      header('Content-Length: ' . strlen($file));
   
      echo $file;
   }
}
?>
