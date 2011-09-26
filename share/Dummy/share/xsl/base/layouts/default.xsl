<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:include href="../layout.xsl"/>

	<xsl:template match="content" mode="xhtml-layout">
		<xsl:apply-templates select="." mode="xhtml-page-header"/>

		<xsl:apply-templates select="." mode="xhtml-page-content-container">
			<xsl:with-param name="content">
				<xsl:apply-templates select="." mode="xhtml-content"/>
			</xsl:with-param>
		</xsl:apply-templates>

		<xsl:apply-templates select="." mode="xhtml-page-footer"/>
	</xsl:template>


	<!--
	Add backwards compatibility with old xsl styles

	This will allow for templates without mode attribute
	to be treated as ones with mode attribute. this serves
	as a default fallback for older versions of corelib.
	 -->
	<xsl:template match="*|/" mode="xhtml-content">
		<xsl:apply-templates select=".">
			<xsl:with-param name="mode">xhtml-content</xsl:with-param>
		</xsl:apply-templates>
	</xsl:template>


</xsl:stylesheet>