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

class EPub {
	private $name;
	private $author;
	private $title;
	private $fileName;
	private $tempBaseDir;
	private $contentBaseDir;
	private $readyBaseDir;
	private $chapters = array();
	private $paragraphs = array();
	private $isbn;
	private $uri = "";
	private $images = array();
	private $cssCustom = "";

	public function  __construct($author, $title) {
		$this->name = $author." - ".$title;
		$this->author = $author;
		$this->title = $title;

		$this->isbn = rand(1000000, 10000000);
	}

	public function addImage($url){
		$this->images[] = $url;
	}

	public function setISBN($isbn){
		$this->isbn = $isbn;
	}

	public function setURI($uri){
		$this->uri = $uri;
	}
	
	public function setCSS($css){
		$this->cssCustom = $css;
	}

	public function addChapter($chapterName){
		$this->chapters[] = $chapterName;
	}

	public function addParagraph($content){
		if($content === null)
			return;
		
		if(!isset($this->paragraphs[count($this->chapters) - 1]))
			$this->paragraphs[count($this->chapters) - 1] = array();
		
		$this->paragraphs[count($this->chapters) - 1][] = $content;
	}

	private function makeDirs(){
		$this->fileName = Util::makeFilename($this->name);

		$this->tempBaseDir = Util::getRootPath()."ubiquitous/EPub/TempEPubs/$this->fileName/";
		$this->contentBaseDir = Util::getRootPath()."ubiquitous/EPub/TempEPubs/$this->fileName/content/";

		if(!is_dir($this->tempBaseDir)){
			mkdir($this->tempBaseDir);
			mkdir($this->tempBaseDir."META-INF/");
			mkdir($this->contentBaseDir);
			file_put_contents($this->tempBaseDir."mimetype", "application/epub+zip");
		}

		$this->makePGEPubCss($this->contentBaseDir);
		$this->makeContainerXml($this->tempBaseDir."META-INF/");

		$this->readyBaseDir = Util::getRootPath()."ubiquitous/EPub/ReadyEPubs/";
	}

	private function package(){
		#$this->fileName = str_replace("_", " ", $this->fileName);
		$SC = new SystemCommand();
		if(!Util::isWindowsHost())
			$SC->setCommand("cd $this->tempBaseDir && zip -X0 $this->fileName.epub mimetype && zip -Xur9D $this->fileName.epub * && mv $this->fileName.epub $this->readyBaseDir && rm -r $this->tempBaseDir");
		else
			$SC->setCommand("cd $this->tempBaseDir && ".dirname(__FILE__)."/zip.exe -X0 $this->fileName.epub mimetype && ".dirname(__FILE__)."/zip.exe -Xur9D $this->fileName.epub * && move $this->fileName.epub $this->readyBaseDir");
		$SC->execute();
		#zip -X0 EpubGuide-hxa7241.epub mimetype
		#zip -Xur9D EpubGuide-hxa7241.epub *
		return "$this->fileName.epub";
	}

	private function makeChapterFiles(){


		foreach($this->chapters AS $id => $name){
			$paragraphs = "";

			if(isset($this->paragraphs[$id]))
				foreach($this->paragraphs[$id] AS $K => $P){
				#$img = "";

				#if(isset($this->images[$K]))
				#	foreach($this->images[$K] AS $I)
				#		$img .= "<img src=\"".basename($I)."\" />";


				if(
					strpos($P, "<p") === false 
					AND strpos($P, "<h") === false 
					AND strpos($P, "<o") === false 
					AND strpos($P, "<u") === false
				) $paragraphs .= "
		<p>$P</p>";
				else $paragraphs .= "
		$P";


				}

			file_put_contents($this->contentBaseDir."chapter$id.html", '<?xml version=\'1.0\' encoding=\'UTF-8\'?>
<!DOCTYPE html PUBLIC \'-//W3C//DTD XHTML 1.1//EN\'
                  \'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\'>

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title> </title>
		<meta content="text/css" http-equiv="Content-Style-Type"/>
		<meta content="https://www.epub2go.eu ePub generator" name="generator"/>
		<link href="pgepub.css" type="text/css" rel="stylesheet"/>
	</head>
	<body>
		'.$paragraphs.'
	</body>
</html>');
		}
	}

	private function makeTocNcx(){
		$filename = $this->contentBaseDir."toc.ncx";

		$chapters = "";
		foreach($this->chapters AS $id => $name){
			$name = preg_replace("/<sup>[0-9]*<\/sup>/", "", $name);
			$chapters .= "
      <navPoint id=\"navPoint-".($id + 1)."\" playOrder=\"".($id + 1)."\">
         <navLabel>
            <text>$name</text>
         </navLabel>
         <content src=\"chapter$id.html\"/>
      </navPoint>";
		}
		file_put_contents($filename, '<?xml version="1.0"?>
<!DOCTYPE ncx PUBLIC "-//NISO//DTD ncx 2005-1//EN" "http://www.daisy.org/z3986/2005/ncx-2005-1.dtd">

<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">

	<head>
		<meta name="dtb:uid" content="http://www.ePub2Go.eu"/>
		<meta name="dtb:depth" content="2"/>
		<meta name="dtb:totalPageCount" content="0"/>
		<meta name="dtb:maxPageNumber" content="0"/>
	</head>

	<docTitle>
		<text>'.str_replace("&", "&amp;", $this->name).'</text>
	</docTitle>

	<navMap>'.$chapters.'
	</navMap>

</ncx>');

	}

	private function makeDLImages(){
		foreach($this->images AS $P)
			copy($P, $this->contentBaseDir.basename($P));
			
	}

	private function makeContainerXml($dir){
		$filename = $dir."container.xml";
		file_put_contents($filename, '<?xml version=\'1.0\' encoding=\'utf-8\'?>
<container xmlns="urn:oasis:names:tc:opendocument:xmlns:container" version="1.0">
  <rootfiles>
    <rootfile media-type="application/oebps-package+xml" full-path="content/content.opf"/>
  </rootfiles>
</container>');
	}

	private function makeContentOpf(){
		$filename = $this->contentBaseDir."content.opf";

		$chapterFiles = "";
		$imageFiles = "";
		$spine = "";
		foreach($this->chapters AS $id => $name){
			$chapterFiles .= "
		<item href=\"chapter$id.html\" id=\"id$id\" media-type=\"application/xhtml+xml\"/>";

			$spine .= "
		<itemref idref=\"id$id\" linear=\"yes\"/>";
		}
		
		foreach($this->images AS $k => $file){
			$ext = trim(strtolower(pathinfo($file, PATHINFO_EXTENSION)));
			if($ext == "jpg")
				$ext = "jpeg";
			
			$imageFiles .= "
		<item href=\"". basename($file)."\" id=\"image_".$k."\" media-type=\"image/".$ext."\"/>";
		}
		
		file_put_contents($filename, '<?xml version=\'1.0\' encoding=\'UTF-8\'?>

<package xmlns:opf="http://www.idpf.org/2007/opf" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.idpf.org/2007/opf" version="2.0" unique-identifier="id">
	<metadata>
		<dc:rights>Public domain in the USA.</dc:rights>
		<dc:identifier id="id" opf:scheme="URI">'.$this->uri.'</dc:identifier>
		<dc:creator opf:file-as="'.preg_replace("/^[0-9]*_/", "", $this->author).'">'.preg_replace("/^[0-9]*_/", "", $this->author).'</dc:creator>
		<dc:title>'.str_replace("&", "&amp;", $this->title).'</dc:title>
		<dc:language xsi:type="dcterms:RFC4646">de</dc:language>
		<meta content="item2" name="cover"/>
	</metadata>
	<manifest>
		<item href="pgepub.css" id="item1" media-type="text/css"/>'.$chapterFiles.$imageFiles.'
		<item href="cover.jpg" id="item2" media-type="image/jpeg"/>
		<item href="toc.ncx" id="ncx" media-type="application/x-dtbncx+xml"/>
	</manifest>
	<spine toc="ncx">'.$spine.'
	</spine>
	<guide>
		<reference href="cover.jpg" type="cover" title="Cover Image"/>
	</guide>
</package>');

/*
		<!--<dc:subject>Adultery -- Fiction</dc:subject>
		<dc:subject>Prussia (Germany) -- Fiction</dc:subject>
		<dc:date opf:event="publication">2004-03-01</dc:date>
		<dc:date opf:event="conversion">2010-02-15T04:53:08.371523+00:00</dc:date>
		<dc:source>http://www.gutenberg.org/files/5323/5323-8.txt</dc:source>-->
 */
	}

	public function makePGEPubCss($dir){
		$filename = $dir."pgepub.css";
		
		if($this->cssCustom != ""){
			file_put_contents($filename, $this->cssCustom);
			return;
		}
		
		file_put_contents($filename, 'body, body.tei.tei-text {
    color: black;
    background-color: white;
    margin: 0.5em 1em 0 0.5em;
    border: 0;
    padding: 0
    }
div, p, pre, h1, h2, h3, h4, h5, h6 {
    margin-left: 0;
    margin-right: 0
    }
h2 {
    page-break-before: always;
    padding-top: 1em
    }
.pgmonospaced {
    font-family: monospace;
    font-size: 0.9em
    }
a.pgkilled {
    text-decoration: none
    }

span.speaker {
    font-weight: bold;
}

p{
	text-indent: 1.5em;
	margin: 0em;
	margin-bottom: 0.3em;
	text-align: justify;
	line-height: 1.2;
	}
	
td p {
	text-indent: 0em;
}');
	}

	public function pubify(){
		$this->makeDirs();
		$this->makeChapterFiles();
		$this->makeContentOpf();
		$this->makeTocNcx();
		$this->makeDLImages();
		return $this->package();
	}
}
?>