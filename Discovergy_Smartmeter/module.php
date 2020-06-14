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

		if ($username !== "") {
			
			$curl = curl_init('https://api.discovergy.com/public/v1/meters');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
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
				//echo $meterid;
			
				$this->MaintainVariable($i.'meterID', $i.$this->Translate(' Meter Serialnumber'), vtString, '', $vpos++, isset($meter));
				SetValue($this->GetIDForIdent($i.'meterID'), $meterid);
				$this->MaintainVariable($i.'manufacturerId', $i.$this->Translate(' Meter Manufacturer'), vtString, '', $vpos++, isset($meter));
				SetValue($this->GetIDForIdent($i.'manufacturerId'), $manufacturerId);

				if ($manufacturerId == "ESY") {

					$this->MaintainVariable($i.'energy', $i.$this->Translate(' Meter Energy Bought'), vtFloat, '', $vpos++, isset($meter));
					$this->MaintainVariable($i.'energyout', $i.$this->Translate(' Meter Energy Sold'), vtFloat, '', $vpos++, isset($meter));
					$this->MaintainVariable($i.'consumption', $i.$this->Translate(' Meter Current Consumption'), vtFloat, '', $vpos++, isset($meter));
					$this->MaintainVariable($i.'phase1', $i.$this->Translate(' Phase 1'), vtFloat, '', $vpos++, isset($meter));
					$this->MaintainVariable($i.'phase2', $i.$this->Translate(' Phase 2'), vtFloat, '', $vpos++, isset($meter));
					$this->MaintainVariable($i.'phase3', $i.$this->Translate(' Phase 3'), vtFloat, '', $vpos++, isset($meter));
					$this->MaintainVariable($i.'voltage1', $i.$this->Translate(' Voltage 1'), vtFloat, '~Volt', $vpos++, isset($meter));
					$this->MaintainVariable($i.'voltage2', $i.$this->Translate(' Voltage 2'), vtFloat, '~Volt', $vpos++, isset($meter));
					$this->MaintainVariable($i.'voltage3', $i.$this->Translate(' Voltage 3'), vtFloat, '~Volt', $vpos++, isset($meter));					
		
				}
		
				else if ($manufacturerId == "EMH") {
		
					$this->MaintainVariable($i.'effective_power_complete', $i.$this->Translate(' Effective Power Complete'), vtFloat, '', $vpos++, isset($meter));
					$this->MaintainVariable($i.'effective_power_main', $i.$this->Translate(' Effective Power Main Time'), vtFloat, '', $vpos++, isset($meter));
					$this->MaintainVariable($i.'effective_power_secondary', $i.$this->Translate(' Effective Power Secondary Time'), vtFloat, '', $vpos++, isset($meter));
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
			curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	
			$json = curl_exec($curl);
	
			//echo $json,"\n"; //An dieser Stelle kann man alle Werte ausgeben - zur Kontrolle
	
			$data = json_decode($json);

			if ($manufacturerId == "ESY") {
				
				$energy_raw = $data->values->energy;
				$energy = $energy_raw / 1000000000;
				SetValue($this->GetIDForIdent($i.'energy'), $energy);

				$energyout_raw = $data->values->energyOut;
				$energyout = $energyout_raw / 1000000000;
				SetValue($this->GetIDForIdent($i.'energyout'), $energyout);

				$consumption_raw = $data->values->power;
				$consumption = $consumption_raw / 1000;
				SetValue($this->GetIDForIdent($i.'consumption'), $consumption);

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
				
			}	
		}		
	}
		
}
