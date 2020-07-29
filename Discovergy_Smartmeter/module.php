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
			$this->RegisterPropertyInteger("Timer", 0);
			$this->RegisterPropertyBoolean("Debug", 0);

			$this->RegisterPropertyBoolean("ConsumptionMain", 0);
			$this->RegisterPropertyBoolean("ConsumptionMainaWATTar", 0);
			$this->RegisterPropertyBoolean("ConsumptionSecondary", 0);
			$this->RegisterPropertyBoolean("Sale", 0);
			$this->RegisterPropertyString("BasePrice","0.1996");
			//$this->RegisterPropertyString("SellPrice","0.16");
			$this->RegisterPropertyBoolean("EarningsCalculation",0);

			$this->RegisterPropertyBoolean("ConsumptionGas", 0);
			//$this->RegisterPropertyString("CostEnergym3", 0);
			

			$this->RegisterPropertyInteger("CostCalculator", 0);

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
			
			//Component sets timer, but default is OFF
			$this->RegisterTimer("GetMeterReading",0,"DSM_GetMeterReading(\$_IPS['TARGET']);");
			$this->RegisterTimer("QueryAWATTAR",0,"DSM_QueryAWATTAR(\$_IPS['TARGET']);");
			$this->RegisterTimer("CalculateCosts",0,"DSM_CalculateCosts(\$_IPS['TARGET']);");

			$ConsumptionMainaWATTar = $this->ReadPropertyBoolean("ConsumptionMainaWATTar");
			if ($ConsumptionMainaWATTar == 1) {
				$this->QueryAWATTAR(); // get current data
	
				$this->SetTimerInterval("QueryAWATTAR",3600000);
				$CurrentTimer = $this->GetTimerInterval("QueryAWATTAR");
				//if ($CurrentTimer == 0) {
					$now = new DateTime();
					$target = new DateTime();
					$now->getTimestamp();
					$nextHour = (intval($now->format('H'))+1) % 24;
					$target->setTime($nextHour, 00, 0);
					$diff = $target->getTimestamp() - $now->getTimestamp();
					$EvaTimer = $diff * 1000;
					$this->SetTimerInterval('QueryAWATTAR', $EvaTimer);
					//$this->SetTimerInterval("QueryAWATTAR",3600000);
				//}
			}
			else if ($ConsumptionMainaWATTar == 0) {
				$this->SetTimerInterval("QueryAWATTAR",0);
			}
					
		}
	
	public function ApplyChanges()
	{
			
		//Never delete this line!
		parent::ApplyChanges();
		
								
		//Timers Update - if greater than 0 = On
		
		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("GetMeterReading",$TimerMS);

		$TimerMin = $this->ReadPropertyInteger("CostCalculator") * 1000 * 60;
		$this->SetTimerInterval("CalculateCosts",$TimerMin);
		
		$archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
		
		$vpos = 15;	
		
		$username = $this->ReadPropertyString("UserName");
		$password = $this->ReadPropertyString("Password");
		$SmartmeterUID = $this->ReadPropertyString("SmartmeterUID");
		$ConsumptionMain = $this->ReadPropertyBoolean("ConsumptionMain");
		$ConsumptionMainaWATTar = $this->ReadPropertyBoolean("ConsumptionMainaWATTar");
		$EarningsCalculation = $this->ReadPropertyBoolean("EarningsCalculation");
		$ConsumptionSecondary = $this->ReadPropertyBoolean("ConsumptionSecondary");
		$Sale = $this->ReadPropertyBoolean("Sale");

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

					$this->RegisterVariableString("meterlocation", "Meter Location", "");
					SetValue($this->GetIDForIdent("meterlocation"), $meterlocation);
					$this->RegisterVariableString("fullSerialNumber", "Full Serialnumber", "");
					SetValue($this->GetIDForIdent("fullSerialNumber"), $fullSerialNumber);
					$this->RegisterVariableString("serialnumber", "Serialnumber", "");
					SetValue($this->GetIDForIdent("serialnumber"), $serialnumber);
					$this->RegisterVariableString("meterID", "Meter UID", "");
					SetValue($this->GetIDForIdent("meterID"), rtrim($meterid,""));
					$this->RegisterVariableString("manufacturerId", "Manufacturer ID", "");
					SetValue($this->GetIDForIdent("manufacturerId"), $manufacturerId);				

					if ($manufacturerId == "ESY") {
						$this->RegisterVariableFloat("energy", $this->Translate('Energy Bought'), "DSM.WattK");
						$this->RegisterVariableFloat("energyout", $this->Translate('Energy Sold'), "DSM.WattK");
						$this->RegisterVariableFloat("power", $this->Translate('Current Power'), "~Watt.14490");
						$this->RegisterVariableFloat("phase1", $this->Translate('Phase 1'), "DSM.WattK");
						$this->RegisterVariableFloat("phase2", $this->Translate('Phase 2'), "DSM.WattK");
						$this->RegisterVariableFloat("phase3", $this->Translate('Phase 3'), "DSM.WattK");
						$this->RegisterVariableFloat("voltage1", $this->Translate('Voltage Phase 1'), "~Volt");
						$this->RegisterVariableFloat("voltage2", $this->Translate('Voltage Phase 2'), "~Volt");
						$this->RegisterVariableFloat("voltage3", $this->Translate('Voltage Phase 3'), "~Volt");
						if ($ConsumptionMain == true) {
							$this->RegisterVariableFloat("CostEnergykWh", $this->Translate('Cost per kwH'), "~Euro");
							$this->RegisterVariableFloat("CalculatedCost", $this->Translate('Calculated Cost HT'), "~Euro");
							$energyID = $this->GetIDForIdent('energy');
							$CalculatedCostID = $this->GetIDForIdent('CalculatedCost');
							AC_SetLoggingStatus($archiveID, $energyID, true);
							AC_SetAggregationType($archiveID, $energyID, 1);
							AC_SetLoggingStatus($archiveID, $CalculatedCostID, true);
							AC_SetAggregationType($archiveID, $CalculatedCostID, 1);
							IPS_ApplyChanges($archiveID);
						}
						if ($EarningsCalculation == true) {
							$this->RegisterVariableFloat("CompensationEnergykWh", $this->Translate('Compensation per kWh'), "~Euro");
							$this->RegisterVariableFloat("CalculatedEarnings", $this->Translate('Calculated Earnings'), "~Euro");
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
						$this->RegisterVariableFloat("effective_power_complete", $this->Translate('Effective Power Complete'), "DSM.WattK");
						$this->RegisterVariableFloat("effective_power_main", $this->Translate('Effective Power Main Time'), "DSM.WattK");
						$this->RegisterVariableFloat("effective_power_secondary", $this->Translate('Effective Power Secondary Time'), "DSM.WattK");
						$this->RegisterVariableFloat("sold_power_complete", $this->Translate('Sold Power Complete'), "DSM.WattK");
						$this->RegisterVariableFloat("sold_power_main", $this->Translate('Sold Power Main Time'), "DSM.WattK");
						$this->RegisterVariableFloat("sold_power_secondary", $this->Translate('Sold Power Secondary Time'), "DSM.WattK");
						if ($ConsumptionMain == true) {
							$this->RegisterVariableFloat("CostEnergykWh", $this->Translate('Cost per kWh HT'), "~Euro");
							$this->RegisterVariableFloat("CalculatedCost", $this->Translate('Calculated Cost HT'), "~Euro");
							$effective_power_mainID = $this->GetIDForIdent('effective_power_main');
							$CalculatedCostID = $this->GetIDForIdent('CalculatedCost');
							AC_SetLoggingStatus($archiveID, $effective_power_mainID, true);
							AC_SetAggregationType($archiveID, $effective_power_mainID, 1);
							AC_SetLoggingStatus($archiveID, $CalculatedCostID, true);
							AC_SetAggregationType($archiveID, $CalculatedCostID, 1);
							IPS_ApplyChanges($archiveID);
						}
						if ($ConsumptionSecondary == true) {
							$this->RegisterVariableFloat("CostEnergykWhSecondary", $this->Translate('Cost per kWh NT'), "~Euro");
							$this->RegisterVariableFloat("CalculatedCostSecondary", $this->Translate('Calculated Cost NT'), "~Euro");
							$effective_power_secondaryID = $this->GetIDForIdent('effective_power_secondary');
							$CalculatedCostSecondaryID = $this->GetIDForIdent('CalculatedCostSecondary');
							AC_SetLoggingStatus($archiveID, $effective_power_secondaryID, true);
							AC_SetAggregationType($archiveID, $effective_power_secondaryID, 1);
							AC_SetLoggingStatus($archiveID, $CalculatedCostSecondaryID, true);
							AC_SetAggregationType($archiveID, $CalculatedCostSecondaryID, 1);
							IPS_ApplyChanges($archiveID);
						}
						if ($EarningsCalculation == true) {
							$this->RegisterVariableFloat("CompensationEnergykWh", $this->Translate('Compensation per kwH'), "~Euro");
							$this->RegisterVariableFloat("CalculatedEarnings", $this->Translate('Calculated Earnings'), "~Euro");
							$sold_power_mainID = $this->GetIDForIdent('sold_power_main');
							$CalculatedEarningsID = $this->GetIDForIdent('CalculatedEarnings');
							AC_SetLoggingStatus($archiveID, $sold_power_mainID, true);
							AC_SetAggregationType($archiveID, $sold_power_mainID, 1);
							AC_SetLoggingStatus($archiveID, $CalculatedEarningsID, true);
							AC_SetAggregationType($archiveID, $CalculatedEarningsID, 1);
							IPS_ApplyChanges($archiveID);
						}					
					}	

					else if ($manufacturerId == "ELS") {
						$this->RegisterVariableFloat("gas_usage", $this->Translate('Gas Usage'), "~Gas");
						if ($ConsumptionGas == true) {
							$this->RegisterVariableFloat("CostEnergym3", $this->Translate('Cost per m3'), "~Euro");
							$this->RegisterVariableFloat("CalculatedCost", $this->Translate('Calculated Cost Gas'), "~Euro");
							$gas_usageID = $this->GetIDForIdent('gas_usageID');
							$CalculatedCostID = $this->GetIDForIdent('CalculatedCost');
							AC_SetLoggingStatus($archiveID, $gas_usageID, true);
							AC_SetAggregationType($archiveID, $gas_usageID, 1);
							AC_SetLoggingStatus($archiveID, $CalculatedCostID, true);
							AC_SetAggregationType($archiveID, $CalculatedCostID, 1);
							IPS_ApplyChanges($archiveID);
						}
					}
				}
			}

		}
		
		//Set hourly timer to get aWATTar data 

		if ($ConsumptionMainaWATTar == 1) {
			$this->QueryAWATTAR(); // get current data

			$this->SetTimerInterval("QueryAWATTAR",3600000);
			$CurrentTimer = $this->GetTimerInterval("QueryAWATTAR");
			//if ($CurrentTimer == 0) {
				$now = new DateTime();
				$target = new DateTime();
				$now->getTimestamp();
				$nextHour = (intval($now->format('H'))+1) % 24;
				$target->setTime($nextHour, 00, 0);
				$diff = $target->getTimestamp() - $now->getTimestamp();
				$EvaTimer = $diff * 1000;
				$this->SetTimerInterval('QueryAWATTAR', $EvaTimer);
				//$this->SetTimerInterval("QueryAWATTAR",3600000);
			//}
		}
		else if ($ConsumptionMainaWATTar == 0) {
			$this->SetTimerInterval("QueryAWATTAR",0);
		}
			
	}
		
	public function GetMeterReading()
	{

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

				$phase1_raw = $data->values->power1;
				$phase1 = $phase1_raw / 1000;
				SetValue($this->GetIDForIdent('phase1'), $phase1);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 1 Consumption: ').round($phase1,3)." kWh",0);

				$phase2_raw = $data->values->power2;
				$phase2 = $phase2_raw / 1000;
				SetValue($this->GetIDForIdent('phase2'), $phase2);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 2 Consumption: ').round($phase2,3)." kWh",0);

				$phase3_raw = $data->values->power3;
				$phase3 = $phase3_raw / 1000;
				SetValue($this->GetIDForIdent('phase3'), $phase3);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 3 Consumption: ').round($phase3,3)." kWh",0);

				$voltage1_raw = $data->values->voltage1;
				$voltage1 = $voltage1_raw / 1000;
				SetValue($this->GetIDForIdent('voltage1'), $voltage1);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 1 Voltage: ').round($voltage1,3)." V",0);

				$voltage2_raw = $data->values->voltage2;
				$voltage2 = $voltage2_raw / 1000;
				SetValue($this->GetIDForIdent('voltage2'), $voltage2);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 2 Voltage: ').round($voltage2,3)." V",0);

				$voltage3_raw = $data->values->voltage3;
				$voltage3 = $voltage3_raw / 1000;
				SetValue($this->GetIDForIdent('voltage3'), $voltage3);
				$this->SendDebug($this->Translate('ESY Meter'),$this->Translate('Phase 3 Voltage: ').round($voltage3,3)." V",0);
				
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

	public function QueryAWATTAR() {

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
		$CostEnergykWh = $BasePrice + ($CurrentPrice * 1.16);

		SetValue($this->GetIDForIdent('CostEnergykWh'), $CostEnergykWh);

		$ConsumptionMainaWATTar = $this->ReadPropertyBoolean("ConsumptionMainaWATTar");
		$CurrentTimer = $this->GetTimerInterval("QueryAWATTAR");
		if (($CurrentTimer > 0) AND ($ConsumptionMainaWATTar == 1)) {
			$this->SetTimerInterval("QueryAWATTAR", 3600000);
		}
	}

	public function CalculateCosts() {
		
		$manufacturerId = GetValue($this->GetIDForIdent('manufacturerId'));
		$CostCalculatorInterval = $this->ReadPropertyInteger("CostCalculator");

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
			$CostCalculatorInterval = $this->ReadPropertyInteger("CostCalculator");
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

			// Caculate Feed-In Revenue 
			
			$ConsumptionSecondary = $this->ReadPropertyBoolean("ConsumptionSecondary");
			
			if ($ConsumptionSecondary == 1) {
				$CostCalculatorInterval = $this->ReadPropertyInteger("CostCalculator");
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
				
				echo "UID ".$meterid." Type ".$manufacturerId." // ";
				
			}
		}	
	}
		
}
