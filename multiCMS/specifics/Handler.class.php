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
class Handler extends PersistentObject implements iNewWithValues, iDeletable  {
	public static function handleForm($valuesAssocArray){
		if(!isset($valuesAssocArray["HandlerName"])){
			if(!isset($valuesAssocArray["HandlerID"])) die("error:multiCMSMessages.E001");
			$H = new Handler($valuesAssocArray["HandlerID"]);
			$H->loadMe();
			if($H->getA() == null) die("error:multiCMSMessages.E002");
			$c = $H->getA()->HandlerFileTemplate;
		}
		else {
			$c = $valuesAssocArray["HandlerName"];
			$H = new Handler(-1);
		}
		
		$c = new $c();
		$c->handleForm($valuesAssocArray, $H);
	}
}
?>
