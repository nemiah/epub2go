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
var Website = {
	elems: null,
	
	init:function(){
		Website.elems = Array();
	},
	
	add: function(sort){
		Website.elems[Website.elems.length] = sort;
	},
	
	start: function(){
		for(var i = 0; i < Website.elems.length; i++)
			$j('#'+Website.elems[i]).sortable({
				axis: "y", 
				update: function() {
					contentManager.rmePCR("mWebsite","","SaveNavOrder", Website.serialize()," ");
				},
				dropOnEmpty: true,
				handle: $j('.navigationHandler_'+Website.elems[i])
			});
		
	},
	
	serialize: function(){
		cerial = "";
		for(var i = 0; i < Website.elems.length; i++)
			cerial += (cerial != "" ? ";-;;newline;;-;" : "")+Sortable.serialize(Website.elems[i]);
		
		cerial = cerial.replace(/&/g,";-;;und;;-;");
		
		return cerial;
	}/*,

	reset: function(){
		$('linkURL').parentNode.style.display = 'none';
		$('SeiteID').parentNode.style.display = 'none';
		$('inactiveTemplateID').parentNode.style.display = 'none';
		$('activeTemplateID').parentNode.style.display = 'none';
	},
	
	set: function(select){
		Website.reset();
		
		if(select.value=='cmsPage') {
			$('SeiteID').parentNode.style.display = '';
			$('inactiveTemplateID').parentNode.style.display = '';
			$('activeTemplateID').parentNode.style.display = '';
		} else if(select.value=='url') {
			$('linkURL').parentNode.style.display = '';
			$('inactiveTemplateID').parentNode.style.display = '';
		} else if(select.value=='HTML') {
			$('activeTemplateID').parentNode.style.display = '';
		
		} else if(select.value=='separator') {
		
		}
	}*/
}
