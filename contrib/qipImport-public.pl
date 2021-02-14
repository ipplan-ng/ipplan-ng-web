#! /usr/local/bin/perl

eval '(exit $?0)' && eval 'exec /usr/local/bin/perl $0 ${1+"$@"}'
&& eval 'exec /usr/local/bin/perl $0 $argv:q'
if 0;

# ============================================================================

# $Id: qipImport.pl,v 0.01 2007/12/12

# Copyright (c) 2007 Duane Walker
# All rights reserved.

# This program is free software; you may redistribute it and/or modify it
# under the same terms as Perl itself.

# ============================================================================
#
# This script reads the QIP-EXPORT CSV (QEF) files and reformats them
# as and SQL script to import into the IPplan database.
#
# perl qipImport.pl [-help]
#

# ============================================================================
# This describes the data formats and mappings from QIP to IPplan along with
# some sample data.
#
# The format is the QIP export files and sample data followed by the IPplan
# table and how the fields map.
# ============================================================================
## Lucent QIP v5.2 Export to IPplan v4.86a
## ----------------------------------------
## QIP Export Directory	admprod	/opt/qip/export
##
## administrators.qef
## -------------------
## admin_id,person_id,database_id,org_id,login_name,pass_word,printer,start_addr,end_addr,business_unit,warning_flag,sort_order,admin_type,create_admin,create_infra,parent_admin,create_dns_rrs,gui_level,max_gui_level,add_users,create_ubi_infra,restrict_cnamemx_zones,restrict_subnet_splits,last_upd_usr,last_upd_dt
## 75,0,49,6,"userid","013d8438ef2ae7caba5c19346e5878619f05"," ",0,0," ",1,0,1,"Y","Y",1,"Y",3,3,"Y",1,0,0,75,11/13/2007 15:17
##
## IPplan users table
## +-------------+-------------+------+-----+---------+-------+
## | Field       | Type        | Null | Key | Default | Extra |
## +-------------+-------------+------+-----+---------+-------+
## | userid      | varchar(40) | NO   | PRI |         |       |
## | userdescrip | varchar(80) | NO   |     |         |       |
## | password    | varchar(40) | NO   |     |         |       |
## +-------------+-------------+------+-----+---------+-------+
##
## login_name -> userid
## 'Full Name' -> userdescrip
## 'xqhSYP5jNKxhQ' -> password (set to Password01)
##
## IPplan grp table
## +------------+----------------------+------+-----+---------+-------+
## | Field      | Type                 | Null | Key | Default | Extra |
## +------------+----------------------+------+-----+---------+-------+
## | grpdescrip | varchar(80)          | NO   | UNI |         |       |
## | grp        | varchar(40)          | NO   | PRI |         |       |
## | createcust | varchar(1)           | NO   |     | N       |       |
## | resaddr    | smallint(5) unsigned | YES  |     | 0       |       |
## | grpopt     | int(10) unsigned     | NO   |     | 0       |       |
## +------------+----------------------+------+-----+---------+-------+
##
## 'Network Team' -> grpdescrip
## 'Network' -> grp
## 'Y' -> createcust
## 1 -> grpopt
##
## IPplan usergrp table
## +--------+-------------+------+-----+---------+-------+
## | Field  | Type        | Null | Key | Default | Extra |
## +--------+-------------+------+-----+---------+-------+
## | userid | varchar(40) | NO   | PRI |         |       |
## | grp    | varchar(40) | NO   | PRI |         |       |
## +--------+-------------+------+-----+---------+-------+
##
## login_name -> userid
## 'Network' -> grp
##
## organizations.qef
## ------------------
## org_id,org_name,org_descr,last_upd_usr,last_upd_dt
## 1,"Company Ltd - Internal",,1,11/21/2002 9:3
## 6,"Company Ltd - External",,1,11/21/2002 9:17
##
## IPplan customer table
## +-------------+----------------------+------+-----+---------+----------------+
## | Field       | Type                 | Null | Key | Default | Extra          |
## +-------------+----------------------+------+-----+---------+----------------+
## | custdescrip | varchar(80)          | NO   | UNI |         |                |
## | customer    | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
## | crm         | varchar(20)          | YES  | MUL | NULL    |                |
## | admingrp    | varchar(40)          | NO   | MUL |         |                |
## +-------------+----------------------+------+-----+---------+----------------+
##
## org_id -> customer
## org_name -> custdescrip
## 'Network' -> admingrp
##
## networks.qef
## -------------
## network_id,net_name,net_addr1,net_addr2,net_addr3,mask_length,org_id,cidr,last_upd_usr,last_upd_dt
## 1,"Area Mappings",10,10,0,16,1,"Y",1,11/23/2004 10:15
##
## IPplan area table
## +-----------+----------------------+------+-----+---------+----------------+
## | Field     | Type                 | Null | Key | Default | Extra          |
## +-----------+----------------------+------+-----+---------+----------------+
## | areaaddr  | int(10) unsigned     | NO   | MUL | 0       |                |
## | descrip   | varchar(80)          | NO   |     |         |                |
## | areaindex | bigint(20)           | NO   | PRI | NULL    | auto_increment |
## | customer  | smallint(5) unsigned | NO   | MUL | 0       |                |
## +-----------+----------------------+------+-----+---------+----------------+
## 10.0.0.0 (167772160)	Private IP Address Space Index 2 Customer 1
##
## IPplan netrange table
## +------------+----------------------+------+-----+---------+----------------+
## | Field      | Type                 | Null | Key | Default | Extra          |
## +------------+----------------------+------+-----+---------+----------------+
## | rangeaddr  | int(10) unsigned     | NO   | MUL | 0       |                |
## | rangesize  | int(10) unsigned     | NO   |     | 0       |                |
## | descrip    | varchar(80)          | NO   |     |         |                |
## | rangeindex | bigint(20)           | NO   | PRI | NULL    | auto_increment |
## | areaindex  | bigint(20)           | NO   | MUL | 0       |                |
## | customer   | smallint(5) unsigned | NO   | MUL | 0       |                |
## +------------+----------------------+------+-----+---------+----------------+
##
## network_id -> rangeindex
## (areaindex) -> areaindex
## net_name -> descrip
## net_addr1,net_addr2,net_addr3 -> rangeaddr
## mask_length -> rangesize
## '1' -> customer
##
## subnet.qef
## -----------
## subnet_id,subnet_addr1,subnet_addr2,subnet_addr3,subnet_addr4,subnet_name,search_name,subnet_mask2,subnet_mask3,subnet_mask4,network_id,ospf_id,subnet_org_id,reclaim_day,reclaim_hr,assign_flag,end_ip_addr2,end_ip_addr3,end_ip_addr4,loc_id,appl_id,contact_id,sn_desc,ftp_svr_name,hardware_type,bootp_id,temp_id,check_use,show_used,last_address,last_upd_usr,last_upd_dt
## 1817,10,179,130,44,,,255,255,252,32,0,0,0,0,"Y",179,130,45,0,0,0,,,0,0,0,"N","N",179536431,1,3/25/2003 11:25
##
## IPplan base table
## +------------+----------------------+------+-----+-------------------+----------------+
## | Field      | Type                 | Null | Key | Default           | Extra          |
## +------------+----------------------+------+-----+-------------------+----------------+
## | baseaddr   | int(10) unsigned     | NO   | MUL | 0                 |                |
## | subnetsize | int(10) unsigned     | NO   |     | 0                 |                |
## | descrip    | varchar(80)          | NO   |     |                   |                |
## | baseindex  | bigint(20)           | NO   | PRI | NULL              | auto_increment |
## | admingrp   | varchar(40)          | NO   | MUL |                   |                |
## | customer   | smallint(5) unsigned | NO   | MUL | 0                 |                |
## | lastmod    | timestamp            | NO   |     | CURRENT_TIMESTAMP |                |
## | userid     | varchar(40)          | YES  |     | NULL              |                |
## | baseopt    | int(10) unsigned     | YES  |     | 0                 |                |
## | swipmod    | datetime             | YES  |     | NULL              |                |
## +------------+----------------------+------+-----+-------------------+----------------+
##
## 170878976,256,Queen St level 1 Subnet,1,Network,1,2007-11-23 14:37:15,userid,0,NULL
##
## subnet_id -> baseindex
## subnet_addr1,subnet_addr2,subnet_addr3,subnet_addr4 -> baseaddr
## '255',subnet_mask2,subnet_mask3,subnet_mask4 -> subnetsize
## subnet_name -> descrip
## 'Network' -> admingrp
## '1' -> customer
## 'userid' -> userid
##
## obj_prof.qef
## ------------
## obj_id,obj_ip_addr1,obj_ip_addr2,obj_ip_addr3,obj_ip_addr4,org_id,domn_id,subnet_id,alloc_type_cd,dual_prof_cd,obj_class_cd,appl_id,mac_id,model_type,serial_num,asset_no,host_id,flag,server_type,bootfile_name,purchase_dt,obj_desc,exp_dt,contact_id,auth_name,bootp_flag,loc_id,room_id,object_tag,ns_usage,ns_update_flags,lease_time,bootp_id,temp_id,client_class,default_name,min_time,user_class,last_upd_usr,last_upd_dt
## 22353,160,7,8,33,1,2,258,1,0,5,0,0,,,,,144,0,,,"App/Web Server 4",,0,,0,0,,,1,63,-1,0,0,,"oep04",-1,,1,10/1/2004 15:27
##
## IPplan ipaddr table
## +-----------+------------------+------+-----+-------------------+-------+
## | Field     | Type             | Null | Key | Default           | Extra |
## +-----------+------------------+------+-----+-------------------+-------+
## | ipaddr    | int(10) unsigned | NO   | PRI | 0                 |       |
## | userinf   | varchar(80)      | YES  |     | NULL              |       |
## | location  | varchar(80)      | YES  |     | NULL              |       |
## | telno     | varchar(15)      | YES  |     | NULL              |       |
## | descrip   | varchar(80)      | YES  |     | NULL              |       |
## | hname     | varchar(100)     | YES  |     | NULL              |       |
## | macaddr   | varchar(12)      | YES  |     | NULL              |       |
## | baseindex | bigint(20)       | NO   | PRI | 0                 |       |
## | lastmod   | timestamp        | NO   |     | CURRENT_TIMESTAMP |       |
## | lastpol   | datetime         | YES  |     | NULL              |       |
## | userid    | varchar(40)      | YES  |     | NULL              |       |
## +-----------+------------------+------+-----+-------------------+-------+
##
## 173690960,,Level 6 Test Lab 388 Queen St,,,cacti.int.corp.sun,,73,2007-12-12 11:57:01,NULL,userid
##
## (base subnet index) -> baseindex
## obj_ip_addr1,obj_ip_addr2,obj_ip_addr3,obj_ip_addr4 -> ipaddr
## obj_desc -> descrip
## 'userid' -> userid
##
## obj_alias.qef
## --------------
## obj_id,obj_alias_name,domn_id,search_name,last_upd_usr,last_upd_dt
## 26027,"citrixpri",2,"CITRIXPRI",65,1/15/2003 8:53
##
## I didn't find an IPplan equivalent to alias.
# ============================================================================
# ============================================================================

use strict;
use DBI();
use DBD::ODBC;
use Getopt::Long;
use IO::Handle;

my $helpFlag;

#This is the directory where the QIP-EXPORT files have been dumped
my $qipFilePath = "C:\\Documents and Settings\\userid\\My Documents\\QIP\\";

GetOptions(
			"help"			=>		\$helpFlag,
			"path=s"		=>		\$qipFilePath,
);

if ($helpFlag){
   usage();
}

#Connecting to the IPplan database by ODBC
#Need to setup the ODBC connection in control panel
#I couldn't get the MySQL client on my PC
my $datasource = q/dbi:ODBC:ipplan/;
my $user = q/ipplan/;
my $pass = q/ipplan99/;

my $dbh = DBI->connect($datasource, $user, $pass)
		or die "Can't connect to $datasource: $DBI::errstr\n";

#Some general variables
my $fn;
my $qry;
my $count;

#
#Outfile is a new file that will contain the SQL Script to import the QIP Data
#
my $outfile = 'ipplanQIPimport.sql';
my $sql = new IO::Handle;
open ($sql, "> $outfile") or die "Open failed: $!\n";

#-------------------------------------------------------------------------------
#Make sure we work with the IPplan database
#-------------------------------------------------------------------------------
$sql->print("use ipplan;\n\n");

#-------------------------------------------------------------------------------
#Read the QIP organizationbs file and convert to IPplan customers table
#-------------------------------------------------------------------------------
$fn = 'organizations.qef';
my $first = 1;
open (IN, "$qipFilePath$fn") or die "Open failed: $!\n";
$count = 0;
foreach my $line (<IN>){
	if ($first){
		#Skip over the first line (csv header)
		$first = 0;
	} else {
		chomp($line);
		my @items = split(/,/,$line);
		my $descrip = $items[1];
		$descrip =~ s/("|')//g;
		$sql->print("INSERT INTO customer (customer, custdescrip, admingrp) VALUES ($items[0], '$descrip', 'Network');\n");
		++$count;

		#print("$line\n");
	}
}
$sql->print("\n");
print("Converted $count customer(s)\n");

#-------------------------------------------------------------------------------
#Read the QIP administrators file and convert to IPplan users table
#-------------------------------------------------------------------------------
#Create a Group for all the users
$qry = <<_END_OF_TEXT_;
INSERT INTO grp (grp, grpdescrip,createcust,grpopt) values ('Network', 'Network Team', 'Y', 1);
_END_OF_TEXT_

$sql->print("$qry\n");

$fn = 'administrators.qef';
my $first = 1;
$count = 0;
open (IN, "$qipFilePath$fn") or die "Open failed: $!\n";
foreach my $line (<IN>){
	if ($first){
		#Skip over the first line (csv header)
		$first = 0;
	} else {
		chomp($line);
		my @items = split(/,/,$line);
		my $userid = $items[4];
		$userid =~ s/("|')//g;
		#Only interested in userids that start with u or a (ignore case)
		if ($userid =~ /^(u|a)/i){
			#print("$userid\n");
			$sql->print("INSERT INTO users (userid, userdescrip,password) VALUES ('$userid', 'Full Name','xqhSYP5jNKxhQ');\n");
			$sql->print("INSERT INTO usergrp (userid, grp) VALUES ('$userid', 'Network');\n");
			++$count;

			#print("$line\n");
		}
	}
}
$sql->print("\n");
print("Converted $count users\n");



#-------------------------------------------------------------------------------
#Read the QIP networks file and convert to IPplan area and netrange tables
#-------------------------------------------------------------------------------
#Create areas
my $ip;
$ip = Ip2Dec('10.0.0.0');
$qry = <<_END_OF_TEXT_;
INSERT INTO area (areaaddr, descrip, customer) values ('$ip', 'Private IP Address Space', 1);
_END_OF_TEXT_
$sql->print($qry);

#Now make a hash with the first byte of the area ip address so we can index the ranges correctly
my %area;
my $sth = $dbh->prepare("SELECT areaaddr,areaindex FROM area where customer = 1;");
$sth->execute();
while ((my @f) = $sth->fetchrow_array) {
	#print("Area $f[0] Index $f[1]\n");
	#Convert the dec IP to text
	my $textip = Dec2Ip($f[0]);
	#Get the first octet
	if ($textip =~ /^(\d{2,3})\./){
		#print("Text IP $textip First Octet $1\n");
		$area{$1} = $f[1];
	}
}

$fn = 'networks.qef';
my $first = 1;
$count = 0;
open (IN, "$qipFilePath$fn") or die "Open failed: $!\n";
foreach my $line (<IN>){
	if ($first){
		#Skip over the first line (csv header)
		$first = 0;
	} else {
		chomp($line);
		my @items = split(/,/,$line);
		my $index = $items[0];
		my $descrip = $items[1];
		$descrip =~ s/("|')//g;
		if (length($descrip) == 0){
			$descrip = "$items[2].$items[3].$items[4].0";
		}
		$ip = Ip2Dec("$items[2].$items[3].$items[4].0");
		#Get the area index using the first octet to lookup the area hash
		my $areaindex = $area{$items[2]};
		my $rangesize = 2 ** (32 - $items[5]);
		#$sql->print("DELETE FROM netrange where rangeindex = $index;\n");
		$sql->print("INSERT INTO netrange (rangeindex, areaindex, descrip, rangeaddr, rangesize, customer) VALUES ($index,$areaindex,'$descrip',$ip,$rangesize,1);\n");
		++$count;
		#print("$line\n");
	}
}
$sql->print("\n");
print("Converted $count networks\n");


#-------------------------------------------------------------------------------
#Read the QIP subnet file and convert to IPplan base table
#-------------------------------------------------------------------------------
$fn = 'subnet.qef';
my $first = 1;
$count = 0;
my $maxAddresses = Ip2Dec("255.255.255.255");

open (IN, "$qipFilePath$fn") or die "Open failed: $!\n";
foreach my $line (<IN>){
	if ($first){
		#Skip over the first line (csv header)
		$first = 0;
	} else {
		chomp($line);
		$line = replaceEmbeddedCommas($line);
		my @items = split(/,/,$line);
		my $index = $items[0];
		$ip = Ip2Dec("$items[1].$items[2].$items[3].$items[4]");
		my $subnetSize = $maxAddresses - Ip2Dec("255.$items[7].$items[8].$items[9]") + 1;

		#Descrip needs to be unique so append the ip address
		my $descrip = $items[5];
		$descrip =~ s/("|')//g;
		if (length($descrip) > 0){
			$descrip = "$descrip - $items[1].$items[2].$items[3].$items[4]";
		} else {
			$descrip = "$items[1].$items[2].$items[3].$items[4]";
		}
		#$sql->print("DELETE FROM base WHERE baseindex = $index;\n");
		$sql->print("INSERT INTO base (baseaddr, subnetsize, descrip, baseindex, admingrp, customer, userid, baseopt) VALUES ($ip, $subnetSize, '$descrip', $index, 'Network', 1, 'userid', 0);\n");
		++$count;

		#print("$line\n");
	}
}
$sql->print("\n");
print("Converted $count subnets\n");


#-------------------------------------------------------------------------------
#Read the QIP obj_prof file and convert to IPplan ipaddr table
#-------------------------------------------------------------------------------
$fn = 'obj_prof.qef';
my $first = 1;
$count = 0;
open (IN, "$qipFilePath$fn") or die "Open failed: $!\n";
foreach my $line (<IN>){
	if ($first){
		#Skip over the first line (csv header)
		$first = 0;
	} else {
		chomp($line);
		my @items = split(/,/,$line);
		$ip = Ip2Dec("$items[1].$items[2].$items[3].$items[4]");
		my $subnet = $items[7];
		my $descrip = $items[21];
		$descrip =~ s/("|')//g;

		my $name = $items[35];
		$name =~ s/("|')//g;

		$sql->print("INSERT INTO ipaddr (ipaddr, hname, baseindex, descrip, userid) VALUES ($ip, '$name', $subnet, '$descrip', 'userid');\n");
		++$count;
	}
}
$sql->print("\n");
print("Converted $count objects\n");

#Disconnect from the Database
$dbh->disconnect();

$sql->close;

#We are done, exit gracefully
exit 0;

#######################################################################
#
# Give a line of a CSV file replace commas within quotes with a semicolon
# Needed so CSV files can be split on commas
#
#######################################################################
sub replaceEmbeddedCommas{

	my ($line) = @_;

	#print($line."\n");
	my $withinQuote = 0;
	for (my $i = 0; $i < length($line); $i++){
		#Get the next character
		my $ch = substr($line, $i, 1);
		#print("Char $ch\n");

		if ($ch eq '"'){
			if ($withinQuote){
				#print("Leaving Quote\n");
				$withinQuote = 0;
			} else {
				#print("Starting Quote\n");
				$withinQuote = 1;
			}
		} elsif (($ch eq ',') && ($withinQuote)){
			#It is a comma between Quotes
			#print("Starting Line: $line\n");
			my $before = substr($line, 0, $i);
			my $after = substr($line, $i + 1);
			#print("Before $before\n");
			#print("After $after\n");
			$line = $before.';'.$after;
			#print("Replaced Line: $line\n");
		}
	}
	return($line);
}

#######################################################################
#
# Command line options processing
#
#######################################################################

sub usage(){

my $usage = <<_END_OF_TEXT_;

This script reads Lucent QIP export files and converts them to SQL syntax to
import into the IPplan database.

This script converts and populates the IPplan customers, users, areas,
netranges, base and ipaddr tables. It doesn't do any DNS or DHCP
conversions (sorry I didn't need it).

The commands will probably need some editing after generation (that is why
I didn't apply them directly to the database). Apply the script like this:

- telnet/ssh to the database server
- mysql [-f] -u root -p < /path/ipplanQIPimport.sql

I got a few errors so a -f forces it to continue after and SQL error (e.g.
duplicate key).  You may or may not want to force.  Take a backup of the
database first so you can recover it.

MYSQLDUMP --add-drop-table  database -udbuser -pdbpass > ipplanDBdump.sql

usage: perl $0 [-help] -path path-to-qip-files

 -help          : display this text
 -path          : path to the qip export files

example: perl $0 -path /opt/qip/export/

The command line options can be shortened to unique values.
eg. -help can be shortened to -he

_END_OF_TEXT_

    print STDERR $usage;
    exit;
}


#From NeDi inc/libmisc.pl
#===================================================================
# Converts IP addresses to dec for efficiency in DB
#===================================================================
sub Ip2Dec {
	if(!$_[0]){$_[0] = 0}
    return unpack N => pack CCCC => split /\./ => shift;
}

#From NeDi inc/libmisc.pl
#===================================================================
# Of course we need to convert them back...
#===================================================================
sub Dec2Ip {
	return join '.' => map { ($_[0] >> 8*(3-$_)) % 256 } 0 .. 3;
}
