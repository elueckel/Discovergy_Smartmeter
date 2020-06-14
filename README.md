# Discovergy Smartmeter
Das Modul ermöglicht das Abfragen von Daten aus dem Discovergy Portal. Getestet wurde es mit dem OEM von ComMetering, wobei es mit allen Integrationen funktionieren sollte.

## Version 1.0 (14.06.2020)
* Abfragen von Stromzählern der Hersteller EMH (2 Tarif/Wärmepumpenzähler) und ESY (Einspeisezähler)
* Bereitstellen von Daten in Variablen

Daten Wärmepumpe: 
* Zählerstand Gesamt
* Zählerstand Hauptzeit/Nebenzeit

Daten Einspeisezähler: 
* Zählerstand Bezug/Einspeisung
* Verbrauch Aktuell 
* Verbruach Phase 1-3
* Spannung Phase 1-3

Sollten weitere Zähler benötigt werden bitte hier posten. Für den Einbau benötige ich 'manufacturerId' und evtl. ein Beispiel JSON. Bitte melden. Ich denke für einen Gaszähler wäre es noch spannend.

Warum das Modul obwohl es schon eines gibt? Weil das andere Modul leider nicht weiter entwickelt wird und z.B. der Mehrtarifzähler nicht unterstützt wird. 