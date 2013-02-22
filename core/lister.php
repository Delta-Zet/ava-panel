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



class lister extends Form{
	/*

		Класс формирует списки

		на странице размещается результат от $active до $active + $step - 1

		Основные блоки в списке
			1. Записи (сам список)
			2. Форма действий над списком
			3. Фрорма поиска
			4. Форма определения количества выводимых записей
			5. Паджинация
			6. Блок ссылок выбора по параметрам (Вывести все / вывести действующие и т.п.)
			7. Сохранение сортировки

		Примерный план организации
			1. Установить defaults
			2. Установить массив для перебора
			3. Установить параметры для записей
			4. Установить параметры для действий над списком
			5. Установить параметры для поиска
			6. Установить параметры для формы определения количества выводимых записей
			7. Установить параметры для паджинации
			8. Установить параметры для блока ссылок выбора по параметрам
			---
			9. Сгенерировать соответствующие блоки
			10. Сгенерировать шаблон

	*/

	public $templPref = 'list';
	public $listTemplates = array();
	public $entryTemplate;

	//Данные для построения паджинации
	public $active;			//Активная запись
	public $page;			//Отображаемая страница
	public $step;			//Записей на страницу

	protected $inBlock;		//Страниц на блок
	protected $pagCnt;		//Всего страниц
	protected $blkCnt;		//Всего блоков
	protected $blkCur;		//Текущий блок

	protected $pageStart;	//Стартовая страница из показанных
	protected $pageEnd;		//Конечная страница из показанных
	protected $entryStart;	//Первая запись на показаннй странице
	protected $entryEnd;	//Последняя запись на показаннй странице

	protected $url;			//Массив для формирования GET-запроса
	protected $j = 1;		//Счетчик записей


	//Данные основного запроса и работы со списком
	protected $req;			//Запрос к базе
	protected $extraReqs;	//Дополнительные запросы
	protected $unserialize = array();
	public $entries = array();		//Массив из всех записей
	protected $selected;	//Выбрано запросом


	//Данные запроса-подсчета и работы с паджинацией
	protected $countReq;	//Запрос-подсчет
	protected $count;		//Всего строк


	//Установки для генерации блоков
	public $heads = array();
	public $actions = array();
	public $quickSearchLinks = array();


	public function __ava__setDefaults($step = false, $active = 0, $page = 1){
		/*
			Устанавливает значения по умолчанию
		*/

		$this->step = $step;
		$this->active = (int)$active;
		$this->page = (int)$page;

		//Если активная зпись установлена а страница нет
		if($page == 'all'){
			$this->page = 1;
			$this->step = 0;
		}
		elseif(!$this->page && $this->active && $this->step){
			$this->page = ceil($this->active / $this->step);
		}
		elseif(!$this->page){
			$this->page = 1;
		}
	}

	public function __ava__setUrl($url){
		//Формируем URL для запросов по списку

		if(!is_array($url)){
			$url = parse_url($url);
			$url['base'] = $url['scheme'].'//'.$url['host'].$url['path'];
		}

		if(isset($url['query']) && !is_array($url['query'])){
			parse_str($url['query'], $url['query']);
		}

		$this->url = $url;
	}

	private function getUrl($disallow = array(), $additional = array(), $toBackLink = true){
		//Возвращает URL для ссылки

		$url = Library::array_merge($this->url['query'], $additional);
		$url['mod'] = $this->parent->getMod();
		$url['func'] = $this->parent->getFunc();

		if($GLOBALS['Core']->getGPCVar('callData', 'in_search')){
			if($toBackLink){
				$url['backLink'] = regExp::replace('=', '', base64_encode(Library::array2url(Library::array_merge($url, $GLOBALS['Core']->getGPCArr('callData')))));
			}
			else $url = Library::array_merge($GLOBALS['Core']->getGPCArr('callData'), $url);
		}

		return $this->url['base'].'?'.Library::array2url($url, $disallow);
	}

	public function __ava__setTemplates($templates){
		/*
			Устанавливает имена шаблонов для блока

			list - шаблон всего списка
			form - шаблон формы

			head - шаблон шапки, например head=mylist для headmylist
			entry - шаблон записи, например entry=mylist для entrymylist. Может быть постфикс 2,3,4 и т.д. для кратных соответствующему числу, например entrymylist2 и т.д.,
				также может быть установлен уровень вложенности префиксом nest1_, nest2 и т.п., например nest1_entrymylist, nest2_entrymylist2 и т.п.
			active - аналогично entry для выделенной активной записи, например activemylist, activemylist2, nest2_activemylist2

			pagin_link - ссылка для паджинации
			pagin_act - активная ссылка в паджинации
			pagin_block_link - ссылка для блока в паджинации
			pagin_block_act - активная ссылка блока в паджинации
		*/

		$this->listTemplates = $templates;

		//Считываем шаблоны форм
		$this->templates = Library::array_merge($GLOBALS['Core']->getTemplatePage('form'), $this->templates);
		if(!empty($this->listTemplates['form']) && $this->listTemplates['form'] != 'form'){
			$this->templates = Library::array_merge($this->templates, $GLOBALS['Core']->getTemplatePage($this->listTemplates['form']));
		}
	}

	public function __ava__setEntryTemplate($template){
		/*
			Устанавливает шаблон для записей
		*/

		$this->entryTemplate = $template;
	}



	/********************************************************************************************************************************************************************

																		Установка массива для перебора

	*********************************************************************************************************************************************************************/

	public function __ava__setMultilevelReq($req, $parent = 'parent_id', $id = 'id'){
		/*
			Многоуровневый запрос (с вставкой субзаписей по полю $parent)
			Для нормальной работы поле parent и id должно присутствовать в запросе и по нему должны быть отсортированы записи
			Возвращает массив:
				$return	[id] = значения
						[id][subblock]	[id] = значения
										[id][subblock] = значения
		*/

		$list = array();
		$dbObj = $this->DB->Req($req);
		while($r = $dbObj->Fetch()) $list[] = $r;
		$this->setMultilevelReqArray($list, $parent, $id);
	}

	public function __ava__setMultilevelReqArray($arr, $parent = 'parent_id', $id = 'id'){
		/*
			Многоуровневый запрос (с вставкой субзаписей по полю $parent)
			Для нормальной работы поле parent и id должно присутствовать в запросе и по нему должны быть отсортированы записи
			Возвращает массив:
				$return	[id] = значения
						[id][subblock]	[id] = значения
										[id][subblock] = значения
		*/

		$this->entries = array();
		foreach($arr as $r){
			if(!$r[$parent]) $this->entries[$r[$id]] = $r;
			else $this->insertSubblock($this->entries, $parent, $id, $r);
		}
	}

	private function insertSubblock(&$list, $parent, $id, $r){
		/*
			Добавляет субзапись в list
		*/

		if(!is_array($list)) return;

		foreach($list as $i => $e){
			if($i == $r[$parent]){
				$list[$r[$parent]]['subblock'][$r[$id]] = $r;
				return;
			}

			$this->insertSubblock($list[$i]['subblock'], $parent, $id, $r);
		}
	}

	public function __ava__setDBReq($req, $countReq = '', $extraReqs = array(), $unserialize = array()){
		/*
			Создает массив из MySQL запроса
			Также подсчитывает сколько всего записей можно выдрать из базы по этому запросу

			$extraReqs - Дополнительные запросы на дополнительные (как правило, базы данных), содержит позиции:
				'req' => Собственно запрос
				'DB' => Объект БД к которой идиот запрос
				'unitedFld1' => Поле в основном запросе по которому идет объединение
				'unitedFld2' => Поле в доп. запросе по которому идет объединение
				'prefix' => Приставка к именам полей в выборке
		*/

		$this->req = $req;
		$this->extraReqs = $extraReqs;
		$this->unserialize = $unserialize;

		//Добавляем Limit к запросу
		if(!empty($this->step)){
			$lim = (($this->page - 1) * $this->step).','.$this->step;

			if(is_array($this->req)){
				if(empty($this->req['limit']) && !empty($this->req['table'])){
					$this->req['limit'] = $lim;
				}
				elseif(empty($this->req['4']) && !empty($this->req['0'])){
					$this->req['4'] = $lim;
				}
			}
			else{
				if(!regExp::Match("/\sLIMIT\s.{1,10}$/i", $this->req, true)){
					$this->req .= ' LIMIT '.$lim;
				}
			}
		}

		//Производим запрос
		$dbObj = $this->DB->Req($this->req);
		$this->count = $this->selected = $dbObj->getRows();

		while($r = $dbObj->Fetch()){
			$this->entries[] = $r;
		}
		$this->extraReq();

		//Если нет смысла делать пажинацию
		if((($this->selected < $this->step) && ($this->page <= 1)) || !$this->step){
			return;
		}

		if(empty($countReq)){
			if(is_array($req)){
				$table = isset($req['table']) ? $this->DB->getPrefix().$req['table'] : $this->DB->getPrefix().$req['0'];
				$where = empty($req['where']) ? empty($req['2']) ? '' : 'WHERE '.$req['2'] : 'WHERE '.$req['where'];
				$countReq = "SELECT COUNT(id) FROM $table $where";
			}
			else{
				$countReq = regExp::replace("/LIMIT\s.{1,10}$/is", '', regExp::replace("/^SELECT\s+(.+)\s+FROM(.+)$/iUs", 'SELECT COUNT(id) FROM $2', trim($req), true), $countReq, true);
				if(regExp::Match("/FROM.+AS\s+(\w+)\s/iUs", $countReq, true, true, $m)) $countReq = regExp::Replace("COUNT(id)", "COUNT({$m['1']}.id)", $countReq);
			}
		}

		$this->countReq = $countReq;
		$this->count = $this->DB->Count($this->countReq);
	}

	private function extraReq(){
		/*
			Возвращает where для всех extra к данной записи
		*/

		if(empty($this->extraReqs)) return;

		foreach($this->extraReqs as $i => $e){
			$where = array();
			foreach($this->entries as $e1){
				$where[$e1[$e['unitedFld1']]] = "`{$e['unitedFld2']}`='{$e1[$e['unitedFld1']]}'";
			}

			if(!$where) return;
			if(is_array($e['req']) && is_array($e['req'][1]) && isset($e['req'][1]) && !in_array($e['unitedFld2'], $e['req'][1])) $e['req'][1][] = $e['unitedFld2'];
			elseif(is_array($e['req']) && isset($e['req']['fields']) && !in_array($e['unitedFld2'], $e['req']['fields'])) $e['req']['fields'][] = $e['unitedFld2'];

			db_main::innerWhere($e['req'], '('.implode(' OR ', $where).')');
			$obj = $e['DB']->Req($e['req']);
			$result = array();

			while($r = $obj->Fetch()){
				$result[$r[$e['unitedFld2']]] = array();
				foreach($r as $i2 => $e2){
					$result[$r[$e['unitedFld2']]][$e['prefix'].$i2] = $e2;
				}
			}

			foreach($this->entries as $i1 => $e1){
				if(empty($result[$e1[$e['unitedFld1']]])) continue;
				$this->entries[$i1] = Library::array_merge($this->entries[$i1], $result[$e1[$e['unitedFld1']]]);
			}
		}
	}

	public function __ava__setArrayReq($entries, $count = 0){
		/*
			Устанавливает массив для интерации
		*/

		foreach($entries as $i => $e){
			if(!isset($entries[$i]['id'])) $entries[$i]['id'] = $i;
		}

		$this->entries = $entries;
		$this->selected = count($entries);
		$this->count = empty($count) ? $this->selected : $count;
	}



	/********************************************************************************************************************************************************************

																Установка параметров для основных блоков

	*********************************************************************************************************************************************************************/

	public function __ava__setEntriesParams($actions = array(), $heads = array()){
		/*
			Параметры для построения списка
			$actions - Массив активных действий применяемых к записи. Имеет вид Идентификатор => Функция (например для выбора записи)
			$heads - массив заголовков к списку.
		*/

		$this->actions = $actions;
		$this->heads = $heads;
	}

	public function __ava__setQuickSearchLinks($links){
		$this->quickSearchLinks = $links;
	}

	public function __ava__setEntriesFormParams($action, $actions, $matrix, $values = array(), $hiddens = array(), $type = 'select',  $method = 'post'){
		/*
			Параметры для построения основной формы
		*/

		$this->setParam('action', $action);
		$this->setParam('method', $method);

		foreach($matrix as $i => $e){
			if(empty($matrix[$i]['template'])) $matrix[$i]['template'] = 'list';
		}

		if($type && $actions){
			$matrix['action'] = array(
				'type' => $type,
				'additional' => $actions,
				'text' => '{Call:Lang:core:core:sotmechennym}',
				'warn' => '{Call:Lang:core:core:vyneukazalic}',
				'template' => 'list'
			);
		}

		$this->setMatrix($matrix, 'form');
		$this->setValues($values);
		$this->setHiddens($hiddens);
	}

	public function __ava__setPaginateParams($inBlock = false){
		/*
			Параметры для паджинации
		*/

		$this->inBlock = $inBlock;

		if(!empty($this->step)) $this->pagCnt = ceil($this->count / $this->step);		//Всего страниц
		$this->pageStart = 1;
		$this->pageEnd = $this->pagCnt ? $this->pagCnt : 1;

		if(!empty($this->inBlock)){
			$this->blkCnt = ceil($this->pagCnt / $this->inBlock);			//Всего блоков
			$this->blkCur = ceil($this->page / $this->inBlock);				//Текущий блок
			$this->pageStart = (($this->blkCur - 1) * $this->inBlock) + 1;	//Первая страница текущего блока
			$this->pageEnd = (($this->pageStart + $this->inBlock - 1) < $this->pagCnt) ? $this->pageStart + $this->inBlock - 1 : $this->pagCnt;
		}
	}



	/********************************************************************************************************************************************************************

																Непосредственно генерация блоков

	*********************************************************************************************************************************************************************/

	public function addList(){
		/*
			Выполняет интерацию по списку, создавая его центральную часть
		*/

		//Определяем какие записи будут показаны
		$this->entryStart = (($this->page - 1) * $this->step) + 1;
		$this->entryEnd = $this->entryStart + $this->selected - 1;

		if(!empty($this->heads)){
			//Формируем заголовок
/*			$templates['head'] = empty($this->listTemplates['head']) ? '' : $this->listTemplates['head'];
			$headTmpl = empty($this->templates['head'.$this->name.$templates['head']]) ?
				$this->templates['entry'.$this->name] : $this->templates['head'.$this->name.$templates['head']];

			$cUrl = $this->getUrl(array('sort' => true, 'field' => true)); //стандартный URL для сортировки, используется в заголовке
			$block = '';

			foreach($this->heads as $i => $e){
				if(!is_array($e)){
					$replaces[$i] = $e;
					$replaces[$i.'_sortasc'] = $cUrl.'&sort=asc&field='.$i;
					$replaces[$i.'_sortdesc'] = $cUrl.'&sort=desc&field='.$i;
				}
				else{
					$replaces = Library::array_merge($replaces, $e);
					if(empty($replaces[$i]) && !empty($e['text'])) $replaces[$i] = $e['text'];
					if(empty($replaces[$i.'_sortasc'])) $replaces[$i.'_sortasc'] = $cUrl.'&sort=asc&field='.$i;
					if(empty($replaces[$i.'_sortdesc'])) $replaces[$i.'_sortdesc'] = $cUrl.'&sort=desc&field='.$i;
				}
			} */
		}

		if(!empty($this->templates['head'][$this->entryTemplate][0]['content'])) $this->setParam('list_head', $this->templates['head'][$this->entryTemplate][0]['content']);
		$this->setParam('list', $this->getEntries($this->entries));
	}

	public function __ava__getEntries($entries, $nesting = 0, $template = false){
		/*
			Функция перебирает записи одну за другой.
			Возвращает список как строку.

			Получает список из всех записей, шаблоны, сведения об уровне вложенности и j (параметр начала четности).
			В записи могут присутствовать 2 массива - entry и subentries.
			Если присутствует subentries, функция вызовет сама себя с параметрами из subentries, при этом будет пытаться обнаружить шаблоны с соответствующим
			уровнем вложенности и если не обнаружит их, будет использовать те что были переданы в нее саму

			templates может содержать entry - шаблон основной записи, active - шаблон активной записи

			к entry может добавляться постфикс 2, 3, 4 и т.д. который соответствует 2, 3, 4 и т.д. записи в шаблоне, что позволяет делить записи на четные и нечетные
			ко всем шаблонам добавляется постфикс равный имени списка($this->entryTemplate)

			также может существовать постфикс _nest1, _nest2 ..., соответствующий уровню вложенности для этой записи.

			Для применения активных действий к записям может применяться actions, который формирует записи
			{$i}_url, где $i соответствует индексу в actions и рекомендуется чтобы соответствовала имени поля в выборке
			$e - массив или текст. Во втором случае он соответствует функции выбора в URL. Все образуемые записи получают префикс {$i}_
		*/

		$return = '';
		if($template === false) $template = $this->entryTemplate;

		//Формируем основную часть
		foreach($entries as $r){
			if(!$this->getObjFlag('notEval') && !empty($r['eval']) && eval($r['eval']) === false) continue;
			foreach($this->unserialize as $e){
				$r = Library::array_merge(Library::unserialize($r[$e]), $r);
				unset($r[$e]);
			}

			//Обрабатываем actions
			if(empty($r['extra_actions'])) $r['extra_actions'] = '';
			foreach($this->actions as $i => $e){
				if(!is_array($e)){
					$r[$i.'_url'] = (regExp::match("#^(http://|https://|/)#iUs", $e, true) || regExp::match("?", $e)) ? $e.'&id='.$r['id'] : $this->getUrl(array('id' => true, 'func' => true)).'&func='.$e.'&id='.$r['id'];
					$r[$i.'_func'] = $e;
				}
				else{
					if(empty($e['id'])) $e['id'] = 'id';
					if(empty($e['func'])) $e['func'] = $this->url['query']['func'];
					if(empty($e['mod'])) $e['mod'] = $this->url['query']['mod'];

					$r[$i.'_url'] = $this->getUrl(array('id' => true, 'mod' => true, 'func' => true)).'&func='.$e['func'].'&mod='.$e['mod'].'&id='.$r[$e['id']];
					foreach($e as $i1 => $e1){
						$r[$i.'_'.$i1] = $e1;
					}
				}

				if(!empty($r[$i])) $r[$i] = '<a href="'.$r[$i.'_url'].'">'.$r[$i].'</a>';
				elseif(empty($r[$i]) && !empty($r[$i.'_text'])) $r['extra_actions'] .= '<a href="'.$r[$i.'_url'].'">'.$e['text'].'</a>';
			}

			//Прочее
			if($this->active == $this->j) $r['entry_active'] = true;
			$r['entry_count'] = $this->j;
			$r['nesting'] = $nesting;
			$r['extraParams'] = $this->params;

			if(!empty($r['subblock']) && is_array($r['subblock'])){
				$r['subblock'] = $this->getEntries($r['subblock'], $nesting + 1, $template);
			}

			$return .= $GLOBALS['Core']->replace($this->getTmplBlock($template), $this->parent, $r);
			$this->j ++;
		}

		return $return;
	}

	public function addForm(){
		/*
			Создает список для каких-либо манипуляций над выделенными позициями.
			Если этот блок не устанавливался, в списке не образуются checkbox.
			Помимо прочего устанавливает для формы url, method, extras (как массив, используется для формирования form_extras)
			Может принимать данные матрицы формы, тогда создает форму для обработки запроса для actions. Список также устанавливается через матрицу с использованием
			type. Если type пустой используется select. В качестке template используется list. Эти параметры могут быть переопределены через matrix.
			В качестве имени поля всегда используется action
		*/

		//Создаем форму
		$this->addBlock('form');
	}

	public function addPaginate(){
		/*
			Создает список страничек для навигации
			$inBlock - Количество ссылок на страницы показанных одновременно. Если false - неограниченно
			Паджинация не генерируется если количество всего <= чем размещается на 1 странице и отображается 1 страница
			В процессе помимо списка страниц создаются URL в replaces:
				url_start = начало
				url_end = конец
				url_next = следующая страница
				url_prev = предыдущая страница
				url_next_block = след. блок
				url_prev_block = пред. блок
				url_all = все
				url_block = url следующего блока (группы страниц)

			Для создания ссылок используются обычный шаблон - для всех ссылок и active - для текущей страницы
			Для каждой ссылки доступны replaces: $start, $end, $page, $url
		*/

		if(($this->count <= $this->selected) && ($this->page <= 1)){
			return '';
		}

		$url = $this->getUrl(array('page' => true), array(), false);
		$i = 0;

		if(!empty($this->inBlock)){
			//Записи для блоков страниц

			if($this->blkCnt > $this->blkCur) $this->setParam('url_next_block', $url.'&page='.($this->page + $this->inBlock));
			if($this->blkCur > 1) $this->setParam('url_prev_block', $url.'&page='.($this->page - $this->inBlock));

			$blocks_prev = '';
			$blocks_next = '';
			$blocks_paginate = '';

			for( $j = 1; $j <= $this->blkCnt; $j ++ ){
				$i = ($j - 1) * $this->inBlock;
				$replaces = array(
					'start' => $i + 1,
					'end' => (($i + $this->inBlock) > $this->pagCnt) ? $this->pagCnt : ($i + $this->inBlock),
					'start_entry' => $i * $this->step,
					'end_entry' => ($i + $this->inBlock) * $this->step,
					'block' => $j,
					'url' => $url.'&page='.(($j - 1) * $this->inBlock + 1)
				);

				if($j == $this->blkCur) $replaces['entry_active'] = true;
				$entry = $GLOBALS['Core']->replace($this->getTmplBlock('pagin_block_link', $replaces, 'extra'), $this->parent, $replaces);
				$this->block_paginate .= $entry;

				if($j < $this->blkCur) $blocks_prev .= $entry;
				elseif($j > $this->blkCur) $blocks_next .= $entry;
			}

			$this->setParam('blocks_prev', $blocks_prev);
			$this->setParam('blocks_next', $blocks_next);
			$this->setParam('blocks_paginate', $blocks_paginate);
		}

		if($this->page < $this->pagCnt){
			$this->setParam('url_next', $url.'&page='.($this->page + 1));
			$this->setParam('url_end', $url.'&page='.$this->pagCnt);
		}

		if($this->page > 1){
			$this->setParam('url_prev', $url.'&page='.($this->page - 1));
			$this->setParam('url_start', $url.'&page=1');
		}

		$paginate = '';
		for( $j = $this->pageStart; $j <= $this->pageEnd; $j ++ ){
			//Список страниц

			$i = ($j - 1) * $this->step;
			$replaces = array(
				'start' => $i + 1,
				'end' => $i + $this->step + 1,
				'page' => $j,
				'url' => $url.'&page='.$j
			);

			if($j == $this->pageEnd) $replaces['end'] = $this->count;
			if($j == $this->page) $replaces['entry_active'] = true;
			$paginate .= $GLOBALS['Core']->replace($this->getTmplBlock('pagin_link', $this->params, 'extra'), $this->parent, $replaces);
		}

		$this->setParam('url_all', $url.'&page=all');
		$this->setParam('paginate', $paginate);
	}

	public function addQuickSearchLinks(){
		/*
			Создает записи быстрой выборки по списку
		*/

		$linksBlock = '';
		foreach($this->quickSearchLinks as $i => $e){
			if(!isset($e['link'])){
				$e['params']['activeSearchVar'] = $i;
				$e['link'] = $this->getUrl(array(), $e['params'], false);
			}

			if(!empty($this->parent->values['activeSearchVar']) && $this->parent->values['activeSearchVar'] == $i) $e['active'] = true;
			$linksBlock .= $GLOBALS['Core']->replace($this->getTmplBlock('quick_search_link', $this->params, 'extra'), $this->parent, $e);
		}

		$this->setParam('quickSearchBlock', $linksBlock);
	}

	public function addAllBlocks(){
		/*
			Устанавливает блоки все скопом
		*/

		//Основные блоки
		if(!empty($this->entries)){
			$this->addList();
			$first = reset($this->entries);
		}
		if(!isset($first['sort'])) $this->clearParam('sortAction');

		if(!empty($this->matrixBlocks['form'])) $this->addForm();
		if($this->pagCnt > 1) $this->addPaginate();
		if($this->quickSearchLinks) $this->addQuickSearchLinks();

		//Данные по выборке
		$this->setParam('selected', (int)$this->selected);
		$this->setParam('count', (int)$this->count);
		$this->setParam('step', (int)$this->step);
		$this->setParam('in_block', (int)$this->inBlock);

		$this->setParam('pag_cnt', (int)$this->pagCnt);
		$this->setParam('blk_cnt', (int)$this->blkCnt);
		$this->setParam('pag_cur', (int)$this->page);
		$this->setParam('blk_cur', (int)$this->blkCur);

		$this->setParam('page_start', (int)$this->pageStart);
		$this->setParam('page_end', (int)$this->pageEnd);
		$this->setParam('entry_start', (int)$this->entryStart);
		$this->setParam('entry_end', (int)$this->entryEnd);
		$this->setParam('entry_count', (int)$this->j);

		//Прочее
		$this->setParam('name', $this->name);
		$url = $this->getUrl(array('page' => true));
		$this->setParam('url', $url);
		$this->setParam('enc_url', library::encodeUrl($url));
	}
}

?>