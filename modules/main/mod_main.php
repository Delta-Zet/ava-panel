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


class mod_main extends gen_main{


	/***************************************************************************************************************************************************************

																		Регистрация

	****************************************************************************************************************************************************************/

	protected function func_forgotPwd(){
		/*
			Напаминалка пороля
		*/

		$this->setMeta('{Call:Lang:core:core:vosstanovlen}');
		$this->setContent('{Call:Lang:core:core:vveditesvojl}');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'forgotPwd',
						'forgotPwd2'
					),
					'forgot'
				)
			)
		);
	}

	protected function func_forgotPwd2(){
		/*
			Отсылает письмо для восстановления
		*/

		$this->setMeta('{Call:Lang:core:core:vosstanovlen}');
		$login = db_main::Quot($this->values['login']);
		if(!$data = $this->DB->columnFetch(array('users', '*', 'eml', "`login`='$login' OR `eml`='$login'"))){
			$this->setError('login', '{Call:Lang:core:core:nenajdenotak}');
		}

		if(!$this->check()) return false;
		foreach($data as $i => $e) $this->sendForgotPwdHash($i, $e);
		$this->setContent('{Call:Lang:core:core:pismosinstru}');
	}

	protected function func_forgotPwd3(){
		/*
			Прием кода восстановления
		*/

		$this->setMeta('{Call:Lang:core:core:vosstanovlen}');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'forgotPwd',
						'forgotPwd4'
					),
					'forgot_code'
				),
				$this->values
			)
		);
	}

	protected function func_forgotPwd4(){
		/*
			Завершение восстановления
		*/

		$this->setMeta('{Call:Lang:core:core:vosstanovlen}');
		if(!$cData = $this->Core->DB->rowFetch(array('users', '*', "`login`='{$this->values['login']}' AND `code`='{$this->values['code']}'"))){
			$this->setError('code', 'Неверно указан код подтверждения, либо он уже был активирован ранее');
		}

		if(!$this->check()) return false;

		$this->Core->setUserPassword($cData['id'], $this->values['pwd']);
		$this->refresh('main');
		return true;
	}

	protected function func_registration(){
		/*
			Регистрация нового пользователя
		 */

		$this->setMeta('{Call:Lang:core:core:registratsii1}');
		$fObj = $this->newForm('registration', 'registrationAdd');

		if(!$this->Core->getUserId()) $this->addFormBlock($fObj, 'registration', array('formTypes' => $this->Core->getUserFormTypes()));

		$matrix = $this->getUserRegFormMatrix('in_reg', '', $values);
		foreach($matrix as $i => $e){
			$matrix[$i]['checkConditions']['type_auth'] = '1';
		}

		$values['type_auth'] = empty($this->values['type_auth']) ? 1 : 2;
		$this->addFormBlock($fObj, $matrix, array('values' => $values));
		$this->callAllMods('__registration', array('fObj' => $fObj));

		if($this->Core->getParam('registrationCaptcha')) $this->addFormBlock($fObj, array('type_captcha', array('captcha' => array('checkConditions' => array('type_auth' => 1)))));
		$lk = Library::lastKey($fObj->matrix);
		$this->addFormBlock(
			$fObj,
			array(
				$lk => array(
					'post_text' => (isset($fObj->matrix[$lk]['post_text']) ? $fObj->matrix[$lk]['post_text'] : '').'</div><script type="text/javascript">'."\n".'showTypeFields();'."\n".'</script>'
				)
			)
		);

		$this->setContent(
			$this->getFormText(
				$fObj,
				array(),
				array('in_module' => empty($this->values['in_module']) ? '' : $this->values['in_module'])
			)
		);
	}

	protected function func_registrationAdd(){
		/*
			Регистрация нового пользователя
		 */

		$this->setMeta('{Call:Lang:core:core:registratsii1}');
		if($this->values['type_auth'] == 2) return $this->func_auth();

		if(!$this->Core->getUserId()){
			if($this->DB->cellFetch(array('users', 'login', "`login`='".db_main::Quot($this->values['login'])."'"))){
				$this->setError('login', '{Call:Lang:core:core:takojloginuz}');
			}
		}

		$this->callAllMods('__registrationCheck');
		if(!$this->check()) return false;

		if(!$id = $this->Core->getUserId()){
			$id = $this->addUser($this->values);
		}

		$this->callAllMods('__registrationAdd', $id);
		$this->Core->User->authById($id);
		if(!empty($this->values['redirect'])) $this->redirect2($this->values['redirect']);
		else $this->refresh('', 'Регистрация выполнена');

		return $id;
	}

	protected function func_confirmRegistration(){
		/*
			Подтверждение регистрации по коду
		*/

		$this->setMeta('{Call:Lang:core:core:vvodkodapodt}');

		if($this->DB->Upd(array('users', array('show' => 1), db_main::q("`login`=#0 AND `code`=#1 AND `show`=0", array($this->values['login'], $this->values['code']))))){
			$this->setContent('{Call:Lang:core:core:vasharegistr}');
			$id = $this->DB->cellFetch(array('users', 'id', "`login`='{$this->values['login']}'"));
			$this->Core->reauthUserSession($id);
			$this->sendRegMail($id);
			return true;
		}
		else{
			$this->setContent('{Call:Lang:core:core:vyukazalinep}');
			return false;
		}
	}

	protected function func_regCode(){
		/*
			Форма для ручной вставки кода
		*/

		$this->setMeta('{Call:Lang:core:core:vvodkodapodt}');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'regCode',
						'confirmRegistration'
					),
					'confirm_reg'
				)
			)
		);
	}


	/***************************************************************************************************************************************************************

																Аутентификация и личный кабинет

	****************************************************************************************************************************************************************/

	protected function func_login(){
		$this->setMeta('{Call:Lang:core:core:vkhodvlichny}');
		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'login',
						'auth'
					),
					_W.'forms/type_auth.php'
				)
			)
		);

		return true;
	}

	protected function func_auth(){
		$this->User->auth($this);
		if(!$this->check()) return false;

		if(empty($this->values['redirect'])) $this->values['redirect'] = _D;
		$this->redirect2($this->values['redirect'], '{Call:Lang:core:core:autentifikat1}');
		return true;
	}

	protected function func_logout(){
		if(!$this->User->logout()) return false;
		if(empty($this->values['redirect'])) $this->values['redirect'] = _D;
		$this->redirect2($this->values['redirect'], '{Call:Lang:core:core:vashiautenti}');
		return true;
	}

	protected function func_cabinate(){
		/*
			Главная страница личного кабинета
		*/

		if(!$id = $this->User->getUserId()) throw new AVA_Access_Exception('Вы не авторизованы для доступа к этому функционалу');
		$userData = $this->Core->getUserParamsById($id);
		$this->setMeta('{Call:Lang:core:core:lichnyjkabin}');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'profileNew',
						'profileNew',
						array(
							'caption' => '{Call:Lang:core:core:vashprofil}'
						)
					),
					array('user_data', $this->getUserRegFormMatrix('in_account', $userData['type']))
				),
				$userData
			)
		);

		$this->userPrefs();

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'pwdNew',
						'pwdNew',
						array(
							'caption' => '{Call:Lang:core:core:smenitparol}'
						)
					),
					'type_newpwd'
				),
				array()
			)
		);
	}

	protected function func_profileNew(){
		/*
			Пользовательский профиль
		*/

		if(!$id = $this->User->getUserId()) throw new AVA_Access_Exception('Вы не авторизованы для доступа к этому функционалу');
		$userData = $this->Core->getUserParamsById($id);
		$this->setMeta('{Call:Lang:core:core:lichnyjkabin}');

		if(!$this->check()) return false;

		$fields = $this->fieldValues(array('name', 'eml', 'utc'));
		$this->getUserRegFormValues('in_account', $fields, $userData['type']);
		$this->DB->Upd(array('users', $fields, "`id`='$id'"));
		$this->Core->reauthUserSession($id);
		$this->refresh('cabinate');

		return true;
	}

	protected function func_pwdNew(){
		/*
			Пользовательский профиль
		*/

		if(!$id = $this->User->getUserId()) throw new AVA_Access_Exception('Вы не авторизованы для доступа к этому функционалу');
		$this->setMeta('{Call:Lang:core:core:lichnyjkabin}');
		if(!$this->check()) return false;

		$this->Core->setUserPassword($id, $this->values['pwd']);
		$this->refresh('cabinate');
		return true;
	}

	protected function func_userPrefsSet(){
		return $this->userPrefsSet('cabinate');
	}

	protected function func_resendRegMail(){
		/*
			Пересылает письмо для подтверждения регистрации
		*/

		if($this->sendRegConfirmMail($this->User->params['eml'], $this->User->params)) $this->refresh('', '{Call:Lang:core:core:pismootpravl}');
		else $this->refresh('', '{Call:Lang:core:core:vprotsesseot}');
	}



	/***************************************************************************************************************************************************************

																		Прочие параметры

	****************************************************************************************************************************************************************/


	protected function func_captcha(){
		/*
			Картинка капчи
		*/

		$captcha = new captcha($this->values['id']);
		$image = $captcha->getImage();
		$this->Core->setHeader('Content-type', $image->getCT());
		$image->flushImage();
		$this->Core->setFlag('rawOutput');
	}

	protected function func_closeStub(){
		/*
			Заглушка закрытого сайта
		*/

		$this->Core->setTemplateType('system');
		$this->Core->setTempl('close');
	}

	protected function func_setPaginParam(){
		/*
			Устанавливает в сессию определенный параметр показа паджинации
		*/

		$this->Core->sessSet('settingEntryOnPage', $this->values['show']);
		$this->Core->setHeader('Location', $this->values['back']);
		$this->refresh($this->values['back']);
	}

	protected function func_userAccountInfo(){
		/*
			Информация об аккаунте.
			сюда же может вставляться инфа из сессии для этого акка
		*/

		if(empty($this->Core->User->userInfoTemplateParams) || !$this->User->getUserId()) return '';
		$this->setContent(
			$this->Core->readAndReplace($this->Core->getModuleTemplatePath($this->mod).'user_account_info.tmpl', $this, array('params' => $this->Core->User->userInfoTemplateParams)),
			'userAccountInfo'
		);
	}

	protected function func_redirect(){
		/*
			Переадресовывает на некий URL
		*/

		if(empty($this->values['url'])) throw new AVA_Exception('Отсутствует url для перенаправления.');
		$this->redirect2($this->values['url']);
	}
}

?>