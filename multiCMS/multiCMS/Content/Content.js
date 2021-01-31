/*
 *
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
 var Content = {
	selectType: function(select){
		if(select.value == "preset") {
			$('presetTemplateIDEditL').parentNode.style.display = "";
			$('formHandlerIDEditL').parentNode.style.display = "";
		} else {
			$('presetTemplateIDEditL').parentNode.style.display = "none";
			$('formHandlerIDEditL').parentNode.style.display = "none";
		}
		
		if(select.value == "php") {
			//$('textEditL').parentNode.style.display = "none";
			$('customContentEditL').parentNode.style.display = "";
		} else {
			//$('textEditL').parentNode.style.display = "";
			$('customContentEditL').parentNode.style.display = "none";
		}
	},

	selectImage: function(contentID, imagePath){
		var exp = imagePath.split(".");

		var end = exp[exp.length-1].toLowerCase();

		if(end != "jpg" && end != "jpeg" && end != "gif" && end != "png"){
			alert("Bitte wählen Sie eine jpg, gif oder png-Datei!");

			return;
		}

		$('ContentForm').ContentImage.value = imagePath.split("/specifics/")[1];
		contentManager.restoreFrame('contentRight','selectionOverlay');
	}
}