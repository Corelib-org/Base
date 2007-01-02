<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template name="manager">
		<xsl:param name="content"/>
		<div id="page">
			<div id="manager_menu">
				<div style="margin: 5px;">
					<xsl:for-each select="/page/settings/managermenu/group">
						<b><xsl:value-of select="@title"/></b><br/>
						<xsl:for-each select="item">
							<a href="{@url}" title="{.}"><xsl:value-of select="."/></a><br/>
						</xsl:for-each>
						<xsl:if test="position() != count(../group)">
							<br/>
						</xsl:if>
					</xsl:for-each>
				</div>
			</div>
			<div id="manager_content">
				<xsl:copy-of select="$content"/>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>