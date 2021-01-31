<?php
/*
 *  This file is part of multiCMS.

 *  multiCMS is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  multiCMS is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
error_reporting(E_ALL);

if(function_exists('date_default_timezone_set'))
	date_default_timezone_set('Europe/Berlin');

require "./multiCMSData/connect.php";
if(isset($_POST["formID"]) OR isset($_GET["formID"])){
	Handler::handleForm(isset($_POST["formID"]) ? $_POST : $_GET);
	exit();
}

if(isset($_GET["d"])) $_SERVER["HTTP_HOST"] = $_GET["d"];

if(isset($_GET["AJAXClass"])){
	$c = $_GET["AJAXClass"];
	if(substr($c, 0, 2) != "CC")
		$c = "CC$c";
	
	$c = new $c();

	$parameters = array();
	if(isset($_GET["AJAXParameters"]))
		$parameters = explode(";;;", $_GET["AJAXParameters"]);
	
	$R = new ReflectionMethod($_GET["AJAXClass"], $_GET["AJAXMethod"]);
	echo $R->invokeArgs($c, $parameters);
		
	exit();
}

if(isset($_GET["filedl"])) {
	$DL = new Download($_GET["filedl"]);
	$DL->makeDownload();
	header("Location: ".$DL->getA()->url);
	exit();
}

if(isset($_GET["newestdl"])) {
	$aC = new anyC();
	$aC->setCollectionOf("Download");
	$aC->addAssocV3("ContentID","=",$_GET["newestdl"]);
	$aC->addOrderV3("datum","DESC");
	$aC->setLimitV3("1");
	$DL = $aC->getNextEntry();
	$DL = new Download($DL->getID());

	if(!isset($_GET["getLink"])) {
		$DL->makeDownload();
		header("Location: ".$DL->getA()->url);
		exit();
	}
	else die($DL->getA()->url);
}

if(isset($_GET["a"]))
	switch($_GET["a"]) {
		case "DBImage":
			$DBI = new DBImageGUI();
			$DBI->getHTML($_GET["id"]);
		break;

		case "putInCart":
			$CC = new CookieCart();
			$CC->update($_GET["artikelID"],$_GET["menge"]);
		break;
	}
try {
	$domains = new Domains();
	$domain = $domains->getMyDomain();
	$ex = explode("\n", $domain->A("url"));
	
	if(isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) AND $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https")
		$_SERVER["HTTPS"] = "on";
	
	$newLocation = null;
	if($domain->A("https") AND (!isset($_SERVER["HTTPS"]) OR $_SERVER["HTTPS"] != "on"))
		$newLocation = "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	
	if($domain->A("umleitung") != null){
		if($domain->A("umleitung") == "1" AND substr_count(str_replace(".uk", "", $_SERVER["HTTP_HOST"]),".") == 1)
			$newLocation = "http".(((isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on") OR $domain->A("https")) ? "s" : "")."://www.".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		

		if(count($ex) > 0 AND $domain->A("umleitung") == "2" AND $_SERVER["HTTP_HOST"] != trim($ex[0]))
			$newLocation = "http".(((isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on") OR $domain->A("https")) ? "s" : "")."://".trim($ex[0]).$_SERVER["REQUEST_URI"];
		
	}
	
	if($newLocation){
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $newLocation");
		header("Connection: close");
		die();
	}

	if(isset($_GET["permalink"]) AND $_GET["permalink"] != ""){
		$ac = new anyC();
		$ac->setCollectionOf("Seite");
		$ac->addAssocV3("permalink","=",$_GET["permalink"]);
		$ac->addAssocV3("DomainID","=",$domain->getID());
		$resolvedPL = $ac->getNextEntry();

		#if($resolvedPL == null) {
			#header("HTTP/1.1 404 Not Found");
			#emoFatalError("Die gesuchte Seite kann leider nicht gefunden werden", "Die Seite, die Sie suchen, existiert nicht (mehr).<br />Vielleicht m&ouml;chten Sie die Suche auf der <a href=\"/\">Startseite</a> fortsetzen.", "multiCMS", "./multiCMS");
			#header("Connection: close");
			#exit();
			#die("Die gew&uuml;nschte Seite existiert nicht!");
		#}
		if($resolvedPL != null)
			$_GET["p"] = $resolvedPL->getID();
		else {
			$ac = new anyC();
			$ac->setCollectionOf("Seite");
			$ac->addAssocV3("permalink","=",$_GET["permalink"]);
			$ac->addAssocV3("DomainID","=",0);
			$resolvedPL = $ac->getNextEntry();
			
			if($resolvedPL != null)
				$_GET["p"] = $resolvedPL->getID();
			else
				$_GET["p"] = -1;
		}
		
		if($ac->numLoaded() > 1)
			die("der Permalink $_GET[permalink] ist nicht eindeutig und verweist auf ".$ac->numLoaded()." Seiten!");
	}


	if(isset($_GET["p"])) $domain->setSeite($_GET["p"]);
	$domain->getCMSHTML();
} catch(Exception $e){
	switch(get_class($e)){
		case "NoDBUserDataException":
			emoFatalError("multiCMS kann leider keine Verbindung zur Datenbank herstellen","Es wurden noch keine Datenbankzugangsdaten eingetragen.<br />Bitte benutzen Sie das Installations-Plugin im Admin-Bereich von <a href=\"$phpFWPath\">multiCMS</a>","multiCMS", $phpFWPath);
		break;

		case "TableDoesNotExistException":
			emoFatalError("multiCMS kann leider eine oder mehrere Tabellen nicht finden","Die multiCMS Datenbank-Tabellen wurden vermutlich nicht korrekt angelegt.<br />Bitte benutzen Sie das Installations-Plugin im Admin-Bereich von <a href=\"$phpFWPath\">multiCMS</a>","multiCMS", $phpFWPath);
		break;

		case "FieldDoesNotExistException":
			die("Ein oder mehrere Felder wurden in der Datenbank nicht gefunden.<br />Bitte benutzen Sie das Installations-Plugin von <a href=\"$phpFWPath\">multiCMS</a>:".$e->getErrorMessage());
		break;

		default:
			emoFatalError("Es ist leider ein unvorhergesehener Fehler aufgetreten","Falls Ihnen folgender Fehler nichts sagt, wenden Sie sich bitte mit der Fehlermeldung an das <a href=\"http://forum.furtmeier.it\">Support-Forum</a>:<h2>".get_class($e)."</h2><pre>".$e->getTraceAsString()."</pre>","multiCMS", $phpFWPath);
		break;
	}
}
?>