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
class ContentGUI extends Content implements iGUIHTML2 {
	public $singular = false;

	function getHTML($id){
		$type = "none";
		
		$U = new mUserdata();
		$U = $U->getUDValue("selectedDomain");
		
		$bps = $this->getMyBPSData();
		if($bps != -1 AND isset($bps["type"])) $type = $bps["type"];

		$this->loadMeOrEmpty();
		
		if($id == -1)
			if($type != "none" AND $type != "undefined")
				$this->A->SeiteID = $type;
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Content");

		$gui->setType("contentType","select");
		$gui->setOptions("contentType", array("text","preset","downloads","php"), array("Text", "HTML-Vorlage", "Downloads","PHP"));
		addClassPath(FileStorage::getFilesDir());
		$FB = new FileBrowser();
		$FB->addDir("../specifics/");
		$FB->addDir(FileStorage::getFilesDir());
		#if($_SESSION["S"]->checkForPlugin("mShop")) $FB->addDir("../multiCMS/Shop/");
		
		$a = $FB->getAsLabeledArray("iCustomContent",".class.php");
		
		$gui->setLabel("customContent","Inhalt");
		$gui->setType("customContent","select");
		$gui->setOptions("customContent", array_values($a), array_keys($a));

		$Tab = new HTMLTable(1);

		if($this->singular){
			$gui->setType("sort", "hidden");
		} else {

			$B = new Button("zurück","back");
			$B->onclick("contentManager.loadFrame('contentLeft','Seite', ".$this->A("SeiteID").");");
			
			$Tab->addRow($B);
		}

		$gui->setLabel("contentType","Typ");
		$gui->setLabel("ContentImage","Bild");
		$gui->setLabel("SeiteID","Seite");
		$gui->setLabel("TemplateID","Vorlage");
		$gui->setLabel("sort","Sortierung");

		$gui->setFieldDescription("ContentImage", "Wird an Stelle des Parameters %%%IMAGE%%% eingesetzt.");
		#$gui->setType("header","hidden");

		$gui->insertSpaceAbove("contentType");
		#$gui->insertSpaceAbove("TemplateID","sonstiges", true);
		$gui->setInputJSEvent("contentType", "onchange","Content.selectType(this);");
		$gui->setType("text","HTMLEditor");

		if(Session::isPluginLoaded("mFile")){
			$B = new Button("Bild auswählen","./images/i2/add.png");
			$B->type("icon");
			$B->customSelect("contentRight", $this->ID, "mFile", "Content.selectImage");

			$gui->activateFeature("addCustomButton", $this, "ContentImage", $B);
		} else
			$gui->setType("ContentImage", "hidden");
		
		$gui->setShowAttributes(array("text","ContentSpracheID","ContentImage","TemplateID","header","sort","contentType","presetTemplateID", "formHandlerID","customContent"));
		$gui->setFormID("ContentForm");

		$S = new anyC();
		$S->setCollectionOf("Seite");
		$gui->selectWithCollection("SeiteID", $S, "name");
		$gui->setType("name","hidden");
		$gui->setType("SeiteID","hidden");
		$gui->setLabel("presetTemplateID","Vorlage");
		$gui->setLabel("formHandlerID","Handler");
		$gui->setLabel("ContentSpracheID","Sprache");

		if(Session::isPluginLoaded("mSprache")){
			$Sprachen = new anyC();
			$Sprachen->setCollectionOf("Sprache");
			$gui->selectWithCollection("ContentSpracheID", $Sprachen, "SpracheIdentifier", "alle");
		} else $gui->setType("ContentSpracheID", "hidden");

		$aC = new anyC();
		$aC->setCollectionOf("Template");
		$aC->addAssocV3("templateType","=","presetTemplate");
		$gui->selectWithCollection("presetTemplateID", $aC, "name");
		
		if($_SESSION["S"]->checkForPlugin("mHandler")){
			$handlerAC = new anyC();
			$handlerAC->setCollectionOf("Handler");
			$handlerAC->addAssocV3("HandlerDomainID","=", $U);
			$gui->selectWithCollection("formHandlerID", $handlerAC, "HandlerName","keiner");
		} else $gui->setParser("formHandlerID","ContentGUI::noHandlerParser");
		
		if($this->A->contentType != "preset") {
			$gui->setLineStyle("presetTemplateID","display:none;");
			$gui->setLineStyle("formHandlerID","display:none;");
		}
		
		if($this->A->contentType != "php")
			$gui->setLineStyle("customContent","display:none;");
		
		$T = new TemplatesGUI();
		$T->addAssocV3("templateType","=","contentTemplate");
		$T->addAssocV3("templateType","=","listTemplate","OR");
		$T->addAssocV3("templateType","=","tableTemplate","OR");
		$T->addAssocV3("templateType","=","dlTemplate","OR");
		$T->addOrderV3("templateType");
		

		$TG = new TemplatesGUI();
		$cats = $TG->getAvailableCategories();
		
		$options = array();
		
		while($o = $T->getNextEntry())
			$options[$o->getID()] = $cats[$o->getA()->templateType];
		
		$T->resetPointer();
		
		$gui->selectWithCollection("TemplateID", $T, "name");
		$gui->selectOptgroup("TemplateID", $options);
		
		$gui->setJSEvent("onSave","function() { contentManager.loadFrame('contentLeft','Seite', ".$this->A("SeiteID")."); }");
		
		$gui->setStandardSaveButton($this);

		$gui->customize($this->customizer);

		return $Tab.$gui->getEditHTML();
	}
	
	public static function noHandlerParser(){
		return "Handler-Plugin nicht geladen";
	}
	
	public function getCMSHTML(Seite $Seite, Domain $Domain){
		#$this->setParser("text","Util::nothingParser");

		$Template = new Template($this->A->TemplateID);
		
		$html = $Template->getA()->html;
		
		$text = $this->getA()->text;
		#if($Template->getA()->templateType == "contentTemplate") $text = nl2br($text);
		#if($Template->getA()->templateType == "dlTemplate") $text = nl2br($text);
		
		#$text = ereg_replace("src=\"([0-9a-zA-Z/\. ]*)\"","test=\"\\1\"", $text);
		$text = preg_replace_callback("=src\=\"([0-9a-zA-Z/\. ]*)\"=", "preg_callback", $text);
		
		if($this->getA()->contentType == "preset") {
			$T = new Template($this->getA()->presetTemplateID);
			$presetHTML = $T->getA()->html;
			if($this->getA()->formHandlerID != 0) $presetHTML = str_replace("%%%HANDLER%%%", $this->getA()->formHandlerID, $presetHTML);
			$html = str_replace("%%%TEXT%%%", $presetHTML, $html);
		}
		
		$html = str_replace("%%%HEADER%%%",$this->getA()->header, $html);
		
		if($this->getA()->contentType == "downloads") {
			$dls = new DownloadsGUI();
			$dls->addAssocV3("ContentID","=",$this->ID);
			$dls->addOrderV3("datum","DESC");
			
			$html = str_replace("%%%DOWNLOADS%%%",$dls->getCMSHTML(), $html);
		}

		if($this->getA()->contentType == "php") {
			$n = $this->getA()->customContent;
			$c = new $n();
			$html = str_replace("%%%TEXT%%%", $c->getCMSHTML($Seite, $this, $Domain), $html);
		}
		
		$html = str_replace("%%%TEXT%%%", $text, $html);

		$html = str_replace("%%%CONTENTID%%%",$this->ID, $html);

		if($this->A("ContentImage") != ""){
			$imageSmall = str_replace(basename($this->A("ContentImage")), "small/".basename($this->A("ContentImage")), $this->A("ContentImage"));
			$imageBig = str_replace(basename($this->A("ContentImage")), "big/".basename($this->A("ContentImage")), $this->A("ContentImage"));

			$html = str_replace("%%%IMAGE%%%","<a href=\"/multiCMS/specifics/$imageBig\" rel=\"lightbox\"><img class=\"contentImage\" src=\"/multiCMS/specifics/$imageSmall\" /></a>", $html);
		}
		
		$html = SeiteGUI::replaceFunctionCalls($html, $this);
		
		return $html;
		
	}
}
	
function preg_callback($treffer){
	#$treffer[1] = "hallo";
	return "src=\"$treffer[1]\"";
}
?>