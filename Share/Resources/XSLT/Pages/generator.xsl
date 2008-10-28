<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template match="content">
		<xsl:call-template name="manager">
			<xsl:with-param name="content">
				<xsl:call-template name="h1">
					<xsl:with-param name="headline">Code Generation Tool</xsl:with-param>
					<xsl:with-param name="backtitle">Code Generation</xsl:with-param>
				</xsl:call-template>
				<p>
					The code generation tool allow you to update or create class'es based on specific database layout.
				</p>
				
				<xsl:if test="/page/settings/get/name != ''">
					<form method="get">
						<input type="hidden" name="name" value="{/page/settings/get/name}"/>
						<input type="hidden" name="write" value="true"/>
						<xsl:for-each select="codewriter/actions">
							<b><xsl:value-of select="@name"/> (<xsl:value-of select="@table"/>)</b><br/>
							<xsl:for-each select="action">
								<xsl:choose>
									<xsl:when test="@action = 'create'">
										+&#160;
									</xsl:when>
									<xsl:when test="@action = 'patch'">
										P&#160;
									</xsl:when>
									<xsl:otherwise>
										N&#160;
									</xsl:otherwise>
								</xsl:choose>
								<xsl:value-of select="@filename"/><br/>
								<xsl:if test="@action = 'patch'">
									<div style="font-size: 11px; white-space: pre; font-family: monospace;"><xsl:value-of select="." disable-output-escaping="yes"/></div>
								</xsl:if>
							</xsl:for-each>
						</xsl:for-each>
						<xsl:if test="/page/settings/get/write != true()">
							<input type="submit" value="Write changes" class="button submit right"/>
						</xsl:if>
						<small>+ = Create new file, P = Patch excisting file, N = No action required</small>
					</form>
					<div class="clear"><br/></div>
					
					<xsl:call-template name="h1">
						<xsl:with-param name="headline">Select another class tree</xsl:with-param>
						<xsl:with-param name="backtitle">Select another class</xsl:with-param>
					</xsl:call-template>
				</xsl:if>
				
				<form method="get">								
					<div>
						<label for="field">Select class tree</label>
						<select class="select" name="name">
							<option value="">[Select class tree]</option>
							<xsl:for-each select="codewriter/class">
								<option value="{@name}"><xsl:value-of select="@name"/> (<xsl:value-of select="@table"/>)</option>
							</xsl:for-each>
						</select>
						<div class="fielddesc"><p>Select which class tree you would like to create or update.</p></div>						
					</div>
					<input type="submit" value="Continue and review actions" class="button submit right"/>
				</form>
				<div class="clear"></div>				
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

</xsl:stylesheet>