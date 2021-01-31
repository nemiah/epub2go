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
class SeiteGUI extends Seite implements iGUIHTML2 {
	function getHTML($id){
		
		$U = new mUserdata();
		$U = $U->getUDValue("selectedDomain");
		
		if($id == -1) {
			$this->A = $this->newAttributes();
			$this->A->DomainID = $U;
			$this->A->header = "leere Seite";
			if($_SESSION["S"]->checkForPlugin("Templates"))
				$this->A->TemplateID = TemplatesGUI::getDefault("pageTemplate");
			
			$id = $this->newMe();
			
			$c = new Content(-1);
			$cA = $c->newAttributes();
			$c->setA($cA);
			
			$c->changeA("SeiteID", $id);
			if($_SESSION["S"]->checkForPlugin("Templates"))
				$c->changeA("TemplateID", TemplatesGUI::getDefault("contentTemplate"));
			$c->newMe();
			
			$this->forceReload();
		}
		
		
		if($this->A == null) $this->loadMe();	
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Seite");

		$gui->setShowAttributes(array("header", "DomainID", "TemplateID", "name", "metaTagDescription", "metaTagKeywords","permalink"));
		
		$gui->insertSpaceAbove("name","sonstiges", true);
		
		$gui->setFieldDescription("name","wird nur intern angezeigt");
		$gui->setFieldDescription("permalink","Ein Name, unter der die Seite über ?permalink= erreichbar ist. Darf nur aus Buchstaben (keine Umlaute), Zahlen, _ und - bestehen und muss eindeutig für die Domain sein. Es kann dann mit mod_rewrite auf diesen permalink an Stelle der SeitenID verlinkt werden.");
		$gui->setFieldDescription("metaTagDescription","<span id=\"charCounter\">".strlen($this->A("metaTagDescription"))."</span> Zeichen");
		
		$gui->setType("DomainID","hidden");
		$gui->setType("metaTagKeywords","textarea");
		$gui->setType("metaTagDescription","textarea");
		
		$gui->setLabel("header","Seitenname");
		$gui->setLabel("TemplateID","Vorlage");
		$gui->setLabel("DomainID","Domain");
		$gui->setLabel("metaTagDescription","Description-meta tag");
		$gui->setLabel("metaTagKeywords","Keywords-meta tag");
		
		$gui->setInputJSEvent("metaTagDescription", "onkeyup", "$('charCounter').update($('metaTagDescription').value.length)");
		
		$gui->setInputStyle("metaTagDescription","font-size:10px;");
		$gui->setInputStyle("metaTagKeywords","font-size:10px;");

		if(Session::isPluginLoaded("mMultiLanguage"))
			$gui->activateFeature("addAnotherLanguageButton", $this, "header");

		if(Session::isPluginLoaded("Templates")){
			$T = new anyC();
			$T->setCollectionOf("Template");
			$T->addAssocV3("templateType","=","pageTemplate", "AND", "1");
			$T->addAssocV3("TemplateDomainID", "=", "0", "AND", "2");
			$T->addAssocV3("TemplateDomainID", "=", $this->A("DomainID"), "OR", "2");
			
			$gui->selectWithCollection("TemplateID", $T, "name");
		} else $gui->setType("TemplateID","hidden");

			$T = new anyC();
			$T->setCollectionOf("Domain");
			
			$gui->selectWithCollection("DomainID", $T, "url", "Globale Seite");
		
		$gui->setStandardSaveButton($this);


		$H = "";
		$E = "";
		$C = new mContentGUI();
		$C->addAssocV3("SeiteID","=", $this->ID);
		$C->lCV3();
		if($C->numLoaded() == 1){
			$content = $C->getNextEntry();
			$H = new ContentGUI($content->getID());
			$H->singular = true;
			$H = "<div style=\"height:20px;width:20px;\"></div>".$H->getHTML($content->getID());
		} else {
			$E = $C->getHTML(-1);
		}
		
		$tab = new HTMLTable(1);
		$tab->setTableStyle("margin-top:20px;");
		$B = new Button("Content\nhinzufügen","gutschrift");
		$B->rmePCR("Seite", $this->ID, "createContent","","contentManager.reloadFrame('contentLeft');");
		$tab->addRow($B);
		
		return $gui->getEditHTML().$H.$tab.$E;
	}
	
	public function getCMSHTML($startseite = 0, $DomainID = null, Domain $Domain = null){
		$this->loadMe();

		try{
			$test = new MultiLanguage(-1);
			
			$Dom = new Domain($DomainID);
			$contentLanguage = $Dom->A("DomainDefaultSpracheID");

			$userLang = CCMultiLanguage::getUserLanguage();

			if($userLang != null AND $userLang->getID() != null AND $userLang->getID() != $Dom->A("DomainDefaultSpracheID")){
				$contentLanguage = $userLang->getID();
				
				$mL = MultiLanguage::getTranslation($userLang->getID(), "Seite", $this->getID(), "header");
				if($mL != null)
					$this->changeA("header", $mL);
			}

		} catch(ClassNotFoundException $e){ }

		
		#try {
		#	new Tracker(-1);
		#	Tracker::trackUser($this->ID, $this->A->DomainID, "page");
		#	Tracker::trackPage($this->ID, $this->A->DomainID);
		#} catch(ClassNotFoundException $e) {  }

		$Template = new Template($this->A->TemplateID);

		$html = $Template->getA()->html;
		
		$html = str_replace("%%%HEADER%%%",$this->A("header"), $html);
		$html = str_replace("%%%DOMAIN%%%",strtolower($_SERVER["HTTP_HOST"]), $html);
		
		$html = SeiteGUI::replaceFunctionCalls($html, $this);
		
		$Contents = new mContentGUI();
		$Contents->addOrderV3("sort","ASC");
		$Contents->addAssocV3("SeiteID","=",$this->ID);
		$Contents->addAssocV3("ContentSpracheID", "=", "0", "AND", "2");
		if(isset($contentLanguage)) $Contents->addAssocV3("ContentSpracheID", "=", $contentLanguage, "OR", "2");

		$Contents->lCV3();

		$content = "";
		while(($C = $Contents->getNextEntry())){
			$CGUI = $C->getGUIClass();
			$content .= $CGUI->getCMSHTML($this, $Domain);
		}
		
		if(!isset($_GET["contentOnly"])) $html = str_replace("%%%CONTENT%%%",$content, $html);
		else $html = $content;
		if($Contents->numLoaded() == 0) $nS = new SeiteGUI($startseite);
		
		return ($Contents->numLoaded() != 0 ? $html : $nS->getCMSHTML());
	}
	
	public static function replaceFunctionCalls($html, $object){
	
		preg_match_all("/\[\[([a-zA-Z0-9:]*)\]\]/", $html, $treffer);

		if(count($treffer) > 0){
			$treffer = array_unique($treffer[1]);
			foreach($treffer AS $k => $v){
				$s = explode("::", $v);
			
				try {
					$c = $s[0];
					$c = new $c();
					if(PMReflector::implementsInterface($s[0], "iCustomContent") AND method_exists($c, $s[1])){
						
						$method = new ReflectionMethod($s[0], $s[1]);
						$html = str_replace("[[$v]]", $method->invoke(null, $object) ,$html);
						
					} else str_replace("[[$v]]","[[$s does not implement iCustomContent]]" ,$html); 
				} catch(ClassNotFoundException $e) {  }
			}
		}
		
		return $html;
	}
	
	public function createContent(){
			$F = new Factory("Content");

			$F->sA("SeiteID", $this->ID);
			$F->sA("TemplateID", TemplatesGUI::getDefault("contentTemplate"));

			$F->store(true, true);
	}
}
?>