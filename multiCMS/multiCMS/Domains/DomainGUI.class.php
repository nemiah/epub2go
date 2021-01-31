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
class DomainGUI extends Domain implements iGUIHTML2 {
	private $seite = 0;
	
	function getHTML($id){
		
		$this->loadMeOrEmpty();
				
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Domain");
		
		if($id != -1) {
			$Seiten = new SeitenGUI();
			$Seiten->addAssocV3("DomainID","=",$this->getID());
			$Seiten->setFieldsV3(array("SeiteID","IF(name = '', header , name) AS name"));
			$gui->selectWithCollection("startseite",$Seiten,"name");
			
			$Seiten->resetPointer();
			$gui->selectWithCollection("fehlerseite",$Seiten,"name", "Standard");
		} else {
			$gui->setParser("startseite","DomainGUI::startseiteParser");
		}
		$T = new TemplatesGUI();
		$T->addAssocV3("templateType","=","domainTemplate");
		$gui->selectWithCollection("TemplateID",$T,"name");
		$gui->setStandardSaveButton($this);
		#$gui->setSaveButtonValues(get_parent_class($this),$this->ID,"Domains");

		$gui->setShowAttributes(array(
			"TemplateID",
			"url",
			"DomainDefaultSpracheID",
			"startseite",
			"fehlerseite",
			"title",
			"header",
			"umleitung",
			"https",
			"permalinkPrefix",
			"horizontalNav"));

		if(Session::isPluginLoaded("mSprache")){
			$mS = new anyC();
			$mS->setCollectionOf("Sprache");

			$gui->selectWithCollection("DomainDefaultSpracheID", $mS, "SpracheIdentifier", "keine Auswahl");
		} else
			$gui->setType("DomainDefaultSpracheID", "hidden");

		$gui->setLabel("DomainDefaultSpracheID","Sprache");
		$gui->setLabel("url","Domains");
		$gui->setLabel("title","Titelzeile");
		$gui->setLabel("header","Header");
		$gui->setLabel("permalinkPrefix","Permalink-Präfix");
		$gui->setLabel("horizontalNav","horizontale Navigation");
		$gui->setLabel("https", "HTTPS?");
		
		$gui->setType("https","checkbox");
		$gui->setType("horizontalNav","checkbox");
		$gui->setFieldDescription("horizontalNav","Subkategorien in der Navigation werden nicht zwischen den Einträgen angezeigt sondern am Ende angehängt.");
		
		$gui->setFieldDescription("umleitung","<b>www-Umleitung</b><br />Anfragen an Adressen ohne www-Subdomain werden auf die www-subdomain umgeleitet. Also es würde http://example.com an http://www.example.com umgeleitet. Nicht jedoch http://test.example.com<br /><br /><b>erster Eintrag</b><br />Alle Anfragen werden auf den ersten Eintrag der Liste umgeleitet.");
		$gui->setType("umleitung","select");
		
		$gui->setOptions("umleitung", array("0","1","2"),array("keine","www-Umleitung","erster Eintrag"));
		
		$gui->setType("url","textarea");
		$gui->setFieldDescription("url","geben Sie eine Domain pro Zeile an oder * für eine beliebige Domain");
		
		$tab = new HTMLTable(1);
		$tab->addRow("Das Permalink-Präfix wird vor den Permalink geschrieben, wenn er für eine Seite eingetragen wurde.<br /><br />Wenn Sie also \"page-\" als Präfix angeben, wird der Permalink für die Startseite so aussehen (wenn der Permalink für die Startseite \"Startseite\" lautet) page-Startseite.<br /><br />Sie müssen diesen Permalink dann noch mit mod_rewrite umschreiben. Mit präfix \"page-\" dann zum Beispiel:<pre style=\"font-size:9px;\">RewriteEngine on
RewriteRule ^page-([a-zA-Z0-9-_]*)$ ?permalink=$1</pre>");
		
		return $gui->getEditHTML().$tab;
	}
	
	public static function startseiteParser(){
		return "<small>Noch nicht verfügbar, bitte speichern Sie diesen Eintrag zuerst.</small>";
	}
	
	public function setSeite($seite){
		$this->seite = $seite;
	}
	
	public function getCMSHTML(){

		$Template = new Template($this->A->TemplateID);
		if($Template->getA() == null)
			emoFatalError("multiCMS kann die Seite leider nicht erzeugen", "Das ausgew&auml;hlte Domain-Template wurde nicht gefunden.<br />Bitte w&auml;hlen Sie ein neues Template bei der Domain ".$this->A("url")." (".$this->A("title").") aus", "multiCMS", "./multiCMS");#die("The selected domain-template does not exist.");
		
		$html = $Template->getA()->html;
		$navi = new mNavigationGUI();

		$Seite = new SeiteGUI($this->seite != 0 ? $this->seite : $this->A->startseite);
		#$Scripts = new ScriptsGUI();
		#Seite->loadMe();

		if($Seite->A("permalink") != "" AND !isset($_GET["permalink"]) AND $this->A->startseite != $Seite->getID()){
			$ex = explode("\n", $this->A("url"));
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: http".((isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on") ? "s" : "")."://$ex[0]/".$this->A("permalinkPrefix").$Seite->A("permalink"));
			header("Connection: close");
			exit();
		}

		if($Seite->getA() == null) {
			if($this->A("fehlerseite") == "0"){
				header("HTTP/1.0 404 Not Found");
				emoFatalError("Die gesuchte Seite kann leider nicht gefunden werden", "Die Seite, die Sie suchen, existiert nicht (mehr).<br />Vielleicht m&ouml;chten Sie die Suche auf der <a href=\"/\">Startseite</a> fortsetzen.", "multiCMS", "./multiCMS");
			} else {
				header("HTTP/1.0 404 Not Found");
				$Seite = new SeiteGUI($this->A("fehlerseite"));
			}
		}

		if(strpos($html, "%%%SEITE%%%") === false) 
			$html = str_replace("%%%PAGE%%%", $Seite->getCMSHTML($this->A->startseite, $this->ID, $this), $html);
		else
			$html = str_replace("%%%SEITE%%%", $Seite->getCMSHTML($this->A->startseite, $this->ID, $this), $html);


		$metaTagDesc = $Seite->A("metaTagDescription");
		if($metaTagDesc == "") {
			$StartSeite = new Seite($this->A->startseite);
			$StartSeite->loadMe();
			$metaTagDesc = $StartSeite->A("metaTagDescription");
		}
		
		$metaTagKey = $Seite->A("metaTagKeywords");
		if($metaTagKey == "") {
			$StartSeite = new Seite($this->A->startseite);
			$StartSeite->loadMe();
			$metaTagKey = $StartSeite->A("metaTagKeywords");
		}
		
		$html = str_replace("%%%NAVIGATION%%%",$navi->getCMSHTML(0, $this->seite != 0 ? $this->seite : $this->A->startseite, $this->ID, $this), $html);
		$html = str_replace("%%%HEADER%%%",$this->A->header, $html);
		$html = str_replace("%%%TITLE%%%",($this->A("title") != "" ? $this->A("title") : "").($Seite->A("header") != "" ? " - ".$Seite->A("header") : ""), $html);
		#$html = str_replace("%%%SCRIPTS%%%",$Scripts->getCMSHTML($this->ID), $html);
		$html = str_replace("%%%DESCRIPTION%%%",$metaTagDesc, $html);
		$html = str_replace("%%%KEYWORDS%%%",$metaTagKey, $html);

		$html = SeiteGUI::replaceFunctionCalls($html, $this);

		if(isset($_GET["contentOnly"]))
			echo $Seite->getCMSHTML($this->A->startseite, $this->ID, $this);
		else
			echo $html;
	}
}
?>