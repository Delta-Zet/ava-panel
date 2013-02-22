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


class moduleInterface extends objectInterface{

	private $content = array();			//Массив содержащий все параметры контентного наполнения

	//Формы
	public $objects = array();			//Список всех форм, списков и др. объектов. Позволяет предотвратить дублирование имен форм на странице. Также возможно потребуется для создания персональных шаблонов страниц, для отметки места формы на странице
	public $errorMessages = array();	//Ошибки
	public $messages = array();			//Сообщения (лог)
	public $curFormObj;					//Объект проверяемой формы

	//Прочее
	protected $func;					//Вызванный через callFunc последний метод
	protected $mod;						//Установленное имя объекта-потомка
	protected $modName;					//Имя модуля в системе
	private $settedFuncPlugs = false;
	private $dependModules = false;
	public $values;						//Установленные значения. Слитые вместе массивы $_POST и $_GET

	public $pathPoint = array();		//Путь
	public $pathFunc = array();			//Функция-родитель
	public $funcName = array();			//Название текущей функции (просто текст)

	public $path;						//Путь URL до ?
	public $callUrl;					//Путь полный

	private $debId;						//Идентификатор объекта который в данный момент проверяется на скорость выполнения

	//Ссылки на глобальные объекты
	public $DB;							//Ссылка на объект БД
	public $Core;						//Ссылка на объект Core
	public $User;						//Ссылка на объект User

	//Ответы вызовов методов
	public $returns = array();
	public $lastReturn;

	public function __construct($mod, $modName, $DB, $values = array()){
		if($mod === false){
			if($modName) $this->modName = $modName;
			if(is_object($DB)) $this->DB = $DB;
			return;
		}
		unset($values['tmplName']);

		$this->Core = $GLOBALS['Core'];
		$this->User = $GLOBALS['Core']->User;
		$this->DB = $DB;

		$this->mod = db_main::Quot($mod);
		$this->modName = $modName;
		$this->values = $values;

		if(!empty($this->values['ava_form_transaction_id'])){
			$form = $this->loadForm($this->values['ava_form_transaction_id']);
			foreach($form->matrix as $i => $e){
				if(!isset($this->values[$i]) && isset($this->values[regExp::replace('.', '_', $i)])){
					$this->values[$i] = $this->values[regExp::replace('.', '_', $i)];
					unset($this->values[regExp::replace('.', '_', $i)]);
				}

				if($e['type'] == 'calendar' || $e['type'] == 'calendar2') $this->values[$i] = Dates::intTime($this->values[$i]);
				if($e['type'] == 'calendar') $this->values[$i.'_to'] = Dates::intTime($this->values[$i.'_to']);

				if($e['type'] == 'file'){
					if(!empty($this->values[$i]['name'])){
						if(isset($e['additional']['newName'])) $nn = $e['additional']['newName'];
						elseif(isset($e['additional']['newNameFrom']) && !empty($this->values[$e['additional']['newNameFrom']])){
							$pref = isset($e['additional']['newNamePrefix']) ? $e['additional']['newNamePrefix'] : '';
							$postf = isset($e['additional']['newNamePostfix']) ? $e['additional']['newNamePostfix'] : '';
							$nn = $pref.$this->values[$e['additional']['newNameFrom']].$postf;
						}
						else $nn = false;

						if(!$this->values[$i] = Files::moveUploads($this->values[$i], $nn)){
							$this->setError($i, '{Call:Lang:core:core:problemazagr}');
						}
					}
					elseif(isset($this->values[$i.'_hidden'])) $this->values[$i] = $this->values[$i.'_hidden'];
					else $this->values[$i] = '';
				}
			}
		}

		if(defined('IN_INSTALLATOR') && IN_INSTALLATOR > 0){
			$this->path = _D.'install/index.php';
		}
		elseif(defined('IN_ADMIN') && IN_ADMIN > 0){
			$this->path = _D.ADMIN_FOLDER.'/index.php';
		}
		else{
			$this->path = _D.'index.php';
		}

		if(!$mod) return;

		if(is_object($this->User)){
			if($this->User->getUserId()){
				foreach($this->User->params as $i => $e){
					$this->setContent($e, 'user_'.$i);
				}
			}
			if($this->User->getAdminId()){
				foreach($this->User->adminParams as $i => $e){
					$this->setContent($e, 'admin_'.$i);
				}
			}
		}

		if(method_exists($this, '__init') || method_exists($this, '__ava____init')) $this->__init();
	}



	/******************************************************************************************************************************************************************

																	Внешние интерфейсы

	******************************************************************************************************************************************************************/

	public final function __ava__callFunc($func){
		/*
			Обращается к методу класса
		*/

		if(Library::constVal('IN_ADMIN') && !Library::constVal('IN_INSTALLATOR') && !$this->Core->User->isAuthority($this->mod, $func)) throw new AVA_Access_Exception('{Call:Lang:core:core:uvasnetprava}');
		$this->errorMessages = array();
		$this->objects = array();

		$this->func = db_main::Quot($func);
		$func = 'func_'.$func;
		$this->__callFunctionPlugins($this->func, 'before');

		if(($return = $this->__callFunctionPlugins($this->func, 'instead')) == 'noAnyPlugins'){
			if(!method_exists($this, $func)){
				throw new AVA_NotFound_Exception('{Call:Lang:core:core:nesushchestv1:'.Library::serialize(array($func, $this->mod)).'}');
			}
			$this->returns[$func] = $this->lastReturn = $this->$func();
		}
		else{
			$this->returns[$func] = $this->lastReturn = $return;
		}

		$this->__callFunctionPlugins($this->func, 'after');
		return $this->lastReturn;
	}

	public function __callFunctionPlugins($func, $point){
		/*
			Вызывает плаги функции
		*/

		if($this->settedFuncPlugs === false){
			$this->settedFuncPlugs = array();
			foreach($this->Core->getPlugins('function', $this->Core->getCurrentPluginService()) as $i => $e){
				$params = $this->Core->getPlugin($e);
				if($params['mod'] == $this->mod){
					$this->settedFuncPlugs[$params['function']][$params['point']][$i] = $e;
				}
			}
		}

		$return = array();
		if(isset($this->settedFuncPlugs[$func][$point])){
			foreach($this->settedFuncPlugs[$func][$point] as $i => $e){
				$return[$i] = $this->evalPlugin($e);
			}
		}

		switch(count($return)){
			case 0: return 'noAnyPlugins';
			case 1: return $return[Library::firstKey($return)];
			default: return $return;
		}
	}

	public function __ava__callFuncWithArray($func, $values = array()){
		/*
			Обращается к некой функции с массивом, устанавливаемым как values для этой функции
		*/

		$this->values = $values;
		return $this->callFunc($func);
	}

	public function __ava__callFuncAndGetFormId($func, $formName, $values = array()){
		/*
			Обращается к функции с массивом переменных. Возвращает ID установленной формы.
		*/

		$this->callFuncWithArray($func, $values);
		return $this->getFormId($formName);
	}

	public function callCoMods($func, $param = array(), &$cnt = 0, $downMod = 'cms'){
		/*
			Обращается к объединенным с данным модулям
		*/

		if($downMod){
			foreach($this->Core->getUnitedDownModules($this->Core->getUnitedModule($this->mod, $downMod)) as $i => $e){
				if($this->mod == $i) continue;
				$mObj = $this->Core->callModule($i);

				if(method_exists($mObj, $func) || method_exists($mObj, '__ava__'.$func)){
					$mObj->$func($this, $param, $cnt);
					$called[$i] = true;
				}
			}
		}

		foreach($this->Core->getUnitedDownModules($this->mod) as $i => $e){
			if(empty($called[$i])){
				$mObj = $this->Core->callModule($i);
				if(method_exists($mObj, $func) || method_exists($mObj, '__ava__'.$func)) $mObj->$func($this, $param, $cnt);
			}
		}
	}

	public final function callAllMods($func, $param = array(), &$cnt = 0, $thisMod = false){
		/*
			Обращается ко всем модулям в системе
		*/

		$return = array();
		$mList = $this->Core->getModules();
		$mList['main'] = 'main';
		if($this->Core->UserIsRoot()) $mList['core'] = 'core';

		foreach($mList as $i => $e){
			if($thisMod || $i != $this->mod){
				$mObj = $this->Core->callModule($i);
				if(method_exists($mObj, $func) || method_exists($mObj, '__ava__'.$func)) $return[$i] = $mObj->$func($this, $param, $cnt);
			}
		}

		return $return;
	}

	public final function __ava__getFormId($name){
		/*
			Возвращает ID формы
		*/

		if(!isset($this->objects[$name])) throw new AVA_Exception('{Call:Lang:core:core:formynesushc:'.Library::serialize(array($name)).'}');
		if(empty($this->objects[$name]->hiddens['ava_form_transaction_id'])) $this->objects[$name]->setHidden('ava_form_transaction_id', $this->saveObj($this->objects[$name]));
		return $this->objects[$name]->hiddens['ava_form_transaction_id'];
	}

	public final function __ava__getReturn($func = false){
		if($func === false) return $this->lastReturn;
		return $this->returns[$func];
	}

	public final function __ava__getMod(){
		return $this->mod;
	}

	public final function __ava__getFunc(){
		return $this->func;
	}

	public final function __ava__getPathFunc(){
		return $this->pathFunc;
	}

	public final function __ava__getFuncName(){
		return $this->funcName;
	}

	public final function __ava__setContent($data, $var = 'body', $pos = 'post'){
		/*
			Добавляет значение в массив контента
		*/

		if($pos == 'pre') $this->content[$var] = $data.(isset($this->content[$var]) ? $this->content[$var] : '');
		else $this->content[$var] = (isset($this->content[$var]) ? $this->content[$var] : '').$data;
	}

	public final function __ava__setNewContent($data, $var = 'body'){
		/*
			Добавляет значение в массив контента
		*/

		$this->content[$var] = $data;
	}

	public final function __ava__issetContentVar($var){
		/*
			Проверяет установлена ли вообще контент-переменная
		*/

		return isset($this->content[$var]);
	}

	public final function __ava__getContent(){
		/*
			Возвращает массив контента
		*/

		return $this->content;
	}

	public final function __ava__getContentVar($var){
		/*
			Возвращает переменную внутри массива content
		*/

		if(!isset($this->content[$var])) return '';
		return $this->content[$var];
	}

	public final function __ava__clearContent(){
		$this->content = array();
	}

	public final function __ava__getCallUrl($extra = array()){
		/*
			Возвращает URL по которому было обращение к данной странице
		*/

		if(empty($this->callUrl)){
			$this->callUrl = $this->path.'?&mod='.$this->mod.'&func='.$this->func;

			foreach($this->Core->getGPCArr('g') as $i => $e){
				if(is_array($e)){
					$this->callUrl .= $this->getCallUrlArrayVar($i, $e);
				}
				else $this->callUrl .= "&{$i}={$e}";
			}
		}

		$return = $this->callUrl;
		foreach($extra as $i => $e){
			if(is_array($e)){
				$return .= $this->getCallUrlArrayVar($i, $e);
			}
			else $return .= "&{$i}={$e}";
		}

		return $return;
	}

	private final function getCallUrlArrayVar($var, $arr){
		$return = '';
		foreach($arr as $i => $e){
			if(is_array($e)) $return .= $this->getCallUrlArrayVar($var.'['.$i.']', $e);
			else $return .= "&{$var}[{$i}]={$e}";
		}

		return $return;
	}


	/******************************************************************************************************************************************************************

																	Выдача служебных страниц

	******************************************************************************************************************************************************************/

	protected final function __ava__redirect($func, $mod = '', $msg = ''){
		/*
			Осуществляет перенаправление по headers. Для серверов не поддерживающих header используется редирект html
		*/

		if(!$mod) $mod = $this->mod;
		$msg .= '{Call:Lang:core:core:podozhditese:'.Library::serialize(array($this->getPrintMessages())).'}';

		$refresh_url = $this->path.'?mod='.$mod.'&func='.$func;
		$this->Core->setHeader('Location', $refresh_url);
		$this->refresh($func, $msg, $mod);
	}

	protected final function __ava__redirect2($url, $msg = ''){
		/*
			Осуществляет перенаправление по headers. Для серверов не поддерживающих header используется редирект html
		*/

		$this->Core->setHeader('Location', $url);
		$msg .= '{Call:Lang:core:core:podozhditese:'.Library::serialize(array($this->getPrintMessages())).'}';

		$this->Core->setTemplateType('system');
		$this->Core->setTempl('refresh');
		$this->setContent($this->path, $url);

		$this->setContent($msg, 'refresh_msg');
		$this->Core->setFlag('refreshed', true);
	}

	protected final function __ava__refresh($func, $msg = false, $mod = ''){
		/*
			Осуществляет перенаправление по refrresh в html-коде.
		*/

		if($this->Core->getFlag('refreshed')){
			return;
		}

		if($msg === false){
			$msg = '{Call:Lang:core:core:izmeneniiavn}';
		}
		$msg = $this->getPrintMessages().$msg;

		if(empty($mod)){
			$mod = $this->mod;
		}

		$this->Core->setTemplateType('system');
		$this->Core->setTempl('refresh');

		if(!empty($this->values['backLink'])){
			$func .= '&'.base64_decode($this->values['backLink']);
		}

		if($func) $this->setContent($this->path.'?mod='.$mod.'&func='.$func, 'refresh_url');
		else $this->setContent($this->path, 'refresh_url');

		$this->setContent($msg, 'refresh_msg');
		$this->Core->setFlag('refreshed', true);
	}

	protected final function __ava__back($func, $msg = false, $mod = '', $errorMsg = '{Call:Lang:core:core:vprotsessevy}'){
		/*
			Выдает страницу с надписью "Назад". Не использует редиректа.
		*/

		if($this->Core->getFlag('refreshed')) return;
		if($msg === false) $msg = '{Call:Lang:core:core:izmeneniiane}';
		if($errorMsg) $this->setError('', $errorMsg);
		$msg = $this->getPrintMessages().$msg;

		if(empty($mod)){
			$mod = $this->mod;
		}

		$this->Core->setTemplateType('system');
		$this->Core->setTempl('back');

		if($func) $this->setContent($this->path.'?mod='.$mod.'&func='.$func, 'refresh_url');
		else $this->setContent($this->path, 'refresh_url');

		$this->setContent($msg, 'refresh_msg');
		$this->Core->setFlag('refreshed', true);
	}

	public final function getPrintMessages($messages = false){
		/*
			Возвращает список всех сообщений в HTML-формате
		*/

		$return = '';
		if($messages === false) $messages = $this->messages;
		foreach($messages as $i => $e) $return .= $e.'<br/><br/>';
		return $return;
	}

	protected final function getErrorsList(){
		/*
			Возвращает список всех возникших ошибок в HTML-формате
		*/

		$return = '';
		foreach($this->errorMessages as $i => $e){
			$return .= '<span class="error">'.$e.'</span><br/><br/>';
		}

		return $return;
	}


	/******************************************************************************************************************************************************************

																	Работа с формами

	******************************************************************************************************************************************************************/

	public final function __ava__emptyForm(){
		/*
			Возвращает пустую форму, в которой не принципиальны ни какие параметры, типа имени и т.п.
		*/

		return new Form('__', array(), '', $this);
	}

	public final function __ava__newForm($name, $action, $params = array(), $templFile = 'form', $forceDefTmplRead = true){
		/*
			Создает новую форму
		*/

		if(isset($this->objects[$name])){
			throw new AVA_Exception('{Call:Lang:core:core:obektsimenem:'.Library::serialize(array($name)).'}');
		}

		if(SHOW_HWT > 0) $this->debId = $this->Core->debugStart();
		if(empty($params['method'])) $params['method'] = 'post';
		$params['action'] = regExp::Match("#^[A-z]+://#iUs", $action, true) ? $action : (regExp::Match('&mod=', $action) ? $this->path.'?func='.$action : $this->path.'?mod='.$this->mod.'&func='.$action);

		$this->curFormObj = $this->objects[$name] = new Form($name, $params, $templFile, $this, $forceDefTmplRead);
		return $this->curFormObj;
	}

	public final function __ava__addFormBlock($form, $formMatrix, $formData = array(), $exclude = array(), $block = 'form'){
		$hiddens = array();
		$matrix = array();
		if(!is_object($form)){
			if(!is_object($this->curFormObj)) throw new AVA_Exception('Не определен объект формы');
			$form = $this->curFormObj;
		}

		extract($formData);

		if(!is_array($formMatrix)){
			$file = $this->getFormFile($formMatrix);
			require($file);
		}
		elseif(!Library::isHash($formMatrix)){
			//Подключаем кучу матриц

			foreach($formMatrix as $entryMatrix){
				if(is_array($entryMatrix)){
					$matrix = Library::array_merge($matrix, $entryMatrix, 2);
				}
				else{
					$file = $this->getFormFile($entryMatrix);
					require($file);
				}
			}
		}
		else{
			$matrix = Library::array_merge($matrix, $formMatrix, 2);
		}

		if(!is_array($exclude)) $exclude = array($exclude);
		$form->setExcludes($exclude);

		if(!empty($hiddens)) $form->setHiddens($hiddens);
		if(!empty($matrix)) $form->setMatrix($matrix, $block);
		if(!empty($values)) $form->setValues($values);

		return $form;
	}

	public final function __ava__getFormText($form = false, $values = array(), $hiddens = array(), $templName = ''){
		/*
			Завершающая стадия генерации формы
		*/

		if(!is_object($form)){
			$form = $this->curFormObj;
		}

		if(!empty($values)){
			$form->clearVar('values');
		}

		if(!empty($this->values['backLink'])) $hiddens['backLink'] = $this->values['backLink'];

		$form->setHiddens($hiddens);
		$form->setValues($values);
		$form->setTemplName($templName);

		if(empty($form->hiddens['redirect']) && !empty($this->values['redirect'])) $form->setHidden('redirect', $this->values['redirect']);
		$form->exclude();
		$form->setHidden('ava_form_transaction_id', $this->saveObj($form));

		$templText = $this->getObjText($form);
		if(empty($templText)) return '';
		$this->setContent($templText, $form->getName());

		if(!$this->Core->getFlag('formJS')){
			$this->Core->getMainModObj()->setContent('<script type="text/javascript" src="'._D.'js/form.js"></script>', 'head');
			$this->Core->setFlag('formJS');
		}

		if(SHOW_HWT > 0) $GLOBALS['Core']->setDebugAction('{Call:Lang:core:core:vremiaraboty2:'.Library::serialize(array($name)).'}');
		return $templText;
	}

	private function getObjText($obj){
		return $obj->getText();
	}

	private function getFormFile($file){
		/*
			Определяет имя файла подключаемой матрицы
		*/

		if(!regExp::Match("/^(\/|\.)/", $file, true)){
			$inMod = _W.'modules/'.$this->modName.'/forms/'.$file.'.php';
			$file = file_exists($inMod) ? $inMod : _W.'forms/'.$file.'.php';
		}

		if(!file_exists($file)){
			throw new AVA_Files_Exception('{Call:Lang:core:core:nenajdenfajl3:'.Library::serialize(array($inMod)).'}');
		}

		return $file;
	}

	public final function __ava__saveObj($obj){
		/*
			Сохраняет сведения об отправленном объекте в БД
		*/

		$clone = clone($obj);
		$clone->clearObj();
		if(isset($clone->errors)) $clone->errors = array();

		if(!is_object($this->Core->DB)){
			$t = (rand(1, 100000) + rand(1, 30000)) * rand(1, 100);
			$this->Core->sessMerge(array('forms' => array($t => $clone)));
			return $t;
		}

		$data = array('vars' => Library::cmpSerialize($clone), 'date' => time());
		return $this->Core->DB->Ins(array('forms', $data));
	}

	public final function __ava__setError($var, $error){
		if(empty($this->errorMessages[$var])) $this->errorMessages[$var] = '';
		$this->errorMessages[$var] .= $error.'<br/>';
		$this->setMessage($var, $error, 'error');
	}

	public final function __ava__setErrors($errors){
		foreach($this->errors as $i => $e) $this->setError($i, $e);
	}

	public final function __ava__setMessage($var, $msg, $type = 'success'){
		if($type == 'error') $msg = '<span class="error">'.$msg.'</span>';
		else $msg = '<span>'.$msg.'</span>';
		if(!isset($this->messages[$var])) $this->messages[$var] = '';
		$this->messages[$var] .= $msg.'<br/><br/>';
	}

	public final function __ava__rmMessage($var){
		/*
			Удаляет сообщение
		*/

		unset($this->messages[$var]);
	}

	public final function __ava__clearMessages(){
		/*
			Удаляет сообщение
		*/

		$this->messages = array();
	}

	public final function check2(){
		/*
			Проверка на наличие ошибок в логе, если есть - вернет false, иначе true
		*/

		if(!empty($this->values['ava_form_transaction_id'])) return $this->check();
		elseif($this->errorMessages){
			$this->setContent($this->getErrorsList());
			return false;
		}

		return true;
	}

	public final function check(&$form = false, $insertForm = true){
		/*
			Проверяет форму на наличие ошибок.
		*/

		$__runnedFunc = 'check';
		require(_W.'core/includes/plugin_callpre.php');
		if($__return != 'noAnyPlugins') return $__return;

		if(!is_object($form) && empty($this->values['ava_form_transaction_id'])){
			throw new AVA_Access_Exception('{Call:Lang:core:core:nenajdenydan}');
		}
		elseif(!is_object($form)){
			$form = $this->loadForm($this->values['ava_form_transaction_id']);
			$form->values = $this->values;
			$form->setVar('parent', $this);
		}

		$form->check();
		$actionUrl = parse_url($form->getParam('action'));
		$actionVars = Library::parseStr($actionUrl['query']);
		$mmObj = $this->Core->getMainModObj();

		if(($actionVars['mod'] != $mmObj->getMod() && $actionVars['mod'] != $this->mod) || ($actionVars['func'] != $mmObj->getFunc() && $actionVars['func'] != $this->func)){
			throw new AVA_Access_Exception('{Call:Lang:core:core:popytkadostu:'.Library::serialize(array($actionVars['mod'], $actionVars['func'], $mmObj->getMod(), $this->mod, $mmObj->getFunc(), $this->func)).'}');
		}

		if(empty($this->errorMessages) && empty($form->errors) && !$this->Core->getFlag('filesUpload') && $this->Core->getGPCArr('f')){
			foreach($this->Core->getGPCArr('f') as $i => $e){
				if(!isset($form->matrix[$i])) throw new AVA_Access_Exception('{Call:Lang:core:core:popytkazagru}');
				elseif(!empty($e['name']) && !empty($form->matrix[$i]['additional']['dstFolder'])){
					$this->values[$i] = $this->Core->moveFileToFolder(
						TMP.$this->values[$i],
						$form->matrix[$i]['additional']['dstFolder'],
						$i,
						$this,
						isset($form->matrix[$i]['additional']['newName']) ? $form->matrix[$i]['additional']['newName'] : false
					);
				}
			}

			$this->Core->setFlag('filesUpload');
		}

		$form->errors = Library::array_merge($form->errors, $this->errorMessages);
		$form->values = $this->values;

		if($form->errors){
			if($insertForm) $this->setContent($this->getFormText($form, array(), array(), $form->getTemplName()));
			require(_W.'core/includes/plugin_callpost.php');
			return false;
		}

		require(_W.'core/includes/plugin_callpost.php');
		return true;
	}

	private function loadForm($id){
		if(empty($this->objects[$id])){
			if(is_object($this->Core->DB)){
				$this->objects[$id] = Library::cmpUnserialize($this->Core->DB->cellFetch(array('forms', 'vars', db_main::q("`id`=#0", array($id)))));
			}
			else{
				$sess = $this->Core->getSessArray();
				$this->objects[$id] = $sess['forms'][$id];
			}

			if(!is_object($this->objects[$id])) throw new AVA_Access_Exception('{Call:Lang:core:core:dannyjzapros}');
		}

		return $this->objects[$id];
	}



	/******************************************************************************************************************************************************************

																	Работа со списками

	******************************************************************************************************************************************************************/

	protected final function __ava__newList($name, $extraParams = array(), $params = array(), $templFile = false, $forceDefTmplRead = true){
		/*
			Создает новый объект списка
		*/

		if(isset($this->objects[$name])) throw new AVA_Exception('{Call:Lang:core:core:obektsimenem:'.Library::serialize(array($name)).'}');
		if(SHOW_HWT > 0) $this->debId = $this->Core->debugStart();
		if(!$templFile) $templFile = $this->Core->getModuleTemplatePath($this->mod).'list.tmpl';

		if(!isset($params['sortAction'])){
			if(!empty($extraParams['table'])) $tbl = $extraParams['table'];
			elseif(isset($extraParams['req']) && is_array($extraParams['req'])) $tbl = empty($extraParams['req']['0']) ? $extraParams['req']['table'] : $extraParams['req']['0'];
			if(!empty($tbl)) $params['sortAction'] = $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc='.library::encodeUrl($this->func.'&'.Library::array2url($this->Core->getGPCArr('g'))).'&table='.$tbl;
		}

		//Создаем объект списка
		$list = new Lister($name, $params, $templFile, $this, $forceDefTmplRead);
		$list->setDB(isset($extraParams['DB']) ? $extraParams['DB'] : $this->DB);
		if(Library::constVal('IN_ADMIN') && empty($params['useEval'])) $list->setObjFlag('notEval');

		if(!empty($extraParams)){
			$page = $this->Core->getGPCVar('g', 'page');
			$step = $this->Core->sessGet('settingEntryOnPage') ? $this->Core->sessGet('settingEntryOnPage') : $this->Core->getParam('listEntry');
			$url = array('query' => $this->Core->getGPCArr('g'), 'base' => $this->path);

			$entryTemplate = $name;
			$active = 0;
			$countReq = $action = '';

			$type = 'select';
			$method = 'post';
			$templates = $actions = $extraReqs = $multilevelReq = $heads = $matrix = $values = $hiddens = $form_actions = $searchForm = $unserialize = array();

			if(!empty($extraParams['action']) && !regExp::Match("|^(\w+)://|iUs", $extraParams['action'], true)){
				$extraParams['action'] = regExp::Match("/(\&|\?)mod=/", $extraParams['action'], true) ?
					$this->path.'?func='.$extraParams['action'] : $this->path.'?mod='.$this->mod.'&func='.$extraParams['action'];
			}

			extract($extraParams);

			$list->setDefaults($step, $active, $page);
			$list->setUrl($url);
			$list->setTemplates($templates);
			$list->setEntryTemplate($entryTemplate);

			if(!empty($searchForm)){
				if(!empty($this->values['in_search']) && !empty($this->values['ava_form_transaction_id']) && !$this->check($form, false)){
					$list->setParam('search', $this->getFormText($form, array(), array(), $form->getTemplName()));
					$list->setParam('errorInSearch', 1);
				}
				else{
					$searchMatrix = $searchMatrixSync = $searchValues = $searchHiddens = $searchParams = $searchFields = $notSearchFields = $orderFields = $searchPrefix = $isBe = $searchExpr = $searchAlias = $orderExpr = array();
					extract($searchForm);

					if(empty($searchParams['action'])) $searchParams['action'] = $this->path.'?mod='.$this->mod.'&func='.$this->func;
					if(empty($searchParams['method'])) $searchParams['method'] = 'post';
					if(empty($searchParams['id_prefix'])) $searchParams['id_prefix'] = 'search_';

					$searchHiddens['in_search'] = 1;
					if($searchFields) $searchMatrix = Library::array_merge($this->getListSearchMatrix($searchFields, $orderFields, $searchMatrix), $searchMatrix);
					if($searchMatrixSync) $searchMatrix = Library::syncArraySeq($searchMatrix, $searchMatrixSync);
					if(!empty($this->values['in_search'])) $searchValues = $this->values;

					$list->setParam(
						'search',
						$this->getFormText(
							$this->addFormBlock(
								$this->newForm(
									'search_'.$name,
									$searchParams['action'],
									$searchParams
								),
								$searchMatrix
							),
							$searchValues,
							$searchHiddens,
							'search'
						)
					);

					if(isset($req)){
						if(!empty($this->values['in_search'])){
							$extraFilter = array();

							if($extraReqs){
								foreach($extraReqs as $i => $e){
									if(!empty($e['search'])){
										if(is_array($e['req'])){
											$extraFilter[] = '('.$this->getEntriesWhere(
												$e['DB']->columnFetch(
													array(
														isset($e['req'][0]) ? $e['req'][0] : $e['req']['table'],
														$e['unitedFld2'],
														$e['unitedFld2'],
														$this->getListSearchWhere(
															Library::deconcatPrefixArrayKey($searchValues, $e['prefix']),
															$e['search'],
															array(),
															$isBe,
															Library::deconcatPrefixArrayKey($searchMatrix, $e['prefix'])
														)
													)
												),
												$e['unitedFld1'],
												is_array($searchPrefix) ? (isset($searchPrefix[$e['unitedFld1']]) ? $searchPrefix[$e['unitedFld1']].'.' : '') : ($searchPrefix ? $searchPrefix.'.' : '')
											).')';

											foreach($e['search'] as $i1 => $e1) unset($searchFields[$e['prefix'].$i1]);
										}
									}
								}
							}

							foreach($notSearchFields as $i => $e) unset($searchValues[$e]);
							$where = $this->getListSearchWhere($searchValues, $searchFields, $searchPrefix, $isBe, $searchMatrix, $searchExpr, $searchAlias);
							$order = empty($this->values['search_sort']) ? '' : $this->getListSearchOrder($this->values['search_sort'], $this->values['search_direction'], $searchPrefix, $orderExpr, $searchAlias);
							if($extraFilter) $where = $where ? $where.' AND '.implode(' AND ', $extraFilter) : implode(' AND ', $extraFilter);

							if(is_array($req)){
								if($where){
									if(!empty($req['where'])) $req['where'] .= " AND ($where)";
									elseif(!empty($req['2'])) $req['2'] .= " AND ($where)";
									else $req['2'] = $where;
								}

								if($order){
									if(!empty($req['table'])) $req['order'] = $order.(!empty($req['order']) ? ', '.$req['order'] : '');
									else $req['3'] = $order.(!empty($req['3']) ? ', '.$req['3'] : '');
								}
							}
							else{
								regExp::Match("/\s(where|order)\s(.+)$/iUs", $req, true, true, $m);
								if(!$m) $m = array('');

								$parts = regExp::Split("/order(\s*)by/i", trim($m[0]), true);
								$parts[0] = trim(regExp::Replace("/where/i", '', $parts[0], true));
								$parts[1] = isset($parts[1]) ? trim($parts[1]) : '';

								if($where) $parts[0] = $parts[0] ? $parts[0]." AND ($where)" : $where;
								if($order) $parts[1] = $order.($parts[1] ? ', '.$parts[1] : '');
								if($m[0]) $req = regExp::replace($m[0], '', $req);

								$req = $req.($parts[0] ? ' WHERE '.$parts[0] : '').($parts[1] ? ' ORDER BY '.$parts[1] : '');
							}
						}
					}
				}
			}

			if(!empty($quickSearchParams)){
				$list->setQuickSearchLinks($quickSearchParams);
			}

			if(isset($req)){
				if(!empty($multilevelReq)){
					$list->setMultilevelReq($req, $multilevelReq['parent'], $multilevelReq['id']);
				}
				else{
					$list->setDBReq($req, $countReq, $extraReqs, $unserialize);
				}
			}
			elseif(isset($arr)){
				if(!empty($multilevelReq)){
					$list->setMultilevelReqArray($arr, $multilevelReq['parent'], $multilevelReq['id']);
				}
				else{
					$list->setArrayReq($arr);
				}
			}
			else throw new AVA_Exception('{Call:Lang:core:core:neustanovlen3}');

			$list->setEntriesParams($actions, $heads);
			if(!empty($action)) $list->setEntriesFormParams($action, $form_actions, $matrix, $values, $hiddens, $type,  $method);
			$list->setPaginateParams($this->Core->getParam('inBlock'));
		}

		$this->curFormObj = $this->objects[$name] = $list;
		return $list;
	}

	public function __ava__getListSearchMatrix($fields, $order, $searchMatrix = array()){
		/*
			Создает типовую матрицу поиска по списку
		*/

		$matrix = array();
		$values = array();

		if($order){
			$matrix = array(
				'search_sort' => array(
					'type' => 'checkbox_array',
					'text' => '{Call:Lang:core:core:sortirovatpo}',
					'template' => 'listsorter',
					'additional' => $order
				),
				'search_direction' => array(
					'type' => 'radio',
					'template' => 'listsorter',
					'additional' => array(
						'' => '<img src="'.$this->Core->getTemplateUrl().'images/sort_up.gif" />',
						'desc' => '<img src="'.$this->Core->getTemplateUrl().'images/sort_dn.gif" />'
					)
				)
			);
		}

		foreach($fields as $i => $e){
			if(!is_array($e)) $matrix[$i]['text'] = $e;

			if(!empty($searchMatrix[$i]['type'])){
				if($searchMatrix[$i]['type'] == 'calendar'){
					$field = $i;
					$field_to = $i.'_to';
					$value_to = empty($this->values[$field_to]) ? '' : $this->values[$field_to];
					$text = $e;
					include(_W.'forms/type_calendar.php');

					continue;
				}
				elseif($searchMatrix[$i]['type'] == 'gap'){
					$field = $i;
					$field_to = $i.'_to';
					$value_to = empty($this->values[$field_to]) ? '' : $this->values[$field_to];
					$text = $e;
					include(_W.'forms/type_gap.php');

					continue;
				}
			}

			switch($i){
				case 'show':
					$matrix[$i]['type'] = 'radio';
					$matrix[$i]['template'] = 'line';
					if(empty($searchMatrix['show']['additional'])){
						$matrix[$i]['additional'] = array(
							'' => '{Call:Lang:core:core:vse}',
							'1' => '{Call:Lang:core:core:otkrytye}',
							'0' => '{Call:Lang:core:core:zakrytye}'
						);
					}

					break;

				case 'date':
					$field = 'date';
					$field_to = 'date_to';
					$value_to = empty($this->values[$field_to]) ? '' : $this->values[$field_to];
					$text = $e;
					include(_W.'forms/type_calendar.php');

					break;

				default:
					if(is_array($e)){
						$matrix[$i]['type'] = 'radio';
						$matrix[$i]['text'] = '&#160;';
						$matrix[$i]['template'] = 'line';
						$matrix[$i]['additional'] = $e;
					}
					else $matrix[$i]['type'] = 'text';
			}
		}

		$this->values = Library::array_merge($values, $this->values);
		return $matrix;
	}

	protected function __ava__getListSearchWhere($params, $fields, $prefix = array(), $isBe = array(), $matrix = array(), $expressions = array(), $alias = array()){
		/*
			Создает типовой where-запрос по параметрам
		*/

		$return = array();
		if($prefix && !is_array($prefix)){
			$p2 = $prefix;
			$prefix = array();
			foreach($fields as $i => $e) $prefix[$i] = $p2;
		}

		foreach($fields as $i => $e){
			if(isset($expressions[$i]) && isset($params[$i]) && $params[$i] !== ''){
				if(is_array($expressions[$i]) && isset($expressions[$i][$params[$i]])){
					$return[] = $expressions[$i][$params[$i]];
					continue;
				}
				elseif(!is_array($expressions[$i])){
					$return[] = $expressions[$i];
					continue;
				}
			}

			$p = empty($prefix[$i]) ? '' : $prefix[$i].'.';
			$fld = db_main::Quot(!empty($alias[$i]) ? $alias[$i] : $i);
			$params[$i] = isset($params[$i]) ? db_main::Quot($params[$i]) : '';

			switch(true){
				case ($i == 'date' || $matrix[$i]['type'] == 'calendar'):
					if(!empty($params[$i])) $return[] = "{$p}`$fld`>='".db_main::Quot($params[$i])."'";
					if(!empty($params[$i.'_to'])) $return[] = "{$p}`$fld`<='".db_main::Quot($params[$i.'_to'])."'";
					break;

				case ($matrix[$i]['type'] == 'gap'):
					if($params[$i]) $return[] = "{$p}`$fld`>='{$params[$i]}'";
					if($params[$i.'_to']) $return[] = "{$p}`$fld`<='".db_main::Quot($params[$i.'_to'])."'";
					break;

				case ($matrix[$i]['type'] == 'checkbox'):
					if(!empty($params[$i])) $return[] = "{$p}`$i`";
					break;

				case ($matrix[$i]['type'] == 'checkbox_array'):
					if(!empty($params[$i])){
						$return2 = array();
						foreach($params[$i] as $i1 => $e1) $return2[] = "{$p}`$fld`='{$i1}'";
						$return[] = '('.implode(' OR ', $return2).')';
					}
					break;

				case ($matrix[$i]['type'] == 'radio'):
				case ($matrix[$i]['type'] == 'select'):
				case ($i == 'show'):
					if(isset($params[$i]) && $params[$i] === '~nobody'){
						$return[] = "{$p}`$i`=''";
						break;
					}

				default:
					if(isset($params[$i]) && $params[$i] !== ''){
						if(empty($isBe[$i])) $return[] = "({$p}`$fld` REGEXP ('{$params[$i]}') OR {$p}`$fld`='{$params[$i]}')";
						else $return[] = "{$p}`$fld`='{$params[$i]}'";
					}
			}
		}

		return implode(' AND ', $return);
	}

	protected function getListSearchOrder($orders, $desc, $prefix = array(), $orderExpr = array(), $alias = array()){
		/*
			Создает типовой order-запрос
		*/

		$return = array();
		if($prefix && !is_array($prefix)){
			$p2 = $prefix;
			$prefix = array();
			foreach($orders as $i => $e) $prefix[$i] = $p2;
		}

		foreach($orders as $i => $e){
			$i = db_main::Quot(!empty($alias[$i]) ? $alias[$i] : $i);
			if(!empty($orderExpr[$i])) $i = $orderExpr[$i];
			elseif(!empty($prefix[$i])) $i = $prefix[$i].'.'.$i;
			else $i = '`'.$i.'`';
			$return[] = $i.($desc == 'desc' ? ' DESC' : '');
		}

		return implode(', ', $return);
	}

	protected final function __ava__getListText($list = false, $templName = ''){
		/*
			Генерирует список
		*/

		if(!is_object($list)){
			$list = $this->curFormObj;
		}

		$list->setTemplName($templName);
		$templText = $this->getObjText($list);
		if(empty($templText)) return '';
		$this->setContent($templText, $list->getName());

		if(!$this->Core->getFlag('listJS')){
			$this->setContent('<script type="text/javascript" src="'._D.'js/list.js"></script>', 'head');
			$this->Core->setFlag('listJS');
		}

		if(!$this->Core->getFlag('formJS')){
			$this->setContent('<script type="text/javascript" src="'._D.'js/form.js"></script>', 'head');
			$this->Core->setFlag('formJS');
		}

		if(SHOW_HWT > 0) $GLOBALS['Core']->setDebugAction('{Call:Lang:core:core:vremiasozdan:'.Library::serialize(array($name)).'}');
		return $templText;
	}


	/******************************************************************************************************************************************************************

																	Типовой квартет функций

	******************************************************************************************************************************************************************/

	protected final function __ava__typicalMain($params){
		/*
			Создает типовую основную страиницу блока настроек. Как правило состоит из главной формы и списка настроек
			Устанавливаются в this->values
				table - таблица выбора
				form - форма содержащая записи
				req - запрос для выборки списка
				caption - заголовок для формы
				list_caption - заголовок для списка
				fields - поля которые будут участвовать при внесении записей в БД

				action - что делаем
		*/

		$type_action = empty($this->values['type_action']) ? 'main' : $this->values['type_action'];
		$name = empty($params['name']) ? $this->func : $params['name'];
		$listName = $name.'_list';
		$func = $this->func;

		$table = $form = $name;
		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$caption = $this->funcName;

		//Для формы
		$formData = array();
		$formParams = array();
		$formExclude = array();
		$modifyData = array();

		$formValues = array();
		$formHiddens = array();
		$formTemplName = '';

		//Для списка
		$listParams = array();
		$listParams2 = array();
		$listTmplFile = false;
		$listTemplName = '';

		extract($params);
		if(!isset($this->funcName)) $this->funcName = $caption;
		if(!isset($formParams['caption'])) $formParams['caption'] = $caption;

		if(empty($listParams['req']) && empty($listParams['arr'])){
			$filter = array();
			if($this->DB->issetField($table, 'sort')) $filter[] = '`sort`';
			if($this->DB->issetField($table, 'date')) $filter[] = '`date` DESC';
			$listParams['req'] = array($table, '*', '', implode(', ', $filter));
		}

		if(!isset($listParams['action'])) $listParams['action'] = $this->path.'?mod='.$this->mod.'&func='.$func.'&form='.$form.'&type_action=delete';
		if(!isset($listParams2['caption'])) $listParams2['caption'] = '{Call:Lang:core:core:spisok}';

		if(!isset($listParams['form_actions'])){
			if($this->DB->issetField($table, 'show')){
				$listParams['form_actions']['suspend'] = '{Call:Lang:core:core:zakryt}';
				$listParams['form_actions']['unsuspend'] = '{Call:Lang:core:core:otkryt}';
			}
			$listParams['form_actions']['delete'] = '{Call:Lang:core:core:udalit}';
		}

		switch($type_action){

			case 'main':
				/*
				  	Основная страница - форма добавления объекта и список
				  */

				$this->setContent(
					$this->getFormText(
						$this->addFormBlock(
							$this->newForm(
								$name,
								$func.'&form='.$form.'&type_action=new',
								$formParams
							),
							$form,
							$formData,
							$formExclude
						),
						$formValues,
						$formHiddens,
						$formTemplName
					)
				);

				//Список
				$this->setContent(
					$this->getListText(
						$this->newList(
							$listName,
							$listParams,
							$listParams2,
							$listTmplFile
						),
						$listTemplName
					)
				);

				return true;

			case 'new':
				/*
					Создает новый объект либо меняет существующий
				  */

				if(!empty($isUniq)) $this->isUniq($table, $isUniq, isset($this->values['modify']) ? $this->values['modify'] : false, isset($isUniqWhere) ? $isUniqWhere : '', isset($isUniqValues) ? $isUniqValues : false);
				return $this->typeIns($table, !empty($fields) ? $fields : $form, $func);

			case 'modify':
				/*
					Создает форму изменения объекта
				  */

				$modifyData['formData'] = $formData;
				$modifyData['formData']['modify'] = $this->values['id'];

				if(!isset($modifyReq)) $modifyReq = array($table, '*', "`id`='".db_main::Quot($this->values['id'])."'");
				if(!isset($modifyData['caption'])) $modifyData['caption'] = '{Call:Lang:core:core:izmenit}';

				$this->pathFunc = $this->func;
				return $this->typeModify($modifyReq, $form, $func.'&type_action=new', $modifyData);

			case 'delete':
				/*
					Удаляет набор за писей
				  */

				if(empty($actionFields)) $actionFields = array();
				if(empty($actionWhere)) $actionWhere = '';
				if(empty($actionId)) $actionId = 'id';

				return $this->typeActions($table, $func, $actionFields, $actionWhere, $actionId);
		}
	}

	public final function __ava__getFields($form, $exclude = array(), $toKeys = true, $__values = false, $formData = array()){
		//Выдеат список полей для внесения в таблицу на основании формы

		$return = array();
		$form = $this->getFormFile($form);
		if($__values === false) $__values = $this->values;

		extract($formData);
		require($form);

		foreach($matrix as $i => $e){
			if(!$e['type'] || ($e['type'] == 'caption') || in_array($i, $exclude) || ($e['type'] == 'file' && empty($__values[$i]))) continue;
			elseif($e['type'] == 'gap' || $e['type'] == 'calendar'){
				$return[$i] = $__values[$i];
				$return[$i.'_to'] = $__values[$i.'_to'];
			}
			else $return[$i] = empty($__values[$i]) ? '' : (((is_array($__values[$i]) && $toKeys) ? Library::arrKeys2str($__values[$i]) : $__values[$i]));
		}

		return $return;
	}

	public final function __ava__getEntriesWhere($entry = false, $field = 'id', $prefix = '', $eqSign = '=', $logic = 'OR'){
		/*
			Создает строку WHERE для SQL на основании существующих entry
		*/

		if($entry === false) $entry = isset($this->values['entry']) ? $this->values['entry'] : array();
		$where = array();
		foreach($entry as $i => $e) if($e) $where[] = "$prefix`".db_main::Quot($field)."`".$eqSign."'".db_main::Quot($i)."'";
		return '('.($where ? implode(' '.$logic.' ', $where) : '1'.$eqSign.'2').')';
	}

	public final function __ava__getEntriesWhereByValue($entries, $field = 'id', $prefix = '', $eqSign = '=', $logic = 'OR'){
		/*
			Создает строку WHERE для SQL на основании существующих entry
		*/

		if(!is_array($entries)) $entries = array($entries);
		$where = array();
		foreach($entries as $e) $where[] = "$prefix`".db_main::Quot($field)."`".$eqSign."'{$e}'";
		return '('.($where ? implode(' '.$logic.' ', $where) : '1'.$eqSign.'2').')';
	}

	protected final function __ava__typeIns($table, $fields, $back = false, $check = true){
		/*
			Типовая функция вставки данных
		*/

		if($check && !$this->check()){
			return false;
		}

		if(!empty($this->values['modify'])){
			$reqType = 'Upd';
			$where = db_main::q("`id`=#0", array($this->values['modify']));
		}
		else{
			$reqType = 'Ins';
			$where = "";
		}

		if(!is_array($fields)) $fields = $this->getFields($fields);
		$return = $this->DB->$reqType(array($table, $fields, $where));

		if($back){
			$this->refresh($back);
			$fld = !empty($fields['text']) ?
				$fields['text'] :
				(!empty($fields['name']) ?
					$fields['name'] :
					(empty($this->values['modify']) ? $return : $this->values['modify']));

			if(empty($this->values['modify'])) $this->setAdminStat('new', '{Call:Lang:core:core:dobavlenazap:'.Library::serialize(array($fld)).'}', $table, $return);
			else $this->setAdminStat('new', '{Call:Lang:core:core:izmenenazapi:'.Library::serialize(array($fld)).'}', $table, $this->values['modify']);
		}

		return $return;
	}

	protected final function __ava__typeModify($req, $forms, $action, $data){
		/*
			Типовая функция формы модификации
		*/

		if($req) $values = $this->DB->rowFetch($req);
		$params = array();
		$hiddens = array('modify' => $this->values['id']);

		$formData = array();
		$tmplName = 'big';
		$tmplFile = '';

		extract($data);

		if(empty($formData['modify'])) $formData['modify'] = $this->values['id'];
		if(!empty($data['caption'])) $params['caption'] = $this->Core->simpleReplace($data['caption'], $values);
		else $params['caption'] = '{Call:Lang:core:core:izmenenienas}';

		if(!empty($extract)){
			foreach($extract as $e){
				if(regExp::Match("|^,.*,$|", $values[$e], true)) $values[$e] = library::str2arrKeys($values[$e]);
				else{
					$values[$e] = library::unserialize($values[$e]);
					$values = library::array_merge($values, $values[$e]);
				}
			}
		}

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						$this->func,
						$action,
						$params,
						$tmplFile
					),
					$forms,
					$formData
				),
				$values,
				$hiddens,
				$tmplName
			)
		);

		if(!empty($this->pathFunc) && empty($this->funcName)) $this->funcName = $params['caption'];
		return $values;
	}

	protected final function __ava__typeActions($table, $back, $fields = array(), $where = '', $id = 'id', $entry = false){
		/*
			Внесение изменений в соответствии со списком
		*/

		if($entry === false) $entry = isset($this->values['entry']) ? $this->values['entry'] : array();

		if(empty($this->values['entry'])){
			$this->back($back, '', '', '{Call:Lang:core:core:neotmechenni}');
			return false;
		}

		$where = '('.$this->getEntriesWhere($entry, $id).')'.$where;
		$fld = $this->DB->issetField($table, 'text') ? 'text' : ($this->DB->issetField($table, 'name') ? 'name' : $id);
		$fldList = implode(', ', array_unique($this->DB->columnFetch(array($table, $fld, '', $where))));

		switch($this->values['action']){
			case 'delete':
				$reqFunc = 'Del';
				$req = array($table, $where);
				$this->setAdminStat('delete', '{Call:Lang:core:core:udaleniezapi:'.Library::serialize(array($fldList)).'}', $table);
				break;

			case 'suspend':
				$reqFunc = 'Upd';
				$req = array($table, array('show' => '0'), $where);
				$this->setAdminStat('modify', '{Call:Lang:core:core:zablokirovan:'.Library::serialize(array($fldList)).'}', $table);
				break;

			case 'unsuspend':
				$reqFunc = 'Upd';
				$req = array($table, array('show' => '1'), $where);
				$this->setAdminStat('modify', '{Call:Lang:core:core:razblokirova:'.Library::serialize(array($fldList)).'}', $table);
				break;

			default:
				$reqFunc = 'Upd';
				$req = array($table, $fields[$this->values['action']], $where);
		}

		$this->DB->$reqFunc($req);
		if($back) $this->refresh($back);
		return true;
	}

	protected final function __ava__actionPreForm($entryList, $func, $caption = ''){
		/*
			Возвращает объект предустановленной перед action формой
		*/

		if(!$caption){
			switch($this->values['action']){
				case 'delete': $caption = '{Call:Lang:core:core:udalitzapisi}'; break;
				case 'suspend': $caption = '{Call:Lang:core:core:zablokirovat}'; break;
				case 'unsuspend': $caption = '{Call:Lang:core:core:razblokirova1}'; break;
			}
		}

		$form = $this->newForm('actionPreForm', $func, array('caption' => $caption));
		$this->addFormBlock(
			$form,
			array(
				'entry' => array(
					'type' => 'checkbox_array',
					'value' => $this->values['entry'],
					'warn' => '{Call:Lang:core:core:neotmechenon}',
					'additional' => $entryList
				),
				'action' => array('type' => 'hidden', 'value' => $this->values['action']),
				'confirm' => array('type' => 'hidden', 'value' => 1)
			)
		);

		return $form;
	}

	protected final function __ava__fieldValues($fields, $params = false){
		$return = array();
		if($params === false) $params = $this->values;
		if(!is_array($fields)) throw new AVA_Exception('{Call:Lang:core:core:strokapereda}');

		foreach($fields as $e){
			if(isset($params[$e])) $return[$e] = $params[$e];
			else $return[$e] = '';
		}
		return $return;
	}

	protected function __ava__isUniq($table, $fields, $id = false, $additWhere = '', $values = false){
		$whId = '';
		if($id !== false) $whId = "`id`!=#0 AND ";
		if($values === false) $values = $this->values;

		foreach($fields as $i => $e){
			if($this->DB->cellFetch(array($table, $i, db_main::q("{$whId} `$i`=#1 {$additWhere}", array($id, $values[$i]))))){
				$this->setError($i, $e);
			}
		}
	}

	protected function __ava__isUsers($tbl, $userId = false, $userFld = 'user_id', $where = false){
		/*
			Проверяет что указанная запись принадлежит данному юзеру
		*/

		if($userId === false) $userId = $this->Core->User->getUserId();
		if($where === false) $where = $this->getEntriesWhere();
		return !$this->DB->Count(array($tbl, "`{$userFld}`!='{$userId}' AND (".$where.")"));
	}

	protected function __ava__checkUserAuth($tbl, $where = false, $userFld = 'user_id', $msg = 'Вы пытаетесь получить доступ к записи, к которой у вас нет прав доступа.', $userId = false){
		/*
			Проверяет полномочия юзера. Если их нет - выдает исключение
		*/

		if(!$userId = $this->Core->User->getUserId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');
		if(!$this->isUsers($tbl, $userId, $userFld, $where)) throw new AVA_Access_Exception($msg);
		return true;
	}

	protected function func_main(){
		/*
			Главная страница.
			Если пользователь установил какая страница для него главная - отображается она
			Либо устанавливается шаблон главной страницы (как шаблон главной помечается в БД)
			Либо устанавливается шаблон по умолчанию
		 */

		if(!empty($this->Core->User->params['main_page'])){
			$this->redirect2($this->Core->User->params['main_page']);
			return true;
		}
	}

	protected function func_sortListParams(){
		/*
			Выполняет сортировку списка по полю sort таблицы
		*/

		foreach($this->values['sort_entry'] as $i => $e){
			$this->DB->Upd(array($this->values['table'], array('sort' => $e), "`id`='".db_main::Quot($i)."'"));
		}

		$this->refresh(library::decodeUrl($this->values['backFunc']));
	}


	/******************************************************************************************************************************************************************

																	Типовое добавление полей в форму

	******************************************************************************************************************************************************************/

	protected final function __ava__formFields($table, $params = array(), $extraTblParams = array()){
		/*
			Управление добавлением полей в динамическую форму.
		*/

		$func = empty($params['func']) ? $this->func : $params['func'];

		if(!empty($this->values['field_action'])){
			switch($this->values['field_action']){
				case 'actions': $return = $this->formFieldsActions($table, $params, $extraTblParams); break;
				case 'add':
					if(!empty($extraTblParams['table'])){
						$DB = !empty($extraTblParams['DB']) ? $extraTblParams['DB'] : $this->DB;
						if($DB->issetField($extraTblParams['table'], $this->values['name'])) $this->setError('name', "Это имя зарезервировано");
					}

				case 'modify2':
					if(isset($params['disabled_fields']) && in_array($this->values['name'], $params['disabled_fields'])){
						$this->setError('name', "Это имя зарезервировано");
					}

					$this->isUniq($table, array('name' => '{Call:Lang:core:core:takoeimiapol}'), empty($this->values['modify']) ? false : $this->values['modify'], empty($params['filter']) ? '' : $params['filter']);
					if(!$this->check()) return false;

					if($extraTblParams && !isset($extraTblParams['fieldType']) && !empty($this->values['insert_field'])){
						if($this->values['insert_field_type'] == 'manual') $this->values['insert_field_type'] = $this->values['insert_field_type_manual'];
						$extraTblParams['fieldType'] = $this->values['insert_field_type'];
					}

					$return = $this->formFieldsAdd(
						$table,
						empty($params['insert']) ? $this->values : Library::array_merge($this->values, $params['insert']),
						empty($params['extraFields']) ? array() : $params['extraFields'],
						empty($this->values['modify']) ? 0 : $this->values['modify'],
						$extraTblParams
					);

					if($this->values['field_action'] == 'add') $this->redirect($func.'&field_action=modify&id='.$return);
					break;

				case 'modify':
					return $this->formFieldsModify($table, $this->values['id'], $params, $extraTblParams);
			}

			if($return !== false) $this->refresh($func);
			else $this->back($func);
			return $return;
		}

		$matrixParams = array('caption' => '{Call:Lang:core:core:novoepolefor}');
		$extraValues = $matrixExtra = $matrixHiddens = $matrixData = $matrixExclude = $listParams = $listData = array();
		$formTemplate = '';

		$formName = $func;
		$req = array($table, library::array_merge_numeric(array('id', 'name', 'text', 'type', 'show', 'sort'), isset($params['extraFields']) ? $params['extraFields'] : array()), '', "`sort`");
		$listActions = array('text' => $func.'&field_action=modify');
		$listFormActions = array('delete' => '{Call:Lang:core:core:udalit}', 'suspend' => '{Call:Lang:core:core:ubratizformy}', 'unsuspend' => '{Call:Lang:core:core:sdelatotobra}');

		$listTmpl = '';
		$listFileTmpl = $this->Core->getModuleTemplatePath('core').'list.tmpl';
		$listEntryTmpl = 'fields_list';

		extract($params);
		if(!empty($extraTblParams) && !isset($matrixData['canAddNewField'])) $matrixData['canAddNewField'] = $extraTblParams['table'];

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						$formName,
						$func.'&field_action=add',
						$matrixParams
					),
					$matrixExtra ? array('fields', $matrixExtra) : 'fields',
					$matrixData,
					$matrixExclude
				),
				$extraValues,
				$matrixHiddens,
				$formTemplate
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					$listEntryTmpl,
					Library::array_merge(
						array(
							'req' => $req,
							'form_actions' => $listFormActions,
							'actions' => $listActions,
							'action' => $func.'&field_action=actions',
							'searchForm' => array(
								'searchFields' => array(
									'name' => '{Call:Lang:core:core:imiapolia}',
									'text' => '{Call:Lang:core:core:tekstkpoliu}',
									'type' => '{Call:Lang:core:core:tip}'
								),
								'orderFields' => array(
									'name' => '{Call:Lang:core:core:imeni}',
									'text' => '{Call:Lang:core:core:tekstu}',
								),
								'searchMatrix' => array(
									'type' => array(
										'type' => 'select',
										'additional' => array(
											'' => '{Call:Lang:core:core:vse}',
											'text' => '{Call:Lang:core:core:tekstovoepol}',
											'textarea' => '{Call:Lang:core:core:tekstovaiaob}',
											'checkbox' => '{Call:Lang:core:core:galochka}',
											'checkbox_array' => '{Call:Lang:core:core:spisokiznesk}',
											'select' => '{Call:Lang:core:core:vypadaiushch}',
											'multiselect' => '{Call:Lang:core:core:vypadaiushch1}',
											'radio' => '{Call:Lang:core:core:radioknopka}',
											'password' => '{Call:Lang:core:core:polevvodapar}',
											'file' => '{Call:Lang:core:core:zagruzkafajl1}',
											'hidden' => '{Call:Lang:core:core:skrytoepole}'
										)
									)
								)
							)
						),
						$listData
					),
					Library::array_merge(
						array(
							'caption' => '{Call:Lang:core:core:vsepoliaform}',
							'sortAction' => $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc='.library::encodeUrl($func).'&table='.$table
						),
						$listParams
					),
					$listFileTmpl
				),
				$listTmpl
			)
		);
	}

	protected function __ava__formFieldsAdd($table, $insert = array(), $extraFields = array(), $id = 0, $extraTblParams = array()){
		/*
			Добавляет поле формы в специфицированную таблицу
		*/

		$fields = $this->fieldValues(library::array_merge_numeric(array('name', 'text', 'type', 'show', 'sort'), $extraFields), $insert);
		$fields['vars']['matrix'] = $this->fieldValues(array('comment', 'warn', 'warn_function', 'warn_pattern', 'warn_pattern_text', 'additional_text', 'additional_style', 'disabled', 'value'), $insert);
		$fields['vars']['eval'] = empty($insert['eval']) ? '' : $insert['eval'];

		$fields['vars']['extra'] = $insert;
		unset($fields['vars']['extra']['eval'], $fields['vars']['extra']['other_params'], $fields['vars']['extra']['ava_form_transaction_id'], $fields['vars']['extra']['AVAUrl'], $fields['vars']['extra']['backLink'], $fields['vars']['extra']['modify']);
		if(!empty($insert['other_params'])) $fields['vars']['matrix'] = Library::array_merge($fields['vars']['matrix'], Library::block2hash($insert['other_params'], "\n"));

		switch($insert['type']){
			case 'multiselect':
			case 'checkbox_array':
				$fields['vars']['matrix']['value'] = empty($insert['additional']) ? array() : Library::list2hash($insert['value'], "\n");

			case 'select':
			case 'radio':
				$fields['vars']['matrix']['additional'] = empty($insert['additional']) ? array() : Library::block2hash($insert['additional']);
				break;

			case 'file':
				$insert['allow_ext'] = explode(' ', $insert['allow_ext']);
				$fields['vars']['matrix']['additional'] = $this->fieldValues(array('max', 'min', 'allow_ext', 'dstFolder'), $insert);
				break;

			case 'captcha':
				$fields['vars']['matrix']['captchaStandart'] = $this->values['captchaStandart'];
				if(!$fields['vars']['matrix']['warn_function']) $fields['vars']['matrix']['warn_function'] = 'checkFunctions::captcha';
				break;
		}

		foreach($fields as $i => $e) unset($fields['vars']['extra'][$i]);
		foreach($this->Core->getGPCArr('g') as $i => $e) unset($fields['vars']['extra'][$i]);
		foreach($fields['vars']['matrix'] as $i => $e) unset($fields['vars']['extra'][$i]);

		if(!$id && ($return = $this->DB->Ins(array($table, $fields)))) $this->setAdminStat('new', '{Call:Lang:core:core:dobavlenopol:'.Library::serialize(array($insert['name'])).'}', $table, $return);
		elseif($id && (($return = $this->DB->Upd(array($table, $fields, "`id`='$id'"))) !== false)) $this->setAdminStat('modify', '{Call:Lang:core:core:izmenenopole:'.Library::serialize(array($insert['text'])).'}', $table, $return, $this->DB->rowFetch(array($table, '*', "`id`='$return'")));

		if(!empty($insert['insert_field']) && $return !== false && !empty($extraTblParams)){
			if(empty($extraTblParams['DB'])) $extraTblParams['DB'] = $this->DB;
			if(empty($extraTblParams['field'])) $extraTblParams['field'] = $fields['name'];
			$alterType = $extraTblParams['DB']->issetField($extraTblParams['table'], $extraTblParams['field']) ? 'modify' : 'add';
			$alter = array();

			if(empty($extraTblParams['fieldType'])){
				switch($fields['type']){
					case 'checkbox':
						$extraTblParams['fieldType'] = 'CHAR(1)';
						break;

					case 'textarea':
					case 'multiselect':
					case 'checkbox_array':
						$extraTblParams['fieldType'] = 'TEXT';
						break;

					case 'gap':
					case 'calendar':
						$alter[$extraTblParams['DB']->issetField($extraTblParams['table'], $extraTblParams['field'].'_to') ? 'modify' : 'add'][$extraTblParams['field'].'_to'] = 'INT';

					case 'calendar2':
						$extraTblParams['fieldType'] = 'INT';
						break;

					default:
						$extraTblParams['fieldType'] = '';
				}
			}

			$alter[$alterType][$extraTblParams['field']] = $extraTblParams['fieldType'];
			$extraTblParams['DB']->Alter(array($extraTblParams['table'], $alter));
		}

		return $return;
	}

	protected function __ava__formFieldsModify($table, $id, $params = array(), $extraTblParams = array()){
		/*
			Изменение поля формы
			extra - Дополнительные настройки
		*/

		$func = $this->func;
		$templFile = 'form';
		$formName = $func;

		$values = $this->DB->rowFetch(array($table, '*', "id='$id'"));
		$values['vars'] = Library::unserialize($values['vars']);

		$mainCaption = '{Call:Lang:core:core:osnovnyepara}';
		$matrixParams = array('caption' => '{Call:Lang:core:core:pole:'.Library::serialize(array($values['name'])).'}');
		$extraBlocks = $extraValues = $matrixExtra = $matrixData = $matrixExclude = $matrixHiddens = array();

		if(!empty($values['vars']['extra'])) $values = Library::array_merge($values['vars']['extra'], $values);
		if(!empty($values['vars']['matrix'])) $values = Library::array_merge($values['vars']['matrix'], $values);
		if(!empty($values['vars']['eval'])) $values['eval'] = $values['vars']['eval'];

		switch($values['type']){
			case 'multiselect':
			case 'checkbox_array':
				$values['value'] = Library::hash2list($values['value']);

			case 'select':
			case 'radio':
				$values['additional'] = Library::hash2block($values['additional']);
				break;

			case 'file':
				$values['allow_ext'] = implode(' ', $values['vars']['matrix']['additional']['allow_ext']);
				$values['max'] = $values['vars']['matrix']['additional']['max'];
				$values['min'] = $values['vars']['matrix']['additional']['min'];
				$values['dstFolder'] = $values['vars']['matrix']['additional']['dstFolder'];
				break;

			case 'captcha':
				$values['captchaStandart'] = $values['vars']['matrix']['captchaStandart'];
				unset($values['vars']['matrix']['captchaStandart']);
				break;
		}

		$values['other_params'] = '';
		foreach($values['vars']['matrix'] as $i => $e){
			if($i != 'comment' && $i != 'warn' && $i != 'warn_function' && $i != 'warn_pattern' && $i != 'warn_pattern_text' && $i != 'additional' && $i != 'additional_text' && $i != 'additional_style' && $i != 'disabled' && $i != 'value'){
				$values['other_params'] .= "{$i}={$e}\n";
			}
		}

		unset($values['vars']);
		extract($params);

		if(!empty($extraTblParams)){
			if(!isset($matrixData['canAddNewField'])) $matrixData['canAddNewField'] = $extraTblParams['table'];
			$matrixData['issetField'] = $this->DB->issetField($extraTblParams['table'], isset($extraTblParams['field']) ? $extraTblParams['field'] : $values['name']);
		}

		$values = Library::array_merge($values, $extraValues);
		$matrixHiddens['modify'] = $id;

		if(!empty($extract)){
			foreach($extract as $e){
				if(regExp::Match("|^,.*,$|", $values[$e], true)) $values[$e] = library::str2arrKeys($values[$e]);
				else $values[$e] = library::unserialize($values[$e]);
			}
		}

		$matrixData = Library::array_merge(
			array(
				'extra' => true,
				'type' => $values['type'],
				'formTpl' => $extraBlocks ? 'multiblock' : 'big',
				'folders' => $this->Core->getFoldersListByPath($this->mod)
			),
			$matrixData
		);

		$fObj = $this->newForm($formName, $func.'&field_action=modify2', $matrixParams, $templFile);
		$this->addFormBlock($fObj, array('fields', $matrixExtra), $matrixData, $matrixExclude, $extraBlocks ? 'block0' : 'form');
		if($extraBlocks) $fObj->setParam('caption0', $mainCaption);

		$j = 1;
		foreach($extraBlocks as $i => $e){
			$this->addFormBlock( $fObj, $e['matrix'], Library::array_merge($matrixData, empty($e['formData']) ? array() : $e['formData']), $matrixExclude, 'block'.$j );
			$fObj->setParam('caption'.$j, $e['name']);
			$j ++;
		}

		$this->setContent($this->getFormText($fObj, $values, $matrixHiddens, $matrixData['formTpl']));
		$this->pathFunc = $func;
		$this->funcName = 'Изменить поле "'.$values['text'].'"';

		return $values;
	}

	protected function __ava__formFieldsActions($table, $params = array(), $extraTblParams = array()){
		/*
			Определенные множественные действия с полями формы
		*/

		if(empty($this->values['entry'])) return false;
		elseif(!empty($params['listParams']['protected'])){
			foreach($this->DB->columnFetch(array($table, "text", "id", $this->getEntriesWhere($params['listParams']['protected'], 'name'))) as $i => $e){
				if(!empty($this->values['entry'][$i])) $this->setError("entry", "Удаление поля \"$e\" запрещено");
			}
		}

		if($this->errorMessages) return false;
		if($this->values['action'] == 'suspend') $actionFields = array('show' => 0);
		elseif($this->values['action'] == 'unsuspend') $actionFields = array('show' => 0);
		elseif(!empty($params['actionFields'])) $actionFields = $params['actionFields'][$this->values['action']];

		unset($params['actionFields']);
		$where = $this->getEntriesWhere();
		extract($params);
		$fldList = array_unique($this->DB->columnFetch(array($table, 'text', '', $where)));

		switch($this->values['action']){
			case 'delete':
				$fields = $this->DB->columnFetch(array($table, 'name', 'name', $where));

				if($return = $this->DB->Del(array($table, $where))){
					$this->setAdminStat('delete', '{Call:Lang:core:core:udaleniepole:'.Library::serialize(array(implode(', ', $fldList))).'}', $table);

					if(!empty($extraTblParams)){
						if(empty($extraTblParams['DB'])) $extraTblParams['DB'] = $this->DB;
						$alter = array('drop' => array());

						foreach($fields as $i => $e){
							if(empty($extraTblParams['field'][$i])) $extraTblParams['field'][$i] = $i;
							$alter['drop'][$extraTblParams['field'][$i]] = $extraTblParams['field'][$i];
						}
						$extraTblParams['DB']->Alter(array($extraTblParams['table'], $alter));
					}
				}
				break;

			default:
				if($return = $this->DB->Upd(array($table, $actionFields, $where))){
					if($this->values['action'] == 'suspend') $this->setAdminStat('modify', '{Call:Lang:core:core:zablokirovan1:'.Library::serialize(array(implode(', ', $fldList))).'}', $table);
					if($this->values['action'] == 'unsuspend') $this->setAdminStat('modify', '{Call:Lang:core:core:razblokirova2:'.Library::serialize(array(implode(', ', $fldList))).'}', $table);
				}
				break;
		}

		return $return;
	}

	protected function getMatrixField($r, &$values = array(), &$extra = false){
		/*
			Возвращает параметры матрицы для поля, получая параметры выбранного поля
		*/

		$vars = is_string($r['vars']) ? Library::unserialize($r['vars']) : $r['vars'];
		$return = isset($vars['matrix']) ? Library::array_merge($r, $vars['matrix']) : $r;

		$values[$r['name']] = isset($vars['matrix']['value']) ? $vars['matrix']['value'] : '';
		$extra = isset($vars['extra']) ? $vars['extra'] : '';
		unset($return['vars'], $return['show'], $return['sort'], $return['value'], $return['id']);

		if(!empty($vars['eval'])) $return = Library::array_merge($return, eval($vars['eval']));
		return $return;
	}

	protected function __ava__getMatrixArray($req, $formBlocks = false, $blockName = false){
		/*
			Возвращает матрицу как массив
			Если специфицировано поле $formBlocks, формируется многоблочная структура
		*/

		$dbObj = $this->DB->Req($req);
		$matrix = $values = $names = $extra = array();

		while($r = $dbObj->Fetch()){
			$m = $this->getMatrixField($r, $values, $extra[$r['name']]);
			if(!isset($r[$formBlocks])) $matrix[$r['name']] = $m;
			else{
				$matrix[$r[$formBlocks]][$r['name']] = $m;
				$names[$r[$formBlocks]] = $r[$blockName];
			}
		}

		return array($matrix, $values, $names, $extra);
	}

	protected function __ava__getSearchMatrix($req, $orders = false, $search = false, &$values = array()){
		/*
			Возвращает матрицу как массив
			Если специфицировано поле $formBlocks, формируется многоблочная структура
		*/

		list($matrix, $values) = $this->getMatrixArray($req);
		$return = array(
			'searchFields' => array(),
			'searchMatrix' => array(),
			'orderFields' => array(),
			'isBe' => array(),
		);

		foreach($matrix as $i => $e){
			if($orders === false || in_array($i, $orders)){
				$return['orderFields'][$i] = regExp::lower($e['text']);
			}

			if($search === false || in_array($i, $search)){
				$return['searchFields'][$i] = $e['text'];
				unset($e['text'], $e['template']);

				if($e){
					$return['searchMatrix'][$i] = $e;
					switch($e['type']){
						case 'select':
						case 'radio':
							$return['isBe'][$i] = true;

						case 'checkbox_array':
							$e['additional'] = Library::array_merge(array('' => 'Все'), $e['additional']);
							$e['type'] = 'select';
							break;

						case 'textarea':
							unset($return['searchMatrix'][$i]['type']);
							break;
					}
				}
			}
		}

		return $return;
	}

	protected function __ava__getGeneratedFormValues($req, $values = false, $prefix = '', $postfix = ''){
		/*
			Список значений вводимых в генерированную форму
		*/

		list($matrix) = $this->getMatrixArray($req);
		return $this->getGeneratedFormValuesByMatrix($matrix, $values, $prefix, $postfix);
	}

	protected function __ava__getGeneratedFormValuesByMatrix($matrix, $values = false, $prefix = '', $postfix = ''){
		/*
			Список значений вводимых в форму по матрице
		*/

		if($values === false) $values = $this->values;
		$return = array();

		foreach($matrix as $i => $e){
			$return[$i] = isset($values[$prefix.$i.$postfix]) ? $values[$prefix.$i.$postfix] : '';
			if($e['type'] == 'gap' || $e['type'] == 'calendar'){
				$return[$i.'_to'] = isset($values[$prefix.$i.'_to'.$postfix]) ? $values[$prefix.$i.'_to'.$postfix] : '';
			}
		}

		return $return;
	}

	protected function __ava__getDescription($req, $tmplFile, $params = false, $tmplBlock = 'descript'){
		/*
			Создает блок-описание по матрице
		*/

		$return = '';
		if($params === false) $params = $this->values;
		list($matrix) = $this->getMatrixArray($req);

		foreach($matrix as $i => $e){
			$e['value'] = isset($params[$i]) ? $params[$i] : '';
			if($e['type'] == 'gap' || $e['type'] == 'calendar') $e['value_to'] = isset($params[$i.'_to']) ? $params[$i.'_to'] : '';
			elseif($e['type'] == 'radio' || $e['type'] == 'select') $e['value'] = isset($e['additional'][$e['value']]) ? $e['additional'][$e['value']] : '';

			if($e['type'] == 'calendar' || $e['type'] == 'calendar2'){
				$e['value'] = Dates::dateTime($e['value']);
				if($e['type'] == 'calendar') $e['value_to'] = Dates::dateTime($e['value_to']);
			}

			$return .= $this->Core->readBlockAndReplace($tmplFile, $tmplBlock, $this, $e);
		}

		return $return;
	}



	/******************************************************************************************************************************************************************

																	Типовое добавление расширений

	******************************************************************************************************************************************************************/

	protected function __ava__installExtensions($table, $prefix, $params = array(), $settingsFunc = 'settingsForm', $settingsGetFunc = 'getSettings'){
		/*
			Типовая установка расширений
		*/

		if(!isset($this->values['type_action'])) $this->values['type_action'] = '';

		switch($this->values['type_action']){
			case 'install':
				return $this->addExtension($table, $prefix, $params);
				break;

			case 'update':
				return $this->updateExtension($table, $prefix, $params);
				break;

			case 'update2':
				return $this->updateExtension2($table, $prefix, $params);
				break;

			case 'settings':
				return $this->adjustExtension($table, $prefix, $settingsFunc, $params);
				break;

			case 'settings2':
				return $this->adjustExtension2($table, $prefix, $settingsGetFunc, $params);
				break;

			case 'modify':
				return $this->modifyExtension($table, $params);
				break;

			case 'modify2':
				return $this->modifyExtension2($table, $params);
				break;

			case 'actions':
				return $this->extensionActions($table, $params);
				break;

			default:
				$name = $table;
				$form = 'type_extension_install';
				$list_name = $name.'_list';

				$func = $this->func;
				$formData = $formParams = $listParams = $listParams2 = array();
				$formActions = array('delete' => 'Удалить');

				if($this->DB->issetField($table, 'show')){
					$formActions['suspend'] = 'Отключить';
					$formActions['unsuspend'] = 'Включить';
				}

				extract($params);

				$listParams = Library::array_merge(
					array(
						'req' => array($table, "*", "", "`sort`"),
						'form_actions' => $formActions,
						'actions' => array(
							'name' => $func.'&type_action=modify',
							'update' => $func.'&type_action=update',
							'settings' => $func.'&type_action=settings',
						),
						'action' => $func.'&type_action=actions',
						'searchForm' => array(
							'searchFields' => array(
								'name' => 'Имя',
								'mod' => 'Идентификатор'
							),
							'orderFields' => array(
								'name' => 'имени',
								'mod' => 'идентификатору'
							)
						)
					),
					$listParams
				);

				$listParams2 = Library::array_merge(array('caption' => 'Список расширений'), $listParams2);
				$mods = $this->Core->getModulesByType($this->Core->getModuleTechName($this->mod));
				unset($mods[$this->mod]);
				$formData = Library::array_merge(array('mods' => $mods), $formData);

				$this->setContent(
					$this->getFormText(
						$this->addFormBlock(
							$this->newForm(
								$name,
								$func.'&type_action=install',
								$formParams
							),
							$form,
							$formData
						)
					)
				);

				$this->setContent(
					$this->getListText(
						$this->newList($list_name, $listParams, $listParams2)
					)
				);
		}
	}

	protected function __ava__modifyExtension($table, $params){
		/*
			Параметры расширения соединения
		*/

		$name = $table;
		$form = 'type_extension_install';
		$func = $this->func;
		$formData = $formParams = array();

		extract($params);

		return $this->typeModify(
			array($table, '*', "`id`='".db_main::Quot($this->values['id'])."'"),
			$form,
			$func.'&type_action=modify2',
			array(
				'formData' => Library::array_merge(array('modify' => 1), $formData),
				'params' => Library::array_merge(array('caption' => 'Параметры расширения "{name}"'), $formParams)
			)
		);
	}

	protected function __ava__modifyExtension2($table, $params){
		$this->isUniq($table, array('name' => 'Такое название уже используется'), $this->values['modify']);
		return $this->typeIns($table, $this->fieldValues(array('name', 'sort')), isset($params['func']) ? $params['func'] : $this->func);
	}

	protected function __ava__extensionActions($table, $params){
		return $this->typeActions($table, isset($params['func']) ? $params['func'] : $this->func);
	}

	protected function __ava__addExtension($table, $prefix, $params = array()){
		/*
			Инсталлирует новое расширение
		*/

		if(!$this->check()) return false;

		list($tmpFolder, $exMod, $files, $instParams) = $this->loadExtensionParams($prefix);
		foreach($files as $e) if(file_exists(_W.'modules/'.$instParams[2].'/extensions/'.$e)) $this->setError('extension', 'Файл "'.$e.'" уже существует');
		$this->isUniq($table, array('mod' => 'Такой модуль уже существует', 'name' => 'Такое имя уже существует'), false, '', array('mod' => $exMod, 'name' => $this->values['name']));

		if(!$this->check()) return false;
		$this->refresh(isset($params['func']) ? $params['func'] : $this->func);
		return $this->installExtension(isset($this->values['bill_mods']) ? $this->values['bill_mods'] : array(), $tmpFolder, $exMod, $instParams, $table, $params);
	}

	protected function __ava__updateExtension($table, $prefix, $params = array()){
		/*
			Обновление расширения
		*/

		$name = $table;
		$form = 'type_extension_install';
		$func = $this->func;
		$formData = $formParams = array();

		extract($params);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						$name,
						$func.'&type_action=update2',
						Library::array_merge(array('caption' => 'Обновить расширение'), $formParams)
					),
					'type_extension_file'
				),
				array(),
				array('id' => $this->values['id']),
				'big'
			)
		);
	}

	protected function __ava__updateExtension2($table, $prefix, $params = array()){
		/*
			Устанавливаем новое расширение
		*/

		if(!$this->check()) return false;
		list($tmpFolder, $exMod, $files, $instParams) = $this->loadExtensionParams($prefix);
		if($exMod && $exMod != regExp::lower($this->DB->cellFetch(array($table, 'mod', "`id`='{$this->values['id']}'")))){
			$this->setError('extension', 'Не совпадают установленное и обновляемое расширения');
		}

		if(!$this->check()) return false;

		$mods = array();
		foreach($this->Core->getModulesByType($this->Core->getModuleTechName($this->mod)) as $i => $e){
			if($this->Core->callModule($i)->DB->cellFetch(array($table, 'id', "`mod`='$exMod'"))) $mods[$i] = 1;
		}

		$return = $this->installExtension($mods, $tmpFolder, $exMod, $instParams, $table, $params, 'Upd');
		$this->refresh(isset($params['func']) ? $params['func'] : $this->func);
		return $return;
	}

	protected function __ava__adjustExtension($table, $prefix, $callFunc, $params = array()){
		/*
			Обновление расширения
		*/

		$name = $table;
		$func = $this->func;
		$varsField = 'vars';

		$formSettingsData = $formSettingsParams = $settingsMatrix = array();
		$data = $this->DB->rowFetch(array($table, array('mod', $varsField), "`id`='{$this->values['id']}'"));
		extract($params);

		$fObj = $this->newForm($name, $func.'&type_action=settings2', Library::array_merge(array('caption' => 'Настройка расширения'), $formSettingsParams));
		if($settingsMatrix) $this->addFormBlock($fObj, $settingsMatrix);

		if(!empty($exObj)){
			if(method_exists($exObj, $callFunc)) $exObj->$callFunc($this, $fObj, $table, $prefix, $params);
		}
		else{
			$exName = $prefix.$data['mod'];
			$this->Core->loadExtension($this->Core->getModuleTechName($this->getMod()), $exName);
			if(function_exists($exName.'::'.$callFunc)) call_user_func(array($exName, $callFunc), $this, $fObj, $table, $prefix, $params);
		}

		$this->setContent($this->getFormText($fObj, Library::unserialize($data[$varsField]), array('id' => $this->values['id']), 'big'));
	}

	protected function __ava__adjustExtension2($table, $prefix, $callFunc, $params = array()){
		/*
			Внести настройки расширения
		*/

		$varsField = 'vars';
		extract($params);
		$data = $this->DB->rowFetch(array($table, array('mod'), "`id`='{$this->values['id']}'"));
		$ins = !empty($settingsMatrix) ? $this->getFields($settingsMatrix) : array();

		if(isset($params['exObj'])){
			if(method_exists($params['exObj'], $callFunc)) $ins = Library::array_merge($ins, $exObj->$callFunc($this));
		}
		else{
			$exName = $prefix.$data['mod'];
			$this->Core->loadExtension($this->Core->getModuleTechName($this->getMod()), $exName);
			if(function_exists($exName.'::'.$callFunc)) $ins = Library::array_merge($ins, call_user_func(array($exName, $callFunc), $this));
		}

		$return = $this->DB->Upd(array($table, array($varsField => $ins), "`id`='{$this->values['id']}'"));
		$this->refresh(isset($params['func']) ? $params['func'] : $this->func);
		return $return;
	}

	protected function __ava__loadExtensionParams($prefix){
		/*
			Считывает параметры расширения
		*/

		$files = $params = array();

		if(!$tmpFolder = $this->Core->extract2tmpArc(TMP.$this->values['extension'])) $this->setError('extension', 'Не удалось распаковать архив');
		else{
			$files = Files::readFolder($tmpFolder);
			foreach($files as $e) if(regExp::Match("/^".$prefix."(\w+)\.php$/", $e, true, true, $m)) break;
			if(empty($m[1])) $this->setError('extension', 'Не найден файл расширения');
			else $params = $this->readExtensionParams($tmpFolder, $prefix, $m['1']);
		}

		if(!isset($params[2])) $params[2] = $this->Core->getModuleTechName($this->mod);
		return array($tmpFolder, isset($m[1]) ? $m[1] : '', $files, $params);
	}

	protected function __ava__readExtensionParams($folder, $prefix, $mod){
		/*
			Считывает параметры расширения
		*/

		require_once($folder.$prefix.$mod.'.php');
		$params = call_user_func(array($prefix.$mod, 'getInstallParams'));
		return $params;
	}

	protected function __ava__installExtension($mods, $tmpFolder, $exMod, $params, $table, $params2, $instType = 'Ins'){
		/*
			Ставит расширение для соединения
		*/

		if(!$this->Core->ftpCopy($tmpFolder, isset($params2['path']) ? $params2['path'] : _W.'modules/'.$params[2].'/extensions/')){
			$this->back($this->func, 'Ошибка копирования файлов');
			return false;
		}

		if(isset($params2['installFunc'])){
			$this->Core->setFlag('tmplLock');
			$return = $this->{$params2['installFunc']}($mods, $tmpFolder, $exMod, $params, $instType);

			$this->Core->rmFlag('tmplLock');
			$this->Core->rmFlag('refreshed');
			$this->Core->rmHeader('Location');
		}
		else{
			if($instType == 'Ins'){
				$mods[$this->mod] = 1;
				foreach($mods as $i => $e){
					$return = $this->DB->$instType(array($table, array('mod' => $exMod, 'name' => $this->values['name'], 'sort' => $this->values['sort'])));
				}
			}
			else $return = true;
		}

		return $return;
	}


	/******************************************************************************************************************************************************************

																	Перенаправление на аутентификацию

	******************************************************************************************************************************************************************/

	protected function __ava__authRedirect($new = true, $extraVars = array(), $extraMods = array()){
		/*
			Создает форму аутентификации
		*/

		$mMod = $this->Core->callModule('main');
		$mMod->values['type_auth'] = 2;
		$mMod->values['in_module'][$this->mod] = 1;

		foreach($extraMods as $e) $mMod->values['in_module'][$e] = 1;
		$mMod->values['redirect'] = $this->getCallUrl($extraVars);
		$return = $mMod->callFunc('registration');
		$this->Core->contentMod2Mod($mMod, $this, $new);
	}

	protected function __ava__getParam($param){
		return $this->Core->getParam($param, $this->mod);
	}


	/******************************************************************************************************************************************************************

																			Шаблонизатор

	******************************************************************************************************************************************************************/

	public function __ava__setMeta($name){
		/*
			Выставляет мета-параметры - title, keywords, description, caption
		*/

		$this->content['title'] = $name;
		$this->content['keywords'] = $name;
		$this->content['description'] = $name;
		$this->content['caption'] = $name;
	}



	/************************************************************************************************************************************************************************

																			Отправка писем

	*************************************************************************************************************************************************************************/

	public function __ava__getTmplParams($tmpl, $mod = false){
		/*
			Возвращает параметры шаблона по его имени
		*/

		if($mod === false) $mod = $this->mod;

		return $this->Core->DB->rowFetch(
			array(
				'mail_templates',
				array(
					'format',
					'subj',
					'body',
					'sender_eml',
					'sender',
					'extra',
					'notify_success',
					'notify_fail',
					'notify_success_subj',
					'notify_success_body',
					'notify_fail_subj',
					'notify_fail_body',
					'notify_eml',
					'notify_sender',
					'notify_sender_eml',
					'notify_success_extra',
					'notify_fail_extra'
				),
				"`name`='$tmpl' AND `mod`='{$mod}'"
			)
		);
	}

	public function __ava__getDefaultTmplParams($subj, $body){
		/*
			Возвращает параметры шаблона по его имени
		*/

		return array(
			'format' => $this->Core->getParam('mailFormat'),
			'subj' => $subj,
			'body' => $body,
			'sender_eml' => $this->Core->getParam('defaultEml'),
			'sender' => $this->Core->Site->params['name'],
			'extra' => '',
			'notify_success' => '',
			'notify_fail' => '',
			'notify_success_subj' => '',
			'notify_success_body' => '',
			'notify_fail_subj' => '',
			'notify_fail_body' => '',
			'notify_eml' => '',
			'notify_sender' => '',
			'notify_sender_eml' => '',
			'notify_success_extra' => '',
			'notify_fail_extra' => '',
		);
	}

	public function __ava__mail($receiver, $params = array(), $data = array()){
		/*
			Отправляет письмо сформированное в соответствии с tmpl (имя шаблона письма)
			Письмо может быть отправлено через очередь или сразу (this::mail):
				Вначале для него готовят все данные
				Затем письмо вносится в базу

				Если письмо поставлено на очередь, взвращается ответ что письмо в очереди (return 2)
				В очереди cron выполняет те шаги которые положено выполнять без очереди сразу

				Если отправка сразу (mail::sendWithQueue($id, $params)(Данные из БД как есть - масивом)):
					Отсылается запрос на отправку
					Если отправка прошла удачно:
						В базу вносится запись что письмо ушло удачно
						Если стоит галка отправлять удачно отправленные письма админу, копия отсылается админу

					Если отправка прошла неудачно:
						Если исчерпано число повторов отправки или письмо устарело:
							Ставится пометка не отправлено и не должно
							Если стоит пометка "Уведомлять админа о неудачных письмах по истечении очереди" копия уходит админу

						Если не исчерпано число повторов:
							Ставится пометка о том что попытка повторилась
							Если стоит пометка "Уведомлять админа о всех неудачных письмах" копия уходит админу
		*/

		if(SHOW_HWT > 0) $debId = $this->Core->debugStart();
		if(!$params) $params = $this->getDefaultTmplParams();
		elseif(!is_array($params)) $params = $this->getTmplParams($params, $this->mod);

		$data['receiver'] = $params['eml'] = $receiver;
		$data['siteName'] = $GLOBALS['Core']->Site->params['name'];
		$data['siteUrl'] = $GLOBALS['Core']->Site->params['url'];

		$data['subj'] = $params['subj'] = $GLOBALS['Core']->replace($params['subj'], $this, $data);
		$data['body'] = $params['body'] = $GLOBALS['Core']->replace($params['body'], $this, $data);
		$params['notify_success_subj'] = $GLOBALS['Core']->replace($params['notify_success_subj'], $this, $data);

		$params['notify_success_body'] = $GLOBALS['Core']->replace($params['notify_success_body'], $this, $data);
		$params['notify_fail_subj'] = $GLOBALS['Core']->replace($params['notify_fail_subj'], $this, $data);
		$params['notify_fail_body'] = $GLOBALS['Core']->replace($params['notify_fail_body'], $this, $data);

		$params['mod'] = $this->mod;
		$params['func'] = $this->func;
		$params['date'] = time();

		if(!is_array($params)) throw new AVA_Exception('Неверные параметры переданы для формирования письма: [nocall]'.$params.'[/nocall]');
		$id = $this->Core->DB->Ins(array('mails', $params));
		if($this->Core->getParam('mailQueue')) return 2;

		$return = mail::sendWithQueue($id);
		if(SHOW_HWT > 0) $this->Core->debugEnd('{Call:Lang:core:core:otpravkapism:'.Library::serialize(array($params['subj'], $params['eml'])).'}', '', $debId);
		return $return;
	}


	/******************************************************************************************************************************************************************

																	Обслуживание стандартных ссылок

	******************************************************************************************************************************************************************/

	public function __ava__insertLink($links, $parent = ''){
		if(Library::isHash($links)) $links = array($links);
		$instObj = new InstallModuleObject($this->Core->DB, $this, $this->mod);
		return $instObj->setDefaultModuleLinks($links, 'Ins', $parent);
	}


	/******************************************************************************************************************************************************************

																	Обслуживание записей статистики

	******************************************************************************************************************************************************************/

	protected function __ava__setAdminStat($actionType, $actionDescript = '', $object = '', $objectId = 0, $vars = array(), $mod = '', $adminId = 0){
		if(defined('IN_INSTALLATOR') && IN_INSTALLATOR > 0) return;
		if(!$adminId) $adminId = $this->User->getAdminId();
		if(!$mod) $mod = $this->mod;

		$this->Core->DB->Ins(
			array(
				'admin_stat',
				array(
					'admins_id' => $adminId,
					'date' => time(),
					'ip' => $this->Core->getGPCVar('s', 'REMOTE_ADDR'),
					'action_type' => $actionType,
					'action_descript' => $actionDescript,
					'action_mod' => $mod,
					'action_object' => $object,
					'action_id' => $objectId,
					'vars' => Library::serialize($vars)
				)
			)
		);
	}


	/******************************************************************************************************************************************************************

															Функции обслуживания пользовательской части

	******************************************************************************************************************************************************************/

	public function getEntriesRecursive($array, $nameFld = 'name', $parentFld = 'parent_id', $idFld = 'id', $exPref = false, $prefix = '', $parent = 0){
		/*
			Возвращает список сформированный рекурсивно
		*/

		$return = array();
		if($exPref === false) $exPref = '&#160;&#160;';

		foreach($array as $i => $e){
			if(!isset($e[$nameFld])) throw new AVA_Exception('{Call:Lang:core:core:nevernozadan}');
			elseif(!isset($e[$idFld])) throw new AVA_Exception('{Call:Lang:core:core:nevernozadan1}');
			elseif(!isset($e[$parentFld])) throw new AVA_Exception('{Call:Lang:core:core:nevernozadan2}');

			if($parent == $e[$parentFld]){
				$return[$e[$idFld]] = $prefix.$e[$nameFld];
				$return = Library::array_merge($return, $this->getEntriesRecursive($array, $nameFld, $parentFld, $idFld, $exPref, $prefix.$exPref, $e[$idFld]));
			}
		}

		return $return;
	}

	public function getEntriesRecursiveArray($array, $parentFld = 'parent_id', $idFld = 'id', $parent = 0){
		/*
			Возвращает список сформированный рекурсивно
		*/

		$return = array();
		foreach($array as $i => $e){
			if(!isset($e[$idFld])) throw new AVA_Exception('{Call:Lang:core:core:nevernozadan1}');
			elseif(!isset($e[$parentFld])) throw new AVA_Exception('{Call:Lang:core:core:nevernozadan2}');

			if($parent == $e[$parentFld]){
				$return[$e[$idFld]] = $e;
				$return[$e[$idFld]]['subblock'] = $this->getEntriesRecursiveArray($array, $parentFld, $idFld, $e[$idFld]);
			}
		}

		return $return;
	}

	protected function __ava__setUserAction($action, $markType1, $mark1, $markType2 = '', $mark2 = '', $markType3 = '', $mark3 = ''){
		/*
			Делает запись о произведенном пользователем действии
		*/

		$sessId = $this->Core->sessGetId();
		$userId = $this->user;

		$mod = $this->mod;
		$func = $this->func;
		$date = time();

		return $this->DB->Ins(
			array(
				'user_actions',
				array(
					'sess_id' => $sessId,
					'user_id' => $userId,
					'mark1' => $mark1,
					'mark_type1' => $markType1,
					'mark2' => $mark2,
					'mark_type2' => $markType2,
					'mark3' => $mark3,
					'mark_type3' => $markType3,
					'date' => $date,
					'action' => $action,
					'mod' => $mod,
					'func' => $func
				)
			)
		);
	}
}

?>
