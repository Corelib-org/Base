<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>			
	<xsl:template name="page">
		<xsl:param name="content"/>
		
		<div id="header">
		
		</div>
		
		<div id="container">
			<xsl:call-template name="status_box"/>
			<xsl:copy-of select="$content"/>				
		</div>
		
		<div id="footer">

		</div>	
	</xsl:template>

	
	<xsl:template name="statusbox">
		<xsl:if test="/page/settings/message = true()">
				<div id="message_{/page/settings/message/@type}">
					<h2><xsl:value-of select="/page/settings/message/headline" /></h2>
					<xsl:if test="/page/settings/message/item">
						<span>
							<xsl:value-of select="." />
						</span>
					</xsl:if>			
				</div>		
		</xsl:if>
	</xsl:template>
		
	<xsl:template name="nav">
	</xsl:template>
	
</xsl:stylesheet>