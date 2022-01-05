# Discovergy Smartmeter
Das Modul ermöglicht das Abfragen von Daten aus dem Discovergy Portal. Getestet wurde es mit dem OEM von ComMetering, wobei es mit allen Integrationen funktionieren sollte. Die komplette Dokumentation gibt es im Forum: https://www.symcon.de/forum/threads/43805-Modul-Discovergy-Smartmeter-die-zweite-f%C3%BCr-Module-Store

## Konfiguration 1.01
Um das Modul nutzen zu können ist ein Account bei Discovery oder einer deren OEMs (z.B. ComMetering, aWATTar, ...) und ein hier verbundener Smartmeter von nöten. Anhand der Login Daten werden alle Smartmeter in Symcon eingerichtet und das Modul stellt dann, entspr. der Liste unten, die entsprechenden Variablen zur Verfügung je nach Zähler Typ zur Verfügung. 
Um langfristig Daten auszuwerten, sollte die Archivfunktion für die Variablen nach Einrichtung aktiviert werden (Zähler als Typ bei Bezügen und Einspeisung, sonst Standard Aggegation). 
Der Abrufinterval definiert wie oft Daten abgerufen werden sollen.

## Version 1.0 (14.06.2020)
* Abfragen von Stromzählern der Hersteller EMH (2 Tarif/Wärmepumpenzähler) und ESY (Einspeisezähler)
* Bereitstellen von Daten in Variablen

Daten Wärmepumpe: 
* Zählerstand Gesamt
* Zählerstand Hauptzeit/Nebenzeit

Daten Einspeisezähler: 
* Zählerstand Bezug/Einspeisung
* Verbrauch Aktuell 
* Verbrauch Phase 1-3
* Spannung Phase 1-3

## Version 1.01 (30.06.2020)
* Abfragen von Gaszähler
* Erweiterte Variablen von EMH Zähler
* CURL Timeout hinzugefügt

## Version 2.0 (10.07.2020)
* Komplett neuer Aufbau mit einem Smartmeter pro Komponente
* Einrichtung durch manuelles setzen der UID pro Smartmeter (wird zur Verfügung gestellt)
* Berechnung der Kosten oder Erlöse (ACHTUNG - Bei Kosten und Ertragsberechnung werden die Variablen automatisch Archiviert - für die Auswertung)
* Integration von aWATTar für die dynamische Berechnung von Stromkosten

## Version 2.1 (30.12.2020)
* Überarbeitung der Kostenberechnung (generische Ansatz)
* Unterstützung von Tibber als Stromlieferanten neben aWATTar
* Neues Variablen Profil für Stromkosten mit 4 Nachkommastellen
* Update Konfigurations UI
* Hoffentlich jetzt kompatibel mit dem Module Store obwohl die Archivierung immer noch automatisch aktivert wird

## Version 2.11 (25.01.2021)
* Erweiterung für weitere ESY Zähler (vorheriger Fehler es wurde Spannung und Leistung der Phasen nicht angezeigt)
* Lokalisierungsupdate

## Version 2.12 (10.04.2021)
* Fix Phase 2 Leistung wurde falsch ausgewertet

## Version 2.13 (01.09.2021)
* Fix um Fehler beim Abruf von Tibber abzufangen

## Version 2.2 (04.01.2022)
* Unterstützung für OBIS 1.25 bei EMH SmartMeter
* Methode zur Erkennung von Zählern geändert - jetzt basierend auf verfügbaren Feldwerten

Sollten weitere Zähler benötigt werden bitte im Forum https://www.symcon.de/forum/threads/43805-Modul-Discovergy-Smartmeter-die-zweite-f%C3%BCr-Module-Store  posten. Für den Einbau benötige ich 'manufacturerId' und evtl. ein Beispiel JSON. Bitte melden.
