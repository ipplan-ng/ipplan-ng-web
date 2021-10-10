
IPplan - IP address management and tracking

   Revision History
   Revision 4.81 2007-01-23 Revised by: re
   Revision 2.91 2002-05-17 Revised by: re

   IPplan is a free (GPL), web based, multilingual, IP address management and
   tracking tool written in [1]php 4, simplifying the administration of your IP
   address  space. IPplan goes beyond IP address management including DNS
   administration,  configuration  file  management,  circuit  management
   (customizable  via  templates)  and  storing  of  hardware information
   (customizable via templates). IPplan can handle a single network or cater
   for multiple networks and customers with overlapping address space. See the
   introduction section for more.
     _________________________________________________________________

   Table of Contents
   1. [2]Introduction

        1.1. [3]Copyright Information
        1.2. [4]Disclaimer
        1.3. [5]New Versions
        1.4. [6]Credits
        1.5. [7]Feedback
        1.6. [8]Translations

   2. [9]Requirements

        2.1. [10]Databases
        2.2. [11]Additional features

   3. [12]Installation

        3.1. [13]Customization

   4. [14]Downloads, bugs and forums

        4.1. [15]Screenshots

   5. [16]Mode of operation

        5.1. [17]Services company
        5.2. [18]ISP

   6. [19]Concepts

        6.1. [20]Deployment strategy
        6.2. [21]Linking addresses

   7. [22]Administration

        7.1. [23]Admin user
        7.2. [24]Customer access
        7.3. [25]Subnet access
        7.4. [26]Group authority boundaries

   8. [27]Circuit administration, host configuration data and asset information
   9. [28]Device configuration file management
   10. [29]DNS administration

        10.1. [30]Handling exported zones
        10.2. [31]Automatic updating of zone records

   11. [32]Dealing with registrars
   12. [33]Searching

        12.1. [34]Searching for individual address details
        12.2. [35]Searching areas and ranges

   13. [36]Config file
   14. [37]Importing data

        14.1. [38]TAB delimited data
        14.2. [39]Importing using NMAP

   15. [40]Templates

        15.1. [41]Customer, Subnet, DNS and IP address templates
        15.2. [42]Registrar templates

   16. [43]DHCP
   17. [44]Triggers
   18. [45]External command line poller
   19. [46]IP address request system
   20. [47]Authentication schemes
   21. [48]Problems
   22. [49]Limitations
   23. [50]Questions and Answers (FAQ)

   [51]http://sourceforge.net
     _________________________________________________________________

1. Introduction

   IPplan is a web based, multilingual, IP address management and tracking tool
   based on [52]php 4, simplifying the administration of your IP address space.
   IPplan can handle a single network or cater for multiple networks with
   overlapping address space.

   Current functionality includes

     * internationalization
     * importing network definitions from routing tables
     * importing definitions from TAB delimited files and [53]NMAP's XML format
     * multiple administrators with different access profiles (per group,
       allowing access per customer, per network etc.)
     * define address space authority boundaries per group
     * finding free address space across a range
     * split and join networks to make them smaller and larger - ip definitions
       remain intact
     * display overlapping address space between networks
     * search capabilities
     * an audit log - contents before and after change is logged
     * statistics
     * keeping track of and sending SWIP/registrar information
     * DNS administration (forward and reverse zones, import existing zones via
       zone transfer)
     * template system to extend IPplan to contain site specific information
       like circuit data, host configuration data, asset information
     * device configuration file management
     * external stylesheet to change display look
     * triggers - every user event can call a user defined function - useful to
       execute backend DNS scripts
     * external poller - scan subnets for active addresses to gather usage
       statistics
     * IP address request system - allows users to request static IP addresses
       from the database

   Two authentication methods are available - either IPplan's own internal
   authentication scheme, or alternatively make use of any external Apache
   authentication module. This includes single sign on systems like SiteMinder
   or your own scheme based on LDAP, or any other Apache compatible system.
     _________________________________________________________________

1.1. Copyright Information

   This document is copyrighted (c) 2002 Richard E and is distributed under the
   terms of the Linux Documentation Project (LDP) license, stated below.

   Unless otherwise stated, Linux HOWTO documents are copyrighted by their
   respective authors. Linux HOWTO documents may be reproduced and distributed
   in whole or in part, in any medium physical or electronic, as long as this
   copyright notice is retained on all copies. Commercial redistribution is
   allowed and encouraged; however, the author would like to be notified of any
   such distributions.

   All translations, derivative works, or aggregate works incorporating any
   Linux HOWTO documents must be covered under this copyright notice. That is,
   you may not produce a derivative work from a HOWTO and impose additional
   restrictions on its distribution. Exceptions to these rules may be granted
   under certain conditions; please contact the Linux HOWTO coordinator at the
   address given below.
     _________________________________________________________________

1.2. Disclaimer

   No liability for the contents of this documents can be accepted. Use the
   concepts, examples and other content at your own risk. As this is a new
   edition of this document, there may be errors and inaccuracies, that may of
   course be damaging to your system. Proceed with caution, and although this
   is highly unlikely, the author(s) do not take any responsibility for that.

   All  copyrights  are  held by their by their respective owners, unless
   specifically noted otherwise. Use of a term in this document should not be
   regarded as affecting the validity of any trademark or service mark.

   Naming of particular products or brands should not be seen as endorsements.

   Warning

   It is strongly recommended to make a backup of your system before major
   installation or upgrades and to backup at regular intervals.
     _________________________________________________________________

1.3. New Versions

   See the CHANGELOG file for more information.
     _________________________________________________________________

1.4. Credits

   Thanks to [54]ValueHunt Inc. for the use of their layout class used for
   rendering all HTML pages.

   Thanks to [55]AdoDB for the use of their generic database abstraction class.

   Thanks to [56]Vex for their Visual Editor for XML used to generate the
   IPplan documentation.

   Thanks to [57]The PHP Layers Menu System for their menu system.
     _________________________________________________________________

1.5. Feedback

   Feedback  is  most  certainly  welcome for this document. Without your
   submissions  and input, this document wouldn't exist. Please send your
   additions,  comments  and  criticisms to the following email address :
   <[58]ipplan@gmail.com>.
     _________________________________________________________________

1.6. Translations

   See the INSTALL and TRANSLATIONS files on how to enable multilingual support
   and how to do a translation to your own language. Doing a translation does
   not require any programming experience. Current languages supported are
   English, Bulgarian, French - Auto Translation, German - Auto Translation,
   Italian - Auto Translation, Norwegian - Auto Translation, Portuguese - Auto
   Translation and Spanish - Auto Translation.

   Nickola Kolev for the Bulgarian translation - nikky at mnet.bg.

   Conrado Pinto Rebessi for the Brazillian translation - conradopinto at
   yahoo.com.br

   Tadashi   Jokagi   for   the   Japanese   transalation  -  elf2000  at
   users.sourceforge.net

   Vladimir   Leshchenko   for   the  Russian  translation  -  worker  at
   smtn.stavropol.ru
     _________________________________________________________________

2. Requirements

   IPplan requires a working web server installation. Currently the [59]Apache
   web server is preferred, but php as an ISAPI or CGI module on IIS works too
   - follow the appropriate installation instructions in the IPplan directory
   (INSTALL-IIS+MSSQL). Apache works just fine on Windows platforms too. For
   installing Apache on a Windows platform, follow [60]these instructions. Or
   you can use [61]AppServ or [62]WampServer which are complete installation
   packages  for  Apache,  MySQL and PHP for Windows - just add IPplan by
   following the installation instructions in the IPPLAN-WINDOWS file (part of
   IPPlan).
     _________________________________________________________________

2.1. Databases

   IPplan requires a working database installation. The following databases
   currently work:

     * [63]MySQL 3.23.15 or higher (preferred)
     * [64]PostgreSQL 7.1 or higher
     * [65]Oracle 9i or higher (SQL99)
     * Microsoft SQL server (both 7 and 2000)

   The following may work, but are untested - Sybase. In fact, any database
   that supports SQL99 compliant joins, in particular LEFT JOIN, should work.
   See limitations section below for more.

   The web scripting language [66]php 4.1 or higher must also be installed as a
   module in Apache (NOT as a cgi). Php must have the preferred database driver
   compiled in and enabled. See the respective web sites and installation
   documents for more detail. IPplan works just fine with a combination of the
   Apache web server and php on a Windows platform - just read the relevant
   installation instructions for Windows carefully.

   Tip

   IPplan is also known to work in a distributed, replicated MySQL environment
   with multiple database servers. See [67]www.oreilly.com for more
   information.
     _________________________________________________________________

2.2. Additional features

   To enable SNMP support, you will require the [68]ucd-snmp package installed
   and configured in your environment. This must also be activated in the php
   configuration. SNMP support is only required if you wish to read routing
   tables directly from routers.
     _________________________________________________________________

3. Installation

   Follow the instructions for your platform and database in the INSTALL files
   in the IPplan directory.
     _________________________________________________________________

3.1. Customization

   IPplan is customizable in many ways. See the sections on templates, triggers
   and pollers. You can also extend the menu system to include your own custom
   menus  for other systems at your site - see the config.php file for an
   example.
     _________________________________________________________________

4. Downloads, bugs and forums

   You can report bugs, contribute to forums and download it [69]here and look
   at the latest [70]TODO and [71]CHANGELOG.
     _________________________________________________________________

4.1. Screenshots

   You can find some screen shots [72]here.
     _________________________________________________________________

5. Mode of operation

   There  are two modes of operation, one can be classified as a services
   company and the other as an ISP.
     _________________________________________________________________

5.1. Services company

   As  a  services  company  your primary use of IPplan will be to manage
   individual IP address records and the address plan of one or more customers.
     _________________________________________________________________

5.2. ISP

   In ISP mode, you will assign blocks of IP address space to your customers.
   In this mode, you will not be concerned at all with individual IP address
   records and how the customer breaks down his assigned address space. When
   you operate as an ISP, you may also generate SWIP/registrar entries, which
   are only useful if you deal directly with ARIN or any other registrar. (SWIP
   is enabled in the config.php file, see ARIN [73]tutorial for more details).
   All the relevant SWIP/registrar information is entered when the customer is
   created.

   When using this mode, I suggest creating a dummy customer which holds all
   the allocated address space from your regional registrar (ARIN?) already
   broken up into the various blocks that you will eventually assign to your
   customers. All these blocks should be called "free" to allow them to be
   found using the "Find free" menu option. Once you are ready to assign a
   block,  create  a  new  customer  with all the relevant SWIP/registrar
   information completed, go to your dummy customer and move a block of address
   space to the newly created customer, and finally generate a SWIP/registrar
   entry for the new block. In this mode areas and ranges are not too relevant
   except for the dummy customer (see concepts below). You may also need to
   create a template for your registrar in the templates directory. If you have
   done this, feel free to contribute it to IPplan.
     _________________________________________________________________

6. Concepts

   The flow of address management is based on the creation of areas, then
   ranges which belong to areas, and finally, subnets which belong to ranges.
   Actually, only subnets are required, but on large networks it makes logical
   sense to group the network into areas to ease administration and to reduce
   routing updates on the network. There is a jpeg drawing included with the
   distribution that graphically shows these relationships. The methodology
   employed  borrows  significantly  from OSPF routing concepts which are
   explained more fully [74]here.
     _________________________________________________________________

6.1. Deployment strategy

   So in a new installation, first create the areas, then create ranges adding
   them to areas, and finally create subnets. Searching is now a simple matter
   of selecting an area which will display all the ranges for the area, or
   selecting  no area and simply selecting a range from the total list of
   ranges, or simply selecting a base network address.

   Note

   Within a customer or autonomous system, no overlaps of address space is
   allowed. This follows standard IP addressing rules. You can have overlapping
   ranges/aggregates, but the default behaviour of ranges also prevents
   overlaps. This can be changed in the config.php file.

   To handle challenges like NAT or other overlapping address space, you will
   be required to create multiple autonomous systems. See 'Searching' below how
   to see information across multiple autonomous systems.
     _________________________________________________________________

6.2. Linking addresses

   IP  address records can be linked together. This allows one address or
   multiple addresses to reference another address or addresses. Using this
   feature allows for the referencing of NATed addresses or having a link to a
   loopback address of a device. Linking is done on the IP address details page
   by completing the "Linked address" field. Once the field is completed, you
   can follow the link. The link also appears on subnet summary pages.

   You can also link many addresses in one go by choosing multiple addresses in
   the "Select multiple addresses to do a bulk change" window, then completing
   the "User" field as follows:
   LNKx.x.x.x userinfo

   The LNK identifier must be in uppercase, followed by exactly one valid IP
   address  with  no  spaces, then followed by an optional space and user
   description. After the page is submitted, the embedded LNK will vanish.

   Note

   If the destination record of a linked address does not exist, a record will
   automatically get created pointing back to the source address, but only if
   the destination subnet exists. This is to signal the "Find Next Free"
   address logic of the subnet that the destination address is used.
     _________________________________________________________________

7. Administration

   The access control is divided up into three layers and revolves around the
   creation of groups:
     _________________________________________________________________

7.1. Admin user

   Firstly  you will need to create users and groups using the admin user
   defined in the config.php script. The admin user can only be used on the
   admin  pages.  Once you are done with the admin functions, you will be
   required to re-authenticate as one of the newly created users as soon as you
   access functions on the main index page.
     _________________________________________________________________

7.2. Customer access

   When a customer is created, a group must be assigned to the customer. This
   will be the customers admin group and all members of this group can create
   and delete both subnets, ranges, areas and individual IP address records for
   the customer.

   When the subnet is created, the creator will choose a subnet admin group.
     _________________________________________________________________

7.3. Subnet access

   The users assigned to the group that has subnet access can only modify
   individual IP records for that subnet.

   Initially I would create three groups, one group that can create customers,
   one group that can create subnets, areas and ranges, and another group which
   can only modify individual IP records. Normally in large networks the people
   that modify IP records are not the same people that administer routers and
   configure the IP address space.

   If a group is set to see only a particular customer, the same group needs to
   be used for all operations for the customer. The side effect to this is that
   the users assigned to the group have full access to the customer and can
   make any changes to the customers data, including creating and deletion of
   subnets. This is not ideal and will be changed in future.

   Tip

   Groups can be created that prevent certain users from changing an
   administrator defined number of reserved addresses at the start of a subnet.
     _________________________________________________________________

7.4. Group authority boundaries

   Areas of responsibility can be assigned to a group, thus limiting what
   address space a group can create networks in. The default behavior allows
   administration anywhere. Care should be taken when using this feature as
   changing  the boundaries at a later stage may orphan some parts of the
   database and yield data inaccessible.

   Note

   If a user belongs to multiple groups and one of the groups does not have
   boundaries defined, then the user is granted all access. Thus boundaries are
   a sum of all the boundaries the user belongs to.

   Tip

   Bounds are also useful to create users that only have read access to the
   IPplan information. Select the "Read Only" option when creating a new group.
     _________________________________________________________________

8. Circuit administration, host configuration data and asset information

   Using the template capability IPplan can be extended to contain custom
   information about your site. You can add any number of custom fields for
   your site. See the section on "Templates" for further information.
     _________________________________________________________________

9. Device configuration file management

   Any number of files can be attached to individual IP records. Using this
   feature, configuration data (text and binary, drawings etc) for devices can
   be stored and managed by IPplan.
     _________________________________________________________________

10. DNS administration

   Both forward and reverse zones can be created via the web interface. Zone
   domains are forward zones - there can be as many forward zones as you like
   per customer. Each of these can be unique or they can even overlap with
   other customers.

   Forward Zones:

   To create a forward zone, select a customer and select "Add a DNS zone". The
   next screen will allow you to enter information for the new zone. The domain
   name must be entered as must at least two nameservers.

   At this point you have the option of creating a new zone from scratch,
   cloning  (copying)  a  zone  from  an  already  existing  zone  called
   "template.com", or importing an existing zone via a zone transfer. If you do
   a zone transfer, the PRIMARY or SECONDARY DNS servers must be directly
   contactable from the webserver on which IPplan is running. If the DNS server
   is not contactable an error will be returned.

   If a domain is created from scratch, the domain name must be entered as must
   at least two upstream DNS servers. The DNS servers are automatically entered
   for   you   if   the  customer  record  (created  via  "Create  a  new
   customer/autonomous system") contains DNS servers. This is a good way to not
   have to manually add the DNS servers for each new zone created. The DNS
   servers can be changed and will be independent per zone created. At the
   bottom of the Add/Edit screen is place to enter two zone file paths. In
   future this can be used to determine where the zone must be saved or on what
   DNS server the zone must be created - currently these fields do nothing.

   The next step is to add individual records to the newly created zone. Do
   this under the "Zone DNS records" main menu function. Select the customer,
   select the domain and add a host. The "Host name" refers to the left hand
   side of a bind zone file, then the type (A, CNAME or MX - more types in
   future) and the "IP/Hostname" refers to the right hand side of the zone
   file. In future the screen will change depending on the record type you
   select and more record types will be possible. The sort order determines the
   placement of the record in the zone and on the screen. This is a number and
   the default is 9999 or the end of the file. If you want to insert records
   between other records, work out a numbering plan.

   In future this will be automatic with options to "Insert before" and "Insert
   after". Currently you can renumber the values retaining the order of the
   entries.

   Reverse zones:

   To create reverse zones is very much like creating a forward zone, except
   that  there are no detail records. All that is required is to create a
   starting address and mask. The actual reverse records are extracted based on
   the start and mask from the IP records when you create a subnet and add
   records to the subnet. The field used is the host name field and all invalid
   information in this field will be ignored with a warning.

   Once your forward and reverse zones are created, each time a change is made
   you will be required to export the zone by clicking on the "Export zone"
   option. The output generated is in XML and must then be parsed using and
   XSLT processor into a format compatible with your DNS server.
     _________________________________________________________________

10.1. Handling exported zones

   The zone files are created in the directory specified by the DNSEXPORTPATH
   variable in the config.php file. The files are in XML format and are created
   when a user hits the Export option either on the DNS page or Reverse zone
   page. The files have a format of zone_ or revzone_ followed by the zone name
   followed  by  a  trailing unique identifier, which is operating system
   dependent. The file has a .xml extension.

   If a template is attached to the forward zone, the template fields will also
   appear in the exported XML file with tag names the same as the template
   field names.

   These files must be processed using a XML stylesheet processor into a format
   suitable for your DNS server, and then placed into the correct location and
   activated by your DNS server. This is beyond the scope of IPplan and will
   require custom scripts for your installation. Contributions are welcome.

   Sample procedure:

   You will require a script for your environment that periodically runs to
   check for new zone files that have been added to the output directory. You
   will probably use cron to do this. Once your script finds a file, you can
   extract the file paths saved in IPplan using a simple grep:

   grep -A 1 '<primary>' /tmp/revzone_FS9mEU|grep -v '<primary>'

   This gives me the primary file path. Once you have the destination path,
   process the file and copy the output by whatever means your environment uses
   to the target DNS server. I would suggest using scp with a public key on the
   remote server to prevent having to type in user id's and passwords during
   the copy process.

   Processing the file:

   A sample XSLT stylesheet can be found in the contrib directory to transform
   the forward zone XML (files starting with zone_) into a bind8 or higher
   compatible  zone  file.  I  use  xsltproc  from  the libxslt package (
   [75]http://xmlsoft.org/XSLT/) which should be installed on most modern linux
   systems. A different stylesheet (.xsl file) will be required for each DNS
   server system that you use - I have no intention of writing style sheets for
   all the various DNS servers out there, but you are more than welcome to send
   me style sheets for different DNS servers to be included with IPplan.

   A sample command is:

   xsltproc bind9_zone.xsl zone_

   For sample XML input of:

<?xml version="1.0" ?>
<zone domain="test.com">
<soa serialdate="20040626" serialnum="04" ttl="21600" retry="3600" refresh="864
00" expire="604800" minimumttl="21600" email="" />
<record><NS><iphostname>ns1.example.com</iphostname></NS></record>
<record><NS><iphostname>ns2.example.com</iphostname></NS></record>
<record><NS><iphostname>ns3.example.com</iphostname></NS></record>
<record><NS><iphostname>ns4.example.com</iphostname></NS></record>
<record><A><host>myhost</host><iphostname>10.10.10.1</iphostname></A></record>
<record><CNAME><host>myhost-alias</host><iphostname>myhost</iphostname></CNAME>
</record>
<record><MX><host></host><iphostname>mailhost</iphostname></MX></record>
</zone>

   Generating output as follows:

$ORIGIN test.com.
$TTL 86400
@       IN      SOA     test.com.       dnsadmin.test.com. (
                        2004062604 ; serial
                        21600      ; refresh
                        3600       ; retry
                        604800     ; expire
                        21600 )    ; minimum TTL

        IN      NS      ns1.example.com.
        IN      NS      ns2.example.com.
        IN      NS      ns3.example.com.
        IN      NS      ns4.example.com.
myhost  IN      A       10.10.10.1
myhost-alias    IN      CNAME   myhost
        IN      MX      10      mailhost
     _________________________________________________________________

10.2. Automatic updating of zone records

   IP subnet records (which equate to zone PTR records) and forward zone A
   records will automatically get syncronised and updated provided a number of
   criteria are fulfilled.

   If a DNS A record is created or updated, and there is exactly one A record
   across all the customers zones matching one IP subnet record, then the IP
   record hostname field will be updated with the A record hostname field.

   If an IP record hostname field is updated, then the zone A record field will
   be updated if there is exactly one A record matching the IP record across
   all the customers zones.

   If an IP record hostname field is updated and a matching A record cannot be
   found, then an A record will automatically be created in the matching domain
   provided there is only one matching domain. This will only happen if the
   DNSAUTOCREATE setting is TRUE in config.php.

   Under all the above conditions a warning message will be displayed stating
   that  an  update occured. The appropriate log entries will be made and
   triggers will fire.
     _________________________________________________________________

11. Dealing with registrars

   Registrars are interacted with by email. IPplan can manage all the records
   and  manage all the fields and data required to generate the registrar
   updates, no matter what the registrar is (ARIN, RIPE, APNIC etc). Before
   these functions will work, the correct config variables need to be set -
   these include the REGENABLED, MAINTAINERID, REGISTRY, REGEMAIL etc. Only
   users  belonging  to  the customer admin group can send these updates.
   Additional fields can be added to the IPplan display pages if required, and
   these fields are also available to the registrar templates.

   To generate updates, the required registrar template is selected. IPplan
   includes a limited number of templates, but these are fully customizable and
   simple to create and modify - see the section on "Templates" later in the
   manual. Once the template is selected, the fields from the database are
   substituted  into the template file and the output is displayed on the
   screen. The user then selects which updates should be sent to the registrar.
   The selected updates are then sent via email and the date the updates were
   sent are recorded in the IPplan database.
     _________________________________________________________________

12. Searching

   Creating a special customer called 'All' allows searching for information
   across all the available customer/autonomous systems using the 'Display
   subnet' function. This special customer can contain areas and ranges that
   limit the scope of searches, just like normal customers. Using this feature
   allows a user to see the entire network picture in one view.

   Tip

   When creating new subnets, it is also beneficial to create unused subnets
   with a a description of either 'free' or 'spare'. These can be searched for
   at a later stage using the 'Find Free' function.

   Tip

   It may also be beneficial to give ASE (Autonomous System External, networks
   not local to yours) a special handle like EXTERNAL so that they can be
   searched for at a later stage. These networks often appear in routing tables
   as static routes to third parties (not via the Internet).
     _________________________________________________________________

12.1. Searching for individual address details

   Searching can also be done on individual addresses using the 'Match any IP
   address in subnet' option of the 'Display subnet information' option. This
   is useful for finding which networks, either for a single customer, or for
   all customers an IP address belongs to. Using this option makes it easy to
   find the offending network in a complaint situation if you are an ISP.

   If matching by IP address, you will automatically jump to the IP address
   edit page if the search is unique and matches only one subnet from one
   customer.  If you use the 'All' customer you will need to click on the
   relevant customer network you wish to work with.
     _________________________________________________________________

12.2. Searching areas and ranges

   You can also create areas and ranges to search across only certain address
   space ranges. Areas are containers for ranges. Selecting an area that has
   ranges attached will search only in those ranges. Select an area and not
   selecting a range will search across all the ranges in the area.

   Depending on what settings have been selected in the config.php file, ranges
   may either never overlap (the default), have overlaps within an area only,
   or overlap in anyway, including having duplicate ranges.

   Note

   Areas with no attached ranges will not display in the "Area" selection list
   until ranges are added to the area. Areas with no ranges yeild no search
   results.
     _________________________________________________________________

13. Config file

   A a number of settings that can be changed in the config.php file. These
   include the database connection information, admin user and password, and
   the number of lines displayed in tables. See the comments in the config.php
   file for more details.
     _________________________________________________________________

14. Importing data

   Data can be imported by the admin user via TAB delimited text files or from
   output generated in XML format by [76]NMAP.
     _________________________________________________________________

14.1. TAB delimited data

   Network  definitions  or  individual ip records can be imported in TAB
   delimited format.

   The order of columns for network definitions or subnet descriptions are
   (three columns required): The first column contains the IP base address, the
   second the description and the third the mask either in dotted decimal
   format or in bit format.

   The  order  of columns for importing IP records should be (six columns
   required): The first column contains the IP address, the second the user,
   the third the location, the fourth the description, the fifth the hostname
   and the sixth the telephone number.

   If you have more than six columns, the remaining columns will be entered
   into the user defined fields specified in the template. The order will be
   the order the fields are defined in the XML template file.
     

   See the templates section for details on how the templates work.
     _________________________________________________________________

14.2. Importing using NMAP

   A typical application for this would be to obtain data for networks that
   there are no records for. NMAP would be used to obtain the host info of all
   the addresses that are active on the network. Once this is done, the data
   can be read into IPplan. The NMAP parameters required by IPplan to generate
   a valid import file are:
   Â -sP -oX output.xml

   To speed up the process, you can add -n to not resolve host names. The
   import process also understands the -O operating system detection parameter.
   To use -O, you will need to drop the -sP parameter. Using -O increases
   scanning time significantly.

   As of version 3.93 of nmap, the -sP option also returns the MAC address
   which will be recorded in the MAC address field of the subnet IP record. MAC
   addresses only appear if the scan was done using root user - thus the MAC
   address will not appear if nmap was executed through php and the webserver.
   You can set the S bit on the nmap binary, but this is not advised due to
   security concerns.
     _________________________________________________________________

15. Templates

   Various forms of templates are available. These include templates that add
   additional, custom fields to the IPplan display pages (different templates
   can be added to the customer, subnet and ip address record pages), and
   templates that then munipulate the data contained in the IPplan databases to
   generate output for various registrars. All of these templates are fully
   administrator customizable with display fields added to customer and subnet
   pages automatically available in output templates used to generate registrar
   updates.
     _________________________________________________________________

15.1. Customer, Subnet, DNS and IP address templates

   The  administrator can define a custom template in IPplan to allow the
   addition of as many custom fields to the customer, subnet, DNS forward zones
   and ip address record pages as the administrator wishes. This functionality
   allows flexibility in the way data is added to the database. Some uses for
   these  templates  include  tracking  asset  information or WAN circuit
   information, the management of DHCP address space, dealing with additional
   and ever changing registrar requirements.

   As mentioned there are different template files for the customer, subnet,
   DNS  and  IP address pages. The template for each of these pages has a
   different name:

   Customer pages - custtemplate.xml

   Subnet pages - basetemplate.xml

   DNS forward zones - fwdzonetemplate.xml

   IP address pages - iptemplate.xml

   IP address pages (network address) - iptemplate-network.xml

   A  sample  template  called  iptemplate.xml.sample can be found in the
   /ipplan/templates/display directory.

   If  a  template called called iptemplate-network.xml exists, then this
   template  will be used for network addresses (the first address of the
   subnet). This is useful to define subnet wide information like VLAN id's,
   telco line numbers, router configurations etc. If the iptemplate-network.xml
   does not exist, then the iptemplate.xml template will be used for network
   addresses.

   The above templates are generic and act across all customers, and thus all
   fields are the same for all customers. Unique templates can be created per
   customer by adding the customer id to the template name. To obtain the
   customer id (a number), use the Admin/Maintenance function within IPplan. To
   create a unique IP address template for customer id 25, place a template
   file called iptemplate-25.xml in the /ipplan/templates/display directory.
   This template will override the default template for customer 25 only.
     _________________________________________________________________

15.1.1. Format of Customer, Subnet and IP address template files

   The templates are well formed XML files with a .xml extension and should be
   stored  in the /ipplan/templates/display directory. You should use the
   xmllint tool to test the XML file before using it with IPplan - no errors
   should be returned.

   The standard iptemplate.xml file adds a free form text field to all the ip
   address record pages and has this definition:
<?xml version="1.0" ?>
<TEMPLATE>
    <FIELD>
     <DEFINITION NAME="info" DESCRIP="Additional information" TYPE="T" MAXLENGT
H="10000" SIZE="80" ROWS="10" REGEX="" ERRMSG="Invalid field: Additional inform
ation" />
    </FIELD>
</TEMPLATE>

   A field definition to add a select drop down list:

    <FIELD>
     <DEFINITION NAME="select" TYPE="S" DESCRIP="This is select list with two o
ptions" DEFAULT="2">
       <SELECT OPTION="Option 1" VAL="1" />
       <SELECT OPTION="Option 2" VAL="2" />
     </DEFINITION>
    </FIELD>

   The template file must be surrounded with <TEMPLATE> statements. Note that
   XML tags are case sensitive and must match as per the example. Each field is
   contained in a <FIELD></FIELD> statement. Each field must have exactly one
   <DEFINITION> line. Any line with an error will be silently ignore! The
   following are valid for a definition:

   NAME

   This is the field name used internally to track the names of the variables.
   Can contain letters and numbers only - no spaces or anything else.

   DESCRIP

   This is the description that will be displayed above the field.

   TYPE

   This is the type of the field. C for a character field and T for a memo or
   multi-line field, S for a select drop down list.

   DEFAULT

   The default value for the field the first time the field is completed.

   MAXLENGTH

   This is the maximum number of characters a field can consist of.

   SIZE

   This is the display length of the field on the screen - if SIZE is less than
   MAXLENGTH, the field will scroll. SIZE may not be more than MAXLENGTH.

   ROWS

   This is the number of rows to display on the screen - only valid for text or
   multi-line fields.

   REGEX

   This is a regular expression to test validity of the entered information. To
   test a field, use ^ and $ to signify the start and end of the field, so
   something like
   ^[a-zA-Z0-9]$

   ensures  that  the  field  only  contains  numbers  and  letters.  See
   http://regexlib.com/ for more on regular expressions.

   Regular  expressions  are PERL compatible and use a / character as the
   delimiter, thus if you want to match on a /, you will need to escape the /.
   So to match a letter, / and a number, this is the expression
   ^[a-zA-Z\/0-9]$

   Regular expression | can be used for multiple matches. Thus the following
   will match a field or a blank entry
   ^[a-zA-Z\/0-9]$|^$

   ERRMSG

   This is the error message that is displayed if the regular expression match
   fails.

   Default template:

   The  default  template  contains  one  field  which is the "Additional
   information" field of older versions of IPplan. If you do not want this
   field, change the template or delete the template entirely.

   Template rules:

   If you delete the template all fields will vanish and cannot be accessed. IP
   records that have been completed with fields from a template will not be
   lost, but records that do not have template fields cannot have template
   fields added until the template is either restored or similar fields are
   added.

   Note

   Deleting fields from a template results in existing fields in the database
   with template data using a default field definition. The data is not lost.
   It is not a good idea to modify or change the template on a production
   system. Plan the template fields carefully before implementing.
     _________________________________________________________________

15.2. Registrar templates

   Templates for sending information to registrars should be well formed XML
   stylesheets. See the existing templates in the /ipplan/templates directory
   for examples. Currently only the
   <xsl:value-of select="variable"/>

   tag is understood and all other XML tags are stripped, thus only variable
   substitution is done. This is to prevent requiring the XSLT php library to
   be compiled in. In future XML stylesheets will be supported in full.

   There are a number of special variables available during the registrar
   output process which are defined in the config.php file:

source      -> the REGISTRY config setting
maint       -> the MAINTAINERID config setting
regid       -> the REGID config setting
password    -> the REGPASS config setting
date        -> todays date in UTC format

   in addition to the standard customer page variables:

ntsnum      -> starting of IP address range
ntenum      -> end of IP address range
ntname      -> network name as defined in the
               subnet description field
DNS:
hname1      -> DNS hostname 1
ipaddr1     -> Primary DNS server
hname2      -> DNS hostname 2
ipaddr2     -> Secondary DNS server
hname3      -> DNS hostname 3
ipaddr3     -> Third DNS server
   .
   .
   .
hname10     -> DNS hostname 10
ipaddr10    -> Thenth DNS server

Contact:
org         -> Organization
street      -> Street
city        -> City
state       -> State
zipcode     -> Zip code/Postal code
country     -> Two letter country code

Technical contact:
nichandl    -> Nickname/handle
lname       -> Lastname
fname       -> Firstname
mname       -> Middle name
torg        -> Organization
tstreet     -> Street
tcity       -> City
tstate      -> State
tzipcode    -> Zip code/Postal code
tcntry      -> Two letter country code
phne        -> Phone number
mbox        -> Email address

   Plus any variables defined in a customer template (custtemplate.xml) and
   also the subnet template (basetemplate.xml) as described above will also be
   available. The names of the variables will be the same as defined in the
   customer template.

   Thus the following basetemplate.xml file:

<?xml version="1.0" ?>
<TEMPLATE>
    <FIELD>
     <DEFINITION NAME="remarks" DESCRIP="Remarks" TYPE="C" MAXLENGTH="100" SIZE
="100" ROWS="1" REGEX="" ERRMSG="RIPE remarks" />
    </FIELD>
</TEMPLATE>

   will provide the following additional variable to the registrar template

   remarks        -> Remarks field for subnet

   Note

   If there are duplicate variable names defined, the variables in the
   basetemplate.xml file, then the custtemplate.xml file appearing in the
   "Additional information" section of the customer and subnet administration
   pages will have precedence over any standard variables.
     _________________________________________________________________

16. DHCP

   A special sample template called basetemplate.xml.dhcp can be used in place
   of the basetemplate.xml file to provide DHCP support. This template works in
   conjunction with the DHCP export function of IPplan. For a DHCP export to
   work, the subnet must have the correct template, the subnet must be marked
   as DHCP and subnet address descriptions must start with the 'DHCPRESERVED'
   config.php variable. Once an export has been initiated, the resulting output
   must be processed via an XML parser just like any other export function of
   IPplan.

   Multiple address pools can be simulated by adding numbers to the end of the
   IP address decsriptions eg. 'Reserved - DHCP pool 1'. Static assignments are
   dealt with automatically if an IP address has a valid MAC address assigned.
     _________________________________________________________________

17. Triggers

   Every database event (create a customer, update an ip record, export a DNS
   zone file etc) in IPplan can trigger an external user defined function or
   script. This is useful to update and external system like a DNS server once
   something in IPplan has changed.

   You will be required to write your own custom script to do the required
   action. This script will be called from the user_trigger() function in the
   ipplanlib.php script. See the comments of this function for more details.
     

   A list of all the IPplan triggers and what information is passed to the
   user_trigger() function can be found in the TRIGGERS file.
     _________________________________________________________________

18. External command line poller

   The poller allows you to periodically scan subnets for addresses that are
   active  on  the network. This information is then logged in the IPplan
   database and will appear on the display subnet page. You can find the poller
   script in the IPplan contrib directory.

   The scans are done using nmap, thus large networks can be scanned rapidly.
   Subnets  that are to be scanned get entered into a plain text file, so
   maintenance is easy. Polling can be automated by adding the poller to cron.

   Firstly,  you  will  need  to  create  a  file  containing  a  list of
   networks/addresses that you would like to poll. The file is a text file with
   one address per line in the any format that the nmap command understands
   (type nmap -h for more info).

10.10.10.0/24
10.10.11.*
10.12.12.1

   Once you have created your file you will need to make sure the poller is
   configured correctly. Edit the poller file and change the path at the top to
   the correct place where the command line php can be found on your system.

   You can find the location by typing

   which php

   Next make sure that nmap can be found. Type

   which nmap

   and either update the NMAP statement in the IPplan config.php file, or
   uncomment the NMAP define at the top of ipplan-poller.php and update to
   reflect the correct path to nmap. This define will override whatever is in
   config.php  allowing  a  different NMAP to be used. You may also leave
   config.php blank to prevent scanning from within the IPplan web frontend.

   Now run the ipplan-poller.php file with the -d option, or navigate to the
   admin->maintenance page. This will dump a list of customers and customer
   id's. You need to find the id of the customer you want to update:

   php -q ipplan-poller.php -d

ID      Description
2       Test
46      Test customer
47      Test customer 2

   Finally run the poller again with the correct command line (assuming you
   want to update the customer called "Test customer" which has id 46):

   php -q ipplan-poller.php -c 46 -f nmap.list

   If the configuration is correct and there are no errors, there should be no
   output - the database is updated silently in the background. Any address
   that was successfully polled will now have a key in the "Pol" column on the
   IPplan display pages.

D polled today
W polled within last week
M last month
Y last year

   You can now add the above line to cron to scan certain subnets periodically.

   The following command line options are available with the poller:

IPplan poller v1.0

  -h        this message!
  -q        suppress check if tool is executed from the command line
  -d        dump a list of customers and customer id's
  -f        filename containing list of subnets to scan, one per line in addres
s/bits format
                see the NMAP manpage for examples
  -hostnames    resolve and populate hostnames
  -time     timestamps the scan at start and completion
  -a        create auditlog entries for newly added records

  -c        customer id to update

  example:  php ipplan-poller.php -d
            php ipplan-poller.php -time -hostnames -f /tmp/nmap.lst -c 1

   Using the -hostnames option will query the DNS when polling addresses and
   update the host name field within IPplan with the DNS name. Note that using
   this option could significantly increase polling time, especially with
   incorrectly configured DNS servers. Use the -time option to determine impact
   of using the -hostnames option.

   Note

   Security: You should use a different user and password to access the
   database using the command line poller. I suggest creating a user that can
   only SELECT, UPDATE and INSERT into the ipaddr table, and SELECT from the
   base and customer tables. See your database administrator or manual for more
   details.
     _________________________________________________________________

19. IP address request system

   IPplan  has  a request feature whereby users can request individual IP
   addresses by completing a form. This form is then submitted into the IPplan
   database  and  via  email to an administrator or help desk account for
   processing. When the administrator processes the request, all outstanding
   requests for a particular customer/AS will appear on the IP address modify
   screen. The administrator picks the relevant address from the list and all
   the  fields  are automatically completed with the request details. The
   administrator can then change some of the fields details before submitting.
   When the form is submitted, the request is deleted.

   Up  to  100  outstanding  requests  can be active at one time (this is
   configurable  by changing a variable at the start of the requestip.php
   script). This is to prevent denial of service on the database as the request
   page is not authenticated. Excess junk requests can be cleared via an admin
   only option on the maintenance page.

   Request email addresses are set in the config file. The list of visible
   customers/AS's that addresses can be requested for are also set via config
   options. The default is that addresses can be requested for all customers.

   The request page also has the top menu structure disabled by default (this
   can be enabled by changing a variable at the start of the requestip.php
   script). Disabling the menu allows the page to be used as a generic request
   page on a sites intranet server without confusing regular users with menu
   options they cannot use or access.

   Tip

   If an email field exists in the iptemplate.xml file, this field will be used
   to popup an email window, allowing the newly actioned requested address to
   be emailed back to the requester. The email window will only display after
   Submit has been pressed and the user has entered a valid email address.

   A sample definition for this field is as follows:
<FIELD>
 <DEFINITION NAME="email" DESCRIP="Email address" TYPE="C" MAXLENGTH="100" SIZE
="100" ROWS="1" DEFAULT="" REGEX="" ERRMSG="Invalid field: Additional informati
on" />
</FIELD>

   See the section on templates for more information.

   If a valid email address is found in the above field you will be given the
   option to email the details back to the requester. The gateways are site
   specific and can vary - manual completion of this field is required unless
   one of the subnet addresses has a description field starting with GW. This
   address will then be used as the gateway address in the email.
     _________________________________________________________________

20. Authentication schemes

   IPplan  supports  either its own internal authentication scheme, or an
   external scheme based on the Apache webservers authentication modules. To
   use an external scheme, change the setting in the config.php file. Next,
   place  the  relevant  .htaccess  file  in the IPplan user subdirectory
   (/ipplan/user). Do not place an equivalent file in the admin subdirectory as
   the admin account cannot be overridden.

   Note

   When using the external authentication method, IPplan never prompts for a
   userid and password. It is the responsibility of the external module to do
   the prompting, if any. IPplan uses the credentials supplied and matches them
   to the IPplan userid records to determine if access should be granted.

   Important

   The relevant users requiring access to IPplan must still be created via the
   IPplan admin interface, but no password information is required as this is
   overridden by the external authenticator.

   If the user is removed from the external authenticators database (ldap,
   radius etc), the user will no longer be able to log in to IPplan even if the
   account still exists in IPplan. This scheme only handles single signon and
   password changes, not single point of administration.

   Make sure that the external authenticator only returns the userid in the php
   REMOTE_USER variable. Ldap (or auth_ldap) by default will return the entire
   DN, but this can be configured to return only the userid. From the auth_ldap
   docs at [77]http://www.rudedog.org/auth_ldap/1.6/auth_ldap.html ensure that
   the AuthLDAPRemoteUserIsDN is set correctly. You will also need to look at
   the AuthLDAPGroupAttributeIsDN attribute.

   Read the instructions in config.php carefully for debug tips.

   External  authentication  was tested against SiteMinder and the Apache
   [78]auth_ldap module. The [79]CAS authentication module is also supported -
   there are special config.php variables to support this configuration.

   Warning

   THE HTTP BASIC AUTHENTICATION SCHEME DOES NOT ENCRYPT USER-IDS AND PASSWORDS
   TRANSMITTED TO THE WEBSERVER - IT IS RECOMMENDED THAT IPPLAN IS INSTALLED ON
   AN SSL PROTECTED WEBSERVER ON PRODUCTION SYSTEMS.
     _________________________________________________________________

21. Problems

     * PHP's  IP management functions and binary arithmetic functions are
       seriously broken (see [80]bug report logged) Due to this limitation, the
       biggest subnet that can be created is limited to 254k hosts.
     * This brokenness also prevents the use of the ip2long() and long2ip()
       functions introduced with php 4.x. I have used php only functions called
       inet_aton() and inet_ntoa().
     * SNMP appears to be broken on Redhat 7.2 systems. Try following these
       [81]instructions for help.
     _________________________________________________________________

22. Limitations

     * Due to the authentication scheme used, only the Apache web server is
       supported currently. PHP installed under IIS as a cgi is reported to
       work too.
     * IPplan  can  be  run  with  php  configured  for  safe_mode=on and
       register_globals=off.
     * IPplan was developed using MySQL as a database. Using other databases
       require workarounds for some functionality which is not optimal. During
       my own tests, speed was visibly faster using MySQL.
     _________________________________________________________________

23. Questions and Answers (FAQ)

   Check the forums on SourceForge too.

   The  FAQ  is  no longer maintained in the manual - check online at the
   [82]IPplan homepage.

References

   1. http://www.php.net/
  51. http://sourceforge.net/
  52. http://www.php.net/
  53. http://www.insecure.org/
  54. http://www.vhconsultants.com/
  55. http://adodb.sourceforge.net/
  56. http://vex.sourceforge.net/
  57. http://phplayersmenu.sourceforge.net/
  58. mailto:ipplan@gmail.com
  59. http://httpd.apache.org/
  60. http://httpd.apache.org/docs/windows.html
  61. http://www.appservnetwork.com/
  62. http://www.wampserver.com/en/index.php
  63. http://www.mysql.com/
  64. http://www.postgresql.org/
  65. http://www.oracle.com/
  66. http://www.php.net/
  67. http://www.oreilly.com/catalog/hpmysql/chapter/ch07.pdf
  68. http://sourceforge.net/projects/net-snmp
  69. http://sourceforge.net/projects/iptrack
  70. http://cvs.sourceforge.net/viewcvs.py/iptrack/ipplan/TODO?view=markup
  71. http://cvs.sourceforge.net/viewcvs.py/iptrack/ipplan/CHANGELOG?view=markup
  72. file://localhost/home/richarde/public_html/iptrackdev/ipplan/screenshots
  73. http://www.arin.net/minutes/tutorials/swipit.htm
  74. http://www.cisco.com/univercd/cc/td/doc/cisintwk/ito_doc/ospf.htm
  75. http://xmlsoft.org/XSLT/
  76. http://www.insecure.org/
  77. file://localhost/home/richarde/public_html/iptrackdev/ipplan/README.html
  78. http://www.rudedog.org/auth_ldap/
  79. http://www.ja-sig.org/products/cas/index.html
  80. http://www.php.net/bugs.php?id=10924
  81. https://sourceforge.net/forum/forum.php?thread_id=627391&forum_id=101033
  82. http://iptrack.sourceforge.net/
