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
			
		$this->MaintainVariable('PublicIP', $this->Translate('PublicIP'), vtString, "", $vpos++, $this->ReadPropertyBoolean("PublicIPVariable") == 1);
				
			
	}
		
	public function GetMeterReading()
	{
		
		$username = $this->ReadPropertyString("UserName");
		$password = $this->ReadPropertyString("Password");
			
			
	}

	public function GetMeters()
	{
		
		$username = $this->ReadPropertyString("UserName");
		$password = $this->ReadPropertyString("Password");

		
		$curl = curl_init('https://api.discovergy.com/public/v1/meters');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

		$json = curl_exec($curl);

		//echo $json,"\n"; //An dieser Stelle kann man alle Werte ausgeben - zur Kontrolle

		$data = json_decode($json);

		$i = 1;

		foreach ($data as $meter) {

			$i++;

			$meterid = $meter->meterId;
			//echo $meterid;

			$ID_serialnumber = @IPS_GetVariableIDByName($i." Zähler Seriennummer", $_IPS['SELF']);
			if($ID_serialnumber === false) {
				$ID_serialnumber = IPS_CreateVariable(3);
				IPS_SetName($ID_serialnumber, $i." Zähler Seriennummer");
				IPS_SetParent($ID_serialnumber, $_IPS['SELF']);
			}

			//SetValue($ID_serialnumber,$meterid);
			SetValue($this->GetIDForIdent($ID_serialnumber), $meterid);	

			$manufacturerId = $meter->manufacturerId;
			//echo $manufacturerId;
			
			$ID_manufacturerId = @IPS_GetVariableIDByName($i." Hersteller ID", $_IPS['SELF']);
			if($ID_manufacturerId === false) {
				$ID_manufacturerId = IPS_CreateVariable(3);
				IPS_SetName($ID_manufacturerId, $i." Hersteller ID");
				IPS_SetParent($ID_manufacturerId, $_IPS['SELF']);
			}

			//SetValue($ID_manufacturerId,$manufacturerId);
			SetValue($this->GetIDForIdent($ID_manufacturerId), $manufacturerId);		
					
		}
	}
		
}
