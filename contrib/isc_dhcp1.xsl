<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

<xsl:template match="network">
subnet <xsl:value-of select="@address"/> netmask <xsl:value-of select="@mask"/>
{
 option routers <xsl:value-of select="router"/>;
 option domain-name-servers <xsl:value-of select="dns"/>;
 option domain-name "<xsl:value-of select="domain"/>";
 default-lease-time <xsl:value-of select="defaultlease"/>;
 max-lease-time <xsl:value-of select="maxlease"/>;
 range <xsl:value-of select="poolrange"/>;
}
<xsl:apply-templates select="host"/>
</xsl:template>

<xsl:template match="host">
<xsl:if test="macaddr">
host <xsl:value-of select="hostname"/> {
 hardware ethernet <xsl:value-of select="macaddr"/>;
 fixed-address <xsl:value-of select="@ip"/>;
}
</xsl:if>
</xsl:template>

</xsl:stylesheet>
