<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */

class mEPubGUI extends anyC implements iGUIHTML2 {
	public function getHTML($id){
		$FB = new FileBrowser();
		$FB->addDir(dirname(__FILE__));
		$list = $FB->getAsLabeledArray("iEPubParser", ".class.php", true);

		$T = new HTMLTable(2, "ePub-Parser");
		$T->setColWidth(1, 20);
		
		foreach($list AS $n => $v){
			$B = new Button("", "./ubiquitous/EPub/EPub18x18.png");
			$B->type("icon");
			$B->loadFrame("contentLeft", $v);

			$T->addRow(array($B, $n));
		}

		return $T;
	}
}
?>
