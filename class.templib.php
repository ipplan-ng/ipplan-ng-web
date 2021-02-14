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

class IPplanIPTemplate {

    // format of $userfld array is:
    // $userfld["field1"]["descrip"]
    // $userfld["field1"]["type"]
    // $userfld["field1"]["maxlength"]
    // $userfld["field1"]["size"]
    // $userfld["field1"]["rows"]
    // $userfld["field1"]["regex"]
    // $userfld["field1"]["errmsg"]
    // $userfld["field1"]["value"]      - only defined once template is merged

    var $userfld = array();
    var $error = FALSE;
    var $errmsg = "";

    // which template do I use? create a template in the format: 
    //      iptemplate.xml
    //      iptemplate-X.xml
    //      iptemplate-X-yyy-yyy-yyy-yyy.xml
    function __findtemp($name, $cust, $netaddr) {
        if (CONFIG_DIR=="") {
            $dir=dirname(__FILE__);    // class will always be in ipplan root dir 
        }
        else {
            $dir=CONFIG_DIR;
        }

        // network address?
        $filename=sprintf("%s/templates/display/%s-network.xml", $dir, $name);
        if($netaddr AND is_readable($filename)) {
            return $filename;
        }

        // try template with customer
        $filename=sprintf("%s/templates/display/%s-%s.xml", $dir, $name, $cust);
        if($cust AND is_readable($filename)) {
            return $filename;
        }

        // try template with no customer
        $filename=sprintf("%s/templates/display/%s.xml", $dir, $name);
        if(is_readable($filename)) {
            return $filename;
        }

        // no template found, return error
        $this->error=TRUE;
        $this->errmsg="Error finding template file";
        if (DEBUG) {
            echo $this->errmsg;
        }
        return FALSE;
    }

    function IPplanIPTemplate($name, $cust=0, $netaddr=FALSE) {

        $filename=$this->__findtemp($name, $cust, $netaddr);
        if ($filename==FALSE) {
            return;
        }

        // suppress errors when loading file
        if (!$data=@file($filename)) {
            $this->error=TRUE;
            $this->errmsg="Error reading template file";
            if (DEBUG) {
                echo $this->errmsg;
            }
            return;
        }
        $input = implode("", $data);

        require_once("../xmllib.php");

        $xml_parser = new xml("FIELD");
        if (!$xml_parser->parser) {
            $this->error=TRUE;
            $this->errmsg="Error opening XML parser";
            if (DEBUG) {
                echo $this->errmsg;
            }
            return 1;  // parser not initialized - XML probably not compiled in
        }
        $output=$xml_parser->parse($input);
        if (!$output) {
            $this->error=TRUE;
            $this->errmsg="Error parsing XML file";
            if (DEBUG) {
                echo $this->errmsg;
            }
            return 1;  // not XML format
        }
        // turn on line below for debugging
        //echo "<pre>"; var_dump($output); echo "</pre>";

        foreach ($output as $key=>$value) {
            $field=$value["DEFINITION"]["NAME"];
            // field names must be alpha numeric with no spaces etc, else row is ignored!
            if (!empty($field) and preg_match("/^[a-zA-Z0-9]+$/", $field)) {
                $this->userfld["$field"]["descrip"]=isset($value["DEFINITION"]["DESCRIP"]) ?
                                                     $value["DEFINITION"]["DESCRIP"] : "Field: ".$field;

                // selectbox?
                if ($this->__selectbox($value, $field)) {
                    continue;
                }
                // checkbox?
                //else if ($this->__checkbox($value, $field)) {
                //    continue;
                //}

                // use default type of character - all bogus fields are converted to character
                $this->__character($value, $field);

            }
        }

        //echo "<pre>";var_dump($this->userfld);echo "</pre>";
        return;

    }

    function __selectbox($value, $field) {

        // field type is selectbox?
        if (isset($value["DEFINITION"]["TYPE"]) and $value["DEFINITION"]["TYPE"] == "S") {
            $lst=array();
            foreach ($value["SELECT"] as $value1) {
                $lst[$value1["VAL"]]=$value1["OPTION"];
            }
            $this->userfld["$field"]["type"]= "S";
            $this->userfld["$field"]["select"]=$lst;
            $this->userfld["$field"]["default"]=isset($value["DEFINITION"]["DEFAULT"]) ? 
                $value["DEFINITION"]["DEFAULT"] : "" ;

            return TRUE;
        }

        // not a selectbox
        return FALSE;

    }
    
    /*
    function __checkbox($value, $field) {

        // field type is checkbox?
        if (isset($value["DEFINITION"]["TYPE"]) and $value["DEFINITION"]["TYPE"] == "B") {
            $this->userfld["$field"]["type"]= "B";
            $this->userfld["$field"]["default"]=isset($value["DEFINITION"]["DEFAULT"]) ? 
                $value["DEFINITION"]["DEFAULT"] : "1" ;

            return TRUE;
        }

        // not a checkbox
        return FALSE;

    }
    */
 
    function __character($value, $field) {

        // default field type is C
        if ((isset($value["DEFINITION"]["TYPE"]) and $value["DEFINITION"]["TYPE"] == "C") or 
                (isset($value["DEFINITION"]["TYPE"]) and $value["DEFINITION"]["TYPE"] == "T")) {
            $this->userfld["$field"]["type"]=
                $value["DEFINITION"]["TYPE"];
        }
        else {
            $this->userfld["$field"]["type"]= "C";
        }
        // size and maxlength default to 80 characters if 
        // there is crap data in template
        if (isset($value["DEFINITION"]["MAXLENGTH"]) and 
                is_numeric($value["DEFINITION"]["MAXLENGTH"])) {
            $this->userfld["$field"]["maxlength"]=
                $value["DEFINITION"]["MAXLENGTH"];
        }
        else {
            $this->userfld["$field"]["maxlength"]= 80;
        }
        if (isset($value["DEFINITION"]["SIZE"]) and 
                is_numeric($value["DEFINITION"]["SIZE"])) {
            $this->userfld["$field"]["size"]=
                $value["DEFINITION"]["SIZE"];
        }
        else {
            $this->userfld["$field"]["size"]= 80;
        }
        if (isset($value["DEFINITION"]["ROWS"]) and 
                is_numeric($value["DEFINITION"]["ROWS"])) {
            $this->userfld["$field"]["rows"]=
                $value["DEFINITION"]["ROWS"];
        }
        else {
            $this->userfld["$field"]["rows"]= 1;
        }
        $this->userfld["$field"]["regex"]=isset($value["DEFINITION"]["REGEX"]) ? 
            $value["DEFINITION"]["REGEX"] : "" ;
        $this->userfld["$field"]["errmsg"]=isset($value["DEFINITION"]["ERRMSG"]) ?
            $value["DEFINITION"]["ERRMSG"] : 
            "Invalid field: ".$this->userfld["$field"]["descrip"];
        $this->userfld["$field"]["default"]=isset($value["DEFINITION"]["DEFAULT"]) ? 
            $value["DEFINITION"]["DEFAULT"] : "" ;

    }

    // adds the current template information to the layout class for
    // display
    function DisplayTemplate(&$layout) {

        //echo "<pre>";var_dump($this->userfld);echo "</pre>";
        foreach($this->userfld as $field=>$val) { 

            insert($layout,textbr());
            insert($layout,text($this->userfld["$field"]["descrip"]));
            insert($layout,textbr());

            // "name" definition looks strange, but associative arrays
            // in html must not have quotes, something like
            // userfld[fieldname] which will be correctly converted by php
            if ($this->userfld["$field"]["type"] == "C") {
                insert($layout,input_text(array(
                                "name"=>"userfld[$field]",
                                "value"=>isset($this->userfld["$field"]["value"]) ? $this->userfld["$field"]["value"] : $this->userfld["$field"]["default"],
                                "size"=>$this->userfld["$field"]["size"],
                                "maxlength"=>$this->userfld["$field"]["maxlength"])));
            }
            elseif ($this->userfld["$field"]["type"] == "T") {
                insert($layout,textarea(array(
                                "name"=>"userfld[$field]",
                                "cols"=>$this->userfld["$field"]["size"],
                                "rows"=>$this->userfld["$field"]["rows"],
                                "maxlength"=>$this->userfld["$field"]["maxlength"]), 
                            isset($this->userfld["$field"]["value"]) ? $this->userfld["$field"]["value"] : $this->userfld["$field"]["default"]));

            }
            elseif ($this->userfld["$field"]["type"] == "S") {
                insert($layout,selectbox($this->userfld["$field"]["select"],
                   array("name"=>"userfld[$field]"),
                   isset($this->userfld["$field"]["value"]) ? $this->userfld["$field"]["value"] : $this->userfld["$field"]["default"]));
            }
            /*
            elseif ($this->userfld["$field"]["type"] == "B") {
                insert($layout,checkbox(array("name"=>"userfld[$field]",
                                "value"=>isset($this->userfld["$field"]["value"]) ? $this->userfld["$field"]["value"] : $this->userfld["$field"]["default"]), "Text"));

            }
            */
        }
    }

    // merges the serialized info stored in the database
    // can be empty
    // data stored as userfld["fieldname"]["value"] to be 
    // used by DisplayTemplate method
    function Merge($dbfinfo) {

        //echo "<pre>";var_dump($this->userfld);echo "</pre>";
        if (is_array($dbfinfo)) {
            foreach($dbfinfo as $field=>$val) { 
                // check if field exists in template as user may have modified
                // template at some stage
                if (isset($this->userfld["$field"]) and is_array($this->userfld["$field"])) {
                    $this->userfld["$field"]["value"] = $val;
                }
                // add default information if field no longer exists
                else {
                    $this->userfld["$field"]["descrip"] = "Unknown field $field";
                    $this->userfld["$field"]["type"] = "C";
                    $this->userfld["$field"]["maxlength"] = 255;
                    $this->userfld["$field"]["rows"] = 1;
                    $this->userfld["$field"]["size"] = 80;
                    $this->userfld["$field"]["regex"] = "";
                    $this->userfld["$field"]["errmsg"] = "";
                    $this->userfld["$field"]["value"] = $val;
                }
            }
        }
        //echo "<pre>";var_dump($this->userfld);echo "</pre>";
    }

    // updates the serialized info stored in the database - does not add additional
    // modifyipform request ip function must not add additional fields else these
    // additional fields overflow the HTML page variables and overwrite some of the
    // hidden fields on the page
    // fields into template
    function Update($dbfinfo) {

        //echo "<pre>";var_dump($this->userfld);echo "</pre>";
        if (is_array($dbfinfo)) {
            foreach($dbfinfo as $field=>$val) { 
                // check if field exists in template as user may have modified
                // template at some stage
                if (isset($this->userfld["$field"]) and is_array($this->userfld["$field"])) {
                    $this->userfld["$field"]["value"] = $val;
                }
            }
        }
        //echo "<pre>";var_dump($this->userfld);echo "</pre>";
    }

    // verify the current template information against user template
    function Verify(&$layout) {

        $err=FALSE;
        foreach($this->userfld as $field=>$val) { 
            // test against regex in template
            if (($this->userfld["$field"]["type"] == "C" or $this->userfld["$field"]["type"] == "T") and 
                !empty($this->userfld["$field"]["regex"]) and
                !preg_match("/".$this->userfld["$field"]["regex"]."/", 
                        $this->userfld["$field"]["value"])) {

                insert($layout,text($this->userfld["$field"]["errmsg"], 
                        array("color"=>"#FF0000")));
                insert($layout,textbr());
                $err=TRUE;
            }

        }

        return $err;
    }

    // reset all template fields - basically reinitialize template so class definition
    // can be re-used with re-instatiating
    function Clear() {

        $this->userfld=array();

    }

    // check if current template is blank
    function is_blank() {

        // check if all template fields are blank
        foreach($this->userfld as $field=>$val) {
            if (!empty($this->userfld["$field"]["value"])) {
                return FALSE;
            }
        }

        return TRUE;
    }
    
    // check if current template had an error
    function is_error() {
        return $this->error;
    }

    // return error message
    function errmsg() {
        return $this->errmsg;
    }

    // encode the template via serialize function
    // binary data should be base64 encoded

    // format of data to serialze is 
    //      array("fieldname1"=>"val1", "fieldname2"=>"val2")

    // should check with is_blank method first else will return
    // blank serialzed output!
    function encode() {

        $data="";
        //serialize($this->userfld);
        foreach($this->userfld as $field=>$val) {
            $data["$field"]=$this->userfld["$field"]["value"];
        }

        return serialize($data);

    }

    
    // decode the template via serialize function
    // binary data should be base64 encoded

    // takes properly formatted serialized field created by encode method
    // from database as input
    function decode($dbfinfo) {

        $data=unserialize((string)$dbfinfo);   // suppress warning message if string is empty

        // unserialize failed, move data into old info field for
        // backwards compatability - only if info field is defined in
        // user template
        if ($data==FALSE and isset($this->userfld["info"]) and is_array($this->userfld["info"])) {
            $this->userfld["info"]["value"]=$dbfinfo; 
        }

        return $data;
    }
    

    function return_templ_name() {
        //echo "<pre>";var_dump($this->userfld);echo "</pre>";
        $tmpl_def=array();
        $tmpl_def["any"]=my_("Any");
        foreach($this->userfld as $field=>$val) {
            //echo "<pre>";var_dump($val["descrip"]);echo "</pre>";
            $tmpl_def[$field]=$val["descrip"];
        }
        //echo "<pre>";var_dump($tmpl_def);echo "</pre>";
        return $tmpl_def;
    }
    
    function return_userfld(){
	    return $this->userfld;
    }
}

// NB - no space after last line here!
?>
