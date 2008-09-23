<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template match="content">
		<xsl:call-template name="manager">
			<xsl:with-param name="content">

				<xsl:call-template name="h1">
					<xsl:with-param name="headline">XHTML Markup</xsl:with-param>
					<xsl:with-param name="nav">
					<label for="view">Change view</label>
						<select id="view" class="select">
							<option>Basics</option>
							<option>Extended Information</option>
							<option>Permissions</option>
						</select>
					</xsl:with-param>
				</xsl:call-template>
				<p>
					Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nam cursus. Sed metus massa, luctus vel, nonummy ut, blandit quis, magna. Nulla justo ante, auctor et, sodales sed, fermentum at, neque. Maecenas tempus justo ac mi. Fusce tincidunt semper risus. Nunc sagittis, velit vitae venenatis commodo, felis nisl lacinia elit, eget tincidunt mauris quam in nunc. Fusce at nunc. Aliquam eu dui id orci accumsan vestibulum. Aenean ultrices urna ut nisi tincidunt scelerisque. Curabitur non sapien et dolor egestas feugiat. Nam posuere vulputate massa. Curabitur eleifend consequat lacus. Vivamus adipiscing, metus vitae varius porta, lectus urna mollis mi, sed vulputate odio massa vel justo. Aliquam consectetuer. Pellentesque laoreet imperdiet lorem.
				</p>
				 <p>
					Ut nunc justo, rutrum eu, posuere quis, dapibus id, tellus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nulla facilisi. Cras feugiat nulla commodo leo. Morbi posuere, purus in nonummy condimentum, massa ipsum rhoncus erat, id fringilla libero purus eget pede. Vivamus quis magna. Etiam congue, purus quis ultricies placerat, nisi ligula dapibus mi, sit amet pretium sem ante tempus quam. Praesent sit amet nisi. Donec sit amet mi. Mauris cursus massa et risus. Aliquam suscipit ipsum ac leo. Curabitur ligula ipsum, pretium eget, tristique in, condimentum at, dolor. Curabitur sit amet elit eget quam congue sagittis. Maecenas auctor magna eu libero. Sed eget sem. Vivamus erat dui, placerat nec, sollicitudin sed, mattis eu, sem. Donec nec mauris sit amet dui ornare faucibus.
				</p>
				
				<xsl:call-template name="h1">
					<xsl:with-param name="headline">Select blog</xsl:with-param>
					<xsl:with-param name="backtitle">Step 1</xsl:with-param>
				</xsl:call-template>
							
							<form>								
								<div>
									<label for="field">Blog titel</label><input type="text" id="field" name="field" class="text" value="form item content"/>
									<div class="fielddesc"><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nam cursus. Sed metus massa, luctus vel, nonummy ut, blandit quis, magna.</p></div>

									<label for="field">Blog beskrivelse</label><input type="text" id="field" name="field" class="text" value="form item content"/>
									<div class="fielddesc"><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nam cursus. Sed metus massa, luctus vel, nonummy ut, blandit quis, magna.</p></div>

									<label for="field">Offentlig Weblog</label>
									<select class="select">
										<option>Yes</option>
										<option>No</option>
										<option>Maybe</option>
										<option>Surprise me</option>
									</select>
									<div class="fielddesc"><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nam cursus. Sed metus massa, luctus vel, nonummy ut, blandit quis, magna.</p></div>
								</div>
								<input type="submit" value="Save Changes" class="button submit right"/>
								<input type="reset" value="Reset Changes" class="button cancel right"/>
							</form>
							<div class="clear"></div>
							<span class="backtitle">Step 2</span>
							<h1>Select Items in list</h1>
							<table>
								<thead>
									<tr>
										<th>&#160;</th>
										<th>Navn</th>
										<th>Dato</th>
										<th>Sted</th>
										<th class="functions">Funktioner</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="5">select all</td>
									</tr>
								</tfoot>
								<tbody>
									<tr>
										<td class="checkbox"><input type="checkbox"/></td>
										<td>Steffen Soerensen</td>
										<td>14 April 2008</td>
										<td>Nakskov</td>
										<td class="functions">Edit</td>
									</tr>
									<tr class="highlight">
										<td class="checkbox"><input type="checkbox"/></td>
										<td>Brian Borge</td>
										<td>14 April 2008</td>
										<td>Nakskov</td>
										<td class="functions">Edit</td>
									</tr>
									<tr>
										<td class="checkbox"><input type="checkbox"/></td>
										<td>Mike Crunch</td>
										<td>14 April 2008</td>
										<td>Nakskov</td>
										<td class="functions">Edit</td>
									</tr>
									<tr class="highlight">
										<td class="checkbox"><input type="checkbox"/></td>
										<td>Brian Borge</td>
										<td>14 April 2008</td>
										<td>Nakskov</td>
										<td class="functions">Edit</td>
									</tr>
									<tr class="selected">
										<td class="checkbox"><input type="checkbox" checked="true"/></td>
										<td>Mike Crunch</td>
										<td>14 April 2008</td>
										<td>Nakskov</td>
										<td class="functions">Edit</td>
									</tr>
								</tbody>
							</table>



			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

</xsl:stylesheet>