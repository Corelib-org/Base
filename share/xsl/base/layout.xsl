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
	<xsl:template name="page-header">
		<div id="page-header">
			<div>
				<img src="corelib/resource/manager/images/header/corelib.gif"/>
			</div>
		</div>
	</xsl:template>

	<!--
	/**
	 * Page navigation
	 */
	-->
	<xsl:template name="page-navigation">
		<div id="page-navigation" class="shadow" style="float: left;">
			<div id="page-navigation-container">
				<ul>
					<xsl:for-each select="/page/settings/menu/group">
						<li>
							<xsl:choose>
								<xsl:when test="@url = true()">
									<a href="{@url}"><xsl:value-of select="@title"/></a>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="@title"/>
								</xsl:otherwise>
							</xsl:choose>
						</li>
						<xsl:if test="count(item) > 0">
							<ul class="shadow">
								<xsl:for-each select="item">
									<li><a href="{@url}"><xsl:value-of select="."/></a></li>
								</xsl:for-each>
							</ul>
						</xsl:if>
					</xsl:for-each>
				</ul>
			</div>
			<div class="shadow"></div>
		</div>
	</xsl:template>

	<!--
	/**
	 * Page content container
	 *
	 * @param content content of page-contant-container
	 */
	 -->
	<xsl:template name="page-content-container">
		<xsl:param name="content"/>
		<div id="page-content-container">
			<div style="display: inline-block">
				<xsl:call-template name="page-navigation"/>
				<div id="page-content">
					<xsl:copy-of select="$content"/>
				</div>
			</div>
		</div>
	</xsl:template>


	<!--
	/**
	 * View selector
	 *
	 * @param selected selected view
	 */
	 -->
	 <xsl:template match="*" mode="view-selector">
	 	<xsl:param name="select"/>
	 	<xsl:param name="id"/>
	 	<select id="view" class="select" onchange="Toolbox.setLocation(this.options[this.selectedIndex].value);">
	 		<xsl:apply-templates select="mode" mode="view-selector">
	 			<xsl:with-param name="select" select="$select"/>
	 			<xsl:with-param name="id" select="$id"/>
	 		</xsl:apply-templates>
	 	</select>
	 </xsl:template>

	 <xsl:template match="mode" mode="view-selector">
	 	<xsl:param name="select"/>
	 	<xsl:param name="id"/>
		<xsl:element name="option">
			<xsl:attribute name="value"><xsl:value-of select="@prefix"/><xsl:value-of select="$id"/><xsl:value-of select="@suffix"/></xsl:attribute>
			<xsl:if test="$select = @id">
				<xsl:attribute name="selected">true</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="@title"/>
		</xsl:element>
	 </xsl:template>

	<!--
	/**
	 * Pager
	 *
	 * @param prefix page change url prefix
	 */
	 -->
	<xsl:template match="pager">
		<xsl:param name="prefix"/>
		<xsl:variable name="pages">10</xsl:variable>

		<xsl:if test="count(page) > 1">
			<div class="pager">
				<xsl:variable name="start">
					<xsl:choose>
						<xsl:when test="count(page) > $pages and page[@current = 'true'] = true()">
							<xsl:value-of select="page[@current = 'true'] - ($pages div 2) + 1"/>
						</xsl:when>
						<xsl:otherwise>
							1
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<xsl:variable name="end">
					<xsl:choose>
						<xsl:when test="count(page) > $pages and page[@current = 'true'] = true()">
							<xsl:choose>
								<xsl:when test="$start &lt; ($pages div 2)">
									<xsl:value-of select="(page[@current = 'true'] + $pages div 2) "/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="page[@current = 'true'] + ($pages div 2)"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$pages"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<xsl:if test="page[@current = 'true'] > 1">
					<a href="{$prefix}?p={(page[@current = 'true'] - 1)}">&#171;</a><xsl:text>&#160;</xsl:text>
				</xsl:if>

				<xsl:if test="$start &gt; 1">
					<a href="{$prefix}?p={page[position() = 1]}"><xsl:value-of select="page[position() = 1]"/></a><xsl:text>&#160;...&#160;</xsl:text>
				</xsl:if>
				<xsl:for-each select="page[position() &gt;= $start and position() &lt;= $end]">
					<xsl:choose>
						<xsl:when test="@current = 'true'">
							<span class="selected"><xsl:value-of select="."/></span><xsl:text>&#160;</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<a href="{$prefix}?p={.}"><xsl:value-of select="."/></a><xsl:text>&#160;</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:for-each>
				<xsl:if test="count(page) &gt; $end">
					... <a href="{$prefix}?p={page[position() = last()]}"><xsl:value-of select="page[position() = last()]"/></a><xsl:text>&#160;</xsl:text>
				</xsl:if>

				<xsl:choose>
					<xsl:when test="page[@current = 'true'] &lt; count(page)">
						<a href="{$prefix}?p={(page[@current = 'true'] + 1)}">&#187;</a>
					</xsl:when>
					<xsl:when test="count(page) &gt; 1 and page[@current = 'true'] != true()">
						<a href="{$prefix}?p=2">&#187;</a>
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
	</xsl:template>


	<xsl:template name="h1">
		<xsl:param name="headline"/>
		<xsl:param name="backtitle"/>
		<xsl:param name="nav"/>

		<span class="backtitle">
			<xsl:choose>
				<xsl:when test="$backtitle = true()">
					<xsl:value-of select="$backtitle"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$headline"/>
				</xsl:otherwise>
			</xsl:choose>
		</span>
		<xsl:if test="$nav">
			<span class="viewnavigator">
				<xsl:copy-of select="$nav"/>
			</span>
		</xsl:if>
		<h1><xsl:value-of select="$headline"/></h1>
	</xsl:template>

	<xsl:template name="select-options-boolean">
		<xsl:param name="selected">true</xsl:param>
		<xsl:param name="value-true">True</xsl:param>
		<xsl:param name="value-false">False</xsl:param>

		<xsl:element name="option">
			<xsl:attribute name="value">true</xsl:attribute>
			<xsl:if test="$selected = 'true'">
				<xsl:attribute name="selected">true</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="$value-true"/>
		</xsl:element>
		<xsl:element name="option">
			<xsl:attribute name="value">false</xsl:attribute>
			<xsl:if test="$selected = 'false'">
				<xsl:attribute name="selected">false</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="$value-false"/>
		</xsl:element>
	</xsl:template>

	<!--
	/**
	 * Page footer
	 */
	 -->
	<xsl:template name="page-footer">
		<div id="page-footer">
			Corelib.org
		</div>
	</xsl:template>

</xsl:stylesheet>