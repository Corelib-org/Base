<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:include href="../../../../xsl/base/layouts/default.xsl"/>
	
	<xsl:template match="content" mode="xhtml-content">
		<xsl:call-template name="h1">
			<xsl:with-param name="headline">Database Tool</xsl:with-param>
		</xsl:call-template>
		<p>
			In this section you can get an overview of your currently selected 
			database compared to sql-files. You can also upgrade tables from earlier 
			versions.
		</p>
		<xsl:if test="count(database/object) > 0">
			<div style="float: right">
				<a href="javascript:void(0);" onclick="DatabaseTool.selectAllObjects(true)">Select</a>
				 / 
				<a href="javascript:void(0);" onclick="DatabaseTool.selectAllObjects(false)">deselect</a> all objects
				| 
				<a href="javascript:void(0);" onclick="DatabaseTool.showAllDescriptions(true)">Show</a>
				 / 
				<a href="javascript:void(0);" onclick="DatabaseTool.showAllDescriptions(false)">hide</a> all descriptions
			</div>
		</xsl:if>
		<h2>
			Database Status
		</h2>
		
		<form method="post">
			<xsl:choose>
				<xsl:when test="count(database/object) > 0">
					<xsl:for-each select="database/object">
						<xsl:variable name="name" select="@name"/>						
						<xsl:variable name="dependencies">
							<xsl:for-each select="dependencies/dependency">
								<xsl:text>'</xsl:text><xsl:value-of select="."/><xsl:text>'</xsl:text>
								<xsl:if test="position() != last()">
									<xsl:text>, </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</xsl:variable>
						<script type="text/javascript">
							<xsl:comment>
								DatabaseTool.addObject('<xsl:value-of select="@name"/>', new Array(<xsl:value-of select="$dependencies"/>));
							// </xsl:comment>
						</script>
						<div>
							<table cellpadding="0" cellspacing="0" class="noborders">
								<tr>
									<td style="vertical-align: top; width: 20px;">
											<xsl:variable name="checked">
												<xsl:for-each select="../object/dependencies[child::dependency = $name]">
													<xsl:if test="@type = 'update'">
														<xsl:text>1</xsl:text>
													</xsl:if>
												</xsl:for-each>
											</xsl:variable>
										
										<xsl:element name="input">
											<xsl:attribute name="type">checkbox</xsl:attribute>
											<xsl:attribute name="class">DatabaseToolCheckbox</xsl:attribute>
											<xsl:attribute name="id">checkbox_v_<xsl:value-of select="@name"/></xsl:attribute>
											
											<xsl:choose>
												<xsl:when test="count(../object/dependencies[child::dependency = $name]) &gt; 0">
													<xsl:if test="$checked != ''">
														<xsl:attribute name="disabled">true</xsl:attribute>
														<xsl:attribute name="checked">true</xsl:attribute>
													</xsl:if>
												</xsl:when>
												<xsl:when test="count(dependencies/dependency) > 0 or count(actions/action[@type = 'update']) > 0">
													<xsl:if test="$checked != ''">
														<xsl:attribute name="checked">true</xsl:attribute>
													</xsl:if>
												</xsl:when>
											</xsl:choose>
											<xsl:attribute name="onchange">DatabaseTool.toggleExcludeAction('<xsl:value-of select="@name"/>', this, $('exclude_<xsl:value-of select="@name"/>'));</xsl:attribute>
										</xsl:element>
										<xsl:element name="input">
											<xsl:attribute name="type">checkbox</xsl:attribute>
											<xsl:attribute name="name">exclude[<xsl:value-of select="@name"/>]</xsl:attribute>
											<xsl:attribute name="id">exclude_<xsl:value-of select="@name"/></xsl:attribute>
											<xsl:attribute name="style">display: none;</xsl:attribute>
											<xsl:choose>
												<xsl:when test="count(../object/dependencies[child::dependency = $name]) = 0 and count(dependencies/dependency) = 0 and count(actions/action[@type = 'update']) = 0">
													<xsl:attribute name="checked">true</xsl:attribute>
												</xsl:when>
												<xsl:when test="$checked = ''">
													<xsl:attribute name="checked">true</xsl:attribute>
												</xsl:when>
											</xsl:choose>											
										</xsl:element>
									</td>
									<td>
										<div style="float: right"><a href="javascript:void(0);" onclick="DatabaseTool.toggleUpdateDescription($('desc_{@name}'))">Show / hide description</a></div>										
										<h3><xsl:value-of select="@name"/></h3>
										<small>
											Type: 
											<xsl:choose>
												<xsl:when test="actions/action/@type = 'create'">
													<xsl:text>new object</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:text>update</xsl:text>
												</xsl:otherwise>
											</xsl:choose>
											<xsl:if test="count(dependencies/dependency)">
												<xsl:text>,</xsl:text> Dependencies: 
												<xsl:for-each select="dependencies/dependency">
													<xsl:value-of select="."/>
													<xsl:if test="position() != last()">
														<xsl:text>, </xsl:text>
													</xsl:if>
												</xsl:for-each>
											</xsl:if>
										</small><br/>
										<div id="desc_{@name}" style="display:none;" class="DatabaseToolHiddenBlock">
											<br/>
											<pre style="white-space: pre-wrap;">
												<xsl:for-each select="actions/action">
													<xsl:value-of select="."/>
													<xsl:if test="position() != last()">
														<br/><br/>
													</xsl:if>
												</xsl:for-each>
											</pre>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</xsl:for-each>
					<input type="submit" value="Update database" class="button submit right"/>
				</xsl:when>
				<xsl:otherwise>
					<div style="text-align: center;">
						<p><br/>Database is up-to-date</p>
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</form>
	</xsl:template>

</xsl:stylesheet>