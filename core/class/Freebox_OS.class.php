<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
include_file('core', 'FreeboxAPI', 'class', 'Freebox_OS');
class Freebox_OS extends eqLogic {	
	public static $_widgetPossibility = array('custom' => array(
	        'visibility' => true,
	        'displayName' => true,
	        'displayObjectName' => true,
	        'optionalParameters' => true,
	        'background-color' => true,
	        'text-color' => true,
	        'border' => true,
	        'border-radius' => true
	));
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'Freebox_OS';		
		if(trim(config::byKey('FREEBOX_SERVER_IP','Freebox_OS'))!=''&&config::byKey('FREEBOX_SERVER_APP_TOKEN','Freebox_OS')!=''&&trim(config::byKey('FREEBOX_SERVER_APP_ID','Freebox_OS'))!='')
			$return['launchable'] = 'ok';
		else
			$return['launchable'] = 'nok';
		$cache = cache::byKey('Freebox_OS::SessionToken');
		$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation');
		if(is_object($cron) && $cron->running() && is_object($cache) && $cache->getValue('')!='')
			$return['state'] = 'ok';
		else 
			$return['state'] = 'nok';
		return $return;
	}
	public static function deamon_start($_debug = false) {
		log::remove('Freebox_OS');
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] == 'ok') 
			return;
		$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation');
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('Freebox_OS');
			$cron->setFunction('RefreshInformation');
			$cron->setEnable(1);
			$cron->setDeamon(1);
			$cron->setSchedule('* * * * *');
			$cron->setTimeout('999999');
			$cron->save();
		}
		$cron->start();
		$cron->run();
	}
	public static function deamon_stop() {
		$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation');
		if (is_object($cron)) {
			$cron->stop();
			$cron->remove();
		}
		$cache = cache::byKey('Freebox_OS::SessionToken');
		$cache->remove();
	}
	public static function AddEqLogic($Name,$_logicalId) {
		$EqLogic = self::byLogicalId($_logicalId, 'Freebox_OS');
		if (!is_object($EqLogic)) {
			$EqLogic = new Freebox_OS();
			$EqLogic->setLogicalId($_logicalId);
			$EqLogic->setObject_id(null);
			$EqLogic->setEqType_name('Freebox_OS');
			$EqLogic->setIsEnable(1);
			$EqLogic->setIsVisible(0);
		}
		$EqLogic->setName($Name);
		$EqLogic->save();
		return $EqLogic;
	}
	public function AddCommande($Name,$_logicalId,$Type="info", $SubType='binary', $Template='', $unite='') {
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$VerifName=$Name;
			$Commande = new Freebox_OSCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
			$count=0;
			while (is_object(cmd::byEqLogicIdCmdName($this->getId(),$VerifName)))
			{
				$count++;
				$VerifName=$Name.'('.$count.')';
			}
			$Commande->setName($VerifName);
			$Commande->setUnite($unite);
			$Commande->setType($Type);
			$Commande->setSubType($SubType);
			$Commande->setTemplate('dashboard',$Template);
			$Commande->setTemplate('mobile', $Template);
			$Commande->save();
		}
		return $Commande;
	}
	public static function CreateArchi() {
		self::AddEqLogic('Home Adapters','HomeAdapters');
		self::AddEqLogic('Réseau','Reseau');
		self::AddEqLogic('Disque Dur','Disque');
		// ADSL
		$ADSL=self::AddEqLogic('ADSL','ADSL');
		$ADSL->AddCommande('Freebox rate down','rate_down',"info",'numeric','','Ko/s');
		$ADSL->AddCommande('Freebox rate up','rate_up',"info",'numeric','','Ko/s');
		$ADSL->AddCommande('Freebox bandwidth up','bandwidth_up',"info",'numeric','','Mb/s');
		$ADSL->AddCommande('Freebox bandwidth down','bandwidth_down',"info",'numeric','','Mb/s');
		$ADSL->AddCommande('Freebox media','media',"info",'string');
		$ADSL->AddCommande('Freebox state','state',"info",'string');
		$System=self::AddEqLogic('Système','System');
		$System->AddCommande('Update','update',"action",'other','Freebox_OS_System');
		$System->AddCommande('Reboot','reboot',"action",'other','Freebox_OS_System');
		$StatusWifi=$System->AddCommande('Status du wifi','wifiStatut',"info",'binary','Freebox_OS_Wifi');
		$StatusWifi->setIsVisible(0);
		$StatusWifi->save();
		$ActiveWifi=$System->AddCommande('Active/Désactive le wifi','wifiOnOff',"action",'other','Freebox_OS_Wifi');
		$ActiveWifi->setValue($StatusWifi->getId());
		$ActiveWifi->save();
		$WifiOn=$System->AddCommande('Wifi On','wifiOn',"action",'other','Freebox_OS_Wifi');
		$WifiOn->setIsVisible(0);
		$WifiOn->save();
		$WifiOff=$System->AddCommande('Wifi Off','wifiOff',"action",'other','Freebox_OS_Wifi');
		$WifiOff->setIsVisible(0);
		$WifiOff->save();
		$System->AddCommande('Freebox firmware version','firmware_version',"info",'string','Freebox_OS_System');
		$System->AddCommande('Mac','mac',"info",'string','Freebox_OS_System');
		$System->AddCommande('Vitesse ventilateur','fan_rpm',"info",'string','Freebox_OS_System','tr/min');
		$System->AddCommande('temp sw','temp_sw',"info",'string','Freebox_OS_System','°C');
		$System->AddCommande('Allumée depuis','uptime',"info",'string','Freebox_OS_System');
		$System->AddCommande('board name','board_name',"info",'string','Freebox_OS_System');
		$System->AddCommande('temp cpub','temp_cpub',"info",'string','Freebox_OS_System','°C');
		$System->AddCommande('temp cpum','temp_cpum',"info",'string','Freebox_OS_System','°C');
		$System->AddCommande('serial','serial',"info",'string','Freebox_OS_System');
		$cmdPF=$System->AddCommande('Redirection de ports','port_forwarding',"action",'message','Freebox_OS_System');
	        $cmdPF->setIsVisible(0);
                $cmdPF->save(); 
		$Phone=self::AddEqLogic('Téléphone','Phone');
		$Phone->AddCommande('Nombre Appels Manqués','nbAppelsManquee',"info",'numeric','Freebox_OS_Phone');
		$Phone->AddCommande('Nombre Appels Reçus','nbAppelRecus',"info",'numeric','Freebox_OS_Phone');
		$Phone->AddCommande('Nombre Appels Passés','nbAppelPasse',"info",'numeric','Freebox_OS_Phone');
		$Phone->AddCommande('Liste Appels Manqués','listAppelsManquee',"info",'string','Freebox_OS_Phone');
		$Phone->AddCommande('Liste Appels Reçus','listAppelsRecus',"info",'string','Freebox_OS_Phone');
		$Phone->AddCommande('Liste Appels Passés','listAppelsPasse',"info",'string','Freebox_OS_Phone');
		$Phone->AddCommande('Faire sonner les téléphones DECT','sonnerieDectOn',"action",'other','Freebox_OS_Phone');
		$Phone->AddCommande('Arrêter les sonneries des téléphones DECT','sonnerieDectOff',"action",'other','Freebox_OS_Phone');
                $Downloads=self::AddEqLogic('Téléchargements','Downloads');
                $Downloads->AddCommande('Nombre de tâche(s)','nb_tasks',"info",'string','Freebox_OS_Downloads');  
                $Downloads->AddCommande('Nombre de tâche(s) active','nb_tasks_active',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) en extraction','nb_tasks_extracting',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) en réparation','nb_tasks_repairing',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) en vérification','nb_tasks_checking',"info",'string','Freebox_OS_Downloads');
                $Downloads->AddCommande('Nombre de tâche(s) en attente','nb_tasks_queued',"info",'string','Freebox_OS_Downloads');
                $Downloads->AddCommande('Nombre de tâche(s) en erreur','nb_tasks_error',"info",'string','Freebox_OS_Downloads');
                $Downloads->AddCommande('Nombre de tâche(s) stoppée(s)','nb_tasks_stopped',"info",'string','Freebox_OS_Downloads');
                $Downloads->AddCommande('Nombre de tâche(s) terminée(s)','nb_tasks_done',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Téléchargement en cours','nb_tasks_downloading',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Vitesse réception','rx_rate',"info",'string','Freebox_OS_Downloads','Mo/s');
		$Downloads->AddCommande('Vitesse émission','tx_rate',"info",'string','Freebox_OS_Downloads','Mo/s');
                $Downloads->AddCommande('Start DL','start_dl',"action",'other','Freebox_OS_Downloads');
                $Downloads->AddCommande('Stop DL','stop_dl',"action",'other','Freebox_OS_Downloads');
                $AirPlay=self::AddEqLogic('AirPlay','AirPlay');
		$AirPlay->AddCommande('Player actuel AirMedia','ActualAirmedia',"info",'string','Freebox_OS_AirMedia_Recever');
		$AirPlay->AddCommande('AirMedia Start','airmediastart',"action",'message','Freebox_OS_AirMedia_Start');
		$AirPlay->AddCommande('AirMedia Stop','airmediastop',"action",'message','Freebox_OS_AirMedia_Start');
		log::add('Freebox_OS','debug',config::byKey('FREEBOX_SERVER_APP_TOKEN'));
		log::add('Freebox_OS','debug',config::byKey('FREEBOX_SERVER_TRACK_ID'));
		if(config::byKey('FREEBOX_SERVER_TRACK_ID')!='')
		{
			$FreeboxAPI= new FreeboxAPI();
			$FreeboxAPI->disques();
			$FreeboxAPI->wifi();
			$FreeboxAPI->system();
			$FreeboxAPI->adslStats();
			$FreeboxAPI->nb_appel_absence();
			$FreeboxAPI->freeboxPlayerPing();
			$FreeboxAPI->getHomeAdapters();
			$FreeboxAPI->getTiles();
			$FreeboxAPI->DownloadStats();
		}
    	}
	public function toHtml($_version = 'mobile') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		$replace['#cmd#']='';
		switch($this->getLogicalId()){
			case 'System':
			case 'Reseau':
				$EquipementsHtml='';
				foreach ($this->getCmd(null, null, true) as $cmd) {
					$replaceCmd['#host_type#'] = $cmd->getConfiguration('host_type');
					$replaceCmd['#IPV4#'] = $cmd->getConfiguration('IPV4');
					$replaceCmd['#IPV6#'] = $cmd->getConfiguration('IPV6');
					$EquipementsHtml.=template_replace($replaceCmd, $cmd->toHtml($_version));
				}
				$replace['#Equipements#'] = $EquipementsHtml;
			break;
			case 'Disque':		
				foreach ($this->getCmd(null, null, true) as $cmd) 
					 $replace['#cmd#'] .= $cmd->toHtml($_version);
			default:
				foreach ($this->getCmd(null, null, true) as $cmd) {
					if($cmd->getIsVisible())	
						$masque[]=$cmd->getLogicalId();
					$replace['#'.$cmd->getLogicalId().'#'] = $cmd->toHtml($_version);
				}
				$replace['#masque#']=json_encode($masque);
			break;
		}
		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $this->getLogicalId(), 'Freebox_OS')));
	}
	public function preSave() {	
		switch($this->getLogicalId())	{
			case 'FreeboxTv':
				$ActionPower=$this->AddCommande('Power','power',"action",'other','Freebox_Tv');
				$InfoPower=$this->AddCommande(' Statut Power','powerstat',"info",'binary','Freebox_Tv');
				$ActionPower->setValue($InfoPower->getId());
				$ActionPower->save();
				$this->AddCommande('Volume +','vol_inc',"action",'other','Freebox_Tv');
				$this->AddCommande('Volume -','vol_dec',"action",'other','Freebox_Tv');
				$this->AddCommande('Programme +','prgm_inc',"action",'other','Freebox_Tv');
				$this->AddCommande('Programme -','prgm_dec',"action",'other','Freebox_Tv');
				$this->AddCommande('Home','home',"action",'other','Freebox_Tv');
				$this->AddCommande('Mute','mute',"action",'other','Freebox_Tv');
				$this->AddCommande('Enregister','rec',"action",'other','Freebox_Tv');
				$this->AddCommande('1','1',"action",'other','Freebox_Tv');
				$this->AddCommande('2','2',"action",'other','Freebox_Tv');
				$this->AddCommande('3','3',"action",'other','Freebox_Tv');
				$this->AddCommande('4','4',"action",'other','Freebox_Tv');
				$this->AddCommande('5','5',"action",'other','Freebox_Tv');
				$this->AddCommande('6','6',"action",'other','Freebox_Tv');
				$this->AddCommande('7','7',"action",'other','Freebox_Tv');
				$this->AddCommande('8','8',"action",'other','Freebox_Tv');
				$this->AddCommande('9','9',"action",'other','Freebox_Tv');
				$this->AddCommande('0','0',"action",'other','Freebox_Tv');
				$this->AddCommande('Precedent','prev',"action",'other','Freebox_Tv');
				$this->AddCommande('Lecture','play',"action",'other','Freebox_Tv');
				$this->AddCommande('Suivant','next',"action",'other','Freebox_Tv');
				$this->AddCommande('Rouge','red',"action",'other','Freebox_Tv');
				$this->AddCommande('Vert','green',"action",'other','Freebox_Tv');
				$this->AddCommande('Bleu','blue',"action",'other','Freebox_Tv');
				$this->AddCommande('Jaune','yellow',"action",'other','Freebox_Tv');
				$this->AddCommande('Ok','ok',"action",'other','Freebox_Tv');
				$this->AddCommande('Haut','up',"action",'other','Freebox_Tv');
				$this->AddCommande('Bas','down',"action",'other','Freebox_Tv');
				$this->AddCommande('Gauche','left',"action",'other','Freebox_Tv');
				$this->AddCommande('Droite','right',"action",'other','Freebox_Tv');
			break;
			case 'AirPlay':
				$this->airmediaConfig();
			break;
		}
		if($this->getLogicalId()=='')
			$this->setLogicalId('FreeboxTv');
	}
	public static function RefreshInformation() {
		$FreeboxAPI = new FreeboxAPI();
		while(true){
			if($FreeboxAPI->open_session()===false)
				break;
			foreach(eqLogic::byType('Freebox_OS') as $Equipement){
				if($Equipement->getIsEnable()){
					switch ($Equipement->getLogicalId()){
						case 'AirPlay':
						break;
						case 'ADSL':
							$result = $FreeboxAPI->adslStats();
							if($result!=false){
								foreach($Equipement->getCmd('info') as $Commande){
									switch ($Commande->getLogicalId()) {
										case "rate_down":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['rate_down']);
										break;
										case "rate_up":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['rate_up']);
										break;
										case "bandwidth_up":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['bandwidth_up']);
										break;
										case "bandwidth_down":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['bandwidth_down']);
										break;
										case "media":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['media']);
										break;
										case "state":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['state']);
										break;
									}
								}
							}
						break;
						case 'Downloads':
							$result = $FreeboxAPI->DownloadStats();
							if($result!=false){
								foreach($Equipement->getCmd('info') as $Commande){
									switch ($Commande->getLogicalId()){
										case "nb_tasks":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks']);
										break;
										case "nb_tasks_downloading":
											$return= $result[''];
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_downloading']);
										break;
										case "nb_tasks_done":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_done']);
										break;
										case "rx_rate":
											$result= $result['rx_rate'];
											if(function_exists('bcdiv'))
												$result= bcdiv($result,1048576,2);
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
										break;
										case "tx_rate":
											$result= $result['tx_rate'];
											if(function_exists('bcdiv'))
												$result= bcdiv($result,1048576,2);
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
										break;
										case "nb_tasks_active":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_active']);
										break;
										case "nb_tasks_stopped":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_stopped']);
										break;
										case "nb_tasks_queued":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_queued']);
										break;
										case "nb_tasks_repairing":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_repairing']);
										break;
										case "nb_tasks_extracting":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_extracting']);
										break;
										case "nb_tasks_error":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_error']);
										break;    
										case "nb_tasks_checking":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_checking']);
										break;    
									}
								}
							}
						break;
						case 'System':
							foreach($Equipement->getCmd('info') as $Commande){
								if($Commande->getLogicalId()=="wifiStatut")
									$result = $FreeboxAPI->wifi();
								else
									$result = $FreeboxAPI->system();
								switch ($Commande->getLogicalId()){
									case "mac":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['mac']);
									break;
									case "fan_rpm":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['fan_rpm']);
									break;
									case "temp_sw":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['temp_sw']);
									break;
									case "uptime":
										$result= $result['uptime'];
										$result=str_replace(' heure ','h ',$result);
										$result=str_replace(' heures ','h ',$result);
										$result=str_replace(' minute ','min ',$result);
										$result=str_replace(' minutes ','min ',$result);
										$result=str_replace(' secondes','s',$result);
										$result=str_replace(' seconde','s',$result);
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
									break;
									case "board_name":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['board_name']);
									break;
									case "temp_cpub":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['temp_cpub']);
									break;
									case "temp_cpum":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['temp_cpum']);
									break;
									case "serial":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['serial']);
									break;
									case "firmware_version":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['firmware_version']);
									break;
									case "wifiStatut":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
									break;
								}		
							}
						break;
						case 'Disque':		
							$result = $FreeboxAPI->disques($Commande->getLogicalId());
							if($result!=false){								
								foreach($Equipement->getCmd('info') as $Commande)
									$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
							}
						break;
						case 'Phone':
							$result = $FreeboxAPI->nb_appel_absence();
							if($result!=false){								
								foreach($Equipement->getCmd('info') as $Commande){
									switch ($Commande->getLogicalId()) {
										case "nbAppelsManquee":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['missed']);
										break;
										case "nbAppelRecus":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['accepted']);
										break;
										case "nbAppelPasse":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['outgoing']);
										break;
										case "listAppelsManquee":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['list_missed']);
										break;
										case "listAppelsRecus":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['list_accepted']);
										break;
										case "listAppelsPasse":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['list_outgoing']);
										break;
									}
								}
							}
						break;
						case'Reseau':
							$result=$FreeboxAPI->ReseauPing($Commande->getLogicalId());
							if($result!=false){								
								foreach($Equipement->getCmd('info') as $Commande){
									if (isset($result['l3connectivities']))	{
										foreach($result['l3connectivities'] as $Ip){
											if ($Ip['active']){
												if($Ip['af']=='ipv4')
													$Commande->setConfiguration('IPV4',$Ip['addr']);
												else
													$Commande->setConfiguration('IPV6',$Ip['addr']);
											}
										}
									}
									$Commande->setConfiguration('host_type',$result['host_type']);
									$Commande->save();
									if (isset($result['active'])) {
										if ($result['active'] == 'true') {
											$Commande->setOrder($Commande->getOrder() % 1000);
											$Commande->save();
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),true);
										} else {
											$Commande->setOrder($Commande->getOrder() % 1000 + 1000);
											$Commande->save();
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),false);
										}
									} else {
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),false);
									}
								}
							}
						break;
						case'HomeAdapters':
							$result=$FreeboxAPI->getHomeAdapterStatus($Commande->getLogicalId());
							//if($result!=false){
								foreach($Equipement->getCmd('info') as $Commande)
									$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['status']);
							//}
						break;
						case'FreeboxTv':
							foreach($Equipement->getCmd('info') as $Commande){
								switch($Commande->getLogicalId()){
									case 'powerstat':
										$result=exec('nc -zv '.$Equipement->getConfiguration('FREEBOX_TV_IP').' 7000 2>&1 | grep -E "open|succeeded" | wc -l');
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
										log::add('Freebox_OS','debug','Etat du player freebox '.$Equipement->getConfiguration('FREEBOX_TV_IP').' '.$result);
									break;
								}
							}
						break;
						default:
							$result=$FreeboxAPI->getTile($Equipement->getLogicalId());
							if($result!=false){
								foreach($result as $Commande)
									$Equipement->checkAndUpdateCmd($Commande['ep_id'],$Commande['value']);
							}
						break;
					}
				}
			}
			$FreeboxAPI->close_session();
			sleep(config::byKey('DemonSleep','Freebox_OS'));
		}
		self::deamon_stop();
	}
	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'Freebox_OS_update';
		$return['progress_file'] = '/tmp/compilation_Freebox_OS_in_progress';
		if (exec('dpkg -s netcat | grep -c "Status: install"') ==1)
				$return['state'] = 'ok';
		else
			$return['state'] = 'nok';
		return $return;
	}
	public static function dependancy_install() {
		if (file_exists('/tmp/compilation_Freebox_OS_in_progress')) {
			return;
		}
		log::remove('Freebox_OS_update');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
		$cmd .= ' >> ' . log::getPathToLog('Freebox_OS_update') . ' 2>&1 &';
		exec($cmd);
	}
}
class Freebox_OSCmd extends cmd {
	public function execute($_options = array())	{
		log::add('Freebox_OS','debug','Connexion sur la freebox pour '.$this->getName());
		$FreeboxAPI= new FreeboxAPI();
		switch ($this->getEqLogic()->getLogicalId())
		{
			case 'ADSL':
				$result = $FreeboxAPI->adslStats();
				if($result!=false){
					switch ($this->getLogicalId()) 
					{
						case "rate_down":
							$return= $result['rate_down'];
							break;
						case "rate_up":
							$return= $result['rate_up'];
							break;
						case "bandwidth_up":
							$return= $result['bandwidth_up'];
							break;
						case "bandwidth_down":
							$return= $result['bandwidth_down'];
							break;
						case "media":
							$return= $result['media'];
							break;
						case "state":
							$return= $result['state'];
							break;
					}
				}
			break;
			case 'Downloads':
				$result = $FreeboxAPI->DownloadStats();
				if($result!=false){
					switch ($this->getLogicalId())
					{
						case "nb_tasks":
							$return= $result['nb_tasks'];
							break;
						case "nb_tasks_downloading":
                                                        $return= $result['nb_tasks_downloading'];
                                                        break;
						case "nb_tasks_done":
                                                        $return= $result['nb_tasks_done'];
                                                        break;
						case "rx_rate":
                                                        $return= $result['rx_rate'];
                                                        if(function_exists('bcdiv'))
                                                		$return= bcdiv($return,1048576,2);
                                                        break;
						case "tx_rate":
                                                        $return= $result['tx_rate'];
                                                        if(function_exists('bcdiv'))
                                                		$return= bcdiv($return,1048576,2);
							break;
                                                case "nb_tasks_active":
                                                        $return= $result['nb_tasks_active'];
                                                        break;
                                                case "nb_tasks_stopped":
                                                        $return= $result['nb_tasks_stopped'];
                                                        break;
                                                case "nb_tasks_queued":
                                                        $return= $result['nb_tasks_queued'];
                                                        break;
                                                case "nb_tasks_repairing":
                                                        $return= $result['nb_tasks_repairing'];
                                                        break;
                                                case "nb_tasks_extracting":
                                                        $return= $result['nb_tasks_extracting'];
                                                        break;
                                                case "nb_tasks_error":
                                                        $return= $result['nb_tasks_error'];
                                                        break;    
                                                case "nb_tasks_checking":
                                                        $return= $result['nb_tasks_checking'];
                                                        break;    
                                                case "stop_dl":
                                                        $FreeboxAPI->Downloads(0);
	                                                break;  
                                                case "start_dl":
                                                        $FreeboxAPI->Downloads(1);
                                                        break;  
					}
				}
			break;
			case 'System':
				if($this->getLogicalId()=="wifiStatut"||$this->getLogicalId()=="wifiOnOff"||$this->getLogicalId()=='wifiOn'||$this->getLogicalId()=='wifiOff')
					$result = $FreeboxAPI->wifi();
				else
					$result = $FreeboxAPI->system();
					switch ($this->getLogicalId()) 
					{
						case "reboot":
							$FreeboxAPI->reboot();
							break;
						case "update":
							$FreeboxAPI->UpdateSystem();
							break;
						case "wifiOnOff":
							if($result==true) 
								$FreeboxAPI->wifiPUT(0);
							else
								$FreeboxAPI->wifiPUT(1);
						break;
						case 'wifiOn':
							$FreeboxAPI->wifiPUT(1);
						break;
						case 'wifiOff':
							$FreeboxAPI->wifiPUT(0);
						break;
						case 'port_forwarding':
							$FreeboxAPI->PortForwarding($_options['message']);
				}		break;
			break;
			case 'Phone':
				$result = $FreeboxAPI->nb_appel_absence();
				if($result!=false){
					switch ($this->getLogicalId()) 
					{
						case "sonnerieDectOn":
							$FreeboxAPI->ringtone_on();
							break;
						case "sonnerieDectOff":
							$FreeboxAPI->ringtone_off();
							break;
					}
				}
			break;
			case'FreeboxTv':
				switch($this->getLogicalId()){
					case 'power':
						if($this->getEqLogic()->getConfiguration('mini4k')) {
							$result=exec('sudo '.dirname(__FILE__) .'/../../ressources/mini4k_cmd '.$this->getEqLogic()->getConfiguration('FREEBOX_TV_IP').' '.$this->getLogicalId());
							log::add('Freebox_OS','debug', 'Mini 4K : sudo '.dirname(__FILE__) .'/../../ressources/mini4k_cmd '.$this->getEqLogic()->getConfiguration('FREEBOX_TV_IP').' '.$this->getLogicalId());
						}
						else
							$result=$FreeboxAPI->send_cmd_fbxtv($this->getLogicalId());
						 $this->getEqLogic()->getCmd('info','powerstat')->execute();
					break;
					default:
						if($this->getEqLogic()->getConfiguration('mini4k')) {
							$result=exec('sudo '.dirname(__FILE__) .'/../../ressources/mini4k_cmd '.$this->getEqLogic()->getConfiguration('FREEBOX_TV_IP').' '.$this->getLogicalId());
							log::add('Freebox_OS','debug', 'Mini 4K : sudo '.dirname(__FILE__) .'/../../ressources/mini4k_cmd '.$this->getEqLogic()->getConfiguration('FREEBOX_TV_IP').' '.$this->getLogicalId());
						}
						else
							$result=$FreeboxAPI->send_cmd_fbxtv($this->getLogicalId());
					break;
				}
			break;
			case'AirPlay':
				$receivers=$this->getEqLogic()->getCmd(null,"ActualAirmedia");
				$receiver=$receivers->execCmd();
				$receivers->setCollectDate(date('Y-m-d H:i:s'));
				$receivers->save();
				switch($this->getLogicalId()){
					case "airmediastart":
						$return = $FreeboxAPI->AirMediaAction($receiver,"start",$_options['titre'],$_options['message']);
					break;
					case "airmediastop":
						$return = $FreeboxAPI->AirMediaAction($receiver,"stop",$_options['titre'],$_options['message']);
					break;
				}
			break;
		}		
	}
}
