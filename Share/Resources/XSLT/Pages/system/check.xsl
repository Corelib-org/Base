<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template match="content">
		<xsl:call-template name="manager">
			<xsl:with-param name="content">
				<xsl:call-template name="h1">
					<xsl:with-param name="headline">System Check</xsl:with-param>
				</xsl:call-template>
				<p>
					Some text about system check
				</p>
				<xsl:apply-templates select="systemchecks/check/*"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template match="check/folder">
		<p>
			folder: <xsl:value-of select="@folder"/>
			<table>
				<xsl:if test="result/dir = true()">
					<tr>
						<td>Is direcotry</td><td><xsl:value-of select="result/dir"/></td>
					</tr>
				</xsl:if>
				<xsl:if test="result/writable = true()">
					<tr>
						<td>Writable</td><td><xsl:value-of select="result/writable"/></td>
					</tr>
				</xsl:if>
				<xsl:if test="result/readable = true()">
					<tr>
						<td>Readable</td><td><xsl:value-of select="result/readable"/></td>
					</tr>
				</xsl:if>
			</table>
		</p>
	</xsl:template>
	
	<xsl:template match="check/file">
		<p>
			file: <xsl:value-of select="@file"/>
			<table>
				<xsl:if test="result/file = true()">
					<tr>
						<td>Is file</td><td><xsl:value-of select="result/file"/></td>
					</tr>
				</xsl:if>
				<xsl:if test="result/writable = true()">
					<tr>
						<td>Writable</td><td><xsl:value-of select="result/writable"/></td>
					</tr>
				</xsl:if>
				<xsl:if test="result/readable = true()">
					<tr>
						<td>Readable</td><td><xsl:value-of select="result/readable"/></td>
					</tr>
				</xsl:if>
			</table>
			<xsl:apply-templates select="links"/>		
		</p>
	</xsl:template>
	
	<xsl:template match="links">
		<xsl:for-each select="link">
			<a href="{@href}"><xsl:value-of select="."/></a>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>