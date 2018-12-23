<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
include_file('core', 'FreeboxApi', 'class', 'Freebox_OS');
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
	public static function AddCommande($eqLogic,$Name,$_logicalId,$Type="info", $SubType='binary', $Template='', $unite='') {
		$Commande = $eqLogic->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$VerifName=$Name;
			$Commande = new Freebox_OSCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($eqLogic->getId());
			$count=0;
			while (is_object(cmd::byEqLogicIdCmdName($eqLogic->getId(),$VerifName)))
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
		Freebox_OS::AddEqLogic('Réseau','Reseau');
		self::AddEqLogic('Disque Dur','Disque');
		// ADSL
		$ADSL=self::AddEqLogic('ADSL','ADSL');
		self::AddCommande($ADSL,'Freebox rate down','rate_down',"info",'numeric','Freebox_OS_Adsl');
		self::AddCommande($ADSL,'Freebox rate up','rate_up',"info",'numeric','Freebox_OS_Adsl');
		self::AddCommande($ADSL,'Freebox bandwidth up','bandwidth_up',"info",'numeric','Freebox_OS_Adsl');
		self::AddCommande($ADSL,'Freebox bandwidth down','bandwidth_down',"info",'numeric','Freebox_OS_Adsl');
		self::AddCommande($ADSL,'Freebox media','media',"info",'string','Freebox_OS_Adsl');
		self::AddCommande($ADSL,'Freebox state','state',"info",'string','Freebox_OS_Adsl');
		$System=self::AddEqLogic('Système','System');
		self::AddCommande($System,'Update','update',"action",'other','Freebox_OS_System');
		self::AddCommande($System,'Reboot','reboot',"action",'other','Freebox_OS_System');
		$StatusWifi=self::AddCommande($System,'Status du wifi','wifiStatut',"info",'binary','Freebox_OS_Wifi');
		$StatusWifi->setIsVisible(0);
		$StatusWifi->save();
		$ActiveWifi=self::AddCommande($System,'Active/Désactive le wifi','wifiOnOff',"action",'other','Freebox_OS_Wifi');
		$ActiveWifi->setValue($StatusWifi->getId());
		$ActiveWifi->save();
		$WifiOn=self::AddCommande($System,'Wifi On','wifiOn',"action",'other','Freebox_OS_Wifi');
		$WifiOn->setIsVisible(0);
		$WifiOn->save();
		$WifiOff=self::AddCommande($System,'Wifi Off','wifiOff',"action",'other','Freebox_OS_Wifi');
		$WifiOff->setIsVisible(0);
		$WifiOff->save();
		self::AddCommande($System,'Freebox firmware version','firmware_version',"info",'string','Freebox_OS_System');
		self::AddCommande($System,'Mac','mac',"info",'string','Freebox_OS_System');
		self::AddCommande($System,'Vitesse ventilateur','fan_rpm',"info",'string','Freebox_OS_System','tr/min');
		self::AddCommande($System,'temp sw','temp_sw',"info",'string','Freebox_OS_System','°C');
		self::AddCommande($System,'Allumée depuis','uptime',"info",'string','Freebox_OS_System');
		self::AddCommande($System,'board name','board_name',"info",'string','Freebox_OS_System');
		self::AddCommande($System,'temp cpub','temp_cpub',"info",'string','Freebox_OS_System','°C');
		self::AddCommande($System,'temp cpum','temp_cpum',"info",'string','Freebox_OS_System','°C');
		self::AddCommande($System,'serial','serial',"info",'string','Freebox_OS_System');
		$cmdPF=self::AddCommande($System,'Redirection de ports','port_forwarding',"action",'message','Freebox_OS_System');
	        $cmdPF->setIsVisible(0);
                $cmdPF->save(); 
		$Phone=self::AddEqLogic('Téléphone','Phone');
		self::AddCommande($Phone,'Nombre Appels Manqués','nbAppelsManquee',"info",'numeric','Freebox_OS_Phone');
		self::AddCommande($Phone,'Nombre Appels Reçus','nbAppelRecus',"info",'numeric','Freebox_OS_Phone');
		self::AddCommande($Phone,'Nombre Appels Passés','nbAppelPasse',"info",'numeric','Freebox_OS_Phone');
		self::AddCommande($Phone,'Liste Appels Manqués','listAppelsManquee',"info",'string','Freebox_OS_Phone');
		self::AddCommande($Phone,'Liste Appels Reçus','listAppelsRecus',"info",'string','Freebox_OS_Phone');
		self::AddCommande($Phone,'Liste Appels Passés','listAppelsPasse',"info",'string','Freebox_OS_Phone');
		self::AddCommande($Phone,'Faire sonner les téléphones DECT','sonnerieDectOn',"action",'other','Freebox_OS_Phone');
		self::AddCommande($Phone,'Arrêter les sonneries des téléphones DECT','sonnerieDectOff',"action",'other','Freebox_OS_Phone');
                $Downloads=self::AddEqLogic('Téléchargements','Downloads');
                self::AddCommande($Downloads,'Nombre de tâche(s)','nb_tasks',"info",'string','Freebox_OS_Downloads');  
                self::AddCommande($Downloads,'Nombre de tâche(s) active','nb_tasks_active',"info",'string','Freebox_OS_Downloads');
		self::AddCommande($Downloads,'Nombre de tâche(s) en extraction','nb_tasks_extracting',"info",'string','Freebox_OS_Downloads');
		self::AddCommande($Downloads,'Nombre de tâche(s) en réparation','nb_tasks_repairing',"info",'string','Freebox_OS_Downloads');
		self::AddCommande($Downloads,'Nombre de tâche(s) en vérification','nb_tasks_checking',"info",'string','Freebox_OS_Downloads');
                self::AddCommande($Downloads,'Nombre de tâche(s) en attente','nb_tasks_queued',"info",'string','Freebox_OS_Downloads');
                self::AddCommande($Downloads,'Nombre de tâche(s) en erreur','nb_tasks_error',"info",'string','Freebox_OS_Downloads');
                self::AddCommande($Downloads,'Nombre de tâche(s) stoppée(s)','nb_tasks_stopped',"info",'string','Freebox_OS_Downloads');
                self::AddCommande($Downloads,'Nombre de tâche(s) terminée(s)','nb_tasks_done',"info",'string','Freebox_OS_Downloads');
		self::AddCommande($Downloads,'Téléchargement en cours','nb_tasks_downloading',"info",'string','Freebox_OS_Downloads');
		self::AddCommande($Downloads,'Vitesse réception','rx_rate',"info",'string','Freebox_OS_Downloads','Mo/s');
		self::AddCommande($Downloads,'Vitesse émission','tx_rate',"info",'string','Freebox_OS_Downloads','Mo/s');
                self::AddCommande($Downloads,'Start DL','start_dl',"action",'other','Freebox_OS_Downloads');
                self::AddCommande($Downloads,'Stop DL','stop_dl',"action",'other','Freebox_OS_Downloads');
                $AirPlay=self::AddEqLogic('AirPlay','AirPlay');
		self::AddCommande($AirPlay,'Player actuel AirMedia','ActualAirmedia',"info",'string','Freebox_OS_AirMedia_Recever');
		self::AddCommande($AirPlay,'AirMedia Start','airmediastart',"action",'message','Freebox_OS_AirMedia_Start');
		self::AddCommande($AirPlay,'AirMedia Stop','airmediastop',"action",'message','Freebox_OS_AirMedia_Start');
		log::add('Freebox_OS','debug',config::byKey('FREEBOX_SERVER_APP_TOKEN'));
		log::add('Freebox_OS','debug',config::byKey('FREEBOX_SERVER_TRACK_ID'));
		if(config::byKey('FREEBOX_SERVER_TRACK_ID')!='')
		{
			self::disques();
			self::wifi();
			self::system();
			self::adslStats();
			self::nb_appel_absence();
			self::freeboxPlayerPing();
			self::DownloadStats();
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
				$ActionPower=self::AddCommande($this,'Power','power',"action",'other','Freebox_Tv');
				$InfoPower=self::AddCommande($this,' Statut Power','powerstat',"info",'binary','Freebox_Tv');
				$ActionPower->setValue($InfoPower->getId());
				$ActionPower->save();
				self::AddCommande($this,'Volume +','vol_inc',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Volume -','vol_dec',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Programme +','prgm_inc',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Programme -','prgm_dec',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Home','home',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Mute','mute',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Enregister','rec',"action",'other','Freebox_Tv');
				self::AddCommande($this,'1','1',"action",'other','Freebox_Tv');
				self::AddCommande($this,'2','2',"action",'other','Freebox_Tv');
				self::AddCommande($this,'3','3',"action",'other','Freebox_Tv');
				self::AddCommande($this,'4','4',"action",'other','Freebox_Tv');
				self::AddCommande($this,'5','5',"action",'other','Freebox_Tv');
				self::AddCommande($this,'6','6',"action",'other','Freebox_Tv');
				self::AddCommande($this,'7','7',"action",'other','Freebox_Tv');
				self::AddCommande($this,'8','8',"action",'other','Freebox_Tv');
				self::AddCommande($this,'9','9',"action",'other','Freebox_Tv');
				self::AddCommande($this,'0','0',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Precedent','prev',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Lecture','play',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Suivant','next',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Rouge','red',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Vert','green',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Bleu','blue',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Jaune','yellow',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Ok','ok',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Haut','up',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Bas','down',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Gauche','left',"action",'other','Freebox_Tv');
				self::AddCommande($this,'Droite','right',"action",'other','Freebox_Tv');
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
					foreach($Equipement->getCmd('info') as $Commande){
						$Commande->execute();
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
						case "mac":
							$return= $result['mac'];
							break;
						case "fan_rpm":
							$return= $result['fan_rpm'];
							break;
						case "temp_sw":
							$return= $result['temp_sw'];
							break;
						case "uptime":
							$return= $result['uptime'];
							$return=str_replace(' heure ','h ',$return);
							$return=str_replace(' heures ','h ',$return);
							$return=str_replace(' minute ','min ',$return);
							$return=str_replace(' minutes ','min ',$return);
							$return=str_replace(' secondes','s',$return);
							$return=str_replace(' seconde','s',$return);
							break;
						case "board_name":
							$return= $result['board_name'];
							break;
						case "temp_cpub":
							$return= $result['temp_cpub'];
							break;
						case "temp_cpum":
							$return= $result['temp_cpum'];
							break;
						case "serial":
							$return= $result['serial'];
							break;
						case "firmware_version":
							$return= $result['firmware_version'];
							break;
						case "wifiStatut":
							$return= $result;
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
			case 'Disque':		
				$result = $FreeboxAPI->disques($this->getLogicalId());
				if($result!=false)			
					$return= $result;
			break;
			case 'Phone':
				$result = $FreeboxAPI->nb_appel_absence();
				if($result!=false){
					switch ($this->getLogicalId()) 
					{
						case "nbAppelsManquee":
							$return= $result['missed'];
							//$return= $result;
							break;
						case "nbAppelRecus":
							$return= $result['accepted'];
							break;
						case "nbAppelPasse":
							$return= $result['outgoing'];
							break;
						case "listAppelsManquee":
							$return= $result['list_missed'];
							break;
						case "listAppelsRecus":
							$return= $result['list_accepted'];
							break;
						case "listAppelsPasse":
							$return= $result['list_outgoing'];
							break;
						case "sonnerieDectOn":
							$FreeboxAPI->ringtone_on();
							break;
						case "sonnerieDectOff":
							$FreeboxAPI->ringtone_off();
							break;
					}
				}
			break;
			case'Reseau':
				$result=$FreeboxAPI->ReseauPing($this->getLogicalId());
				if($result!=false){
					if (isset($result['l3connectivities']))
					{
						foreach($result['l3connectivities'] as $Ip){
							if ($Ip['active']){
								if($Ip['af']=='ipv4')
									$this->setConfiguration('IPV4',$Ip['addr']);
								else
									$this->setConfiguration('IPV6',$Ip['addr']);
							}
						}
					}
					$this->setConfiguration('host_type',$result['host_type']);
					$this->save();
					if (isset($result['active'])) {
						if ($result['active'] == 'true') {
                            $this->setOrder($this->getOrder() % 1000);
                            $this->save();
                            $return = 1;
                        } else {
                            $this->setOrder($this->getOrder() % 1000 + 1000);
                            $this->save();
                            $return = 0;
                        }
					} else {
						$return=0;
					}
				}
			break;
			case'FreeboxTv':
				switch($this->getLogicalId()){
					case 'powerstat':
						$return=exec('nc -zv '.$this->getEqLogic()->getConfiguration('FREEBOX_TV_IP').' 7000 2>&1 | grep -E "open|succeeded" | wc -l');
						log::add('Freebox_OS','debug','Etat du player freebox '.$this->getEqLogic()->getConfiguration('FREEBOX_TV_IP').' '.$return);
					break;
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
		if (isset($return) && $this->execCmd() != $return){
			$this->setCollectDate(date('Y-m-d H:i:s'));
			$this->setConfiguration('doNotRepeatEvent', 1);
			$this->event($return);
			$this->getEqLogic()->refreshWidget();
			return $return;
		}
	}
}
