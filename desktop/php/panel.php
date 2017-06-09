<?php
	if (!isConnect()) {
		throw new Exception('{{401 - Accès non autorisé}}');
	}
	$eqLogics = eqLogic::byType('Freebox_OS');
?>
<div style="position : fixed;height:100%;width:15px;top:50px;left:0px;z-index:998;background-color:#f6f6f6;" id="bt_displayObjectList">
	<i class="fa fa-arrow-circle-o-right" style="color : #b6b6b6;"></i>
</div>
<div class="row row-overflow" id="div_Freebox_OS">
	<div class="col-xs-2" id="sd_objectList" style="z-index:999">
		<div class="bs-sidebar">
			<ul id="ul_object" class="nav nav-list bs-sidenav">
				<li class="nav-header">{{Freebox OS}}</li>
				<?php
					foreach ($eqLogics as $eqLogic) {
						echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName() . '</a></li>';
					}
				?>
			</ul>
		</div>
	</div>

	<div class="col-xs-10" id="div_graphiqueDisplay">
		<legend style="height: 40px;"></legend>
		<div class="row">
			<div class="col-lg-6">
				<legend>{{Mon casier}}</legend>
				<?php

					/// Require Composer AutoLoader
					require_once 'plugins/Freebox_OS/core/php/utils/Application.php';
					$app_id =trim(config::byKey('FREEBOX_SERVER_APP_ID','Freebox_OS'));
					$app_name=trim(config::byKey('FREEBOX_SERVER_APP_NAME','Freebox_OS'));
					$app_version=trim(config::byKey('FREEBOX_SERVER_APP_VERSION','Freebox_OS'));
					/// Define our application
					$App = new \alphayax\freebox\utils\Application($app_id,$app_name,$app_version);
					$App->authorize();
					$App->openSession();

					$FilterService = new \alphayax\freebox\api\v3\services\ParentalControl\Filter( $App);
					$Config = $FilterService->getConfiguration();
					print_r( $Config);

					$Filters = $FilterService->getAll();
					print_r( $Filters);

				?>
			</div>
			<div class="col-lg-6">
				<legend>{{Ma liste de vin}}</legend>
			</div>
			<div class="col-lg-6">
				<legend>{{Fiche vin}}</legend>
			</div>
		</div>
	</div>
</div>
<?php include_file('desktop', 'panel', 'js', 'Freebox_OS');?>
