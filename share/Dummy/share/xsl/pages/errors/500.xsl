<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes"/>
	<xsl:include href="../../base/layouts/default.xsl"/>

	<xsl:template match="content" mode="xhtml-content">
		<h1>Ooops! something went wrong</h1>
		<p>
			It looks as though we've broken something in our system. Don't panic!
			An email have been sent to the systems administrator.
		</p>
	</xsl:template>

</xsl:stylesheet>