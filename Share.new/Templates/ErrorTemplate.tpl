<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title>Fatal Error</title>
		<script type="text/javascript">
			<!--
				function toggleErrorDescription(element){
					if(element.style.display == 'none'){
						element.style.display = 'block';
					} else {
						element.style.display = 'none';
					}
				}
			// -->
		</script>
	</head data="ffffe0">
	<body style="background: #ffffFF; margin: 20px">
	<!--
		<div style="margin: auto; border: 1px solid #CCCCCC; background: #ffffe0; width: 80%; margin-bottom: 20px; text-align: left;" align="center">
			<div style="margin-left: 10px; margin-right: 10px; margin-top: 10px; ">
				<h1 style="color: #DF1111; font-family: Verdana, Arial; font-size: 25px;">Application error.</h1>
				<p style="font-family: Verdana, Arial; font-size: 12px; color: #666666;">
					A error has occured, please report this error to you administrator.
				</p>
				<h2 style="font-family: Verdana, Arial; font-size: 18px; color: #666666;">How do i report this error?</h2>
				<ol style="font-family: Verdana, Arial; font-size: 12px; color: #666666;">
					<li>Right click -> Save page as</li>
					<li>Attach the page to email</li>
					<li>Send the email to your administrator</li>
				</ol>
				<h2 style="font-family: Verdana, Arial; font-size: 15px; color: #666666;">
					Why am i seeing this page?
				</h2>
				<p style="font-family: Verdana, Arial; font-size: 12px; color: #666666;">
					The reason why you see this page is because this site is running in developer mode. if this site should'nt display this kind
					of errors please consult the corelib manual.
				</p>
			</div>
		</div> -->
		
		!ERROR_TEMPLATE {
			<table style="border: 1px solid #CCCCCC; background: #ffffe0; width: 80%; margin-bottom: 20px; text-align: left;" align="center">
				<tr>
					<td>
			 			<div style="margin-left: 10px; margin-right: 10px; margin-top: 10px;">
			 				<h1 style="font-family: Verdana, Arial; font-size: 20px; color: #DF1111; font-weight: bold; margin: 0px;">
			 					!ERROR_NAME!
			 				</h1>
			 			</div>
			 		</td>
				</tr>
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 12px; color: #666666;">
			 			<div style="margin-left: 10px; margin-right: 10px; line-height: 18px;">
			 				<div style="margin-bottom: 10px; line-height: 20px;">!ERROR_DESC!</div>
			 				<b>Error occured at:</b><br/>
			 				<i>!ERROR_FILE! at line !ERROR_LINE!</i>
			 			</div>
			 		</td>
				</tr>
				
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 12px; line-height: 13px; color: #666666;">
			 			<div style="margin-left: 10px; font-weight: bold; font-size: 12px;"><a href="javascript:void(0);" onclick="toggleErrorDescription(document.getElementById('!REQUEST_CONTENT_ID!'));">Request Information &#187;</a></div>
			 			<div style="display: block; margin: 10px; border: 1px dashed #CCCCCC; padding: 10px; background-color: #ffffef; margin-bottom: 10px;" id="!REQUEST_CONTENT_ID!">
			 				!REQUEST_CONTENT!
			 			</div>
						<script type="text/javascript">
							<!--
							document.getElementById('!REQUEST_CONTENT_ID!').style.display = 'none';
							// -->
						</script>			 			
					</td>
				</tr>				
				
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 12px; line-height: 13px; color: #666666;">
			 			<div style="margin-left: 10px; font-weight: bold; font-size: 12px;"><a href="javascript:void(0);" onclick="toggleErrorDescription(document.getElementById('!ERROR_FILE_CONTENT_ID!'));">Source &#187;</a></div>
			 			<div style="display: block; margin: 10px; border: 1px dashed #CCCCCC; padding: 10px; background-color: #ffffef; margin-bottom: 10px;" id="!ERROR_FILE_CONTENT_ID!">
			 				!ERROR_FILE_CONTENT!
			 			</div>
						<script type="text/javascript">
							<!--
							document.getElementById('!ERROR_FILE_CONTENT_ID!').style.display = 'none';
							// -->
						</script>			 			
					</td>
				</tr>
				
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 10px; line-height: 13px; color: #666666;">
			 			<div style="margin-left: 10px; font-weight: bold; font-size: 12px;"><a href="javascript:void(0);" onclick="toggleErrorDescription(document.getElementById('!STACK_TRACE_ID!'));">Stack trace &#187;</a></div>
			 			<div style="display: block; margin: 10px; border: 1px dashed #CCCCCC; padding: 10px; background-color: #ffffef; margin-bottom: 10px;" id="!STACK_TRACE_ID!">
			 				!STACK_TRACE!
			 			</div>
						<script type="text/javascript">
							<!--
							document.getElementById('!STACK_TRACE_ID!').style.display = 'none';
							// -->
						</script>
					</td>
				</tr>
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 10px; padding: 10px; padding-top: 0px; color: #666666; text-align: right;">
			 			Copyright !CORELIB_COPYRIGHT_YEAR! !CORELIB_COPYRIGHT! / Corelib v!CORELIB_VERSION!
					</td>
				</tr>
			</table>
		}
	</body>
</html>