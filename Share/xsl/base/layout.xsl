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
	 * Page footer 
	 */
	 -->
	<xsl:template name="page-footer">
		<div id="page-footer">
			Corelib.org
		</div>
	</xsl:template>	
	
</xsl:stylesheet>