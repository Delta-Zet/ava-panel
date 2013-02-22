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


if(empty($extra) || !$pageBlocks) return;

$matrix['in_page_caption']['text'] = '{Call:Lang:modules:cms:pravilazapol}';
$matrix['in_page_caption']['type'] = 'caption';

$matrix['in_page_ins_style']['text'] = '{Call:Lang:modules:cms:stilzapolnen}';
$matrix['in_page_ins_style']['type'] = 'select';
$matrix['in_page_ins_style']['additional_style'] = "onChange='switchByValue(\"in_page_ins_style\", {blocks:{forField: 1, forTemplates: 1, forAuto: 1}, 1:{forField: 1}, 2:{forAuto: 1}, 4:{forTemplates: 1}});'";
$matrix['in_page_ins_style']['additional'] = array(
	'0' => '{Call:Lang:modules:cms:nezapolniat}',
	'1' => '{Call:Lang:modules:cms:zapolniatpop}',
	'2' => '{Call:Lang:modules:cms:formirovatav}',
	'3' => '{Call:Lang:modules:cms:zapolniatpro}',
	'4' => '{Call:Lang:modules:cms:nastroitvzav}'
);

if($allPageBlocks){
	$matrix['blocks']['text'] = '{Call:Lang:modules:cms:ukazhitepoka}';
	$matrix['blocks']['type'] = 'select';
	$matrix['blocks']['additional'] = $allPageBlocks;
	$matrix['blocks']['pre_text'] = '<div id="forField" style="display: none">';

	if($inPageUpd){
		$matrix['in_page_update']['text'] = '{Call:Lang:modules:cms:obnovliatzap}';
		$matrix['in_page_update']['type'] = 'checkbox';
		$matrix['in_page_update']['post_text'] = '</div>';
		$values['in_page_update'] = 1;
	}
	else $matrix['blocks']['post_text'] = '</div>';

	$j1 = 0;
	$j2 = 0;

	foreach($pageBlocks as $i => $e){
		if(!empty($e['pages'])){
			$matrix['in_page_'.$i.'_caption']['text'] = '{Call:Lang:modules:cms:pravilazapol}';
			$matrix['in_page_'.$i.'_caption']['type'] = 'caption';
			if(empty($first)) $first = 'in_page_'.$i.'_caption';

			foreach($e['pages'] as $i1 => $e1){
				if($e1){
					$matrix['in_page_'.$i.'_caption']['text'] = '{Call:Lang:modules:cms:dliashablona:'.Library::serialize(array($e['name'])).'}';
					$matrix['in_page_'.$i.'_caption']['type'] = 'caption';

					$matrix['in_page_ins_style_'.$i.'_'.$i1]['text'] = '{Call:Lang:modules:cms:dliastranits1:'.Library::serialize(array($i1)).'}';
					$matrix['in_page_ins_style_'.$i.'_'.$i1]['type'] = 'select';
					$matrix['in_page_ins_style_'.$i.'_'.$i1]['additional_style'] = "onChange='switchByValue(\"in_page_ins_style_{$i}_{$i1}\", {blocks:{forField_{$j1}_{$j2}: 1, forAuto_{$j1}_{$j2}: 1}, 1:{forField_{$j1}_{$j2}: 1}, 2:{forAuto_{$j1}_{$j2}: 1}});'";
					$matrix['in_page_ins_style_'.$i.'_'.$i1]['additional'] = array(
						'0' => '{Call:Lang:modules:cms:nezapolniat}',
						'1' => '{Call:Lang:modules:cms:zapolniatpop}',
						'2' => '{Call:Lang:modules:cms:formirovatav}',
						'3' => '{Call:Lang:modules:cms:zapolniatpro}',
					);

					$matrix['blocks_'.$i.'_'.$i1]['text'] = '{Call:Lang:modules:cms:ukazhitepoka1:'.Library::serialize(array($i1)).'}';
					$matrix['blocks_'.$i.'_'.$i1]['type'] = 'select';
					$matrix['blocks_'.$i.'_'.$i1]['additional'] = $e1;
					$matrix['blocks_'.$i.'_'.$i1]['pre_text'] = '<div id="forField_'.$j1.'_'.$j2.'" style="display: none">';
					$last = 'blocks_'.$i.'_'.$i1;

					if($inPageUpd){
						$matrix['in_page_update_'.$i.'_'.$i1]['text'] = '{Call:Lang:modules:cms:obnovliatzap}';
						$matrix['in_page_update_'.$i.'_'.$i1]['type'] = 'checkbox';
						$values['in_page_update_'.$i.'_'.$i1] = 1;
						$last = 'in_page_update_'.$i.'_'.$i1;
					}

					$matrix[$last]['post_text'] = '</div>';

					$matrix['form_as_'.$i.'_'.$i1]['text'] = '{Call:Lang:modules:cms:formirovatka}';
					$matrix['form_as_'.$i.'_'.$i1]['type'] = 'select';
					$matrix['form_as_'.$i.'_'.$i1]['pre_text'] = '<div id="forAuto_'.$j1.'_'.$j2.'" style="display: none">';

					$matrix['form_as_'.$i.'_'.$i1]['additional'] = array(
						'link' => '{Call:Lang:modules:cms:ssylku}',
						'parent_id' => '{Call:Lang:modules:cms:idroditelia}',
						'date' => 'Дату начала отображения страницы',
						'ident' => '{Call:Lang:modules:cms:identifikato}',
						'text' => '{Call:Lang:modules:cms:imiazapisi}',
						'sort' => '{Call:Lang:modules:cms:indekssortir}',
						'show' => '{Call:Lang:modules:cms:indikatoroto}'
					);

					$last = 'form_as_'.$i.'_'.$i1;
					$matrix['form_as_'.$i.'_'.$i1]['post_text'] = '</div><script type="text/javascript">
						switchByValue("in_page_ins_style_'.$i.'_'.$i1.'", {blocks:{forField_'.$j1.'_'.$j2.': 1, forAuto_'.$j1.'_'.$j2.': 1}, 1:{forField_'.$j1.'_'.$j2.': 1}, 2:{forAuto_'.$j1.'_'.$j2.': 1}});
					</script>';

					$j2 ++;
				}
			}

			$j1 ++;
		}
	}

	if(!empty($first)){
		$matrix[$first]['pre_text'] = '<div id="forTemplates" style="display: none">';
		$matrix[$last]['post_text'] .= '</div>';
	}
}
else{
	unset($matrix['in_page_ins_style']['additional'][1], $matrix['in_page_ins_style']['additional'][4]);
}

$matrix['form_as']['text'] = '{Call:Lang:modules:cms:formirovatka}';
$matrix['form_as']['type'] = 'select';
$matrix['form_as']['pre_text'] = '<div id="forAuto" style="display: none;">';
$matrix['form_as']['post_text'] = '</div><script type="text/javascript">
	switchByValue("in_page_ins_style", {blocks:{forField: 1, forTemplates: 1, forAuto: 1}, 1:{forField: 1}, 2:{forAuto: 1}, 4:{forTemplates: 1}});
</script>';

$matrix['form_as']['additional'] = array(
	'link' => '{Call:Lang:modules:cms:ssylku}',
	'parent_id' => '{Call:Lang:modules:cms:idroditelia}',
	'date' => 'Дату начала отображения страницы',
	'ident' => '{Call:Lang:modules:cms:identifikato}',
	'text' => '{Call:Lang:modules:cms:imiazapisi}',
	'sort' => '{Call:Lang:modules:cms:indekssortir}',
	'show' => '{Call:Lang:modules:cms:indikatoroto}'
);

?>