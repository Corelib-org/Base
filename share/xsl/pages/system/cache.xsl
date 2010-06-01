<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:include href="../../base/layouts/default.xsl"/>

	<xsl:template match="content" mode="xhtml-content">
		<xsl:call-template name="h1">
			<xsl:with-param name="headline">Cache status</xsl:with-param>
		</xsl:call-template>
		<p>
			In this section you can get an overview of your how much corelib has cached,
			and you can clear the cache here as well.
		</p>
		<p>
			Corelib cache size is: <b><xsl:value-of select="manager-cache-status/@size"/></b>.
		</p>
		<form action="corelib/system/cache/clear/">
			<input type="submit" value="Clear cache" class="button cancel right" onclick="this.value='Clearing cache...'; this.disabled=true"/>
		</form>
	</xsl:template>

</xsl:stylesheet>