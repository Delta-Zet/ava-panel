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


class User extends objectInterface{

	private $id = 0;
	private $status = 0;

	public $params = array();
	public $extraParams = array();
	public $tempParams = array();
	public $userInfoTemplateParams = array();

	private $adminId = 0;
	public $adminParams = array();
	public $extraAdminParams = array();
	public $adminGroupParams = array();

	private $adminRights = array();
	private $adminGroupRights = array();
	private $accessableSites = array();

	public function __deinit(){
		$this->clearObj();
	}

	public function loadUserData(){
		if(!$this->id || !($this->params = $GLOBALS['Core']->DB->rowFetch(array('users', '*', "`id`='".db_main::Quot($this->id)."'")))) throw new AVA_Access_Exception('{Call:Lang:core:core:nenajdenpolz}');

		$this->tempParams = array();
		if(!$this->params['name']) $this->params['name'] = $this->params['login'];
		$this->params['pwd'] = '';
		$this->status = $this->params['show'];

		$this->params = Library::array_merge($this->params, Library::unserialize($this->params['vars']));
		if($this->adminId) $this->loadAdminData();
		$this->extraAuth();
	}

	public function loadAdminData(){
		$loginStat = $GLOBALS['Core']->DB->rowFetch(array('admin_stat', array('date', 'ip'), "`admins_id`='".db_main::Quot($this->adminId)."' AND `action_type`='login'", "`date` DESC"));

		$this->adminParams = $GLOBALS['Core']->DB->rowFetch(array('admins', '*', "`id`='".db_main::Quot($this->adminId)."'"));
		$this->adminParams['login_date'] = (int)$loginStat['date'];
		$this->adminParams['ip'] = $loginStat['ip'];

		$this->adminParams = Library::array_merge($this->adminParams, Library::unserialize($this->adminParams['data']));
		$this->adminRights = Library::unserialize($this->adminParams['rights']);
		$this->adminGroupParams = $GLOBALS['Core']->DB->rowFetch(array('admins_groups', array('rights', 'ip_access_type', 'show'), "`id`='".db_main::Quot($this->adminParams['group'])."'"));
		$this->adminGroupRights = Library::unserialize($this->adminGroupParams['rights']);

		unset($this->adminParams['rights'], $this->adminGroupParams['rights']);
		$this->fetchAccessableSites();
	}

	public function getUserId(){
		return $this->id;
	}

	public function getAdminId(){
		return $this->adminId;
	}

	public function getStatus(){
		return $this->status;
	}

	public function isRoot(){
		if($this->adminParams['root']) return true;
		return false;
	}

	public function __ava__isAuthority($mod, $func = false, $rightType = 1){
		/*
			Возвращает имеет ли пользователь право доступа к данному функционалу, данного модуля по данныму типу:
				0 (false) - не имеет
				1 - гость
				2 - админ
				3 - админ с правом создания пользователей (зарезервировано)
		*/

		if(!empty($this->adminParams['root'])) return true;
		$right = 0;

		if($mod == 'core') return false;
		elseif($mod == 'main') return true;
		elseif(!isset($this->adminRights[$mod])) return false;
		elseif($this->adminRights[$mod] == 5){
			if(!is_array($this->adminGroupRights[$mod])) return $this->adminGroupRights[$mod] >= $rightType;
			elseif($func === false) return true;
			else return $this->adminGroupRights[$mod][$func] >= $rightType;
		}
		elseif(!is_array($this->adminRights[$mod])) return $this->adminRights[$mod] >= $rightType;
		elseif($func === false) return true;
		else return $this->adminRights[$mod][$func] >= $rightType;
	}

	private function fetchAccessableSites(){
		$sites = $GLOBALS['Core']->DB->columnFetch(array('sites', array('name', ), 'id', "!`access` OR `access`='1'"));
		foreach($GLOBALS['Core']->getSites() as $i => $e){
			foreach($GLOBALS['Core']->getSiteModules($i) as $i1 => $e1){
				if($this->isAuthority($i1)){
					$this->accessableSites[$i] = $e;
					break;
				}
			}
		}
	}

	public function __ava__auth($obj){
		/*
			Проверяет данные пользователя и устанавливает его id
		*/

		if(!is_object($obj)){
			throw new AVA_Exception('{Call:Lang:core:core:prblemaauten}');
		}

		if($this->id){
			$obj->setError('login', '{Call:Lang:core:core:vyuzhevoshli}');
			return false;
		}

		$login = $obj->values['login'];
		$pwd = $obj->values['pwd'];

		if(!$data = $GLOBALS['Core']->DB->rowFetch(array('users', array('id', 'show', 'pwd', 'code', 'group'), "`login`='".db_main::Quot($login)."'"))){
			$obj->setError('login', '{Call:Lang:core:core:takogopolzov}');
		}
		elseif($data['show'] < 0 || ($data['group'] && !$GLOBALS['Core']->DB->cellFetch(array('users_groups', 'show', "`id`='{$data['group']}'")))){
			$obj->setError('login', '{Call:Lang:core:core:dliaehtogopo}');
		}
		else{
			$crcPwd = Library::getPassHash($login, $pwd, $data['code']);
			if($crcPwd != $data['pwd']){
				$obj->setError('pwd', '{Call:Lang:core:core:vyvvelinepra}');
			}
		}

		if(!$obj->check($form, false)) return false;

		//Ставим юзеру в сессию что он ауфентицирован
		$this->id = $data['id'];
		$this->loadUserData();

		if(!empty($obj->values['memory'])){
			$obj->Core->setCookie('AVAMem', $login, 2000000000);
			$obj->Core->setCookie('AVAMemHash', $crcPwd, 2000000000);
		}

		return $data['id'];
	}

	public function __ava__authById($userId){
		/*
			Аутентифицирует пользователя по ID
		*/

		if(!$data = $GLOBALS['Core']->DB->rowFetch(array('users', array('id', 'show'), "`id`='$userId'"))){
			throw new AVA_Exception('{Call:Lang:core:core:nenajdenpolz1:'.Library::serialize(array($userId)).'}');
		}
		elseif($data['show'] < 0){
			$obj->setError('login', '{Call:Lang:core:core:dliaehtogopo}');
		}

		$this->id = $data['id'];
		$this->loadUserData();
		return true;
	}

	public function __ava__authAdmin($obj){
		/*
			Аутентификация админа
		*/

		if(!is_object($obj)){
			throw new AVA_Exception('{Call:Lang:core:core:prblemaauten}');
		}

		if($this->adminId){
			return $this->adminId;
		}

		$p = $GLOBALS['Core']->DB->getPrefix();
		$t1 = $p.'admins';
		$t2 = $p.'admins_groups';
		$t3 = $p.'users';

		if(!$data = $GLOBALS['Core']->DB->rowFetch("SELECT t1.id, t1.user_id, t1.date, t1.show, t1.pwd, t1.group, t2.show AS grp_show, t3.code
			FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.group=t2.id LEFT JOIN $t3 AS t3 ON t1.user_id=t3.id
			WHERE t1.login='".db_main::Quot($obj->values['login'])."' AND t1.user_id>0")){
			$obj->setError('login', '{Call:Lang:core:core:takogopolzov}');
		}
		elseif(!$data['show'] || ($data['group'] && !$data['grp_show'])){
			$obj->setError('login', '{Call:Lang:core:core:dostupnasajt}');
		}
		else{
			$crcPwd = Library::getPassHash($obj->values['login'], $obj->values['pwd'], $data['code']);
			if($crcPwd != $data['pwd']){
				$obj->setError('pwd', '{Call:Lang:core:core:vyvvelinepra}');
			}
		}

		if(!$obj->check($form, false)) return false;

		//Ставим юзеру в сессию что он ауфентицирован
		$this->id = $data['user_id'];
		$this->adminId = $data['id'];
		$this->loadUserData();

		if(!empty($obj->values['memory'])){
			$obj->Core->setCookie('AVAMem', $obj->values['login'], 2000000000);
			$obj->Core->setCookie('AVAMemHash', $crcPwd.sha1($obj->values['pwd']), 2000000000);
		}

		return $data['id'];
	}



	/************************************************************************************************************************************************************************

																		Доступ на сайт

	*************************************************************************************************************************************************************************/

	public function adminAccess(){
		/*
			Проверяет что данному пользователю разрешен доступ на сайт. Если не разрешен, throw new Exception
			В ip_access_type '' == allow
		*/

		if(($this->adminGroupParams && !$this->adminGroupParams['show']) || ($this->adminParams && !$this->adminParams['show'])) $this->accessDeny();

		$ip = $GLOBALS['Core']->getGPCVar('s', 'REMOTE_ADDR');
		$adminId = $this->getAdminId();
		$grpId = empty($this->adminParams['group']) ? 0 : $this->adminParams['group'];

		$bySite = false;
		$byGrp = false;
		$byAdmin = false;

		$ips = $GLOBALS['Core']->DB->columnFetch(
			array(
				'admin_access_ip',
				'*',
				'',
				db_main::q(
					"#0 REGEXP (`ip`) AND (`admins_id`=#1 OR !`admins_id`) AND (`admins_groups_id`=#2 OR !`admins_groups_id`)",
					array($ip, $adminId, $grpId)
				),
				"`type`"
			)
		);

		foreach($ips as $r){
			if($r['admins_id']) $byAdmin = $r['type'];
			elseif($r['admins_groups_id']) $byGrp = $r['type'];
			else $bySite = $r['type'];
		}

		if(($bySite == 'disallow') || ($byGrp == 'disallow') || ($byAdmin == 'disallow')) $this->accessDeny();
		elseif(($GLOBALS['Core']->getParam('adminAccessType') == 'disallow') && ($bySite == 'disallow' || $bySite === false)) $this->accessDeny();
		elseif($this->adminGroupParams && $this->adminGroupParams['ip_access_type'] == 'disallow' && $byGrp === false) $this->accessDeny();
		elseif($this->adminParams && ($this->adminParams['ip_access_type'] == 'disallow') && $byAdmin === false) $this->accessDeny();

		return true;
	}

	public function userAccess(){
		/*
			Проверяет возможность доступа пользователя
		*/

		return true;
	}

	private function accessDeny(){
		$GLOBALS['Core']->runPlugins('accessDeny');
		throw new AVA_Access_Exception('{Call:Lang:core:core:dostupksajtu}');
	}


	/************************************************************************************************************************************************************************

															Взаимодействие со сторонними авторизациями

	*************************************************************************************************************************************************************************/

	private function extraAuth(){
		/*
			Функция проходит все модули и аутентифицирует пользователя во всех каких сможет
			Чтобы выставить доп параметры юзеру при аутентификации должен быть специальный метод __authUser, для админа __authAdmin
			Все данные хранятся в extraParams[mod]
		*/

		$this->userInfoTemplateParams = array(
			'main' => array(
				'name' => '{Call:Lang:core:core:osnovnoe}',
				'params' => array(
					'date' => array(
						'name' => '{Call:Lang:core:core:zaregistriro}',
						'value' => Dates::dateTime($this->params['date']),
					),
					'login' => array(
						'name' => '{Call:Lang:core:core:login}',
						'value' => $this->params['login'],
					),
					'eml' => array(
						'name' => 'E-mail',
						'value' => $this->params['eml'],
					)
				)
			)
		);

		foreach($GLOBALS['Core']->getModules() as $i => $e){
			$this->authInModule($i);
		}
	}

	public function __ava__authInModule($mod){
		/*
			Ауфентицируется в определенном модуле
		*/

		$mObj = $GLOBALS['Core']->callModule($mod);
		if(method_exists($mObj, '__authUser') || method_exists($mObj, '__ava____authUser')) $this->extraParams[$mod] = $mObj->__authUser($this);
		if($this->adminId && (method_exists($mObj, '__authAdmin') || method_exists($mObj, '__ava____authAdmin'))) $this->extraAdminParams[$mod] = $mObj->__authAdmin($this);
	}


	/************************************************************************************************************************************************************************

																		Доступ на сайт

	*************************************************************************************************************************************************************************/

	public function logout(){
		foreach($this as $i => $e){
			if(is_array($e)) $this->$i = array();
			else $this->$i = null;
		}

		$GLOBALS['Core']->rmCookie('AVAMem');
		$GLOBALS['Core']->rmCookie('AVAMemHash');
		return true;
	}
}

?>