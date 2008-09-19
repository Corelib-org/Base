<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template match="content">
		<xsl:call-template name="manager">
			<xsl:with-param name="content">
				<xsl:call-template name="h1">
					<xsl:with-param name="headline">Database Tool</xsl:with-param>
				</xsl:call-template>
				<p>
					Database tool is used to keep your database up-to-date.
				</p>

			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

</xsl:stylesheet>