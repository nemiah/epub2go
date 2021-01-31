<?php
/**
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
class mWebsiteGUI extends anyC implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUI();
		$gui->VersionCheck("mWebsite");
		
		$Dom = new anyC();
		$Dom->setCollectionOf("Domain");
		
		$U = new mUserdata();
		$U = $U->getUDValue("selectedDomain");
		
		if($U == null) {
			$html = "
			<ul>";
			
			while($d = $Dom->getNextEntry()){
				$html .= "
				<li>".$d->A("title")."</li>";
			}
			
			$html .= "
			</ul>";
			
			return $html;
		}
		
		$D = new Domain($U);
		
		$html  = "
		<script type=\"text/javascript\">Website.init();</script>";
		$html .= "
			<div style=\"border-left-width:1px;border-left-style:solid;padding-left:10px;\" class=\"borderColor1\">
			<p>
				<b>".$D->A("title")."</b>
			</p>
			<ul style=\"list-style-image:none;list-style-type:none;\" >
				<li><img style=\"float:left;margin-right:10px;\" src=\"./images/i2/new.gif\" onclick=\"contentManager.newClassButton('Navigation','');\" class=\"mouseoverFade\"/> Neues Menüelement erstellen</li>
			</ul>".$this->getNav(0, $U)."</div>";
		$html .= "
		<script type=\"text/javascript\">Website.start();</script>";
		
		return $html;
	}
	
	private function getNav($parentID, $domain){
		$mNav = new anyC();
		$mNav->setCollectionOf("Navigation");
		$mNav->addAssocV3("parentID","=",$parentID);
		$mNav->addAssocV3("DomainID","=",$domain);
		$mNav->addOrderV3("sort");
		
		$mNav->lCV3();
		
		if($mNav->numLoaded() == 0) return;
		
		$html = "
		<ul style=\"list-style-image:none;list-style-type:none;\" id=\"sortable_$parentID\">";
		while($n = $mNav->getNextEntry()) {
			$B = new Button("Element bearbeiten","./images/i2/edit.png");
			$B->type("icon");
			$B->onclick("contentManager.loadFrame('contentLeft','Navigation','".$n->getID()."');");
			$B->style("float:left;margin-right:10px;");
			
			$D = new Button("Element löschen","./images/i2/delete.gif");
			$D->type("icon");
			$D->onclick("deleteClass('Navigation','".$n->getID()."', function() { contentManager.reloadFrameRight(); if(typeof lastLoadedLeft != 'undefined' &amp;&amp; lastLoadedLeft == '1') $('contentLeft').update(''); },'Element und alle Unterelemente wirklich löschen?');");
			$D->style("float:right;margin-right:10px;");
			
			$html .= "<li id=\"NavigationElementID_".$n->getID()."\" style=\"".($n->A("hidden") == "1" ? "text-decoration:line-through;" : "")."\">$D<img src=\"./images/i2/topdown.png\" class=\"navigationHandler_sortable_$parentID\"\" style=\"cursor:pointer;float:right;margin-right:10px;\" />$B".($n->A("name") == "" ? "&lt;kein Name&gt;" : $n->A("name")).$this->getNav($n->getID(),$domain)."</li>";
		}	#
		$html .= "
		</ul>
		<script type=\"text/javascript\">Website.add('sortable_$parentID');</script>";
		return $html;
	}
	
	public function SaveNavOrder($values){
		foreach(explode(";-;;newline;;-;", $values) AS $k => $v){
			$ex2 = explode(";",$v);

			foreach($ex2 AS $k2 => $v2){
				$ex3 = str_replace("NavigationElementID", "", $v2);#explode("[]=", $v2);
				
				$N = new Navigation($ex3);
				$N->loadMe();
				$N->changeA("sort", $k2);
				$N->saveMe();
			}
		}
	}
}
?>