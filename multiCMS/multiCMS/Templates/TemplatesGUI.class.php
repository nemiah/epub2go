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
class TemplatesGUI extends Templates implements iGUIHTMLMP2, iCategoryFilter {
	
	function getHTML($id, $page){
		$this->addOrderV3("templateType", "ASC");
		$this->loadMultiPageMode($id, $page, 0);
		
		$gui = new HTMLGUIX($this);
		$gui->version("Templates");
		$gui->name("Template");
		
		
		
		#$gui->showFilteredCategoriesWarning($this->filterCategories(), $this->getClearClass());
		#$gesamt = $this->loadMultiPageMode($id, $page, 0);
		#$gui->setMultiPageMode($gesamt, $page, 0, "contentRight", str_replace("GUI","",get_class($this)));
		
		$bps = $this->getMyBPSData();
		
		if($this->numLoaded() == 0 AND !isset($bps["skipNew"])) {
			$BJa = new Button("Ja", "bestaetigung");
			$BJa->style("margin:10px;float:right;");
			$BJa->rmePCR("Templates", "-1", "createDefaults", "", OnEvent::reload("Right"));
			
			$BNein = new Button("Nein", "stop");
			$BNein->style("margin:10px;");
			$BNein->loadFrame("contentRight", "Templates", -1, 0, "TemplatesGUI;skipNew:true");
			
			return "<p>Sie haben noch keine Templates. Möchten Sie jetzt Standard-Templates erzeugen?</p>$BJa$BNein";
		}
		
		#if($this->collector != null) $gui->setAttributes($this->collector);
		$gui->attributes(array("aktiv","name"));
		
		$gui->parser("aktiv","aktivParser");
		$gui->parser("name","parserName");
		$gui->colWidth("aktiv","20px");
		
		$gui->displayGroup("templateType", "TemplatesGUI::dgParser");#, $this->getAvailableCategories());
		#$gui->setCollectionOf($this->collectionOf);
		
		#try {
			return $gui->getBrowserHTML($id);
		#} catch (Exception $e){ }
	}
	
	public static function unused($Template){
		if($Template->A("templateType") == "domainTemplate"){
			$U = anyC::getFirst("Domain", "TemplateID", $Template->getID());
			return $U === null;
		}
		
		if($Template->A("templateType") == "pageTemplate"){
			$U = anyC::getFirst("Seite", "TemplateID", $Template->getID());
			return $U === null;
		}
		
		if($Template->A("templateType") == "contentTemplate"){
			$U = anyC::getFirst("Content", "TemplateID", $Template->getID());
			return $U === null;
		}
	}
	
	public static function parserName($w, $E){
		$B = "";
		if(self::unused($E)){
			$B = new Button("Unbenutzt", "./images/i2/clear.png", "icon");
			$B->style("float:right;");
		}
		return $B.$w;
	}
	
	public static function dgParser($n){
		$T = new TemplatesGUI();
		$d = $T->getAvailableCategories();
		
		return $d[$n];
	}
	
	public function createDefaults(){
		$DB = new DBStorage();
		$C = $DB->getConnection();
		
		$C->query("INSERT INTO `Template` (`TemplateID`, `templateType`, `name`, `html`, `TemplateDomainID`, `aktiv`) VALUES
(1, 'contentTemplate', 'default content', '%%%TEXT%%%', 0, 1),
(2, 'naviTemplate', 'default navigation', '				<div class=\"tab\">\n					<p>%%%LINK%%%</p>\n				</div>', 0, 1),
(10, 'naviTemplate', 'default navigation active', '				<div class=\"tab current\">\n					<p>%%%LINK%%%</p>\n				</div>', 0, 0),
(3, 'pageTemplate', 'default page', '\n		<div class=\"text\">\n		<div class=\"bgWrapper\">\n		<h1>%%%HEADER%%%</h1>\n		%%%CONTENT%%%\n		<div class=\"footer\"></div>\n		</div>\n		</div>', 0, 1),
(4, 'domainTemplate', 'default domain', '<!DOCTYPE html>\n<!--[if lt IE 7]> <html class=\"no-js lt-ie9 lt-ie8 lt-ie7\"> <![endif]-->\n<!--[if IE 7]> <html class=\"no-js lt-ie9 lt-ie8\"> <![endif]-->\n<!--[if IE 8]> <html class=\"no-js lt-ie9\"> <![endif]-->\n<!--[if gt IE 8]><!--> <html class=\"no-js\"> <!--<![endif]-->\n    <head>\n        <meta charset=\"utf-8\">\n        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">\n	<title>%%%TITLE%%%</title>\n	<link rel=\"stylesheet\" type=\"text/css\" href=\"./multiCMSData/draft.css\" />\n		\n	<script src=\"./multiCMSData/jquery-1.7.1.min.js\" type=\"text/javascript\"></script>\n	<script src=\"./multiCMSData/multiCMS.js\" type=\"text/javascript\"></script>\n        <meta name=\"description\" content=\"\">\n        <meta name=\"viewport\" content=\"width=device-width\">\n    </head>\n    <body>\n        <!--[if lt IE 7]>\n<p class=\"chromeframe\">You are using an <strong>outdated</strong> browser. Please <a href=\"http://browsehappy.com/\">upgrade your browser</a> or <a href=\"http://www.google.com/chromeframe/?redirect=true\">activate Google Chrome Frame</a> to improve your experience.</p>\n<![endif]-->\n		<div class=\"wrapper\">\n		<div class=\"header\"><p>%%%HEADER%%%</p></div>\n		<div class=\"navi\">%%%NAVIGATION%%%</div><div class=\"spacer\"></div>%%%PAGE%%%\n		</div>\n\n    </body>\n</html>', 0, 1),
(6, 'presetTemplate', 'Kontaktformular', '%%%TEXT%%%</p>\n<form id=\"kontaktFormular\">\n	<table>\n		<colgroup>\n			<col style=\"width:150px;\" />\n			<col />\n		</colgroup>\n		<tr>\n			<td><label for=\"kontaktName\">Name:</label></td>\n			<td><input name=\"kontaktName\" id=\"kontaktName\" type=\"text\" /></td>\n		</tr>\n		<tr>\n			<td><label for=\"kontaktEMail\">E-Mailadresse:</td>\n			<td><input name=\"kontaktEMail\" id=\"kontaktEMail\" type=\"text\" /></td>\n		</tr>\n		<tr>\n			<td style=\"vertical-align:top;\"><label for=\"kontaktText\">Nachricht:</td>\n			<td><textarea name=\"kontaktText\" id=\"kontaktText\"></textarea></td>\n		</tr>\n		<tr>\n			<td></td>\n			<td>\n				<input type=\"button\" value=\"Mitteilung absenden\" onclick=\"multiCMS.formHandler(''kontaktFormular'');\" />\n				<input type=\"hidden\" name=\"HandlerID\" value=\"%%%HANDLER%%%\" />\n			</td>\n		</tr>\n	</table>\n</form>\n<p>', 0, 1),
(8, 'dlTemplate', 'default download', '%%%TEXT%%%\n<table>\n<colgroup>\n<col class=\"s1\" style=\"width:20px\" />\n<col />\n<col class=\"s1\" />\n<col />\n<col class=\"s1\" />\n</colgroup>\n%%%DOWNLOADS%%%\n</table>', 0, 1);");
	}
	
	function getAvailableCategories(){
		return array("contentTemplate" => "Content-Template", "presetTemplate" => "HTML-Template", "pageTemplate" => "Page-Template", "domainTemplate" => "Domain-Template",/* "listTemplate" => "List-Template", "tableTemplate" => "Table-Template",*/ "dlTemplate" => "Download-Template", "naviTemplate" => "Navigation-Template");
	}
	
	function getCategoryFieldName(){
		return "templateType";
	}
	
	public function activate($id){
		$S = new Template($id);
		$S->changeA("aktiv","1");
		
		$this->addAssocV3("aktiv","=","1");
		$this->addAssocV3("templateType", "=", $S->getA()->templateType);
		while($t = $this->getNextEntry()){
			$t->changeA("aktiv","0");
			$t->saveMe();
		}
		
		$S->saveMe();
	}
	
	public static function aktivParser($w, $E){
		return $w == 1 ? "<img src=\"./images/i2/ok.gif\" title=\"ist default-Template dieser Kategorie\" />" : "<img src=\"./images/i2/notok.gif\" title=\"als default-Template dieser Kategorie markieren\" onclick=\"rme('Templates','','activate','".$E->getID()."','contentManager.reloadFrameRight();');\" class=\"mouseoverFade\" />";
	}
	
	public static function getDefault($templateType){
		$T = new TemplatesGUI();
	
		$T->addAssocV3("aktiv","=","1");
		$T->addAssocV3("templateType", "=", $templateType);
		$t = $T->getNextEntry();
		
		return $t->getID();
	}
}
?>