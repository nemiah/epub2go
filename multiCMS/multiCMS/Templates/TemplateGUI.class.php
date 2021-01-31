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
class TemplateGUI extends Template implements iGUIHTML2 {
	function __construct($ID){
		parent::__construct($ID);
		$this->setParser("html","Util::base64Parser");
	}
	
	function getHTML($id){
		/*$U = new mUserdata();
		$U = $U->getUDValue("selectedDomain");
		
		if($U == null) {
			$t = new HTMLTable(1);
			$t->addRow("Sie haben keine Domain ausgewählt.<br /><br />Bitte wählen Sie eine Domain im Domain-Plugin, indem Sie auf das graue Kästchen in der Liste auf der rechten Seite klicken.");
			return $t->getHTML();
		}
		
		$Domain = new Domain($U);
		$Domain->loadMe();*/
		
		$variables = array("domainTemplate", "contentTemplate", "naviTemplate", "pageTemplate");
		$variables["domainTemplate"] = array("TITLE","DESCRIPTION","KEYWORDS","NAVIGATION","HEADER","PAGE");
		$variables["contentTemplate"] = array("TEXT","IMAGE","DOWNLOADS","CONTENTID","HANDLER");
		$variables["naviTemplate"] = array("LINK","URL","TEXT");
		$variables["pageTemplate"] = array("HEADER","CONTENT","DOMAIN");
		$variables["dlTemplate"] = array("TEXT","DOWNLOADS");
		
		#$this->setParser("html","Util::base64Parser");
		if($this->A == null AND $id != -1) $this->loadMe();
		if($id == -1) $this->A = $this->newAttributes();
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Template");

		$TG = new TemplatesGUI();
		$options = $TG->getAvailableCategories();
		
		$gui->setType("templateType","select");
		
		$gui->setOptions("templateType",array_keys($options),array_values($options));
		$gui->setInputJSEvent("templateType", "onchange","CMSTemplate.updateVariables(this);");
		$gui->setInputJSEvent("templateType", "onkeyup","CMSTemplate.updateVariables(this);");
		$gui->setLabel("templateType", "Typ");
		
		$gui->setType("html","TextEditor64");

		$gui->setType("TemplateDomainID","hidden");
		#$gui->setLabel("TemplateDomainID", "Domain");
		#$gui->setOptions("TemplateDomainID",array("0",$U),array("alle","nur ".$Domain->getA()->title));
		
		
		$gui->hideAttribute("TemplateID");
		$gui->setType("aktiv","hidden");
		
		$gui->setStandardSaveButton($this, "Templates");
		
		$vars = "";
		foreach($variables AS $k => $v)
			if(is_array($variables[$k]))
				$vars .= "<p id=\"{$k}Variables\" style=\"".($this->A->templateType == $k ? "" : "display:none;")."\">%%%".implode("%%%<br />%%%", $variables[$k])."%%%</p>";
		
		$html = "
			<script type=\"text/javascript\">
				new Draggable('TBVarsContainer',{handle:'TBVarsHandler', zindex: 2000});
				oldVarSelected = '".($this->A->templateType != null ? $this->A->templateType : "null")."';
			</script>
			<div 
				style=\"position:absolute;z-index:2000;left:450px;width:200px;border-width:1px;border-style:solid;".(isset($variables[$this->A->templateType]) ? "" : "display:none;")."\"
				class=\"backgroundColor0 borderColor1\"
				id=\"TBVarsContainer\"
			>
			<div class=\"cMHeader backgroundColor1\" id=\"TBVarsHandler\">Variablen:</div>
			<div>
				<p><small>Sie können folgende Variablen in Ihrem HTML verwenden (bitte beachen Sie Groß- und Kleinschreibung):</small></p>
				$vars
			</div>
			</div>";

		
		return $html.$gui->getEditHTML();
	}
}
?>