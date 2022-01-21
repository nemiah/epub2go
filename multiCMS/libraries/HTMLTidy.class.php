<?php
/*
 *  This file is part of phynx.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class HTMLTidy {
	protected $content;
	private $makeUTF8 = false;
	private $done = false;
	private $cleanedFile;
	private $errorsFile;
	protected $isUTF8 = false;

	function __construct($uri = null){
		if($uri != null) {
			   // file_get_contents($uri) funktioniert komischerweise seit 2021 nicht mehr!
			   //   daher folgendes entfernt:
			   //  $this->content = file_get_contents($uri);

			  $this->content = $this->curlGETContent($uri);

		} // END if
	}// end __construct 


	private function curlGETContent($uri) {
   		  	  $ch = curl_init();
			  curl_setopt($ch, CURLOPT_URL, $uri);
			  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			  curl_setopt($ch, CURLOPT_USERPWD, "user:pass");
			  $result = curl_exec($ch);
			 curl_close($ch);	  
			return $result;
	}

	// 2021 phi eingebaut, um den Fehler zu finden, dass file_get_contents nicht mehr funktioniert!
	// Die Lösung war 'curl', wegen den Zertifikaten (φ 2021)
	private function debug2021($message){
		 $fp = fopen('/var/www/epub2go/epub2go/multiCMS/ubiquitous/debug2021.txt', 'a') or die("Unable to open file!");
 		 fwrite($fp, $message);
		 fwrite($fp, "\n");
 		 fclose($fp);
	}


	function setContent($text){
		$this->content = $text;
	}

	function makeUTF8(){
		$this->makeUTF8 = true;
	}

	function cleanUp(){

	}

	function tidy(){
		if($this->done) return;
		
		if($this->makeUTF8)
			$this->content = utf8_encode($this->content);

		$this->cleanUp();

		$temp = Util::getTempFilename(session_id(), "html");
		$this->cleanedFile = Util::getTempFilename(session_id(), "xhtml");
		$this->errorsFile = Util::getTempFilename(session_id()."_errors", "txt");
		file_put_contents($temp, $this->content);

		$SC = new SystemCommand();
		if(!Util::isWindowsHost())
			$SC->setCommand("tidy -asxhtml -numeric ".($this->isUTF8 ? "-utf8" : "")." < $temp > $this->cleanedFile 2> $this->errorsFile");
		else
			$SC->setCommand("c:/tidy.exe -asxhtml -numeric < $temp > $this->cleanedFile");
		$SC->execute();
		#echo htmlentities(file_get_contents($this->errorsFile));
		$this->done = true;
	}

	function getCleaned(){
		$this->tidy();
//		echo 'DEBUGG ((' . $this-cleanedFile . "))";
		return file_get_contents($this->cleanedFile);
// DEBUG PHI dez. 2021
//		return  $this->curlGETContent($this->cleanedFile);
		
	}

	function removeTag($tag){
		while(stripos($this->content,"<$tag") > 0){
			$pos1 = stripos($this->content, "<$tag");
			$pos2 = stripos($this->content, "</$tag>", $pos1);
			if($pos2 === false) break;
			$len = $pos2 - $pos1 + strlen("</$tag>");

			$x = substr($this->content, $pos1, $len);
			$this->content = str_replace($x, '', $this->content);
		}

		while(stripos($this->content,"<$tag") > 0){
			$pos1 = stripos($this->content,"<$tag");
			$pos2 = stripos($this->content,">", $pos1);
			if($pos2 === false) break;
			$len = $pos2 - $pos1 + strlen(">");

			$x = substr($this->content, $pos1, $len);
			$this->content = str_replace($x, '', $this->content);
		}

		while(stripos($this->content,"<$tag") > 0){
			$pos1 = stripos($this->content,"<$tag");
			$pos2 = stripos($this->content,"/>", $pos1);
			if($pos2 === false) break;
			$len = $pos2 - $pos1 + strlen("/>");

			$x = substr($this->content, $pos1, $len);
			$this->content = str_replace($x, '', $this->content);
		}
	}

	function removeComments(){
		while(stripos($this->content,"<!--") > 0){
			$pos1 = stripos($this->content, "<!--");
			$pos2 = stripos($this->content, "-->", $pos1);
			if($pos2 === false) break;
			$len = $pos2 - $pos1 + strlen("-->");

			$x = substr($this->content, $pos1, $len);
			$this->content = str_replace($x, '', $this->content);
		}
	}
}
?>
