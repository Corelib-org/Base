<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:c="http://www.corelib.org/xsl/cache" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
	
	
	<xsl:template match="/">
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="child::*">
		<xsl:choose>
			<xsl:when test="name() != ''">
				<xsl:element name="{name()}">
					<xsl:for-each select="attribute::*">
						<xsl:attribute name="{name()}"><xsl:value-of select="." /></xsl:attribute>
					</xsl:for-each>
					<xsl:apply-templates select="*|text()" />
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<copy-text local-name="{local-name()}" name="{name()}" count="count(*);">
					<xsl:copy-of select="." />
				</copy-text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="text()">
		<xsl:copy-of select="."/>
	</xsl:template>
	
	
	
	
	<xsl:template match="c:value-of">
		<xsl:choose>
			<xsl:when test="@disable-output-escaping = 'yes'">
				<xsl:processing-instruction name="php">echo <xsl:value-of select="php:function('PageFactoryDOMXSL::_rewriteCPath', @select)"/>;</xsl:processing-instruction>
			</xsl:when>
			<xsl:otherwise>
				<xsl:processing-instruction name="php">echo XMLTools::escapeXMLCharacters(<xsl:value-of select="php:function('PageFactoryDOMXSL::_rewriteCPath', @select)"/>);</xsl:processing-instruction>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>	

	
	<xsl:template match="c:list">
		<xsl:processing-instruction name="php">$this->each(<xsl:value-of select="php:function('PageFactoryDOMXSL::_rewriteCPath', @select)"/>, '</xsl:processing-instruction>
			<xsl:apply-templates/>			
		<xsl:processing-instruction name="php">');</xsl:processing-instruction>
	</xsl:template>
	
	<xsl:template match="c:object">
		<xsl:processing-instruction name="php">$this->object(<xsl:value-of select="php:function('PageFactoryDOMXSL::_rewriteCPath', @select)"/>, '</xsl:processing-instruction>
			<xsl:apply-templates/>			
		<xsl:processing-instruction name="php">');</xsl:processing-instruction>
	</xsl:template>	
	


</xsl:stylesheet>
