# Discovergy Smartmeter
This modules allows to download data from smartmeters connected to the the Discovergy Portal/System (incl. support for OEM versions like ComMetering).

## Konfiguration 1.01
To use the modul an account with Discovergy (or OEM like ComMetering) is required next to a smartmeter connected to their portal.Based on the login information all conntected meters will be downloaded and readings will be made available. 
To analyze the downloaded data, it is required to turn on the archiving function within the created variables (Count for Consumption - others can be standard). 
The interval determins how often data should be downloaded. 

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
