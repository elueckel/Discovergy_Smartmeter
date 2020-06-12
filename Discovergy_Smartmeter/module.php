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
			$this->RegisterTimer("UpdateTimer",0,"DSM_DiscovergySmartMeter(\$_IPS['TARGET']);");
					
	
		}
	
	public function ApplyChanges()
	{
			
		//Never delete this line!
		parent::ApplyChanges();
		
								
		//Timers Update - if greater than 0 = On
		
		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("UpdateTimer",$TimerMS);
					
		$vpos = 15;			
			
		$this->MaintainVariable('PublicIP', $this->Translate('PublicIP'), vtString, "", $vpos++, $this->ReadPropertyBoolean("PublicIPVariable") == 1);
				
			
	}
	
		
	public function DNSUpdate()
	{
		
			$RootDomain = $this->ReadPropertyString("RootDomain");
			$ARecord = $this->ReadPropertyString("ARecord");
			$Key = $this->ReadPropertyString("APIKey");
			$Secret = $this->ReadPropertyString("Secret");
			$IPInfoToken = $this->ReadPropertyString("IPInfoToken");
			$DNSUpdate = $this->ReadPropertyBoolean("DNSUpdate");
			$PublicIPVariable = $this->ReadPropertyBoolean("PublicIPVariable");
			$Debug = $this->ReadPropertyBoolean("Debug");	
			
	}
		
}
