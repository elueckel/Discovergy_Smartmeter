# Discovergy Smartmeter
This modules allows to download data from smartmeters connected to the the Discovergy Portal/System (incl. support for OEM versions like ComMetering).

## Konfiguration 1.01
To use the modul an account with Discovergy (or OEM like ComMetering) is required next to a smartmeter connected to their portal. Based on the login information all conntected meters will be added and readings will be made available as outlined below in the version information.
To analyze the downloaded data, it is required to turn on the archiving function within the created variables (Count for Consumption - others can be Standard as the aggregetation type). 
The interval determins how often data should be downloaded from the Discovergy Portal.

## Version 1.0 (14.06.2020)
* Query Energy Smartmeters from vendors like EMH and ESY
* Provide Daten in Varaibles

### Data of EMH Meter
* Complete Count purchased or sold energy
* Maint and secondary times

### Data of ESY Meter
* Reading of purchased and sold energy
* Current Consumption
* Consumption Phase 1-3
* Voltage Phase 1-3

## Version 1.01 (25.06.2020) - Release in Module Store
* Reading of Gasmeter in m³
* Enhanced variables for EMH Meter
* Added CURL Timeout

For other unsupported meters, please post a message into the forum: https://www.symcon.de/forum/threads/43805-Modul-Discovergy-Smartmeter-die-zweite-f%C3%BCr-Module-Store?highlight=smartmeter
