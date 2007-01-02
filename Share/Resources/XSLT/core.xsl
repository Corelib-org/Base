<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template match="/">
		<html>
			<head>
				<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
				<meta http-equiv="Content-language" content="en"/>
				<meta http-equiv="Content-Script-Type" content="text/javascript"/>
				<xsl:for-each select="/page/settings/javascript">
					<script language="JavaScript" type="text/javascript" src="{.}" />
				</xsl:for-each>
				<script type="text/javascript">
					<xsl:comment>
						var redirect_url = '<xsl:value-of select="/page/settings/redirect_url"/>';
					//</xsl:comment>
				</script>
				<link rel="shortcut icon" href="/corelib/resource/Manager/images/favicon.ico" />
				<link rel="stylesheet" type="text/css" href="/corelib/resource/Manager/css/basic.css" />
				<xsl:for-each select="/page/settings/stylesheet">
					<link rel="stylesheet" type="text/css" href="{.}" />
				</xsl:for-each>
				<xsl:for-each select="/page/settings/rss">
					<link rel="alternate" type="application/rss+xml" title="{.}" href="{/page/settings/get/page}rss/"/>
				</xsl:for-each>
			</head>
			<body>
				<xsl:apply-templates select="page/content"/>	
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>