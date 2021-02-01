-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 01. Feb 2021 um 13:39
-- Server-Version: 10.1.41-MariaDB-0+deb9u1
-- PHP-Version: 5.6.40-16+0~20200123.27+debian9~1.gbp05c23e

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `epub2go`
--

--
-- TRUNCATE Tabelle vor dem Einfügen `Content`
--

TRUNCATE TABLE `Content`;
--
-- Daten für Tabelle `Content`
--

INSERT INTO `Content` (`ContentID`, `TemplateID`, `name`, `header`, `text`, `sort`, `SeiteID`, `contentType`, `presetTemplateID`, `formHandlerID`, `customContent`, `ContentImage`, `ContentSpracheID`) VALUES
(1, 1, '', 'epub2go', '<p><a href=\"http://www.phynx.de\"><img style=\"float: right;\" src=\"multiCMSData/phynx.png\" alt=\"\" width=\"76\" height=\"76\" /></a>Herzlichen Gl&uuml;ckwunsch!<br /> <br /> Wenn Sie diese Seite sehen, wurde multiCMS erfolgreich auf Ihrer Domain installiert.</p>\n<h2>get started</h2>\n<p>So erstellen Sie eine neue Seite:</p>\n<ul>\n<li>Loggen Sie sich in Ihrem <a href=\"multiCMS\">multiCMS</a> ein.</li>\n<li>Setzen Sie im Domain-Tab ein H&auml;kchen im grauen K&auml;stchen bei der *-Domain</li>\n<li>Wechseln Sie in den Seiten-Tab und erstellen Sie eine neue Seite</li>\n<li>Legen Sie im Navigation-Tab noch einen neuen Men&uuml;punkt an, damit die Seite aufgerufen werden kann.</li>\n</ul>\n<p>Bei weiteren Fragen wenden Sie sich bitte an das <a href=\"http://www.multicms.de/forum/viewforum.php?f=7\">Support-Forum</a>.</p>', 0, 1, 'php', 6, 0, 'CCGB2EPub', '', 0);

--
-- TRUNCATE Tabelle vor dem Einfügen `Domain`
--

TRUNCATE TABLE `Domain`;
--
-- Daten für Tabelle `Domain`
--

INSERT INTO `Domain` (`DomainID`, `TemplateID`, `url`, `startseite`, `title`, `header`, `umleitung`, `permalinkPrefix`, `horizontalNav`, `DomainDefaultSpracheID`, `fehlerseite`, `https`) VALUES
(6, 4, '*', 1, 'epub2go', 'epub2go', 0, '', 0, 0, 0, 0);

--
-- TRUNCATE Tabelle vor dem Einfügen `Navigation`
--

TRUNCATE TABLE `Navigation`;
--
-- Daten für Tabelle `Navigation`
--

INSERT INTO `Navigation` (`NavigationID`, `name`, `sort`, `DomainID`, `parentID`, `linkType`, `SeiteID`, `linkURL`, `activeTemplateID`, `inactiveTemplateID`, `hidden`, `displaySub`, `httpsLink`, `loginType`) VALUES
(1, 'Startseite', 100, '6', 0, 'cmsPage', 1, '', 2, 2, 0, 0, 0, 0);

--
-- TRUNCATE Tabelle vor dem Einfügen `Seite`
--

TRUNCATE TABLE `Seite`;
--
-- Daten für Tabelle `Seite`
--

INSERT INTO `Seite` (`SeiteID`, `TemplateID`, `name`, `header`, `DomainID`, `metaTagDescription`, `permalink`, `metaTagKeywords`) VALUES
(1, 3, '', 'Startseite', 6, '', '', '');

--
-- TRUNCATE Tabelle vor dem Einfügen `Template`
--

TRUNCATE TABLE `Template`;
--
-- Daten für Tabelle `Template`
--

INSERT INTO `Template` (`TemplateID`, `templateType`, `name`, `html`, `TemplateDomainID`, `aktiv`) VALUES
(1, 'contentTemplate', 'default content', '%%%TEXT%%%', 0, 1),
(2, 'naviTemplate', 'default navigation', '				<div class=\"tab inactiveT\">\n					<div class=\"imageDiv\"></div>\n					<p>%%%LINK%%%</p>\n				</div>', 0, 1),
(3, 'pageTemplate', 'default page', '%%%CONTENT%%%', 0, 1),
(4, 'domainTemplate', 'default domain', '<!DOCTYPE html>\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n	<head>\n		<meta charset=\"UTF-8\" />\n		<meta name=\"Description\" content=\"%%%DESCRIPTION%%%\" />\n		<title>%%%TITLE%%%</title>\n		<link rel=\"stylesheet\" type=\"text/css\" href=\"/multiCMSData/draft.css\" />\n		\n		<script src=\"/multiCMSData/prototype.js\" type=\"text/javascript\"></script>\n		<script src=\"/multiCMSData/effects.js\" type=\"text/javascript\"></script>\n		<script src=\"/multiCMSData/builder.js\" type=\"text/javascript\"></script>\n		<script src=\"/multiCMSData/multiCMS.js\" type=\"text/javascript\"></script>\n		<script src=\"/multiCMSData/GB2EPub.js\" type=\"text/javascript\"></script>\n	</head>\n	<body>\n		<div class=\"wrapper\">\n		%%%PAGE%%%\n		</div>\n	</body>\n</html>', 0, 1),
(6, 'presetTemplate', 'Kontaktformular', '%%%TEXT%%%</p>\n<form id=\"kontaktFormular\">\n	<table>\n		<colgroup>\n			<col style=\"width:150px;\" />\n			<col />\n		</colgroup>\n		<tr>\n			<td><label for=\"kontaktName\">Name:</label></td>\n			<td><input name=\"kontaktName\" id=\"kontaktName\" type=\"text\" /></td>\n		</tr>\n		<tr>\n			<td><label for=\"kontaktEMail\">E-Mailadresse:</td>\n			<td><input name=\"kontaktEMail\" id=\"kontaktEMail\" type=\"text\" /></td>\n		</tr>\n		<tr>\n			<td style=\"vertical-align:top;\"><label for=\"kontaktText\">Nachricht:</td>\n			<td><textarea name=\"kontaktText\" id=\"kontaktText\"></textarea></td>\n		</tr>\n		<tr>\n			<td></td>\n			<td>\n				<input type=\"button\" value=\"Mitteilung absenden\" onclick=\"multiCMS.formHandler(%%&ESCSLASH%%&kontaktFormular%%&ESCSLASH%%&);\" />\n				<input type=\"hidden\" name=\"HandlerID\" value=\"%%%HANDLER%%%\" />\n			</td>\n		</tr>\n	</table>\n</form>\n<p>', 0, 1),
(8, 'dlTemplate', 'default download', '%%%TEXT%%%\n<table>\n<colgroup>\n<col class=\"s1\" style=\"width:20px\" />\n<col />\n<col class=\"s1\" />\n<col />\n<col class=\"s1\" />\n</colgroup>\n%%%DOWNLOADS%%%\n</table>', 0, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
