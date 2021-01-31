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
class Content extends PersistentObject {
	function __construct($ID) {
		parent::__construct($ID);
		$this->setParser("text","Util::base64Parser");
		$this->customize();
	}
	function getA(){
		if($this->A == null) $this->loadMe();
		return $this->A;
	}

	function  saveMe($checkUserData = true, $output = false) {
		if($this->A("ContentImage") != ""){
			$path = Util::getRootPath()."specifics/".$this->A("ContentImage");
			$image = new Imagick($path);

			$imageSmall = Util::getRootPath()."specifics/".str_replace(basename($this->A("ContentImage")), "small/".basename($this->A("ContentImage")), $this->A("ContentImage"));
			$imageBig = Util::getRootPath()."specifics/".str_replace(basename($this->A("ContentImage")), "big/".basename($this->A("ContentImage")), $this->A("ContentImage"));

			$image->resizeImage(0, 550, Imagick::FILTER_LANCZOS, 1);
			if(!$image->writeImage($imageBig))
				Red::alertD("Das große Vorschaubild kann nicht erstellt werden!");


			$image->resizeImage(150, 0, Imagick::FILTER_LANCZOS, 1);

			if(!$image->writeImage($imageSmall))
				Red::alertD("Das kleine Vorschaubild kann nicht erstellt werden!");

			$image->destroy();
		}

		parent::saveMe($checkUserData, $output);
	}

}
?>
