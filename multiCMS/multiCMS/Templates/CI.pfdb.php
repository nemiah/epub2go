<?php echo "This is a database-file."; /*
MySQL&%%%&MSSQL
varchar(5000)&%%%&varchar(5000)
CREATE TABLE `Template` (   `TemplateID` int(10) NOT NULL AUTO_INCREMENT,   `templateType` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\',   `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\',   `html` text COLLATE utf8_unicode_ci NOT NULL,   `TemplateDomainID` int(10) NOT NULL,   `aktiv` tinyint(1) NOT NULL,   PRIMARY KEY (`TemplateID`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              &%%%&CREATE TABLE [Template] (   [TemplateID] int NOT NULL AUTO_INCREMENT,   [templateType] varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\',   [name] varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\',   [html] text COLLATE utf8_unicode_ci NOT NULL,   [TemplateDomainID] int NOT NULL,   [aktiv] tinyint NOT NULL,   PRIMARY KEY ([TemplateID]) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         %%&&&
INSERT INTO `Template` VALUES (1,\'contentTemplate\',\'default content\',\'%%%TEXT%%%\',0,1),(2,\'naviTemplate\',\'default navigation\',\'				<div class=\"tab inactiveT\">%n%l%					<div class=\"imageDiv\"></div>%n%l%					<p>%%%LINK%%%</p>%n%l%				</div>\',0,1),(3,\'pageTemplate\',\'default page\',\'%n%l%		<div class=\"text\">%n%l%		<div class=\"bgWrapper\">%n%l%		<h1>%%%HEADER%%%</h1>%n%l%		%%%CONTENT%%%%n%l%		<div class=\"footer\"></div>%n%l%		</div>%n%l%		</div>\',0,1),(4,\'domainTemplate\',\'default domain\',\'<?xml version=\"1.0\" encoding=\"UTF-8\" ?>%n%l%<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"%n%l%     \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">%n%l%<html xmlns=\"http://www.w3.org/1999/xhtml\">%n%l%	<head>%n%l%		<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />%n%l%		<meta name=\"revisit-after\" content=\"14 days\" />%n%l%		<meta http-equiv=\"content-encoding\" content=\"gzip\" />%n%l%		<meta http-equiv=\"cache-control\" content=\"no-cache\" />%n%l%		<meta name=\"Description\" content=\"\" />%n%l%		<meta name=\"keywords\" content=\"\" />%n%l%		<title>%%%TITLE%%%</title>%n%l%		<link rel=\"stylesheet\" type=\"text/css\" href=\"./multiCMSData/draft.css\" />%n%l%		%n%l%		<script src=\"./multiCMSData/prototype.js\" type=\"text/javascript\"></script>%n%l%		<script src=\"./multiCMSData/effects.js\" type=\"text/javascript\"></script>%n%l%		<script src=\"./multiCMSData/multiCMS.js\" type=\"text/javascript\"></script>%n%l%		%n%l%	</head>%n%l%	<body>%n%l%		<div class=\"wrapper\">%n%l%		<div class=\"header\"><p>%%%HEADER%%%</p></div>%n%l%		<div class=\"navi\">%%%NAVIGATION%%%</div><div class=\"spacer\"></div>%%%PAGE%%%%n%l%		</div>%n%l%	</body>%n%l%</html>\',0,1),(6,\'presetTemplate\',\'Kontaktformular\',\'%%%TEXT%%%</p>%n%l%<form id=\"kontaktFormular\">%n%l%	<table>%n%l%		<colgroup>%n%l%			<col style=\"width:150px;\" />%n%l%			<col />%n%l%		</colgroup>%n%l%		<tr>%n%l%			<td><label for=\"kontaktName\">Name:</label></td>%n%l%			<td><input name=\"kontaktName\" id=\"kontaktName\" type=\"text\" /></td>%n%l%		</tr>%n%l%		<tr>%n%l%			<td><label for=\"kontaktEMail\">E-Mailadresse:</td>%n%l%			<td><input name=\"kontaktEMail\" id=\"kontaktEMail\" type=\"text\" /></td>%n%l%		</tr>%n%l%		<tr>%n%l%			<td style=\"vertical-align:top;\"><label for=\"kontaktText\">Nachricht:</td>%n%l%			<td><textarea name=\"kontaktText\" id=\"kontaktText\"></textarea></td>%n%l%		</tr>%n%l%		<tr>%n%l%			<td></td>%n%l%			<td>%n%l%				<input type=\"button\" value=\"Mitteilung absenden\" onclick=\"multiCMS.formHandler(%%&ESCSLASH%%&kontaktFormular%%&ESCSLASH%%&);\" />%n%l%				<input type=\"hidden\" name=\"HandlerID\" value=\"%%%HANDLER%%%\" />%n%l%			</td>%n%l%		</tr>%n%l%	</table>%n%l%</form>%n%l%<p>\',0,1),(8,\'dlTemplate\',\'default download\',\'%%%TEXT%%%%n%l%<table>%n%l%<colgroup>%n%l%<col class=\"s1\" style=\"width:20px\" />%n%l%<col />%n%l%<col class=\"s1\" />%n%l%<col />%n%l%<col class=\"s1\" />%n%l%</colgroup>%n%l%%%%DOWNLOADS%%%%n%l%</table>\',0,1);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        &%%%&INSERT INTO [Template] ([TemplateID], [templateType], [name], [html], [TemplateDomainID], [aktiv]) VALUES (1,\'contentTemplate\',\'default content\',\'%%%TEXT%%%\',0,1),(2,\'naviTemplate\',\'default navigation\',\'				<div class=\"tab inactiveT\">%n%l%					<div class=\"imageDiv\"></div>%n%l%					<p>%%%LINK%%%</p>%n%l%				</div>\',0,1),(3,\'pageTemplate\',\'default page\',\'%n%l%		<div class=\"text\">%n%l%		<div class=\"bgWrapper\">%n%l%		<h1>%%%HEADER%%%</h1>%n%l%		%%%CONTENT%%%%n%l%		<div class=\"footer\"></div>%n%l%		</div>%n%l%		</div>\',0,1),(4,\'domainTemplate\',\'default domain\',\'<?xml version=\"1.0\" encoding=\"UTF-8\" ?>%n%l%<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"%n%l%     \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">%n%l%<html xmlns=\"http://www.w3.org/1999/xhtml\">%n%l%	<head>%n%l%		<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />%n%l%		<meta name=\"revisit-after\" content=\"14 days\" />%n%l%		<meta http-equiv=\"content-encoding\" content=\"gzip\" />%n%l%		<meta http-equiv=\"cache-control\" content=\"no-cache\" />%n%l%		<meta name=\"Description\" content=\"\" />%n%l%		<meta name=\"keywords\" content=\"\" />%n%l%		<title>%%%TITLE%%%</title>%n%l%		<link rel=\"stylesheet\" type=\"text/css\" href=\"./multiCMSData/draft.css\" />%n%l%		%n%l%		<script src=\"./multiCMSData/prototype.js\" type=\"text/javascript\"></script>%n%l%		<script src=\"./multiCMSData/effects.js\" type=\"text/javascript\"></script>%n%l%		<script src=\"./multiCMSData/multiCMS.js\" type=\"text/javascript\"></script>%n%l%		%n%l%	</head>%n%l%	<body>%n%l%		<div class=\"wrapper\">%n%l%		<div class=\"header\"><p>%%%HEADER%%%</p></div>%n%l%		<div class=\"navi\">%%%NAVIGATION%%%</div><div class=\"spacer\"></div>%%%PAGE%%%%n%l%		</div>%n%l%	</body>%n%l%</html>\',0,1),(6,\'presetTemplate\',\'Kontaktformular\',\'%%%TEXT%%%</p>%n%l%<form id=\"kontaktFormular\">%n%l%	<table>%n%l%		<colgroup>%n%l%			<col style=\"width:150px;\" />%n%l%			<col />%n%l%		</colgroup>%n%l%		<tr>%n%l%			<td><label for=\"kontaktName\">Name:</label></td>%n%l%			<td><input name=\"kontaktName\" id=\"kontaktName\" type=\"text\" /></td>%n%l%		</tr>%n%l%		<tr>%n%l%			<td><label for=\"kontaktEMail\">E-Mailadresse:</td>%n%l%			<td><input name=\"kontaktEMail\" id=\"kontaktEMail\" type=\"text\" /></td>%n%l%		</tr>%n%l%		<tr>%n%l%			<td style=\"vertical-align:top;\"><label for=\"kontaktText\">Nachricht:</td>%n%l%			<td><textarea name=\"kontaktText\" id=\"kontaktText\"></textarea></td>%n%l%		</tr>%n%l%		<tr>%n%l%			<td></td>%n%l%			<td>%n%l%				<input type=\"button\" value=\"Mitteilung absenden\" onclick=\"multiCMS.formHandler(%%&ESCSLASH%%&kontaktFormular%%&ESCSLASH%%&);\" />%n%l%				<input type=\"hidden\" name=\"HandlerID\" value=\"%%%HANDLER%%%\" />%n%l%			</td>%n%l%		</tr>%n%l%	</table>%n%l%</form>%n%l%<p>\',0,1),(8,\'dlTemplate\',\'default download\',\'%%%TEXT%%%%n%l%<table>%n%l%<colgroup>%n%l%<col class=\"s1\" style=\"width:20px\" />%n%l%<col />%n%l%<col class=\"s1\" />%n%l%<col />%n%l%<col class=\"s1\" />%n%l%</colgroup>%n%l%%%%DOWNLOADS%%%%n%l%</table>\',0,1);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            %%&&&
*/ ?>
