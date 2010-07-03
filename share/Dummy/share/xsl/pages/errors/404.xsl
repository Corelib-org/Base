<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes"/>
	<xsl:include href="../../base/layouts/default.xsl"/>

	<xsl:template match="content" mode="xhtml-content">
		<h1>Sorry, this page could not be found</h1>
		<p>
			This may be because of a mis-typed URL, faulty referral from another
			site, out-of-date search engine listing or we simply deleted a file.
		</p>
	</xsl:template>

</xsl:stylesheet>