Lansurfer Intranet Readme
=========================
Version: 1.55a
Copyright (c) 2000-2001 Tobias 'TcT' Taschner
Email: tct@lansurfer.de

Installation
============
Die Benutzung der Software setzt einen Funktionsfähigen MySQL Server vorraus.
Des weiteren wird ein Web Server benötigt der mindestens PHP 3 unterstützt. (PHP 4 Empfohlen)

Grundlegende Schritte zur Einrichtung
=====================================
1. Einfach alle Dateien auspacken 
   in dem Verzeichnis "htdocs" befinden sich das eigentliche Programm in Form von PHP Scripten
2. die Datei htdocs/includes/ls_conf.inc mit einem Texteditor öffnen und die Zugangsdaten ueberpruefen
   ggf. die Daten anpassen
3. im Browser die _setup.phtml aufrufen, beim ersten start ueberprüft diese Datei die SQL konfiguration
   und erzeugt alle benoetigten datenbanken und tabellen.
4. aus dem Internetbereich von LANsurfer die "Daten fuers Intranet" herrunterladen
5. erneut die _setup.phtml aufrufen dort unter "Intranet Daten" die soeben heruntergeladene intranet.sql 
   auswaehlen und "Importieren" druecken
6. evtl noch das aussehen der Dateien ein wenig anpassen :)
  dazu sind besonders die dateien content.phtml (der linke frame) und includes/ls_base.inc zu beachten

Wichtige Hinweise
=================
- Nach dem Importieren mindestens einem Benutzer des OrgaTeams auf der Admin Page das Recht geben 
  "Turniere" anzulegen
- Beim Anlegen des Turniers ist es zur zeit noch notwendig einen "Ergebnissnamen 1" einzutragen
  damit wird das ergebnis eines spiels bewertet also z.B. Frags bei Q3&UT, Wins bei CS oder Captures bei CTF
- zum Login muss zur zeit noch Cookie Support im Browser aktiviert sein

Sicherheits Hinweis
===================
- _setup.phtml sollte wenn sie nicht mehr gebraucht wird entfernt werden oder umbenannt
