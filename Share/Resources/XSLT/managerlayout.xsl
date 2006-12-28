<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template name="manager">
		<xsl:with-param name="content"/>
		<div style="border: 1px solid #FF0000;">
			<div style="width: 250px; border: 1px solid #00FF00;">
				menu 1
			</div>
			<div>
				content
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>