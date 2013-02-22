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


$matrix['name']['text'] = '{Call:Lang:modules:cms:identifikato}';
$matrix['name']['comment'] = '{Call:Lang:modules:cms:znacheniebud}';

$matrix['text']['text'] = '{Call:Lang:modules:cms:imiabloka}';
$matrix['text']['comment'] = '{Call:Lang:modules:cms:budetvyveden}';

$matrix['type']['text'] = '{Call:Lang:modules:cms:tipbloka}';
$matrix['type']['additional'] = array(
	'text' => '{Call:Lang:modules:cms:tekstovoepol}',
	'textarea' => '{Call:Lang:modules:cms:tekstovaiaob}',
	'select' => '{Call:Lang:modules:cms:vypadaiushch}',
	'multiselect' => '{Call:Lang:modules:cms:vypadaiushch1}',
	'radio' => '{Call:Lang:modules:cms:radioknopka}',
	'checkbox_array' => '{Call:Lang:modules:cms:spisokiznesk}',
	'file' => '{Call:Lang:modules:cms:zagruzkafajl}'
);

$matrix['show']['text'] = '{Call:Lang:modules:cms:sposobraboty}';
$matrix['show']['type'] = 'select';
$matrix['show']['additional'] = array(
	'0' => '{Call:Lang:modules:cms:neispolzuets}',
	'1' => '{Call:Lang:modules:cms:otobrazhaets}',
	'2' => '{Call:Lang:modules:cms:dliakazhdojs}',
	'3' => '{Call:Lang:modules:cms:nakazhdojstr}',
	'4' => '{Call:Lang:modules:cms:vystavitpers}'
);
$values['show'] = 1;

if(!empty($templates)){
	$matrix['tmpl_caption']['text'] = '{Call:Lang:modules:cms:nastrojkipos}';
	$matrix['tmpl_caption']['type'] = 'caption';
	$matrix['tmpl_caption']['pre_text'] = '<div id="tmpl_settings" style="display: none">';

	$matrix['show']['additional_style'] = 'onChange="if(this.value == 4) showFormBlock(\'tmpl_settings\'); else hideFormBlock(\'tmpl_settings\');"';

	foreach($templates as $i => $e){
		if(!empty($e['pages'])){
			$matrix['tmpl_caption_'.$i]['text'] = '{Call:Lang:modules:cms:shablon:'.Library::serialize(array($e['name'])).'}';
			$matrix['tmpl_caption_'.$i]['type'] = 'caption';

			foreach($e['pages'] as $i1 => $e1){
				$last = 'show_'.$i.'_'.$i1;
				$matrix['show_'.$i.'_'.$i1]['text'] = '{Call:Lang:modules:cms:dliastranits:'.Library::serialize(array($i1, $e1)).'}';
				$matrix['show_'.$i.'_'.$i1]['type'] = 'select';
				$matrix['show_'.$i.'_'.$i1]['additional'] = array(
					'0' => '{Call:Lang:modules:cms:vsegdaskryto}',
					'1' => '{Call:Lang:modules:cms:otobrazhaets}',
					'2' => '{Call:Lang:modules:cms:dliakazhdojs}',
					'3' => '{Call:Lang:modules:cms:nakazhdojstr}'
				);
			}
		}
	}

	if(!empty($last)){
		$matrix[$last]['post_text'] = '</div>'.
			'<script type="text/javascript">'."\n".'if(document.getElementById(\'show\').value == \'4\') showFormBlock(\'tmpl_settings\');'."\n".'</script>';
	}
}

?>