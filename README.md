# Discovergy Smartmeter
Das Modul ermöglicht das Abfragen von Daten aus dem Discovergy Portal. Getestet wurde es mit dem OEM von ComMetering, wobei es mit allen Integrationen funktionieren sollte.

## Konfiguration 1.01
Um das Modul nutzen zu können ist ein Account bei Discovery oder einer deren OEMs (z.B. ComMetering) und ein hier verbundener Smartmeter von nöten. Anhand der Login Daten werden alle Smartmeter in Symcon eingerichtet und stehene dort mit den unten genannten Informationen zur Verfügung. 
Um langfristig Daten auszuwerten, sollte die Archivfunktion für die Variablen nach Einrichtung aktiviert werden (Zähler bei Bezügen und Einspeisung, sonst Standard). 
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

## Version 1.01 (25.06.2020) - Release im Module Store
* Abfragen von Gaszähler
* Erweiterte Variablen von EMH Zähler
* CURL Timeout hinzugefügt

Sollten weitere Zähler benötigt werden bitte hier posten. Für den Einbau benötige ich 'manufacturerId' und evtl. ein Beispiel JSON. Bitte melden. Ich denke für einen Gaszähler wäre es noch spannend.

Warum das Modul obwohl es schon eines gibt? Weil das andere Modul leider nicht weiter entwickelt wird und z.B. der Mehrtarifzähler nicht unterstützt wird. 
