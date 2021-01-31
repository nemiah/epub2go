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
class NavigationGUI extends Navigation implements iGUIHTML2 {
	function getHTML($id){
		$U = new mUserdata();
		$U = $U->getUDValue("selectedDomain");
		
		$this->loadMeOrEmpty();
		
		if($id == -1 AND $U != null) {
			$this->A->DomainID = $U;
			$this->A->sort = -1;
			$this->A->SeiteID = -1;
			$this->A->linkType = "cmsPage";
		}
		
		$gui = new HTMLGUIX($this);
		$gui->name("Navigation");
		
		$N = new mNavigation();
		$N->addAssocV3("NavigationID","!=", $this->getID());
		$N->addAssocV3("DomainID","=",$U);
		
		$gui->type("parentID", "select", $N, "name", "kein übergeordnetes Element");

		#$gui->parser("SeiteID", "NavigationGUI::SeiteParser");
		
		$gui->type("DomainID", "hidden");
		$gui->space("activeTemplateID");
		$gui->space("linkType");
		$gui->space("hidden", "Optionen");
		
//		$gui->setType("sort","hidden");
		$gui->type("sort", "hidden");
		$gui->type("httpsLink", "checkbox");
		
		$gui->space("activeTemplateID", "Templates");
		$gui->space("linkType");
		$gui->space("hidden", "Optionen");
		
		
		$gui->label("httpsLink", "Https-Link?");
		
		$gui->label("activeTemplateID", "Link aktiv");
		$gui->label("inactiveTemplateID", "Link inaktiv");
		
		$gui->label("DomainID", "Domain");
		$gui->label("SeiteID", "Seite");
		$gui->label("parentID", "Vaterelement");
		
		$aC = new anyC();
		$aC->setCollectionOf("Seite");
		$aC->setFieldsV3(array("IF(name = '', header, name) AS name"));
		$aC->addAssocV3("DomainID","=",$U);
		$aC->addOrderV3("name");
		
		$Seiten = array();
		while($S = $aC->getNextEntry())
			$Seiten[$S->getID()] = $S->A("name")." (".$S->getID().")";
		
		$aC = new anyC();
		$aC->setCollectionOf("Seite");
		$aC->setFieldsV3(array("IF(name = '', header, name) AS name"));
		$aC->addAssocV3("DomainID", "=", 0);
		$aC->addOrderV3("name");
		
		
		while($S = $aC->getNextEntry())
			$Seiten[$S->getID()] = "Global ".$S->A("name")." (".$S->getID().")";
		
		$gui->type("SeiteID", "select", $Seiten);
		
		if(Session::isPluginLoaded("mMultiLanguage"))
			$gui->activateFeature("addAnotherLanguageButton", $this, "name");

		$gui->label("linkType","Link-Typ");
		$gui->label("linkURL","Link-URL");
		
		$T = new TemplatesGUI();
		$T->addAssocV3("templateType","=","naviTemplate");
		$gui->type("activeTemplateID", "select", $T, "name");
		
		$T = new TemplatesGUI();
		$T->addAssocV3("templateType","=","naviTemplate");
		$gui->type("inactiveTemplateID", "select", $T, "name");
		

		$gui->label("hidden","versteckt");
		$gui->descriptionField("hidden", "Der Menüpunkt wird auf der Seite nicht angezeigt");
		$gui->type("hidden","checkbox");

		$gui->label("displaySub","Unterkat. immer anzeigen");
		$gui->descriptionField("displaySub","Blendet die Unterkategorien immer ein, auch wenn der Menüpunkt nicht ausgewählt ist.");
		$gui->type("displaySub","checkbox");
		$gui->descriptionField("httpsLink","Erzeugt einen https://...-Link");
		$gui->descriptionField("loginType", "Anmerkung: \$_SESSION[\"C\"]");
		$gui->label("loginType", "Login");
		$gui->type("loginType", "select", array("0" => "Immer Anzeigen", "1" => "Nur Angemeldet", "2" => "Nur Abgemeldet"));
		
		
		$gui->type("linkType", "select", array("cmsPage" => "multiCMS-Seite", "url" => "URL", "separator" => "Trennlinie", "HTML" => "Template-HTML"));
		$gui->toggleFieldsInit("linkType", array("SeiteID", "inactiveTemplateID", "activeTemplateID", "linkURL"));
		$gui->toggleFields("linkType", array("cmsPage"), array("SeiteID", "inactiveTemplateID", "activeTemplateID"));
		$gui->toggleFields("linkType", array("url"), array("linkURL", "inactiveTemplateID"));
		$gui->toggleFields("linkType", array("HTML"), array("activeTemplateID"));
		
		if($id == -1) $gui->addToEvent("onSave","$('contentLeft').update(); contentManager.reloadFrameRight();");
		else $gui->addToEvent("onSave","contentManager.reloadFrameRight();");
		

		return $gui->getEditHTML().OnEvent::script("\$j('select[name=SeiteID]').prop('size', 10);");
	}
	
	/*public static function SeiteParser($w, $l, $p){
		$Seite = new Seite($w);
		$Seite->loadMe();
		
		$aC = new anyC();
		$aC->setCollectionOf("Seite");
		$aC->setFieldsV3(array("IF(name = '', header, name) AS name"));
		$aC->addAssocV3("DomainID", "=", $p->A("DomainID"));
//		$aC->addAssocV3("DomainID","=",$p);
		
		$select = "
		<ul style=\"list-style-image:none;list-style-type:none;\">";
		
		#$select .= NavigationGUI::getOption(-1, "Neue Seite erstellen", $w, "./images/i2/new.gif");
		$select .= NavigationGUI::getOption(0, "Keine Seite", $w, "./images/i2/stop.png","margin-bottom:5px;");
		
		while($s = $aC->getNextEntry())
			$select .= NavigationGUI::getOption($s->getID(), $s->A("name"), $w);
		
		$label = $Seite->A("name") == "" ? $Seite->A("header") : $Seite->A("name");
		if($Seite->getA() == null) $label = "Seite unbekannt";
		#if($w == -1) $label = "Neue Seite erstellen";
		if($w == 0) $label = "Keine Seite";
			
		$select .= "
		</ul>";
		
		$html = "
		<input type=\"hidden\" value=\"$w\" name=\"SeiteID\" />
		
		<div onclick=\"if($('pageSelection').style.display == 'none') new Effect.BlindDown('pageSelection', { duration: 0.3 }); else new Effect.BlindUp('pageSelection', { duration: 0.3 });\"
			style=\"background-image:url(./images/i2/go-down.png);background-repeat:no-repeat;background-position:99% 2px;width:246px;padding:3px;border-bottom-style:dotted;border-bottom-width:1px;\" class=\"borderColor1 backgroundColor0\">
			<span id=\"selectedPage\">$label</span>
		</div>
		<div id=\"pageSelection\" class=\"backgroundColor0 borderColor1\" style=\"border-width:1px;border-style:solid;border-top-width:0px;position:absolute;display:none;width:250px;\">
			<div style=\"overflow:auto;height:150px;\">
			$select
			</div>
		</div>";
		
		return $html;
	}*/
	
	private static function getOption($value, $label, $preset, $backgroundImage = "", $style = ""){
		return "
			<li
				onclick=\"
					$('selectedPage').update('$label');
					new Effect.BlindUp('pageSelection', { duration: 0.3 });
					
					if($('SeiteIDValues'+$('AjaxForm').SeiteID.value))
						$('SeiteIDValues'+$('AjaxForm').SeiteID.value).style.fontWeight = 'normal';
					
					this.style.fontWeight = 'bold';
					$('AjaxForm').SeiteID.value = '$value';\"
				style=\"padding:3px;margin:0px;cursor:pointer;background-position:99% 2px;background-repeat:no-repeat;".($backgroundImage != "" ? "background-image:url(".$backgroundImage.");" : "").($preset == $value ? "font-weight:bold;" : "")."$style\"
				onmouseover=\"this.className = 'backgroundColor2';\"
				onmouseout=\"this.className = '';\"
				id=\"SeiteIDValues".$value."\">$value - $label</li>";
	}
}
?>
