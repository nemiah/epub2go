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
class mContentGUI extends anyC implements iGUIHTML2 {
	function __construct(){
		$this->setCollectionOf("Content");

		if(Session::isPluginLoaded("mSprache"))
			$this->addJoinV3("Sprache", "ContentSpracheID", "="," SpracheID");

		$this->customize();
	}
	
	function getHTML($id){
		$this->addOrderV3("sort","ASC");
		
		if($this->A == null) $this->lCV3($id);
		
		$gui = new HTMLGUIX($this);
		$gui->name("Content");

		$gui->options(true, true, false, false);

		$a = array("sort", "text");
		if(Session::isPluginLoaded("mSprache")){
			$a[] = "SpracheIdentifier";
			$gui->colWidth("SpracheIdentifier", "50");

		}

		$gui->attributes($a);
		$gui->parser("text","mContentGUI::textParser");
		$gui->colWidth("sort", "30");
		$gui->colStyle("sort", "text-align:right;");

		$gui->addToEvent("onDelete", "contentManager.reloadFrame('contentLeft');");
		$gui->customize($this->customizer);

			
		return $gui->getBrowserHTML($id);
	}
	
	public static function textParser($w, $E){
		$B = new Button("in HTML-Editor bearbeiten","./multiCMS/Content/html.png");
		$B->type("icon");
		$B->style("float:left;margin-right:5px;");
		$B->windowRme("Wysiwyg", "", "getEditor", "", "WysiwygGUI;FieldClass:Content;FieldClassID:".$E->getID().";FieldName:text");

		return $B.substr(strip_tags($w, "<a><i><b><strong><img>"), 0, 150);
	}
}
?>
