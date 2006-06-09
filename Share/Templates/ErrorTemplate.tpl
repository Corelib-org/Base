<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title>Fatal Error</title>
	</head>
	<body style="background: #ffffe0;">
		!ERROR_TEMPLATE {
			<table style="border: 1px solid #CCCCCC; background: #FFFFFF; margin-top:20px; width: 700px;" align="center">
				<tr>
					<td style="font-family: Verdana, Arial; font-size: 14px; color: #CCCC00;">
			 			<div style="margin-left: 10px; margin-right: 10px; margin-top: 10px;">
			 				!ERROR_NAME!
			 			</div>
			 		</td>
				</tr>
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 12px; color: #666666;">
			 			<div style="margin-left: 10px; margin-right: 10px;">
			 				<div style="margin-bottom: 10px;">!ERROR_DESC!</div>
			 				<b>Exception Cought At:</b><br/>
			 				<i>!ERROR_FILE! at line !ERROR_LINE!</i>
			 			</div>
			 		</td>
				</tr>
				<tr>
			 		<td style="font-family: Verdana, Arial; font-size: 10px; color: #666666;">
			 			<div style="margin: 10px; border: 1px dashed #CCCCCC; padding: 10px;">
			 				!STACK_TRACE!
			 			</div>
					</td>
				</tr>
			</table>
		}
	</body>
</html>