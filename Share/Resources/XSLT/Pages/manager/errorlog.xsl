<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template match="errorlog">
		<xsl:call-template name="h1">
			<xsl:with-param name="headline">Error log</xsl:with-param>
		</xsl:call-template>
		
		<xsl:for-each select="entry">
			<div class="logentry" onclick="ManagerWidgetErrorLog.toggleTrace($('trace{@id}'))">
				<h2><xsl:value-of select="code"/> (<xsl:value-of select="count(dates/date)"/>)&#160;<small><xsl:value-of select="@id"/></small></h2>
				<xsl:value-of select="file"/>:<xsl:value-of select="line"/><br/>
				<small>
				<xsl:for-each select="contentlines/contentline">
					<xsl:value-of select="."/><br/>
				</xsl:for-each>
				</small>
				<ul class="trace" id="trace{@id}" style="display: none;">
					<xsl:for-each select="tracelines/traceline">
						<li>#<xsl:value-of select="position()"/>: <xsl:value-of select="."/></li>
					</xsl:for-each>
				</ul>
			</div>
		</xsl:for-each>

		
	</xsl:template>
</xsl:stylesheet>