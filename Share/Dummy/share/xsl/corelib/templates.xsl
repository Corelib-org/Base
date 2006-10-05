<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template name="page">
		<xsl:param name="content"/>
		<div id="page">
			<div class="center">
				<img src="/share/web/images/corelib/corelib.gif"/>
			</div>
			<xsl:call-template name="menu"/>
			<div id="content" style="clear: both;">
				<xsl:copy-of select="$content"/>
			</div>
		</div>
	</xsl:template>
	
	<xsl:template name="menu">
		<div id="menu">
			<ul>
				<li><a href="/corelib/install/">Install</a></li>
				<li><a href="/corelib/doc/">Documentation</a></li>
				<li><a href="/corelib/license/">License</a></li>
				<li><a href="/corelib/about/">About</a></li>
			</ul>
		</div>
	</xsl:template>
</xsl:stylesheet>