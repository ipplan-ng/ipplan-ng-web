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
// workaround for funky behaviour of nested includes with older
// versions of php
require_once(dirname(__FILE__)."/config.php");

// common class functions for forward and reverse zones
class DNSZone extends IPplanDbf {

    // form variables
    var $hname;
    var $ttl;
    var $refresh;
    var $retry;
    var $expire;
    var $minimum;
    var $responsiblemail;
    var $slaveonly;
    var $zonepath;
    var $seczonepath;
    var $info="";

    var $serialdate=0;
    var $serialnum=0;

    // local class variables
    var $grps;
    var $errstr = "";
    var $err = 1;
    var $clone = 0;

    function SetSOA($hname, $ttl, $refresh, $retry, $expire, $minimum, 
            $responsiblemail, $slaveonly, $zonepath, $seczonepath, $info="") {

        $this->hname = $hname;
        $this->ttl = $ttl;
        $this->refresh = $refresh;
        $this->retry = $retry;
        $this->expire = $expire;
        $this->minimum = $minimum;
        $this->responsiblemail = $responsiblemail;
        $this->slaveonly = $slaveonly;
        $this->zonepath = $zonepath;
        $this->seczonepath = $seczonepath;
        $this->info = $info;

    }

    function SetSerial($serialdate, $serialnum) {

        $this->serialdate=$serialdate;
        $this->serialnum=$serialnum;

    }

    // work out the new serial number from a previous SetSerial
    function Serial() {

        $now = getdate();
        $newserialdate = $now["year"] . str_pad($now["mon"], 2, '0', STR_PAD_LEFT) . str_pad( $now["mday"], 2, '0', STR_PAD_LEFT);
        if ($this->serialdate==$newserialdate) {
            $this->serialnum++;
        }else{
            $this->serialdate=$newserialdate;
            $this->serialnum=0;
        }
    }
    
    // remove domain name from FQDN, converts to lowercase
    // if domain cannot be stripped, return FQDN with trailing .
    function strip_domain($hname, $domain) {

        $hname=strtolower($hname);
        $domain=strtolower($domain);

        // use ~ as sperator as this cannot appear in dns name
        $temp = preg_replace("~\.$domain$~", "", $hname);
        if ($hname===$temp) {
            $temp=$hname.".";
        }
        return $temp;

    }

    function ZoneAXFR($domain, $server) {

        require_once("Net/DNS.php");

        $res = new Net_DNS_Resolver();
        //$res->debug = 1;
        $res->persistent_tcp=1;
        $res->nameservers=array($server);

        $answer = $res->axfr($domain);

/*
echo "<pre>"; 
var_dump($answer); 
//var_dump($res); 
//var_dump($answer[0]->header->rcode); 
echo "</pre>";
exit;
*/

        // check for errors
        /*
        if ($res->errorstring != "NOERROR") {
            $this->err=70;
            $this->errstr .= sprintf(my_("Zone transfer for domain %s failed with message %s"), $this->domain, $res->errorstring)."\n";
            return "";
        }
        */

        if ($answer) {
            $this->hname=array(); $i=1;   // kill form information
            foreach($answer as $rr) {
                if ($rr->type == "SOA") {
                    //var_dump($rr);
                    $this->ttl=$rr->ttl;
                    $this->refresh=$rr->refresh;
                    $this->retry=$rr->retry;
                    $this->expire=$rr->expire;
                    $this->minimum=$rr->minimum;
                    $this->responsiblemail=$rr->rname;
                }
                if ($rr->type == "NS" and $rr->name == $this->domain) {
                    $this->hname[$i++]=$this->strip_domain($rr->nsdname, $this->domain);
                }
            }

            $this->err = 0;
            return $answer;

        } else {
            $this->errstr .= sprintf(my_("Zone transfer for domain %s failed - using defaults: %s"), $this->domain, $res->errorstring)."\n";
            // could not do transfer, so use defaults from now on!
            $this->err = -1;

            return "";

        }

    }

} // end class DNSZone

// specific forward zone functions
class DNSfwdZone extends DNSZone {

    var $cust;
    var $dataid;
    var $domain;
    var $server;
    var $createmod;
    var $expiremod;
    var $regmod;

    function SetForm($cust, $dataid, $domain) {

        $this->cust = $cust;
        $this->dataid = $dataid;
        $this->domain = $domain;
    }

    function SetDate($createmod, $expiremod, $regmod) {

        $this->createmod = $createmod;
        $this->expiremod = $expiremod;
        $this->regmod = $regmod;
    }

    function FwdDelete($cust, $dataid, $domain) {

        // use local function variables as they may change
        $this->cust = $cust;
        $this->rangeindex = $dataid;
        $this->areaindex = $domain;

        // check for records
        $result = $this->ds->Execute("SELECT recidx 
                FROM fwdzonerec 
                WHERE customer=$cust AND data_id=$dataid");
        $recs=$result->PO_RecordCount("fwdzonerec", "customer=$cust AND data_id=$dataid");
        if ($recs) {
            $this->err=50;
            $this->errstr .= my_("Cannot delete domain zone - there are still DNS records\n");
            return FALSE;
        }

        $result = $this->ds->Execute("DELETE FROM fwdzone 
                WHERE customer=$cust AND data_id=$dataid") and
            $this->ds->Execute("DELETE FROM fwdzoneadd
                WHERE customer=$cust AND data_id=$dataid") and
            $this->ds->Execute("DELETE FROM fwddns 
                    WHERE id=$dataid");

        return $result;
    }

    function FwdZoneAddRR ($dataid, $answer) {

        $i=5;
        foreach($answer as $rr) {
            if ($rr->type == "A") {
                $recordtype=$rr->type;
                $host=$this->strip_domain($rr->name, $this->domain);
                $iphostname=$rr->address;
            }
            else if ($rr->type == "CNAME") {
                $recordtype=$rr->type;
                $host=$this->strip_domain($rr->name, $this->domain);
                $iphostname=$this->strip_domain($rr->cname, $this->domain);
            }
            else if ($rr->type == "NS" and $rr->name != $this->domain) {
                $recordtype=$rr->type;
                $host=$this->strip_domain($rr->name, $this->domain);
                $iphostname=$this->strip_domain($rr->nsdname, $this->domain);
            }
            else if ($rr->type == "MX") {
                $recordtype=$rr->type;
                $host=$this->strip_domain($rr->name, $this->domain);
                $iphostname=$rr->preference." ".$this->strip_domain($rr->exchange, $this->domain);
            }
            else if ($rr->type == "TXT") {
                $recordtype=$rr->type;
                $host=$this->strip_domain($rr->name, $this->domain);
                // truncate TXT field to 254 chars - can be much bigger
                $iphostname=substr($rr->rdata, 1, $rr->rdlength > 254 ? 254 : $rr->rdlength);
            }
            else if ($rr->type == "SRV") {
                $recordtype=$rr->type;
                $host=$this->strip_domain($rr->name, $this->domain);
                $iphostname=$rr->preference." ".$rr->weight." ".$rr->port." ".$this->strip_domain($rr->target,  $this->domain);
	    }
            else if ($rr->type == "AFSDB") {
                $recordtype=$rr->type;
                $host=$this->strip_domain($rr->name, $this->domain);
                $iphostname=$rr->preference." ".$this->strip_domain($rr->exchange, $this->domain);
            }
            else {
                continue;
            }

            $result = $this->ds->Execute("INSERT into fwdzonerec 
                    (customer, data_id, sortorder, lastmod, host, 
                     recordtype, userid, ip_hostname) ".
                    "VALUES ($this->cust, $dataid, ". $i.",".
                    $this->ds->DBTimeStamp(time()).",".
                    $this->ds->qstr($host).",".
                    $this->ds->qstr($recordtype).",".
                    $this->ds->qstr(getAuthUsername()).",".
                    $this->ds->qstr($iphostname).")" );
            if (!$result) {
                return FALSE;
            }
            $i=$i+5;
        }

        return TRUE;

    }

    function FwdAddNS($dataid) {

        // add reverse DNS into fwddns table
        for ($i = 1; $i < 11; $i++) {
            if (isset($this->hname[$i]) and !empty($this->hname[$i])) {
                $hnametemp=$this->hname[$i];

                // add DNS records
                $result=$this->ds->Execute("INSERT INTO fwddns
                        (id, hname, horder)
                        VALUES
                        ($dataid,
                         ".$this->ds->qstr($hnametemp).",
                         $i)");
                if (!$result) {
                    return FALSE;
                }
            }
        }

        return TRUE;

    }

    // test if the zone exists and lock for update
    function FwdZoneExists($cust, $dataid) {
        // need to do select as mysql driver does not have RowLock
        // mysqlt does have rowlock
        $row = $this->ds->GetOne("SELECT data_id
                FROM fwdzone
                WHERE customer=$cust AND data_id=$dataid");
        $this->ds->RowLock("fwdzone", "customer=$cust AND data_id=$dataid");
        return !empty($row);
    }

    function FwdUpdateSOA($cust, $dataid) {

        // use local function variables as they may change
        $this->cust = $cust;

        if (!$this->FwdZoneExists($cust, $dataid)) {
            $this->err=90;
            $this->errstr .= my_("Could not find the zone - possibly deleted by another user")."\n";
            return FALSE;
        }

        // work out new serial numbers
        $this->Serial();

        // check and warn if there are zone records if slaveonly=Y
        if ($this->slaveonly=="Y") {
            $result = $this->ds->Execute("SELECT count(*) AS cnt
                    FROM fwdzonerec
                    WHERE customer=$cust AND data_id=$dataid");
            // loop through each host record
            $row = $result->FetchRow();
            if($row["cnt"] > 0) {
                $this->err=-50;
                $this->errstr .= my_("You are changing this zone to a slave only zone, but there are zone records\n");
            }
        }

        // Updated DB here.
        $result = $this->ds->Execute("UPDATE fwdzone ".
                "set serialdate=".$this->ds->qstr($this->serialdate).
                ", serialnum=$this->serialnum".
                ",ttl=".$this->ttl.
                ",refresh=".$this->refresh.
                ",retry=".$this->retry.
                ",expire=".$this->expire.
                ",minimum=".$this->minimum.
                ",error_message=".$this->ds->qstr("E").
                ",responsiblemail=".$this->ds->qstr($this->responsiblemail).
                ",userid=".$this->ds->qstr(getAuthUsername()).
                ",zonefilepath1=".$this->ds->qstr($this->zonepath).
                ",zonefilepath2=".$this->ds->qstr($this->seczonepath).
                ",createmod=".$this->ds->DBDate($this->createmod).
                ",lastmod=".$this->ds->DBTimeStamp(time()).
                ",expiremod=".$this->ds->DBDate($this->expiremod).
                ",regmod=".$this->ds->DBDate($this->regmod).
                ",slaveonly=".$this->ds->qstr($this->slaveonly).
                " WHERE customer=$cust AND data_id=".$dataid );

        if($this->ds->GetRow("SELECT info
                FROM fwdzoneadd
                WHERE customer=$cust AND
                data_id=$dataid")) {   // should have FOR UPDATE here!
            $result = $this->ds->Execute("UPDATE fwdzoneadd ".
                "set info=".$this->ds->qstr($this->info).
                " WHERE customer=$cust AND data_id=".$dataid );
        } 
        else {  // no record, insert
            if (!empty($this->info)) {
                $result = $this->ds->Execute("INSERT into fwdzoneadd (customer, data_id, info) ".
                        "VALUES ($this->cust,".
                        $dataid.",".
                        $this->ds->qstr($this->info).")" );
            }

        }

        // delete all the DNS records first to preserve correct order
        $result=$this->ds->Execute("DELETE FROM fwddns
                WHERE id=$dataid");

        // add reverse DNS into fwdzone table
        $this->FwdAddNS($dataid);

        return $result;

    }

    function FwdAddSOA() {

        // work out new serial numbers
        $this->Serial();

        // Add to DB here.
        $result = $this->ds->Execute("INSERT into fwdzone (customer, domain, error_message,
            createmod, lastmod, expiremod, regmod, serialdate, serialnum, ttl, refresh, retry, 
            expire, minimum, responsiblemail, userid, zonefilepath1, zonefilepath2, slaveonly) ".
                "VALUES ($this->cust,".
                $this->ds->qstr($this->domain).",".
                $this->ds->qstr("E").",".
                $this->ds->DBDate($this->createmod).",".
                $this->ds->DBTimeStamp(time()).",".
                $this->ds->DBDate($this->expiremod).",".
                $this->ds->DBDate($this->regmod).",".
                $this->ds->qstr($this->serialdate).", $this->serialnum,".
                $this->ttl.",".
                $this->refresh.",".
                $this->retry.",".
                $this->expire.",".
                $this->minimum.",".
                $this->ds->qstr($this->responsiblemail).",".
                $this->ds->qstr(getAuthUsername()).",".
                $this->ds->qstr($this->zonepath).",".
                $this->ds->qstr($this->seczonepath).",".
                $this->ds->qstr($this->slaveonly).")" );

        // did not fail due to key error?
        // should not fail as we checked this already!
        if ($result) {
            if (DBF_TYPE=="mysql" or DBF_TYPE=="maxsql") {
                $dataid=$this->ds->Insert_ID();
            }
            else {
                // emulate getting the last insert_id
                $result=$this->ds->Execute("SELECT data_id 
                        FROM fwdzone
                        WHERE customer=$this->cust AND 
                        domain=".$this->ds->qstr($this->domain));
                $temprow = $result->FetchRow();
                $dataid=$temprow["data_id"];
            }

            if (!empty($info)) {
                $result = $this->ds->Execute("INSERT into fwdzoneadd (customer, data_id, info) ".
                        "VALUES ($this->cust,".
                        $dataid.",".
                        $this->ds->qstr($this->info).")" );
            }

            return $dataid;
        }

        // key error?
        return 0;

    }

    function FwdAdd($cust, $domain, $server) {

        // use local function variables as they may change
        $this->cust = $cust;

        // is the server variable set? then do zone transfer
        // should really only read SOA if slaveonly set
        if (!empty($server)) {
            $answer=$this->ZoneAXFR($this->domain, $server);
            // fatal zone transfer error?
            if ($this->err > 0) {
                return $this->err;
            }
        }

        // could use unique key on database to do check, but requires extra key
        // just to add a new record
        $restemp=$this->ds->Execute("SELECT domain FROM fwdzone 
                WHERE customer=$cust AND domain = ".$this->ds->qstr($this->domain));

        if ($restemp->FetchRow()) {
            // domain already exists, fail transaction
            $this->err=-60;
            $this->errstr .= sprintf(my_("DNS Domain %s could not be created - possibly duplicate zone"), $this->domain)."\n";
        }
        else {
            // create the actual zone
            $dataid=$this->FwdAddSOA();
            $this->dataid=$dataid;

            // did not fail due to key error?
            if ($dataid) {
                if(!$this->FwdAddNS($dataid)) {
                    $this->err = -60;
                    return $this->err;
                }

                // now load individual rr records if zone transfer requested
                // only if not a slavezone and no previous error
                if (!empty($server) and $this->slaveonly=="N" and !empty($answer)) {
                    if(!$this->FwdZoneAddRR($dataid, $answer)) {
                        $this->err = -60;
                        return $this->err;
                    }
                }
                // or clone zone if clone requested
                else if (empty($server) and $this->slaveonly=="N" and $this->clone) {
                    // this SQL only works with MySql v >= 4.0.14
                    $result=$this->ds->Execute("INSERT INTO fwdzonerec
                        (data_id, host, recordtype, ip_hostname, sortorder, customer, userid, lastmod)
                        SELECT $dataid AS data_id, fwdzonerec.host, fwdzonerec.recordtype, 
                            fwdzonerec.ip_hostname, fwdzonerec.sortorder, fwdzonerec.customer, 
                            ".$this->ds->qstr(getAuthUsername())." AS userid,
                            ".$this->ds->DBTimeStamp(time())." AS lastmod
                        FROM fwdzonerec, fwdzone
                        WHERE fwdzonerec.data_id=fwdzone.data_id AND 
                            fwdzone.domain=".$this->ds->qstr("template.com"));

                }
                $this->err = 0;
            }
            else {
                $this->err = -60;
                $this->errstr .= sprintf(my_("DNS Domain %s could not be created - possibly duplicate zone"), $this->domain)."\n";
            }
        }

        return $this->err;
    }

    function FwdZoneExport($cust, $dataid) {

        // use local function variables as they may change
        $this->cust = $cust;

        $this->Serial();

        // Update DNS Database Serial Count.  Update Serial Count only when we export.
        $result = $this->ds->Execute("UPDATE fwdzone ".
                "set serialdate=".$this->ds->qstr($this->serialdate).
                ", userid=".$this->ds->qstr(getAuthUsername()).
                ", error_message=".$this->ds->qstr("").
                ", lastexp=".$this->ds->DBTimeStamp(time()).
                ", serialnum=$this->serialnum".
                " WHERE customer=$cust AND data_id=".$dataid);

        if ($result) {

            $sqllastmod = $this->ds->SQLDate("M d Y H:i:s", 'lastmod');
            $result = $this->ds->Execute("SELECT data_id, domain, engineer, error_message, 
                        responsiblemail, serialdate, serialnum, ttl, refresh, retry, expire, 
                        minimum, zonefilepath1, zonefilepath2, customer, admingrp, 
                        $sqllastmod AS lastmod, userid, slaveonly
                    FROM fwdzone 
                    WHERE customer=$cust AND data_id=$dataid");
            $row = $result->FetchRow();
            $this->domain=$row["domain"];

            $info = $this->ds->GetOne("SELECT info
                    FROM fwdzoneadd
                    WHERE customer=$cust AND data_id=$dataid");

            $tmpfname = tempnam (DNSEXPORTPATH, "zone_".$this->domain."_");
            if(!$tmpfname) {
                $this->err=80;
                $this->errstr .= my_("Could not create temporary file!");
                return;
            }
            $fp = fopen ("$tmpfname", "w");

            // header of document
            $output='<?xml version="1.0" ?>';
            fputs($fp, $output);
            fputs($fp, "\n");
            fputs($fp, sprintf('<zone domain="%s" slaveonly="%s">', $row["domain"], 
                        (empty($row["slaveonly"]) ? "N" : $row["slaveonly"])));
            fputs($fp, "\n");

            fputs($fp, sprintf("<path>\n<primary>\n%s\n</primary>\n",
                htmlspecialchars($row["zonefilepath1"])));
            fputs($fp, sprintf("<primaryfile>\n%s\n</primaryfile>\n",
                htmlspecialchars(basename($row["zonefilepath1"]))));
            fputs($fp, sprintf("<primarydir>\n%s\n</primarydir>\n",
                htmlspecialchars(dirname($row["zonefilepath1"]))));

            fputs($fp, sprintf("<secondary>\n%s\n</secondary>\n",
                htmlspecialchars($row["zonefilepath2"])));
            fputs($fp, sprintf("<secondaryfile>\n%s\n</secondaryfile>\n",
                htmlspecialchars(basename($row["zonefilepath2"]))));
            fputs($fp, sprintf("<secondarydir>\n%s\n</secondarydir>\n",
                htmlspecialchars(dirname($row["zonefilepath2"]))));
            fputs($fp, "</path>\n");

            // SOA portion
            fputs($fp, sprintf('<soa serialdate="%s" serialnum="%02d" ttl="%s" retry="%s" refresh="%s" expire="%s" minimumttl="%s" email="%s" />', 
                        $this->serialdate, $this->serialnum, 
                        $row["ttl"],
                        $row["retry"],
                        $row["refresh"],
                        $row["expire"],
                        $row["minimum"],
                        $row["responsiblemail"]
                        ));
            fputs($fp, "\n");

            // additional fields
            if (!empty($info)) {
                $template=new IPplanIPTemplate("fwdzonetemplate", $cust);
                if ($template->is_error() == FALSE) {
                    $template->Merge($template->decode($info));
                    fputs($fp, "<additional>\n");
                    foreach($template->userfld as  $field=>$val) {
                        fputs($fp, sprintf("\t<%s>%s</%s>\n", 
                                    htmlspecialchars($field),
                                    htmlspecialchars($val["value"]),
                                    htmlspecialchars($field)));
                    }
                    fputs($fp, "</additional>\n");
                }
            }

            // nameservers
            $result1 = $this->ds->Execute("SELECT hname
                    FROM fwddns
                    WHERE id=$dataid
                    ORDER BY horder");

            $cnt=0;
            while($row1 = $result1->FetchRow()) {
                fputs($fp, '<record><NS>');
                fputs($fp, sprintf('<iphostname>%s</iphostname>', $row1["hname"]));
                fputs($fp, '</NS></record>');
                fputs($fp, "\n");

                $cnt++;
            }
            if ($cnt < 2) {
                fclose($fp);
                unlink($tmpfname);

                $this->err=70;
                $this->errstr .= my_("Invalid zone - zone should have at least two name servers defined")."\n";
                return;
            }

            $sqllastmod = $this->ds->SQLDate("M d Y H:i:s", 'lastmod');
            $result = $this->ds->Execute("SELECT recidx, data_id, host, recordtype, ip_hostname, 
                        error_message, sortorder, customer, $sqllastmod AS lastmod, userid
                    FROM fwdzonerec
                    WHERE customer=$cust AND data_id=$dataid
                    ORDER BY sortorder");
            // loop through each host record
            while($row = $result->FetchRow()) {
                fputs($fp, sprintf('<record><%s>', $row["recordtype"]));
                fputs($fp, sprintf('<host>%s</host>', $row["host"]));
                // MX records are in format "10 hostname.com" in database field ip_hostname
                if ($row["recordtype"]=="MX" or $row["recordtype"]=="AFSDB") {
                    list($preference, $iphost) = explode(" ", $row["ip_hostname"], 2);
                    if (is_numeric($preference) and $preference >= 0) {
                        fputs($fp, sprintf('<preference>%s</preference>', $preference));
                        fputs($fp, sprintf('<iphostname>%s</iphostname>', $iphost));
                    }
                    else {
                        fputs($fp, '<preference>10</preference>');
                        fputs($fp, sprintf('<iphostname>%s</iphostname>', $row["ip_hostname"]));

                    }
                  }
                else if ($row["recordtype"]=="SRV")
                  {
                    list($priority, $weight, $port, $iphost) = explode(" ", $row["ip_hostname"], 4);
                    if (is_numeric($priority) and is_numeric($weight) and is_numeric($port)
                               and $priority >= 0        and $weight >= 0        and $port >= 0 ) {
                        fputs($fp, sprintf('<preference>%s %s %s</preference>', $priority, $weight, $port));
                        fputs($fp, sprintf('<iphostname>%s</iphostname>', $iphost));
                    }
                    else {
                        fputs($fp, '<preference> Invalid SRV record </preference>');
                        fputs($fp, sprintf('<iphostname>%s</iphostname>', $row["ip_hostname"]));

                    }
                  }
                else {
                    fputs($fp, sprintf('<iphostname>%s</iphostname>', $row["ip_hostname"]));
                }
                fputs($fp, sprintf('</%s></record>', $row["recordtype"]));
                fputs($fp, "\n");
            }
            // close zone
            fputs($fp, '</zone>');
            fputs($fp, "\n");

            fclose($fp);
            // give file proper extension
            rename($tmpfname, $tmpfname.".xml");
            @chmod($tmpfname.".xml",0644);

            $this->err=0;
            return $tmpfname.".xml";
        }

        //return $tmpfname;
        // database error?

    }


} // end of class DNSFwdZone


// specific forward zone functions
class DNSrevZone extends DNSZone {

    var $cust;
    var $zoneid;
    var $zone;
    var $zoneip;
    var $size;
    var $server;

    function SetForm($cust, $zoneid, $zone, $zoneip, $size) {

        $this->cust = $cust;
        $this->zoneid = $zoneid;
        $this->zone = $zone;
        $this->zoneip = $zoneip;
        $this->size = $size;
    }

    function RevDelete($cust, $zoneid, $zone) {

        // use local function variables as they may change
        $this->cust = $cust;

        $result = $this->ds->Execute("DELETE FROM zones 
                WHERE customer=$cust AND id=$zoneid") and
            $this->ds->Execute("DELETE FROM zonedns
                    WHERE id=$zoneid");

        return $result;
    }

    function RevZoneAddRR ($zoneid, $answer) {

        global $grps;

        // open a new database connection
        $ds = new Base();
        if(!$ds) {
            $this->err=90;
            $this->errstr .= my_("Could not connect to database");
        }

        $ds->SetGrps($grps);
        $ds->SetSearchIn(1);

        foreach($answer as $rr) {
            if ($rr->type == "PTR") {
                $recordtype=$rr->type;
                $domain=$rr->ptrdname;  // proper domain name
                $host=$rr->name;  // in format 46.61.110.147.in-addr.arpa
            }
            else {
                continue;
            }

            // now split ip address
            list($oc1, $oc2, $oc3, $oc4, $tail) = split("\.", $host, 5);
            $ipaddr="$oc4.$oc3.$oc2.$oc1";
            if (testIP($ipaddr)) {
                $this->errstr .= sprintf(my_("Invalid address %s"), $ipaddr)."\n";
                continue;
            }

            $ds->SetIPaddr($ipaddr); 
            $result = $ds->FetchBase($this->cust, 0, 0);
            if (!$result) {
                $this->err=70;
                $this->errstr .= $ds->errstr;
            }
            // add records here - got a match for a subnet
            if ($row = $result->FetchRow()) {
                $baseindex=$row["baseindex"];
                $affected=$ds->UpdateIP(inet_aton($ipaddr), $baseindex, "hname", $domain);
                if (!$affected) {
                    $ds->AddIP(inet_aton($ipaddr), $baseindex, "", "", "", "",
                            "Reverse zone import", $domain, "");
                }
            }
            // no subnet matched, add something to the error string
            else {
                $this->errstr .= sprintf(my_("No subnet found for address %s"), $ipaddr)."\n";
            }
        }

        return TRUE;

    }

    function RevAddNS($zoneid) {

        // add reverse DNS into fwddns table
        for ($i = 1; $i < 11; $i++) {
            if (isset($this->hname[$i]) and !empty($this->hname[$i])) {
                $hnametemp=$this->hname[$i];

                // add DNS records
                $result=$this->ds->Execute("INSERT INTO zonedns
                        (id, hname, horder)
                        VALUES
                        ($zoneid,
                         ".$this->ds->qstr($hnametemp).",
                         $i)");

                if (!$result) {
                    return FALSE;
                }
            }
        }

        return TRUE;

    }

    // test if the zone exists and lock for update
    function RevZoneExists($cust, $zoneid) {
        // need to do select as mysql driver does not have RowLock
        // mysqlt does have rowlock
        $row = $this->ds->GetOne("SELECT id
                FROM zones
                WHERE customer=$cust AND id=$zoneid");
        $this->ds->RowLock("zones", "customer=$cust AND id=$zoneid");
        return !empty($row);
    }

    // test if the zone has changed records - check the last serial for the zone
    // against the ippaddr table for changed records i.e. records with a date equal or
    // later than the serial date
    function RevZoneChanged($cust, $zoneid) {
        $sqlfn = $this->ds->SQLDate('Ymd','ipaddr.lastmod');
        $row = $this->ds->GetOne("SELECT zones.id
                FROM zones, base, ipaddr
                WHERE zones.customer=base.customer AND 
                base.baseindex=ipaddr.baseindex AND 
                $sqlfn >= zones.serialdate AND
                ipaddr.ipaddr >= zones.zoneip AND 
                ipaddr.ipaddr < zones.zoneip+zones.zonesize AND
                zones.id=$zoneid");
        return !empty($row);
    }

    function RevUpdateSOA($cust, $zoneid, $zone, $zoneip, $size) {

        // use local function variables as they may change
        $this->cust = $cust;

        if (!$this->RevZoneExists($cust, $zoneid)) {
            $this->err=90;
            $this->errstr .= my_("Could not find the zone - possibly deleted by another user")."\n";
            return FALSE;
        }

        // work out new serial numbers
        $this->Serial();

        // Updated DB here.
        $result = $this->ds->Execute("UPDATE zones SET zoneip=$zoneip".
                ",zone=".$this->ds->qstr($this->zone).
                ",zonesize=$size".
                ",serialdate=".$this->ds->qstr($this->serialdate).
                ",serialnum=$this->serialnum".
                ",ttl=".$this->ttl.
                ",refresh=".$this->refresh.
                ",retry=".$this->retry.
                ",expire=".$this->expire.
                ",minimum=".$this->minimum.
                ",error_message=".$this->ds->qstr("E").
                ",responsiblemail=".$this->ds->qstr($this->responsiblemail).
                ",userid=".$this->ds->qstr(getAuthUsername()).
                ",zonefilepath1=".$this->ds->qstr($this->zonepath).
                ",zonefilepath2=".$this->ds->qstr($this->seczonepath).
                ",lastmod=".$this->ds->DBTimeStamp(time()).
                ",slaveonly=".$this->ds->qstr($this->slaveonly).
                " WHERE customer=$cust AND id=".$zoneid );

        // delete all the DNS records first to preserve correct order
        $result=$this->ds->Execute("DELETE FROM zonedns
                WHERE id=$zoneid");

        // add reverse DNS into fwdzone table
        $this->RevAddNS($zoneid);

        return $result;

    }

    function RevAddSOA() {

        // work out new serial numbers
        $this->Serial();

        // Add to DB here.

        $result = $this->ds->Execute("INSERT into zones 
                (customer, zoneip, zone, zonesize, serialdate, 
                 serialnum, error_message, ttl, refresh, retry, expire, minimum, 
                 lastmod, responsiblemail, userid, zonefilepath1, 
                 zonefilepath2, slaveonly) ".
                "VALUES ($this->cust, $this->zoneip,".
                $this->ds->qstr($this->zone).", $this->size,".
                $this->ds->qstr($this->serialdate).", $this->serialnum,".
                $this->ds->qstr("E").",".
                $this->ttl.",".
                $this->refresh.",".
                $this->retry.",".
                $this->expire.",".
                $this->minimum.",".
                $this->ds->DBTimeStamp(time()).",".
                $this->ds->qstr($this->responsiblemail).",".
                $this->ds->qstr(getAuthUsername()).",".
                $this->ds->qstr($this->zonepath).",".
                $this->ds->qstr($this->seczonepath).",".
                $this->ds->qstr($this->slaveonly).")" );

        // did not fail due to key error?
        // should not fail as we checked this already!
        if ($result) {
            if (DBF_TYPE=="mysql" or DBF_TYPE=="maxsql") {
                $zoneid=$this->ds->Insert_ID();
            }
            else {
                // emulate getting the last insert_id
                $result=$this->ds->Execute("SELECT id 
                        FROM zones
                        WHERE customer=$this->cust AND 
                        zoneip=$this->zoneip");
                $temprow = $result->FetchRow();
                $zoneid=$temprow["id"];
            }

            return $zoneid;
        }

        // key error?
        return 0;

    }

    function RevAdd($cust, $zone, $server) {

        // use local function variables as they may change
        $this->cust = $cust;

        // is the server variable set? then do zone transfer
        // should really only read SOA if slaveonly set
        if (!empty($server)) {
            $answer=$this->ZoneAXFR($this->zone, $server);
            // fatal zone transfer error?
            if ($this->err > 0) {
                return $this->err;
            }
        }

        // could use unique key on database to do check, but requires extra key
        // just to add a new record
        $restemp=$this->ds->Execute("SELECT zone FROM zones
                WHERE customer=$cust AND zone = ".$this->ds->qstr($this->zone));

        if ($restemp->FetchRow()) {
            // domain already exists, fail transaction
            $this->err=-60;
            $this->errstr .= sprintf(my_("DNS Domain %s could not be created - possibly duplicate zone"), $this->zone)."\n";
        }
        else {
            // create the actual zone
            $zoneid=$this->RevAddSOA();
            $this->zoneid=$zoneid;

            // did not fail due to key error?
            if ($zoneid) {
                if(!$this->RevAddNS($zoneid)) {
                    $this->err = -60;
                    return $this->err;
                }

                // now load individual rr records
                // only if not a slavezone and no previous error
                if (!empty($server) and $this->slaveonly=="N" and !empty($answer)) {
                    if(!$this->RevZoneAddRR($zoneid, $answer)) {
                        $this->err = -60;
                        return $this->err;
                    }
                }
                $this->err = 0;
            }
            else {
                $this->err = -60;
                $this->errstr .= sprintf(my_("DNS Domain %s could not be created - possibly duplicate zone"), $this->zone)."\n";
            }
        }

        return $this->err;
    }

    function RevZoneExport($cust, $zoneid) {

        // use local function variables as they may change
        $this->cust = $cust;

        $this->Serial();

        $result = $this->ds->Execute("UPDATE zones ".
                "set serialdate=".$this->ds->qstr($this->serialdate).
                ", userid=".$this->ds->qstr(getAuthUsername()).
                ", lastexp=".$this->ds->DBTimeStamp(time()).
                ", error_message=".$this->ds->qstr("").
                ", serialnum=$this->serialnum ".
                " WHERE customer=$cust AND id=$zoneid");

        if ($result) {

            $sqllastmod = $this->ds->SQLDate("M d Y H:i:s", 'lastmod');
            $result = $this->ds->Execute("SELECT id, zoneip, zonesize, zone, serialdate, 
                        serialnum, ttl, refresh, retry, expire, minimum, zonefilepath1, 
                        zonefilepath2, responsiblemail, customer, $sqllastmod AS lastmod, 
                        userid, slaveonly
                    FROM zones
                    WHERE customer=$cust AND id=$zoneid");
            $row = $result->FetchRow();
            $this->zone=$row["zone"];
            $this->zoneip=$row["zoneip"];
            $this->size=$row["zonesize"];
            $prefix=inet_bits($row["zonesize"]);

            $tmpfname = tempnam (DNSEXPORTPATH, "revzone_".$this->zone."_");
            if(!$tmpfname) {
                $this->err=80;
                $this->errstr .= my_("Could not create temporary file!");
                return;
            }
            $fp = fopen ("$tmpfname", "w");

            // header of document
            $output='<?xml version="1.0" ?>';
            fputs($fp, $output);
            fputs($fp, "\n");

            $ip=inet_ntoa($row["zoneip"]);
            list($octet1,$octet2,$octet3,$octet4) = explode(".",$ip);
            fputs($fp, sprintf('<zone domain="%s" zoneip="%s" zonesize="%s" prefix="%s" slaveonly="%s" octect1="%s" octect2="%s" octect3="%s" octect4="%s">', 
                        $row["zone"], $ip, $row["zonesize"], $prefix,
                        (empty($row["slaveonly"]) ? "N" : $row["slaveonly"]), 
                        $octet1, $octet2, $octet3, $octet4));
            fputs($fp, "\n");

            fputs($fp, sprintf("<path>\n<primary>\n%s\n</primary>\n",
                htmlspecialchars($row["zonefilepath1"])));
            fputs($fp, sprintf("<primaryfile>\n%s\n</primaryfile>\n",
                htmlspecialchars(basename($row["zonefilepath1"]))));
            fputs($fp, sprintf("<primarydir>\n%s\n</primarydir>\n",
                htmlspecialchars(dirname($row["zonefilepath1"]))));

            fputs($fp, sprintf("<secondary>\n%s\n</secondary>\n",
                htmlspecialchars($row["zonefilepath2"])));
            fputs($fp, sprintf("<secondaryfile>\n%s\n</secondaryfile>\n",
                htmlspecialchars(basename($row["zonefilepath2"]))));
            fputs($fp, sprintf("<secondarydir>\n%s\n</secondarydir>\n",
                htmlspecialchars(dirname($row["zonefilepath2"]))));
            fputs($fp, "</path>\n");

            // SOA portion
            fputs($fp, sprintf('<soa serialdate="%s" serialnum="%02d" ttl="%s" retry="%s" refresh="%s" expire="%s" minimumttl="%s" email="%s" />', 
                        $this->serialdate, $this->serialnum, 
                        $row["ttl"],
                        $row["retry"],
                        $row["refresh"],
                        $row["expire"],
                        $row["minimum"],
                        $row["responsiblemail"]
                        ));
            fputs($fp, "\n");

            // nameservers
            $result1 = $this->ds->Execute("SELECT hname FROM zonedns
                    WHERE id=$zoneid
                    ORDER BY horder");

            $cnt=0;
            while($row1 = $result1->FetchRow()) {
                fputs($fp, '<record><NS>');
                fputs($fp, sprintf('<iphostname>%s</iphostname>', $row1["hname"]));
                fputs($fp, '</NS></record>');
                fputs($fp, "\n");

                $cnt++;
            }
            if ($cnt < 2) {
                fclose($fp);
                unlink($tmpfname);

                $this->err=90;
                $this->errstr .= my_("Invalid zone - zone should have at least two name servers defined");
                return;
            }

            // get records from main ipplan ipaddr tables
            $result1 = $this->ds->Execute("SELECT ipaddr.ipaddr, ipaddr.hname
                    FROM base, ipaddr
                    WHERE base.customer = $cust AND
                    base.baseindex = ipaddr.baseindex AND
                    ipaddr.ipaddr >= ".$row["zoneip"]." AND
                    ipaddr.ipaddr <= ".($row["zoneip"]+$row["zonesize"])."
                    ORDER BY ipaddr.ipaddr");

            while($row1 = $result1->FetchRow()) {
                $ip=inet_ntoa($row1["ipaddr"]);

                // ignore blank records
                if (empty($row1["hname"])) {
                    continue;
                }
                // test for valid domain name
                if (!preg_match('/^(([\w][\w\-\.]*)\.)?([\w][\w\-]+)(\.([\w][\w\.]*))?$/', $row1["hname"])) {

                    $this->errstr .= sprintf(my_("Invalid record - ignored: %s %s"), $ip, $row1["hname"]);
                    continue;
                }
                fputs($fp, '<record><PTR>');
                fputs($fp, sprintf('<host>%s</host>', $row1["hname"]));
                list($octet1,$octet2,$octet3,$octet4) = explode(".",$ip);
                fputs($fp, sprintf('<octet1>%s</octet1>', $octet1));
                fputs($fp, sprintf('<octet2>%s</octet2>', $octet2));
                fputs($fp, sprintf('<octet3>%s</octet3>', $octet3));
                fputs($fp, sprintf('<octet4>%s</octet4>', $octet4));
                fputs($fp, "\n");
                fputs($fp, sprintf('<iphostname>%s</iphostname>', $ip));
                fputs($fp, '</PTR></record>');
                fputs($fp, "\n");
            }

            // close zone
            fputs($fp, '</zone>');
            fputs($fp, "\n");

            fclose($fp);
            // give file proper extension
            rename($tmpfname, $tmpfname.".xml");
            @chmod($tmpfname.".xml",0644);

            $this->err=0;
            return $tmpfname.".xml";

        }

        //return $tmpfname;
        // database error?



        /*






        // Update DNS Database Serial Count.  Update Serial Count only when we export.
        $result = $this->ds->Execute("UPDATE fwdzone ".
        "set serialdate=".$this->ds->qstr($this->serialdate).
        ", userid=".$this->ds->qstr(getAuthUsername()).
        ", serialnum=$this->serialnum".
        " WHERE customer=$cust AND data_id=".$zoneid);

        if ($result) {

        $result = $this->ds->Execute("SELECT * FROM fwdzone 
        WHERE customer=$cust AND data_id=$zoneid");
        $row = $result->FetchRow();
        $this->domain=$row["domain"];

        $tmpfname = tempnam (DNSEXPORTPATH, "zone_") or
        myError($w,$p, my_("Could not create temporary file!"));
        $fp = fopen ("$tmpfname", "w");

        // header of document
        $output='<?xml version="1.0" ?>';
        fputs($fp, $output);
        fputs($fp, "\n");
        fputs($fp, sprintf('<zone domain="%s" slaveonly="%s">', $row["domain"], 
        (empty($row["slaveonly"]) ? "N" : $row["slaveonly"])));
        fputs($fp, "\n");

        // SOA portion
        fputs($fp, sprintf('<soa serialdate="%s" serialnum="%02d" ttl="%s" retry="%s" refresh="%s" expire="%s" minimumttl="%s" email="%s" />', 
        $this->serialdate, $this->serialnum, 
        $row["ttl"],
        $row["retry"],
        $row["refresh"],
        $row["expire"],
        $row["minimum"],
        $row["responsiblemail"]
        ));
        fputs($fp, "\n");

        // nameservers
        $result1 = $this->ds->Execute("SELECT hname
        FROM fwddns
        WHERE id=$zoneid
        ORDER BY horder");

        $cnt=0;
        while($row1 = $result1->FetchRow()) {
        fputs($fp, '<record><NS>');
        fputs($fp, sprintf('<iphostname>%s</iphostname>', $row1["hname"]));
        fputs($fp, '</NS></record>');
        fputs($fp, "\n");

        $cnt++;
        }
        if ($cnt < 2) {
        insert($w,textbr(my_("Invalid zone - zone should have at least two name servers defined")));
        }

        $result = $this->ds->Execute("SELECT * FROM fwdzonerec
        WHERE customer=$cust AND data_id=$zoneid
        ORDER BY sortorder");
        // loop through each host record
        while($row = $result->FetchRow()) {
        fputs($fp, sprintf('<record><%s>', $row["recordtype"]));
        fputs($fp, sprintf('<host>%s</host>', $row["host"]));
        // MX records are in format "10 hostname.com" in database field ip_hostname
        if ($row["recordtype"]=="MX") {
            list($preference, $iphost) = explode(" ", $row["ip_hostname"], 2);
            if (is_numeric($preference) and $preference >= 0) {
                fputs($fp, sprintf('<preference>%s</preference>', $preference));
                fputs($fp, sprintf('<iphostname>%s</iphostname>', $iphost));
            }
            else {
                fputs($fp, '<preference>10</preference>');
                fputs($fp, sprintf('<iphostname>%s</iphostname>', $row["ip_hostname"]));

            }
        }
        else {
            fputs($fp, sprintf('<iphostname>%s</iphostname>', $row["ip_hostname"]));
        }
        fputs($fp, sprintf('</%s></record>', $row["recordtype"]));
        fputs($fp, "\n");
    }
    // close zone
    fputs($fp, '</zone>');
    fputs($fp, "\n");

    fclose($fp);
    }

    return $tmpfname;

    */
    }


} // end of class DNSFwdZone


?>
