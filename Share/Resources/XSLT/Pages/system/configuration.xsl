<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<xsl:template match="content">
		<xsl:call-template name="manager">
			<xsl:with-param name="content">
				<h1>System Configuration</h1>
				<xsl:for-each select="constants/group">
					<xsl:if test="count(constant) > 0">
						<div>
							<h2><xsl:value-of select="@title"/></h2>
							<table border="1" style="width: 100%;">

								<xsl:for-each select="constant">
									<tr>
										<td>
											<h3><xsl:value-of select="title"/></h3>
											<p><xsl:value-of select="desc"/></p>
											<br/>
											<p><b>Default: </b><xsl:value-of select="default"/></p>
											<br/>
											<xsl:if test="binds = true()">
												<h4>Values</h4>
												<xsl:for-each select="binds/constant">
													<p><i><xsl:value-of select="@name"/> - <xsl:value-of select="title"/></i></p>
													<p><xsl:value-of select="desc"/></p>
													<br/>
												</xsl:for-each>
											</xsl:if>
										</td>
										<td valign="top">
											<xsl:choose>
												<xsl:when test="@fieldtype = 'select' and @type = 'constant'">
													<select name="{@name}">
														<xsl:for-each select="binds/constant">
															<option name="{@name}"><xsl:value-of select="@name"/></option>
														</xsl:for-each>
													</select>
												</xsl:when>
												<xsl:otherwise>
													<input type="text" name="{@name}"/>
												</xsl:otherwise>
											</xsl:choose>
										</td>
									</tr>
								</xsl:for-each>
							</table>
						</div>
					</xsl:if>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
</xsl:stylesheet>