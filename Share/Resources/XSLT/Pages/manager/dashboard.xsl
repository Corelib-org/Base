<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:include href="../../../../xsl/base/layouts/default.xsl"/>

	<xsl:template match="content" mode="xhtml-content">
		<xsl:for-each select="dashboard/*">
			<div class="widget">
				<xsl:apply-templates select="."/>
			</div>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>