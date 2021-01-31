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
class mPatchGUI extends anyC implements iGUIHTML2 {
	function getHTML($id){
		$this->addOrderV3("PatchID");
		$this->lCV3($id);
		
		$gui = new HTMLGUI();
		$gui->setName("Updates");
		$gui->setAttributes($this->collector);
		$gui->setCollectionOf($this->collectionOf, "Update");
		
		$gui->setShowAttributes(array("PatchDescription","PatchExecuted"));
		
		$gui->setParser("PatchExecuted","Util::catchParser");
		$gui->setParser("PatchDescription","mPatchGUI::descriptionParser");
		
		#$gui->setIsDisplayMode(true);
		#$gui->setEditInDisplayMode(true,"contentLeft");
		#$gui->setDeleteInDisplayMode(false);

		$BXML = new Button("XML\nerzeugen", "empty");
		$BXML->style("float:right;");
		$BXML->windowRme("mPatch", "-1", "getXML", "");
		if(strpos($_SERVER["SCRIPT_FILENAME"], "/nemiah") === false)
			$BXML = "";
		
		$BU = new Button("Updates\naktualisieren", "refresh");
		$BU->rmePCR("mPatch", "-1", "update", "", "function(transport){ if(transport.responseText != 'error') ".OnEvent::reload("Right")." else new Effect.BlindDown('errorMessage'); }");
		
		
		$T = new HTMLTable(1, "Einzelne Aktualisierungen");
		$T->addRow(array($BU.$BXML."
					<div id=\"errorMessage\" style=\"display:none;color:red;\">Es konnte keine Verbindung zum Updates-Server hergestellt werden.<br />Bitte stellen Sie folgende Voraussetzungen sicher:
					<ul>
						<li>Der Server mit Ihrer Installation kann eine Verbindung ins Internet aufbauen.</li>
						<li>Sie benutzen die aktuellste Version des Update-Plugins.</li>
					</ul></div>"));
		$html = OnEvent::script("var Patch = { popup: { 'width':600, 'hPosition': 'center', hasX: false } }").$T;
		/*"
		<table>
			<colgroup>
				<col class=\"backgroundColor3\" />
			</colgroup>
			<tr>
				<td>
					".(strpos($_SERVER["SCRIPT_FILENAME"], "/nemiah") !== false ? "<input
						style=\"float:right;\"
						onclick=\"windowWithRme('mPatch','','getXML','');\"
						type=\"button\"
						class=\"bigButton backgroundColor2\"
						value=\"XML\nerzeugen\"
					/>" : "")."
					<input
						style=\"background-image:url(./images/navi/refresh.png);\"
						onclick=\"rme('mPatch','','update','','if(transport.responseText != \'error\') contentManager.reloadFrameRight(); else new Effect.BlindDown(\'errorMessage\');');\"
						type=\"button\"
						class=\"bigButton backgroundColor2\"
						value=\"Updates\naktualisieren\"
					/>
				</td>
			</tr>
		</table>";*/
		#else
		#	$html = "";
		
		$F = new HTMLForm("updateForm", array("file"), "Die Anwendung aktualisieren");
		$F->getTable()->setColWidth(1, 120);
		
		$F->setType("file", "file");
		$F->setLabel("file", "Paket");
		
		$F->addJSEvent("file", "onChange", "Overlay.showDark(0.1, 0.8); ".OnEvent::popup("Anwendungsaktualisierung", "mPatch", "-1", "processUpdate", array("fileName"),"", "Patch.popup"));

		$F->setDescriptionField("file", "Hier aktualisieren Sie Ihre komplette Anwendung mit einem neuen Paket, das Sie von Furtmeier Hard- und Software erhalten haben.<br /><br />Sie können damit sowohl neue Versionen (Update) einspielen als auch eine mit Plugins erweiterte Version (Upgrade) einrichten.<br /><br />Alle Dateien werden vorher gesichert und alle eingetragenen Daten bleiben erhalten.");
		
		try {
			return ($id == -1 ? $F."<div style=\"height:30px;\"></div>".$html : "").$gui->getBrowserHTML($id);
		} catch (Exception $e){ }
		
		return $html;
	}
	
	public function processUpdate($fileName){
		$T = new HTMLTable(1); //preload
		$B = new Button("nix"); //preload
		$E = new OnEvent(); //preload
		$T = new T(); //preload
		
		set_time_limit(0);
		$messages = array();
		
		$unbeschreibbar = $this->checkWritable(Util::getRootPath());
		
		if(count($unbeschreibbar) > 0)
			$this->throwError("Dateisystem unbeschreibbar", "Die folgenden Dateien und Verzeichnisse sind für den Webserver nicht beschreibbar. Für dieses automatische Update müssen alle Dateien und Verzeichnisse innerhalb von <code>".Util::getRootPath()."</code> beschreibbar sein. Der Vorgang wird nicht fortgesetzt.", "<pre style=\"max-height:300px;overflow:auto;font-size:11px;\">".implode("\n", $unbeschreibbar)."</pre>");
		
		$suffix = explode(".", $fileName);
		$suffix = end($suffix);
		
		if($suffix != "zip")
			$this->throwError("Unerwartete Datei", "Das Dateiformat '$suffix' entspricht nicht dem erwarteten Dateiformat 'zip'. Der Vorgang wird nicht fortgesetzt.");
		
		
		$rootPath = Util::getRootPath();
		$fileName = Util::getTempDir().$fileName.".tmp";
		
		
		if(!extension_loaded("zip"))
			$this->throwError("Erweiterung nicht verfügbar", "Die zip-Erweiterung für PHP steht auf Ihrem System nicht zur Verfügung. Der Vorgang wird nicht fortgesetzt.");
		
		$za = new ZipArchive();

		if(!$za->open($fileName))
			$this->throwError("Fehler beim Öffnen", "Das hochgeladene Archiv kann nicht geöffnet werden. Der Vorgang wird nicht fortgesetzt.");
		
		
		$basicBackupDir = Util::getRootPath()."phynxBackup_".date("Ymd");
		$i = 1;
		while(file_exists($basicBackupDir.".$i/"))
			$i++;
		
		$backupDir = $basicBackupDir.".$i/";
		
		$messages[] = "Erstelle Backup-Verzeichnis ".basename($backupDir);
		if(!mkdir($backupDir))
			$this->throwError("Fehler beim Erstellen eines Verzeichnisses", "Das Verzeichnis $backupDir kann nicht erstellt werden. Der Vorgang wird nicht fortgesetzt.");
		
		
		$updateBasicDir = Util::getRootPath()."phynxUpdate_".date("Ymd");
		$i = 1;
		while(file_exists($updateBasicDir.".$i/"))
			$i++;
		
		$updateDir = $updateBasicDir.".$i/";
		
		$messages[] = "Erstelle Update-Verzeichnis ".basename($updateDir);
		if(!mkdir($updateDir) AND !file_exists($updateDir))
			$this->throwError("Fehler beim Erstellen eines Verzeichnisses", "Das Verzeichnis $updateDir kann nicht erstellt werden. Der Vorgang wird nicht fortgesetzt.");
		
		$messages[] = "Entpacke Archiv in Update-Verzeichnis";
		if(!$za->extractTo($updateDir))
			$this->throwError("Fehler beim Entpacken", "Das hochgeladene Archiv kann nicht in das neue Verzeichnis $updateDir entpackt werden. Der Vorgang wird nicht fortgesetzt.");
		
		
		$messages[] = "Verschiebe Daten in Backup-Verzeichnis\n";
		$this->copyDir(Util::getRootPath(), $backupDir, true);
		
		
		$messages[] = "Kopiere Datenbank-Zugangsdaten";
		copy($backupDir."system/DBData/Installation.pfdb.php", $updateDir."system/DBData/Installation.pfdb.php");
		
		
		$messages[] = "Kopiere specifics-Verzeichnis\n";
		$this->copyDir($backupDir."specifics/", $updateDir."specifics/", false, true);
		
		$messages[] = "Verschiebe neue Version von Update-Verzeichnis in Stammverzeichnis";
		$this->copyDir($updateDir, $rootPath, true);
		#print_r($za);
		#var_dump($za);
		#echo "numFiles: " . $za->numFiles . "\n";
		#echo "status: " . $za->status  . "\n";
		#echo "statusSys: " . $za->statusSys . "\n";
		#echo "filename: " . $za->filename . "\n";
		#echo "comment: " . $za->comment . "\n";

		/*for ($i=0; $i<$za->numFiles;$i++) {
			echo "index: $i\n";
			print_r($za->statIndex($i));
		}*/
		#echo "numFile:" . $za->numFiles . "\n";
		
		$messages[] = "Entferne Update-Verzeichnis";
		#$this->emptyDir($updateDir);
		rmdir($updateDir);
		
		echo "<pre style=\"max-height:450px;font-size:11px;overflow:auto;padding:5px;line-height:1.3;white-space: pre-wrap;margin-bottom:20px;\">";
		echo implode("\n", $messages);
		echo "</pre>";
		
		$link = str_replace("/interface/rme.php", "/".basename($backupDir), $_SERVER["SCRIPT_NAME"]);
		echo "<p style=\"line-height:1.5;\">Wenn Sie Probleme mit der neuen Version haben, können Sie jederzeit mit der alten Version arbeiten. Sie finden die alte Version hier:<br /><a href=\"$link\" target=\"_blank\">$link</a></p>";
		
		$B = new Button("Anwendung\nneu laden", "refresh");
		$B->onclick("document.location.reload();");
		$B->style("margin:10px;float:left;margin-top:0px;");
		
		echo "<div style=\"margin-top:20px;line-height:1.5;color:grey;\">".$B."Klicken Sie nun auf den nebenstehenden Knopf, um die Anwendung neu zu laden und die Aktualisierung fortzusetzen.</div><div style=\"clear:both;\"></div>";
	}
	
	private function checkWritable($dirPath){
		$unwritable = array();
		
		if(!is_writable($dirPath))
			$unwritable[] = $dirPath;
		
		$dir = new DirectoryIterator($dirPath);
		foreach($dir as $file) {
			if($file->isDot())
				continue;
			
			if($file->isDir()) {
				$unwritable = array_merge($unwritable, $this->checkWritable($file->getPathname()));
				
				if(!$file->isWritable())
					$unwritable[] = $file->getPathname();
				
				continue;
			}
			
			if(!$file->isWritable())
				$unwritable[] = $file->getPathname();
		}
		
		return $unwritable;
	}
	
	private function copyDir($dirPath, $to, $move = false, $ignoreExisting = false){
		$dir = new DirectoryIterator($dirPath);
		foreach($dir as $file) {
			if($file->isDot())
				continue;
			
			if(strpos($file->getFilename(), ".") === 0)
				continue;
			
			if(strpos($file->getFilename(), "phynxBackup") === 0)
				continue;
			
			if(strpos($file->getFilename(), "phynxUpdate") === 0)
				continue;
			
			if($file->isDir()) {
				mkdir($to.$file->getFilename());
				
				$this->copyDir($file->getPathname(), $to.$file->getFilename()."/", $move, $ignoreExisting);
				
				if($move)
					rmdir($file->getPathname());
				
				continue;
			}
			
			if($ignoreExisting AND file_exists($to.$file->getFilename()))
				continue;
			
			$copyOK = copy($file->getPathname(), $to.$file->getFilename());
			
			if($copyOK AND $move)
				unlink($file->getPathname());
		}
	}
	
	private function emptyDir($dirPath){
		$dir = new DirectoryIterator($dirPath);
		foreach($dir as $file) {
			if($file->isDot())
				continue;
			
			if($file->isDir()) {
				$this->emptyDir($file->getPathname());
				
				rmdir($file->getPathname());
				continue;
			}
			
			unlink($file->getPathname());
		}
	}
	
	private function throwError($title, $message, $list = ""){
		$T = new HTMLTable(1);

		$BE = new Button($title, "warning", "icon");
		$BE->style("float:left;margin-right:10px;margin-bottom:15px;");

		$T->addRow(array($BE.$message));

		if($list != "")
			$T->addRow($list);
		
		$B = new Button("Aktualisierung\nbeenden", "bestaetigung");
		$B->style("margin:10px;");
		$B->onclick(OnEvent::closePopup("mPatch"));
		die($T.$B);
	}
	
	public static function descriptionParser($w){
		return nl2br($w);
	}
	
	public function runUpdate($XML){
		$IDs = array();

		$XML->lCV3();
		while($t = $XML->getNextEntry()) {
			$ac = new anyC();
			$ac->setCollectionOf("Patch");
			$ac->addAssocV3("PatchNummer","=",$t->A("PatchNummer"));
			$P = $ac->getNextEntry();
			
			if($P == null) {
				$IDs[] = $t->newMe();
			}
			else/*if($P->A("PatchDate") < $t->A("PatchDate"))*/ {
				$nP = new Patch($P->getID());
				$AS = $t->getA();
				unset($AS->PatchID);
				$nP->setA($AS);
				$nP->saveMe();
				$IDs[] = $P->getID();
			}
		}
		return $IDs;
	}
	
	public function localUpdate($xmlString){
		$XML = new XMLC();
		$XML->setXML($xmlString);
		return $this->runUpdate($XML);
	}
	
	public function update(){
		$XML = new XMLC("http://www.open3a.de/updates.xml");
		$this->runUpdate($XML);
	}
	
	public function getXML(){
		#$this->addAssocV3("PatchNummer","<", "1000000");
		$this->lCV3();
		$XML = new XML();
		$XML->setCollection($this);
		$XML->setXMLHeader();
		echo $XML->getXML();
	}
}
?>