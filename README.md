# epub2go

## Installation

	apt install tidy
	
	git clone https://github.com/nemiah/epub2go.git
	
	cd epub2go
	
	chmod 777 multiCMS/ubiquitous/EPub/ReadyEPubs
	chmod 777 multiCMS/ubiquitous/EPub/TempEPubs
	chmod 666 multiCMS/system/DBData/Installation.pfdb.php
	
Das verwendete Framwork sollte bereits PHP Version 8.0 unterstützen, der Generator selbst wurde bisher nur mit PHP 5.6 getestet.
	
## Setup

Öffne die Anwendung im Unterverzeichnis /multiCMS im Browser und folge den Anweisungen zum Eintragen der Datenbank-Zugangsdaten und zum Anlegen eines neuen Benutzers.

Zum Erstellen der Seite bitte die Datei `setup.sql` in die Datenbank importieren. Der ePub-Generator wird über multiCMS betrieben. Das ist ein kleines CMS, das in diesem Fall auch gleich das Backend zur Verfügung stellt. Mit den Daten in der sql-Datei sollte alles soweit eingerichtet werden, dass der Generator beim Aufruf der index.php-Datei im Wurzelverzeichnis gleich angezeigt wird.

## Mitwirken

Debugging: Als Benutzer gibt es nach der Anmeldung einen Reiter "ePub", dort kann nach der Eingabe der Adresse zum Buch die Debugging-Ausgabe gestartet werden. 
Dazu wird in der Datei /multiCMS/ubiquitous/EPub/gbSpiegelParserGUI.class.php die Methode `gbSpiegelParserGUI::debug()` aufgerufen.

Sämtliche Logik zum Verarbeiten der Quelldaten befindet sich in den Methoden der Klasse `gbSpiegelParserGUI`.

Wenn der HTML-Code vor der Verarbeitung angepasst werden soll, dann kann dies in der Methode `GBTidy::cleanUp()` eingefügt werden. Die Klasse "GBTidy" befindet sich in der Datei /multiCMS/ubiquitous/EPub/gbSpiegelParserGUI.class.php ganz unten.

Pull requests sind willkommen, die Daten der Bücher sind mitunter recht inhomogen.
