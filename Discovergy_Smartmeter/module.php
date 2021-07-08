<?php

if (!defined('vtBoolean')) {
    define('vtBoolean', 0);
    define('vtInteger', 1);
    define('vtFloat', 2);
    define('vtString', 3);
    define('vtArray', 8);
    define('vtObject', 9);
}


	class Discovergy_Smartmeter extends IPSModule
	
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			//Properties

			$this->RegisterPropertyString("UserName","");
			$this->RegisterPropertyString("Password","");
			$this->RegisterPropertyInteger("TimerQueryMeter", 0);
			$this->RegisterPropertyBoolean("Debug", 0);

			$this->RegisterPropertyBoolean("ConsumptionMain", 0);
			$this->RegisterPropertyInteger("CostCalculationMethod",0);
			$this->RegisterPropertyBoolean("aWATTarCalculateConsumptionMain", 0);
			$this->RegisterPropertyBoolean("ConsumptionSecondary", 0);
			$this->RegisterPropertyBoolean("Sale", 0);
			$this->RegisterPropertyString("BasePrice","0.1996");
			
			$this->RegisterPropertyBoolean("EarningsCalculation",0);

			$this->RegisterPropertyString("TibberAPIKey","");

			$this->RegisterPropertyBoolean("ConsumptionGas", 0);
			
			$this->RegisterPropertyInteger("TimerCostCalculator", 0);

			$this->RegisterPropertyString("SmartmeterUID","");

			if (IPS_VariableProfileExists("DSM.Watt") == false) {
				IPS_CreateVariableProfile("DSM.Watt", 2);
				IPS_SetVariableProfileIcon("DSM.Watt", "Electricity");
				IPS_SetVariableProfileDigits("DSM.Watt", 2);
				IPS_SetVariableProfileText("DSM.Watt", "", " W");
			}

			if (IPS_VariableProfileExists("DSM.WattK") == false) {
				IPS_CreateVariableProfile("DSM.WattK", 2);
				IPS_SetVariableProfileIcon("DSM.WattK", "Electricity");
				IPS_SetVariableProfileDigits("DSM.WattK", 2);
				IPS_SetVariableProfileText("DSM.WattK", "", " kWh");
			}

			if (IPS_VariableProfileExists("DSM.Euro4Digits") == false) {
				IPS_CreateVariableProfile("DSM.Euro4Digits", 2);
				IPS_SetVariableProfileIcon("DSM.Euro4Digits", "Euro");
				IPS_SetVariableProfileDigits("DSM.Euro4Digits", 4);
				IPS_SetVariableProfileText("DSM.Euro4Digits", "", " €");
			}

			

			if (IPS_VariableProfileExists("DSM.CostCalculationMethod") == false){
					IPS_CreateVariableProfile("DSM.CostCalculationMethod", 1);
					IPS_SetVariableProfileIcon("DSM.Watt", "Euro");
					IPS_SetVariableProfileAssociation("DSM.CostCalculationMethod", 0,  $this->Translate('No Cost Calculation'), "",-1);
					IPS_SetVariableProfileAssociation("DSM.CostCalculationMethod", 1, $this->Translate('Manual - Configured in Object Tree'), "",-1);
					IPS_SetVariableProfileAssociation("DSM.CostCalculationMethod", 2, $this->Translate('Automatic - aWATTar'), "",-1);
					IPS_SetVariableProfileAssociation("DSM.CostCalculationMethod", 3, $this->Translate('Automatic - Tibber'), "",-1);
			}
			
			//Component sets timer, but default is OFF
			$this->RegisterTimer("GetMeterReading",0,"DSM_GetMeterReading(\$_IPS['TARGET']);");
			$this->RegisterTimer("Query Energy Cost Hourly",0,"DSM_QueryEnergyCostHourly(\$_IPS['TARGET']);");
			$this->RegisterTimer("CalculateCosts",0,"DSM_CalculateCosts(\$_IPS['TARGET']);");
					
		}
	
	public function ApplyChanges()
	{
			
		//Never delete this line!
		parent::ApplyChanges();
								
		//Timers Update - if greater than 0 = On
		
		$TimerMS = $this->ReadPropertyInteger("TimerQueryMeter") * 1000;
		$this->SetTimerInterval("GetMeterReading",$TimerMS);

		$TimerMin = $this->ReadPropertyInteger("TimerCostCalculator") * 1000 * 60;
		$this->SetTimerInterval("CalculateCosts",$TimerMin);
		
		$vpos = 15;	
		
		$username = $this->ReadPropertyString("UserName");
		$password = $this->ReadPropertyString("Password");
		$SmartmeterUID = $this->ReadPropertyString("SmartmeterUID");
		$ConsumptionMain = $this->ReadPropertyBoolean("ConsumptionMain");
		$CostCalculationMethod = $this->ReadPropertyInteger("CostCalculationMethod");
		$EarningsCalculation = $this->ReadPropertyBoolean("EarningsCalculation");
		$ConsumptionSecondary = $this->ReadPropertyBoolean("ConsumptionSecondary");
		$Sale = $this->ReadPropertyBoolean("Sale");
		$ConsumptionGas = $this->ReadPropertyBoolean("ConsumptionGas");

		if (($username !== "") AND ($password !== "") AND ($SmartmeterUID !== "")) {
			
			$curl = curl_init('https://api.discovergy.com/public/v1/meters');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5);
			curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

			$json = curl_exec($curl);

			//echo $json,"\n"; //An dieser Stelle kann man alle Werte ausgeben - zur Kontrolle

			$data = json_decode($json);

			$i = 0;
			$vpos = 10;

			foreach ($data as $meter) {

				$i++;
				$meterid = $meter->meterId;
				if ($SmartmeterUID == $meterid) {

					$meterid = $meter->meterId;
					$manufacturerId = $meter->manufacturerId;
					$serialnumber = $meter->serialNumber;
					$fullSerialNumber = $meter->fullSerialNumber;
					$locationStreet = $meter->location->street;
					$locationStreetNumber = $meter->location->streetNumber;
					$locationzip = $meter->location->zip;
					$locationCity = $meter->location->city;
					$locationCountry = $meter->location->country;
					$meterlocation = $locationStreet." ".$locationStreetNumber." ".$locationzip." ".$locationCity." ".$locationCountry;

					$this->RegisterVariableString("meterlocation", $this->Translate("Meter Location"), "");
					SetValue($this->GetIDForIdent("meterlocation"), $meterlocation);
					$this->RegisterVariableString("fullSerialNumber", $this->Translate("Full Serialnumber"), "");
					SetValue($this->GetIDForIdent("fullSerialNumber"), $fullSerialNumber);
					$this->RegisterVariableString("serialnumber", $this->Translate("Serialnumber"), "");
					SetValue($this->GetIDForIdent("serialnumber"), $serialnumber);
					$this->RegisterVariableString("meterID", $this->Translate("Meter UID"), "");
					SetValue($this->GetIDForIdent("meterID"), rtrim($meterid,""));
					$this->RegisterVariableString("manufacturerId", $this->Translate("Manufacturer ID"), "");
					SetValue($this->GetIDForIdent("manufacturerId"), $manufacturerId);				

					if ($manufacturerId == "ESY") {
						$this->RegisterVariableFloat("energy", $this->Translate('Energy Bought'), "DSM.WattK");
						$this->RegisterVariableFloat("energyout", $this->Translate('Energy Sold'), "DSM.WattK");
						$this->RegisterVariableFloat("power", $this->Translate('Current Power'), "~Watt.14490");
						$this->RegisterVariableFloat("phase1", $this->Translate('Phase 1'), "DSM.Watt");
						$this->RegisterVariableFloat("phase2", $this->Translate('Phase 2'), "DSM.Watt");
						$this->RegisterVariableFloat("phase3", $this->Translate('Phase 3'), "DSM.Watt");
						$this->RegisterVariableFloat("voltage1", $this->Translate('Voltage Phase 1'), "~Volt");
						$this->RegisterVariableFloat("voltage2", $this->Translate('Voltage Phase 2'), "~Volt");
						$this->RegisterVariableFloat("voltage3", $this->Translate('Voltage Phase 3'), "~Volt");
						if ($CostCalculationMethod > 0) {
							$this->RegisterVariableFloat("CostEnergykWh", $this->Translate('Cost per kwH'), "~Euro");
							$this->RegisterVariableFloat("CalculatedCost", $this->Translate('Calculated Cost HT'), "~Euro");
							$this->RegisterVariableInteger("CostCalculationMethod", $this->Translate('Cost Calculation Method'),"DSM.CostCalculationMethod");
							SetValue($this->GetIDForIdent("CostCalculationMethod"), 0);
						}
						if ($EarningsCalculation == true) {
							$this->RegisterVariableFloat("CompensationEnergykWh", $this->Translate('Compensation per kWh'), "~Euro");
							$this->RegisterVariableFloat("CalculatedEarnings", $this->Translate('Calculated Earnings'), "~Euro");
							$energyoutID = $this->GetIDForIdent('energyout');
							$CalculatedEarningsID = $this->GetIDForIdent('CalculatedEarnings');
						}			
					}
			
					else if ($manufacturerId == "EMH") {
						$this->RegisterVariableFloat("effective_power_complete", $this->Translate('Effective Power Complete'), "DSM.WattK");
						$this->RegisterVariableFloat("effective_power_main", $this->Translate('Effective Power Main Time'), "DSM.WattK");
						$this->RegisterVariableFloat("effective_power_secondary", $this->Translate('Effective Power Secondary Time'), "DSM.WattK");
						$this->RegisterVariableFloat("sold_power_complete", $this->Translate('Sold Power Complete'), "DSM.WattK");
						$this->RegisterVariableFloat("sold_power_main", $this->Translate('Sold Power Main Time'), "DSM.WattK");
						$this->RegisterVariableFloat("sold_power_secondary", $this->Translate('Sold Power Secondary Time'), "DSM.WattK");
						if ($CostCalculationMethod > 0) {
							$this->RegisterVariableFloat("CostEnergykWh", $this->Translate('Cost per kWh HT'), "~Euro");
							$this->RegisterVariableFloat("CalculatedCost", $this->Translate('Calculated Cost HT'), "~Euro");
							$this->RegisterVariableInteger("CostCalculationMethod", $this->Translate('Cost Calculation Method'),"DSM.CostCalculationMethod");
							SetValue($this->GetIDForIdent("CostCalculationMethod"), 0);
						}

						if ($ConsumptionSecondary == true) {
							$this->RegisterVariableFloat("CostEnergykWhSecondary", $this->Translate('Cost per kWh NT'), "~Euro");
							$this->RegisterVariableFloat("CalculatedCostSecondary", $this->Translate('Calculated Cost NT'), "~Euro");
						}
						if ($EarningsCalculation == true) {
							$this->RegisterVariableFloat("CompensationEnergykWh", $this->Translate('Compensation per kwH'), "~Euro");
							$this->RegisterVariableFloat("CalculatedEarnings", $this->Translate('Calculated Earnings'), "~Euro");
							$sold_power_mainID = $this->GetIDForIdent('sold_power_main');
							$CalculatedEarningsID = $this->GetIDForIdent('CalculatedEarnings');
						}					
					}	

					else if ($manufacturerId == "ELS") {
						$this->RegisterVariableFloat("gas_usage", $this->Translate('Gas Usage'), "~Gas");
						if ($ConsumptionGas == true) {
							$this->RegisterVariableFloat("CostEnergym3", $this->Translate('Cost per m3'), "~Euro");
							$this->RegisterVariableFloat("CalculatedCost", $this->Translate('Calculated Cost Gas'), "~Euro");
							$gas_usageID = $this->GetIDForIdent('gas_usageID');
							$CalculatedCostID = $this->GetIDForIdent('CalculatedCost');
						}
					}
				}
			}

		}
		
		//Set hourly timer to get aWATTar or Tibber data 

		

		if ($CostCalculationMethod > 1) {
			$this->QueryEnergyCostHourly(); // get current data

			$this->SetTimerInterval("Query Energy Cost Hourly",3600000);
			$CurrentTimer = $this->GetTimerInterval("Query Energy Cost Hourly");
			$now = new DateTime();
			$target = new DateTime();
			$now->getTimestamp();
			$nextHour = (intval($now->format('H'))+1) % 24;
			$target->setTime($nextHour, 00, 0);
			$diff = $target->getTimestamp() - $now->getTimestamp();
			$EvaTimer = $diff * 1000;
			$this->SetTimerInterval('Query Energy Cost Hourly', $EvaTimer);
		}
		else if ($CostCalculationMethod < 2) {
			$this->SetTimerInterval("Query Energy Cost Hourly",0);
		}
			
	}

	public function TurnArchivingOn() {

		$archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
		$manufacturerId = GetValue($this->GetIDForIdent("manufacturerId"));
		$CostCalculationMethod = $this->ReadPropertyInteger("CostCalculationMethod");
		$ConsumptionMain = $this->ReadPropertyBoolean("ConsumptionMain");
		$ConsumptionSecondary = $this->ReadPropertyBoolean("ConsumptionSecondary");
		$EarningsCalculation = $this->ReadPropertyBoolean("EarningsCalculation");
		$ConsumptionGas = $this->ReadPropertyBoolean("ConsumptionGas");

		If ($CostCalculationMethod > 0) {
		
			if ($manufacturerId == "ESY") {
			
				if ($ConsumptionMain == true) {
					$energyID = $this->GetIDForIdent('energy');
					$CalculatedCostID = $this->GetIDForIdent('CalculatedCost');
					AC_SetLoggingStatus($archiveID, $energyID, true);
					AC_SetAggregationType($archiveID, $energyID, 1);
					AC_SetLoggingStatus($archiveID, $CalculatedCostID, true);
					AC_SetAggregationType($archiveID, $CalculatedCostID, 1);
					IPS_ApplyChanges($archiveID);
				}
				if ($EarningsCalculation == true) {
					$energyoutID = $this->GetIDForIdent('energyout');
					$CalculatedEarningsID = $this->GetIDForIdent('CalculatedEarnings');
					AC_SetLoggingStatus($archiveID, $energyoutID, true);
					AC_SetAggregationType($archiveID, $energyoutID, 1);
					AC_SetLoggingStatus($archiveID, $CalculatedEarningsID, true);
					AC_SetAggregationType($archiveID, $CalculatedEarningsID, 1);
					IPS_ApplyChanges($archiveID);
				}
			}	

			else if ($manufacturerId == "EMH") {
				
				if ($ConsumptionMain == true) {
					$effective_power_mainID = $this->GetIDForIdent('effective_power_main');
					$CalculatedCostID = $this->GetIDForIdent('CalculatedCost');
					AC_SetLoggingStatus($archiveID, $effective_power_mainID, true);
					AC_SetAggregationType($archiveID, $effective_power_mainID, 1);
					AC_SetLoggingStatus($archiveID, $CalculatedCostID, true);
					AC_SetAggregationType($archiveID, $CalculatedCostID, 1);
					IPS_ApplyChanges($archiveID);
				}

				if ($ConsumptionSecondary == true) {
					$effective_power_secondaryID = $this->GetIDForIdent('effective_power_secondary');
					$CalculatedCostSecondaryID = $this->GetIDForIdent('CalculatedCostSecondary');
					AC_SetLoggingStatus($archiveID, $effective_power_secondaryID, true);
					AC_SetAggregationType($archiveID, $effective_power_secondaryID, 1);
					AC_SetLoggingStatus($archiveID, $CalculatedCostSecondaryID, true);
					AC_SetAggregationType($archiveID, $CalculatedCostSecondaryID, 1);
					IPS_ApplyChanges($archiveID);
				}
				if ($EarningsCalculation == true) {
					$sold_power_mainID = $this->GetIDForIdent('sold_power_main');
					$CalculatedEarningsID = $this->GetIDForIdent('CalculatedEarnings');
					AC_SetLoggingStatus($archiveID, $sold_power_mainID, true);
					AC_SetAggregationType($archiveID, $sold_power_mainID, 1);
					AC_SetLoggingStatus($archiveID, $CalculatedEarningsID, true);
					AC_SetAggregationType($archiveID, $CalculatedEarningsID, 1);
					IPS_ApplyChanges($archiveID);
				}					
			}
		}
		
		if ($ConsumptionGas == 1 AND $manufacturerId == "ELS") {
			$gas_usageID = $this->GetIDForIdent('gas_usageID');
			$CalculatedCostID = $this->GetIDForIdent('CalculatedCost');
			AC_SetLoggingStatus($archiveID, $gas_usageID, true);
			AC_SetAggregationType($archiveID, $gas_usageID, 1);
			AC_SetLoggingStatus($archiveID, $CalculatedCostID, true);
			AC_SetAggregationType($archiveID, $CalculatedCostID, 1);
			IPS_ApplyChanges($archiveID);
		}

	}

		
	public function GetMeterReading() {

		$username = $this->ReadPropertyString("UserName");
		$password = $this->ReadPropertyString("Password");
		
		$meterid = GetValue($this->GetIDForIdent('meterID'));
		$manufacturerId = GetValue($this->GetIDForIdent('manufacturerId'));

		$curl = curl_init('https://api.discovergy.com/public/v1/last_reading?meterId='.$meterid);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

		$json = curl_exec($curl);

		//echo $json,"\n"; //An dieser Stelle kann man alle Werte ausgeben - zur Kontrolle

		$data = json_decode($json);

		if ($data != NULL) {
			
			if ($manufacturerId == "ESY") {
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('*********************************************************************'),0);
				$energy_raw = $data->values->energy;
				$energy = $energy_raw / 10000000000;
				SetValue($this->GetIDForIdent('energy'), $energy);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Energy Consumed: ').round($energy,3)." kWh",0);

				$energyout_raw = $data->values->energyOut;
				$energyout = $energyout_raw / 10000000000;
				SetValue($this->GetIDForIdent('energyout'), $energyout);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Energy Sold: ').round($energyout,3)." kWh",0);

				$power_raw = $data->values->power;
				$power = $power_raw / 1000;
				SetValue($this->GetIDForIdent('power'), $power);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Current Consumption: ').round($power,3)." kW",0);

				if (isset ($data->values->power1)) {
					$phase1_raw = $data->values->power1;
					$phase1 = $phase1_raw / 1000;
					SetValue($this->GetIDForIdent('phase1'), $phase1);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 1 Consumption: ').round($phase1,3)." W",0);
				}
				else if (isset ($data->values->phase1Power)) {
					$phase1_raw = $data->values->phase1Power;
					$phase1 = $phase1_raw / 1000;
					SetValue($this->GetIDForIdent('phase1'), $phase1);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 1 Consumption: ').round($phase1,3)." W",0);
				}

				if (isset ($data->values->power2)) {
					$phase2_raw = $data->values->power2;
					$phase2 = $phase2_raw / 1000;
					SetValue($this->GetIDForIdent('phase2'), $phase2);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 2 Consumption: ').round($phase2,3)." W",0);
				}
				else if (isset ($data->values->phase2Power)) {
					$phase2_raw = $data->values->phase2Power;
					$phase2 = $phase2_raw / 1000;
					SetValue($this->GetIDForIdent('phase2'), $phase2);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 2 Consumption: ').round($phase2,3)." W",0);
				}

				if (isset ($data->values->power3)) {
					$phase3_raw = $data->values->power3;
					$phase3 = $phase3_raw / 1000;
					SetValue($this->GetIDForIdent('phase3'), $phase3);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 3 Consumption: ').round($phase3,3)." W",0);
				}
				else if (isset ($data->values->phase3Power)) {
					$phase3_raw = $data->values->phase3Power;
					$phase3 = $phase3_raw / 1000;
					SetValue($this->GetIDForIdent('phase3'), $phase3);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 3 Consumption: ').round($phase3,3)." W",0);
				}

				if (isset ($data->values->voltage1)) {
					$voltage1_raw = $data->values->voltage1;
					$voltage1 = $voltage1_raw / 1000;
					SetValue($this->GetIDForIdent('voltage1'), $voltage1);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 1 Voltage: ').round($voltage1,3)." V",0);
				}
				else if (isset ($data->values->phase1Voltage)) {
					$voltage1_raw = $data->values->phase1Voltage;
					$voltage1 = $voltage1_raw / 1000;
					SetValue($this->GetIDForIdent('voltage1'), $voltage1);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 1 Voltage: ').round($voltage1,3)." V",0);
				}

				if (isset ($data->values->voltage2)) {
					$voltage2_raw = $data->values->voltage2;
					$voltage2 = $voltage2_raw / 1000;
					SetValue($this->GetIDForIdent('voltage2'), $voltage2);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 2 Voltage: ').round($voltage2,3)." V",0);
				}
				else if (isset ($data->values->phase2Voltage)) {
					$voltage2_raw = $data->values->phase2Voltage;
					$voltage2 = $voltage2_raw / 1000;
					SetValue($this->GetIDForIdent('voltage2'), $voltage2);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 2 Voltage: ').round($voltage2,3)." V",0);
				}

				if (isset ($data->values->voltage3)) {
					$voltage3_raw = $data->values->voltage3;
					$voltage3 = $voltage3_raw / 1000;
					SetValue($this->GetIDForIdent('voltage3'), $voltage3);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 3 Voltage: ').round($voltage3,3)." V",0);
				}
				else if (isset ($data->values->phase3Voltage)) {
					$voltage3_raw = $data->values->phase3Voltage;
					$voltage3 = $voltage3_raw / 1000;
					SetValue($this->GetIDForIdent('voltage3'), $voltage3);
					$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 3 Voltage: ').round($voltage3,3)." V",0);
				}
				
			}

			else if ($manufacturerId == "EMH") {
				$this->SendDebug($this->Translate('EMH Meter'),$this->Translate('*********************************************************************'),0);
				$effective_power_complete_raw = $data->values->{'1.8.0'};
				$effective_power_complete = $effective_power_complete_raw / 1000000;
				SetValue($this->GetIDForIdent('effective_power_complete'), $effective_power_complete);
				$this->SendDebug($this->Translate('EMH Meter'),$this->Translate('Effective Power Complete: ').round($effective_power_complete,3)." kWh",0);

				$effective_power_main_raw = $data->values->{'1.8.1'};
				$effective_power_main = $effective_power_main_raw / 1000000;
				SetValue($this->GetIDForIdent('effective_power_main'), $effective_power_main);
				$this->SendDebug($this->Translate('EMH Meter'),$this->Translate('Effective Power Complete HT: ').round($effective_power_main,3)." kWh",0);

				$effective_power_secondary_raw = $data->values->{'1.8.2'};
				$effective_power_secondary = $effective_power_secondary_raw / 1000000;
				SetValue($this->GetIDForIdent('effective_power_secondary'), $effective_power_secondary);
				$this->SendDebug($this->Translate('EMH Meter'),$this->Translate('Effective Power Complete NT: ').round($effective_power_secondary,3)." kWh",0);

				$sold_power_complete_raw = $data->values->{'2.8.0'};
				$sold_power_complete = $sold_power_complete_raw / 1000000;
				SetValue($this->GetIDForIdent('sold_power_complete'), $sold_power_complete);
				$this->SendDebug($this->Translate('EMH Meter'),$this->Translate('Sold Power Complete: ').round($sold_power_complete,3)." kWh",0);

				$sold_power_main_raw = $data->values->{'2.8.1'};
				$sold_power_main = $sold_power_main_raw / 1000000;
				SetValue($this->GetIDForIdent('sold_power_main'), $sold_power_main);
				$this->SendDebug($this->Translate('EMH Meter'),$this->Translate('Sold Power Complete HT: ').round($sold_power_main,3)." kWh",0);

				$sold_power_secondary_raw = $data->values->{'2.8.2'};
				$sold_power_secondary = $sold_power_secondary_raw / 1000000;
				SetValue($this->GetIDForIdent('sold_power_secondary'), $sold_power_secondary);
				$this->SendDebug($this->Translate('EMH Meter'),$this->Translate('Sold Power Complete NT: ').round($sold_power_secondary,3)." kWh",0);
				/*
				$current_power_raw = $data->values->{'1.25'};
				$current_power = $current_power_raw / 1000;
				SetValue($this->GetIDForIdent('current_power'), $current_power);
				*/
			}
			
			else if ($manufacturerId == "ELS") {
				
				$gas_raw = $data->values->volume;
				$gas_usage = $gas_raw / 1000;
				SetValue($this->GetIDForIdent('gas_usage'), $gas_usage);
				$this->SendDebug($this->Translate('GAS Meter'),$this->Translate('*********************************************************************'),0);
				$this->SendDebug($this->Translate('GAS Meter'),$this->Translate('Gas Consumed: ').round($gas_usage,3)." m3",0);
					
			}
		}
		else {
			// no data found;
		}
	}

	public function QueryEnergyCostHourly() {

		$CostCalculationMethod = $this->ReadPropertyInteger("CostCalculationMethod");
		$this->SendDebug($this->Translate('Get current energy price'),$CostCalculationMethod,0);

		if ($CostCalculationMethod < 2) {
			$this->SendDebug($this->Translate('Get current energy price'),$this->Translate('Automatic price calculated is off or set to manual prices. Please select aWATTar or Tibber as providers'),0);
		}

		else if ($CostCalculationMethod == 2) {
			$BasePrice = $this->ReadPropertyString("BasePrice");
			
			$curl = curl_init('https://api-test.awattar.de/v1/optimizer');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

			$json = curl_exec($curl);

			//echo $json,"\n"; //An dieser Stelle kann man alle Werte ausgeben - zur Kontrolle

			$data = json_decode($json);

			$CurrentPriceMWh = $data->data->current->price;
			$CurrentPrice = $CurrentPriceMWh / 1000;
			$this->SendDebug($this->Translate('aWATTar'),$this->Translate('Current cost per kWh: ').$CurrentPrice,0);
			$CostEnergykWh = $BasePrice + ($CurrentPrice * 1.19);
			$this->SendDebug($this->Translate('aWATTar'),$this->Translate('Current cost per kWh incl Base Price: ').$CostEnergykWh,0);

			If ($CostCalculationMethod == 2) {
				SetValue($this->GetIDForIdent('CostEnergykWh'), $CostEnergykWh);
			}

		}

		else if ($CostCalculationMethod == 3) {

			$CostCalculationMethod = $this->ReadPropertyInteger("CostCalculationMethod");
			$TibberAPIKey = $this->ReadPropertyString("TibberAPIKey");

			if ($TibberAPIKey != "") {

				$json = '{"query":"{viewer {homes {currentSubscription {priceInfo {current {total energy tax startsAt }}}}}}"}';

				$curl = curl_init('https://api.tibber.com/v1-beta/gql');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_TIMEOUT, 5);
				curl_setopt($curl, CURLOPT_HTTPHEADER, 
				array('Content-Type: application/json',  
				'Authorization: Bearer '.$TibberAPIKey)); // Demo token
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

				$json = curl_exec($curl);
				$data = json_decode($json);
				
				if ($Current != "") { 

					$current = $data->data->viewer->homes[0];
					/*
					$total = $current->currentSubscription->priceInfo->current->total;
					var_dump($total);

					$energy = $current->currentSubscription->priceInfo->current->energy;
					var_dump($energy);

					$tax = $current->currentSubscription->priceInfo->current->tax;
					var_dump($tax); 
					*/

				
					$CurrentPricekwh = $current->currentSubscription->priceInfo->current->total;
					$this->SendDebug($this->Translate('Tibber'),$this->Translate('Current cost per kWh: ').$CurrentPricekwh,0);
					If ($CostCalculationMethod == 3) {
						SetValue($this->GetIDForIdent('CostEnergykWh'), $CurrentPricekwh);
					}
					
				}
				
			}

			Else {
				$this->SendDebug($this->Translate('Tibber'),$this->Translate('API is empty - please set YOUR Api Key for Tibber'),0);
				echo 'API is empty - please set YOUR Api Key for Tibber';
			}
			
		}

		

	}

	//Future Use 
	public function QueryTibberCostAhead() {

		

		$json = '{"query":"{viewer {homes {currentSubscription {priceInfo {current {total energy tax startsAt }}}}}}"}';

		$curl = curl_init('https://api.tibber.com/v1-beta/gql');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		curl_setopt($curl, CURLOPT_HTTPHEADER, 
		array('Content-Type: application/json',  
		'Authorization: Bearer d1007ead2dc84a2b82f0de19451c5fb22112f7ae11d19bf2bedb224a003ff74a')); // Demo token
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

		$json = curl_exec($curl);
		$data = json_decode($json);

		$current = $data->data->viewer->homes[0];
		/*
		$total = $current->currentSubscription->priceInfo->current->total;
		var_dump($total);

		$energy = $current->currentSubscription->priceInfo->current->energy;
		var_dump($energy);

		$tax = $current->currentSubscription->priceInfo->current->tax;
		var_dump($tax); 
		*/

		
		$CurrentPricekwh = $current->currentSubscription->priceInfo->current->total[0];
		$this->SendDebug($this->Translate('Tibber'),$this->Translate('Current cost per kWh: ').$CurrentPricekwh,0);

		//SetValue($this->GetIDForIdent('CostEnergykWh'), $CostEnergykWh);
		/*
		if ($EnergyCostArchiveTibberEnabled == 1) {
			//SetValue($this->GetIDForIdent('EnergyCostArchiveTibber'), $CurrentPricekwh);
		}
		*/

	}

	public function CalculateCosts() {
		
		$manufacturerId = GetValue($this->GetIDForIdent('manufacturerId'));
		$CostCalculatorInterval = $this->ReadPropertyInteger("TimerCostCalculator");

		SetValue($this->GetIDForIdent('CostCalculationMethod'),$this->ReadPropertyInteger("CostCalculationMethod")); 

		if ($manufacturerId == "ESY") {

			//Calculate Consumption
			$archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
			$CostEnergykWh = GetValue($this->GetIDForIdent('CostEnergykWh'));
			$Energy = $this->GetIDForIdent('energy'); //Variable where sold energy for ESY meter is stored


			$werte = AC_GetLoggedValues($archiveID, $Energy, strtotime("-".$CostCalculatorInterval." minutes"), time(), 0);
			//var_dump($werte);

			$letzter = array_pop($werte);
			//echo $letzter["Value"];

			$array_count = count($werte);

			if ($array_count > 0) {
				$first = $werte["0"]["Value"];
				//echo "echo ".$first;

				$wert = array_pop($werte);
				$last = $wert["Value"];
				//echo " last ".$last;
				$verbrauch = $first - $last;
				//echo " verbrauch ".$verbrauch;
				$kosten = $verbrauch * $CostEnergykWh;
				//echo " kosten ".$kosten;
				SetValue($this->GetIDForIdent('CalculatedCost'), $kosten);
				$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Datapoints found: ').$array_count.$this->Translate(' / Power Consumed: ').round($verbrauch,3)." kWh",0);
				$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current cost per kWh: ').round($CostEnergykWh, 3)." €".$this->Translate(' / Calculated Cost: ').round($kosten, 3)." €",0);
			}

			// Caculate Feed-In Revenue 
			
			$EarningsCalculation = $this->ReadPropertyBoolean("EarningsCalculation");

			if ($EarningsCalculation == 1) {
				$CompensationEnergykWh = GetValue($this->GetIDForIdent('CompensationEnergykWh'));
				$energyout = $this->GetIDForIdent('energyout');
				$werte = AC_GetLoggedValues($archiveID, $energyout, strtotime("-".$CostCalculatorInterval." minutes"), time(), 0);
				//var_dump($werte);

				$letzter = array_pop($werte);
				//echo $letzter["Value"];

				$array_count = count($werte);

				if ($array_count > 0) {
					$first = $werte["0"]["Value"];
					//echo "echo ".$first;

					$wert = array_pop($werte);
					$last = $wert["Value"];
					//echo " last ".$last;
					$production = $first - $last;
					//echo " verbrauch ".$verbrauch;
					$earnings = $production * $CompensationEnergykWh;
					//echo " kosten ".$kosten;
					SetValue($this->GetIDForIdent('CalculatedEarnings'), $earnings);
					$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Datapoints found: ').$array_count.$this->Translate(' / Power Generated: ').round($production,3)." kWh",0);
					$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current earning per kWh: ').round($CompensationEnergykWh, 3)." €".$this->Translate(' / Calculated Earnings: ').round($earnings, 3)." €",0);
				}
			}
		}

		else if ($manufacturerId == "EMH") {
			//$this->SendDebug($this->Translate('Cost Calculation'),"EMH Schleife",0);

			//Calculate Consumption
			$archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
			$CostCalculatorInterval = $this->ReadPropertyInteger("TimerCostCalculator");
			$CostEnergykWh = GetValue($this->GetIDForIdent('CostEnergykWh'));

			$Energy = $this->GetIDForIdent('effective_power_main'); //Variable where sold energy for ESY meter is stored
			$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current cost per kWh HT: ').round($CostEnergykWh, 3),0);


			$werte = AC_GetLoggedValues($archiveID, $Energy, strtotime("-".$CostCalculatorInterval." minutes"), time(), 0);
			//var_dump($werte);

			$letzter = array_pop($werte);
			//echo $letzter["Value"];

			$array_count = count($werte);
			//$this->SendDebug($this->Translate('Cost Calculation'),"Array Count".$array_count,0);
			if ($array_count > 0) {
				$first = $werte["0"]["Value"];
				//echo "echo ".$first;

				$wert = array_pop($werte);
				$last = $wert["Value"];
				//echo " last ".$last;
				$verbrauch = $first - $last;
				//echo " verbrauch ".$verbrauch;
				$kosten = $verbrauch * $CostEnergykWh;
				//echo " kosten ".$kosten;
				SetValue($this->GetIDForIdent('CalculatedCost'), $kosten);
				$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current cost per kWh HT: ').round($CostEnergykWh, 3),0);
				$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Datapoints found: ').$array_count.$this->Translate(' / Power Consumed HT: ').round($verbrauch,3)." kWh",0);
				$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current cost per kWh HT: ').round($CostEnergykWh, 3)." €".$this->Translate(' / Calculated Cost: ').round($kosten, 3)." €",0);
			}
			else {
				SetValue($this->GetIDForIdent('CalculatedCost'), 0);
			}

			// Caculate Feed-In Revenue 
			
			$ConsumptionSecondary = $this->ReadPropertyBoolean("ConsumptionSecondary");
			
			if ($ConsumptionSecondary == 1) {
				$CostCalculatorInterval = $this->ReadPropertyInteger("TimerCostCalculator");
				$CostEnergykWhSecondary = GetValue($this->GetIDForIdent('CostEnergykWhSecondary'));

				$Energy = $this->GetIDForIdent('effective_power_secondary'); //Variable where sold energy for ESY meter is stored
				$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current cost per kWh NT: ').round($CostEnergykWhSecondary, 3),0);


				$werte = AC_GetLoggedValues($archiveID, $Energy, strtotime("-".$CostCalculatorInterval." minutes"), time(), 0);
				//var_dump($werte);
	
				$letzter = array_pop($werte);
				//echo $letzter["Value"];
	
				$array_count = count($werte);
	
				if ($array_count > 0) {
					$first = $werte["0"]["Value"];
					//echo "echo ".$first;
	
					$wert = array_pop($werte);
					$last = $wert["Value"];
					//echo " last ".$last;
					$verbrauch = $first - $last;
					//echo " verbrauch ".$verbrauch;
					$kosten = $verbrauch * $CostEnergykWhSecondary;
					//echo " kosten ".$kosten;
					SetValue($this->GetIDForIdent('CalculatedCostSecondary'), $kosten);
					$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current cost per kWh NT: ').round($CostEnergykWhSecondary, 3),0);
					$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Datapoints found: ').$array_count.$this->Translate(' / Power Consumed NT: ').round($verbrauch,3)." kWh",0);
					$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current cost per kWh NT: ').round($CostEnergykWhSecondary, 3)." €".$this->Translate(' / Calculated Cost NT: ').round($kosten, 3)." €",0);
				}
				else {
					SetValue($this->GetIDForIdent('CalculatedCostSecondary'), 0);
				}
			}


			$EarningsCalculation = $this->ReadPropertyBoolean("EarningsCalculation");

			if ($EarningsCalculation == 1) {
				$CompensationEnergykWh = GetValue($this->GetIDForIdent('CompensationEnergykWh'));
				$energyout = $this->GetIDForIdent('sold_power_main');
				$werte = AC_GetLoggedValues($archiveID, $energyout, strtotime("-".$CostCalculatorInterval." minutes"), time(), 0);
				//var_dump($werte);

				$letzter = array_pop($werte);
				//echo $letzter["Value"];

				$array_count = count($werte);

				if ($array_count > 0) {
					$first = $werte["0"]["Value"];
					//echo "echo ".$first;

					$wert = array_pop($werte);
					$last = $wert["Value"];
					//echo " last ".$last;
					$production = $first - $last;
					//echo " verbrauch ".$verbrauch;
					$earnings = $production * $CompensationEnergykWh;
					//echo " kosten ".$kosten;
					SetValue($this->GetIDForIdent('CalculatedEarnings'), $earnings);
					$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Datapoints found: ').$array_count.$this->Translate(' / Power Generated: ').round($production,3)." kWh",0);
					$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current earning per kWh: ').round($CompensationEnergykWh, 3)." €".$this->Translate(' / Calculated Earnings: ').round($earnings, 3)." €",0);
				}
			}
		}

		else if ($manufacturerId == "ELS") {

			//Calculate Consumption
			$archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
			$CostCalculatorInterval = $this->ReadPropertyInteger("CostCalculator");
			$CostEnergym3 = GetValue($this->GetIDForIdent('CostEnergym3'));

			$Energy = $this->GetIDForIdent('gas_usage'); //Variable where sold energy for ESY meter is stored

			$werte = AC_GetLoggedValues($archiveID, $Energy, strtotime("-".$CostCalculatorInterval." minutes"), time(), 0);
			//var_dump($werte);

			$letzter = array_pop($werte);
			//echo $letzter["Value"];

			$array_count = count($werte);

			if ($array_count > 0) {
				$first = $werte["0"]["Value"];
				//echo "echo ".$first;

				$wert = array_pop($werte);
				$last = $wert["Value"];
				//echo " last ".$last;
				$verbrauch = $first - $last;
				//echo " verbrauch ".$verbrauch;
				$kosten = $verbrauch * $CostEnergym3;
				//echo " kosten ".$kosten;
				SetValue($this->GetIDForIdent('CalculatedCost'), $kosten);
				$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Datapoints found: ').$array_count.$this->Translate(' / Gas Consumed: ').round($verbrauch,3)." kWh",0);
				$this->SendDebug($this->Translate('Cost Calculation'),$this->Translate('Current cost per m3: ').round($CostEnergym3, 3)." €".$this->Translate(' / Calculated Cost: ').round($kosten, 3)." €",0);
			}
		}

	}



	public function GetMeters() {
		$vpos = 15;			
		

		$username = $this->ReadPropertyString("UserName");
		$password = $this->ReadPropertyString("Password");

		if (($username !== "") AND ($password !== "")) {
			
			$curl = curl_init('https://api.discovergy.com/public/v1/meters');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5);
			curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

			$json = curl_exec($curl);

			//echo $json,"\n"; //An dieser Stelle kann man alle Werte ausgeben - zur Kontrolle

			$data = json_decode($json);

			$i = 0;
			$vpos = 10;

			foreach ($data as $meter) {

				$i++;

				$meterid = $meter->meterId;
				$manufacturerId = $meter->manufacturerId;
				
				echo "UID ".trim($meterid)." Type ".$manufacturerId." // ";
				
			}
		}	
	}
		
}
