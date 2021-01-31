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
class SeitenGUI extends anyC implements iGUIHTML2 {
	function __construct(){
		$this->setCollectionOf("Seite");
		$this->customize();
	}
	
	function getHTML($id, $global = false){
		$gui = new HTMLGUI();
		$gui->VersionCheck("Seiten");
		
		$U = new mUserdata();
		$U = $U->getUDValue("selectedDomain");
		
		if($U == null) {
			$t = new HTMLTable(1);
			$t->addRow("Sie haben keine Domain ausgewählt.<br /><br />Bitte wählen Sie eine Domain im Domain-Plugin, indem Sie auf das graue Kästchen in der Liste auf der rechten Seite klicken.");
			return $t->getHTML();
		}
		$this->addOrderV3("TemplateID");
		$this->addOrderV3("SeiteID");
		if($U != null) 
			$this->addAssocV3("DomainID","=",$U);
		if($global)
			$this->setAssocV3 ("DomainID", "=", 0);
		#$this->addAssocV3("DomainID","=","0", "OR");
		if($this->A == null) $this->lCV3($id);
		
		$gui->setName("Seite");
		$gui->setObject($this);
		
		$gui->setShowAttributes(array("SeiteID","name"));
		
		$gui->setJSEvent("onNew","function() { contentManager.reloadFrameRight(); }");
		$gui->addColStyle("SeiteID","width:40px;text-align:right;");
		$gui->setParser("name","SeitenGUI::nameParser",array("\$aid","\$header"));
		
		$gui->setDisplayGroup("TemplateID");
		$gui->setDisplayGroupParser("SeitenGUI::DGParser");
		$gui->setCollectionOf($this->collectionOf);

		$gui->customize($this->customizer);

		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){
			print_r($e);
			
		}
	}
	
	public static function nameParser($w, $l, $s){
		$s = HTMLGUI::getArrayFromParametersString($s);
		#return "<img title=\"Content anzeigen\" onclick=\"loadFrameV2('contentRight','mContent', 'mContentGUI;type:$s[0]');\" src=\"./images/i2/export.png\" class=\"mouseoverFade\" style=\"float:left;margin-right:3px;\" />".
		return ($w != "" ? $w : $s[1]);
	}
	
	public static function DGParser($w){
		$T = new Template($w);
		$T->loadMe();
		if($T != null AND $T->getA() != null) return $T->getA()->name;
		else return "-";
	}
}
?>
