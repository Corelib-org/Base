<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
		
	<xsl:template name="manager">
		<xsl:param name="content"/>
		<div id="topbar">
			<div>
				<img src="corelib/resource/manager/images/corelib.gif"/>
			</div>
		</div>
		<div id="container">
			<div>
				<div class="left">
					<div id="menu" class="left">
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
									<ul>
										<xsl:for-each select="item">
											<li><a href="{@url}"><xsl:value-of select="."/></a></li>
										</xsl:for-each>
									</ul>
								</xsl:if>
							</xsl:for-each>
						</ul>
						<div class="shadow"></div>
					</div>
					<div class="clear"></div>
					<div id="menu_bottom"></div>
				</div>
				<div class="left">
					<div id="content">
						<div id="breadcrumb" class="left clear">&#160;<!-- Settings Configuration --></div>
						<div id="innercontent" class="left clear">
							
							<xsl:copy-of select="$content"/>							

							<div class="clear"></div>
						</div>
						<div class="clear"></div>
					</div>
					<div id="content_bottom"></div>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<br/>
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

</xsl:stylesheet>