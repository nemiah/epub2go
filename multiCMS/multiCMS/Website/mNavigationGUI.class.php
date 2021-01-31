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
class mNavigationGUI extends mNavigation implements iGUIHTML2 {
	private $domainURL = "";
	private $horizontalNav = "";
	private $isSubNavigation = false;
	private $isSubNavigationRoot = null;
	
	function getHTML($id){
		$gui = new HTMLGUI();
		$gui->VersionCheck("mNavigation");
		
		$U = new mUserdata();
		$U = $U->getUDValue("selectedDomain");
		
		if($U == null) {
			$t = new HTMLTable(1);
			$t->addRow("Sie haben keine Domain ausgewählt.<br /><br />Bitte wählen Sie eine Domain im Domain-Plugin, indem Sie auf das graue Kästchen in der Liste auf der rechten Seite klicken.");
			return $t->getHTML();
		}
		
		$this->addOrderV3("DomainID","ASC");
		$this->addOrderV3("sort","ASC");
		if($U != null) $this->addAssocV3("DomainID","=","$U");
		if($this->A == null) $this->lCV3($id);
		
		
		$gui->setName(get_parent_class($this));
		if($this->collector != null) $gui->setAttributes($this->collector);
		
		$gui->setDisplayGroup("DomainID");
		$gui->setDisplayGroupParser("mNavigationGUI::DGParser");
		
		$gui->setShowAttributes(array("name","sort"));
		$gui->setCollectionOf($this->collectionOf);
		
		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}
	
	public static function DGParser($w){
		if($w == 0) return "-";
		$D = new Domain($w);
		return $D->getA()->url;
	}
	
	public function subNavigation($use, $root){
		$this->isSubNavigation = $use;
		$this->isSubNavigationRoot = $root;
	}
	
	private function getList($pid, $SeiteID, $DomainID){
		$html = "";
		$Dom = new Domain($DomainID);

		$multiLang = false;
		try{
			$test = new MultiLanguage(-1);
			$userLang = CCMultiLanguage::getUserLanguage();

			if($userLang != null AND $userLang->getID() != null AND $userLang->getID() != $Dom->A("DomainDefaultSpracheID"))
				$multiLang = true;
			
		} catch(ClassNotFoundException $e){ }

		while(($C = $this->getNextEntry())){
			if($C->A("loginType") == "1" AND (!isset($_SESSION["C"]) OR empty($_SESSION["C"])))
				continue;
			
			if($C->A("loginType") == "2" AND (isset($_SESSION["C"]) OR !empty($_SESSION["C"])))
				continue;
			
			if($multiLang){
				$mL = MultiLanguage::getTranslation($userLang->getID(), "Navigation", $C->getID(), "name");
				if($mL == null) continue;
				$C->changeA("name", $mL);
			}
			$CA = $C->getA();
			
			if($this->domainURL == "") {
				$D = new Domain($C->A("DomainID"));
				$this->domainURL = $D->getA()->url;
				if($this->domainURL == "") $this->domainURL = "none";
			}
		
			$sub = new mNavigationGUI();
			$sub->addOrderV3("sort","ASC");
			$sub->addAssocV3("parentID","=", $C->getID());
			$sub->addAssocV3("t1.DomainID","=", $DomainID);
			$sub->addAssocV3("hidden","=", "0");
			$sub->addJoinV3("Template","activeTemplateID","=","TemplateID");
			$sub->addJoinV3(" Template","inactiveTemplateID","=","TemplateID");
			$sub->addJoinV3("Seite","SeiteID","=","SeiteID");
			$sub->setFieldsV3(array("t2.html as activeHTML","t3.html as inactiveHTML","t1.name","t1.DomainID","t1.SeiteID","linkURL","linkType","displaySub","t4.permalink","httpsLink", "loginType"));

			$sub->lCV3();
			
			$sub->subNavigation($this->isSubNavigation, $this->isSubNavigationRoot);
			
			$subOpened = new mNavigation();
			$subOpened->addAssocV3("parentID","=",$C->getID());
			$subOpened->addAssocV3("SeiteID","=",$SeiteID);
			$subOpened->lCV3();
				
			
			
			if($CA->linkType == "separator")
				$html .= 
				"<div class=\"separator\">".$CA->name."</div>";
			
			/*.= "
				<li class=\"hld\" style=\"cursor:pointer;\" onclick=\"dd('naviul_".$C->getID()."');\"><img id=\"naviul_".$C->getID()."Img\" class=\"sslico\" src=\"./$this->domainURL/images/dd.gif\" title=\"Menü ausklappen\" />".$CA->name."</li>";
			*/
			
			$rootDomain = "http".($CA->httpsLink == "1" ? "s" : "")."://$_SERVER[HTTP_HOST]";
			if($this->isSubNavigationRoot != null)
				$rootDomain = $this->isSubNavigationRoot;
			
			if($CA->linkType == "cmsPage") {
				if(!isset($CA->permalink) OR $CA->permalink == "")
					$link = "$rootDomain/index.php?p=".$CA->SeiteID."".(isset($_GET["d"]) ? "&d=$_GET[d]" : "")."";
				else
					$link = $rootDomain."/".($this->isSubNavigation ? "" : $Dom->A("permalinkPrefix")).$CA->permalink;
				
				if($C->A("SeiteID") == $D->A("startseite"))
					$link = "/";
				
				$html .= 
					(($CA->SeiteID == $SeiteID OR $subOpened->numLoaded() > 0) ? 
						str_replace("%%%LINK%%%","<a href=\"$link\">".$CA->name."</a>",$CA->activeHTML) : 
						str_replace("%%%LINK%%%","<a href=\"$link\">".$CA->name."</a>", $CA->inactiveHTML));			
				
				#if(!isset($CA->permalink) OR $CA->permalink == "")
					$html = str_replace("%%%URL%%%", "?p=".$CA->SeiteID."".(isset($_GET["d"]) ? "&d=$_GET[d]" : "")."", $html);
				#else
				#	$html = str_replace("%%%URL%%%", "page-".$CA->permalink, $html);
					
				$html = str_replace("%%%TEXT%%%", $CA->name, $html);
			}
			
			if($CA->linkType == "url") {
				$html .= str_replace("%%%LINK%%%","<a href=\"".$CA->linkURL."\">".$CA->name."</a>",$CA->inactiveHTML);
				#"<li class=\"hl\"><a href=\"".$CA->linkURL."\">".$CA->name."</a></li>";
				
				$html = str_replace("%%%URL%%%", $CA->linkURL, $html);
				$html = str_replace("%%%TEXT%%%", $CA->name, $html);
			}
			
			if($CA->linkType == "HTML") {
				$html .= str_replace("%%%LINK%%%","<a href=\"".$CA->linkURL."\">".$CA->name."</a>",$CA->activeHTML);
				#"<li class=\"hl\"><a href=\"".$CA->linkURL."\">".$CA->name."</a></li>";
				
				$html = str_replace("%%%URL%%%", $CA->linkURL, $html);
				$html = str_replace("%%%TEXT%%%", $CA->name, $html);
			}
			
			if($sub->numLoaded() > 0){

				if($C->A("displaySub") == "1" OR $subOpened->numLoaded() > 0 OR $C->A("SeiteID") == $SeiteID)
					if($Dom->A("horizontalNav") == "0")
						$html .= "<div class=\"subcategory\">".$sub->getCMSHTML($C->getID(), $SeiteID, $DomainID)."</div>";
					else
						$this->horizontalNav .= "<div class=\"subcategory\">".$sub->getCMSHTML($C->getID(), $SeiteID, $DomainID)."</div>";
			}
		}
		
		return $html;
	}
	
	function getCMSHTML($pid = 0, $SeiteID = 0, $DomainID = 0){

		if($pid == 0) {
			$this->addOrderV3("sort","ASC");
			$this->addAssocV3("parentID","=", $pid);
			$this->addAssocV3("t1.DomainID","=", $DomainID);
			$this->addAssocV3("hidden","=", "0");
			$this->addJoinV3("Template","activeTemplateID","=","TemplateID");
			$this->addJoinV3(" Template","inactiveTemplateID","=","TemplateID");
			$this->addJoinV3("Seite","SeiteID","=","SeiteID");
			$this->setFieldsV3(array("t2.html as activeHTML","t3.html as inactiveHTML","t1.name","t1.DomainID","t1.SeiteID","linkURL","linkType","displaySub","t4.permalink","httpsLink", "loginType"));
			$this->lCV3();
		}
		
		#$hide = ($pid != 0 AND (!isset($_COOKIE["naviul_$pid"]) OR $_COOKIE["naviul_$pid"] == "false"));
		
		$html = $this->getList($pid, $SeiteID, $DomainID);
		
		return $html.$this->horizontalNav;
	}
}
?>