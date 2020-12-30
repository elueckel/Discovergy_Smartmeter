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

Sollten weitere Zähler benötigt werden bitte im Forum https://www.symcon.de/forum/threads/43805-Modul-Discovergy-Smartmeter-die-zweite-f%C3%BCr-Module-Store  posten. Für den Einbau benötige ich 'manufacturerId' und evtl. ein Beispiel JSON. Bitte melden.