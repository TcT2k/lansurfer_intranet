LANsurfer Intranet Readme
=========================
Copyright (c) 2000-2001 Tobias 'TcT' Taschner
Email: tct@lansurfer.com

Installation
============
Die Benutzung der Software setzt einen Funktionsfähigen MySQL Server vorraus.
Des weiteren wird ein Web Server benötigt der mindestens PHP 4.0.5 unterstützt.
Um ein nicht englisches Intranet zu betreiben muss das modul "gettext" von PHP geladen sein.

Grundlegende Schritte zur Einrichtung
=====================================
  I. aus dem Internet von LANsurfer in der "Party Konfiguration" die "Daten fuers Intranet" herrunterladen
 II. Einfach alle Dateien auspacken 
     in dem Verzeichnis "htdocs" befinden sich das eigentliche Programm in Form von PHP Scripten
III. im Browser die _setup.php (z.B. http://localhost/_setup.php) aufrufen, und den Anweisungen auf der Seite folgen
     1. Konfiguration: 
        Hier einfach die benötigten Angaben eintragen (diese Einstellungen können später immer wieder bearbeitet werden)
     2. Datenbank Überprüfen/Erzeugen:
		    Hier wird das korrekte Format der Datenbank überprüft und dieses ggf. angepasst. 
		    Diese Seite sollte nach einem Intranet Update aufgerufen werden.
     3. Registrierungsdaten Importieren
        Auf dieser Seite einfach die intranet.sql auswählen ind den "Import" button drücken
 IV. evtl. noch das aussehen der Dateien ein wenig anpassen:
     Dazu sind besonders die Dateien in includes/design/, intra.css (Style Sheet angaben die im ganzen Intranet 
     benutzt werden und includes/ls_base.inc zu beachten

Wichtige Hinweise
=================
- Nach dem Importieren der Intranetdaten mindestens einem Benutzer des OrgaTeams auf der "Admin Page" 
  das Recht "Turniere" gegeben werden.
- Für die Benutzung des Caterings gilt das gleiche
- zum Login muss zur Zeit noch Cookie Support im Browser aktiviert sein

Sicherheits Hinweis
===================
- Die Datei _setup.php sollte, wenn sie nicht mehr gebraucht wird, entfernt werden oder umbenannt werden
