<?php
try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'FreeboxAPI', 'class', 'Freebox_OS');
	include_file('core', 'authentification', 'php');
	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}	
	$FreeboxAPI= new FreeboxAPI();
	switch(init('action')){			
		case 'sendToBdd':
			config::save('FREEBOX_SERVER_TRACK_ID', init('track_id'),'Freebox_OS');
			config::save('FREEBOX_SERVER_APP_TOKEN', init('app_token'),'Freebox_OS');
			ajax::success(true);
		break;		
		case 'connect':
			ajax::success($FreeboxAPI->track_id());
		break;
		case 'ask_track_authorization':
			ajax::success($FreeboxAPI->ask_track_authorization());
		break;
		case 'SearchHomeAdapters':
			ajax::success($FreeboxAPI->getHomeAdapters());
		break;
		case 'SearchReseau':
			ajax::success($FreeboxAPI->freeboxPlayerPing());
		break;
		case 'SearchDisque':
			ajax::success($FreeboxAPI->disques());
		break;
		case 'AddPortForwarding':
			$PortForwarding=array(
			"enabled"		=> 	init('enabled'),
			"comment"		=> 	init('comment'),
			"lan_port"		=> 	init('lan_port'),
			"wan_port_end"	=> 	init('wan_port_end'),
			"wan_port_start"=> 	init('wan_port_start'),
			"lan_ip" 		=>	init('lan_ip'),
			"ip_proto" 		=> 	init('ip_proto'),
			"src_ip"		=> 	init('src_ip'));
			ajax::success();
		break;
		case 'PortForwarding':
			ajax::success();
		break;
		case 'WakeOnLAN':
			$Commande=cmd::byId(init('id'));
			if(is_object($Commande)){
				$Mac=str_replace ('ether-','',$Commande->getLogicalId());
				ajax::success($FreeboxAPI->WakeOnLAN($Mac));
			}
			ajax::success(false);
		break;
		case 'sendCmdPlayer':
			$Player=eqLogic::byId(init('id'));
			if(is_object($Player)){
				$Cmd=$Player->getCmd('action',init('cmd'));
				if(is_object($Cmd))
					ajax::success($Cmd->execute());
			}
			ajax::success(false);
		break;
		case 'getAirMediaRecivers':
			ajax::success($FreeboxAPI->airmediaReceivers());
		break;
		case 'setAirMediaReciver':
			$cmd=cmd::byId(init('id'));
			if(is_object($cmd)){
				$cmd->setCollectDate('');
				$cmd->event(init('value'));
				ajax::success(true);
			}
			ajax::success(false);
		break;
		default:
			ajax::success($FreeboxAPI->getTiles());
		break;
	}	
	throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} 
catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
?>
