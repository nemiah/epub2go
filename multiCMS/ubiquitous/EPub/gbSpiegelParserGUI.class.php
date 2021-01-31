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
			echo ($paragraph);
			#$EP->addParagraph($paragraph);
		}

		print_r($this->makeFootnotesParagraph());
		print_r($this->makeAnnotationsParagraph());
		
		if(Util::isWindowsHost()){
			$zipExe = dirname(__FILE__)."/zip.exe";
			echo "ZIP:\t\t<span style=\"".(file_exists($zipExe) ? "color:green;" : "color:red;")."\">".$zipExe."</span>\n";
		}
		#echo "\n\n";

		#print_r(htmlentities($HS->getStructure()->asXML()));

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
		
		#$url = parse_url($spiegelURL);
		#parse_str($url["query"], $query);
		$ex = explode("/", $spiegelURL);
		$this->URL = $spiegelURL;
		#var_dump($ex);
		
		$BID = $ex[3]."_".$ex[4];
		#if(strpos($BID, "-") !== false){
		#	$ex2 = explode("-", $BID);
		#	$BID = $ex2[count($ex2) - 1];
		#}
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

		#$ex[5] = "chap001.html";
		
		$spiegelURL = implode("/", $ex);
		
		/**
		 * HTML laden
		 */
		$HS = new HTMLSlicer(new GBTidy($spiegelURL));

		$Kapitelanzahl = $this->findChapterNumber($HS);
		if($Kapitelanzahl > 50 AND !$force){
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
		}
		
		$Title = $this->title = $this->findTitle($HS);
		$Author = $this->author = $this->findAuthor($HS);
		#var_dump($Title);
		#var_dump($Author);
		#die();
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
		#chmod($readyPath.$BID.".lib", 0666);
		#chmod($filename, 0666);
		
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
		#$Info = $HS->getTag("//ul[@class='gbnav']//li[@class='active']//a");
		#$Chapter = $Info[0]."";
		#if(strpos($Chapter, "Kapitel") !== 0)
		#	return preg_replace("/(?:\s|&nbsp;)+/", " ", $Chapter);
		
		
		$Info2 = $HS->getTag("//div[@id='gutenb']/h2[@class='title']");
		if(isset($Info2[0]))
			$Info = $Info2;
		
		$Info3 = $HS->getTag("//div[@id='gutenb']/h3");
		if(isset($Info3[0]))
			$Info = $Info3;
		#var_dump($Info3);
		$Chapter = $Info[0]."";
		$Chapter = preg_replace("/(?:\s|&nbsp;)+/", " ", $Chapter);

		return $Chapter;
	}
	
	private function findChapterSub($HS){
		#$Info = $HS->getTag("//ul[@class='gbnav']//li[@class='active']//a");
		#$Chapter = $Info[0]."";
		#if(strpos($Chapter, "Kapitel") !== 0)
		#	return preg_replace("/(?:\s|&nbsp;)+/", " ", $Chapter);
		
		#if(!isset($Info[0]))
		$Info = $HS->getTag("//div[@id='gutenb']/h2");
		
		if(!isset($Info[0]))
			$Info = $HS->getTag("//div[@id='gutenb']/h3");
		
		if(!isset($Info[0]))
			return null;
		
		#$Info = $HS->getTag("//div[@id='gutenb']");
		$Chapter = $Info[0]."";
		$Chapter = preg_replace("/(?:\s|&nbsp;)+/", " ", $Chapter);

		return $Chapter;
	}

	#private $lastLink = null;
	private function findLink($HS, $isfirstPage = true){
		$KLs = $HS->getTag("//div[@id='gutenb']/a");
		#echo "<pre>";
		$nextLink = null;
		foreach($KLs AS $link){
			#print_r($link);
			if(strpos($link."", "weiter") !== false)
				$nextLink = $link->attributes()["href"]."";
		}
		#echo "</pre>";
		#var_dump($nextLink);
		#if($isfirstPage AND isset($KLs[0]))
		#$nextLink = $KLs[count($KLs) - 1]."";

		#if($this->lastLink !== null AND $this->lastLink == $nextLink)
		#	return null;
		
		$this->lastLink = $nextLink;
			
		#if(!$isfirstPage AND count($KLs) != 2)
		#	return null;
		#if(!$isfirstPage AND count($KLs) == 2)
		#	$nextLink = $KLs[1]."";

		return $nextLink;
	}

	private function findParagraphs($HS){
		$P = $HS->getTag("//div[@id='gutenb']/p | //div[@id='gutenb']/img | //div[@id='gutenb']/h4 | //div[@id='gutenb']/h3 | //div[@id='gutenb']/h2 | //div[@id='gutenb']/h1 | //div[@id='gutenb']/h2[@class='title'] | //div[@id='gutenb']/h5 | //div[@id='gutenb']/table | //div[@id='gutenb']/div | //div[@id='gutenb']/ol | //div[@id='gutenb']/ul | //div[@id='gutenb']/li | //div[@id='gutenb']/blockquote");

		#if($P == null) $P = $HS->getTag("//div[@id='gb_data']/p | //div[@id='gb_data']/h4 | //div[@id='gb_data']/h1 | //div[@id='gb_data']/h5 | //div[@id='gb_data']/table | //div[@id='gb_data']/div | //div[@id='gb_data']/ol | //div[@id='gb_data']/ul | //div[@id='gb_data']/li | //div[@id='gb_data']/blockquote");

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

		#if(!isset($T[0])){
		#	$Title = $HS->getTag("//div[@id='bookcoll_title']/b");
		#	return str_replace("\n", " ", trim($Title[0].""));
		#}

		return preg_replace("/, Band [0-9]/", "", $Title);
	}

	private function findCSS($HS){
		#<link href="../../css/prosa.css" type="text/css" rel="stylesheet" />
		
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

	#private function findISBN($HS){
		#$I = $HS->getTag("//div[@id='metadata']/table/tr/td/b[text()='isbn']/parent::*/following-sibling::*");
	#	if(!isset($I[0]))
	#		return null;
		
	#	$ISBN = $I[0]."";#$HS->getTag("//meta[@name='isbn']");

	#	return strip_tags($ISBN);#[0]["content"];
	#}

	public function getAuthor(){
		return $this->author;
	}

	public function getTitle(){
		return $this->title;
	}
	
	public function findBase($HS){
		return dirname($this->URL)."/";
		#$base = $HS->getTag("//base");
		
		#$attributes = $base[0]->attributes();
		
		return "/";#$attributes->href[0]."";#[0]["content"];
	}
}


class GBTidy extends HTMLTidy {
	function cleanUp(){
		$this->isUTF8 = true;
		
		if(strpos($this->content, "<hr") === false){
			$this->content = str_replace("<BR CLEAR=\"all\"> </DIV>", "<BR CLEAR=\"all\"> </DIV><div id=\"gutenb\">", $this->content);
			$this->content = str_replace("</body>", "</div></body>", $this->content);
		}
		
		#$this->content = utf8_decode($this->content);
		$this->content = preg_replace("/<hr size=\"1\" color=\"#808080\">/", "<div id=\"gutenb\">", $this->content, 1);
		$this->content = preg_replace("/<hr size=\"1\" color=\"#808080\">/", "</div>", $this->content, 1);
		
		$this->content = preg_replace("/<hr>&nbsp;<\/hr>/", "<div id=\"gutenb\">", $this->content, 1);
		$this->content = preg_replace("/<hr>&nbsp;<\/hr>/", "</div>", $this->content, 1);
		
		$this->content = str_replace("<tt>", "<span class=\"teletype\">", $this->content);
		$this->content = str_replace("</tt>", "</span>", $this->content);

		#echo ($this->content);
		#die();
		#$hasInnerHTML = strpos($this->content, "<div id=\"gb_texte\"><html><head>");

		#if($hasInnerHTML !== false){
		#	$this->content = str_replace("<div id=\"gb_texte\"><html><head>", "<div id=\"gb_texte\">", $this->content);
		#	$this->content = str_replace("</p></body></html></div>", "</p></div>", $this->content);
		#}

		#$this->content = preg_replace("/<div class=\"chapter\" id=\"ch[0-9]*\">/", "", $this->content);
		#$this->content = preg_replace("/<div class=\"chapter\" id=\"chap[0-9]*\">/", "", $this->content);
		#$this->content = preg_replace("/<div id=\"gn_lnk_print\"><a href=\"\?id=[0-9]*&amp;xid=[0-9]*&amp;kapitel=[0-9]*&amp;cHash=[a-zA-Z0-9]*\" target=\"_blank\">Druckversion<\/a><\/div>/", "", $this->content);
		#$this->content = preg_replace("/<div align=\"right\"><a href=\"\?id=[0-9]*&amp;xid=[0-9]*&amp;kapitel=[0-9]*&amp;cHash=[a-zA-Z0-9]*\" target=\"_blank\">Druckversion<\/a><\/div>/", "", $this->content);
		#$this->content = preg_replace("/<div id=\"ch[0-9]*\" class=\"chapter\">/", "", $this->content);
		#$this->content = preg_replace("/<div id=\"chap[0-9]*\" class=\"chapter\">/", "", $this->content);
		
		#if(strpos($this->content, "id=\"gb_texte\"") === false)
		#	$this->content = preg_replace("/<div class=\"part\" id=\"teil[0-9]*\">/", "<div id=\"gb_texte\">", $this->content);


		$this->removeTag("script");
		#$this->removeTag("spangenberg");
		#$this->removeTag("meta");
		#$this->removeTag("link");
		$this->removeTag("noscript");
		#$this->removeTag("gb_meta");
		#$this->content = str_replace("gb_meta", "meta", $this->content);
		#$this->content = str_replace("<doc>", "", $this->content);
		$this->removeComments();
	}
}
?>