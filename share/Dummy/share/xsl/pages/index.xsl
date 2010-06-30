<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes"/>
	<xsl:include href="../base/layouts/default.xsl"/>

	<xsl:template match="content" mode="xhtml-content">
		<h1>It works!</h1>
		<p>
			Welcome to your new Corelib website. You have successfully configured the corelib dummy website.
		</p>
		<h2>This is a temporary page</h2>
		<p>
			This page is part of the Corelib framework. It will disappear as soon as you start configuring your site.
		</p>
		<p>
			If you would like to edit this page you will find the XSL stylesheet located at:
			<code>share/xsl/pages/index.xsl</code>
		</p>
		<p>
			The corresponding controller for this page is found at:
			<code>lib/http/get/index.php [WebPage::build()]</code>
		</p>
		<p>
			If you are exploring Corelib for the very time, you should start by reading
			our <a href="http://www.corelib.org/getting-started">Getting started</a> guide.
		</p>

		<h2>What's next</h2>
		<p>
			<a href="corelib/">Access Corelib web administration interface</a><br/>
			<a href="http://www.corelib.org/documentation">Learn more from the online manual</a><br/>
		</p>
	</xsl:template>

</xsl:stylesheet>