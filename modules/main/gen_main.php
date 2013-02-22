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


class gen_main extends ModuleInterface{

	public function __ava____map($obj){
		/*
			Карта сайта
				- Регистрация
				- Вход
				- Напомнить пароль
				- Вставка кода подтверждения регистрации
				- Кабинет
				- Выход
		*/

		$return = array(
			array('name' => 'Регистрация', 'link' => 'index.php?mod=main&func=registration'),
			array('name' => 'Вход', 'link' => 'index.php?mod=main&func=login'),
			array('name' => 'Напомнить пароль', 'link' => 'index.php?mod=main&func=forgotPwd'),
			array('name' => 'Подтвердить регистрацию', 'link' => 'index.php?mod=main&func=regCode')
		);

		if($this->User->getUserId()){
			$return[] = array('name' => 'Кабинет', 'link' => 'index.php?mod=main&func=cabinate');
			$return[] = array('name' => 'Выход', 'link' => 'index.php?mod=main&func=logout');
		}
		return $return;
	}


	/***************************************************************************************************************************************************************

																				Регистрация

	****************************************************************************************************************************************************************/

	public function __ava__addUser($params){
		/*
			Добавляет пользователя
		*/

		$confReg = $this->Core->getParam('confirmRegistration');
		if(!$confReg) $params['show'] = 1;
		if(empty($params['pwd'])) $params['pwd'] = Library::inventStr(8);
		$params['code'] = Library::inventStr(16);

		$fields = $this->fieldValues(array('type', 'name', 'eml', 'login', 'utc', 'code', 'show', 'date', 'comment'), $params);
		$fields['date'] = empty($fields['date']) ? time() : $fields['date'];
		$fields['pwd'] = Library::getPassHash($fields['login'], $params['pwd'], $fields['code']);

		$this->getUserRegFormValues('in_reg', $fields, $fields['type']);
		$id = $this->typeIns('users', $fields, '', false);

		if($confReg == 1){
			$this->sendRegConfirmMail($params['eml'], $params);
			$this->setContent('{Call:Lang:core:core:vyuspeshnoza}');
		}
		elseif($confReg == 2){
			$this->setContent('{Call:Lang:core:core:vyuspeshnoza1}');
		}
		else $this->sendRegMail($id, $this->values);

		return $id;
	}

	public function getUserRegFormValues($type, &$values, $fType, $params = false){
		if($params === false) $params = $this->values;

		foreach($this->getUserRegFormMatrix($type, $fType) as $i => $e){
			$param = isset($params[$fType.'_'.$i]) ? $params[$fType.'_'.$i] : (isset($params[$i]) ? $params[$i] : '');
			if($this->Core->DB->issetField('users', $i)) $values[$i] = $param;
			else $values['vars'][$i] = $param;
		}
	}

	public function getUserRegFormMatrix($type = '', $fType = '', &$values = array(), &$names = array(), &$extra = array()){
		/*
			Возвращает мацрицу формы регистрации йузера
		*/

		$type = $type ? "`$type` AND " : "";
		list($matrix, $values, $names, $extra) = $this->getMatrixArray(array('user_reg_form', '*', $type."`show` AND `form_types` REGEXP (',@without,')", "`sort`"));

		if($fType == ''){
			foreach($this->Core->getUserFormTypes() as $i => $e){
				list($m, $v, $n, $e) = $this->getMatrixArray(array('user_reg_form', '*', $type."`show` AND `form_types` REGEXP (',{$i},')", "`sort`"));

				if($m){
					$fk = Library::FirstKey($m);
					$lk = Library::LastKey($m);
					$m[$fk]['pre_text'] = '<div id="'.$i.'" style="display: none;">'.(isset($m[$fk]['pre_text']) ? $m[$fk]['pre_text'] : '');
					$m[$lk]['post_text'] = (isset($m[$fk]['post_text']) ? $m[$fk]['post_text'] : '').'</div>';

					foreach($m as $i1 => $e1){
						$m[$i1]['checkConditions']['type'] = $i;
					}

					$matrix = Library::array_merge($matrix, Library::concatPrefixArrayKey($m, $i.'_'));
					$values = Library::array_merge($values, Library::concatPrefixArrayKey($v, $i.'_'));
					$names = Library::array_merge($names, $n);
					$extra = Library::array_merge($extra, Library::concatPrefixArrayKey($e, $i.'_'));
				}
			}
		}
		else{
			list($m, $v, $n, $e) = $this->getMatrixArray(array('user_reg_form', '*', $type."`show` AND `form_types` REGEXP (',$fType,')", "`sort`"));
			$matrix = Library::array_merge($matrix, $m);
			$values = Library::array_merge($values, $v);
			$names = Library::array_merge($names, $n);
			$extra = Library::array_merge($extra, $e);
		}

		if(!$type) foreach($matrix as $i => $e) unset($matrix[$i]['disabled']);
		return $matrix;
	}

	/***************************************************************************************************************************************************************

																				Предпочтения

	****************************************************************************************************************************************************************/

	protected function __ava__userPrefs($formTmpl = ''){
		/*
			Предпочтения для пользователя

			1. Главная страница
			2. Шаблон сайта
		 */

		$id = $this->User->getUserId();
		$values = Library::unserialize($this->DB->cellFetch(array('users', 'vars', "`id`='$id'")));

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'user_prefs',
						'userPrefsSet',
						array('caption' => '{Call:Lang:core:core:predpochteni}')
					),
					'user_prefs',
					array('templates' => $this->Core->getAllTemplates('main', true, true))
				),
				$values,
				array(),
				$formTmpl
			)
		);
	}

	protected function __ava__userPrefsSet($back){
		/*
			Предпочтения админки
		*/

		if(!$this->check()) return false;
		$id = $this->User->getUserId();
		$data = Library::array_merge(Library::unserialize($this->DB->cellFetch(array('users', 'vars', "`id`='$id'"))), $this->fieldValues(array('main_page', 'template')));

		$this->DB->Upd(array('users', array('vars' => $data), "`id`='$id'"));
		$this->Core->reauthUserSession($id);
		$this->refresh($back);

		return true;
	}

	public function __ava__showStat($id, $action = false){
		/*
			Принимает ID администратора и возвращает список статистики по нему с возможностью сортировки, выбора с использованием формы выбора и разбивкой на страницы
		*/

		$id = db_main::Quot($id);
		$adminName = $this->DB->cellFetch( array( 'admins', 'login', "id='$id'" ) );
		if(!empty($this->values['action_object'])) $this->values['action_object'] = regExp::Replace(AVA_DB_PREF.$this->values['mod'], '', $this->values['action_object']);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'admin_stat_list',
					array(
						'req' => array('admin_stat', '*', "`admins_id`='$id'", "`date` DESC"),
						'searchForm' => array(
							'orderFields' => array('ip' => 'IP', 'date' => '{Call:Lang:core:core:date}'),
							'searchFields' => array(
								'ip' => 'IP',
								'action_object' => '{Call:Lang:core:core:tablitsa}',
								'action_id' => '{Call:Lang:core:core:idobekta}',
								'action_type' => '{Call:Lang:core:core:tipdejstviia}',
								'action_mod' => '{Call:Lang:core:core:modul}',
								'action_descript' => '{Call:Lang:core:core:opisaniedejs}',
								'date' => '{Call:Lang:core:core:data}'
							),
							'searchMatrix' => array(
								'action_type' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:core:core:liuboe}',
										'new' => '{Call:Lang:core:core:sozdanieobek}',
										'modify' => '{Call:Lang:core:core:izmenenieobe}',
										'delete' => '{Call:Lang:core:core:udalenieobek}',
										'login' => '{Call:Lang:core:core:vkhod}',
										'logout' => '{Call:Lang:core:core:vykhod}'
									)
								),
								'action_mod' => array(
									'type' => 'select',
									'additional' => Library::array_merge(
										array('' => '{Call:Lang:core:core:liuboj}'),
										$this->Core->DB->columnFetch(array('modules', 'text', 'url', "`show`", "`sort`"))
									)
								),
								'action_object' => array(
									'comment' => '{Call:Lang:core:core:tablitsabazy}'
								),
								'action_id' => array(
									'comment' => '{Call:Lang:core:core:idobektavtab}'
								)
							),
							'searchParams' => array('action' => $action)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:spisokdejstv:'.Library::serialize(array($adminName)).'}'
					)
				)
			)
		);
	}


	/***************************************************************************************************************************************************************

																				Письма

	****************************************************************************************************************************************************************/

	public function __ava__sendRegMail($userId, $extraParams = array()){
		/*
			Отправляет письмо о регистрации
		*/

		$params = $this->DB->rowFetch(array('users', '*', "`id`='$userId'"));
		$params['pwd'] = 'тот, который вы вводили при регистрации';
		$params = Library::array_merge($params, $extraParams);
		return $this->mail($params['eml'], $this->getTmplParams('registration'), $params);
	}

	public function __ava__sendRegConfirmMail($eml, $data){
		/*
			Письмо с кодом подтверждения регистрации
		*/

		$data['link'] = _D.'index.php?mod=main&func=confirmRegistration&login='.$data['login'].'&code='.$data['code'];
		$data['registerConfirmationLink'] = _D.'?mod=main&func=regCode';
		return $this->mail($eml, $this->getTmplParams('registrationCode'), $data);
	}

	public function __ava__sendForgotPwdHash($eml, $data){
		/*
			Писмьо с напоминанием пороля
		*/

		$data['link'] = _D.'index.php?mod=main&func=forgotPwd3&code='.$data['code'].'&login='.$data['login'];
		$data['forgotPwdConfirmLink'] = _D.'index.php?mod=main&func=forgotPwd3';
		return $this->mail($eml, $this->getTmplParams('recoverPwdLink'), $data);
	}



	/***************************************************************************************************************************************************************

																				О программе

	****************************************************************************************************************************************************************/

	protected function func_about(){
		/*
			О программе
		*/

		$this->setMeta('{Call:Lang:core:core:orazrabotchi}');
		$this->setContent('{Call:Lang:core:core:sajtsozdansi}');
		$this->setContent('{Call:Lang:core:core:avtorprogram}');
		$this->setContent('{Call:Lang:core:core:ofitsialnyjs}');
		$this->setContent('{Call:Lang:core:core:programmnoeo}');
	}
}

?>