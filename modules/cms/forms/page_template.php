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


$matrix['text']['text'] = 'Имя шаблона';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = 'Вы не указали имя';

$matrix['name']['text'] = 'Идентификатор';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = 'Вы не указали идентификатор';

$matrix['sort']['text'] = '{Call:Lang:modules:cms:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = 'Шаблон доступен';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

if(!empty($modify)){
	$matrix['name']['disabled'] = 1;

	$matrix['settings_caption']['text'] = 'Настройки страницы';
	$matrix['settings_caption']['type'] = 'caption';

	$matrix['publish_run_style']['text'] = 'Начало публикации';
	$matrix['publish_run_style']['type'] = 'select';
	$matrix['publish_run_style']['additional'] = array('current' => 'Выставлять текущую дату', 'fix' => 'Выставлять фиксированную дату');
	$matrix['publish_run_style']['additional_style'] = "onChange='return setTmplParams();'";

	$matrix['publish_run_correct']['pre_text'] = '<div id="run_current" style="display: none;">';
	$matrix['publish_run_correct']['text'] = 'Скорректировать на, секунд';
	$matrix['publish_run_correct']['type'] = 'text';
	$matrix['publish_run_correct']['warn_function'] = 'regExp::digit';
	$matrix['publish_run_correct']['post_text'] = '</div>';
	$values['publish_run_correct'] = 0;

	$matrix['publish_run_date']['pre_text'] = '<div id="run_fix" style="display: none;">';
	$matrix['publish_run_date']['text'] = 'Дата начала публикации';
	$matrix['publish_run_date']['type'] = 'calendar2';
	$matrix['publish_run_date']['post_text'] = '</div>';
	$values['publish_run_date'] = time();

	$matrix['publish_end_style']['text'] = 'Конец публикации';
	$matrix['publish_end_style']['type'] = 'select';
	$matrix['publish_end_style']['additional'] = array('fix' => 'Выставлять фиксированную дату', 'calc' => 'Прибавлять к дате начала публикации определенный промежуток');
	$matrix['publish_end_style']['additional_style'] = "onChange='return setTmplParams();'";

	$matrix['publish_end_date']['pre_text'] = '<div id="end_fix" style="display: none;">';
	$matrix['publish_end_date']['text'] = 'Дата окончания публикации';
	$matrix['publish_end_date']['type'] = 'calendar2';
	$matrix['publish_end_date']['post_text'] = '</div>';
	$values['publish_end_date'] = 2147000000;

	$matrix['publish_end_correct']['pre_text'] = '<div id="end_calc" style="display: none;">';
	$matrix['publish_end_correct']['text'] = 'Завершать публикацию после начала через, часов';
	$matrix['publish_end_correct']['type'] = 'text';
	$matrix['publish_end_correct']['warn_function'] = 'regExp::float';
	$matrix['publish_end_correct']['post_text'] = '</div>';

	$matrix['sort_style']['text'] = 'Стиль выставления сортировки';
	$matrix['sort_style']['type'] = 'select';
	$matrix['sort_style']['additional'] = array('fix' => 'Выставлять фиксированное значение', 'min' => 'Выставлять всегда меньше самого меньшего (начало списка)', 'max' => 'Выставлять всегда больше самого большего (конец списка)');
	$matrix['sort_style']['additional_style'] = "onChange='return setTmplParams();'";

	$matrix['sort_value']['pre_text'] = '<div id="sort_fix" style="display: none;">';
	$matrix['sort_value']['text'] = 'Значение сортировки по умолчанию';
	$matrix['sort_value']['type'] = 'text';
	$matrix['sort_value']['post_text'] = '</div>';

	$matrix['sort_correct']['pre_text'] = '<div id="sort_calc" style="display: none;">';
	$matrix['sort_correct']['text'] = 'Шаг сортировщика';
	$matrix['sort_correct']['type'] = 'text';
	$matrix['sort_correct']['warn_function'] = 'regExp::digit';
	$matrix['sort_correct']['post_text'] = '</div>';
	$values['sort_correct'] = 1;

	$matrix['show_style']['text'] = 'Уровень доступа';
	$matrix['show_style']['type'] = 'select';
	$matrix['show_style']['additional'] = array(
		'0' => '{Call:Lang:modules:cms:stranitsaned}',
		'1' => '{Call:Lang:modules:cms:dostupnaadmi}',
		'2' => '{Call:Lang:modules:cms:dostupnapolz}',
		'3' => '{Call:Lang:modules:cms:dostupnavsem}',
		'4' => '{Call:Lang:modules:cms:dostupnavsem1}'
	);
	$values['show_style'] = 3;

	$matrix['version_style']['text'] = 'По умолчанию версия';
	$matrix['version_style']['type'] = 'select';
	$matrix['version_style']['additional'] = array('1' => 'Действующая', '' => 'Не действующая');
	$matrix['version_style']['post_text'] = '<script type="text/javascript">
			function setTmplParams(){
				hideFormBlock("run_current");
				hideFormBlock("run_fix");
				hideFormBlock("end_calc");

				hideFormBlock("end_fix");
				hideFormBlock("sort_fix");
				hideFormBlock("sort_calc");

				if(ge("publish_run_style").value == "current") showFormBlock("run_current");
				else if(ge("publish_run_style").value == "fix") showFormBlock("run_fix");

				if(ge("publish_end_style").value == "calc") showFormBlock("end_calc");
				else if(ge("publish_end_style").value == "fix") showFormBlock("end_fix");

				if(ge("sort_style").value == "fix") showFormBlock("sort_fix");
				else if(ge("sort_style").value == "min" || ge("sort_style").value == "max") showFormBlock("sort_calc");
			}

			function setBlocksParams(blk){
				hideFormBlock("set_" + blk);
				hideFormBlock("system_" + blk);
				if(ge("block_style_" + blk).value == "system") showFormBlock("system_" + blk);
				else if(ge("block_style_" + blk).value == "set") showFormBlock("set_" + blk);
			}

			function setStructureParams(str, fld){
				hideFormBlock("str_set_" + str + "_" + fld);
				hideFormBlock("str_system_" + str + "_" + fld);
				if(ge("structure_style_" + str + "_" + fld).value == "system") showFormBlock("str_system_" + str + "_" + fld);
				else if(ge("structure_style_" + str + "_" + fld).value == "set") showFormBlock("str_set_" + str + "_" + fld);
			}

			setTmplParams();
		</script>';


	$matrix['tmpl_caption']['text'] = 'Взаимодействие с шаблонами сайта';
	$matrix['tmpl_caption']['type'] = 'caption';

	foreach($templates as $i => $e){
		if($e['pages']){
			$matrix['template_'.$i]['text'] = '{Call:Lang:modules:cms:dliashablona:'.Library::serialize(array($e['name'])).'}';
			$matrix['template_'.$i]['type'] = 'select';
			$matrix['template_'.$i]['additional'] = Library::array_merge(array('' => '{Call:Lang:modules:cms:nepokazyvatd}'), $e['pages']);
		}
	}


	$matrix['blocks_caption']['text'] = 'Настройки контент-блоков';
	$matrix['blocks_caption']['type'] = 'caption';

	foreach($blocks[0] as $i => $e){
		$matrix['block_caption_'.$i]['text'] = $e['text'];
		$matrix['block_caption_'.$i]['type'] = 'caption';

		$matrix['block_style_'.$i]['text'] = '';
		$matrix['block_style_'.$i]['type'] = 'select';
		$matrix['block_style_'.$i]['additional'] = array(
			'' => 'Не выставлять',
			'system' => 'Выставлять значение по служебному полю',
			'parent' => 'Выставлять значение страницы-родителя',
			'set' => 'Указать значение'
		);
		$matrix['block_style_'.$i]['additional_style'] = "onChange='return setBlocksParams(\"$i\");'";

		$matrix['val_'.$i] = $e;
		$matrix['val_'.$i]['pre_text'] = '<div id="set_'.$i.'" style="display: none;">';
		$matrix['val_'.$i]['text'] = 'Значение по умолчанию';
		$matrix['val_'.$i]['warn'] = '';
		$matrix['val_'.$i]['post_text'] = '</div>';
		$values['val_'.$i] = $blocks[1][$i];

		$matrix['sysfld_'.$i]['pre_text'] = '<div id="system_'.$i.'" style="display: none;">';
		$matrix['sysfld_'.$i]['type'] = 'select';
		$matrix['sysfld_'.$i]['text'] = 'По какому полю заполнить';
		$matrix['sysfld_'.$i]['additional'] = array(
			'name' => 'Имя страницы',
			'url' => 'URL-наименование',
			'parent' => 'Страница-родитель',
			'start' => 'Начало публикации',
			'stop' => 'Конец публикации',
			'sort' => 'Позиция в сортировке',
			'show' => 'Доступ'
		);
		$matrix['sysfld_'.$i]['post_text'] = '</div><script type="text/javascript">
				setBlocksParams("'.$i.'");
			</script>';
	}


	$matrix['structure_caption']['text'] = 'Настройки контент-структур связанных со страницей';
	$matrix['structure_caption']['type'] = 'caption';

	foreach($structures as $i => $e){
		if($e['in_page'] && isset($structureMatrix[$i])){
			$matrix['structure_caption_'.$i]['text'] = $e['text'];
			$matrix['structure_caption_'.$i]['type'] = 'caption';

			$matrix['structure_on_'.$i]['text'] = 'По умолчанию внесение записей в структуру включено';
			$matrix['structure_on_'.$i]['type'] = 'checkbox';

			if($structureMatrix[$i]){
				$matrix['structure_caption2_'.$i]['text'] = '';
				$matrix['structure_caption2_'.$i]['type'] = 'caption';

				foreach($structureMatrix[$i] as $i1 => $e1){
					$matrix['structure_style_'.$i.'_'.$i1]['text'] = $e1['matrix']['text'];
					$matrix['structure_style_'.$i.'_'.$i1]['type'] = 'select';
					$matrix['structure_style_'.$i.'_'.$i1]['additional'] = array(
						'' => 'Не выставлять',
						'system' => 'Выставлять значение по служебному полю',
						'set' => 'Указать значение'
					);
					$matrix['structure_style_'.$i.'_'.$i1]['additional_style'] = "onChange='return setStructureParams(\"$i\", \"$i1\");'";

					$matrix['str_val_'.$i.'_'.$i1] = $e1['matrix'];
					$matrix['str_val_'.$i.'_'.$i1]['pre_text'] = '<div id="str_set_'.$i.'_'.$i1.'" style="display: none;">';
					$matrix['str_val_'.$i.'_'.$i1]['text'] = 'Значение по умолчанию для "'.$e1['matrix']['text'].'"';
					$matrix['str_val_'.$i.'_'.$i1]['warn'] = '';
					$matrix['str_val_'.$i.'_'.$i1]['post_text'] = '</div>';
					$values['str_val_'.$i.'_'.$i1] = $e1['value'];

					$matrix['str_sysfld_'.$i.'_'.$i1]['pre_text'] = '<div id="str_system_'.$i.'_'.$i1.'" style="display: none;">';
					$matrix['str_sysfld_'.$i.'_'.$i1]['type'] = 'select';
					$matrix['str_sysfld_'.$i.'_'.$i1]['text'] = 'По какому полю заполнить "'.$e1['matrix']['text'].'"';
					$matrix['str_sysfld_'.$i.'_'.$i1]['additional'] = array(
						'name' => 'Имя страницы',
						'url' => 'URL-наименование',
						'parent' => 'Страница-родитель',
						'start' => 'Начало публикации',
						'stop' => 'Конец публикации',
						'sort' => 'Позиция в сортировке',
						'show' => 'Доступ'
					);
					$matrix['str_sysfld_'.$i.'_'.$i1]['post_text'] = '</div><script type="text/javascript">
							setStructureParams("'.$i.'", "'.$i1.'");
						</script>';
				}
			}
		}
	}


	$matrix['body_caption']['text'] = 'Тело статьи';
	$matrix['body_caption']['type'] = 'caption';

	$matrix['body_pre']['text'] = 'Текст включаемый до статьи';
	$matrix['body_pre']['type'] = 'textarea';
	$matrix['body_pre']['template'] = 'big';

	$matrix['body_post']['text'] = 'Текст включаемый после статьи';
	$matrix['body_post']['type'] = 'textarea';
	$matrix['body_post']['template'] = 'big';
}

?>