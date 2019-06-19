<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_install() {	
	Freebox_OS::CreateArchi();
}
function Freebox_OS_update() {
	Freebox_OS::CreateArchi();
	foreach(eqLogic::byLogicalId('FreeboxPlayer','Freebox_OS',true) as $eqLogic){
		$eqLogic->remove();
	}
}
function Freebox_OS_remove() {
}
?>
