<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

<xsl:template match="dhcp">
       <xsl:apply-templates select="network"/>
</xsl:template>

<xsl:template match="network">

subnet  <xsl:value-of select="@address"/> netmask <xsl:value-of select="@mask"/>
{
       authoritative;
       allow unknown-clients;
       option domain-name-servers <xsl:value-of select="dns"/>;
       option routers <xsl:value-of select="router"/>;
       option domain-name "<xsl:value-of select="domain"/>";
       option broadcast-address <xsl:value-of select="@broadcast"/>;
       option subnet-mask <xsl:value-of select="@mask"/>;

       max-lease-time <xsl:value-of select="maxlease"/> ;
       default-lease-time <xsl:value-of select="defaultlease"/>;

       range <xsl:value-of select="poolrange"/>;


<xsl:for-each select="host/macaddr" xml:space="preserve">
       host <xsl:value-of select="../hostname"/> {
             hardware ethernet <xsl:value-of select="."/>;
             fixed-address <xsl:value-of select="../@ip"/>;
       }
</xsl:for-each>

}

</xsl:template>

</xsl:stylesheet>
