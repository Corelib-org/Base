<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<!--
	/**
	 * Bootstrapping XSL template.
	 *
	 * Do not make any changes to this template. If you want anything
	 * appended to the html head section, use the related methods or overwrite
	 * the template defined below this template.
	 */
	-->
	<xsl:template match="/">
		<html>
			<head>
				<base href="{/page/settings/redirect-url}"/>
				<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
				<meta http-equiv="Content-language" content="en"/>
				<meta http-equiv="Content-Script-Type" content="text/javascript"/>

				<xsl:for-each select="/page/settings/javascript">
					<script language="JavaScript" type="text/javascript" src="{.}" />
				</xsl:for-each>
				<script type="text/javascript">
					<xsl:comment>
						var redirect_url = '<xsl:value-of select="/page/settings/base-url"/>';
					//</xsl:comment>
				</script>

				<link rel="shortcut icon" href="share/web/images/favicon.ico" />
				<link rel="stylesheet" type="text/css" href="share/web/style/basic.css" />
				<xsl:for-each select="/page/settings/stylesheet">
					<link rel="stylesheet" type="text/css" href="{.}" />
				</xsl:for-each>

				<xsl:apply-templates select="page/settings" mode="xhtml-header"/>
			</head>
			<body>
				<div id="page-container">
					<xsl:apply-templates select="page/content" mode="xhtml-layout"/>
				</div>
			</body>
		</html>
	</xsl:template>

	<!--
	/**
	 * Default xhtml-header template
	 *
	 * Default template to prevent any bogus output to be generated to
	 * headers if nothing is supposed to be drawn. you can at any point
	 * overwrite this template to make additions to the html head section.
	 */
	-->
	<xsl:template match="settings" mode="xhtml-header"></xsl:template>

</xsl:stylesheet>