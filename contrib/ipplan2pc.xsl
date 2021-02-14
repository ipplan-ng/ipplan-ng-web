<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

<xsl:key name="sharednetworks" match="network" use="description" />
<xsl:template match="dhcp">
<xsl:for-each select="network[generate-id(.)=generate-id(key('sharednetworks', description)[1])]">
shared-network <xsl:value-of select="description" /> {
<xsl:for-each select="key('sharednetworks', description)">
	subnet <xsl:value-of select="@address" /> netmask <xsl:value-of select="@mask" /> {
		<xsl:choose><xsl:when test="not(router='')">
                option routers <xsl:value-of select="router" />;
		</xsl:when><xsl:otherwise><xsl:message>
WARNING: The scope <xsl:value-of select="@address" />/<xsl:value-of select="@mask" /> does NOT have a default gateway!
		</xsl:message></xsl:otherwise></xsl:choose>
		<xsl:if test="not(domain='')">option domain-name "<xsl:value-of select="domain" />";
		</xsl:if>
		<xsl:if test="not(dns='')">option domain-name-servers <xsl:value-of select="dns" />;
		</xsl:if>
		<xsl:if test="not(maxlease='')">max-lease-time <xsl:value-of select="maxlease" />;
		</xsl:if>
		<xsl:if test="not(defaultlease='')">default-lease-time <xsl:value-of select="defaultlease" />;</xsl:if>
                <xsl:for-each select="host"><xsl:if test="not(macaddr)">range <xsl:value-of select="@ip"/>;</xsl:if></xsl:for-each>
	}
</xsl:for-each>
}
<xsl:for-each select="key('sharednetworks', description)">
	<xsl:apply-templates select="host" />
</xsl:for-each>

</xsl:for-each>
</xsl:template>

<xsl:template match="host">
<xsl:if test="macaddr=''">
host <xsl:value-of select="generate-id(.)"/> { hardware ethernet <xsl:value-of select="macaddr"/>; fixed-address <xsl:value-of select="@ip"/>; }</xsl:if>
</xsl:template>

</xsl:stylesheet>

