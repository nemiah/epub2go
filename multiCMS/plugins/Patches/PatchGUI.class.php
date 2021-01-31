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
class PatchGUI extends Patch implements iGUIHTML2 {
	function __construct($ID){
		parent::__construct($ID);
		$this->setParser("PatchDate","Datum::parseGerDate");
	}
	
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		if($id == -1) {
			$mP = new mPatchGUI();
			$mP->addAssocV3("PatchNummer","<", "1000000");
			$this->A->PatchNummer = $mP->getIncrementedField("PatchNummer");
			$this->A->PatchDate = date("d.m.Y");
		}
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Update");

		$gui->setType("PatchExecuted","checkbox");
		$gui->setType("PatchDescription","TextEditor");
		$gui->setType("PatchValue","TextEditor");
		$gui->setLabel("PatchValue","Befehl");
		$gui->setLabel("PatchApplication","Anwendung");
		$gui->setLabel("PatchExecuted","Ausgeführt?");
		$gui->setLabel("PatchDate","Datum");
		$gui->setLabel("PatchDescription","Beschreibung");
		$gui->setLabel("PatchNummer","Nummer");
		$gui->setLabel("PatchType","Typ");
		$gui->setType("PatchType","select");
		#$gui->setType("PatchNummer","hidden");
		$gui->setType("PatchApplication","select");
		
		$apps = $_SESSION["applications"]->getApplicationsList();
		$apps["phpappfw"] = "phpAppFW";
		
		$gui->setOptions("PatchType",array("mysql","php"),array("MySQL","PHP"));
		$gui->setOptions("PatchApplication",array_keys($apps),array_values($apps));
		
		$gui->setStandardSaveButton($this);
		if($id != -1) $html = "
		<table>
			<colgroup>
				<col class=\"backgroundColor3\" />
			</colgroup>
			<tr>
				<td>Bitte beachten Sie, dass manche Updates mit einer neueren Version nicht mehr notwendig sind.</td>
			</tr>
			<tr class=\"backgroundColor0\">
				<td></td>
			</tr>
			<tr>
				<td>".nl2br($this->A->PatchDescription)."</td>
			</tr>
			<tr class=\"backgroundColor0\">
				<td></td>
			</tr>
			<tr>
				<td>
					<input
						type=\"button\"
						class=\"bigButton backgroundColor2\"
						value=\"Update\nausführen\"
						style=\"float:right;background-image:url(./images/navi/update.png);\"
						onclick=\"rmeP('Patch','$this->ID','execute','','if(checkResponse(transport)) contentManager.reloadFrameLeft();');\"
					/>
					".($this->A->PatchExecuted == "1" ? "<span style=\"color:green;font-weight:bold;\">Dieses Update wurde bereits ausgeführt.</span>" : "Dieses Update wurde noch nicht ausgeführt")."
				</td>
			</tr>
			<tr>
				<td>Folgende Befehle werden ausgeführt:<br /><br />".nl2br($this->A->PatchValue)."</td>
			</tr>
			<tr class=\"backgroundColor0\">
				<td></td>
			</tr>
			<tr>
				<td>
					<input	
						type=\"button\"
						value=\"Update bearbeiten\"
						onclick=\"if($('editUpdate').style.display == 'none') new Effect.BlindDown('editUpdate'); else new Effect.BlindUp('editUpdate');\"
					/>
				</td>
			</tr>
		</table>
		<div id=\"editUpdate\" style=\"display:none;\">";
		else $html = "<div>";
		return $html.$gui->getEditHTML()."</div>";
	}
}
?>