# Discovergy Smartmeter
This modules allows to download data from smartmeters connected to the the Discovergy Portal/System (incl. support for OEM versions like ComMetering).

1. Install the module 
2. Add the Instance for Discovergy (there are also other ones named by the OEM names like aWATTar, ComMetering etc.)
3. Once added enter your username and password - than click on Get Msart Meters
4. Based on the login the UIDs of connected meters is displayed - please add this to your the corresponding field (without blanks)
5. Set the interval you want to use to receive data.
6. In the object tree variables will be created based on the meter type

If you like to calculate cost or earnings for feed in (pv) than open the section for the meter and enable it
* to set the cost or earnings per kWh or m3 (Gas) you can do so once the Funktion ist activated in the object tree using a "dot" as a seperator 0.26€
* If you use aWATTar as a energy supplier, active it and the cost will calculated hourly based on the current price. The base price is set in the module.

In general different meters provide different data points. Please also check the Discovergy portal for more information.
 

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
* Calculation of cost and earnings (PV) - WARNING ... if enabled variables for consumption and earnings will be automatically archived.
* Integration of aWATTar smart tarifs

For other unsupported meters, please post a message into the forum: https://www.symcon.de/forum/threads/43805-Modul-Discovergy-Smartmeter-die-zweite-f%C3%BCr-Module-Store?highlight=smartmeter