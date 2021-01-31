<?php
/**
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
 */

class CCGB2EPub implements iCustomContent, iFormHandler {

	function getLabel(){
		return "GB2EPub";
	}

	function getCMSHTML(Seite $Seite = null, Content $Content = null){
		$target = filter_input(INPUT_GET, "t");
		
		$default = "https://www.projekt-gutenberg.org/...";
		if($target)
			$default = $target;
		
		$I = new HTMLInput("gutenbergURL", "text", $default);
		$I->onEnter("GB2EPub.doIT();");
		$I->onfocus("if(this.value == '$default') this.value = '';");
		$I->onblur("if(this.value == '') this.value = '$default';");
		$I->id("spiegelURL");

		$B = new Button("epub erzeugen", "/multiCMSData/go-next.png");
		$B->type("icon");
		$B->style("float:right;cursor:pointer;");
		$B->onclick("GB2EPub.doIT();");

		$F = new HTMLForm("reportEPub", array("description", "author", "title"));
		$F->cols(1);

		$F->getTable()->setTableStyle("margin-top:10px;");

		$F->setLabel("author", "Autor");
		$F->setLabel("title", "Titel");
		$F->setLabel("description", "Beschreibung");

		$F->setType("description", "textarea");

		$_GET["permalink"] = "";
		$F->setSaveMultiCMS("Meldung abschicken", "/multiCMSData/go-next.png", get_class($this), "report", "function(transport){ if(multiCMS.checkResponse(transport)) { alert(transport.responseText); GB2EPub.hideReport(); } }");

		$html = "
			<script type=\"text/javascript\">
				Ajax.Responders.register({
					onCreate: function(){
						new Effect.Appear('loader');
						GB2EPub.running = true;
					},

					onFailure: function(transport) {
						alert('An error occured: '+transport.responseText);
					},

					onComplete: function(){
						new Effect.Fade('loader');
						GB2EPub.running = false;
					}
				});
				
				console.log(multiCMS);

				window.onload = function(){
					".($target ? "GB2EPub.doIT();" : "")."
				}
			</script>
		<style type=\"text/css\">
			* {
				line-height:1.5;
			}

			#close a, #close a:link { color:white; }	

			#reportWindow, #queueInfoWindow {
				background-color:white;
				position:absolute;
				top:200px;
				left:630px;
				width:310px;

				border-radius: 9px;

				transform: rotate(2deg);

				filter: progid:DXImageTransform.Microsoft.Matrix(sizingMethod='auto expand', M11=0.9993908270190958, M12=-0.03489949670250097, M21=0.03489949670250097, M22=0.9993908270190958);

				zoom: 1;

				border:1px solid #c0c0c0;
				padding:5px;

				box-shadow: 2px 2px 4px #000000;
			}

			label {
				text-align:left;
			}

			textarea {
				height:100px;
			}

			table {
				width:300px;
			}
		</style>

		<div id=\"gutenberg\" style=\"width:330px;height:300px;position:absolute;top:140px;left:20px;\">$B$I
			<p style=\"margin-top:15px;\">Dieser Generator erzeugt aus den <a href=\"https://www.projekt-gutenberg.org\" target=\"_blank\">Projekt Gutenberg-Büchern</a> Dateien im ePub-Format, die Sie mit den meisten eBook-Readern problemlos lesen können.</p>
			<p>Bitte tragen Sie in das obere Feld die URL zu der Projekt Gutenberg-Seite ein, die das erste Kapitel des Buches enthält. Anschließend benötigen Sie etwas Geduld, bis die Kapitel heruntergeladen und zu einem ePub zusammengefügt wurden.</p>
			<p>Bookmarklet: <a href=\"javascript:void(window.open('http://www.epub2go.eu/?t='+encodeURIComponent(window.location.toString())))\">epub2go</a><br><small style=\"color:grey;\">Ziehen Sie den Link in Ihre Lesezeichen-Leiste und klicken Sie auf das Lesezeichen, um eine gerade geöffnete Gutenberg-Seite direkt an epub2go zu übergeben.</small></p>
		</div>
		<div id=\"ePub\" style=\"position:absolute;top:200px;left:650px;\">
			<div id=\"ePubDLs\">Sie können die fertigen ePub-Dateien<br />anschließend hier herunterladen.</div>
			<div id=\"beta\" style=\"display:none;\">
				<p>Dieser Generator ist <b>BETA</b> und hofft auf Ihre Unterstützung! Wenn Sie eine ePub-Datei mit fehlerhaften Formatierungen oder fehlenden<br />Inhalten finden, verwenden Sie bitte das<br /><img src=\"/multiCMSData/flagG.png\" style=\"margin-bottom:-3px;\" />-Symbol oder melden Sie sich im <a href=\"http://forum.furtmeier.it/viewforum.php?f=6\">Forum</a>.</p>
			</div>
		</div>
		<div id=\"loader\" style=\"position:absolute;top:135px;left:445px;display:none;\"><img src=\"/multiCMSData/ajax-loader.gif\" /></div>
		<div id=\"reportWindow\" style=\"display:none;\">
			<img
				src=\"/multiCMSData/cross.png\"
				onclick=\"GB2EPub.hideReport();\"
				style=\"float:right;cursor:pointer;\"
				alt=\"Schließen\"
				title=\"Schließen\"
			/>
			<p>Mit diesem Formular können Sie eine<br />fehlerhaft erzeugte Datei melden.</p>
			<p>Bitte geben Sie an, in welchem Kapitel<br />der Fehler auftritt und wie er sich äußert.</p>
			$F
		</div>
		
		<div id=\"queueInfoWindow\" style=\"display:none;\">
			<img
				src=\"/multiCMSData/cross.png\"
				onclick=\"GB2EPub.hideQueueInfo();\"
				style=\"float:right;cursor:pointer;\"
				alt=\"Schließen\"
				title=\"Schließen\"
			/>
			<p>In die Warteschlange werden Titel aufgenommen, die zu groß sind, um sofort umgewandelt zu werden und sich noch nicht im Speicher befinden.</p>
			<p>Pro Nacht werden zehn Einträge der Warteschlange abgearbeitet. Wenn Sie einen Titel erneut zum Download eintragen, wird die Position in der Warteschlange angezeigt.</p>
			<p>Die Warteschlange enthält momentan 0 Einträge.</p>
		</div>";

		return $html;
	}

	function getFieldLabels(){
		return array();
	}
	
	function getDescription(){
		return "";
	}

	function handleForm($valuesAssocArray, Handler $handler){
		switch($valuesAssocArray["action"]){

			case "convert":
				require_once Util::getRootPath()."ubiquitous/EPub/EPub.class.php";
				require_once Util::getRootPath()."ubiquitous/EPub/iEPubParser.class.php";
				require_once Util::getRootPath()."ubiquitous/EPub/gbSpiegelParserGUI.class.php";

				$P = new gbSpiegelParserGUI();
				try {
					$filename = $P->generateEPub($valuesAssocArray["spiegelURL"]);
				} catch (Exception $e){
					$m = $e->getMessage();
					
					$m = str_replace("Warteschlange", "<a href=\"#\" onclick=\"GB2EPub.showQueueInfo(); return false;\">Warteschlange</a>", $m);
					
					die($m);
				}
				
				$newIn = ceil(21 - ((time() - filemtime(Util::getRootPath()."ubiquitous/EPub/ReadyEPubs/".$filename)) / (3600 * 24)));
				
				$link = "
					
					<a href=\"/index.php?formID=nix&HandlerName=CCGB2EPub&action=download&FN=%FILENAME\">
						<img src=\"/multiCMSData/go-down.png\" style=\"margin-top:-3px;float:left;margin-right:10px;\" />
						%FILENAME
					</a>
					<img
						src=\"/multiCMSData/flagG.png\"
						style=\"margin-left:10px;margin-bottom:-3px;cursor:pointer;\"
						onmouseover=\"this.src='/multiCMSData/flag.png';\"
						onmouseout=\"this.src='/multiCMSData/flagG.png';\"
						onclick=\"GB2EPub.showReport('".addslashes($P->getTitle())."', '".addslashes($P->getAuthor())."');\"
						alt=\"Fehlerhafte Datei melden\"
						title=\"Fehlerhafte Datei melden\"
					/><br /><small style=\"color:grey;\">Datei wird neu erzeugt in $newIn Tag".($newIn != 1 ? "en" : "")."</small>";

				echo str_replace("%FILENAME", $filename, $link);
			break;

			case "report":
				if($valuesAssocArray["author"] == "")
					Red::alertD ("Bitte geben Sie den Autor ein.");

				if($valuesAssocArray["title"] == "")
					Red::alertD ("Bitte geben Sie den Titel ein.");

				if($valuesAssocArray["description"] == "")
					Red::alertD ("Bitte geben Sie die Fehlerbeschreibung ein.");

				$mail = new htmlMimeMail5();
				$mail->setSubject("Fehlerbericht ePubi");
				$mail->setText(utf8_decode("Autor: ".$valuesAssocArray["author"]."\nTitel: ".$valuesAssocArray["title"]."\nBeschreibung:".$valuesAssocArray["description"]));
				$mail->setFrom("report@epub2go.eu");

				if($mail->send(array("report@epub2go.eu")))
					echo "Vielen Dank für Ihre Meldung!";
				else
					echo "Es ist ein Fehler beim Mailversand aufgetreten. Bitte verwenden Sie das Forum.";
			break;
			
			case "download":
				header("Content-Type: application/epub+zip");
				header("Content-Disposition: attachment; filename=\"".$valuesAssocArray["FN"]."\"");
 
				readfile(Util::getRootPath()."ubiquitous/EPub/ReadyEPubs/".basename($valuesAssocArray["FN"]));
			break;

		}
	}
}
?>
