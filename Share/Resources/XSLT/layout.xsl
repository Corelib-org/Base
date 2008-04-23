<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
		
	<xsl:template match="blaaaaaaa">
		<div id="container">

			
			
			<!-- Content begin -->
			<div id="page">
				<h1>Pagemanager - all the pages in the system</h1>
		
				<div class="left_content_container floatLeft">
					<!-- List pages design -->
					<table cellspacing="0" cellpadding="0">
						<tr>
							<th width="250px">Pagetitle</th>
							<th>Type</th>
							<th>Level</th>
		
							<th>Last edited</th>					
							<th>Actions</th>					
						</tr>
						<tr class="page_active">
							<td>Forsiden</td>
							<td>Sidestruktur</td>		
							<td>Level 1</td>
							<td>9 January 2008 10:42</td>
		
							<td>
								<a href="#" title="Edit page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_edit.gif" alt="Edit page"/></a>
								<a href="#" title="Delete page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_delete.gif" alt="Delete page"/></a>
								<a href="#" title="Deactivate page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_activate.gif" alt="Deactivate page"/></a>
							</td>
						</tr>
						<tr class="page_active colorrow">
							<td>Om Zornig</td>
		
							<td>Sidestruktur</td>		
							<td>Level 1</td>
							<td>9 January 2008 10:48</td>
							<td>
								<a href="#" title="Edit page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_edit.gif" alt="Edit page"/></a>
								<a href="#" title="Delete page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_delete.gif" alt="Delete page"/></a>
								<a href="#" title="Activate page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_deactivate.gif" alt="Activate page"/></a></td>
						</tr>
		
						<tr class="page_inactive">
							<td><img src="images/page/icons/page_level_2.gif" alt="pagelevel"/>News</td>
							<td>Module</td>		
							<td>Level 2</td>
							<td>10 January 2008 12:42</td>
							<td>
								<a href="#" title="Edit page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_edit.gif" alt="Edit page"/></a>
		
								<a href="#" title="Delete page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_delete.gif" alt="Delete page"/></a>
								<a href="#" title="Deactivate page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_activate.gif" alt="Deactivate page"/></a>	
							</td>
						</tr>
						<tr class="page_inactive colorrow">
							<td><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_level_3.gif" alt="pagelevel"/>Archive</td>
							<td>Module</td>		
							<td>Level 3</td>
		
							<td>10 January 2008 13:42</td>
							<td>
								<a href="#" title="Edit page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_edit.gif" alt="Edit page"/></a>
								<a href="#" title="Delete page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_delete.gif" alt="Delete page"/></a>
								<a href="#" title="Activate page"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/page_deactivate.gif" alt="Activate page"/></a>	
							</td>
						</tr>
						<tr class="table_footer">
							<td></td>
		
							<td></td>		
							<td></td>
							<td></td>
							<td>&#160;</td>					
						</tr>
					</table>
				</div>
				<div class="right_content_container floatRight">
					<form action="#">
						<div class="admin_filter">
		
							<h2>Sortér listevisningen</h2>
							<br />
		
							<div class="form-row">
							    <label for="created_on">From date:</label>
								<img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/date_select.gif" alt="Select date" />
								<input type="text" name="filters[created_on][from]" id="filters_created_on_from" value="" class="text" />
								<div class="clearboth"></div>
		
								<label for="created_on">To date:</label>
								<img src="{/page/settings/redirect_url}/corelib/resource/manager/images/page/icons/date_select.gif" alt="Select date" />
								<input type="text" name="filters[created_on][to]" id="filters_created_on_to" value="" class="text" />
								<div class="clearboth"></div>	
							</div>
							<input type="submit" name="filter" value="Sortér listen nu" class="button floatRight" style="margin-right: 22px" />
							
							
						</div>
					</form>
				</div>
		
				<div class="clearboth"></div>
			</div>
		</div>

	</xsl:template>	
	
	<xsl:template name="header">
		<div id="header">
	
			<div class="inner_header">
				<!-- Logo and shortcut menu -->
				<div class="header_top">
					<div class="logo floatLeft"><a href="/" title="Gå til forsiden"><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/logo_zcms.gif" title="ZCMS - Zornig Interactive" /></a></div>
					<div class="shortcut_nav floatRight">
						Shortcuts:&#160;&#160;
						<a href="#" title="Nyhedsbrev">Newsletter</a>&#160;&#160;|&#160;&#160;
	
						<a href="#" title="Nyheder">News</a>&#160;&#160;|&#160;&#160;
						raquo; <a href="#" title="Add shortcut">Add shortcut</a> 
						(3 left)
					</div>
				</div>
				<div class="clearboth"></div>
				
				<!-- Tab menu - left and right -->
				<div class="header_middle">
	
					<div class="nav floatLeft">
						<ul>
							<xsl:for-each select="/page/settings/menu/group[@align = 'left' or @align != true()]">
								<li><a href="#"><xsl:value-of select="@title"/></a><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/tab_inactive_right_bgr.gif" /></li>
							</xsl:for-each>
							
							<!--	
							<li class="selected"><a href="#">Dashboard</a><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/tab_active_right_bgr.gif" /></li>
							<li><a href="#">Pages</a><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/tab_inactive_right_bgr.gif" /></li>
							<li><a href="#">Modules</a><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/tab_inactive_right_bgr.gif" /></li>
							-->
						</ul>
					</div>
	
					<div class="nav floatRight">
						<ul>
							<xsl:for-each select="/page/settings/menu/group[@align = 'right']">
								<li><a href="#"><xsl:value-of select="@title"/></a><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/tab_inactive_right_bgr.gif" /></li>
							</xsl:for-each>


<!--							<li><a href="#">Pages</a><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/tab_inactive_right_bgr.gif" /></li>
							<li><a href="#">Modules</a><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/tab_inactive_right_bgr.gif" /></li>
							<li class="green"><a href="#">Help</a><img src="{/page/settings/redirect_url}/corelib/resource/manager/images/header/tab_green_right_bgr.gif" /></li> -->
						</ul>
					</div>
	
				</div>
				<div class="clearboth"></div>
				
				<!-- Submenu -->
				<div class="subnav">
					<ul>
						<li class="selected"><a href="#">Dashboard</a></li>
						<li><a href="#">Pages</a></li>
						<li><a href="#">Modules</a></li>
	
					</ul>
					<div class="clearboth"></div>
				</div>
			</div>
		</div>		
	</xsl:template>
	
	<xsl:template name="footer">
		<div id="footer">
			ZCMS er udviklet af Zornig Interactive Aps  -  Hillerødgade 30A  -  2200 København N  -  Telefon: +45 35 87 01 00  -  E-mail: info@zornig.dk  -  Webside: www.zornig.dk<br />
			Copyright 2007 - 2008 Zornig Interactive Aps. Alle rettigheder forbeholdes.
		</div>		
	</xsl:template>
	
	<xsl:template name="manager">
		<xsl:param name="content"/>
		<div id="container">
			<xsl:call-template name="header"/>

		</div>
		<xsl:call-template name="footer"/>		
		<!--
		<div id="page">
			<div id="manager_menu">
				<div style="margin: 5px;">
					<xsl:for-each select="/page/settings/managermenu/group">
						<b><xsl:value-of select="@title"/></b><br/>
						<xsl:for-each select="item">
							<a href="{@url}" title="{.}"><xsl:value-of select="."/></a><br/>
						</xsl:for-each>
						<xsl:if test="position() != count(../group)">
							<br/>
						</xsl:if>
					</xsl:for-each>
				</div>
			</div>
			<div id="manager_content">
				<xsl:copy-of select="$content"/>
			</div>
		</div>
		-->
	</xsl:template>

</xsl:stylesheet>