<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:include href="../../../../xsl/base/layouts/default.xsl"/>

	<xsl:template match="content" mode="xhtml-content">
		<xsl:call-template name="h1">
			<xsl:with-param name="headline">About</xsl:with-param>
		</xsl:call-template>
		<p>
			Some text about corelib and the developers.
		</p>
		<xsl:call-template name="h1">
			<xsl:with-param name="headline">License</xsl:with-param>
		</xsl:call-template>
		<p>
				This script is part of the corelib project. The corelib project is 
				free software; you can redistribute it and/or modify
			it under the terms of the GNU General Public License as published by
			the Free Software Foundation; either version 2 of the License, or
			(at your option) any later version.
		</p>
		<p>
				The GNU General Public License can be found at
				http://www.gnu.org/copyleft/gpl.html.
			A copy is found in the textfile GPL.txt and important notices to the license
			from the author is found in LICENSE.txt distributed with these scripts.
		</p>
		<p>
			This script is distributed in the hope that it will be useful,
				but WITHOUT ANY WARRANTY; without even the implied warranty of
			MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
			GNU General Public License for more details.
		</p>
		<p>
				<b>This copyright notice MUST APPEAR in all copies of Corelib!</b>
		</p>				
	</xsl:template>

</xsl:stylesheet>