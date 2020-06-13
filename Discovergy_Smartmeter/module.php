<?php

if (!defined('vtBoolean')) {
    define('vtBoolean', 0);
    define('vtInteger', 1);
    define('vtFloat', 2);
    define('vtString', 3);
    define('vtArray', 8);
    define('vtObject', 9);
}


	class Discovergy_Module extends IPSModule
	
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
		
		$UserName = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");
			
			
	}

	public function GetMeters()
	{
		
		$UserName = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");

		
			
			
	}
		
}
