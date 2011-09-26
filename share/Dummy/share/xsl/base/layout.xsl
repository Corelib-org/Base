<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes"/>

	<!--
	/**
	 * Page header
	 *
	 * @param content optional page header content
	 */
	-->
	<xsl:template match="page/content" mode="xhtml-page-header">
		<div id="page-header">
			<img src="share/web/images/corelib/logo.jpg"/>
			<xsl:apply-templates select="." mode="xhtml-page-navigation"/>
		</div>
	</xsl:template>

	<!--
	/**
	 * Page navigation
	 */
	-->
	<xsl:template match="page/content" mode="xhtml-page-navigation">
		<div id="page-navigation">
			<a href="http://www.corelib.org/">Corelib website</a> |
			<a href="http://www.corelib.org/getting-started">Getting started</a> |
			<a href="http://www.corelib.org/documentation">Documentation</a> |
			<a href="http://www.corelib.org/function-reference">Function reference</a>
		</div>
	</xsl:template>

	<!--
	/**
	 * Page content container
	 *
	 * @param content content of page-contant-container
	 */
	 -->
	<xsl:template match="page/content" mode="xhtml-page-content-container">
		<xsl:param name="content"/>
		<div id="page-content-container">
			<xsl:copy-of select="$content"/>
		</div>
	</xsl:template>

	<!--
	/**
	 * Page footer
	 */
	 -->
	<xsl:template match="page/content" mode="xhtml-page-footer">
		<div id="page-footer">
			This page is powered by Corelib.
		</div>
	</xsl:template>

</xsl:stylesheet>