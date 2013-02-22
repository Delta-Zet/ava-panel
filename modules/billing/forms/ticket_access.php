<?


	/******************************************************************************************************
	*** Package: AVA-Panel Version 3.0
	*** Copyright (c) 2006, Anton A. Rassypaeff. All rights reserved
	*** License: GNU General Public License v3
	*** Author: Anton A. Rassypaeff | Рассыпаев Антон Александрович
	*** Contacts:
	***   Site: http://ava-panel.ru
	***   E-mail: manage@ava-panel.ru
	******************************************************************************************************/


$matrix['access_caption_'.$mod]['text'] = $modName;
$matrix['access_caption_'.$mod]['type'] = 'caption';

$matrix['access_'.$mod]['text'] = '{Call:Lang:modules:billing:dostupimeiut}';
$matrix['access_'.$mod]['type'] = 'radio';
$matrix['access_'.$mod]['additional'] = array(
	'no' => '{Call:Lang:modules:billing:nikto}',
	'all' => '{Call:Lang:modules:billing:vseklienty}',
	'level' => '{Call:Lang:modules:billing:vzavisimosti}',
	'services' => '{Call:Lang:modules:billing:vzavisimosti1}'
);
$values['access_'.$mod] = 'no';

$matrix['access_'.$mod]['additional_style'] = array(
	'no' => 'onClick="hideFormBlock(\'forServices_'.$mod.'\'); hideFormBlock(\'forLevels_'.$mod.'\');"',
	'all' => 'onClick="hideFormBlock(\'forServices_'.$mod.'\'); hideFormBlock(\'forLevels_'.$mod.'\');"',
	'level' => 'onClick="hideFormBlock(\'forServices_'.$mod.'\'); showFormBlock(\'forLevels_'.$mod.'\');"',
	'services' => 'onClick="showFormBlock(\'forServices_'.$mod.'\'); hideFormBlock(\'forLevels_'.$mod.'\');"'
);

$first = false;

foreach($services as $i => $e){
	$matrix['service_caption_'.$mod.'_'.$i]['text'] = '{Call:Lang:modules:billing:usluga:'.Library::serialize(array($e)).'}';
	$matrix['service_caption_'.$mod.'_'.$i]['type'] = 'caption';
	if(!$first) $first = 'service_caption_'.$mod.'_'.$i;

	$matrix['access_service_'.$mod.'_'.$i]['text'] = '{Call:Lang:modules:billing:dostup}';
	$matrix['access_service_'.$mod.'_'.$i]['type'] = 'radio';
	$matrix['access_service_'.$mod.'_'.$i]['additional'] = array(
		'no' => '{Call:Lang:modules:billing:net}',
		'all' => '{Call:Lang:modules:billing:estuvsekh}'
	);

	$matrix['access_service_'.$mod.'_'.$i]['additional_style'] = array(
		'no' => 'onClick="hideFormBlock(\'forPkgs_'.$mod.'_'.$i.'\');"',
		'all' => 'onClick="hideFormBlock(\'forPkgs_'.$mod.'_'.$i.'\');"'
	);
	$values['access_service_'.$mod.'_'.$i] = 'no';

	$last = 'access_service_'.$mod.'_'.$i;
	if(!empty($packages[$i])){
		$matrix['access_service_pkgs_'.$mod.'_'.$i]['text'] = '{Call:Lang:modules:billing:polzovatelit}';
		$matrix['access_service_pkgs_'.$mod.'_'.$i]['type'] = 'checkbox_array';
		$matrix['access_service_pkgs_'.$mod.'_'.$i]['additional'] = $packages[$i];
		$matrix['access_service_pkgs_'.$mod.'_'.$i]['pre_text'] = '<div id="forPkgs_'.$mod.'_'.$i.'" style="display: none;">';
		$matrix['access_service_pkgs_'.$mod.'_'.$i]['post_text'] = '</div><script type="text/javascript">
			if(document.getElementById("access_service_'.$mod.'_'.$i.'_pkgs").checked) showFormBlock(\'forPkgs_'.$mod.'_'.$i.'\');
		</script>';

		$last = 'access_service_pkgs_'.$mod.'_'.$i;
		$matrix['access_service_'.$mod.'_'.$i]['additional']['pkgs'] = '{Call:Lang:modules:billing:nastroitpota}';
		$matrix['access_service_'.$mod.'_'.$i]['additional_style']['pkgs'] = 'onClick="showFormBlock(\'forPkgs_'.$mod.'_'.$i.'\');"';
	}
}

if($first){
	$matrix[$first]['pre_text'] = '<div id="forServices_'.$mod.'" style="display: none;">';
	$matrix[$last]['post_text'] = isset($matrix[$last]['post_text']) ? $matrix[$last]['post_text'].'</div>' : '</div>';
}
else{
	$matrix['access_'.$mod]['post_text'] = '<div id="forServices_'.$mod.'" style="display: none;"></div>';
}

$matrix['levels_caption_'.$mod]['text'] = '{Call:Lang:modules:billing:urovniklient}';
$matrix['levels_caption_'.$mod]['type'] = 'caption';
$matrix['levels_caption_'.$mod]['pre_text'] = '<div id="forLevels_'.$mod.'" style="display: none;">';

$matrix['levels_'.$mod]['text'] = '';
$matrix['levels_'.$mod]['type'] = 'checkbox_array';
$matrix['levels_'.$mod]['additional'] = $levels;
$matrix['levels_'.$mod]['post_text'] = '</div><script type="text/javascript">
	if(document.getElementById("access_'.$mod.'_level").checked) showFormBlock(\'forLevels_'.$mod.'\');
	else if(document.getElementById("access_'.$mod.'_services").checked) showFormBlock(\'forServices_'.$mod.'\');
</script>';

?>