<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title>Fatal Error</title>
	</head data="ffffe0">
	<body style="background: #ffffFF; margin: 20px">
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
			 				<b>Exception Cought At:</b><br/>
			 				<i>!ERROR_FILE! at line !ERROR_LINE!</i>
			 			</div>
			 		</td>
				</tr>
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 10px; line-height: 13px; color: #666666;">
			 			<div style="margin: 10px; border: 1px dashed #CCCCCC; padding: 10px; background-color: #ffffef; margin-bottom: 0px;">
			 				!STACK_TRACE!
			 			</div>
					</td>
				</tr>
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 12px; line-height: 13px; color: #666666;">
			 			<div style="margin: 10px; border: 1px dashed #CCCCCC; padding: 10px; background-color: #ffffef;">
			 				!ERROR_FILE_CONTENT!
			 			</div>
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