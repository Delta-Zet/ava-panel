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


class gen_cms extends ModuleInterface{

	//Структуры
	private $structureParams = false;
	private $structureParamsById = array();
	private $structureList = array();

	private $usedStructures = array();
	private $structuresInPage = array();
	private $structureEntries = array();

	//Страницы
	private $pages = array();
	private $pageParams = array();
	private $pageParamsById = array();
	private $versionParams = array();

	//Произвольные формы
	private $forms = false;
	private $formsById = array();
	private $formNames = array('all' => array(), 'used' => array());

	//Теги
	private $tags = false;
	private $tagsById = array();
	private $tagNames = array('all' => array(), 'used' => array());

	//Шаблоны заполнения страниц
	private $pageTemplates = false;
	private $pageTemplatesById = array();
	private $pageTemplatesList = array();

	//Контент-блоки
	private $blocks = false;
	private $allBlocks = false;
	private $blocksByTemplates = array();


	/********************************************************************************************************************************************************************

																			Взаимодействие с внешними модулями

	*********************************************************************************************************************************************************************/

	public function __ava____map($obj){		/*
			Карта сайта
				- Ссылки на страницы с учетом иерархии
				- Теги и страницы отсортированные по тегамъ
		*/

		$pages = $this->DB->columnFetch(array('pages', array('parent', 'name', 'url', 'vars'), '', $this->getPageFilter(), "`sort`"));
		foreach($pages as $i => $e){			$pages[$i]['vars'] = Library::unserialize($e['vars']);
			$pages[$i]['link'] = 'index.php?mod='.$this->mod.'&func=page&id='.$e['url'];
			if(!empty($pages[$i]['vars']['caption'])) $pages[$i]['name'] = $pages[$i]['vars']['caption'];
			elseif(!empty($pages[$i]['vars']['title'])) $pages[$i]['name'] = $pages[$i]['vars']['title'];
		}

		$return = $this->getEntriesRecursiveArray($pages, 'parent', 'url', '');
		$ind = count($return);

		if($tags = $this->getTagNames()){			$return[$ind] = array('name' => 'Теги', 'link' => 'index.php?mod='.$this->mod.'&func=tags', 'subblock' => array());
			foreach($tags as $i => $e){				$return[$ind]['subblock'][] = array('name' => $e, 'link' => 'index.php?mod='.$this->mod.'&func=pagesByTag&tag='.$i, 'subblock' => array());			}		}

		return $return;
	}


	/********************************************************************************************************************************************************************

																				Формы

	*********************************************************************************************************************************************************************/

	private function fetchForms(){
		/*
			Извлекает теги
		*/

		if($this->forms === false){
			$this->forms = $this->DB->columnFetch(array('forms', '*', 'name', "", "`sort`"));

			foreach($this->forms as $i => $e){
				$e['vars'] = Library::unserialize($e['vars']);
				$this->forms[$i]['vars'] = $e['vars'];

				$this->formsById[$e['id']] = $e;
				$this->formNames['all'][$i] = $e['text'];
				if($e['show']) $this->formNames['used'][$i] = $e['text'];
			}
		}
	}

	public function __ava__getForm($name = false){
		/*
			Возвращает список тегов
		*/

		$this->fetchForms();
		return $name ? $this->forms[$name] : $this->forms;
	}

	public function __ava__getFormById($id = false){
		/*
			Возвращает список тегов
		*/

		$this->fetchForms();
		return $id ? $this->formsById[$id] : $this->formsById;
	}

	public function __ava__getFormNames($used = true){
		/*
			Возвращает список тегов
		*/

		$this->fetchForms();
		return $this->formsNames[$used ? 'used' : 'all'];
	}


	/********************************************************************************************************************************************************************

																				Теги

	*********************************************************************************************************************************************************************/

	private function fetchTags(){		/*
			Извлекает теги
		*/

		if($this->tags === false){			$this->tags = $this->DB->columnFetch(array('tags', '*', 'name', "", "`sort`"));
			foreach($this->tags as $i => $e){				$this->tagsById[$e['id']] = $e;
				$this->tagNames['all'][$i] = $e['text'];
				if($e['show']) $this->tagNames['used'][$i] = $e['text'];			}		}	}

	public function __ava__getTag($name = false){
		/*
			Возвращает список тегов
		*/

		$this->fetchTags();
		return $name ? $this->tags[$name] : $this->tags;
	}

	public function __ava__getTagById($id = false){
		/*
			Возвращает список тегов
		*/

		$this->fetchTags();
		return $id ? $this->tagsById[$id] : $this->tagsById;
	}

	public function __ava__getTagNames($used = true){
		/*
			Возвращает список тегов
		*/

		$this->fetchTags();
		return $this->tagNames[$used ? 'used' : 'all'];
	}


	/********************************************************************************************************************************************************************

																			Контент-блоки

	*********************************************************************************************************************************************************************/

	public function __ava__blockIsUsable($tmpl, $tmplPg, $params){
		/*
			Проверяет что блок можно использовать в этом шаблоне на этой странице
		*/

		if($params['show'] == 4) if(($params['show'] = $params['vars']['extra']['show_'.$tmpl.'_'.$tmplPg]) < 2) return false;
		return $params['show'];
	}

	public function __ava__getAllContentBlocks(){
		/*
			Возвращает все используемые контент-блоки
		*/

		if($this->allBlocks === false){
			$this->allBlocks = $this->DB->columnFetch(array('blocks', '*', 'name'));
			foreach($this->allBlocks as $i => $e) $this->allBlocks[$i]['vars'] = Library::unserialize($this->allBlocks[$i]['vars']);
		}

		return $this->allBlocks;
	}

	public function __ava__getContentBlocks(){
		/*
			Возвращает все используемые контент-блоки
		*/

		if($this->blocks === false){
			$this->blocks = $this->DB->columnFetch(array('blocks', '*', 'name', "`show`=2 OR `show`=3 OR `show`=4", "`sort`"));
			foreach($this->blocks as $i => $e) $this->blocks[$i]['vars'] = Library::unserialize($this->blocks[$i]['vars']);
		}

		return $this->blocks;
	}

	public function __ava__getMatrix4ContentBlocks($pgList = false, $blocks = false){
		/*
			Возвращает список контент-блоков
		*/

		if($pgList === false) $pgList = $this->getTemplatePages();
		if($blocks === false) $blocks = $this->getContentBlocks();

		$matrix = array();
		$values = array();
		$extra = array();

		foreach($blocks as $r){
			if($r['show'] == 4){
				$r['show'] = 0;
				foreach($pgList as $i => $e){
					foreach($e['pages'] as $i1 => $e1){
						if(!empty($r['vars']['extra']['show_'.$i.'_'.$i1]) && $r['vars']['extra']['show_'.$i.'_'.$i1] > $r['show']) $r['show'] = $r['vars']['extra']['show_'.$i.'_'.$i1];
					}
				}

				if($r['show'] < 2) continue;
			}

			$matrix[$r['name']] = $this->getMatrixField($r, $values, $extra[$r['name']]);
			if($r['show'] == 2) $matrix[$r['name']] = array('type' => 'checkbox', 'text' => '{Call:Lang:modules:cms:otobrazhatbl:'.Library::serialize(array($matrix[$r['name']]['text'])).'}');
		}

		return array($matrix, $values, $extra);
	}


	/********************************************************************************************************************************************************************

																			Шаблоны страниц

	*********************************************************************************************************************************************************************/

	private function fetchPageTemplates(){		if($this->pageTemplates === false){			$this->pageTemplates = $this->DB->columnFetch(array('page_templates', '*', "name", "", "`sort`"));
			foreach($this->pageTemplates as $i => $e){				$this->pageTemplates[$i]['vars'] = Library::unserialize($e['vars']);				$this->pageTemplatesById[$e['id']] = $this->pageTemplates[$i];
				if($e['show']) $this->pageTemplatesList[$i] = $e['text'];
			}		}	}

	public function __ava__getPageTemplate($tmpl){
		$this->fetchPageTemplates();
		return $this->pageTemplates[$tmpl];
	}

	public function __ava__getPageTemplateById($id){
		$this->fetchPageTemplates();
		return $this->pageTemplatesById[$id];
	}

	public function __ava__getPageTemplatesList(){
		$this->fetchPageTemplates();
		return $this->pageTemplatesList;
	}


	/********************************************************************************************************************************************************************

																		Работа со структурами

	*********************************************************************************************************************************************************************/

	private function fetchStructureParams($force = false){		if($this->structureParams === false || $force){			$this->structureParams = $this->DB->columnFetch(array('structures', '*', "name", "", "`sort`"));

			foreach($this->structureParams as $i => $e){				$this->structureParams[$i]['vars'] = Library::unserialize($this->structureParams[$i]['vars']);
				$this->structureParamsById[$e['id']] = $this->structureParams[$i];
				$this->structureList[$i] = $e['text'];
				if($e['in_page'] > 0 || $e['in_page_up']) $this->usedStructures[$i] = $this->structureParams[$i];			}		}	}

	public function __ava__getStructureParams($struct, $force = false){		$this->fetchStructureParams($force);
		return $this->structureParams[$struct];
	}

	public function __ava__getStructureParamsById($id, $force = false){
		$this->fetchStructureParams($force);
		return $this->structureParamsById[$id];
	}

	public function __ava__getStructureList($force = false){
		$this->fetchStructureParams($force);
		return $this->structureList;
	}

	public function __ava__getUsedStructures($force = false){
		$this->fetchStructureParams($force);
		return $this->usedStructures;
	}

	public function __ava__getStructureEntries($structId, $id = 0){		/*
			Возвращает записи структуры рекурсивно
		*/

		if(empty($this->structureEntries[$structId])){			$sData = $this->getStructureParamsById($structId);			$this->structureEntries[$structId] = $this->getEntriesRecursive($this->DB->columnFetch(array($sData['table'], array('id', 'text', 'parent_id'), 'id', "", "`sort`")), 'text');
		}

		$return = $this->structureEntries[$structId];
		unset($return[$id]);
		return $return;	}

	public function __ava__getAdminStructureTemplates(){
		/*
			Структуры списка для админки
		*/

		return Library::array_merge(
			array('structures_standart' => '{Call:Lang:modules:cms:poumolchanii}'),
			$this->Core->getTemplatePageBlockNamesList($this->Core->getModuleTemplatePath($this->mod).'list.tmpl')
		);
	}

	public function __ava__getSiteStructureTemplates(){
		/*
			Структуры для данного шаблона
		*/

		$return = array();
		foreach($this->Core->getAllTemplates('main', false, true) as $i => $e){			$return[$i] = array(
				'name' => $e,
				'blocks' => Library::array_merge(
					array('' => '{Call:Lang:modules:cms:neispolzovat}'),
					file_exists($f = $this->Core->getTemplatePath('main', $i).'structures.tmpl') ? $this->Core->getTemplatePageBlockNamesList($f) : array()
				)
			);		}

		return $return;
	}

	public function __ava__getStructureModTables(){
		/*
			Таблицы по модулям и сами модули
		*/

		$modules = $this->Core->getModules();
		$tables = array();

		foreach($modules as $i => $e){
			$DB = $this->Core->getDBByMod($i);
			$prefix = $DB->getTblPrefix();
			if(!($tables[$i] = $DB->getTables())) unset($modules[$i], $tables[$i]);
		}

		return array($modules, $tables);
	}

	public function getStructureMatrix($id, &$values = array(), &$extra = array()){
		/*
			Выдает матрицу страутуры по ID
		*/

		list($matrix, $values, $names, $extra) = $this->getMatrixArray(array('structure_blocks', '*', "`structures_id`='$id'", "`sort`"));
		return $matrix;
	}

	public function getStructureEntryValues($id){
		return $this->getGeneratedFormValues(array('structure_blocks', '*', "`structures_id`='$id'", "`sort`"));
	}

	public function __ava__getMatrix4Structures($id, $pgList = false, $structures = false){
		/*
			Возвращает матрицу структур
		*/

		if($structures === false) $structures = $this->getUsedStructures();
		if($pgList === false) $pgList = $this->getTemplatePages();
		$issetStructEntries = $this->getStructuresInPage($id);

		$matrix = array();
		$values = array();
		$extra = array();

		foreach($structures as $i => $e){
			$matrix['insert_structure_'.$e['name'].'_caption']['type'] = 'caption';
			$matrix['insert_structure_'.$e['name'].'_caption']['text'] = $e['text'];

			if($e['in_page'] == 4){
				$e['in_page'] = 0;
				foreach($pgList as $i1 => $e1){
					foreach($e1 as $i2 => $e2){
						if(!empty($e['vars']['pages'][$i1][$i2]) && $e['vars']['pages'][$i1][$i2] > $e['in_page']) $e['in_page'] = $e['vars']['pages'][$i1][$i2];
					}
				}
			}

			if(empty($issetStructEntries[$e['id']]) && $e['in_page']){
				$matrix['insert_structure_'.$e['name']]['type'] = 'checkbox';
				$matrix['insert_structure_'.$e['name']]['text'] = '{Call:Lang:modules:cms:vnestizapisv:'.Library::serialize(array($e['text'])).'}';
			}
			elseif(!empty($issetStructEntries[$e['id']]) && $e['in_page_up']){
				$matrix['insert_structure_'.$e['name']]['type'] = 'checkbox';
				$matrix['insert_structure_'.$e['name']]['text'] = '{Call:Lang:modules:cms:obnovitzapis:'.Library::serialize(array($e['text'])).'}';
				$matrix['insert_structure_'.$e['name']]['value'] = '1';
			}
			else{
				unset($matrix['insert_structure_'.$e['name'].'_caption']);
				continue;
			}

			$matrix['insert_structure_'.$e['name'].'_caption2']['type'] = 'caption';
			$matrix['insert_structure_'.$e['name'].'_caption2']['text'] = '';
			$first = false;
			$last = false;

			foreach($this->getStructureMatrix($e['id'], $v, $ex) as $i1 => $e1){
				if(!empty($ex[$i1]['in_page_ins_style'])){
					if($ex[$i1]['in_page_ins_style'] == 4){
						$ex[$i1]['in_page_ins_style'] = 0;

						foreach($pgList as $i2 => $e2){
							foreach($e2 as $i3 => $e3){
								if(!empty($ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3]) && $ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3] > $ex[$i1]['in_page_ins_style']){
									$ex[$i1]['in_page_ins_style'] = $ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3];
								}
							}
						}
					}

					if($ex[$i1]['in_page_ins_style'] == 3){
						$matrix['insert_structure_'.$i.'_'.$i1] = $e1;
						$last = 'insert_structure_'.$i.'_'.$i1;
						if(empty($first)) $first = $last;
						if(!empty($v[$i1])) $values['insert_structure_'.$i.'_'.$i1] = $v[$i1];
					}
				}

				unset($matrix['insert_structure_'.$i.'_'.$i1]['warn']);
				$extra[$i] = $ex;
			}

			if($first){
				$matrix[$first]['pre_text'] = '<div id="'.$first.'_div" style="display: none;">';
				$matrix[$last]['post_text'] = '</div><script type="text/javascript">'."\n".
					"if(document.getElementById('insert_structure_{$e['name']}').checked) showFormBlock(\"{$first}_div\"); else hideFormBlock(\"{$first}_div\");\n".
					'</script>';
				$matrix['insert_structure_'.$e['name']]['additional_style'] = "onClick='if(this.checked) showFormBlock(\"{$first}_div\"); else hideFormBlock(\"{$first}_div\");'";
			}
			else{
				if($matrix['insert_structure_'.$e['name']]['type'] == 'hidden') unset($matrix['insert_structure_'.$e['name'].'_caption']);
				unset($matrix['insert_structure_'.$e['name'].'_caption2']);
			}
		}

		return array($matrix, $values, $extra);
	}

	public function __ava__getMatrix4StructuresArray($pgList = false, $structures = false){
		/*
			Возвращает матрицу структур как массив
		*/

		if($structures === false) $structures = $this->getUsedStructures();
		if($pgList === false) $pgList = $this->getTemplatePages();
		$return = array();

		foreach($structures as $i => $e){
			if($e['in_page'] == 4){
				$e['in_page'] = 0;
				foreach($pgList as $i1 => $e1){
					foreach($e1 as $i2 => $e2){
						if(!empty($e['vars']['pages'][$i1][$i2]) && $e['vars']['pages'][$i1][$i2] > $e['in_page']) $e['in_page'] = $e['vars']['pages'][$i1][$i2];
					}
				}
			}

			if(!$e['in_page'] && !$e['in_page_up']) continue;
			$return[$i] = array();

			foreach($this->getStructureMatrix($e['id'], $v, $ex) as $i1 => $e1){
				if(!empty($ex[$i1]['in_page_ins_style'])){
					if($ex[$i1]['in_page_ins_style'] == 4){
						$ex[$i1]['in_page_ins_style'] = 0;

						foreach($pgList as $i2 => $e2){
							foreach($e2 as $i3 => $e3){
								if(!empty($ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3]) && $ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3] > $ex[$i1]['in_page_ins_style']){
									$ex[$i1]['in_page_ins_style'] = $ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3];
								}
							}
						}
					}

					if($ex[$i1]['in_page_ins_style'] == 3) $return[$i][$i1] = array('matrix' => $e1, 'value' => $v[$i1], 'extra' => $ex[$i1]);
				}
			}
		}

		return $return;
	}

	public function __ava__getStructuresInPage($page){		/*
			Список структур на странице
		*/

		if(!isset($this->structuresInPage[$page])){			$this->structuresInPage[$page] = $this->DB->columnFetch(array('structure_pages', 'entry_id', 'structure_id', "`page_id`='$page'"));
		}
		return $this->structuresInPage[$page];	}


	/********************************************************************************************************************************************************************

																				Страницы

	*********************************************************************************************************************************************************************/

	public function __ava__getTemplatePages($tmplType = 'main', $type = '.tmpl'){
		/*
			Список всех шаблонов типа $tmplType и их страниц
		*/

		if(empty($this->templatePages[$tmplType])){
			$this->templatePages[$tmplType] = $this->Core->getAllTemplates($tmplType, true, true);
			foreach($this->templatePages[$tmplType] as $i => $e){
				if($this->Core->getParam('templateSource') == 'folder'){
					$pages = array();
					foreach($this->Core->getTemplatePagesByXML($tmplType, $i) as $i1 => $e1){
						if($e1['type'] == $type) $pages[$i1] = $e1['name'];					}				}
				elseif($this->Core->getParam('templateSource') == 'db'){
					$pages = $this->Core->DB->columnFetch(array('template_pages', 'name', 'url', db_main::q("`template`=#0 AND `template_type`=#1 AND `type`=#2", array($i, $tmplType, $type))));
					foreach($pages as $i1 => $e1) $pages[$i1] = $e1.' ('.$i1.')';
				}
				else $pages = array();

				$this->templatePages[$tmplType][$i] = array('name' => $e, 'pages' => $pages);
			}
		}

		return $this->templatePages[$tmplType];
	}

	public function __ava__getPages($id = 0){
		/*
			Возвращает записи структуры рекурсивно
		*/

		if(empty($this->pages)){
			$this->pages = $this->getEntriesRecursive($this->DB->columnFetch(array('pages', array('id', 'name', 'url', 'parent'), 'id', "`id`!='$id'", "`sort`")), 'name', 'parent', 'url', false, '', '');
		}
		return $this->pages;
	}

	public function __ava__pageIsAccessible($data){		/*
			Возвращает статус доступа пользователя к странице
		*/

		$t = time();
		if($data['start'] > $t || $data['stop'] < $t || !$data['show']) return false;

		switch($data['show']){			case 1:
				if(!$this->Core->userIsAdmin()) return false;
				break;

			case 2:
				if($this->Core->User->getStatus() < 1) return false;
				break;

			case 3:
				if($this->Core->User->getStatus() < 0) return false;
				break;

			case 4:
				break;

			default: return false;
		}

		return true;
	}

	public function __ava__pageAccessibleLevel(){		/*
			Возвращает уровень доступа по настройкам
		*/

		if($this->Core->userIsAdmin()) return 1;
		elseif($this->Core->User->getStatus() > 0) return 2;
		elseif($this->Core->User->getStatus() < 0) return 4;
		else return 3;
	}


	/********************************************************************************************************************************************************************

																			Создание страниц

	*********************************************************************************************************************************************************************/

	public function __ava__newPage($name, $url, $page_template, $parent){		/*
			Создает новую страницу. Возвращает ее ID
		*/

		$ins = compact('name', 'url', 'page_template', 'parent');
		$ins['date'] = time();
		$ins['vars'] = array('blocks' => array(), 'template' => array(), 'other' => array());
		return $this->DB->Ins(array('pages', $ins, 'extra' => array('notIgnore' => true)));	}

	public function __ava__newVersion($pageId, $values = false){
		/*
			Создает новую версию страницы. Возвращает ее ID
		*/

		if($values === false) $values = $this->values;

		$fields = $this->fieldValues(array('body', 'name', 'version_name', 'show'), $values);
		$fields['page_id'] = $pageId;
		$fields['params'] = $this->fieldValues(array('tags', 'parent', 'start', 'stop', 'sort'), $values);
		$fields['vars']['other'] = $this->fieldValues(array('simple_html'), $values);

		if(!$fields['params']['start']) $fields['params']['start'] = time();
		if(!$fields['params']['stop']) $fields['params']['stop'] = 2147000000;
		$fields['version_date'] = time();

		$pgList = $this->getTemplatePages();
		$fields['vars']['blocks'] = array();

		foreach($this->getContentBlocks() as $i => $e) $fields['vars']['blocks'][$e['name']] = isset($values[$e['name']]) ? $values[$e['name']] : '';
		foreach($pgList as $i => $e) $fields['vars']['template'][$i] = isset($values['template_'.$i]) ? $values['template_'.$i] : '';

		foreach($this->getUsedStructures() as $i => $e){
			if(!empty($values['insert_structure_'.$i])){
				foreach($this->getStructureMatrix($e['id'], $v, $ex) as $i1 => $e1){
					if(!empty($ex[$i1]['in_page_ins_style'])){
						if($ex[$i1]['in_page_ins_style'] == 4){
							$ex[$i1]['in_page_ins_style'] = 0;
							foreach($pgList as $i2 => $e2){
								foreach($e2 as $i3 => $e3){
									if(!empty($ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3]) && $ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3] > $ex[$i1]['in_page_ins_style']){
										$ex[$i1]['in_page_ins_style'] = $ex[$i1]['in_page_ins_style_'.$i2.'_'.$i3];
									}
								}
							}

							switch($ex[$i1]['in_page_ins_style']){
								case '1': $values[$ex[$i1]["blocks"]] = $values[$ex[$i1]['blocks_'.$i.'_'.$i1]]; break;
								case '2': $ex[$i1]['form_as'] = $values[$ex[$i1]['form_as_'.$i.'_'.$i1]]; break;
							}
						}

						switch($ex[$i1]['in_page_ins_style']){
							case '1': $fields['structure_params'][$i][$i1] = $values[$ex[$i1]["blocks"]]; break;
							case '3': $fields['structure_params'][$i][$i1] = isset($values['insert_structure_'.$i.'_'.$i1]) ? $values['insert_structure_'.$i.'_'.$i1] : ''; break;
							case '2':
								switch($ex[$i1]['form_as']){
									case 'link': $fields['structure_params'][$i][$i1] = 'index.php?mod='.$this->mod.'&func=page&id='.$values['url']; break;
									case 'parent_id': $fields['structure_params'][$i][$i1] = $values['parent']; break;
									case 'date': $fields['structure_params'][$i][$i1] = $fields['params']['start']; break;
									case 'ident': $fields['structure_params'][$i][$i1] = $values['url']; break;
									case 'text': $fields['structure_params'][$i][$i1] = $values['name']; break;
									case 'sort': $fields['structure_params'][$i][$i1] = $values['sort']; break;
									case 'show': $fields['structure_params'][$i][$i1] = isset($values['show']) ? $values['show'] : ''; break;
								}
								break;
						}
					}
				}
			}
		}

		return $this->DB->Ins(array('versions', $fields));
	}

	public function __ava__setDefaultVersion($id){
		/*
			Объявляет версию дефолтной
		*/

		$params = $this->getVersionParams($id, true);
		$fields = Library::array_merge(array('name' => $params['name'], 'body' => $params['body'], 'vars' => $params['vars'], 'show' => $params['show']), $params['params']);
		$fields['version_id'] = $id;

		$fields['tags'] = !empty($fields['tags']) ? Library::arrKeys2str($fields['tags']) : '';
		$issetStructEntries = $this->getStructuresInPage($params['page_id']);
		$structures = $this->getUsedStructures();

		foreach($structures as $i => $e){
			if(!empty($params['structure_params'][$i])){				if(!isset($issetStructEntries[$e['id']])){					$this->DB->Ins(array('structure_pages', array('page_id' => $params['page_id'], 'structure_id' => $e['id'], 'entry_id' => $this->DB->Ins(array($e['table'], $params['structure_params'][$i])))));				}
				else $this->DB->Upd(array($e['table'], $params['structure_params'][$i], "`id`='{$issetStructEntries[$e['id']]}'"));
			}		}

		$return = $this->DB->Upd(array('pages', $fields, "`id`='{$params['page_id']}'"));
		if(!empty($params['params']['tags'])) $this->reloadTagCounts($params['params']['tags']);
		return $return;
	}

	public function __ava__reloadTagCounts($tags = false){		/*
			пересчитывает теги
		*/
		if($tags === false || $tags === true) $tags = $this->getTagNames($tags);
		foreach($tags as $i => $e){
			$this->DB->Upd(array('tags', array('pop' => $this->DB->count(array('pages', "`tags` REGEXP (',$i,')"))), "`name`='$i'"));
		}
	}

	public function __ava__getDefaultPageValues($id, $pageTemplate){		/*
			Возвращает значения по умолчанию для страницы. Строит их на основании шаблона заполнения страницы
		*/

		if($pageTemplate){
			$return = array();
			$ptData = $this->getPageTemplate($pageTemplate);

			switch($ptData['vars']['publish_run_style']){				case 'current': $return['start'] = time() + $ptData['vars']['publish_run_correct']; break;
				case 'fix': $return['start'] = $ptData['vars']['publish_run_date']; break;
			}

			switch($ptData['vars']['publish_end_style']){
				case 'calc': $return['stop'] = $return['start'] + ($ptData['vars']['publish_end_correct'] * 3600); break;
				case 'fix': $return['stop'] = $ptData['vars']['publish_end_date']; break;
			}

			switch($ptData['vars']['sort_style']){
				case 'fix': $return['sort'] = $ptData['vars']['sort_value']; break;
				case 'min': $return['sort'] = $this->DB->cellFetch(array('pages', 'sort', '`show`>0', '`sort`')) - $ptData['vars']['sort_correct']; break;
				case 'max': $return['sort'] = $this->DB->cellFetch(array('pages', 'sort', '`show`>0', '`sort` DESC')) + $ptData['vars']['sort_correct']; break;
			}

			$return['show'] = $ptData['vars']['show_style'];
			$return['version_on'] = $ptData['vars']['version_style'];
			$return['body'] = $ptData['vars']['body_pre'].'<p>&nbsp;</p>'.$ptData['vars']['body_post'];

			foreach($this->getTemplatePages() as $i => $e) if(isset($ptData['vars']['template_'.$i])) $return['template_'.$i] = $ptData['vars']['template_'.$i];
			$params = Library::array_merge($return, $this->getPageValuesById($id));
			$parent = $params['parent'] ? $this->getPageValues($params['parent']) : array();

			foreach($this->getContentBlocks() as $i => $e){				switch($ptData['vars']['block_style_'.$i]){					case 'system': $return[$i] = isset($return[$ptData['vars']['sysfld_'.$i]]) ? $return[$ptData['vars']['sysfld_'.$i]] : $params[$ptData['vars']['sysfld_'.$i]]; break;
					case 'parent': $return[$i] = isset($parent[$i]) ? $parent[$i] : ''; break;
					case 'set': $return[$i] = $ptData['vars']['val_'.$i]; break;
				}			}

			foreach($this->getMatrix4StructuresArray() as $i => $e){				$return['insert_structure_'.$i] = empty($ptData['vars']['structure_on_'.$i]) ? 0 : 1;

				foreach($e as $i1 => $e1){					if(isset($ptData['vars']['structure_style_'.$i.'_'.$i1])){
						switch($ptData['vars']['structure_style_'.$i.'_'.$i1]){
							case 'system': $return['insert_structure_'.$i.'_'.$i1] = isset($return[$ptData['vars']['str_sysfld_'.$i.'_'.$i1]]) ? $return[$ptData['vars']['str_sysfld_'.$i.'_'.$i1]] : $params[$ptData['vars']['str_sysfld_'.$i.'_'.$i1]]; break;
							case 'set': $return['insert_structure_'.$i.'_'.$i1] = $ptData['vars']['str_val_'.$i.'_'.$i1]; break;
						}
					}
				}
			}
		}
		else{			$return = array('start' => time(), 'stop' => 2147000000, 'show' => '3', 'version_on' => '1');		}

		return $return;
	}

	public function __ava__getPageParams($url, $force = false){
		if($force || empty($this->pageParams[$url])){
			$this->pageParams[$url] = $this->DB->rowFetch(array('pages', '*', "`url`='$url'"));
			$this->pageParams[$url]['vars'] = Library::unserialize($this->pageParams[$url]['vars']);
			$this->pageParamsById[$this->pageParams[$url]['id']] = $this->pageParams[$url];
		}

		return $this->pageParams[$url];
	}

	public function __ava__getPageParamsById($id, $force = false){
		if($force || empty($this->pageParamsById[$id])){
			$this->pageParamsById[$id] = $this->DB->rowFetch(array('pages', '*', "`id`='$id'"));
			$this->pageParamsById[$id]['vars'] = Library::unserialize($this->pageParamsById[$id]['vars']);
			$this->pageParams[$this->pageParamsById[$id]['url']] = $this->pageParamsById[$id];
		}

		return $this->pageParamsById[$id];
	}

	public function __ava__getPageValues($url){
		$values = $this->getPageParams($url);
		$values = Library::array_merge($values, Library::concatPrefixArrayKey($values['vars']['template'], 'template_', ''));
		$values = Library::array_merge($values, $values['vars']['blocks']);
		$values = Library::array_merge($values, $values['vars']['other']);
		return $values;
	}

	public function __ava__getPageValuesById($id){
		$values = $this->getPageParamsById($id);
		$values = Library::array_merge($values, Library::concatPrefixArrayKey($values['vars']['template'], 'template_', ''));
		$values = Library::array_merge($values, $values['vars']['blocks']);
		$values = Library::array_merge($values, $values['vars']['other']);
		return $values;
	}

	public function __ava__getVersionParams($id, $force = false){
		if($force || empty($this->versionParams[$id])){
			$this->versionParams[$id] = $this->DB->rowFetch(array('versions', '*', "`id`='$id'"));
			$this->versionParams[$id]['vars'] = Library::unserialize($this->versionParams[$id]['vars']);
			$this->versionParams[$id]['params'] = Library::unserialize($this->versionParams[$id]['params']);
			$this->versionParams[$id]['structure_params'] = Library::unserialize($this->versionParams[$id]['structure_params']);
		}

		return $this->versionParams[$id];
	}

	public function __ava__getVersionValues($id){
		$values = $this->getVersionParams($id);
		$values = Library::array_merge($values, Library::concatPrefixArrayKey($values['vars']['template'], 'template_', ''));
		$values = Library::array_merge($values, $values['vars']['blocks']);

		$values = Library::array_merge($values, $values['vars']['other']);
		$values = Library::array_merge($values, $values['params']);
		foreach($values['structure_params'] as $i => $e){
			foreach($e as $i1 => $e1){				$values['insert_structure_'.$i.'_'.$i1] = $e1;
			}		}

		return $values;
	}

	protected function __ava__getPageFilter(){		/*
			Возвращает фильтр для вывода страницы
		*/

		$t = time();
		return "`show`>='".$this->pageAccessibleLevel()."' AND `start`<=$t AND `stop`>=$t";	}


	/********************************************************************************************************************************************************************

																		Установка пунктов меню

	*********************************************************************************************************************************************************************/

	public function __ava__insertMenuLinks($values, $tbl, $parent = ''){
		/*
			Добавляет типовой пункт меню в список
		*/

		if(!$this->DB->issetTable($tbl)){			if($this->DB->issetTable('structurecontent_'.$tbl)) $tbl = 'structurecontent_'.$tbl;
			else return false;		}

		if($parent) $values['parent_id'] = $this->DB->cellFetch(array($tbl, 'id', "`name`='$parent'"));
		return $this->DB->Ins(
			array(
				$tbl,
				array(
					'parent_id' => empty($values['parent_id']) ? '' : $values['parent_id'],
					'eval' => empty($values['eval']) ? '' : $values['eval'],
					'name' => empty($values['name']) ? '' : $values['name'],
					'text' => empty($values['text']) ? '' : $values['text'],
					'link' => empty($values['url']) ? '' : $values['url'],
					'sort' => $this->DB->cellFetch(array($tbl, 'sort', '', "`sort` DESC")),
					'show' => '1'
				)
			)
		);
	}
}

?>