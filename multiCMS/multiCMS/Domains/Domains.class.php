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
class Domains extends anyC {
	function __construct() {
		$this->setCollectionOf("Domain");
		
		$this->customize();
	}
	
	function getMyDomain(){
		#try {
		$this->addAssocV3("url","LIKE ","%\n".$_SERVER["HTTP_HOST"]."%");
		$this->addAssocV3("url","LIKE ","".$_SERVER["HTTP_HOST"]."%", "OR");
		$this->lCV3();
		
		
		if($this->numLoaded() == 0){
			$this->setAssocV3("url","= ","*");
			$this->lCV3();
		}
	
		if($this->numLoaded() == 0)
			die("Domain $_SERVER[HTTP_HOST] not found!");
		
		return $this->getNextEntry()->getGUIClass();
		#} catch(NoDBUserDataException $e){
		#	die("No Userdata found! <a href=\"./multiCMS\">Please install multiCMS</a>");
		#}
	}
}
?>
