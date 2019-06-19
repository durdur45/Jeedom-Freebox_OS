<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_install() {	
	Freebox_OS::CreateArchi();
}
function Freebox_OS_update() {
	log::add('Freebox_OS','debug','Lancement du script de mise à jour'); 
	foreach(eqLogic::byLogicalId('FreeboxTv','Freebox_OS',true) as $eqLogic){
		$eqLogic->remove();
	}
	Freebox_OS::CreateArchi();
	log::add('Freebox_OS','debug','Fin du script de mise à jour');
}
function Freebox_OS_remove() {
}
?>
