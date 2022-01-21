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



class gbSpiegelParserGUI implements iGUIHTML2, iEPubParser {
	private $title;
	private $URL;
	private $author;
	private $debug = false;
	private $guessedVolume;

	function __construct($debug = false){
		$this->debug = $debug;
	}

	public function getHTML($id){
		$F = new HTMLForm("epubForm", array("spiegelURL"), $this->getLabel());

		$F->setLabel("spiegelURL", "URL");

		$F->setSaveRMEPCR("ePub generieren", "", "gbSpiegelParser", -1, "callGenerateEPub", "function(transport){ $('ausgabe').update(transport.responseText); }");

		$ST = new HTMLSideTable("right");

		$B = $ST->addButton("Debug-\nausgabe", "./ubiquitous/EPub/EPubDebug.png");
		$B->windowRme("gbSpiegelParser", -1, "debug", array("$('epubForm').spiegelURL.value"));

		return $ST.$F."<div id=\"ausgabe\"></div>";
	}

/* Debug Function eingebaut, um Fehler besser einzugrenzen, welche nur ONLINE auf epub2go.eu passieren. (phi 2021)*/
	private function debug2021($message){
		 $fp = fopen('/var/www/epub2go/epub2go/multiCMS/ubiquitous/debug2021.txt', 'a') or die("Unable to open file!");
 		 fwrite($fp, $message);
		 fwrite($fp, "\n");
 		 fclose($fp);
	}

	public function debug($spiegelURL){
		$ex = explode("/", $spiegelURL);
		#$ex[5] = "1";
		
		$spiegelURL = implode("/", $ex);
		$this->URL = $spiegelURL;
		
		$HS = new HTMLSlicer(new GBTidy($spiegelURL));

		echo "<pre>";
		echo "URL:\t\t$spiegelURL\n";
		echo "Titel:\t\t".$this->findTitle($HS)."\n";
		echo "Autor:\t\t".$this->findAuthor($HS)."\n";
		#echo "ISBN:\t\t".$this->findISBN($HS)."\n";
		echo "Link 1:\t\t".$this->findLink($HS, true)."\n";
		echo "Link 2:\t\t".$this->findLink($HS, false)."\n";
		echo "Base:\t\t".$this->findBase($HS)."\n";
		echo "Kapitelanzahl:\t".$this->findChapterNumber($HS)."\n";
		echo "CSS:\t\t".$this->findCSS($HS)."\n";
		echo "Images:\t\t";
		$Is = $this->findImages($HS);
		if(isset($Is) AND $Is != false AND $Is != null AND count($Is) > 0)
			foreach($Is AS $I)
				echo $this->findBase($HS).$I->attributes()->src."\n";
				#$I->attributes()->src = basename($I->attributes()->src);
			
		
		$ex = explode("/", $spiegelURL);

		echo "BID:\t\t".$ex[4]."\n";

		$Kapitel = $this->findChapter($HS);
		if($Kapitel == null)
			$Kapitel = "-Kapitelname unbekannt-";
		
		echo "Kapitel:\t$Kapitel\n";
		
		$Kapitel = $this->findChapterSub($HS);
		
		echo "Kapitel Sub:\t$Kapitel\n";
		echo "Band:\t\t".$this->findVolume($HS)."\n";

		$Ps = $this->findParagraphs($HS);
		foreach($Ps AS $P){
			$paragraph = $P->asXML();
			$paragraph = $this->findFootnotes($paragraph);
			$paragraph = $this->findAnnotations($paragraph);
			
			echo $paragraph;
		}

		print_r($this->makeFootnotesParagraph());
		print_r($this->makeAnnotationsParagraph());
		
		if(Util::isWindowsHost()){
			$zipExe = dirname(__FILE__)."/zip.exe";
			echo "ZIP:\t\t<span style=\"".(file_exists($zipExe) ? "color:green;" : "color:red;")."\">".$zipExe."</span>\n";
		}
		
		echo "</pre>";
	}

	public function getLabel(){
		return "gutenberg.spiegel.de";
	}

	public function callGenerateEPub($spiegelURL){
		Red::alertD($this->generateEPub($spiegelURL));
	}

	public function generateEPub($spiegelURL, $force = false){
		set_time_limit(5 * 60);
		$readyPath = Util::getRootPath()."ubiquitous/EPub/ReadyEPubs/";
		
		if($spiegelURL == ""
			OR stripos($spiegelURL, "//www.projekt-gutenberg.org") === false
			OR $spiegelURL == "https://www.projekt-gutenberg.org/...")
			die("error:'Bitte tragen Sie die Adresse zu einem Buch auf https://www.projekt-gutenberg.org ein'");
		
		$ex = explode("/", $spiegelURL);
		$this->URL = $spiegelURL;
		
		$BID = $ex[3]."_".$ex[4];

		/**
		 * Die ePub-Datei wurde bereits erstellt
		 */
		if(file_exists($readyPath.$BID.".lib")){
			$file = file($readyPath.$BID.".lib");
			if(filesize($readyPath.trim($file[0])) < 5183){
				unlink($readyPath.trim($file[0]));
				unlink($readyPath.$BID.".lib");
			}
			
		}
		if(file_exists($readyPath.$BID.".lib") AND filemtime($readyPath.$BID.".lib") > time() - 3600 * 24 * 21){
			$file = file($readyPath.$BID.".lib");
			$this->author = trim($file[1]);
			$this->title = trim($file[2]);
			
			if(trim($file[0]) == $BID."__-_.epub" OR trim($file[0]) == $BID."_-_.epub" OR trim($file[0]) == "_-_.epub"){
				unlink($readyPath.trim($file[0]));
				unlink($readyPath.$BID.".lib");
			}
			
			if(file_exists($readyPath.trim($file[0])))
				return trim($file[0]);
		}
		
		$spiegelURL = implode("/", $ex);
		
		/**
		 * HTML laden
		 */
		$TmpGBTidy = new GBTidy($spiegelURL);
		$HS = new HTMLSlicer($TmpGBTidy);


		#$Kapitelanzahl = $this->findChapterNumber($HS);
		/*if($Kapitelanzahl > 50 AND !$force){
			$AC = anyC::get("EPubDL", "EPubDLLink", $spiegelURL);
			$AC->addAssocV3("EPubDLDone", "=", "0");
			$InQueue = $AC->getNextEntry();
			
			if($InQueue == null){
				$F = new Factory("EPubDL");
				$F->sA("EPubDLLink", $spiegelURL);

				$F->store();
				
				throw new Exception('Der gewünschte Titel enthält über 50 Kapitel und wurde der Warteschlange hinzugefügt.');
			}
			
			throw new Exception("Der gewünschte Titel befindet sich bereits in der Warteschlange an Position ".mEPubDL::getLinkPosition($spiegelURL).".");
		}*/
		
		$Title = $this->title = $this->findTitle($HS);
		$Author = $this->author = $this->findAuthor($HS);
		
		/**
		 * Band auslesen, falls vorhanden
		 */
		$Band = "";
		if($this->findVolume($HS) != null)
			$Band = " Vol".$this->findVolume($HS);
		

		/**
		 * Neues ePub-Objekt erstellen
		 */
		$EP = new EPub($Author."", $Title.$Band);

		/**
		 * ISBN finden und zu ePub hinzufügen
		 */
		#$EP->setISBN($this->findISBN($HS));
		$EP->setURI($spiegelURL);

		$EP->setCSS(file_get_contents(__DIR__."/".$this->findCSS($HS)));
		
		/**
		 * Ein neues Kapitel für die erste Seite beginnen
		 */
		$Kapitel = $this->findChapter($HS);
		if($Kapitel != null)
			$EP->addChapter($Kapitel);
		else
			$EP->addChapter("-Kapitelname unbekannt-");


		/**
		 * Bilder finden und zu ePub hinzufügen
		 */
		$Is = $this->findImages($HS);
		if(isset($Is) AND $Is != false AND $Is != null AND count($Is) > 0)
			foreach($Is AS $I){
				$EP->addImage($this->findBase($HS).$I->attributes()->src);
				$I->attributes()->src = basename($I->attributes()->src);
			}

		/**
		 * Die Absätze der ersten Seite hinzufügen
		 */
		$Ps = $this->findParagraphs($HS);
		foreach($Ps AS $P){
			$paragraph = $P->asXML();
			$paragraph = $this->findFootnotes($paragraph);
			$paragraph = $this->findAnnotations($paragraph);
			$EP->addParagraph($paragraph);
		}
			
		$EP->addParagraph($this->makeFootnotesParagraph());
		$EP->addParagraph($this->makeAnnotationsParagraph());

		/**
		 * Weitere Seiten
		 */
		$nextLink = $this->findLink($HS, true);
		while($nextLink != null){
			$HS = new HTMLSlicer(new GBTidy($this->findBase($HS).$nextLink));


			/**
			 * Link zur nächsten Seite finden
			 */
			$nextLink = $this->findLink($HS, false);
			
			$chapter = $this->findChapterSub($HS);
			if($chapter != null)
				$EP->addChapter($chapter);

			/**
			 * Bilder finden und zu ePub hinzufügen
			 */
			$Is = $this->findImages($HS);
			if(isset($Is) AND $Is != false AND $Is != null AND count($Is) > 0)
				foreach($Is AS $I){
					$EP->addImage($this->findBase($HS).$I->attributes()->src);
					$I->attributes()->src = basename($I->attributes()->src);
				}
				
			/**
			 * Die Absätze hinzufügen
			 */
			$Ps = $this->findParagraphs($HS);
			foreach($Ps AS $P){
				$paragraph = $P->asXML();
				$paragraph = $this->findFootnotes($paragraph);
				$paragraph = $this->findAnnotations($paragraph);
				$EP->addParagraph($paragraph);
			}
			
			$EP->addParagraph($this->makeFootnotesParagraph());
			$EP->addParagraph($this->makeAnnotationsParagraph());
		}
		
		/**
		 * ePub erstellen und speichern
		 */
		$filename = $EP->pubify();
		file_put_contents($readyPath.$BID.".lib", $filename."\n$this->author\n$this->title");
		
		if($filename == $BID."_-_.epub")
			throw new Exception("Der Download ist fehlgeschlagen, bitte versuchen Sie es zu einem späteren Zeitpunkt erneut.");


		return $filename;
	}
	
	private $footnotes = array();
	private $footnoteCounter = 1;
	private $footnoteLast = 1;
	private function findFootnotes($paragraph){
		$parsed = new SimpleXMLElement($paragraph);
		$xp = $parsed->xpath("//span[@class='footnote']");
		
		foreach($xp AS $FN){
			$match = $FN->asXML();
			$paragraph = str_replace($match, "<a href=\"#foot".($this->footnoteCounter)."\" name=\"text".($this->footnoteCounter)."\"><sup>F".($this->footnoteCounter)."</sup></a>", $paragraph);
			
			$this->footnotes[$this->footnoteCounter] = $match;
			$this->footnoteCounter++;
		}
		
		return $paragraph;
	}
	
	private $annotations = array();
	private $annotationsCounter = 1;
	private $annotationsLast = 1;
	private function findAnnotations($paragraph){
		$parsed = new SimpleXMLElement($paragraph);
		$xp = $parsed->xpath("//span[@class='tooltip']");
		
		foreach($xp AS $FN){
			$match = $FN->asXML();
			
			$paragraph = str_replace($match, "$FN<a href=\"#annotation".($this->annotationsCounter)."\" name=\"textAnno".($this->annotationsCounter)."\"><sup>A".($this->annotationsCounter)."</sup></a>", $paragraph);
			
			$this->annotations[$this->annotationsCounter] = $FN;
			$this->annotationsCounter++;
		}
		
		return $paragraph;
	}
	
	private function makeFootnotesParagraph(){
		if(!count($this->footnotes))
			return null;
		
		$html = "<ol start=\"".$this->footnoteLast."\">";
		
		foreach($this->footnotes AS $k => $footnote){
			$html .= "<li><a href=\"#text".($k)."\" name=\"foot".($k)."\">$footnote</a></li>";
			
			$this->footnoteLast++;
		}
		
		$html .= "</ol>";
		
		$this->footnotes = array();
		
		return $html;
	}
	
	private function makeAnnotationsParagraph(){
		if(!count($this->annotations))
			return null;
		
		$html = "<ol start=\"".$this->annotationsLast."\">";
		
		foreach($this->annotations AS $k => $annotation){
			$html .= "<li><a href=\"#textAnno".($k)."\" name=\"annotation".($k)."\">".trim($annotation)."</a>: ".$annotation["title"]."</li>";
			
			$this->annotationsLast++;
		}
		
		$html .= "</ol>";
		
		$this->annotations = array();
		
		return $html;
	}

	private function findChapterNumber($HS){
		
		$Info = $HS->getTag("//ul[@class='gbnav']//li");
		
		return count($Info);
	}
	
	private function findChapter($HS){
		$Info2 = $HS->getTag("//div[@id='gutenb']/h2[@class='title']");
		if(isset($Info2[0]))
			$Info = $Info2;
		
		$Info3 = $HS->getTag("//div[@id='gutenb']/h3");
		if(isset($Info3[0]))
			$Info = $Info3;
		$Chapter = $Info[0]."";
		$Chapter = preg_replace("/(?:\s|&nbsp;)+/", " ", $Chapter);

		return $Chapter;
	}
	
	private function findChapterSub($HS){
		$Info = $HS->getTag("//div[@id='gutenb']/h2");
		
		if(!isset($Info[0]))
			$Info = $HS->getTag("//div[@id='gutenb']/h3");
		
		if(!isset($Info[0]))
			return null;
		
		$Chapter = $Info[0]."";
		$Chapter = preg_replace("/(?:\s|&nbsp;)+/", " ", $Chapter);

		return $Chapter;
	}

	#private $lastLink = null;
	private function findLink($HS, $isfirstPage = true){
		$KLs = $HS->getTag("//div[@id='gutenb']/a");

		$nextLink = null;
		foreach($KLs AS $link){
			if(strpos($link."", "weiter") !== false)
				$nextLink = $link->attributes()["href"]."";
		}
		
		$this->lastLink = $nextLink;
		
		return $nextLink;
	}

	private function findParagraphs($HS){
		$P = $HS->getTag("//div[@id='gutenb']/p | //div[@id='gutenb']/img | //div[@id='gutenb']/h4 | //div[@id='gutenb']/h3 | //div[@id='gutenb']/h2 | //div[@id='gutenb']/h1 | //div[@id='gutenb']/h2[@class='title'] | //div[@id='gutenb']/h5 | //div[@id='gutenb']/table | //div[@id='gutenb']/div | //div[@id='gutenb']/ol | //div[@id='gutenb']/ul | //div[@id='gutenb']/li | //div[@id='gutenb']/blockquote");

		return $P;
	}

	private function findImages($HS){
		return $HS->getTag("//div[@id='gutenb']//img");# | //div[@id='gutenb']/div/img | //div[@id='gutenb']/p/span/img");
	}

	private function findVolume($HS){
		if($this->guessedVolume != null) return $this->guessedVolume;
		
		$Vol = $HS->getTag("//meta[@name='volume']");
		if(!isset($Vol[0]))
			return null;

		if($Vol[0]["content"] == "") return null;

		return $Vol[0]["content"];
	}
	
	private function findTitle($HS){
		$T = $HS->getTag("//meta[@name='title']");
		if(!isset($T[0]))
			return "";

		if($T[0]["content"]."" == "") return "";

		$Title = $T[0]["content"]."";
		
		if(strpos($Title, ", Band") !== false){
			preg_match("/, Band ([0-9\-]*)/", $Title, $matches);
			if(isset($matches[1][0]))
				$this->guessedVolume = $matches[1][0];
		}


		if(strpos($Title, "- Erstes Buch") !== false)
			$this->guessedVolume = "1";

		if(strpos($Title, "- Zweites Buch") !== false)
			$this->guessedVolume = "2";

		if(strpos($Title, "- Drittes Buch") !== false)
			$this->guessedVolume = "3";

		if(strpos($Title, "- Viertes Buch") !== false)
			$this->guessedVolume = "4";

		if(strpos($Title, "- Fünftes Buch") !== false)
			$this->guessedVolume = "5";

		$Title = str_replace(" ?", " -", $Title);
		$Title = str_replace("? ", "- ", $Title);
		$Title = str_replace("\n", " ", $Title);
		$Title = str_replace("(", "", $Title);
		$Title = str_replace(")", "", $Title);

		return preg_replace("/, Band [0-9]/", "", $Title);
	}

	private function findCSS($HS){
		$A = $HS->getTag("//link[@type='text/css']");
		
		if(!isset($A[0]))
			return "prosa.css";
		
		return basename($A[0]["href"]);
	}
	
	private function findAuthor($HS){
		$A = $HS->getTag("//meta[@name='author']");
		if(!isset($A[0]))
			return "";

		if($A[0]["content"]."" == "") return "";

		$Author = trim(str_replace("/", "und", $A[0]["content"].""));
		
		return strip_tags($Author);
	}

	public function getAuthor(){
		return $this->author;
	}

	public function getTitle(){
		return $this->title;
	}
	
	public function findBase($HS){
		return dirname($this->URL)."/";
		
		return "/";
	}
}


class GBTidy extends HTMLTidy {
	function cleanUp(){
		$this->isUTF8 = true;
		
		if(strpos($this->content, "<hr") === false){
			$this->content = str_replace("<BR CLEAR=\"all\"> </DIV>", "<BR CLEAR=\"all\"> </DIV><div id=\"gutenb\">", $this->content);
			$this->content = str_replace("</body>", "</div></body>", $this->content);
		}
		
		$this->content = preg_replace("/<hr size=\"1\" color=\"#808080\">/", "<div id=\"gutenb\">", $this->content, 1);
		$this->content = preg_replace("/<hr size=\"1\" color=\"#808080\">/", "</div>", $this->content, 1);
		
		$this->content = preg_replace("/<hr>&nbsp;<\/hr>/", "<div id=\"gutenb\">", $this->content, 1);
		$this->content = preg_replace("/<hr>&nbsp;<\/hr>/", "</div>", $this->content, 1);
		
		$this->content = str_replace("<tt>", "<span class=\"teletype\">", $this->content);
		$this->content = str_replace("</tt>", "</span>", $this->content);

		$this->removeTag("script");
		$this->removeTag("noscript");
		$this->removeComments();
	}
}
?>
