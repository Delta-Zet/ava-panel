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


class mod_admin_cms extends gen_cms{


	/********************************************************************************************************************************************************************

																				Страницы

	*********************************************************************************************************************************************************************/

	protected function func_pages(){
		/*
			Функция создает страничку, при этом позволяя установить:
				1. Контентное наполнение
				2. Содержимое всех блоков которые предусмотрены настройками
				3. Добавить вывод каких либо модулей в позициях
				4. Открытие / скрытие страницы
				5. Указание произвольного URL для нее
		*/

		$pages = Library::array_merge(array('' => '{Call:Lang:modules:cms:net}'), $this->getPages());
		$pid = empty($this->values['parent']) ? '' : $this->values['parent'];

		if($pid){
			$this->funcName = '{Call:Lang:modules:cms:podstranitsy:'.Library::serialize(array($pages[$pid])).'}';
			$this->pathFunc = 'pages';
		}

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'pages',
						'pagesNew',
						array('caption' => '{Call:Lang:modules:cms:novaiastrani}')
					),
					'pages',
					array('pages' => $pages, 'pageTemplates' => $this->getPageTemplatesList())
				),
				array('parent' => $pid),
				array(),
				'big'
			)
		);

		$p = $this->DB->getPrefix();
		$t1 = $p.'pages';
		$t2 = $p.'versions';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'pages_list',
					array(
						'req' => "SELECT t1.id, t1.date, t1.start, t1.stop, t1.name, t1.url, t1.show, t1.sort, t2.version_date, t2.version_name ".
							"FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.version_id=t2.id WHERE t1.parent='$pid' ".
							(empty($this->values['in_search']) ? "ORDER BY t1.sort" : ""),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:cms:skryt}',
							'unsuspend1' => '{Call:Lang:modules:cms:otkrytdliaad}',
							'unsuspend2' => '{Call:Lang:modules:cms:otkrytdliapo}',
							'unsuspend3' => '{Call:Lang:modules:cms:otkrytdliavs}',
							'unsuspend4' => '{Call:Lang:modules:cms:otkrytdliavs1}',
							'delete' => '{Call:Lang:modules:cms:udalit}'
						),
						'actions' => array(
							'name' => 'pagesData'
						),
						'action' => 'pagesActions',
						'searchForm' => array(
							'searchFields' => array(
								'name' => '{Call:Lang:modules:cms:nazvaniiu}',
								'url' => 'URL',
								'body' => '{Call:Lang:modules:cms:tekstuvstate}',
								'date' => 'Дата создания',
								'start' => '{Call:Lang:modules:cms:dateopubliko}',
								'stop' => 'Дата снятия с публикации',
								'show' => ''
							),
							'orderFields' => array(
								'name' => '{Call:Lang:modules:cms:imeni}',
								'url' => 'URL',
								'date' => 'дате создания',
								'start' => 'дате опубликования',
								'stop' => 'дате снятия с публикации',
							),
							'searchMatrix' => array(
								'date' => array('type' => 'calendar'),
								'start' => array('type' => 'calendar'),
								'stop' => array('type' => 'calendar'),
							),
							'searchPrefix' => 't1',
						)
					),
					array(
						'caption' => '{Call:Lang:modules:cms:vsestranitsy}'
					)
				),
				'big'
			)
		);
	}

	protected function func_pagesNew(){
		/*
			Создание новой страницы. Здесь только создается запись из названия и URL. Все прочие данные включая контентное наполнение вносятся через модификацию.
		*/

		$this->isUniq('pages', array('name' => '{Call:Lang:modules:cms:takoeimiastr}', 'url' => '{Call:Lang:modules:cms:takoeurlnaim}'));
		if(!$this->check()) return false;

		$id = $this->newPage($this->values['name'], $this->values['url'], $this->values['page_template'], $this->values['parent']);
		$this->setAdminStat('new', '{Call:Lang:modules:cms:dobavlenastr:'.Library::serialize(array($this->values['name'])).'}', 'pages', $id);
		$this->redirect('pagesData&id='.$id);
	}

	protected function func_pagesData(){
		/*
			Управление контентным наполнением страницы. Устанавливается:
			1. Название страницы
			2. Все промо-поля которые предусмотрены настройками
			3. Контентное содержание основного блока
			4. URL-наименование страницы
			5. Псевдо-URL
			6. Состояние страницы, как то вкл/выкл, доступность для просмотра по группам пользователей, шаблон и др.

			Форма состоит из 3 основных блоков:
			1. Настройки. Этот блок содержит скрываемую форму содержащую настройки страницы. Форма статична.
			2. Контент-блоки. Динамичная форма с контент-блоками которые используются на странице
			3. Контент. Т.е. основное содержимое.

			Кроме того возможно включение дополнительных блоков на правах плагинов. В этом случае они подцепляются к блоку "Дополнительно", а введенные настройки
			сохраняются в vars в субмассивы соответствующие каждый своему плагину. Кроме того плагином может вызываться дополнительно сохранение данных в какой-либо еще базе

			При получении значений сливаются перезаписывая друг друга:
				- значения по умолчанию для блоков и структур
				- значения по умолчанию создаваемые на основании шаблона заполнения страницы
				- значения указанные для этой версии
		 */

		$id = $this->values['id'];
		$data = $this->getPageValuesById($id);
		$versionId = isset($this->values['versionId']) ? $this->values['versionId'] : $data['version_id'];

		$pgList = $this->getTemplatePages();
		$tags = $this->getTagNames();

		$this->funcName = '{Call:Lang:modules:cms:stranitsa:'.Library::serialize(array($data['name'])).'}';
		$this->pathFunc = 'pages';

		list($blkMatrix, $blkValues) = $this->getMatrix4ContentBlocks($pgList);
		list($strMatrix, $strValues) = $this->getMatrix4Structures($this->values['id']);

		$values = array('version_on' => ($versionId == $data['version_id'] ? 1 : 0), 'version_date' => time(), 'name' => $data['name'], 'url' => $data['url'], 'parent' => $data['parent']);
		$values = Library::array_merge($values, Library::array_merge($blkValues, $strValues));
		$values = Library::array_merge($values, $versionId ? $this->getVersionValues($versionId) : $this->getDefaultPageValues($this->values['id'], $data['page_template']));

		$fObj = $this->newForm('pagesData2', 'pagesData2', array('caption' => '{Call:Lang:modules:cms:stranitsa1:'.Library::serialize(array($data['name'], ', версия от '.Dates::dateTime($values['version_date']))).'}'));
		$this->addFormBlock($fObj, 'pages_content', array('simpleHtml' => !empty($values['simple_html'])));

		$fObj->setParam('caption0', '{Call:Lang:modules:cms:nastrojki}');
		$this->addFormBlock($fObj, 'pages_settings', array('templates' => $pgList, 'pages' => Library::array_merge(array('' => '{Call:Lang:modules:cms:net}'), $this->getPages($id)), 'tags' => $tags), array(), 'block0');

		$fObj->setParam('caption1', '{Call:Lang:modules:cms:kontentbloki}');
		$this->addFormBlock($fObj, $blkMatrix, array(), array(), 'block1');

		$fObj->setParam('caption2', '{Call:Lang:modules:cms:zapisivkonte}');
		$this->addFormBlock($fObj, $strMatrix, array(), array(), 'block2');
		$this->setContent($this->getFormText($fObj, $values, array('page_id' => $id), 'multiblock2'));

		$this->setContent(
			$this->getListText(
				$this->newList(
					'versions_list',
					array(
						'req' => array('versions', array('id', 'page_id', 'version_name', 'version_date', 'name', 'vars', 'params', 'show'), "`page_id`='$id'", "`version_date` DESC"),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:cms:skryt}',
							'unsuspend1' => '{Call:Lang:modules:cms:otkrytdliaad}',
							'unsuspend2' => '{Call:Lang:modules:cms:otkrytdliapo}',
							'unsuspend3' => '{Call:Lang:modules:cms:otkrytdliavs}',
							'unsuspend4' => '{Call:Lang:modules:cms:otkrytdliavs1}',
							'delete' => '{Call:Lang:modules:cms:udalit}'
						),
						'actions' => array(
							'setMain' => 'setMain&pageId='.$id
						),
						'action' => 'versionActions&id='.$id,
						'searchForm' => array(
							'searchFields' => array(
								'body' => '{Call:Lang:modules:cms:tekstuvstate}',
								'name' => '{Call:Lang:modules:cms:imenistranit}',
								'version_name' => '{Call:Lang:modules:cms:imeniversii}',
								'version_date' => '{Call:Lang:modules:cms:datesozdanii}',
								'tags' => 'Теги',
								'show' => ''
							),
							'orderFields' => array(
								'version_date' => '{Call:Lang:modules:cms:datesozdanii1}',
								'version_name' => '{Call:Lang:modules:cms:imeniversii1}',
								'name' => '{Call:Lang:modules:cms:imenistranit1}',
							),
							'searchMatrix' => array(
								'version_date' => array('type' => 'calendar'),
								'tags' => array('type' => 'select', 'additional' => library::array_merge(array('' => 'Нет'), Library::concatPrefixArray($tags, ',', ','))),
							),
							'searchParams' => array(
								'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$id
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:cms:versiistrani}',
						'pageParams' => $data
					)
				),
				'big'
			)
		);
	}

	protected function func_pagesData2(){
		/*
			Вносит содержимое страницы в базу
		*/

		$this->isUniq('pages', array('name' => '{Call:Lang:modules:cms:takoeimiastr}', 'url' => '{Call:Lang:modules:cms:takoeurlnaim}'), $this->values['page_id'], " AND `parent`='{$this->values['parent']}'");
		if(!$this->check()) return false;
		$vId = $this->newVersion($this->values['page_id']);

		if(!empty($this->values['version_on'])) $this->setDefaultVersion($vId);
		$this->setAdminStat('modify', '{Call:Lang:modules:cms:izmenenastra:'.Library::serialize(array($this->values['name'], $vId)).'}', 'pages', $vId);
		$this->refresh('pagesData&id='.$this->values['page_id'].'&versionId='.$vId);
	}

	protected function func_setMain(){
		$this->setDefaultVersion($this->values['id']);
		$this->refresh('pagesData&id='.$this->values['pageId'].'&versionId='.$this->values['id']);
	}

	protected function func_pagesActions(){
		/*
			Удаляет все версии всех выбранных страниц
		*/

		if(empty($this->values['entry'])){
			$this->back('pages', 'Не отмечено ни одной записи');
			return false;
		}

		$this->refresh('pages');
		if($this->values['action'] == 'delete') $this->DB->Del(array('versions', $this->getEntriesWhere(false, 'page_id')));
		else $this->massVersionActions($this->DB->columnFetch(array('pages', 'id', 'version_id', $this->getEntriesWhere())));

		$this->massPageActions($this->values['entry']);
		return true;
	}

	protected function func_versionActions(){
		$this->refresh('pagesData&id='.$this->values['id']);
		$this->massPageActions($this->DB->columnFetch(array('pages', 'version_id', 'id', $this->getEntriesWhere(false, 'version_id'))));
		$this->massVersionActions($this->values['entry']);
		return true;
	}

	private function massPageActions($pages){
		/*
			Массовые действия над страницами
		*/

		$return = $this->typeActions(
			'pages',
			'',
			array(
				'unsuspend1' => array('show' => '1'),
				'unsuspend2' => array('show' => '2'),
				'unsuspend3' => array('show' => '3'),
				'unsuspend4' => array('show' => '4'),
			),
			'',
			'id',
			$pages
		);

		if($return){
			$pFilter = $this->getEntriesWhere(false, 'page_id');
			$structEntries = array();
			foreach($this->DB->columnFetch(array('structure_pages', '*', '', $pFilter)) as $i => $e){
				$structEntries[$e['structure_id']][$e['entry_id']] = $e['page_id'];
			}

			$structures = $this->DB->columnFetch(array('structures', 'table', 'id', $this->getEntriesWhere($structEntries)." AND `type`='internal'"));
			foreach($structures as $i => $e){
				$this->typeActions($e, '', array('unsuspend1' => array('show' => '1'), 'unsuspend2' => array('show' => '2'), 'unsuspend3' => array('show' => '3'), 'unsuspend4' => array('show' => '4')), '', 'id', $structEntries[$i]);
			}

			if($this->values['action'] == 'delete') $this->DB->Del(array('structure_pages', $pFilter));
		}

		return $return;
	}

	private function massVersionActions($versions){
		/*
			Массовые действия над версиями
		*/

		return $this->typeActions('versions', '', array('unsuspend1' => array('show' => '1'), 'unsuspend2' => array('show' => '2'), 'unsuspend3' => array('show' => '3'), 'unsuspend4' => array('show' => '4')), '', 'id', $versions);
	}


	/********************************************************************************************************************************************************************

													Создание контент структур (циклически создаваемых структур информации)

	*********************************************************************************************************************************************************************/

	protected function func_structure(){
		/*
			Структура может строиться:
			1. На основании определенной информационной таблицы
			2. На основании записи в таблице content_structures

			Может быть установлена глубина рекурсии структуры (по умолчанию 1 - т.е. без рекурсии), а также для варианта 1 указываться поле таблицы по которому
			строиться рекурсия.

			$adminTemplates - список шаблонов списков для админки. По умолчанию - list
			$templates - список шаблонов структур вообще. Надо выбрать хоть какой-то

			Для каждой структуры может быть определен свой собственный файл с коллекцией блоков, на основании которого генерируется структура.
			Этот файл должен входить в состав шаблона типа blocks. Для каждого шаблона этого типа может быть указан свой файл построения структуры.

			Для структуры могут быть добавлены свои блоки в list.tmpl для cms, либо списки могут генерирваться на основании шаблона по умолчанию
		*/

		list($modules, $tables) = $this->getStructureModTables();
		$this->setContent('{Call:Lang:modules:cms:podstrukturo}', 'top_comment');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'structureNew',
						'structureNew',
						array(
							'caption' => '{Call:Lang:modules:cms:novaiakonten}'
						)
					),
					'structure_new',
					array(
						'adminTemplates' => $this->getAdminStructureTemplates(),
						'templates' => $this->getSiteStructureTemplates(),
						'pageTemplates' => $this->getTemplatePages(),
						'modules' => $modules,
						'tables' => $tables,
						'type' => ''
					)
				),
				array(),
				array(),
				'big'
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'structures_list',
					array(
						'req' => array( 'structures', '*', '', "`sort`" ),
						'form_actions' => array(
							'delete' => '{Call:Lang:modules:cms:udalit}'
						),
						'actions' => array(
							'text' => 'structureData',
							'add' => 'structureEntry',
							'redact' => 'redactForm'
						),
						'action' => 'structureActions'
					),
					array(
						'caption' => '{Call:Lang:modules:cms:vsekontentst}'
					)
				),
				'big'
			)
		);
	}

	protected function func_structureNew(){
		/*
			Новая HTML-структура
		*/

		$modify = empty($this->values['modify']) ? 0 : $this->values['modify'];

		if($this->DB->cellFetch(array('structures', 'name', "`name`='".db_main::Quot($this->values['name'])."' AND `id`!='$modify'"))) $this->setError('name', '{Call:Lang:modules:cms:takojidentif}');
		elseif(!$modify && $this->values['type'] == 'internal' && $this->DB->issetTable($this->values['name'])) $this->setError('name', '{Call:Lang:modules:cms:ehtotidentif}');
		elseif($this->values['type'] == 'table' && !$this->Core->getDBByMod($this->values['module'])->issetTable($this->values['table'])) $this->setError('table', '{Call:Lang:modules:cms:tablitsynesu:'.Library::serialize(array($this->values['table'])).'}');

		if(!$this->check()) return false;

		$this->values['vars'] = array('admin_template' => $this->values['admin_template']);
		foreach($this->Core->getAllTemplates('main', false, true) as $i => $e){
			if(!empty($this->values['template_'.$i])) $this->values['vars']['template'][$i] = $this->values['template_'.$i];
		}

		if(!empty($this->values['in_page']) && $this->values['in_page'] == 4){
			foreach($this->getTemplatePages() as $i => $e){
				foreach($e['pages'] as $i1 => $e1){
					$this->values['vars']['pages'][$i][$i1] = $this->values['in_page_template_'.$i.'_'.$i1.'_style'];
				}
			}
		}

		if($this->values['type'] == 'internal'){
			$this->values['module'] = $this->mod;
			$this->values['table'] = 'structurecontent_'.$this->values['name'];
		}

		$reqType = $modify ? 'Upd' : 'Ins';
		$id = $this->DB->$reqType(array('structures', $this->fieldValues(array('name', 'text', 'type', 'sort', 'table', 'module', 'vars', 'in_page', 'in_page_up')), "`id`='{$modify}'"));

		if($id && $reqType == 'Ins'){
			$this->setAdminStat('new', '{Call:Lang:modules:cms:dobavlenakon:'.Library::serialize(array($this->values['text'])).'}', 'structures', $id);

			if($this->values['type'] == 'internal'){
				$this->DB->CT(
					array(
						$this->values['table'],
						array('id' => '', 'parent_id' => '', 'name' => '', 'text' => '', 'eval' => 'TEXT', 'sort' => '', 'show' => ''),
						array('uni' => array(array('name')))
					)
				);

				$this->formFieldsAdd(
					'structure_blocks',
					array(
						'name' => 'text',
						'text' => '{Call:Lang:modules:cms:imiazapisi}',
						'type' => 'text',
						'structures_id' => $id,
						'sort' => 1,
						'show' => '1',
						'in_page_ins_style' => 3,
						'form_as' => 'text',
						'warn' => '{Call:Lang:modules:cms:vyneukazalii}'
					),
					array('structures_id')
				);

				$this->formFieldsAdd(
					'structure_blocks',
					array(
						'name' => 'name',
						'text' => '{Call:Lang:modules:cms:identifikato2}',
						'type' => 'text',
						'structures_id' => $id,
						'sort' => 1,
						'show' => '1',
						'in_page_ins_style' => 3,
						'form_as' => 'ident',
						'warn' => '{Call:Lang:modules:cms:vyneukazalii1}',
						'warn_function' => 'regExp::ident'
					),
					array('structures_id')
				);

				$this->formFieldsAdd(
					'structure_blocks',
					array(
						'name' => 'parent_id',
						'text' => '{Call:Lang:modules:cms:roditelskaia}',
						'type' => 'select',
						'structures_id' => $id,
						'sort' => 1,
						'show' => '1',
						'in_page_ins_style' => 3,
						'form_as' => 'parent',
						'eval' => 'return array("additional" => Library::array_merge(array("" => "Нет"), $this->getStructureEntries('.$id.', empty($this->values["structureId"]) ? 0 : $this->values["structureId"])));'
					),
					array('structures_id')
				);

				$this->formFieldsAdd(
					'structure_blocks',
					array(
						'name' => 'sort',
						'text' => '{Call:Lang:modules:cms:pozitsiiavso}',
						'type' => 'text',
						'structures_id' => $id,
						'show' => '1',
						'sort' => 50,
						'in_page_ins_style' => 3,
						'form_as' => 'sort',
					),
					array('structures_id')
				);

				$this->formFieldsAdd(
					'structure_blocks',
					array(
						'name' => 'eval',
						'text' => '{Call:Lang:modules:cms:ispolniaemyj}',
						'type' => 'textarea',
						'structures_id' => $id,
						'show' => '1',
						'comment' => '{Call:Lang:modules:cms:eslivvedenny}'
					),
					array('structures_id')
				);

				$this->formFieldsAdd(
					'structure_blocks',
					array(
						'name' => 'show',
						'text' => '{Call:Lang:modules:cms:zapisotobraz}',
						'type' => 'checkbox',
						'structures_id' => $id,
						'show' => '1',
						'sort' => 100,
						'in_page_ins_style' => 3,
						'form_as' => 'show',
					),
					array('structures_id')
				);
			}
		}

		$this->refresh('structure');
		return $id;
	}

	protected function func_structureData(){
		/*
			Управление структурами.
		*/

		list($modules, $tables) = $this->getStructureModTables();
		$values = $this->DB->rowFetch(array('structures', "*", "id='".db_main::Quot($this->values['id'])."'"));
		$vars = Library::unserialize($values['vars']);

		$values['admin_template'] = $vars['admin_template'];
		$this->funcName = $values['text'];
		$this->pathFunc = 'structure';

		if(!empty($vars['template'])){
			foreach($vars['template'] as $i => $e){
				$values['template_'.$i] = $e;
			}
		}

		if(!empty($vars['pages'])){
			foreach($vars['pages'] as $i => $e){
				foreach($e as $i1 => $e1){
					$values['in_page_template_'.$i.'_'.$i1.'_style'] = $e1;
				}
			}
		}

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'structureNew',
						'structureNew',
						array('caption' => '{Call:Lang:modules:cms:izmenit:'.Library::serialize(array($values['text'])).'}')
					),
					'structure_new',
					array(
						'adminTemplates' => $this->getAdminStructureTemplates(),
						'templates' => $this->getSiteStructureTemplates(),
						'pageTemplates' => $this->getTemplatePages(),
						'type' => $values['type'],
						'modules' => $modules,
						'tables' => $tables,
						'modify' => true
					)
				),
				$values,
				array('modify' => $this->values['id'], 'type' => $values['type']),
				'big'
			)
		);
	}

	protected function func_structureActions(){
		$tables = isset($this->values['entry']) ? $this->DB->columnFetch(array('structures', 'table', 'id', "`type`='internal' AND ".$this->getEntriesWhere())) : array();
		$return = $this->typeActions('structures', 'structure');
		if($tables && $return && $this->values['action'] == 'delete') $this->DB->Drop($tables);
		return $return;
	}

	protected function func_redactForm(){
		/*
			Управление таблицой добавления записей в структуру
			pageBlocks - список вида:
				[Шаблон][Страница][name Контент-блока] = text Контент-блока
		*/

		$structId = !empty($this->values['structures_id']) ? $this->values['structures_id'] : $this->values['id'];
		$data = $this->getStructureParamsById($structId);
		$DB = $this->Core->getDBByMod($data['module']);

		$this->funcName = '{Call:Lang:modules:cms:redaktirovan:'.Library::serialize(array($data['text'])).'}';
		$this->pathFunc = 'structure';
		if($data['type'] != 'internal') throw new AVA_Exception('{Call:Lang:modules:cms:dliaehtojstr}');

		if(!empty($this->values['action']) && $this->values['action'] == 'add' && $DB->issetField($data['table'], $this->values['name'])) $this->setError('name', '{Call:Lang:modules:cms:ehtotidentif1}');
		$pageBlocks = array();
		$allPageBlocks = array();

		if(empty($this->values['field_action']) || $this->values['field_action'] == 'modify'){
			$blocksList = $this->DB->columnFetch(array('blocks', '*', 'name', "`show`>0", "`sort`"));

			foreach($this->getTemplatePages() as $i => $e){
				$pageBlocks[$i]['name'] = $e['name'];

				foreach($e['pages'] as $i1 => $e1){
					foreach($blocksList as $i2 => $e2){
						$e2['vars'] = Library::unserialize($e2['vars']);

						if($this->blockIsUsable($i, $i1, $e2)){
							$pageBlocks[$i]['pages'][$i1][$i2] = $e2['text'];
							$allPageBlocks[$i2] = $e2['text'];
						}
					}
				}
			}
		}

		$return = $this->formFields(
			'structure_blocks',
			array(
				'listParams' => array(
					'caption' => '{Call:Lang:modules:cms:formanapolne:'.Library::serialize(array($data['text'])).'}',
					'sortAction' => $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc='.library::encodeUrl('redactForm&structures_id='.$structId).'&table=structure_blocks',
					'protected' => array('name' => true, 'text' => true, 'sort' => true, 'show' => true)
				),
				'matrixHiddens' => array('structures_id' => $structId),
				'matrixExtra' => $data['in_page'] ? 'structure_in_page_matrix' : array(),
				'matrixData' => array('pageBlocks' => $pageBlocks, 'allPageBlocks' => $allPageBlocks, 'inPageUpd' => $data['in_page_up']),
				'listData' => array('req' => array(2 => "structures_id='{$structId}'")),
				'extraFields' => array('structures_id'),
				'func' => 'redactForm&structures_id='.$structId,
				'formName' => 'redactForm',
				'filter' => " AND `structures_id`='$structId'"
			),
			array(
				'table' => $data['table']
			)
		);

		return $return;
	}



	/********************************************************************************************************************************************************************

																			Записи структуры

	*********************************************************************************************************************************************************************/

	protected function func_structureEntry(){
		/*
			Добавление записи в структуру
		*/

		$data = $this->getStructureParamsById($this->values['id']);
		if($data['type'] != 'internal') throw new AVA_Access_Exception('{Call:Lang:modules:cms:ehtustruktur}');

		$this->funcName = '{Call:Lang:modules:cms:dobavitzapis:'.Library::serialize(array($data['text'])).'}';
		$this->pathFunc = 'structure';

		$this->setContent('{Call:Lang:modules:cms:podstrukturo}', 'top_comment');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'structureEntryAdd',
						'structureEntryAdd',
						array('caption' => '{Call:Lang:modules:cms:novaiazapisv:'.Library::serialize(array($data['text'])).'}')
					),
					$this->getStructureMatrix($this->values['id'], $values)
				),
				$values,
				array('structureId' => $this->values['id']),
				'big'
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					$data['vars']['admin_template'],
					array(
						'req' => array( $data['table'], '*', '', "`sort`" ),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:cms:skryt}',
							'unsuspend' => '{Call:Lang:modules:cms:otkryt}',
							'delete' => '{Call:Lang:modules:cms:udalit}'
						),
						'actions' => array(
							'text' => 'structureEntryData&structureId='.$this->values['id']
						),
						'action' => 'structureEntryActions&structureId='.$this->values['id']
					),
					array(
						'caption' => '{Call:Lang:modules:cms:vsezapisidli:'.Library::serialize(array($data['text'])).'}'
					)
				)
			)
		);
	}

	protected function func_structureEntryAdd(){
		/*
			Добавляет запись
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$data = $this->getStructureParamsById($this->values['structureId']);
		$fields = $this->getStructureEntryValues($this->values['structureId']);

		$this->funcName = '{Call:Lang:modules:cms:dobavitzapis:'.Library::serialize(array($data['text'])).'}';
		$this->pathFunc = 'structure';

		$this->isUniq($data['table'], array('name' => '{Call:Lang:modules:cms:takojidentif}'), $id);
		return $this->typeIns($data['table'], $fields, 'structureEntry&id='.$this->values['structureId']);
	}

	protected function func_structureEntryData(){
		/*
			Изменение данных для отдельной записи в структуре
		*/

		$data = $this->getStructureParamsById($this->values['structureId']);
		$values = $this->DB->rowFetch(array($data['table'], '*', "`id`='{$this->values['id']}'"));
		$hiddens = array('modify' => $this->values['id'], 'id' => $this->values['structureId']);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'structureEntryAdd',
						'structureEntryAdd&structureId='.$this->values['structureId'],
						array('caption' => '{Call:Lang:modules:cms:izmenitzapis:'.Library::serialize(array($values['name'])).'}')
					),
					Library::array_merge($this->getStructureMatrix($this->values['structureId']), array('name' => array('disabled' => true)))
				),
				$values,
				array('structureId' => $this->values['structureId'], 'modify' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_structureEntryActions(){
		/*
			Массовое управление записями в структуре
		*/

		$data = $this->getStructureParamsById($this->values['structureId']);
		return $this->typeActions($data['table'], 'structureEntry&id='.$this->values['structureId']);
	}


	/********************************************************************************************************************************************************************

																Установка контент-блоков

	*********************************************************************************************************************************************************************/

	protected function func_blocks(){
		/*
			Устанавливает новые Контент-блоки на странице.
			Контент-блоки могут по настройке:
				- Быть скрытыми на всех страницах, т.е. не выводятся вообще и в данном случае не используется
				- Выводиться на всех страницах, т.е. выводятся всегда и без изменений (используя значение по умолчанию)
				- Для каждой страницы можно установить скрыто / открыто (т.е. либо нет вовсе, либо всегда значение по умолчанию)
				- Для каждой страницы можно установить содержимое индивидуально
			Каждый тип настройки можно указать в зависимости от шаблона страницы, либо для всех страниц сразу

			В контент блоке можно установить тип приема информации:
				- text
				- textarea
				- select
				- multiselect
				- radio
				- checkbox_array

			Можно установить паттерн значения, пометить поле как обязательное для заполнения и т.п.
			Можно установить значение по умолчанию для этого блока
			Можно установить вызов плагинов внутрь блоков
		*/

		return $this->formFields(
			'blocks',
			array(
				'matrixParams' => array('caption' => '{Call:Lang:modules:cms:novyjkontent}'),
				'listParams' => array(
					'caption' => '{Call:Lang:modules:cms:vsekontentbl}',
					'sortAction' => $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc='.library::encodeUrl('blocks').'&table=blocks'
				),
				'listData' => array(
					'form_actions' => array(
						'set_show_0' => '{Call:Lang:modules:cms:sdelatvsegda}',
						'set_show_1' => '{Call:Lang:modules:cms:sdelatvsegda1}',
						'set_show_2' => '{Call:Lang:modules:cms:ispolzovatpe}',
						'set_show_3' => '{Call:Lang:modules:cms:razreshitizm}',
						'delete' => '{Call:Lang:modules:cms:udalit}'
					),
				),
				'actionFields' => (!empty($this->values['action']) && regExp::match("/set_show_(\d)/iUs", $this->values['action'], true, true, $m)) ? array('show' => $m['1']) : array(),
				'matrixData' => array('templates' => $this->getTemplatePages()),
				'matrixExtra' => 'blocks'
			)
		);
	}


	/********************************************************************************************************************************************************************

																			Теги

	*********************************************************************************************************************************************************************/

	protected function func_tags(){
		/*
			Загрузка файла. Список директорий
		*/

		$this->typicalMain(
			array(
				'isUniq' => array('name' => 'Такой идентификатор уже используется', 'text' => 'Такое имя уже используется'),
				'caption' => 'Добавить тег',
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'text' => 'Тег',
							'name' => 'URL-имя',
							'pop' => 'Популярность',
							'show' => '',
						),
						'orderFields' => array(
							'text' => 'имени',
							'name' => 'URL',
							'pop' => 'популярности'
						),
						'searchMatrix' => array(
							'pop' => array('type' => 'gap')
						)
					),
					'form_actions' => array(
						'suspend' => 'Отключить',
						'unsuspend' => 'Включить',
						'delete' => 'Удалить',
					),
					'actions' => array(
						'text' => $this->func.'&type_action=modify'
					)
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																		Загрузка файлов

	*********************************************************************************************************************************************************************/

	protected function func_files(){
		/*
			Загрузка файла. Список директорий
		*/

		$this->setContent(
			$this->getListText(
				$this->newList(
					'folders_list',
					array(
						'arr' => $this->Core->getFolders($this->mod),
						'actions' => array(
							'upload' => 'upload'
						)
					),
					array('caption' => '{Call:Lang:modules:cms:papkizagruzk}')
				),
				'big'
			)
		);
	}


	protected function func_upload(){
		/*
			Загрузка файлов
		*/

		$fData = $this->Core->getFolderData($this->values['id']);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'uploadFile',
						'uploadFile&folderId='.$this->values['id'],
						array('caption' => '{Call:Lang:modules:cms:zagruzitfajl}')
					),
					'upload',
					array('folder' => $fData['path'])
				),
				array(),
				array(),
				'big'
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'files_list',
					array(
						'arr' => Files::readFolderFileParams(_W.$fData['path']),
						'form_actions' => array('delete' => '{Call:Lang:modules:cms:udalit}'),
						'action' => 'filesActions&folderId='.$this->values['id']
					),
					array(
						'caption' => '{Call:Lang:modules:cms:fajlypapki:'.Library::serialize(array(_W, $fData['path'])).'}',
						'folder' => $fData['path'],
					)
				),
				'big'
			)
		);
	}

	protected function func_uploadFile(){
		/*
			Загружает физически файл
		*/

		if(!$this->check()) return false;
		$this->refresh('upload&id='.$this->values['folderId']);
		return true;
	}

	protected function func_filesActions(){
		/*
			Действия над файлами
		*/

		$fData = $this->Core->getFolderData($this->values['folderId']);

		foreach($this->values['entry'] as $i => $e){
			if(!$this->Core->ftpRm(_W.$fData['path'].$i)) $this->setError('', '{Call:Lang:modules:cms:neudalosudal:'.Library::serialize(array($i)).'}');
		}

		if($this->errorMessages) $this->back('upload&id='.$this->values['folderId']);
		else $this->refresh('upload&id='.$this->values['folderId']);
	}


	/********************************************************************************************************************************************************************

																			Альтернативные формы

	*********************************************************************************************************************************************************************/

	protected function func_forms(){
		/*
			Управление формами
		*/

		list($modules, $tables) = $this->getStructureModTables();

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'newForm',
						'newForm',
						array('caption' => '{Call:Lang:modules:cms:novaiaforma}')
					),
					'forms',
					array(
						'modules' => $modules,
						'tables' => $tables,
						'structures' => $this->getStructureList(),
						'templates' => $this->getFormTemplates(),
						'emlTemplates' => $this->Core->getMailTemplateNames()
					)
				),
				array(),
				array(),
				'big'
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'forms_list',
					array(
						'req' => array('forms', '*', "", "`sort`"),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:cms:skryt}',
							'unsuspend' => '{Call:Lang:modules:cms:otkryt}',
							'delete' => '{Call:Lang:modules:cms:udalit}',
						),
						'actions' => array(
							'text' => 'modifyForm',
							'fields' => 'formFields'
						),
						'searchForm' => array(
							'searchFields' => array(
								'text' => '{Call:Lang:modules:cms:imia}',
								'name' => '{Call:Lang:modules:cms:identifikato}',
								'show' => '',
							),
							'orderFields' => array(
								'text' => '{Call:Lang:modules:cms:imeni}',
								'name' => '{Call:Lang:modules:cms:identifikato3}',
							)
						),
						'action' => 'formsActions'
					),
					array(
						'caption' => '{Call:Lang:modules:cms:vseformy}'
					)
				),
				'big'
			)
		);
	}

	protected function func_newForm(){
		/*
			Новая форма
		*/

		$id = isset($this->values['modify']) ? $this->values['modify'] : false;
		$fields = $this->fieldValues(array('text', 'name', 'sort', 'show'));
		$fields['vars'] = $this->getFields('forms', array('text', 'name', 'sort', 'show'), false, false, array('templates' => $this->getFormTemplates()));

		$this->isUniq('forms', array('name' => '{Call:Lang:modules:cms:takojidentif}', 'text' => 'Такое имя уже используется'), $id);
		if(!$id && $this->values['save_style_table'] == '1' && !empty($this->values['save_style']['db']) && $this->DB->issetTable($this->values['new_table'])) $this->setError('new_table', '{Call:Lang:modules:cms:tablitsauzhe:'.Library::serialize(array($this->values['new_table'])).'}');
		$return = $this->typeIns('forms', $fields, 'forms');

		if(!$id && $return && $this->values['save_style_table'] == '1' && !empty($this->values['save_style']['db'])){
			$this->DB->CT(array($this->values['new_table'], array('id' => '', 'vars' => '')));
		}
		return $return;
	}

	protected function func_modifyForm(){
		/*
			Модификация параметров формы
		*/

		list($modules, $tables) = $this->getStructureModTables();
		$values = $this->DB->rowFetch(array('forms', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$values = Library::array_merge(Library::unserialize($values['vars']), $values);

		return $this->typeModify(
			false,
			'forms',
			'newForm',
			array(
				'values' => $values,
				'caption' => '{Call:Lang:modules:cms:izmenitparam} "{text}"',
				'formData' => array(
					'modules' => $modules,
					'tables' => $tables,
					'structures' => $this->getStructureList(),
					'templates' => $this->getFormTemplates(),
					'emlTemplates' => $this->Core->getMailTemplateNames()
				)
			)
		);
	}

	protected function func_formsActions(){
		$forms = $this->getFormById();
		$return = $this->typeActions('forms', 'forms');

		if($return){
			foreach($this->values['entry'] as $i => $e){
				if($forms[$i]['vars']['save_style_table'] == 1) $this->DB->Drop($forms[$i]['vars']['new_table']);
			}
		}

		return $return;
	}

	protected function func_formFields(){
		$fId = isset($this->values['formId']) ? $this->values['formId'] : $this->values['id'];
		$fData = $this->getFormById($fId);
		$this->values['form_id'] = $fData['name'];

		$extra = array();
		if($fData['vars']['save_style_table'] == 1){
			$extra = array('table' => $fData['vars']['new_table']);
		}

		return $this->formFields(
			'form_blocks',
			array(
				'req' => array('form_blocks', '*', "`form_id`='{$fData['name']}'"),
				'extraFields' => array('form_id', 'insert_field', 'email_var', 'http_var'),
				'func' => 'formFields&formId='.$fId
			),
			$extra
		);
	}

	private function getFormTemplates(){
		/*
			Возвращает шаблоны формы
		*/

		$templates = array();
		foreach($this->Core->getAllTemplates('main', false, true) as $i => $e){
			$templates[$i]['text'] = $e;
			$templates[$i]['templates'] = $this->Core->getTemplatePageBlockNamesByTmplList('form.tmpl', 'form', 'cover', $i, 'main');
		}

		return $templates;
	}


	/********************************************************************************************************************************************************************

																			Шаблоны страничек

	*********************************************************************************************************************************************************************/

	protected function func_pageTemplates(){
		/*
			Шаблоны страничек. Это не вообще шаблон, а просто заготовка позволяющая создавать странички быстрее
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'newPageTemplate',
						'newPageTemplate',
						array('caption' => 'Новый шаблон')
					),
					'page_template'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'page_templates_list',
					array(
						'req' => array('page_templates', array('id', 'name', 'text', 'sort', 'show'), '', "`sort`"),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:cms:skryt}',
							'unsuspend' => '{Call:Lang:modules:cms:otkryt}',
							'delete' => '{Call:Lang:modules:cms:udalit}'
						),
						'actions' => array(
							'text' => 'modifyPageTemplate'
						),
						'action' => 'pageTemplateActions'
					),
					array(
						'caption' => 'Список шаблонов заполнения страницы'
					)
				)
			)
		);
	}

	protected function func_newPageTemplate(){
		/*
			Добавляет новый шаблон страницы
		*/

		$this->isUniq('page_templates', array('name' => '{Call:Lang:modules:cms:takojidentif}', 'text' => 'Такое имя уже используется'));
		if($id = $this->typeIns('page_templates', $this->fieldValues(array('text', 'name', 'sort', 'show')), 'pageTemplates')) $this->redirect('modifyPageTemplate&id='.$id);
	}

	protected function func_modifyPageTemplate(){
		/*
			Добавляет новый шаблон страницы
		*/

		$structures = $this->getUsedStructures();
		$structureMatrix = $this->getMatrix4StructuresArray(false, $structures);
		$blocksMatrix = $this->getMatrix4ContentBlocks();

		$values = $this->DB->rowFetch(array('page_templates', '*', "`id`='{$this->values['id']}'"));
		$this->funcName = 'Изменить шаблон "'.$values['text'].'"';
		$this->pathFunc = 'pageTemplates';

		$fObj = $this->addFormBlock(
			$this->newForm('modifyPageTemplate2', 'modifyPageTemplate2', array('caption' => 'Изменить шаблон "'.$values['text'].'"')),
			'page_template',
			array('templates' => $this->getTemplatePages(), 'modify' => $this->values['id'], 'blocks' => $blocksMatrix, 'structures' => $structures, 'structureMatrix' => $structureMatrix)
		);

		$fObj->setValues($values);
		$fObj->setValues(Library::unserialize($values['vars']));
		$this->setContent($this->getFormText($fObj, array(), array('modify' => $this->values['id']), 'big100'));
	}

	protected function func_modifyPageTemplate2(){
		/*
			Сохраняет шаблон
		*/

		$this->isUniq('page_templates', array('text' => 'Такое имя уже используется'), $this->values['modify']);
		if(!$this->check()) return false;

		$fields = $this->fieldValues(array('text', 'sort', 'show'));
		$fields['vars'] = $this->values;
		unset($fields['vars']['text'], $fields['vars']['name'], $fields['vars']['sort'], $fields['vars']['show'], $fields['vars']['modify'], $fields['vars']['ava_form_transaction_id']);

		$this->DB->Upd(array('page_templates', $fields, "`id`='{$this->values['modify']}'"));
		$this->refresh('pageTemplates');
	}

	protected function func_pageTemplateActions(){
		/*
			Массовые действия над шаблонами загрузки
		*/

		return $this->typeActions('page_templates', 'pageTemplates');
	}


	/********************************************************************************************************************************************************************

																			Управление страничками

	*********************************************************************************************************************************************************************/



	/********************************************************************************************************************************************************************

																			Управление страничками

	*********************************************************************************************************************************************************************/

	protected function func_export(){
		/*
			Управление экспортом новостей
		*/

		$pages = Library::array_merge(array(0 => '{Call:Lang:modules:cms:net}'), $this->getPages());

		$this->typicalMain(
			array(
				'isUniq' => array('name' => 'Такой идентификатор уже используется', 'text' => 'Такое имя уже используется'),
				'caption' => 'Добавить RSS-ленту',
				'formData' => array(
					'parentPages' => $pages
				),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'text' => 'Имя',
							'name' => 'Идентификатор',
							'url' => 'URL',
							'format' => 'Формат вывода',
							'parent_page' => 'Родительская страница',
							'update_interval' => 'Интервал обновления',
							'last' => 'Последнее обращение',
							'show' => '',
						),
						'orderFields' => array(
							'text' => 'имени',
							'name' => 'идентификатору',
							'url' => 'URL',
							'update_interval' => 'интервалу обновления',
							'last' => 'последнему обращению',
						)
					),
					'form_actions' => array(
						'suspend' => 'Отключить',
						'unsuspend' => 'Включить',
						'delete' => 'Удалить',
					),
					'actions' => array(
						'text' => $this->func.'&type_action=modify',
						'extraSettings' => 'extraExportSettings'
					)
				),
				'listParams2' => array(
					'pages' => $pages
				)
			)
		);
	}
}

?>