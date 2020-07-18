# Discovergy Smartmeter
This modules allows to download data from smartmeters connected to the the Discovergy Portal/System (incl. support for OEM versions like ComMetering).

## Konfiguration 2.0
1. Install the module 
2. Add the Instance for Discovergy (there are also other ones named by the OEM names like aWATTar, ComMetering etc.)
3. Add the 

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

## Version 1.01 (25.06.2020)
* Reading of Gasmeter in m³
* Enhanced variables for EMH Meter
* Added CURL Timeout

## Version 2.0 (10.07.2020)
* Complete re-write using one instance per meter
* Manuell setup via meter UID (provided by component)
* Calculation of cost and earnings (PV)
* Integration of aWATTar smart tarifs


For other unsupported meters, please post a message into the forum: https://www.symcon.de/forum/threads/43805-Modul-Discovergy-Smartmeter-die-zweite-f%C3%BCr-Module-Store?highlight=smartmeter
