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


class mod_cms extends gen_cms{


	/********************************************************************************************************************************************************************

																	Контент-структуры

	*********************************************************************************************************************************************************************/

	protected function func_structure(){
		/*
			Функция создает контент-структуру циклически
			При создании используется класс lister
		 */

		$data = $this->DB->rowFetch(array('structures', '*', "`name`='".db_main::Quot($this->values['name'])."'"));
		$data['vars'] = Library::unserialize($data['vars']);

		if($data['type'] == 'table'){
			$data['STRUCTURE_MODULE'] = $this->Core->callModule($data['module']);
			$reqData = array(
				'arr' => $data['STRUCTURE_MODULE']->DB->columnFetch(
					array(
						$data['table'],
						isset($this->values['fieldsList']) ? $this->values['fieldsList'] : '*',
						isset($this->values['idField']) ? $this->values['idField'] : 'id',
						isset($this->values['where']) ? $this->values['where'] : '',
						isset($this->values['sort']) ? $this->values['sort'] : '',
						isset($this->values['limit']) ? $this->values['limit'] : '',
					)
				)
			);
		}
		else{
			if(empty($data['vars']['template'][$this->Core->getTemplateName('main')]) || !$this->DB->issetTable($data['table'])) return '';

			if($this->values['name'] == 'menu1'){
				$data['table'] .= '';
			}

			$req = array($data['table'], '*', "`show`", isset($this->values['sort']) ? $this->values['sort'] : "`parent_id`, `sort`, `id`");
			if(!empty($this->values['limit'])) $req['4'] = $this->values['limit'];
			$data['STRUCTURE_MODULE'] = $this;
			$reqData = array('req' => $req, 'multilevelReq' => array('parent' => 'parent_id', 'id' => 'id'));
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					isset($this->values['strTemplate']) ? $this->values['strTemplate'] : $data['vars']['template'][$this->Core->getTemplateName()],
					$reqData,
					$data,
					$this->Core->getTemplatePath('main').'structures.tmpl'
				),
				isset($this->values['coverTemplate']) ? $this->values['coverTemplate'] : $data['vars']['template'][$this->Core->getTemplateName()]
			)
		);

		return true;
	}


	/********************************************************************************************************************************************************************

																	Контент-структуры

	*********************************************************************************************************************************************************************/

	public function func_tags(){
		/*
			Выводит список тегов
		*/

		$this->setContent(
			$this->getListText(
				$this->newList(
					isset($this->values['tagTemplate']) ? $this->values['tagTemplate'] : 'simple',
					array('req' => array('tags', '*', "`show`", !empty($this->values['sort']) ? $this->values['sort'] : '`sort`')),
					array('sum' => $this->DB->sum(array('tags', 'pop', '`show`'))),
					$this->Core->getModuleTemplatePath($this->getMod()).'tags.tmpl'
				),
				isset($this->values['coverTemplate']) ? $this->values['coverTemplate'] : 'simple'
			)
		);
	}

	public function func_pagesByTag(){
		/*
			Выводит список тегов
		*/

		$tData = $this->getTag($this->values['tag']);
		$this->setMeta($tData['text']);
		$listName = isset($this->values['list']) ? $this->values['list'] : 'subpages';

		$this->setContent(
			$this->getListText(
				$this->newList(
					empty($this->values['tmpl']) ? 'subpages' : $this->values['tmpl'],
					array(
						'req' => array(
							'pages',
							Library::array_merge_numeric(array('id', 'version_id', 'name', 'date', 'url', 'vars'), isset($this->values['fieldsList']) ? $this->values['fieldsList'] : array()),
							"`tags` REGEXP (',{$this->values['tag']},') AND ".$this->getPageFilter().(!empty($this->values['where']) ? ' AND ('.$this->values['where'].')' : ''),
							isset($this->values['sort']) ? $this->values['sort'] : "`sort`, `date` DESC",
							isset($this->values['limit']) ? $this->values['limit'] : ''
						),
						'step' => empty($this->values['step']) ? 30 : $this->values['step']
					),
					array(),
					empty($this->values['tmplFile']) ? $this->Core->getModuleTemplatePath($this->mod).'subpages.tmpl' : $this->values['tmplFile']
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																		Создание страниц

	*********************************************************************************************************************************************************************/

	protected function func_page(){
		/*
			Установка всех контент-блоков, в т.ч. и основного
		*/

		$id = db_main::Quot($this->values['id']);
		$data = $this->DB->rowFetch(array('pages', '*', "`url`='$id'"));
		$t = time();

		if(!$data) throw new AVA_NotFound_Exception('Такой страницы не найдено');
		elseif(!$this->pageIsAccessible($data)) throw new AVA_Access_Exception('У вас нет прав доступа к этой странице');
		elseif($data['start'] > $t || $data['stop'] < $t) throw new AVA_Access_Exception('Эта страница снята с публикации');

		$data['vars'] = Library::unserialize($data['vars']);
		if(!empty($data['vars']['template'][$tmplName = $this->Core->getTemplateName('main')])) $this->Core->setTempl($data['vars']['template'][$tmplName]);

		foreach($this->getAllContentBlocks() as $i => $e){
			if($e['show'] == '1') $blocks[$i] = $e['vars']['matrix']['value'];
			elseif($e['show'] == '2'){
				if(empty($blocks[$i])) $blocks[$i] = '';
				else $blocks[$i] = $e['vars']['matrix']['value'];
			}
			elseif($e['show'] == '3' || $e['show'] == '4'){
				$blocks[$i] = isset($data['vars']['blocks'][$i]) ? $data['vars']['blocks'][$i] : '';
			}
		}

		$this->setContent($data['body']);
		foreach($blocks as $i => $e){
			$this->setNewContent($e, $i);
		}
	}

	protected function func_subpages(){
		/*
			Создает список всех подстраниц для страницы
		*/

		$pUrl = empty($this->values['parent']) ? $this->values['id'] : $this->values['parent'];
		$listName = isset($this->values['list']) ? $this->values['list'] : 'subpages';

		$this->setContent(
			$this->getListText(
				$this->newList(
					empty($this->values['tmpl']) ? 'subpages' : $this->values['tmpl'],
					array(
						'req' => array(
							'pages',
							Library::array_merge_numeric(array('id', 'version_id', 'name', 'date', 'url', 'vars'), isset($this->values['fieldsList']) ? $this->values['fieldsList'] : array()),
							"`parent`='$pUrl' AND ".$this->getPageFilter().(!empty($this->values['where']) ? ' AND ('.$this->values['where'].')' : ''),
							isset($this->values['sort']) ? $this->values['sort'] : "`sort`, `date` DESC",
							isset($this->values['limit']) ? $this->values['limit'] : ''
						),
						'step' => empty($this->values['step']) ? 30 : $this->values['step']
					),
					array(),
					empty($this->values['tmplFile']) ? $this->Core->getModuleTemplatePath($this->mod).'subpages.tmpl' : $this->values['tmplFile']
				)
			)
		);
	}


	/***************************************************************************************************************************************************************

																			Вывод форм

	****************************************************************************************************************************************************************/

	protected function func_form(){
		/*
			Создает произвольную форму
		*/

		$fData = $this->getForm($this->values['form']);
		if(!$fData) throw new AVA_notFound_Exception('Форма "'.$this->values['form'].'" не найдена');
		elseif(empty($fData['show'])) throw new AVA_Access_Exception('Доступ к форме "'.$fData['text'].'" запрещен');

		$this->setMeta($fData['vars']['caption']);
		$method = 'post';
		$action = '';

		switch($fData['vars']['action_type']){
			case '1': $action = 'formAdd'; break;
			case '2':
				$action = $fData['vars']['action'];
				$method = $fData['vars']['action_method'];
				break;
		}

		$form = $this->newForm($this->values['form'], $action, array('caption' => $fData['vars']['caption'], 'method' => $method));
		list($matrix, $values) = $this->getMatrixArray(array('form_blocks', '*', "`form_id`='{$this->values['form']}' AND `show`>0", "`sort`"));
		$this->setContent($this->getFormText($this->addFormBlock($form, $matrix), $values, array('form' => $this->values['form'])));
	}

	protected function func_formAdd(){
		/*
			Отправляет произвольную форму на исполнение
		*/

		if(!$this->check()) return false;

		$fData = $this->getForm($this->values['form']);
		if(!$fData) throw new AVA_notFound_Exception('Форма "'.$this->values['form'].'" не найдена');
		elseif(empty($fData['show'])) throw new AVA_Access_Exception('Доступ к форме "'.$fData['text'].'" запрещен');

		$values = $this->getGeneratedFormValues(array('form_blocks', '*', "`form_id`='{$this->values['form']}' AND `show`>0", "`sort`"));

		if(!empty($fData['vars']['save_style']['db'])){
			switch($fData['vars']['save_style_table']){
				case '1': $table = $fData['vars']['new_table'];
				case '3':
					$mod = $this;
					if(empty($table)){
						$strData = $this->getStructureParams($fData['vars']['structure']);
						$table = $strData['table'];
					}

				case '2':
					if(empty($mod)){
						$mod = $this->Core->callModule($fData['vars']['module']);
						$table = $fData['vars']['table'];
					}

					$ins = array();
					foreach($values as $i => $e){
						if($mod->DB->issetField($table, $i)) $ins[$i] = $e;
						elseif($mod->DB->issetField($table, 'vars')) $ins['vars'][$i] = $e;
						elseif($mod->DB->issetField($table, 'extra')) $ins['extra'][$i] = $e;
					}

					if($ins) $this->DB->Ins(array($table, $ins));
					break;
			}
		}

		if(!empty($fData['vars']['save_style']['eml'])){
			foreach(regExp::split("\n", $fData['vars']['eml']) as $i => $e){
				$e = trim($e);
				if($e) $this->mail($e, $fData['vars']['eml_template'], $values);
			}
		}

		if(!empty($fData['vars']['save_style']['http'])){
			$http = new httpClient($fData['vars']['url'], $fData['vars']['method']);
			$http->setVars($values);
			$http->send();
		}

		$this->refresh(isset($this->values['back']) ? $this->values['back'] : 'form&form='.$this->values['form']);
	}


	/***************************************************************************************************************************************************************

																			Карта сайта

	****************************************************************************************************************************************************************/

	protected function func_map(){
		/*
			Создает карту сайта
		*/

		if(!empty($this->values['raw'])) $this->Core->setFlag('rawOutput');
		else $this->setMeta('Карта сайта');
		$tmpl = !empty($this->values['map_tmpl']) ? $this->values['map_tmpl'] : 'simple';
		$reqData['arr'] = array();

		$result = $this->callAllMods('__map', array(), $_, true);
		if(isset($result['core'])) foreach($result['core'] as $i => $e) $reqData['arr'][] = $e;
		if(isset($result['main'])) foreach($result['main'] as $i => $e) $reqData['arr'][] = $e;
		unset($result['main'], $result['core']);

		foreach($result as $i => $e){
			$reqData['arr'][$i]['name'] = $this->Core->getModuleName($i);
			$reqData['arr'][$i]['subblock'] = $e;
		}

		$this->setContent($this->getListText($this->newList($tmpl, $reqData, array(), $this->Core->getModuleTemplatePath($this->getMod()).'map.tmpl'), $tmpl));
	}


	/********************************************************************************************************************************************************************

																			Вспомогательные функции

	*********************************************************************************************************************************************************************/

	protected function func_getDefaultMeta(){
		/*
			Устанавливает дефолтные Meta-параметры - title, keywords, description и пр.
		*/

		$this->setContent($this->Core->Site->params['name'], 'title');
	}

	protected function func_rssFeed(){
		/*
			Экспорт RSS
		*/

		$xml = array();
		$t = time();
		$rssList = $this->DB->columnFetch(array('export', '*', 'name', "`show` AND `last`<($t - `update_interval`)", "`sort`"));

		foreach($rssList as $i => $e){
			$http = new httpClient($e['url']);
			$http->send();
			$parsed = XML::parseXML($http->getResponseBody());
			$xml[$i] = library::isHash($parsed['rss']['channel']['item']) ? array($parsed['rss']['channel']['item']) : $parsed['rss']['channel']['item'];
		}

		$filter = array();
		foreach($xml as $i => $e){
			foreach($e as $i1 => $e1){
				$filter[$e1['link']] = $e1['link'];
			}
		}

		$isset = $this->DB->columnFetch(array('exported_urls', 'id', 'url', $this->getEntriesWhere($filter, 'url')));
		foreach($xml as $i => $e){
			foreach($e as $i1 => $e1){
				if(!isset($isset[$e1['link']])){
					$link = $e1['link'];
					$pgId = 0;

					$url = regExp::substr(regExp::Replace("|\W|", '', regExp::Replace("|\S|", '_', Library::cyr2translit(trim($e1['title'])), true), true), 0, 12);
					$j = '';
					while($this->DB->cellFetch(array('pages', 'url', "`parent`='{$rssList[$i]['parent_page']}' AND `url`='{$url}{$j}'"))) $j ++;

					if($rssList[$i]['format'] == 'f'){
						$pgId = $this->DB->Ins(
							array(
								'pages',
								array(
									'parent' => 'news',
									'version_id' => '',
									'date' => $t,
									'start' => $t,
									'stop' => 2147000000,
									'name' => $e1['title'],
									'url' => $url.$j,
									'body' => $e1['content:encoded'],
									'vars' => array(
										'blocks' => array(
											'title' => $e1['title'],
											'caption' => $e1['title']
										),
										'template' => array(
											'default' => 'news.tmpl'
										)
									),
									'show' => '3'
								)
							)
						);

						$link = 'index.php?mod='.$this->mod.'&func=page&id='.$url;
					}

					$this->DB->Ins(
						array(
							'news',
							array(
								'name' => $url.$j,
								'text' => $e1['title'],
								'eval' => '',
								'show' => 1,
								'date' => $t,
								'link' => $link,
								'notice' => $e1['description']
							)
						)
					);

					$this->DB->Ins(
						array(
							'exported_urls',
							array(
								'feed_id' => $i,
								'url' => $e1['link'],
								'date' => $t,
								'vars' => $e1,
								'page_id' => $pgId
							)
						)
					);
				}
			}
		}

		$this->Core->setFlag('rawOutput');
	}
}

?>