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



$GLOBALS['Core']->loadExtension('bill_hosting', 'servconnectIsp');

class servConnectIspReseller extends servconnectIsp{


	/********************************************************************************************************************************************************************

																				Соединение

	*********************************************************************************************************************************************************************/

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if($this->sendReq('reseller')) return true;
		return false;
	}


	/********************************************************************************************************************************************************************

																		Работа с пакетами

	*********************************************************************************************************************************************************************/

	public function __ava__setAddPkgMatrix($service, $params = array()){
		/*
			Матрица для пакета
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'pkgdsc_'.$this->getParamsVar($service, 'ip') => array(
					'additional' => Library::array_merge(
						array(
							'free' => '{Call:Lang:modules:bill_hosting:sviazatsperv}',
							'noassign' => '{Call:Lang:modules:bill_hosting:nesviazyvatn}'
						),
						$this->getIps()
					),
					'warn' => '{Call:Lang:modules:bill_hosting:vyneukazalii}'
				)
			),
			array(),
			array(),
			'block2'
		);

		return true;
	}

	//Управление пакетами
	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		return $this->sendAddPkg($service, $params, 'reseller');
	}



	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/

	public function setAccOrderMatrix($service, $params = array()){
		/*
			Матрица для заказа
		*/

		$this->obj->addFormBlock($params['fObj'], array(), array(), array($params['prefix'].'domain'.$params['id']));
	}

	public function __ava__setAddAccMatrix($service, $params = array()){
		/*
			Матрица для пакета
		*/

		$this->obj->addFormBlock($params['fObj'], array(), array(), array($params['prefix'].'domain'.$params['id']));
		return $this->setAddAccMatrix2($service, $params);
	}

	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		return $this->sendAddAcc($service, $params, array(), true, 'reseller.edit');
	}

	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$reqParams['preset'] = $params['pkgData']['server_name'];
		return $this->sendModifyAcc($service, $params, $reqParams, 'reseller.edit');
	}

	public function __ava__delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/

		return $this->sendMultiAccsReq($service, $params, 'reseller.delete');
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, 'reseller.disable');
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, 'reseller.enable');
	}

	public function isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
		*/
	}

	public function listAccs($service, $params = array()){
		/*
			Усе акки
		*/
	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		$data = parent::getInstallParams();
		$data[0]['userlimit'] = array('type' => 'text', 'unlimit' => '10000', 'text' => '{Call:Lang:modules:bill_hosting:ogranichenie3}', 'extra' => array('warn' => 'Лимит на количество пользователей не может быть нулевым'));
		$data[0]['iplimit'] = array('type' => 'text', 'unlimit' => '100000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoi1}', 'name' => 'ips');
		$data[0]['ip6limit'] = array('type' => 'text', 'unlimit' => '100000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoi2}');
		return array($data[0], '{Call:Lang:modules:bill_hosting:ispmanagerpa}', 'bill_hosting');
	}
}

?>