<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function Freebox_OS_install() {	
	config::save('FREEBOX_SERVER_IP', config::byKey('FREEBOX_SERVER_IP','FreeboxOS'),'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_TOKEN', config::byKey('FREEBOX_SERVER_APP_TOKEN','FreeboxOS'),'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_ID', config::byKey('FREEBOX_SERVER_APP_ID','FreeboxOS'),'Freebox_OS');
	config::save('FREEBOX_SERVER_TRACK_ID', config::byKey('FREEBOX_SERVER_TRACK_ID','FreeboxOS'),'Freebox_OS');
	foreach(eqLogic::byType('FreeboxOS') as $eqLogic){
		foreach($eqLogic->getCmd() as $cmd){			
			$cmd->setEqType('Freebox_OS');
			$cmd->save();
		}
		$eqLogic->setEqType_name('Freebox_OS');
		$eqLogic->save();
	}
	Freebox_OS::CreateArchi();
}

function Freebox_OS_update() {
	config::save('FREEBOX_SERVER_IP', config::byKey('FREEBOX_SERVER_IP','FreeboxOS'),'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_TOKEN', config::byKey('FREEBOX_SERVER_APP_TOKEN','FreeboxOS'),'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_ID', config::byKey('FREEBOX_SERVER_APP_ID','FreeboxOS'),'Freebox_OS');
	config::save('FREEBOX_SERVER_TRACK_ID', config::byKey('FREEBOX_SERVER_TRACK_ID','FreeboxOS'),'Freebox_OS');
	foreach(eqLogic::byType('FreeboxOS') as $eqLogic){
		foreach($eqLogic->getCmd() as $cmd){			
			$cmd->setEqType('Freebox_OS');
			$cmd->save();
		}
		$eqLogic->setEqType_name('Freebox_OS');
		$eqLogic->save();
	}
	Freebox_OS::CreateArchi();    
}

function Freebox_OS_remove() {
}

?>
