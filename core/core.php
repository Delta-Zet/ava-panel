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


class Core extends objectInterface {

	private $settings = array();	//Установленные настройки. Могут перекрываться личными настройками юзера.
	private $modSettings = array();
	private $flags = array();		//Установленные флаги

	//Сессии
	public $session = array();		//Данные сессии
	private $sessId;				//ID сессии

	//headers
	private $headers = array();		//HTTP-хеадерс для отправки
	private $cookie = array();		//Cookie Для отправки (полученные кукисы сидят в $this->c)

	//DB
	public $DB;							//Объект работы с БД основной
	private $databases;					//Ваще все базы
	private $dbParams;					//Ваще все параметры подключения к базам

	//GPC
	private $g;			//$_GET
	private $p;			//$_POST
	private $c;			//$_COOKIE

	private $s;			//$_SERVER
	private $f;			//$_FILES
	protected $callData;	//Объединение массивов $_POST и $_GET передаваемое в объект модуля и вызываемое как $this->values
	public $self;		//Указывает на сам-себя скрипт

	private $templ;		//Страница внутри шаблона, типа main, mypage, anypage. Соответствует main.tmpl, mypage.tmpl и т.д.
	private $mod;		//Имя вызываемого основного модуля
	private $func;		//Имя вызываемой основной функции
	private $varOnly;	//если установлено, только значение этого блока будет выведено

	//Плагины
	private $plugins = false;				//Усе плагины
	private $pluginsByType = array();		//По типу и сервису
	private $noExistFuncPlugs = false;		//Плагины подменяющие функции

	//Шаблоны
	public $tmplText;
	private $templType;	//Тип шаблона - main, admin или system
	private $templName;	//Имя шаблона установленного в системе (default, sometemplate, mybesttemplate)
	private $templateParams = array();

	private $templateBlocks = array();
	private $allTemplates = array();		//Все шаблоны как $allTemplates[$type][$name] = $text
	private $allTemplatesById = array();	//Все шаблоны как $allTemplates[$type][$id] = $text
	private $allModuleTemplates = array();	//Шаблоны модуля

	private $templatePages = array();					//Считанные страницы шаблона. Для шаблонов блоков - разбитые на блоки
	private $templatePageBlockNames = array();			//Названия блоков шаблона типа blocks
	private $templatePageBlockNamesByTmpl = array();	//Названия блоков шаблона типа blocks по шаблону записи

	//Шаблоны
	private $mailTemplates = false;
	private $mailTemplatesById = array();
	private $mailTemplateNames = array();

	//Вызовы модулей
	private $mainModObj;
	private $modules = array();
	private $modulePrototypes = array();
	private $moduleParams = array();
	private $moduleParamsById = array();
	private $unitedModules = array();
	private $unitedModules2 = array();
	private $modulesSites = array();
	private $sitesModules = array();
	private $sites = array();
	private $siteParams = array();

	//Языки
	private $langs = array();
	private $langParams = array();
	private $langParamsById = array();

	//FTP
	private $ftpConnections = array();

	//Папочки
	private $folders = array();
	private $folderMods = array();
	private $folderPaths = array();
	private $folderNames = array();
	private $folderNamesByMod = array();
	private $folderNamesByPath = array();
	private $folderNamesByPathByMod = array();

	//Шрифтики
	private $fonts = array();
	private $fontsByFile = array();

	//Фоны CAPTCHA
	private $captchaBackgrounds = array();
	private $captchaStandarts = array();
	private $captchaStandartsOpen = array();
	private $captchaStandartParams = array();
	private $captchaStandartParamsById = array();

	//Воденые знаки
	private $watermarks = array();
	private $watermarkParams = array();

	//Стандарты обработки изображений
	private $imageStandarts = array();
	private $imageStandartParams = array();

	//Правила формирования URL
	private $allUrlsRights = false;
	private $urlsRights = array();
	private $urlsRightsByType = array();
	private $urlsRightsByMod = array();

	//Преобразователь ссылок
	private $urlRewriteParams = array();
	private $inversePattern = array();
	private $inverseVarPattern = array();

	//Перезапись URL
	private $rewritedUrls = false;

	//Версия
	private $version;

	//Разные пользователи
	private $adminsList = array();
	private $adminsParams = array();
	private $adminsParamsById = array();

	private $usersList = array();
	private $usersGroups = false;
	private $usersFormTypes = false;

	//Пользователь
	public $User;					//Авторизованный юзер, соответствует id юзера в системе
	public $Site;					//Данные текущего сайта
	public $adminSite;				//Данные текущего администрируемого сайта
	public $Lang;

	//Кэш
	private $cache = array();
	private $dataCache = array();

	//Debug
	public $Debugger;

	public function __construct($type = 'main'){
		$this->Debugger = new Debugger();

		$this->setGPC('g', $_GET, true);
		$this->setGPC('p', $_POST);
		$this->setGPC('c', $_COOKIE);
		$this->setGPC('s', $_SERVER);
		$this->setGPC('f', $_FILES, false, false);

		if(TEST_MODE > 0){
			ini_set('display_errors', '1');
			error_reporting(E_ALL);
		}
		else{
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		ini_set('date.timezone', 'Europe/London');
		if(!library::versionCompare('5.3', phpVersion())){
			//Если до PHP 5.3
			set_magic_quotes_runtime(0);
		}

		if(empty($this->g['mod'])) $this->setMod('main');
		else $this->setMod($this->g['mod']);

		if(empty($this->g['func'])) $this->setFunc('main');
		else $this->setFunc($this->g['func']);

		if(empty($this->g['template'])) $this->templ = 'main';
		else $this->templ = $this->g['template'];

		if(!empty($this->g['AVA_varOnly'])) $this->setExclusiveVar($this->g['AVA_varOnly']);
		unset($this->g['mod'], $this->g['func'], $this->g['template'], $this->g['AVA_varOnly']);

		$this->callData = Library::array_merge(Library::array_merge($this->p, $this->f), $this->g);
		$this->self = $_SERVER['REQUEST_URI'];
		$this->templType = $type;

		$this->User = new User;
		$this->Site = new Site;
		$this->adminSite = new Site;
		$this->Lang = new Lang(LANGUAGE);
	}

	public function loadDB(){
		$this->DB = $this->getDB();
	}

	public function loadSite(){
		/*
			Считывает данные сайта
		*/

		$site = '';
		$data = array();
		$default = array();

		$host = $_SERVER['HTTP_HOST'];
		$path = $host.$_SERVER['PHP_SELF'];

		foreach($this->DB->columnFetch(
			array(
				'sites',
				'*',
				'',
				db_main::q(
					"#0 REGEXP (`url`) OR #1 REGEXP (`url`) OR #2 REGEXP (`url`) OR #3 REGEXP (`url`) OR `default`='1'",
					array('http://'.$path, 'https://'.$path, 'http://www.'.$path, 'https://www.'.$path)
				)
			)
		) as $r){
			if((regExp::Len($r['url']) > regExp::Len($site)) && !$r['default']){
				$site = $r['url'];
				$data = $r;
			}
			elseif($r['default'] == 1){
				$default = $r;
			}
		}

		if(!$site){
			$site = $default['url'];
			$data = $default;
		}

		define('_D', $site);
		define('TMPL_STYLE_FOLDER', $site.TMPL_FOLDER.'/');
		$this->Site->loadParams($data);

		if(Library::constVal('IN_ADMIN')){
			$id = $this->Site->getSiteId();
			$data = $this->DB->rowFetch(array('sites', '*', db_main::q("`id`=#0 OR `default`='1'", array($id)), "`default`"));
			$this->adminSite->loadParams($data);
		}
	}

	public function URLParse(){
		/*
			Устанавливает параметры на основании URL запроса.

			Вся строка запроса помещается в переменную AVAUrl с использованием mod_rewrite,
			либо передается в QUERY_STRING после ? но до &
			либо конкретно в переменной GET['AVAUrl'] запроса

			Вначале ищется такая строка в pages, если есть то вызывается соответствующий pages, если нету то строка разбирается по паттерну и осуществляется
			обращение к соответствующей функции соответствующего модуля
		 */

		$useSef = $this->getParam('useSef');

		if(!$useSef) return;
		elseif($useSef == 'mod_rewrite') $url = isset($this->g['AVAUrl']) ? $this->g['AVAUrl'] : '';
		elseif($useSef == 'append_path') $url = regExp::replace("/^".regExp::quote($this->Site->urlParse['path'].'index.php')."/iUs", '', $this->s['REQUEST_URI'], true);
		elseif($useSef == 'append_query') $url = $this->s['QUERY_STRING'];
		else throw new AVA_Exception('{Call:Lang:core:core:neopredelenn:'.Library::serialize(array($useSef)).'}');

		if(regExp::Match('AVAUrl=', $url)){
			throw new AVA_Exception('{Call:Lang:core:core:vyispolzuete}');
		}

		$url = $this->getRewritedUrl(regExp::Replace("/index\.php$/", "", $url, true));

		if($useSef && $url){
			$srcPattern = $this->getParam('sefUrlPattern');
			$srcVarPattern = $this->getParam('sefUrlVarPattern');
			$pattern = "|^".$this->getNormalPattern($srcPattern)."$|is";
			$varPattern = "|".$this->getNormalPattern($srcVarPattern)."|iUs";

			Regexp::Match($pattern, $url, true, true, $m);
			$m1 = Regexp::MatchAll('/\([^)]*(\$\d+)[^)]*\)/iUs', $srcPattern);
			$vars = array();

			foreach($m1['1'] as $i => $e){
				if($e == '$1') $vars['mod'] = empty($m[$i + 1]) ? '' : $m[$i + 1];
				if($e == '$2') $vars['func'] = empty($m[$i + 1]) ? '' : $m[$i + 1];
				if($e == '$3') $vars['vars'] = empty($m[$i + 1]) ? '' : $m[$i + 1];
			}

			$this->g['mod'] = $vars['mod'];
			$this->g['func'] = $vars['func'];

			if($vars['vars']){
				if($urParams = $this->getUrlRightsByMod($this->g['mod'], $this->g['func'], 'dropvars')){
					$m11 = regExp::Split($urParams['vars']['dropVarsDlm2'], $vars['vars'], false, 2);
					$vars['vars'] = isset($m11[1]) ? $m11[1] : '';
				}

				if($vars['vars']){
					$m2 = Regexp::MatchAll('/\([^)]*(\$\d+)[^)]*\)/iUs', $srcVarPattern);
					$m3 = Regexp::MatchAll($varPattern, $vars['vars']);

					if($m2['0']['0'] == '$1'){
						$varPos = '2';
						$valPos = '1';
					}
					else{
						$varPos = '1';
						$valPos = '2';
					}

					$rsltStr = '';
					foreach($m3['0'] as $i => $e) $rsltStr .= $m3[$varPos][$i].'='.$m3[$valPos][$i].'&';
					$this->setGPC('g', Library::array_merge($this->g, Library::parseStr($rsltStr)), true);
				}
			}
		}
		else{
			return;
		}

		if($this->g['mod']) $this->setMod($this->g['mod']);
		if($this->g['func']) $this->setFunc($this->g['func']);
		if(!empty($this->g['template'])) $this->templ = $this->g['template'];
		if(!empty($this->g['AVA_varOnly'])) $this->setExclusiveVar($this->g['AVA_varOnly']);

		unset($this->g['AVAUrl'], $this->callData['AVAUrl'], $this->g['mod'], $this->g['func'], $this->g['template'], $this->g['AVA_varOnly']);
		$this->callData = Library::array_merge($this->callData, $this->g);
		$this->self = _D.$url;
	}

	private function fetchUrlRights(){
		if($this->allUrlsRights === false){
			$this->allUrlsRights = $this->DB->columnFetch(array('url_gen_rights', '*', 'name'));

			foreach($this->allUrlsRights as $i => $e){
				$e['vars'] = Library::unserialize($e['vars']);
				$this->allUrlsRights = $e;

				if($e['show']){
					$this->urlsRights[$i] = $e;
					$this->urlsRightsByType[$e['type']][$i] = $e;
					$this->urlsRightsByMod[$e['mod']][$e['func']][$e['type']] = $e;
				}

			}
		}
	}

	public function __ava__getUrlRights(){
		$this->fetchUrlRights();
		return $this->urlsRights;
	}

	public function __ava__getAllUrlRights(){
		$this->fetchUrlRights();
		return $this->allUrlsRights;
	}

	public function __ava__getUrlRightsByType($type){
		$this->fetchUrlRights();
		return isset($this->urlsRightsByType[$type]) ? $this->urlsRightsByType[$type] : array();
	}

	public function __ava__getUrlRightsByMod($mod, $func, $type = false){
		$this->fetchUrlRights();
		if($type) return isset($this->urlsRightsByMod[$mod][$func][$type]) ? $this->urlsRightsByMod[$mod][$func][$type] : array();
		else return isset($this->urlsRightsByMod[$mod][$func]) ? $this->urlsRightsByMod[$mod][$func] : array();
	}

	private function fetchRewritedUrls(){
		if($this->rewritedUrls === false){
			$this->rewritedUrls = $this->DB->columnFetch(array('urls', 'url', 'rewrited', "`site`='".$this->Site->getSiteId()."'"));
		}
	}

	public function __ava__getRewritedUrls(){
		$this->fetchRewritedUrls();
		return $this->rewritedUrls;
	}

	public function __ava__getRewritedUrl($url){
		$this->fetchRewritedUrls();
		return isset($this->rewritedUrls[$url]) ? $this->rewritedUrls[$url] : $url;
	}

	private function getNormalPattern($pat){
		/*
			Создает нормальный паттерн для поиска совпадения
		*/

		$pat = regExp::ReplaceCallback('/^([^(]*)\(/iUs', create_function('$m', 'return regExp::quot($m["1"], "|")."(";'), $pat);
		$pat = regExp::ReplaceCallback('/\)([^)]*)$/iUs', create_function('$m', 'return ")".regExp::quot($m["1"], "|");'), $pat);
		$pat = regExp::ReplaceCallback('/\)([^()]*)\(/iUs', create_function('$m', 'return ")".regExp::quot($m["1"], "|")."(";'), $pat);

		$pat = regExp::ReplaceCallback(
			'/\(([^\)]*)(\$\d+)([^\)]*)\)/is',
			create_function(
				'$m',
				'$pos1 = $pos2 = $pos3 = "";
				if($m["1"]) $pos1 = "[".regExp::quot($m["1"], "|")."]*";
				if($m["3"]) $pos3 = "[".regExp::quot($m["3"], "|")."]*";

				if($m["2"] == \'$1\') $pos2 = "([^/]+)";
				elseif($m["2"] == \'$2\') $pos2 = "([^/]*)";
				else $pos2 = "(.*)";

				return $pos1.$pos2.$pos3;
				'
			),
			$pat
		);

		return $pat;
	}

	public function loadAllData(){
		/*
			Считывает и устанавливает всю необходимую для работы информацию - подключение к БД, считывание настроек, сессий и т.п.
		*/

		if($this->DB->cellFetch(array('site_access_ip', 'id', db_main::q("#0 REGEXP (`ip`)", array($GLOBALS['Core']->getGPCVar('s', 'REMOTE_ADDR')))))){
			throw new AVA_Access_Exception('{Call:Lang:core:core:dostupksajtu}');
		}

		$this->loadSite();
		$this->settingsLoad();
		$this->runPlugins('settings');

		$this->setTemplateType($this->templType);
		if(Library::constVal('IN_SITE') && $this->mod == 'main' && $this->func == 'main') $this->URLParse();
		$this->runPlugins('url');

		$this->sessStart();
		$this->authUser();
		$this->templName = $this->loadTemplateName($this->templType);

		if(Library::constVal('IN_SITE')) $this->Lang->setLang($this->getParam('language'));
		else $this->Lang->setLang($this->getParam('adminLanguage'));
	}



	/************************************************************************************************************************************************************************

																		Работа с плагинами

	*************************************************************************************************************************************************************************/

	public function getCurrentPluginService(){
		/*
			Возвращает какой сервис для плагов работает
		*/

		if(Library::constVal('IN_INSTALLATOR')) return 'install';
		elseif(Library::constVal('IN_ADMIN')) return 'admin';
		elseif(Library::constVal('IN_API')) return 'api';
		elseif(Library::constVal('IN_CRON')) return 'cron';
		else return 'site';
	}

	public function getPlugin($name){
		/*
			Возвращает параметры плага
		*/

		$this->loadPlugins();
		return empty($this->plugins[$name]) ? array() : $this->plugins[$name];
	}

	public function getPluginCode($name){
		/*
			Возвращает код плагина
		*/

		$this->loadPlugins();
		return empty($this->plugins[$name]['code']) ? false : $this->plugins[$name]['code'];
	}

	public function getPlugins($type, $service, $pos = false, $obj = false, $func = false){
		/*
			Возвращает плаги по типу
		*/

		if($service == 'install') return array();
		$this->loadPlugins();
		$return = empty($this->pluginsByType[$type][$service]) ? array() : $this->pluginsByType[$type][$service];

		if($obj){
			foreach($return as $i => $e){
				if(!empty($this->plugins[$e]['class']) && !($obj instanceof $this->plugins[$e]['class'])) unset($return[$i]);
			}
		}

		if($func){
			$func = regExp::lower($func);
			foreach($return as $i => $e){
				if($this->plugins[$e]['function'] != $func) unset($return[$i]);
			}
		}

		if($pos){
			foreach($return as $i => $e){
				if($this->plugins[$e]['point'] != $pos) unset($return[$i]);
			}
		}

		return $return;
	}

	public function __callNoExistFunc($obj, $func, $args){
		/*
			Вызыает
		*/

		if($this->noExistFuncPlugs === false){
			if(($service = $this->getCurrentPluginService()) == 'install') return $obj->__callAVAfunc($func, $args);
			$this->loadPlugins();
			$this->noExistFuncPlugs = array();

			foreach($this->plugins as $i => $e){
				if($e['type'] == 'noExistFunc' && !empty($e['vars']['services'][$service])){
					$this->replaceFuncsPlugs[$e['point']][$e['function']][$e['mod']][] = $e['name'];
				}
			}
		}

		if(isset($this->replaceFuncsPlugs['before'][$func])){
			foreach($this->replaceFuncsPlugs['before'][$func] as $i => $e){
				if($obj instanceof $i){
					$obj->runPluginsList($e);
					break;
				}
			}
		}

		$instead = false;
		if(isset($this->replaceFuncsPlugs['instead'][$func])){
			foreach($this->replaceFuncsPlugs['before'][$func] as $i => $e){
				if($obj instanceof $i){
					$instead = $e;
					break;
				}
			}
		}

		$return = $instead ? $obj->runPluginsList($instead) : $obj->__callAVAfunc($func, $args);

		if(isset($this->replaceFuncsPlugs['after'][$func])){
			foreach($this->replaceFuncsPlugs['after'][$func] as $i => $e){
				if($obj instanceof $i){
					$obj->runPluginsList($e);
					break;
				}
			}
		}

		return $return;
	}

	public function __ava__getWidgets(){
		/*
			Возвращает виджеты
		*/

		$this->loadPlugins();
		$return = array();

		foreach($this->plugins as $i => $e){
			$return[$i] = $e['text'];
		}
		return $return;
	}

	public function __ava__getWidgetForms(){
		/*
			Формы установки виджетов
		*/

		$this->loadPlugins();
		$return = array();

		foreach($this->plugins as $i => $pluginParams){
			if($pluginParams['set_code']) $return[$i] = eval($pluginParams['set_code']);
		}

		return $return;
	}

	public function loadPlugins(){
		/*
			Считывает список всех плагов в системе
		*/

		if(($this->plugins === false) && is_object($this->DB)){
			$this->plugins = $this->DB->columnFetch(array('plugins', '*', "name", "`show`", "`sort`"));
			foreach($this->plugins as $i => $e){
				$this->plugins[$i]['vars'] = $e['vars'] = Library::unserialize($e['vars']);

				switch($e['type']){
					case 'point':
						$this->plugins[$i]['point'] = $e['vars']['point'];
						break;

					case 'modulePoint':
						$this->plugins[$i]['mod'] = $e['vars']['modulePointMod'];
						$this->plugins[$i]['point'] = $e['vars']['modulePoint'];
						break;

					case 'function':
						$this->plugins[$i]['mod'] = $e['vars']['functionMod'];
						$this->plugins[$i]['function'] = $e['vars']['function'];
						$this->plugins[$i]['point'] = $e['vars']['position'];
						break;

					case 'noExistFunc':
						$this->plugins[$i]['mod'] = $e['vars']['noExistFuncClass'];
						$this->plugins[$i]['function'] = $e['vars']['noExistFunc'];
						$this->plugins[$i]['point'] = $e['vars']['position'];
						break;
				}

				if(!empty($this->plugins[$i]['vars']['services'])){
					foreach($this->plugins[$i]['vars']['services'] as $i1 => $e1) $this->pluginsByType[$this->plugins[$i]['type']][$i1][] = $i;
				}
			}
		}
	}

	public function getPluginPoints(){
		return array();
	}



	/************************************************************************************************************************************************************************

																		Работа с шаблонизатором

	*************************************************************************************************************************************************************************/

	public function getTempl(){
		//Возвращает установленную страницу шаблона

		return $this->templ;
	}

	public function __ava__setTempl($name){
		//Устанавливает страницу шаблона

		if(!$this->getFlag('tmplLock')) $this->templ = $name;
	}

	public function __ava__getTemplateType(){
		//Возвращает текущий основной шаблон - main, admin или system

		return $this->templType;
	}

	public function __ava__setTemplateType($type){
		//Устанавливает текущий основной шаблон - main, admin или system. Вызов бесполезен после запуска основного шаблона

		if($this->getFlag('tmplLock')) return;
		if($type == 'system'){
			if($this->templType != 'system'){
				$name = $this->getTemplateName($type);
				$this->templateParams[$type][$name] = empty($this->templateParams[$this->templType][$this->templName]) ? array() : $this->templateParams[$this->templType][$this->templName];
				$this->setTemplateName($this->getTemplateName('system'));
			}
		}

		$this->templType = $type;
	}

	public function __ava__getTemplateName($type = false){
		/*
			Возвращает название действующего установленного шаблона.
			Если не установлен, шаблон устанавливается
			Параметр type определяет тип шаблона (main, admin, blocks и т.п.) по умолчанию берется текущий установленный в системе тип
		*/

		if($type === false || $type == $this->getTemplateType()) return $this->templName;
		elseif(($type == 'main') || ($type == 'admin')) return $this->loadTemplateName($type);
		else{
			$this->loadTemplates();
			if($type == 'system') $return = $this->templateParams[$this->templType][$this->templName]['vars']['dependTemplates']['sys_depend_tmp'];
			if(empty($return)) throw new AVA_Templ_Exception('{Call:Lang:core:core:nenajdenshab:'.Library::serialize(array($type, $this->templName)).'}');
			return $return;
		}
	}

	public function __ava__setTemplateName($name){
		if(!$this->getFlag('tmplLock')) $this->templName = $name;
	}

	public function __ava__loadTemplateName($type){
		/*
			Считывает текущее имя шаблона типа admin или main
		*/

		if(!empty($this->callData['tmplName'])) return $this->callData['tmplName'];
		else{
			switch($type){
				case 'admin':
					if($tmpl = $this->sessGet('adminTmplName')) return $tmpl;
					elseif(!empty($this->User->adminParams['template'])) return $this->User->adminParams['template'];
					else return $this->getParam('adminTemplate');

				case 'main':
					if($tmpl = $this->sessGet('tmplName')) return $tmpl;
					elseif(!empty($this->User->params['template'])) return $this->User->params['template'];
					else return $this->getParam('template');

				default:
					throw new AVA_Templ_Exception('{Call:Lang:core:core:nevozmozhnoo:'.Library::serialize(array($type)).'}');
			}
		}
	}

	public function __ava__getTemplateUrl($type = false, $name = false){
		/*
			URL-путь для шаблона
		*/

		if($type === false) $type = $this->getTemplateType();
		elseif($type == 'module') return $this->getModuleTemplateUrl($this->mod);
		if($name === false) $name = $this->getTemplateName($type);
		return TMPL_STYLE_FOLDER.$type.'/'.$name.'/';
	}

	public function __ava__getTemplatePath($type = false, $name = false){
		/*
			полный путь для шаблона
		*/

		if($type === false) $type = $this->getTemplateType();
		elseif($type == 'module') return $this->getModuleTemplatePath($this->mod);
		if($name === false) $name =  $this->getTemplateName($type);
		return TMPL.$type.'/'.$name.'/';
	}

	public function __ava__getModuleTemplateUrl($module){
		$data = $this->getModuleParams($module);
		return TMPL_STYLE_FOLDER.'modules/'.$data['name'].'/'.$this->getModuleTemplateName($module).'/';
	}

	public function __ava__getModuleTemplatePath($module){
		$data = $this->getModuleParams($module);
		return TMPL.'modules/'.$data['name'].'/'.$this->getModuleTemplateName($module).'/';
	}

	public function getTemplateFolder($type, $folder){
		/*
			Возвращает папку шаблона
		*/

		return TMPL.$type.'/'.$folder.'/';
	}

	private function loadTemplates($force = false){
		/*
			Считывает шаблоны системы
		*/

		if(!$this->templateParams || $force){
			foreach($this->DB->columnFetch(array('templates', '*', '', '', "sort")) as $r){
				$r['vars'] = Library::unserialize($r['vars']);
				$this->templateParams[$r['type']][$r['folder']] = $r;
				$this->allTemplates[$r['type']][$r['folder']] = array($r['name'], $r['show']);
				$this->allTemplatesById[$r['type']][$r['id']] = array($r['name'], $r['show']);
			}
		}
	}

	public function __ava__getTemplatesByTechName($type, $techName){
		/*
			Выдает шаблоны по типу и техническому наименованию
		*/

		$this->loadTemplates();
		$return = array();

		foreach($this->allTemplates[$type] as $i => $e){
			if($this->templateParams[$type][$i]['tech_name'] == $techName) $return[$i] = $e;
		}

		return $return;
	}

	public function __ava__getTemplateParam($param, $tType = false, $tName = false){
		/*
			Возвращает определенный параметр в настройках шаблона
		*/

		$this->loadTemplates();
		if($tType === false) $tType = $this->templType;
		if($tName === false) $tName = $this->templName;
		return $this->templateParams[$tType][$tName][$param];
	}

	public function __ava__getModuleTemplateName($module){
		/*
			Возвращает имя шаблона для модуля
		*/

		$this->loadTemplates();
		if(empty($this->templateParams[$this->templType][$this->templName]['vars']['dependTemplates']['depend_tmp_'.$module])){
			return '';
			throw new AVA_Templ_Exception('{Call:Lang:core:core:neustanovlen:'.Library::serialize(array($module)).'}');
		}
		return $this->templateParams[$this->templType][$this->templName]['vars']['dependTemplates']['depend_tmp_'.$module];
	}

	public function __ava__getAllTemplates($type, $showOnly = false, $byName = false){
		/*
			Возвращает все шаблоны указанного типа
		 */

		$this->loadTemplates();
		if(!($templates = $byName ? (empty($this->allTemplates[$type]) ? false : $this->allTemplates[$type]) : (empty($this->allTemplatesById[$type]) ? false : $this->allTemplatesById[$type]))) return array();
		$return = array();

		foreach($templates as $i => $e){
			if($showOnly && !$e[1]) continue;
			$return[$i] = $e[0];
		}

		return $return;
	}

	public function __ava__getAllModuleTemplates($showOnly = false){
		/*
			Возвращает список всех шаблонов модуля установленных в системе
		*/

		if(empty($this->allModuleTemplates)){
			$this->loadTemplates();
			$this->loadModulesList();
			$dependModuleTmps = array();
			$depModNames = array();

			foreach($this->moduleParams as $i => $e){
				$names[$i] = array('text' => $e['text'], 'name' => $e['name']);
			}

			$names['core'] = array('text' => 'Core', 'name' => 'core');
			$names['main'] = array('text' => 'Main', 'name' => 'main');

			foreach($names as $i => $e){
				$dependModuleTmps[$i] = $this->getAllTemplates('modules/'.$e['name'], $showOnly, true);
				$depModNames[$i] = $e['text'];
			}

			$this->allModuleTemplates = array('depModNames' => $depModNames, 'dependModuleTmps' => $dependModuleTmps);
		}

		return $this->allModuleTemplates;
	}

	public function __ava__getTemplateBlocks($tmpl = false, $page = false){
		/*
			Возвращает все блоки шаблона
		*/

		if($tmpl === false) $tmpl = $this->getTemplateName('main');
		if($page === false) $page = $this->getTempl();

		if(empty($this->templateBlocks[$tmpl][$page])){
			$this->templateBlocks[$tmpl][$page] = $this->DB->columnFetch(array('template_blocks', 'body', 'name', "`template`='$tmpl' AND `show`"));
		}

		return $this->templateBlocks[$tmpl][$page];
	}


	/************************************************************************************************************************************************************************

																		Работа со страницами шаблона

	*************************************************************************************************************************************************************************/

	public function __ava__getTemplatePage($tmplPg = false, $tmpl = false, $tmplType = false){
		/*
			Возвращает содержимое страницы шаблона
		*/

		return $this->templatePages[$this->readTmplAndGetFile($tmplPg, $tmpl, $tmplType)];
	}

	public function __ava__getTemplatePageBlock($tmplPg, $bName, $bType = 'entry', $bParams = array(), $tmpl = false, $tmplType = false){
		/*
			Возвращает блок в шаблоне blocks
			Возвращается:
				1. Шаблон наиболее подходящий к $bParams
				2. Основной (без параметров) шаблон
				3. Первый в списке
		*/

		$pg = $this->getTemplatePage($tmplPg, $tmpl, $tmplType);
		return $pg[$bType][$bName][$this->getBlockFromCollect($pg[$bType][$bName], $bParams)]['content'];
	}

	public function __ava__getBlockFromCollect($tmps, $bParams = array()){
		/*
			Возвращает блок из коллекции шабов
		*/

		$searched = 0;
		foreach($tmps as $i => $e){
			if(!$e) $searched = $i;
			elseif($bParams == $e['params']){
				$searched = $i;
				break;
			}
			else{
				foreach($bParams as $i1 => $e1){
					if(!isset($e['params'][$i1]) || $e['params'][$i1] != $e1) break;
				}
			}
		}

		return $searched;
	}

	public function __ava__getTemplatePageBlocksList($tmplPg, $bType = 'entry', $tmpl = false, $tmplType = false){
		/*
			Список блоков входящих в страницу
		*/

		$pg = $this->getTemplatePage($tmplPg, $tmpl, $tmplType);
		return $pg[$bType];
	}

	public function __ava__getTemplatePageBlockName($tmplPg, $bName, $bType = 'entry', $tmpl = false, $tmplType = false){
		/*
			Название блока по идентификатору
		*/

		return $this->templatePageBlockNames[$this->readTmplAndGetFile($tmplPg, $tmpl, $tmplType)][$bType][$bName];
	}

	public function __ava__getTemplatePageBlockNameByTmpl($tmplPg, $bName, $bType = 'entry', $entTmpl = '', $tmpl = false, $tmplType = false){
		/*
			Название блока по идентификатору
		*/

		return $this->templatePageBlockNamesByTmpl[$this->readTmplAndGetFile($tmplPg, $tmpl, $tmplType)][$bType][$bName][$entTmpl];
	}

	public function __ava__getTemplatePageBlockNamesList($tmplPg, $bType = 'entry', $tmpl = false, $tmplType = false){
		/*
			Блоки шаблона по именам
		*/

		return $this->templatePageBlockNames[$this->readTmplAndGetFile($tmplPg, $tmpl, $tmplType)][$bType];
	}

	public function __ava__getTemplatePageBlockNamesByTmplList($tmplPg, $bName, $bType = 'entry', $tmpl = false, $tmplType = false){
		/*
			Блоки шаблона по именам по шаблону
		*/

		return $this->templatePageBlockNamesByTmpl[$this->readTmplAndGetFile($tmplPg, $tmpl, $tmplType)][$bType][$bName];
	}

	private function readTmplAndGetFile($tmplPg = false, $tmpl = false, $tmplType = false){
		$file = $this->getTemplateFileName($tmplPg, $tmpl, $tmplType);
		if(!isset($this->templatePageBlockNames[$file])){
			$this->readTemplateFile($file);
		}

		return $file;
	}

	private function getTemplateFileName($tmplPg = false, $tmpl = false, $tmplType = false){
		/*
			Имя файла шаблона
		*/

		if($tmplPg === false) $tmplPg = $this->getTempl();
		if(regExp::match("/^(\/|\.)/", $tmplPg, true)) $file = $tmplPg;
		else{
			if(!regExp::Match("|\.tmpl$|", $tmplPg, true)) $tmplPg = $tmplPg.'.tmpl';
			$file = $this->getTemplatePath($tmplType, $tmpl).$tmplPg;
		}
		return $file;
	}

	private function readTemplateFile($file){
		/*
			Считывает страницу шаблона
		*/

		$this->templatePages[$file] = Files::read($file);

		if(regExp::Match("/^\s*<template/iUs", $this->templatePages[$file], true, true)){
			$m = regExp::matchAll("/<item(.+)>(.*)<\/item>/iUs", $this->templatePages[$file]);
			$this->templatePages[$file] = array();

			foreach($m['2'] as $i => $e){
				$attr = $params = XML::parseXMLAttr($m['1'][$i]);
				if(!isset($attr['name']) || !isset($attr['type'])) throw new AVA_Templ_Exception('{Call:Lang:core:core:nenajdenatri:'.Library::serialize(array($file)).'}');
				unset($params['type'], $params['name'], $params['descript']);
				kSort($params);

				$this->templatePages[$file][$attr['type']][$attr['name']][] = array('content' => $e, 'params' => $params);
				if(!$params || empty($this->templatePageBlockNames[$file][$attr['type']][$attr['name']])) $this->templatePageBlockNames[$file][$attr['type']][$attr['name']] = $attr['descript'];
				$this->templatePageBlockNamesByTmpl[$file][$attr['type']][$attr['name']][isset($attr['template']) ? $attr['template'] : ''] = $attr['descript'];
			}
		}
	}


	/********************************************************************************************************************************************************************

																Доп. функции обслуживания шаблонов

	*********************************************************************************************************************************************************************/

	public function getTemplateXml($params){
		return XML::getXML(
			array(
				'descript' => array(
					'installator' => $params['type'].$params['folder'],
					'type' => 'templates',
					'name' => $params['folder'],
					'tmplType' => $params['type'],
					'text' => $params['name'],
					'version' => empty($params['version']) ? '0.0.0.0' : $params['version'],
				),
			)
		);
	}

	public function getInstallFileText($installator){
		return "<"."? class installTemplates{$installator} extends InstallTemplateObject implements InstallTemplateInterface{\n\t".
			"public function Install(){ return true; }\n\t".
			"public function prepareInstall(){ return true; }\n\t".
			"public function checkInstall(){ return true; }\n\t".
			"public function Uninstall(){ return true; }\n\t".
			"public function checkUninstall(){ return true; }\n\t".
			"public function Update(".'$oldVersion, $newVersion'."){ return true; }\n\t".
			"public function checkUpdate(".'$oldVersion, $newVersion'."){ return true; }\n".
			"} ?".">";
	}

	public function createTemplate($obj, $values = false){
		if($values === false) $values = $obj->values;
		if($values['type'] == 'module') $type = 'modules/'.$values['module'];
		else $type = $values['type'];

		$folder = TMPL.$type.'/';
		$tmplFolder = $folder.$values['folder'];
		$tmplSrc = $this->getParam('templateSource');
		$obj->isUniq('templates', array('name' => '{Call:Lang:core:core:takoeimiauzh}', 'folder' => '{Call:Lang:core:core:shablondliau:'.Library::serialize(array($values['folder'])).'}'), false, " AND `type`='{$type}'");

		if($tmplSrc == 'folder'){
			if(file_exists($tmplFolder)) $obj->setError('folder', '{Call:Lang:core:core:papkauzhesus:'.Library::serialize(array($tmplFolder)).'}');
			if(!$this->ftpCheck($folder)) $obj->setError('folder', '{Call:Lang:core:core:nevozmozhnop:'.Library::serialize(array($folder)).'}');
		}

		if(!$obj->check()) return false;

		if($tmplSrc == 'folder' && !$this->ftpMk($tmplFolder)) return false;
		return $this->DB->Ins(
			array(
				'templates',
				array(
					'name' => $values['name'],
					'folder' => $values['folder'],
					'type' => $type,
					'language' => isset($values['language']) ? $values['language'] : '',
					'tech_name' => $values['folder'],
					'version' => '0.0.0.0',
					'sort' => $values['sort'],
					'show' => isset($values['show']) ? $values['show'] : ''
				)
			)
		);
	}

	public function readTemplateXML($type, $folder){
		$XML = XML::parseXML(Files::read($this->getTemplatePath($type, $folder).'descript.xml'));
		if(empty($XML['descript']['pages'])) $XML['descript']['pages'] = array();
		elseif(Library::isHash($XML['descript']['pages'])) $XML['descript']['pages'] = array($XML['descript']['pages']);
		return $XML;
	}

	public function getTemplatePagesByXML($type, $folder){
		$return = array();
		$XML = $this->readTemplateXML($type, $folder);

		foreach($XML['descript']['pages'] as $i => $e){
			$return[$e['url']] = $e;
			$return[$e['url']]['template_type'] = $type;
			$return[$e['url']]['template'] = $folder;
			$return[$e['url']]['id'] = $e['url'];
		}

		return $return;
	}

	public function getTemplatePageByXML($type, $folder, $page){
		$pages = $this->getTemplatePagesByXML($type, $folder);
		return $pages[$page];
	}

	public function getTemplatePageData($type, $folder, $page, $params = array()){
		if($this->getParam('templateSource') == 'folder'){
			$data = $this->getTemplatePageByXML($type, $folder, $page);
			if($params == '*' || in_array('body', $params)) $data['body'] = Files::read($this->getTemplatePath($type, $folder).$page);
		}
		elseif($this->getParam('templateSource') == 'db'){
			$data = $this->DB->rowFetch(array('template_pages', $params, "`id`='".db_main::Quot($page)."'"));
		}
		else throw new AVA_Exception('{Call:Lang:core:core:neopredeleno1}');

		return $data;
	}



	/************************************************************************************************************************************************************************

																		Обработка страниц шаблона

	*************************************************************************************************************************************************************************/

	public function __ava__runTemplateGenerator($isFinal = true){
		/*
			Запускает генератор шаблонов.
		*/

		if($v = $this->getExclusiveVar()){
			$this->tmplText = $this->replace($this->mainModObj->getContentVar($v), $this->getMainModObj(), array());
		}
		elseif($this->getFlag('rawOutput')) $this->tmplText = $this->replace($this->mainModObj->getContentVar('body'), $this->getMainModObj(), array());
		else{
			if($this->getTemplateType() == 'main'){
				foreach($this->getTemplateBlocks() as $i => $e){
					$this->mainModObj->setContent($e, $i, 'pre');
				}
			}

			foreach($this->mainModObj->getContent() as $i => $e){
				$this->mainModObj->setNewContent($this->replace($this->mainModObj->getContentVar($i), $this->getMainModObj(), $this->mainModObj->getContent()), $i);
			}

			$this->tmplText = $this->replace($this->getTemplatePage(), $this->getMainModObj(), $this->mainModObj->getContent());
		}

		if($isFinal) $this->tmplText = $this->prepareFinalTmplText($this->tmplText);
	}

	public function prepareFinalTmplText($text){
		return regExp::Replace(array("[nocall]", "[/nocall]"), "", $text);
	}

	public function getOutput($toRights = false){
		//Возвращает нагенерированный основным шаблоном текст

		$this->runPlugins('content');
		$return = $this->tmplText;
		if($toRights) $return = $this->putToRights($return);

		$return = regExp::charset('utf-8', $this->getParam('charset'), $return);
		$this->setDebugInterval('Время работы скрипта до окончания работы шаблонизатора', 0);
		return $return;
	}

	public function __ava__putToRights($str){
		/*
			Приводит текст основного шаблона (общий сайт) в сответствие с требованиями:
				- Преобразовывает ссылки в SEF-образные
		*/

		if(SHOW_HWT >= 1) $debId = $this->debugStart('{Call:Lang:core:core:rabotapopreo}');
		$str = regExp::Replace("#(href|src|action)=(['".'"'."])/?(index\.php)?\?#", '$1=$2'._D.'index.php?', $str, true);

		if($this->getParam('useSef') && !$this->getFlag('rawOutput')){
			$links = array();
			foreach($this->getSites() as $i => $e){
				foreach(regExp::matchAll("#(href|src|action)=.?(".regExp::quot($this->siteParams[$i]['url'])."index\.php[^'".'"'."\s>]+)#", $str, false, 0, $c, 2) as $i => $e) $links[] = $e;
			}

			foreach(Library::sortLen(array_unique($links)) as $i => $e){
				$repl = $this->getReplaceLink($e);
				if($repl != $e) $str = regExp::Replace($e, $repl, $str);
			}
		}

		if(SHOW_HWT >= 1) $this->debugEnd($debId);
		return $str;
	}

	public function __ava__readAndReplace($tmplPg, $parent, $replaces = array(), $tmpl = false, $tmplType = false){
		/*
			Считывает страницу и преобразовывает содержимое
		*/

		return $this->replace($this->getTemplatePage($tmplPg, $tmpl, $tmplType), $parent, $replaces);
	}

	public function __ava__readBlockAndReplace($tmplPg, $bName, $parent, $replaces = array(), $bType = 'entry', $bParams = array(), $tmpl = false, $tmplType = false){
		/*
			Считывает страницу и преобразовывает содержимое блока
		*/

		return $this->replace($this->getTemplatePageBlock($tmplPg, $bName, $bType, $bParams, $tmpl, $tmplType), $parent, $replaces);
	}

	protected function __ava__paramReplaces($params, $obj){
		/*
			Реплейс параметра
		*/

		if(!is_array($params)){
			if(regExp::Match("/^\{[A-z_][^<\s\{\}\,\;'".'"'."]+\}$/iUs", $params, true)) return $this->replace($params, $obj, array(), 0);
			else return $params;
		}
		else{
			foreach($params as $i => $e) $params[$i] = $this->paramReplaces($e, $obj);
			return $params;
		}
	}

	public function __ava__replace($text, $parent, $replaces = array(), $continue = true){
		/*
			Преобразование записей в шаблоне.
		*/

		if(SHOW_TMPL_DEBUG_DATA > 0) $dId = $GLOBALS['Core']->setDebugAction('Передан в шаблонизатор шаблон '.$text);
		if(!($parent instanceof moduleInterface)) throw new AVA_Templ_Exception('{Call:Lang:core:core:vshablonizat}');

		if($replaces['CURRENT_MOD'] = $parent->getMod()){
			$replaces['CURRENT_FUNC'] = $parent->getFunc();
			$replaces['CURRENT_PARENT_OBJ'] = $parent;

			if(!Library::constVal('IN_INSTALLATOR')){
				$replaces['CURRENT_TMPL_URL'] = $this->getTemplateUrl();
				$replaces['CURRENT_TMPL_MOD_URL'] = $this->getModuleTemplateUrl($replaces['CURRENT_MOD']);
				$replaces['CURRENT_TMPL_MAIN_URL'] = $this->getTemplateUrl($this->getTemplateType());
			}
		}

		if(Library::constVal('IN_ADMIN')){
			$replaces['URL'] = _D.ADMIN_FOLDER.'/';
			$replaces['PATH'] = _W.ADMIN_FOLDER.'/';
		}
		else{
			$replaces['URL'] = _D;
			$replaces['PATH'] = _W;
		}

		$replaces['TMPL_URL'] = $this->getTemplateUrl($this->getTemplateType());
		$replaces['TMPL_PATH'] = $this->getTemplatePath($this->getTemplateType());

		$repObj = new Template($text, $replaces);
		$return = $repObj->replace($continue);

		if(SHOW_TMPL_DEBUG_DATA > 0) $GLOBALS['Core']->setDebugInterval('Завершена обработка шаблона '.$text, $dId);
		return $return;
	}

	public function __ava__simpleReplace($text, $repl){
		$search = $replaces = array();
		foreach($repl as $i => $e){
			if(!is_array($e) && !is_object($e)){
				$search[] = '{'.$i.'}';
				$replaces[] = $e;
			}
		}

		return regExp::replace($search, $replaces, $text);
	}


	/********************************************************************************************************************************************************************

																			Шаблоны писем

	*********************************************************************************************************************************************************************/

	private function fetchMailTemplates(){
		/*
			Извлекает теги
		*/

		if($this->mailTemplates === false){
			$this->mailTemplates = $this->DB->columnFetch(array('mail_templates', array('id', 'mod', 'name', 'text', 'format'), 'name', "", "`sort`"));
			foreach($this->mailTemplates as $i => $e){
				$this->mailTemplatesById[$e['id']] = $e;
				$this->mailTemplateNames[$i] = $e['text'];
			}
		}
	}

	public function __ava__getMailTemplate($name = false){
		/*
			Возвращает список тегов
		*/

		$this->fetchMailTemplates();
		return $name ? $this->mailTemplates[$name] : $this->mailTemplates;
	}

	public function __ava__getMailTemplateById($id = false){
		/*
			Возвращает список тегов
		*/

		$this->fetchMailTemplates();
		return $id ? $this->mailTemplatesById[$id] : $this->mailTemplatesById;
	}

	public function __ava__getMailTemplateNames(){
		/*
			Возвращает список тегов
		*/

		$this->fetchMailTemplates();
		return $this->mailTemplateNames;
	}

	public function __ava__getMailTemplateName($name){
		/*
			Возвращает список тегов
		*/

		$this->fetchMailTemplates();
		return $this->mailTemplateNames[$name];
	}


	/************************************************************************************************************************************************************************

																			Кеширование

	*************************************************************************************************************************************************************************/

	public function setCache($str, $id){
		$this->cache[$id] = $str;
	}

	public function getCache($id){
		return isset($this->cache[$id]) ? $this->cache[$id] : false;
	}


	/************************************************************************************************************************************************************************

																	Кеширование данных в БД

	*************************************************************************************************************************************************************************/

	public function setDataCache($mod, $name, $value){
		$this->DB->Ins(array('datacache', array('mod' => $mod, 'name' => $name, 'value' => $value), "extra" => array('onDuplicate' => array('value' => $value))));
	}

	public function getDataCache($mod, $name, $force = false){
		if(!isset($this->dataCache[$mod][$name]) || $force) $this->dataCache[$mod][$name] = $this->DB->cellFetch(array('datacache', 'value', "`mod`='$mod' AND `name`='$name'"));
		return $this->dataCache[$mod][$name];
	}


	/************************************************************************************************************************************************************************

																			Работа с FTP

	*************************************************************************************************************************************************************************/

	public function __ava__ftpCheck($folder){
		/*
			Проверяет возможность копирования в папку, причем как по FTP, так и старым дедовским способом
		*/

		if($this->getFtpClient()) return true;
		else return Files::isWritable($folder);
	}

	public function __ava__ftpCopy($source, $destination){
		/*
			Пытается копировать папку либо по ftp либо, если это невозможно, обычным дедовским способом
		*/

		$ftp = $this->getFtpClient();
		if(is_object($ftp)){
			if($ftp->Copy($source, regExp::replace(_W, '', $destination))){
				return true;
			}
		}

		if(Files::isWritable($destination)){
			if(is_dir($source)){ if(Files::cpFolder($source, $destination)) return true; }
			else{ if(Files::cp($source, $destination)) return true; }
		}

		return false;
	}

	public function __ava__ftpSave($file, $text){
		/*
			Сохраняет текст в файл пытаясь использовать FTP либо просто так если FTP не работает
		*/

		$ftp = $this->getFtpClient();
		if(is_object($ftp)){
			$saved = TMP.basename($file);
			if(Files::write($saved, $text) && $ftp->Copy($saved, regExp::replace(_W, '', $file))){
				return true;
			}
		}

		if(Files::isWritable($file) && Files::write($file, $text)) return true;
		return false;
	}

	public function __ava__ftpRename($src, $dst){
		/*
			Переименовывает файл пытаясь использовать FTP либо просто так если FTP не работает
		*/

		$ftp = $this->getFtpClient();
		if(is_object($ftp)){
			if($ftp->Rename(regExp::replace(_W, '', $src), regExp::replace(_W, '', $dst))){
				return true;
			}
		}

		if(Files::mv($src, $dst)){
			return true;
		}

		return false;
	}

	public function __ava__ftpRm($folder){
		/*
			Пытается удалить папку либо по ftp либо, если это невозможно, обычным дедовским способом
		*/

		$ftp = $this->getFtpClient();
		if(is_object($ftp)){
			if($ftp->rm(regExp::replace(_W, '', $folder))){
				return true;
			}
		}

		if(Files::rmFolder($folder)){
			return true;
		}

		return false;
	}

	public function __ava__ftpMk($folder){
		/*
			Создает папку по FTP или по-дедовски
		*/

		$ftp = $this->getFtpClient();
		if(is_object($ftp)){
			if($ftp->mk(regExp::replace(_W, '', $folder))){
				return true;
			}
		}

		if(Files::mkDir($folder)){
			return true;
		}

		return false;
	}

	public function __ava__moveFileToFolder($src, $folder, $fld, $errObj, $file = false){
		/*
			Перемещает файлы в обозначенную директорию и проводит над ними все предусмотренные процедуры
		*/

		$fData = $this->getFolderDataByPath($folder);
		if($file === false) $file = basename($src);
		else $file .= Files::getExtension($src);

		if(file_exists(_W.$folder.$file) && ($errObj->values[$fld.'_hidden'] != $file)){
			$errObj->setError($fld, 'Файл "'._W.$folder.$file.'" уже есть на сервере');
			return false;
		}

		if(!$this->ftpCopy($src, _W.$folder.$file)){
			$errObj->setError($fld, '{Call:Lang:core:core:problemaskop}');
			return false;
		}

		$ex = regExp::lower(Files::getExtension($file));
		if($ex == '.gif' || $ex == '.jpg' || $ex == '.jpeg' || $ex == '.png' || $ex == '.bmp'){
			if($fData){
				$standarts = $fData['standarts'];
				if($fData['main_standart']) $standarts[$fData['main_standart']] = 1;
				$msObj = false;

				foreach($standarts as $i => $e){
					$stdData = $this->getImageStandartParams($i);
					if(!$stdData['show']) continue;

					$img = new Image;
					$img->createImage(_W.$folder.$file);

					if($stdData['rotate'] != 0 && $stdData['rotate_moment'] == '1') $this->rotateImg($img, $i);
					$this->setWM($img, $stdData['watermarks'], '1');
					if($stdData['rotate'] != 0 && $stdData['rotate_moment'] == '2') $this->rotateImg($img, $i);

					if($stdData['width'] && !$stdData['height']) $img->resizeImageWidth($stdData['width'], 0, 0, $stdData['enlarge']);
					elseif(!$stdData['width'] && $stdData['height']) $img->resizeImageHeight($stdData['height'], 0, 0, $stdData['enlarge']);
					elseif($stdData['width'] && $stdData['height']){
						if(!$stdData['resize_style']) $img->resizeImageWhichMore($stdData['width'], $stdData['height'], 0, 0, $stdData['enlarge']);
						else{
							$h = 'l';
							$v = 't';

							if($stdData['resize_style'] == 4 || $stdData['resize_style'] == 5 || $stdData['resize_style'] == 6) $h = 'c';
							elseif($stdData['resize_style'] == 7 || $stdData['resize_style'] == 8 || $stdData['resize_style'] == 9) $h = 'r';

							if($stdData['resize_style'] == 2 || $stdData['resize_style'] == 5 || $stdData['resize_style'] == 8) $v = 'c';
							elseif($stdData['resize_style'] == 3 || $stdData['resize_style'] == 6 || $stdData['resize_style'] == 9) $v = 'b';

							$img->resizeAndSliceImage($stdData['width'], $stdData['height'], $h, $v, $stdData['enlarge']);
						}
					}

					if($stdData['rotate'] != 0 && $stdData['rotate_moment'] == '3') $this->rotateImg($img, $i);
					$this->setWM($img, $stdData['watermarks']);
					if($stdData['rotate'] != 0 && $stdData['rotate_moment'] == '4') $this->rotateImg($img, $i);

					if(!empty($fData['standarts'][$i])) $img->flushImage(_W.$folder.$stdData['name'].'/'.$file, $stdData['quality']);
					if($fData['main_standart'] == $i) $msObj = $img;
				}

				if($msObj){
					$stdData = $this->getImageStandartParams($fData['main_standart']);
					$msObj->flushImage(_W.$folder.$file, $stdData['quality']);
				}
			}

			$img = new Image;
			$img->createImage(_W.$folder.$file);
			if($img->resizeImageWhichMore($this->getParam('thumbWh'), $this->getParam('thumbHt'))) $img->flushImage(_W.$folder.'.thumbs/'.$file, $this->getParam('thumbQuality'));
		}

		return $file;
	}

	private function rotateImg($img, $std){
		/*
			Поворачивает изображение
		*/

		$stdData = $this->getImageStandartParams($std);
		$img->rotate($stdData['rotate'], $stdData['rotate_color'], $stdData['rotate_color_transparent']);
	}

	private function setWM($img, $wmList, $pos = ''){
		/*
			Налагает водяные знаки
		*/

		foreach($wmList as $i1 => $e1){
			$wmData = $this->getWatermarkParams($i1);
			if($wmData['show'] && ($wmData['moment'] == $pos)){
				if($wmData['type'] == 'text'){
					$img->write(
						$wmData['content'],
						new Font($wmData['font_size'], $wmData['color'], $wmData['font']),
						$wmData['hpos'],
						$wmData['vpos'],
						$wmData['hcorner'],
						$wmData['vcorner'],
						$wmData['transparency'],
						$wmData['corner']
					);
				}
				elseif($wmData['type'] == 'image'){
					$img->innerImageByFile(
						_W.$this->getParam('watermarksFolder').$wmData['file'],
						$wmData['hpos'],
						$wmData['vpos'],
						$wmData['hcorner'],
						$wmData['vcorner'],
						$wmData['transparency'],
						$wmData['corner']
					);
				}
			}
		}
	}

	public function extract2tmpArc($arc){
		/*
			Инсталляция во временный архив
		*/

		$tmpFolder = Files::getEmptyFolder(TMP.basename($arc)).'/';
		Files::mkDir($tmpFolder);
		if(Arc::extract($arc, $tmpFolder)) return $tmpFolder;
		return false;
	}

	public function __ava__getFtpClient($host = false, $user = false, $pwd = false, $port = false, $path = false){
		/*
			Возвращает объект FTP-клиента
		*/

		if($host === false) $host = $this->getParam('ftpHost');
		if($user === false) $user = $this->getParam('ftpUser');
		if($pwd === false) $pwd = $this->getParam('ftpPwd');

		if($port === false) $port = $this->getParam('ftpPort');
		if($path === false) $path = $this->getParam('ftpFolder');
		if($path === '') $path = '.';

		if($host){
			if(empty($this->ftpConnections[$host][$user][$port])){
				$this->ftpConnections[$host][$user][$port] = new ftpClient($host, $user, $pwd, $port);
				if(!$this->ftpConnections[$host][$user][$port]->connect()) return false;
			}

			if(!$this->ftpConnections[$host][$user][$port]->setFolder($path)) return false;
			return $this->ftpConnections[$host][$user][$port];
		}
		else return false;
	}


	/************************************************************************************************************************************************************************

																		Работа с папками

	*************************************************************************************************************************************************************************/

	private function fetchFolderData(){
		if(empty($this->folders)){
			$this->folders = $this->DB->columnFetch(array('folders', '*', "id", "", "`sort`"));

			foreach($this->folders as $i => $e){
				$this->folders[$i]['standarts'] = library::str2arrKeys($this->folders[$i]['standarts']);
				$this->folders[$i]['modules'] = library::str2arrKeys($this->folders[$i]['modules']);
				$this->folderPaths[$e['path']] = $this->folders[$i];

				$name = $e['name'].' ('.$e['path'].')';
				$this->folderNames[$i] = $name;
				$this->folderNamesByPath[$e['path']] = $name;

				foreach($this->folders[$i]['modules'] as $i1 => $e1){
					$this->folderMods[$i1][$i] = $this->folders[$i];
					$this->folderNamesByMod[$i1][$i] = $name;
					$this->folderNamesByPathByMod[$i1][$e['path']] = $name;
				}
			}
		}
	}

	public function __ava__getFoldersList($mod = false){
		$this->fetchFolderData();
		return $mod ? (isset($this->folderNamesByMod[$mod]) ? $this->folderNamesByMod[$mod] : array()) : $this->folderNames;
	}

	public function __ava__getFoldersListByPath($mod = false){
		$this->fetchFolderData();
		return $mod ? (isset($this->folderNamesByPathByMod[$mod]) ? $this->folderNamesByPathByMod[$mod] : array()) : $this->folderNamesByPath;
	}

	public function __ava__getFolders($mod = false){
		$this->fetchFolderData();
		return $mod ? (isset($this->folderMods[$mod]) ? $this->folderMods[$mod] : array()) : $this->folders;
	}

	public function __ava__getFoldersByPath($mod = false){
		$this->fetchFolderData();
		return $mod ? (isset($this->folderMods[$mod]) ? $this->folderMods[$mod] : array()) : $this->folders;
	}

	public function __ava__getFolderData($id){
		$this->fetchFolderData();
		return $this->folders[$id];
	}

	public function __ava__getFolderDataByPath($path){
		$this->fetchFolderData();
		return empty($this->folderPaths[$path]) ? false : $this->folderPaths[$path];
	}

	private function fetchFonts(){
		if(!$this->fonts){
			$this->fonts = $this->DB->columnFetch(array('fonts', '*', 'id', '', "`sort`"));
			foreach($this->fonts as $i => $e){
				$this->fontsByFile[$e['file']] = $e['name'];
			}
		}
	}

	public function __ava__getFontsByFile(){
		$this->fetchFonts();
		return $this->fontsByFile;
	}

	public function __ava__getFontByFile($file){
		$this->fetchFonts();
		return $this->fontsByFile[$file];
	}

	private function fetchCaptchaBackgrounds(){
		if(!$this->captchaBackgrounds){
			foreach(Files::readFolderFiles(_W.$this->getParam('captchaFolder')) as $i => $e){
				$this->captchaBackgrounds[$e] = $e;
			}
		}
	}

	public function __ava__getCaptchaBackgrounds(){
		$this->fetchCaptchaBackgrounds();
		return $this->captchaBackgrounds;
	}

	private function fetchCaptchaStandarts(){
		if(!$this->captchaStandarts){
			$this->captchaStandartParams = $this->DB->columnFetch(array('captcha_standarts', '*', 'name', '', '`sort`'));

			foreach($this->captchaStandartParams as $i => $e){
				$this->captchaStandartParams[$i]['backgrounds'] = explode(',', $this->captchaStandartParams[$i]['backgrounds']);
				$this->captchaStandartParams[$i]['fonts'] = explode(',', $this->captchaStandartParams[$i]['fonts']);
				$this->captchaStandartParams[$i]['math_actions'] = explode(',', $this->captchaStandartParams[$i]['math_actions']);

				unset($this->captchaStandartParams[$i]['backgrounds'][0], $this->captchaStandartParams[$i]['backgrounds'][count($this->captchaStandartParams[$i]['backgrounds'])]);
				unset($this->captchaStandartParams[$i]['fonts'][0], $this->captchaStandartParams[$i]['fonts'][count($this->captchaStandartParams[$i]['fonts'])]);
				unset($this->captchaStandartParams[$i]['math_actions'][0], $this->captchaStandartParams[$i]['math_actions'][count($this->captchaStandartParams[$i]['math_actions'])]);

				$this->captchaStandartParamsById[$e['id']] = $this->captchaStandartParams[$i];
				$this->captchaStandarts[$i] = $e['text'];
				if($e['show']) $this->captchaStandartsOpen[$i] = $e['text'];
			}
		}
	}

	public function __ava__getCaptchaStandarts($showOnly = true){
		$this->fetchCaptchaStandarts();
		return $showOnly ? $this->captchaStandartsOpen : $this->captchaStandarts;
	}

	public function __ava__getCaptchaParams($name){
		$this->fetchCaptchaStandarts();
		return $this->captchaStandartParams[$name];
	}

	public function __ava__getCaptchaParamsById($id){
		$this->fetchCaptchaStandarts();
		return $this->captchaStandartParamsById[$id];
	}

	private function fetchWatermarks(){
		if(!$this->watermarkParams){
			$this->watermarkParams = $this->DB->ColumnFetch(array('watermarks', '*', 'name', '', 'sort'));

			foreach($this->watermarkParams as $i => $e){
				$this->watermarks[$i] = $e['text'];
			}
		}
	}

	public function __ava__getWatermarks(){
		$this->fetchWatermarks();
		return $this->watermarks;
	}

	public function __ava__getWatermarkParams($name){
		$this->fetchWatermarks();
		return $this->watermarkParams[$name];
	}

	private function fetchImageStandarts(){
		if(!$this->imageStandartParams){
			$this->imageStandartParams = $this->DB->columnFetch(array('image_standarts', '*', "name", "", "`sort`"));
			foreach($this->imageStandartParams as $i => $e){
				$this->imageStandartParams[$i]['watermarks'] = Library::str2arrKeys($this->imageStandartParams[$i]['watermarks']);
				$this->imageStandarts[$e['name']] = $e['text'];
			}
		}
	}

	public function __ava__getImageStandarts(){
		$this->fetchImageStandarts();
		return $this->imageStandarts;
	}

	public function __ava__getImageStandartName($name){
		$this->fetchImageStandarts();
		return $this->imageStandarts[$name];
	}

	public function __ava__getImageStandartParams($name){
		$this->fetchImageStandarts();

		if(!$this->imageStandartParams[$name]) throw new AVA_Exception('qqqq');
		return $this->imageStandartParams[$name];
	}


	/************************************************************************************************************************************************************************

																				Версия

	*************************************************************************************************************************************************************************/

	public function getVersion(){
		if(!$this->version) $this->version = $this->DB->cellFetch(array('version', 'version', "`name`='core'"));
		return $this->version;
	}


	/************************************************************************************************************************************************************************

																	Обработка ссылок согласно стандартам

	*************************************************************************************************************************************************************************/

	public function __ava__getReplaceLink($link){
		/*
			Преобразовывает ссылку согласно стандартам
		*/

		$link = regExp::Replace('&amp;', '&', $link);
		if(!($useSef = $this->getParam('useSef'))) return $link;

		if(!$this->urlRewriteParams){
			foreach($this->DB->columnFetch(array('settings', array('site', 'name', 'value'))) as $i => $e){
				$this->urlRewriteParams[$e['site']][$e['name']] = $e['value'];
			}
		}

		foreach($this->getSites() as $i => $e){
			if(regExp::Match($this->siteParams[$i]['url'], $link)){
				$lp = parse_url($link);
				if(empty($this->inversePattern[$i])) $this->inversePattern[$i] = $this->getInversePattern($this->urlRewriteParams[$i]['sefUrlPattern']);
				if(empty($this->inverseVarPattern[$i])) $this->inverseVarPattern[$i] = $this->getInversePattern($this->urlRewriteParams[$i]['sefUrlVarPattern']);

				$vars = Library::parseStrOneLevel($lp['query']);
				$mod = isset($vars['mod']) ? $vars['mod'] : '';
				$func = isset($vars['func']) ? $vars['func'] : '';
				unset($vars['mod'], $vars['func']);

				if($urParams = $this->getUrlRightsByMod($mod, $func, 'dropvars')){
					$varsPath = array();

					foreach($vars as $i1 => $e1){
						if(Library::inArray($i1, $urParams['vars']['dropVarsList'], $k)){
							$varsPath[$k] = library::encodeUrl($e1);
							unset($vars[$i1]);
						}
					}

					krsort($varsPath);
					$lk = Library::firstKey($varsPath);
					for($j = 0; $j <= $lk; $j ++) if(!isset($varsPath[$j]) || $varsPath[$j] == '') $varsPath[$j] = $urParams['vars']['dropVarsEmpty'];

					$varsPath = implode($urParams['vars']['dropVarsDlm'], $varsPath).
						(!empty($urParams['vars']['dropVarsLastDlm']) ? $urParams['vars']['dropVarsDlm'] : '').
						(!empty($vars) ? $urParams['vars']['dropVarsDlm2'] : '');
				}
				else $varsPath = '';

				foreach($vars as $i1 => $e1){
					if($i1 !== '' && $e1 !== ''){
						$e1 = library::encodeUrl($e1);
						$var = $this->replaceLinkBlock($this->urlRewriteParams[$i]['sefUrlVarPattern'], $this->inverseVarPattern[$i]['1'], $i1);
						$var = $this->replaceLinkBlock($var, $this->inverseVarPattern[$i]['2'], $e1);
						$varsPath .= $var;
					}
				}

				$path = $this->replaceLinkBlock($this->urlRewriteParams[$i]['sefUrlPattern'], $this->inversePattern[$i]['1'], $mod);
				$path = $this->replaceLinkBlock($path, $this->inversePattern[$i]['2'], $func);
				$path = $this->replaceLinkBlock($path, $this->inversePattern[$i]['3'], $varsPath);
				$lp['query'] = '';

				if($useSef != 'mod_rewrite'){
					if($useSef == 'append_path') $path = 'index.php/'.$path;
					elseif($useSef == 'append_query') $path = '?/'.$path;
					else throw new AVA_Exception('{Call:Lang:core:core:neopredelenn:'.Library::serialize(array($useSef)).'}');
				}

				return $this->siteParams[$i]['url'].$path.(empty($lp['query']) ? '' : '?'.$lp['query']).(empty($lp['fragment']) ? '' : '#'.$lp['fragment']);
			}
		}

		return $link;
	}

	private function replaceLinkBlock($link, $blockPat, $replace){
		/*
			Преобразовывает определенный участок ссылки
		*/

		return regExp::Replace($blockPat, regExp::replace(' '.$replace.' ', $replace, regExp::Replace('/^\((.*)\$\d{1}(.*)\)$/is', '$1 '.$replace.' $2', $blockPat, true)), $link);
	}

	private function getInversePattern($pat){
		/*
			Возвращает обратный паттерн.
			Позиции именуются в массиве как 1, 2, 3 для модуля, фунции и списка переменныхъ
		*/

		regExp::Match('/\([^\)]*\$1[^\)]*\)/is', $pat, true, true, $m1);
		regExp::Match('/\([^\)]*\$2[^\)]*\)/is', $pat, true, true, $m2);
		regExp::Match('/\([^\)]*\$3[^\)]*\)/is', $pat, true, true, $m3);

		$return = array(
			'1' => empty($m1['0']) ? '' : $m1['0'],
			'2' => empty($m2['0']) ? '' : $m2['0'],
			'3' => empty($m3['0']) ? '' : $m3['0']
		);

		return $return;
	}



	/************************************************************************************************************************************************************************

																	Подключение к базам данных

	*************************************************************************************************************************************************************************/

	public function __ava__getDB($dbId = false, $modPrefix = ''){
		/*
			Создает действующий объект DB
		*/

		$dbData = $this->getDBData($dbId, $modPrefix);
		$dbName = 'db_'.$dbData['driver'];

		if(!isset($this->databases[$dbName][$dbData['ident']][$modPrefix])){
			$this->databases[$dbName][$dbData['ident']][$modPrefix] = new $dbName(
				$dbData['host'],
				$dbData['user'],
				$dbData['pwd'],
				$dbData['vars'],
				false,
				Library::constVal('AVA_DB_PCONNECT')
			);

			$this->databases[$dbName][$dbData['ident']][$modPrefix]->setDB($dbData['name'], $dbData['prefix']);
		}

		return $this->databases[$dbName][$dbData['ident']][$modPrefix];
	}

	public function __ava__getDBData($dbId = false, $modPrefix = ''){
		/*
			Выдает данные для подключения к БД
		*/

		if($dbId) $this->loadDBData();

		if(!$dbId || !($dbData = $this->dbParams[$dbId])){
			$dbData = array(
				'ident' => '',
				'name' => AVA_DB_NAME,
				'host' => AVA_DB_HOST,
				'user' => AVA_DB_USER_ADMIN,
				'pwd' => AVA_DB_PWD_ADMIN,
				'prefix' => AVA_DB_PREF,
				'driver' => AVA_DB_DRIVER,
				'vars' => $GLOBALS['AVA_DB_PARAMS']
			);
		}
		elseif($dbData){
			$dbData['vars'] = Library::unserialize($dbData['vars']);
		}

		$dbData['pwd'] = Library::Decrypt($dbData['pwd']);
		$dbData['prefix'] = $dbData['prefix'].$modPrefix;

		return $dbData;
	}

	public function __ava__getDatabases($useDefault = false){
		/*
			Список всех баз данных для построения списка
		*/

		$this->loadDBData();
		$dbList = $useDefault ? array('' => '{Call:Lang:core:core:poumolchanii}') : array();
		foreach($this->dbParams as $i => $e){
			$dbList[$i] = '{Call:Lang:core:core:prefiks:'.Library::serialize(array($e['name'], $e['host'], $e['user'], $e['prefix'])).'}';
		}

		return $dbList;
	}

	public function loadDBData(){
		if(empty($this->dbParams)) $this->dbParams = $this->DB->columnFetch(array('databases', '*', 'ident'));
	}

	public function getDBDrivers(){
		return array('mysql' => 'MySQL');
	}

	public function __ava__getDBByMod($mod){
		/*
			Выдает объект БД по имени модуля
		*/

		if(!$params = $this->getModuleParams($mod)) return false;
		return $this->getDB($params['db'], $mod);
	}



	/************************************************************************************************************************************************************************

																		Вызовы модулей

	*************************************************************************************************************************************************************************/

	public function __ava__callModule($mod, $func = false, $params = array(), $inAdmin = -1){
		/*
			Вызывает модуль $mod метод $func с параметрами $params, присваивает ему имя в массиве модулей
			Проверяет что тот же модель с теми же параметрами еще не вызывался. Если вызывался, возвращает его, ничего не вызывая поновой
			Может также искать потомков данного класса, определенных в системе. Сделано это для того чтобы писать плагины
		*/

		$modParams = $this->getModuleParams($mod);
		if(!$modParams['show']) throw new AVA_Access_Exception('Модуль "'.$modParams['text'].'" в настоящий момент отключен');
		if($inAdmin < 0) $inAdmin = defined('IN_ADMIN') ? IN_ADMIN : 0;

		if($inAdmin) $modObj = 'mod_admin_'.$modParams['name'];
		else $modObj = 'mod_'.$modParams['name'];
		if(!empty($this->obj[$id = $this->getCallHash($mod, $func, $params)]) && is_object($this->obj[$id])) return $this->obj[$id];

		$dbPref = $mod;
		if($mod == 'core' || $mod == 'main') $dbPref = '';
		$db = $this->getDB($modParams['db'], $dbPref);

		if($extClass = $this->getExtClass($modObj)){
			//Если есть некоторый плагин для модуля который бы его дополнял возможностями
			$this->obj[$id] = new $extClass($mod, $modParams['name'], $db, $params);
		}
		else{
			$this->obj[$id] = new $modObj($mod, $modParams['name'], $db, $params);
		}

		if(!empty($func)) $this->obj[$id]->callFunc($func);
		return $this->obj[$id];
	}

	public function __ava__callAllModules($func, Moduleinterface $callBy, $params = array()){
		/*
			Обращается ко всем модулям имеющим указанную функцию
			Реализуется для связки API для подключения всех модулей к указанному
		*/

		$return = array();
		foreach($this->getModules() as $i => $e){
			if($i != $callBy->getMod()){
				$obj = $this->callModule($i);
				if(method_exists($obj, $func)) $return[$i] = $obj->$func($callBy, $params);
			}
		}

		return $return;
	}

	public function __ava__callMainModule($params = array(), $inAdmin = -1){
		/*
			Вызывает главный модуль с параметрами $callData
			Возвращает вызывнный объект
		*/

		if(is_object($this->mainModObj)) return $this->mainModObj;

		$jsConf = '<script type="text/javascript">'."\n".
			'_D = "'._D.'";'."\n".
			'TMPL_FOLDER = "'.TMPL_STYLE_FOLDER.'";'."\n".
			'MTMPL = "'.TMPL_STYLE_FOLDER.'main/'.$this->getTemplateName('main').'/";'."\n".
			'ATMPL = "'.TMPL_STYLE_FOLDER.'admin/'.$this->getTemplateName('admin').'/";'."\n".
		'</script>';

		if(!$params) $params = $this->callData;
		if(Library::constVal('IN_INSTALLATOR')){
			$this->mainModObj = new Install($this->mod, 'install', false, $params);
		}
		else{
			$modParams = $this->getModuleParams($this->mod);
			if(!$modParams['show']) throw new AVA_Access_Exception('Модуль "'.$modParams['text'].'" в настоящий момент отключен');

			if($inAdmin < 0) $inAdmin = defined('IN_ADMIN')? IN_ADMIN : 0;
			if($inAdmin) $mod = 'mod_admin_'.$modParams['name'];
			else $mod = 'mod_'.$modParams['name'];

			$dbPref = $this->mod;
			if($this->mod == 'core' || $this->mod == 'main') $dbPref = '';
			$db = $this->getDB($modParams['db'], $dbPref);

			if($extClass = $this->getExtClass($mod)){
				//Если есть некоторый плагин для модуля который бы его дополнял возможностями

				$this->mainModObj = new $extClass($this->mod, $modParams['name'], $db, $params);
			}
			else{
				$this->mainModObj = new $mod($this->mod, $modParams['name'], $db, $params);
			}
		}

		$this->mainModObj->setContent($jsConf, 'head');
		return $this->mainModObj->callFunc($this->func);
	}

	public function contentMod2Mod($obj1, $obj2, $new = true){
		/*
			Переносит контент из модуля 1 в 2
		*/

		foreach($obj1->getContent() as $i => $e){
			$new ? $obj2->setNewContent($e, $i) : $obj2->setContent($e, $i);
		}
	}

	public function __ava__callModAndGetFormId($mod, $func, $formName, $values = array()){
		/*
			Обращается к модулю и функции. Возвращает ID формы
		*/

		$mod = $this->callModule($mod);
		return $mod->callFuncAndGetFormId($func, $formName, $values);
	}

	public function getExtClass($class){
		//Возвращает самый верхний определенный в системе класс являющийся потомком заданного.

		$classes = get_declared_classes();
		foreach($classes as $i => $e){
			$parents = class_parents($e);
			if($e != $class && !empty( $parents[$class] )){
				if($extClass = self::getExtClass($e)){
					return $extClass;
				}
				else{
					return $e;
				}
			}
		}

		return false;
	}

	public function loadedMainModObj(){
		if(!is_object($this->mainModObj)) return false;
		return true;
	}

	public function getMainModObj(){
		if(!is_object($this->mainModObj)){
			throw new AVA_Exception('{Call:Lang:core:core:osnovnojvyzy}');
		}

		return $this->mainModObj;
	}

	private function getCallHash($p, $f, $params){
		/*
			Создает хеш запроса модуля чтобы предотвратить дублирование объектов
		*/

		ksort($params);
		$str = 'mod='.$p.'&func='.$f.'&';

		foreach($params as $i => $e){
			$str .= $i.'='.$e.'&';
		}
		return md5($str);
	}

	public function __ava__setMod($name){
		$this->mod = $name;
	}

	public function __ava__setFunc($name){
		$this->func = $name;
	}

	public function __ava__setGPC($arr, $data, $decode = false, $strip = true){
		if(!Library::constVal('IN_ADMIN')) $data = regExp::html($data, false, "<>{}'\"");
		if($strip && ini_get('magic_quotes_gpc')) $data = regExp::stripSlashes($data);
		if($decode) $data = library::decodeUrl($data);
		$this->$arr = $data;
	}

	public function __ava__getExclusiveVar(){
		return $this->varOnly;
	}

	public function getMod(){
		return $this->mod;
	}

	public function getFunc(){
		return $this->func;
	}

	public function __ava__setExclusiveVar($var){
		$this->varOnly = $var;
	}

	public function getModuleRightsList($name, $type = ''){
		/*
			Возвращает список всех типов прав для этого модуля
		*/

		$rights = eval('return mod_'.$type.$name.'::rightsList();');
		return $rights;
	}

	public function __ava__loadModulesList($force = false){
		if(empty($this->moduleParams) || $force){
			if(!is_object($this->DB)) throw new AVA_Exception('{Call:Lang:core:core:neopredeleno}');

			$t1 = $this->DB->getPrefix().'modules';
			$t2 = $this->DB->getPrefix().'isset_modules';
			$obj = $this->DB->Req("SELECT t1.*, t2.text AS orig_textname, t2.version FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.name=t2.name ORDER BY t1.sort");

			while($r = $obj->Fetch()){
				$this->moduleParams[$r['url']] = $r;
				$this->moduleParams[$r['url']]['united_modules'] = Library::str2arrKeys($r['united_modules']);
				$this->moduleParamsById[$r['id']] = $this->moduleParams[$r['url']];

				$this->modulePrototypes[$r['name']] = array(
					'text' => $r['orig_textname'],
					'version' => $r['version']
				);
			}
		}
	}

	public function __ava__getModulePrototype($name){
		/*
			Возвращает параметры из isset_modules
		*/

		$this->loadModulesList();
		return $this->modulePrototypes[$name];
	}

	public function __ava__getModulePrototypeName($name){
		/*
			Возвращает имя из isset_modules
		*/

		$this->loadModulesList();
		return $this->modulePrototypes[$name]['text'];
	}

	public function __ava__getModulePrototypeVersion($name){
		/*
			Возвращает версию из isset_modules
		*/

		$this->loadModulesList();
		return $this->modulePrototypes[$name]['version'];
	}

	public function __ava__getModulePrintData($name){
		/*
			Возвращает печатаемую информацию по модулю
		*/

		$this->loadModulesList();
		return '{Call:Lang:core:core:modulversiia:'.Library::serialize(array($this->moduleParams[$name]['orig_textname'], $this->moduleParams[$name]['name'], $this->moduleParams[$name]['version'])).'}';
	}

	public function __ava__getModuleParams($name){
		/*
			Возвращает параметры модуля
		*/

		if($name == 'main') return array('url' => 'main', 'db' => false, 'name' => 'main', 'text' => 'main', 'show' => 1);
		elseif($name == 'core') return array('url' => 'core', 'db' => false, 'name' => 'core', 'text' => 'core', 'show' => 1);

		if(empty($this->moduleParams[$name])) $this->loadModulesList(true);
		if(!$this->moduleParams[$name]) throw new AVA_Exception('{Call:Lang:core:core:nenajdenmodu:'.Library::serialize(array($name)).'}');
		return $this->moduleParams[$name];
	}

	public function __ava__getModuleParamsById($id){
		/*
			Возвращает параметры модуля
		*/

		$this->loadModulesList();
		return $this->moduleParamsById[$id];
	}

	public function __ava__getUnitedModule($mod, $united){
		/*
			Возвращает URL имя объединенного с данным модуля
			$mod - URL имя зависимого модуля
			$united - tech-имя искомого
		*/

		if(empty($this->unitedModules[$mod][$united])){
			$this->loadModulesList();
			$data = $this->getModuleParams($mod);
			$wh = array();

			foreach($data['united_modules'] as $i => $e){
				if($this->moduleParams[$i]['show'] && $this->moduleParams[$i]['name'] == $united) $this->unitedModules[$mod][$united] = $i;
			}
		}

		return $this->unitedModules[$mod][$united];
	}

	public function __ava__getUnitedDownModules($mod, $type = false){
		/*
			Возвращает все модули которые используют искомый как базовый
		*/

		if($mod == 'main' || $mod == 'core') return $this->getModules();

		if(empty($this->unitedModules2[$mod])){
			$this->loadModulesList();
			foreach($this->moduleParams as $i => $e){
				if($e['show'] && !empty($e['united_modules'][$mod])){
					if(!$type || $type == $e['name']) $this->unitedModules2[$mod][$i] = $i;
					$this->unitedModules2[$mod] = Library::array_merge($this->unitedModules2[$mod], $this->getUnitedDownModules($i));
				}
			}
		}

		return isset($this->unitedModules2[$mod]) ? $this->unitedModules2[$mod] : array();
	}

	public function getModules(){
		/*
			Список всех модулей установленных в системе
		*/

		if(empty($this->modules)){
			$this->loadModulesList();
			foreach($this->moduleParams as $i => $e){
				if($e['show']) $this->modules[$i] = $e['text'];
			}
		}

		return $this->modules;
	}

	public function __ava__getModuleName($url){
		/*
			Возвращает имя модуля
		*/

		if($url == 'core') return 'Core';
		elseif($url == 'main') return 'Main';
		$this->loadModulesList();
		return $this->moduleParams[$url]['text'];
	}

	public function __ava__getModuleParamsByTechName($name){
		/*
			Возвращает параметры модуля по техническому наименованию
		*/

		$dsc = XML::parseXML(Files::read(_W.'modules/'.$name.'/descript.xml'));
		return $dsc['descript'];
	}

	public function __ava__getModuleTechNameByTechId($name){
		/*
			Возвращает техническое имя модуля по техническому ID
		*/

		if($name == 'core') return 'Core';
		elseif($name == 'main') return 'Main';
		$params = $this->getModuleParamsByTechName($name);
		return $params['text'];
	}

	public function __ava__getModuleTechName($name){
		/*
			Возвращает техническое имя модуля по URL-имени
		*/

		$modParams = $this->getModuleParams($name);
		return $modParams['name'];
	}

	public function __ava__getTopCMSModule($mod){
		/*
			Возвращает модуль CMS, занимающий самую верхнюю позицию в данной ветке
		*/

		$this->loadModulesList(true);
		if($this->moduleParams[$mod]['name'] == 'cms') return $mod;

		if($this->moduleParams[$mod]['united_modules']){
			foreach($this->moduleParams[$mod]['united_modules'] as $i => $e){
				if($this->moduleParams[$i]['name'] == 'cms') return $i;
			}

			foreach($this->moduleParams[$mod]['united_modules'] as $i => $e){
				if($return = $this->getTopCMSModule($i)) return $return;
			}
		}

		return false;
	}

	public function __ava__getModulesByType($type, $parent = false){
		/*
			Возвращает список всех модулей соответствующих данному типу
		*/

		$this->loadModulesList();
		$return = array();

		foreach($this->moduleParams as $i => $e){
			if($e['show'] && $e['name'] == $type && ($parent === false || isset($e['united_modules'][$parent]))) $return[$i] = $e['text'];
		}

		return $return;
	}

	public function __ava__getFirstModuleByType($type, $parent = false){
		/*
			Возвращает первый модуль данного типа. Может быть нужно когда все равно какая копия модуля будет работать
		*/

		return Library::firstKey($this->getModulesByType($type, $parent));
	}

	public function __ava__getCoUnitedModulesByType($type, $co, $parent){
		/*
			Возвращает список всех модулей соответствующих данному типу, и объединенных с указанным
		*/

		return $this->getModulesByType($type, $this->getUnitedModule($co, $parent));
	}

	private function fetchModuleSites(){
		if(empty($this->modulesSites)){
			$this->siteParams = $this->DB->columnFetch(array('sites', '*', 'id'));
			foreach($this->siteParams as $i => $e){
				$this->sites[$i] = $e['name'];
			}

			$this->loadModulesList();
			foreach($this->moduleParams as $i => $e){
				foreach(explode(',', $e['sites']) as $e1){
					if(!$e1 || !$e['show']) continue;
					$this->modulesSites[$i][$e1] = $this->sites[$e1];
					$this->sitesModules[$e1][$i] = $this->sites[$e1];
				}
			}
		}
	}

	public function __ava__getModuleSites($mod){
		/*
			Возвращает все сайты отнесенные к модулю
		*/

		$this->fetchModuleSites();
		return $this->modulesSites[$mod];
	}

	public function getSites(){
		$this->fetchModuleSites();
		return $this->sites;
	}

	public function __ava__getSiteUrl($site){
		$this->fetchModuleSites();
		return $this->siteParams[$site]['url'];
	}

	public function __ava__getSiteModules($site){
		/*
			Возвращает все сайты отнесенные к модулю
		*/

		$this->fetchModuleSites();
		return $this->sitesModules[$site];
	}

	public function __ava__getExtensionPath($mod, $ext){
		/*
			Возвращает
		*/

		return _W.'modules/'.$this->getModuleTechName($mod).'/extensions/'.strtolower($ext).'/';
	}



	/************************************************************************************************************************************************************************

																		Вызовы йазыкоф

	*************************************************************************************************************************************************************************/

	public function __ava__getLangParams($lang){
		$this->loadLanguages();
		return $this->langParams[$lang];
	}

	public function __ava__getLangParamsById($id){
		$this->loadLanguages();
		return $this->langParamsById[$id];
	}

	public function __ava__getLangs(){
		$this->loadLanguages();
		return $this->langs;
	}

	public function __ava__getLangName($lang){
		$this->loadLanguages();
		return $this->langs[$lang];
	}

	public function getPhrase($str){
		/*
			Возвращает фразу. Принимает код вызова.
		*/

		if(!regExp::Match("|^\{Call:Lang:(.+)\}$|iUs", $str, true, true, $m)) return $str;
		$m[1] = explode(":", $m[1]);
		return $this->Lang->getPhrase($m[1][0], $m[1][1], $m[1][2], isset($m[1][3]) ? unserialize(base64_decode($m[1][3])) : array());
	}

	private function loadLanguages(){
		/*
			Считывает все данные о языках
		*/

		if(empty($this->langParams)){
			$this->langParams = $this->DB->columnFetch(array('languages', '*', 'name', '', "`sort`"));
			foreach($this->langParams as $i => $e){
				$this->langParamsById[$e['id']] = $e;
				$this->langs[$e['name']] = $e['text'];
			}
		}
	}


	/************************************************************************************************************************************************************************

														Функции предустановки пакетов при инсталляции

	*************************************************************************************************************************************************************************/

	public function __ava__innerPreParams($instObj, $params){
		/*
			Устанавливает параметры на основании данных инсталлятора
				- Вначале считываются все существующие в системе данные для:
					- Модулей
					- Языков
					- Шаблонов
					- Плагинов
				- Проходятся все устанавливаемые объекты и устанавливаются соответствующие данные
		*/

		$this->loadTemplates();
		$this->loadModulesList(true);
		$this->getAllModuleTemplates(true);

		if(!empty($params['templates'])){
			$sTmpl = $instObj->getFirstTemplate('system');
			$mmTmpl = $instObj->getFirstTemplate('modules/main');
			$mcTmpl = $instObj->getFirstTemplate('modules/core');

			foreach($params['templates'] as $i => $e){
				if(empty($this->templateParams[$e['tmplType']][$instObj->values['folder_'.$e['installator']]])){
					$this->templateParams[$e['tmplType']][$instObj->values['folder_'.$e['installator']]] = array(
						'name' => $instObj->values['name_'.$e['installator']],
						'folder' => $instObj->values['folder_'.$e['installator']],
						'type' => $e['tmplType'],
						'language' => empty($instObj->values['langname_'.$e['installator']]) ? '' : $instObj->values['langname_'.$e['installator']],
						'tech_name' => $e['name'],
						'vars' => array()
					);

					$this->allTemplates[$e['tmplType']][$instObj->values['folder_'.$e['installator']]] = array($instObj->values['name_'.$e['installator']], 1);
				}
			}

			foreach($params['templates'] as $i => $e){
				if(!empty($params['modules'])){
					foreach($params['modules'] as $i1 => $e1){
						$mName = $instObj->values['name_'.$e1['name']];
						if(!isset($this->allModuleTemplates['dependModuleTmps'][$mName])) $this->allModuleTemplates['dependModuleTmps'][$mName] = array();
						if(!isset($this->allModuleTemplates['depModNames'][$mName])) $this->allModuleTemplates['depModNames'][$mName] = $e1['text'];
					}
				}

				if(regExp::Match("|^modules/(\w+)$|iUs", $e['tmplType'], true, true, $m)){
					if(!empty($instObj->values['name_'.$m['1']]) && empty($this->allModuleTemplates['dependModuleTmps'][$instObj->values['name_'.$m['1']]][$e['name']])){
						$this->allModuleTemplates['dependModuleTmps'][$instObj->values['name_'.$m['1']]][$e['name']] = $e['text'];
					}
					elseif(($m['1'] == 'core' || $m['1'] == 'main')){
						if(empty($this->allModuleTemplates['dependModuleTmps'][$m['1']][$e['name']])){
							$this->allModuleTemplates['dependModuleTmps'][$m['1']][$e['name']] = $e['text'];
						}

						if($m['1'] == 'main') $mmTmpl = $e['name'];
						if($m['1'] == 'core') $mcTmpl = $e['name'];
					}
				}
				elseif($e['tmplType'] == 'system') $sTmpl = $e['name'];

				if($e['tmplType'] == 'main' || $e['tmplType'] == 'admin'){
					if(empty($this->templateParams[$e['tmplType']][$e['name']]['vars']['dependTemplates'])){
						$this->templateParams[$e['tmplType']][$e['name']]['vars']['dependTemplates'] = array(
							'sys_depend_tmp' => empty($instObj->values['sys_depend_tmp_'.$e['installator']]) ?
								$sTmpl : $instObj->values['sys_depend_tmp_'.$e['installator']],

							'depend_tmp_main' => empty($instObj->values['depend_tmp_main_'.$e['installator']]) ?
								$mmTmpl : $instObj->values['depend_tmp_main_'.$e['installator']],

							'depend_tmp_core' => empty($instObj->values['depend_tmp_core_'.$e['installator']]) ?
								$mcTmpl : $instObj->values['depend_tmp_core_'.$e['installator']],
						);
					}
				}
			}
		}

		if(!empty($params['modules'])){
			$this->loadModulesList();
			$this->fetchModuleSites();

			foreach($params['modules'] as $i => $e){
				$e['show'] = 1;
				if($instObj->getVersion('modules', $e, $instObj->values)) continue;
				$mName = $instObj->values['name_'.$e['name']];

				if(empty($this->templateParams[$this->getTemplateType()][$this->getTemplateName()]['vars']['dependTemplates']['depend_tmp_'.$mName])){
					$this->templateParams[$this->getTemplateType()][$this->getTemplateName()]['vars']['dependTemplates']['depend_tmp_'.$mName] =
						isset($instObj->values['depend_tmp_'.$mName.'_'.$e['installator']]) ?
							$instObj->values['depend_tmp_'.$mName.'_'.$e['installator']] :
							key($this->allModuleTemplates['dependModuleTmps'][$mName]);
				}

				if(empty($this->moduleParams[$instObj->values['name_'.$e['name']]])) $this->moduleParams[$instObj->values['name_'.$e['name']]] = $e;
				if(empty($this->moduleParams[$instObj->values['name_'.$e['name']]]['db'])) $this->moduleParams[$instObj->values['name_'.$e['name']]]['db'] = $instObj->values['db'];
				if(empty($this->moduleParams[$instObj->values['name_'.$e['name']]]['united_modules'])) $this->moduleParams[$instObj->values['name_'.$e['name']]]['united_modules'] = array();

				if(empty($this->modulesSites[$instObj->values['name_'.$e['name']]]) && !empty($instObj->values['sites'])){
					foreach($instObj->values['sites'] as $i1 => $e1){
						$this->modulesSites[$instObj->values['name_'.$e['name']]][$i1] = $this->sites[$e1];
						$this->sitesModules[$i1][$instObj->values['name_'.$e['name']]] = $this->sites[$e1];
					}
				}

				if(!empty($e['requirements']['requiredModules'])){
					foreach($e['requirements']['requiredModules'] as $i1 => $e1){
						$united = empty($instObj->values['united_'.$i1]) ? $instObj->values['name_'.$i1] : $instObj->values['united_'.$i1];
						if(empty($this->moduleParams[$instObj->values['name_'.$e['name']]]['united_modules'][$united])){
							$this->moduleParams[$instObj->values['name_'.$e['name']]]['united_modules'][$united] = 1;
						}
					}
				}
			}
		}
	}


	/************************************************************************************************************************************************************************

																		Работа с GPC

	*************************************************************************************************************************************************************************/

	public function __ava__getGPCVar($type, $var){
		$arr = $this->$type;
		if(empty($arr[$var])) return '';
		return $arr[$var];
	}

	public function __ava__getGPCArr($type){
		if(empty($this->$type)) return array();
		return $this->$type;
	}


	/************************************************************************************************************************************************************************

																		Работа с предпочтениями пользователя

	*************************************************************************************************************************************************************************/

	public function authUser(){
		/*
			Аутентификация пользователя.
			Все данные о пользователе хранит объект User
		*/

		$this->User = $this->sessGet('User');
		if(!is_object($this->User)) $this->User = new User();
	}

	public function getUserId(){
		return $this->User->getUserId();
	}

	public function __ava__getUserEml($id = false){
		if($id === false) $id = $this->getUserId();
		if(!$id) return '';
		$params = $this->getUserParamsById($id);
		return $params['eml'];
	}

	public function userIsAdmin(){
		//Проверяет имеет ли пользователь доступ в раздел администратора

		return $this->User->getAdminId();
	}

	public function userIsRoot(){
		//Проверяет имеет ли пользователь доступ в раздел администратора

		return isset($this->User->adminParams['root']) ? $this->User->adminParams['root'] : false;
	}

	public function __ava__userDataGet($var){
		return $this->User->userDataGet($var);
	}

	public function adminRightsGet($mod, $func, $rightType = 'select'){
		return true;
	}

	public function __ava__adminDataGet($var){
		return $this->User->userDataGet($var);
	}


	/************************************************************************************************************************************************************************

																	Выдача данных по левому пользователю

	*************************************************************************************************************************************************************************/

	public function __ava__getUserParamsById($userId){
		if(!isset($this->usersList[$userId])){
			$result = $this->DB->rowFetch(array('users', '*', db_main::q("`id`=#0 OR `login`=#0", array($userId))));
			$result = Library::array_merge(Library::unserialize($result['vars']), $result);
			$this->usersList[$result['id']] = $result;
			$this->usersList[$result['login']] = $result;
		}

		return $this->usersList[$userId];
	}

	public function __ava__getUserParam($userId, $param){
		$data = $this->getUserParamsById($userId);
		return $data[$param];
	}

	private function fetchAdmins(){
		if(!$this->adminsList){
			$p = $this->DB->getPrefix();
			$t1 = $p.'admins';
			$t2 = $p.'users';

			foreach($this->DB->columnFetch("SELECT t1.*, t2.name FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.user_id=t2.id ORDER BY t1.id") as $i => $e){
				$this->adminsParams[$e['login']] = $e;
				$this->adminsParamsById[$e['id']] = $e;
				$this->adminsList[$e['login']] = $e['name'] ? $e['name']." ({$e['login']})" : $e['login'];
			}
		}
	}

	public function __ava__getAdminsList(){
		$this->fetchAdmins();
		return $this->adminsList;
	}

	public function __ava__getAdminEml($login){
		$this->fetchAdmins();
		if(!$login) $login = $this->getRoot();
		return $this->adminsParams[$login]['eml'];
	}

	public function __ava__getAdminEmlById($id){
		$this->fetchAdmins();
		return $this->adminsParamsById[$id]['eml'];
	}

	public function __ava__getRoot(){
		/*
			Возвращает суперадмина
		*/

		$this->fetchAdmins();
		foreach($this->adminsParams as $i => $e){
			if($e['root'] == 1) return $e['login'];
		}

		throw new AVA_Exception('Не установлен суперадминистратор');
	}

	private function fetchUserGroups(){
		if($this->usersGroups === false) $this->usersGroups = $this->DB->columnFetch(array('users_groups', 'text', 'name', "", "`sort`"));
	}

	public function __ava__getUserGroupName($grp){
		$this->fetchUserGroups();
		return isset($this->usersGroups[$grp]) ? $this->usersGroups[$grp] : '';
	}

	public function __ava__getUserGroups(){
		$this->fetchUserGroups();
		return $this->usersGroups;
	}

	private function fetchUserFormTypes(){
		if($this->usersFormTypes === false) $this->usersFormTypes = $this->DB->columnFetch(array('users_form_types', 'text', 'name', "", "`sort`"));
	}

	public function __ava__getUserFormTypeName($type){
		$this->fetchUserFormTypes();
		return isset($this->usersFormTypes[$type]) ? $this->usersFormTypes[$type] : '';
	}

	public function __ava__getUserFormTypes(){
		$this->fetchUserFormTypes();
		return $this->usersFormTypes;
	}

	public function __ava__setUserPassword($id, $pwd){
		$userData = $this->getUserParamsById($id);
		$code = Library::inventStr(16);
		$this->DB->Upd(array('users', array('pwd' => Library::getPassHash($userData['login'], $pwd, $code), 'code' => $code), "`id`='$id'"));
		return $code;
	}

	public function __ava__getUserModifyFormValues(ModuleInterface $obj){
		$mObj = $this->callModule('main');
		$fields = $obj->fieldValues(array('login', 'eml', 'group', 'type', 'name', 'comment', 'show', 'date'));

		if(!empty($obj->values['pwd'])){
			$fields['code'] = Library::inventStr(16);
			$fields['pwd'] = Library::getPassHash($fields['login'], $obj->values['pwd'], $fields['code']);
		}

		$mObj->getUserRegFormValues('', $fields, $fields['type'], $obj->values);
		return $fields;
	}

	public function __ava__confirmUser($userId){
		/*
			Устанавливает что юзер подтвердил регистрацию
		*/

		$this->DB->Upd(array('users', array('show' => 1), "`id`='{$userId}'"));
		$this->reauthUserSession($userId);
	}


	/************************************************************************************************************************************************************************

																		Работа с системными данными

	*************************************************************************************************************************************************************************/

	public function settingsLoad(){
		foreach($this->DB->columnFetch(array('settings', array('value', 'crypt', 'module', 'var_type'), 'name', "!`site` OR `site`='".$this->Site->getSiteId()."'")) as $i => $e){
			if($e['crypt']) $e['value'] = Library::Decrypt($e['value']);

			switch($e['var_type']){
				case 'int': $e['value'] = (int)$e['value']; break;
				case 'flt': $e['value'] = (float)$e['value']; break;
				case 'obj': $e['value'] = Library::unserialize($e['value']); break;
			}

			if(!$e['module'] || $e['module'] == 'core') $this->settings[$i] = $e['value'];
			else $this->modSettings[$e['module']][$i] = $e['value'];
		}
	}

	public function __ava__getParam($var, $module = false){
		if(!isset($this->settings[$var]) && !isset($this->modSettings[$module][$var])){
			if(Library::constVal('IN_INSTALLATOR')) return '';
			else throw new AVA_Exception('{Call:Lang:core:core:nenajdenpara:'.Library::serialize(array($var)).'}');
		}
		return $module ? $this->modSettings[$module][$var] : $this->settings[$var];
	}

	public function __ava__setParam($var, $value){
		$this->settings[$var] = $value;
	}


	/************************************************************************************************************************************************************************

																		Работа с сессиями

	*************************************************************************************************************************************************************************/

	public function sessStart(){
		if(Library::constVal('USE_SESSION')){
			$return = session_start();
			$this->session = $_SESSION;
			return $return;
		}

		$this->sessSetId($this->sessLoad());
	}

	public function sessLoad(){
		$sessSend = $this->getParam('sessSend');
		$sessVar = $this->getParam('sessVar');

		switch($sessSend){
			case 'both':
			case 'get':
				$id = $this->getGPCVar('g', $sessVar);
				if(empty($id)) $id = $this->getGPCVar('p', $sessVar);
				if($sessSend == 'get') break;

			default:
				if(empty($id)) $id = $this->getGPCVar('c', $sessVar);
		}

		if($id){
			$expire = $this->getParam('sessLive') + time();
			$vars = $this->DB->cellFetch(array('session', 'vars', db_main::q("`sessid`=#0 AND `date`<$expire", $id)));
			$this->session = Library::cmpUnserialize($vars);
		}
		else $id = $this->sessInventId();

		return $id;
	}

	private function sessInventId(){
		//Выдумывает ID сессии
		return md5(Library::inventStr(16));
	}

	public function __ava__sessGet($var){
		if(!isset($this->session[$var])) return '';
		return $this->session[$var];
	}

	public function __ava__sessSet($var, $value){
		$this->session[$var] = $value;
	}

	public function __ava__sessMerge($value){
		$this->session = Library::array_merge($this->session, $value);
	}

	public function __ava__sessUnlink($var){
		//Очищает запись сессии
		unset($this->session[$var]);
	}

	public function sessGetId(){
		//Отдает ID сессии
		return $this->sessId;
	}

	public function __ava__sessSetId($id){
		$this->sessId = $id;
		$sessSend = $this->getParam('sessSend');
		$sessVar = $this->getParam('sessVar');

		switch($sessSend){
			case 'both':
			case 'get':
				define('GSID', $sessVar."=".$id);
				define('PSID', '<input type="hidden" name="'.$sessVar.'" value="'.$id.'" />');
				define('_SID', $id);
				if($sessSend == 'get') break;

			default:
				$this->setCookie($sessVar, $id, time() + $this->getParam('sessCookieLive'));
		}
	}

	public function getSessArray(){
		return $this->session;
	}

	public function __ava__reauthUserSession($id){
		if(!$id) throw new AVA_Exception('{Call:Lang:core:core:nevernyjidpo}');

		if(($vars = $this->DB->cellFetch(array('session', 'vars', "`user_id`='$id'"))) !== false){
			$vars = Library::cmpUnserialize($vars);
			$vars['User']->loadUserData();
			$this->DB->Upd(array('session', array('vars' => Library::cmpSerialize($vars)), "`user_id`='$id'"));
			if($id && $id == $this->User->getUserId()) $this->User = $vars['User'];
		}
	}

	public function __ava__unsetUserSession($id){
		$this->DB->Del(array('session', "`user_id`='$id'"));
	}



	/************************************************************************************************************************************************************************

																		Работа с headers

	*************************************************************************************************************************************************************************/

	public function __ava__setCookie($name, $value, $expire, $path = '/'){
		$this->cookie[$name] = array(
			'value' => $value,
			'expire' => $expire,
			'path' => $path
		);
	}

	public function __ava__rmCookie($name){
		$this->cookie[$name] = array(
			'value' => '',
			'expire' => 0,
			'path' => '/'
		);
		if(!empty($this->cookie[$name])) $this->cookie[$name]['expire'] = 0;
	}

	public function __ava__clearCookie(){
		$this->cookie = array();
	}

	public function __ava__getAllSetCookie(){
		return $this->cookie;
	}

	public function __ava__getSetCookie($name){
		return $this->cookie[$name];
	}

	public function __ava__getSetCookieValue($name){
		return $this->cookie[$name]['value'];
	}

	public function __ava__getSetCookieExpire($name){
		return $this->cookie[$name]['expire'];
	}

	public function __ava__getSetCookiePath($name){
		return $this->cookie[$name]['path'];
	}

	public function __ava__setHeader($name, $value){
		$this->headers[$name] = $value;
	}

	public function __ava__rmHeader($name){
		unset($this->headers[$name]);
	}

	public function clearHeaders(){
		unset($this->headers);
	}

	public function getHeadersList(){
		return $this->headers;
	}

	public function __ava__getHeader($name){
		return $this->headers[$name];
	}

	public function flushHeaders(){
		if(empty($this->headers['Content-type'])) $this->setHeader('Content-type', 'text/html; charset='.$this->getParam('charset'));

		foreach($this->headers as $i => $e){
			header($i.': '.$e);
		}

		foreach($this->cookie as $i => $e){
			setcookie($i, $e['value'], $e['expire'], $e['path']);
		}
	}


	/************************************************************************************************************************************************************************

																		Работа с флагами

	*************************************************************************************************************************************************************************/

	public function __ava__getFlag($name){
		if(empty($this->flags[$name])) return false;
		return $this->flags[$name];
	}

	public function __ava__setFlag($name, $param = true){
		$this->flags[$name] = $param;
	}

	public function __ava__rmFlag($name){
		unset($this->flags[$name]);
	}

	public function clearFlags(){
		$this->flags = array();
	}


	/************************************************************************************************************************************************************************

																		Задачи для крона

	*************************************************************************************************************************************************************************/

	public function clear(){
		/*
			Чистка от треша
				- Сессии
				- Формы
				- Транзакции
				- TMP-файлы
				- Задания cron
				- Старые письма
		*/

		$t = time();
		$return = '';

		$sessLive = $t - $this->getParam('sessLive');
		$formLive = $t - $this->getParam('clearForms');
		$filesLive = $t - $this->getParam('clearFiles');
		$statLive = $t - ($this->getParam('clearStat') * 86400);

		$return .= '{Call:Lang:core:core:udalenozapis:'.Library::serialize(array($this->DB->Del(array('session', "`date`<$sessLive")))).'}';
		$return .= '{Call:Lang:core:core:udalenozapis1:'.Library::serialize(array($this->DB->Del(array('forms', "`date`<$formLive")))).'}';
		$return .= 'Удалено '.$this->DB->Del(array('admin_stat', "`date`<$statLive")).' записей статистики администратора';

		if($this->getParam('mailsLive')) $return .= 'Удалено старых писем: '.$this->DB->Del(array('mails', "`date`<".($t - $this->getParam('mailsLive')))).'<br/>';
		if($this->getParam('tasksLive')) $return .= 'Удалено старых заданий: '.$this->DB->Del(array('tasks', "`added`<".($t - $this->getParam('tasksLive')))).'<br/>';

		$files = Files::readFolder(TMP);
		clearstatcache();

		foreach($files as $e){
			if(filectime(TMP.$e) < $filesLive && $e != '.htaccess'){
				if(Files::rmFolder(TMP.$e)) $return .= '{Call:Lang:core:core:udalenfajl:'.Library::serialize(array(TMP, $e)).'}';
			}
		}

		return $return;
	}

	public function mailer(){
		/*
			Рассылка писем
		*/

		$uniq = time() + microtime();
		$j = 0;
		$sended = array();

		$bySess = $this->getParam('mailInSession');
		$byEml = $this->getParam('mailInSessionOnOneEmail');
		$this->DB->Upd(array('mails', array('in_work' => $uniq), "`status`=0", "`date`", $bySess * $byEml));

		foreach($this->DB->columnFetch(array('mails', '*', 'id', "`in_work`='$uniq'", "`date`")) as $i => $e){
			if(empty($sended[$e['eml']])) $sended[$e['eml']] = 0;

			if($j < $bySess && $sended[$e['eml']] < $byEml){
				$e['extra'] = Library::unserialize($e['extra']);
				$e['notify_success_extra'] = Library::unserialize($e['notify_success_extra']);
				$e['notify_fail_extra'] = Library::unserialize($e['notify_fail_extra']);

				if(mail::sendWithQueue($i, $e)){
					$j ++;
					$sended[$e['eml']] ++;
				}
			}
			elseif($j <= $bySess) break;
		}

		$this->DB->Upd(array('mails', array('in_work' => 0), "`status`=0 AND `in_work`='$uniq'"));
		return true;
	}


	/************************************************************************************************************************************************************************

																	Работа встроенного Cron

	*************************************************************************************************************************************************************************/

	public function callCron(){
		/*
			Обращается по http к локальному адресу cron.php
		 */

		$cronCallType = $this->getParam('cronCallType');
		if($cronCallType == 'http'){
			$http = new httpClient(_D.'cron.php');
			$http->prepareHeaders();

			$sock = $http->getSocket(1);
			$http->put($sock);
			$http->close($sock);

			return true;
		}
		elseif($cronCallType == 'shell'){
			Library::shellPhp(_W.'cron.php', array(_W));
			return true;
		}

		return false;
	}



	/************************************************************************************************************************************************************************

																		Работа с отладчиком

	*************************************************************************************************************************************************************************/

	public function setDebugAction($action){
		/*
			Тупо пишет время и что было сделано
		*/

		return $this->Debugger->setAction($action);
	}

	public function setDebugInterval($action, $start, $end = false){
		/*
			Пишет промежуток времени который ушел на действие
		*/

		return $this->Debugger->setInterval($action, $start, $end);
	}



	/******************************************************************************************************************************************************************

																		Вспомогательные функции

	******************************************************************************************************************************************************************/

	public function __ava__getFormattedTime($time){
		/*
			Возвращает отформатированную дату
		*/

		return Dates::date($this->getParam('dateFormat').' '.$this->getParam('timeFormat'), $time);
	}

	public function getBackUrl(){
		if(!empty($this->callData['redirect'])) $return = $this->callData['redirect'];
		elseif($this->p && !empty($this->mainModObj->path)) $return = $this->mainModObj->path;
		elseif($this->p) $return = _D;
		else $return = $this->self;
		return $return;
	}

	public function loadExtension($mod, $class){
		if(class_exists($class, false)) return;
		$mod = strtolower($mod);
		$class = strtolower($class);

		$file = _W.'modules/'.$mod.'/extensions/'.$class.'.php';
		if(!file_exists($file)){
			throw new AVA_Exception('{Call:Lang:core:core:nevozmozhnoz:'.Library::serialize(array($class, $mod)).'}');
		}

		require_once($file);
	}
}

function AVAShutdown(){
	//Сохраняем сессию

	if(!Library::constVal('IN_CRON')){
		$GLOBALS['Core']->runPlugins('shutdown');
		$sessdata = $GLOBALS['Core']->getSessArray();

		if(is_object($GLOBALS['Core']->User)){
			$GLOBALS['Core']->User->__deinit();
			$sessdata['User'] = $GLOBALS['Core']->User;
		}

		if(Library::constVal('USE_SESSION')){
			$_SESSION = $sessdata;
		}
		else{
			$GLOBALS['Core']->DB->trStart();
			$uid = $GLOBALS['Core']->User->getUserId();
			$sid = $GLOBALS['Core']->sessGetId();

			$GLOBALS['Core']->DB->Del(array('session', "(`user_id`>0 AND `user_id`='$uid') OR `sessid`='$sid'"));
			$GLOBALS['Core']->DB->Ins(array('session', array('sessid' => $sid, 'user_id' => $uid, 'date' => time(), 'vars' => Library::cmpSerialize($sessdata))));
			$GLOBALS['Core']->DB->trEnd(true);
		}

		$GLOBALS['Core']->callCron();
	}

	if(TEST_MODE == 2) echo $GLOBALS['Core']->Debugger->close();
}

function __autoload($name){
	$name = strtolower($name);

	if(preg_match("/^mod_admin_(\w+)$/", $name, $m)){
		$file = _W.'modules/'.$m['1'].'/'.$name.'.php';
	}
	elseif(preg_match("/^mod_api_(\w+)$/", $name, $m)){
		$file = _W.'modules/'.$m['1'].'/'.$name.'.php';
	}
	elseif(preg_match("/^mod_(\w+)$/", $name, $m)){
		$file = _W.'modules/'.$m['1'].'/'.$name.'.php';
	}
	elseif(preg_match("/^gen_(\w+)$/", $name, $m)){
		$file = _W.'modules/'.$m['1'].'/'.$name.'.php';
	}
	elseif(preg_match("/^db_(\w+)$/", $name, $m)){
		$file = _W.'core/db/'.$name.'.php';
	}
	elseif(preg_match("/^mail(\w*)$/", $name, $m)){
		$file = _W.'core/mail/'.$name.'.php';
	}
	elseif(preg_match("/^arc(\w*)$/", $name, $m)){
		$file = _W.'core/arc/'.$name.'.php';
	}
	elseif(preg_match("/^install(\w*)$/", $name, $m)){
		$file = _W.'modules/'.$m['1'].'/install.php';
	}

	if(empty($file) || !file_exists($file)){
		$file = _W.'core/'.$name.'.php';
	}

	if(!file_exists($file)){
		throw new AVA_Exception('{Call:Lang:core:core:nevozmozhnoz1:'.Library::serialize(array($name)).'}');
	}
	else{
		require_once($file);
	}
}

require_once(_W.'core/exceptions.php');
set_exception_handler('AVACommonException');
register_shutdown_function('AVAShutdown');

?>