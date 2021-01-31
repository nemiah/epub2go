<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class Patch extends PersistentObject {
	function alterTables(){
		$this->loadAdapter();
		$CI = new CI(-1);
		$CIA = $CI->newAttributes();
		
		switch($this->A("PatchType")){
			case "mysql":
				$v = explode("\n",$this->A("PatchValue"));
				foreach($v AS $key => $value){
					$CIA->MySQL = $value;
					$this->Adapter->alterTable($CIA);
				}
			break;
		}
		
	}

	function execute($output = "true"){
		switch($this->A("PatchType")){
			case "mysql":
				$_SESSION["messages"]->addMessage("It's a MySQL-Patch!");
				$this->alterTables();
			break;
			#case "php":
			#	eval($this->A("PatchValue"));
			#sbreak;
		}

		$this->changeA("patchExecuted", "1");#->patchExecuted = 1;
		$this->saveMe(true, $output == "true");
	}
}
?>
