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
class DomainsGUI extends Domains implements iGUIHTML2 {
	function getHTML($id){

		if($this->A == null) $this->lCV3($id);
		
		$gui = new HTMLGUI();
		$gui->VersionCheck("Domains");
		$gui->setObject($this);
		$gui->setName("Domains");
		#if($this->collector != null) $gui->setAttributes($this->collector);
		
		$gui->setShowAttributes(array("url"));
		
		$gui->setCollectionOf($this->collectionOf);
		
		$gui->setParser("url","DomainsGUI::urlParser",array("\$aid"));
		
		$gui->customize($this->customizer);
		
		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}
	
	public static function urlParser($w, $l, $p){
		
		$U = new mUserdata();
		$U = $U->getUDValue("selectedDomain");
		$i = "<img onclick=\"rme('mUserdata','','setUserdata',Array('selectedDomain','$p'),'contentManager.reloadFrameRight();');\" title=\"Diese Domain bearbeiten\" src=\"./images/i2/notok.gif\" style=\"float:left;margin-right:3px;\" />";
		if($U != null AND $p == $U) $i = "<img src=\"./images/i2/ok.gif\" style=\"float:left;margin-right:3px;\" title=\"Diese Domain wird gerade bearbeitet\" />";
		return "$i$w";
	}
}
?>
