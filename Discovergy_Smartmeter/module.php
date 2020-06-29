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
			
			$this->RegisterVariableInteger('MeterCount', $this->Translate('Meter Count'));	
			//Component sets timer, but default is OFF
			$this->RegisterTimer("GetMeterReading",0,"DSM_GetMeterReading(\$_IPS['TARGET']);");
					
		}
	
	public function ApplyChanges()
	{
			
		//Never delete this line!
		parent::ApplyChanges();
		
								
		//Timers Update - if greater than 0 = On
		
		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("GetMeterReading",$TimerMS);
					
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
				$serialnumber = $meter->serialNumber;
				$fullSerialNumber = $meter->fullSerialNumber;
				$locationStreet = $meter->location->street;
				$locationStreetNumber = $meter->location->streetNumber;
				$locationzip = $meter->location->zip;
				$locationCity = $meter->location->city;
				$locationCountry = $meter->location->country;
				$meterlocation = $locationStreet." ".$locationStreetNumber." ".$locationzip." ".$locationCity." ".$locationCountry;
				$this->MaintainVariable($i.'meterlocation', $this->Translate('Meter ').$i.$this->Translate(' Location'), vtString, '', $vpos++, isset($meter));
				SetValue($this->GetIDForIdent($i.'meterlocation'), $meterlocation);
				$this->MaintainVariable($i.'fullSerialNumber', $this->Translate('Meter ').$i.$this->Translate(' Full Serialnumber'), vtString, '', $vpos++, isset($meter));
				SetValue($this->GetIDForIdent($i.'fullSerialNumber'), $fullSerialNumber);
				$this->MaintainVariable($i.'serialnumber', $this->Translate('Meter ').$i.$this->Translate(' Serialnumber'), vtString, '', $vpos++, isset($meter));
				SetValue($this->GetIDForIdent($i.'serialnumber'), $serialnumber);
				$this->MaintainVariable($i.'meterID', $this->Translate('Meter ').$i.$this->Translate(' ID'), vtString, '', $vpos++, isset($meter));
				SetValue($this->GetIDForIdent($i.'meterID'), $meterid);
				$this->MaintainVariable($i.'serialnumber', $this->Translate('Meter ').$i.$this->Translate(' Serialnumber'), vtString, '', $vpos++, isset($meter));
				SetValue($this->GetIDForIdent($i.'serialnumber'), $serialnumber);
				$this->MaintainVariable($i.'manufacturerId', $this->Translate('Meter ').$i.$this->Translate(' Manufacturer'), vtString, '', $vpos++, isset($meter));
				SetValue($this->GetIDForIdent($i.'manufacturerId'), $manufacturerId);
				

				if ($manufacturerId == "ESY") {
					$this->MaintainVariable($i.'energy', $this->Translate('Meter ').$i.$this->Translate(' Energy Bought'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));
					$this->MaintainVariable($i.'energyout', $this->Translate('Meter ').$i.$this->Translate(' Energy Sold'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));
					$this->MaintainVariable($i.'power', $this->Translate('Meter ').$i.$this->Translate(' Current Power'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));
					$this->MaintainVariable($i.'phase1', $this->Translate('Meter ').$i.$this->Translate(' Phase 1'), vtFloat, 'DSM.Watt', $vpos++, isset($meter));
					$this->MaintainVariable($i.'phase2', $this->Translate('Meter ').$i.$this->Translate(' Phase 2'), vtFloat, 'DSM.Watt', $vpos++, isset($meter));
					$this->MaintainVariable($i.'phase3', $this->Translate('Meter ').$i.$this->Translate(' Phase 3'), vtFloat, 'DSM.Watt', $vpos++, isset($meter));
					$this->MaintainVariable($i.'voltage1', $this->Translate('Meter ').$i.$this->Translate(' Voltage Phase 1'), vtFloat, '~Volt', $vpos++, isset($meter));
					$this->MaintainVariable($i.'voltage2', $this->Translate('Meter ').$i.$this->Translate(' Voltage Phase 2'), vtFloat, '~Volt', $vpos++, isset($meter));
					$this->MaintainVariable($i.'voltage3', $this->Translate('Meter ').$i.$this->Translate(' Voltage Phase 3'), vtFloat, '~Volt', $vpos++, isset($meter));					
		
				}
		
				else if ($manufacturerId == "EMH") {
					$this->MaintainVariable($i.'effective_power_complete', $this->Translate('Meter ').$i.$this->Translate(' Effective Power Complete'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));
					$this->MaintainVariable($i.'effective_power_main', $this->Translate('Meter ').$i.$this->Translate(' Effective Power Main Time'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));
					$this->MaintainVariable($i.'effective_power_secondary', $this->Translate('Meter ').$i.$this->Translate(' Effective Power Secondary Time'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));
					$this->MaintainVariable($i.'sold_power_complete', $this->Translate('Meter ').$i.$this->Translate(' Sold Power Complete'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));
					$this->MaintainVariable($i.'sold_power_main', $this->Translate('Meter ').$i.$this->Translate(' Sold Power Main Time'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));
					$this->MaintainVariable($i.'sold_power_secondary', $this->Translate('Meter ').$i.$this->Translate(' Sold Power Secondary Time'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));	
					$this->MaintainVariable($i.'current_power', $this->Translate('Meter ').$i.$this->Translate(' Current Power'), vtFloat, 'DSM.WattK', $vpos++, isset($meter));				
				}	

				else if ($manufacturerId == "ELS") {
					$this->MaintainVariable($i.'gas_usage', $this->Translate('Meter ').$i.$this->Translate(' Gas Usage'), vtFloat, '~Gas', $vpos++, isset($meter));
				}	
				
				SetValue($this->GetIDForIdent('MeterCount'), $i);
			}
		}		
			
	}
		
	public function GetMeterReading()
	{

		$username = $this->ReadPropertyString("UserName");
		$password = $this->ReadPropertyString("Password");
		$metercount = GetValue($this->GetIDForIdent('MeterCount'));
		
		for($i=1; $i <= $metercount; $i++) {
			//echo "$i, ";
			$meterid = GetValue($this->GetIDForIdent($i.'meterID'));
			$manufacturerId = GetValue($this->GetIDForIdent($i.'manufacturerId'));
			 
			// if ($SerialNumber === false)
			//	 echo "Variable nicht gefunden!";
			// else
			//	 echo "   Geräte ID ".$SerialNumber;


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

			if ($manufacturerId == "ESY") {
				
				$energy_raw = $data->values->energy;
				$energy = $energy_raw / 10000000000;
				SetValue($this->GetIDForIdent($i.'energy'), $energy);

				$energyout_raw = $data->values->energyOut;
				$energyout = $energyout_raw / 10000000000;
				SetValue($this->GetIDForIdent($i.'energyout'), $energyout);

				$power_raw = $data->values->power;
				$power = $power_raw / 1000;
				SetValue($this->GetIDForIdent($i.'power'), $power);

				$phase1_raw = $data->values->power1;
				$phase1 = $phase1_raw / 1000;
				SetValue($this->GetIDForIdent($i.'phase1'), $phase1);

				$phase2_raw = $data->values->power2;
				$phase2 = $phase2_raw / 1000;
				SetValue($this->GetIDForIdent($i.'phase2'), $phase2);

				$phase3_raw = $data->values->power3;
				$phase3 = $phase3_raw / 1000;
				SetValue($this->GetIDForIdent($i.'phase3'), $phase3);

				$voltage1_raw = $data->values->voltage1;
				$voltage1 = $voltage1_raw / 1000;
				SetValue($this->GetIDForIdent($i.'voltage1'), $voltage1);

				$voltage2_raw = $data->values->voltage2;
				$voltage2 = $voltage2_raw / 1000;
				SetValue($this->GetIDForIdent($i.'voltage2'), $voltage2);

				$voltage3_raw = $data->values->voltage3;
				$voltage3 = $voltage3_raw / 1000;
				SetValue($this->GetIDForIdent($i.'voltage3'), $voltage3);
				
			}
	
			else if ($manufacturerId == "EMH") {
				
				$effective_power_complete_raw = $data->values->{'1.8.0'};
				$effective_power_complete = $effective_power_complete_raw / 1000000;
				SetValue($this->GetIDForIdent($i.'effective_power_complete'), $effective_power_complete);

				$effective_power_main_raw = $data->values->{'1.8.1'};
				$effective_power_main = $effective_power_main_raw / 1000000;
				SetValue($this->GetIDForIdent($i.'effective_power_main'), $effective_power_main);

				$effective_power_secondary_raw = $data->values->{'1.8.2'};
				$effective_power_secondary = $effective_power_secondary_raw / 1000000;
				SetValue($this->GetIDForIdent($i.'effective_power_secondary'), $effective_power_secondary);

				$sold_power_complete_raw = $data->values->{'2.8.0'};
				$sold_power_complete = $sold_power_complete_raw / 1000000;
				SetValue($this->GetIDForIdent($i.'sold_power_complete'), $sold_power_complete);

				$sold_power_main_raw = $data->values->{'2.8.1'};
				$sold_power_main = $sold_power_main_raw / 1000000;
				SetValue($this->GetIDForIdent($i.'sold_power_main'), $sold_power_main);

				$sold_power_secondary_raw = $data->values->{'2.8.2'};
				$sold_power_secondary = $sold_power_secondary_raw / 1000000;
				SetValue($this->GetIDForIdent($i.'sold_power_secondary'), $sold_power_secondary);

				$current_power_raw = $data->values->{'1.25'};
				$current_power = $current_power_raw / 1000;
				SetValue($this->GetIDForIdent($i.'current_power'), $current_power);
				
			}
			
			else if ($manufacturerId == "ELS") {
				
				$gas_raw = $data->values->volume;
				$gas_usage = $gas_raw / 1000;
				SetValue($this->GetIDForIdent($i.'gas_usage'), $gas_usage);
					
			}

		}		
	}
		
}
