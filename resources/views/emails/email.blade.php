<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		 <title>@yield('title')</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
	
<body style="margin: 0; padding: 0;">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td>
				<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
					 <tr>
						<td align="center" bgcolor="#70bbd9" style="padding: 40px 0 30px 0;">
							<img src="http://ffffionn.xyz/images/logo.png" alt="Eventure" width="230" height="230" style="display: block;" />
						</td>
					 </tr>
					 
					 @yield('content')
					 
					<tr>
						<td bgcolor="#ee4c50" style="padding: 30px 30px 30px 30px;">
							<table border="0" cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td width="75%">
										&reg; Eventure, The Eventure Team 2016<br/>
										<a href="url('/')">www.Eventure.com</a>
									</td>			  
									
									<td align="right">
										<table border="0" cellpadding="0" cellspacing="0">
											<tr>
												<td>
													<a href="http://www.twitter.com/">
													<img src="http://ffffionn.xyz/images/twitter.png" alt="Twitter" width="38" height="38" style="display: block;" border="0" />
													</a>
												</td>
												
												<td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
												
												<td>
													<a href="http://www.facebook.com/">
													<img src="http://ffffionn.xyz/images/facebook.png" alt="Facebook" width="38" height="38" style="display: block;" border="0" />
													</a>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>

</html>