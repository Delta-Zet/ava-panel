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


class mod_admin_partner extends gen_partner{


	/********************************************************************************************************************************************************************

																	Настройка отчислений за заказы

	*********************************************************************************************************************************************************************/

	protected function func_sums(){
		/*
			Настройка отчислений партнерам за заказ услуг.
				Вначале считываются все установленные модули биллинга подключенные к тому-же модулю CMS.
				Затем считываются все услуги и тарифы из этих модулей
				Затем для каждого ТП предлагается:
					- Установить процент отчисления партнеру за первый срок
					- Установить процент отчисления партнеру за продление
					- Установить процент отчисления партнеру за модификации
					- Установить процент отчисления партнеру за установку
					- Установить процент отчисления партнеру за другие отчисления связанные с тарифом (штрафы и т.п.)
					- Установить отчислять ли только новому клиенту или всем
					- Максимальная сумма начисляемая партнеру в пределах одного заказа данной услуги
		*/

		$fObj = $this->newForm('sums2', 'sums2', array('caption' => '{Call:Lang:modules:partner:nastrojkiotc2}'));
		$this->sumsMatrix($fObj);
		$this->setContent($this->getFormText($fObj, $this->getEstimations('orders'), array(), 'multiblock'));
	}

	protected function func_sums2(){
		/*
			Устанавливает настройки по тарифам
		*/

		if(!$this->check()) return false;

		$vars = $this->values;
		unset($vars['ava_form_transaction_id'], $vars['mod'], $vars['func']);
		$this->DB->Upd(array('order_pays_estimations', array('vars' => Library::cmpSerialize($vars)), "`name`='orders'"));
		$this->refresh('sums');
	}

	private function sumsMatrix($fObj, $isPersonal = false){
		/*
			Создает матрицу таблицы начисляемых партнерам сумм
		*/

		$y = 0;

		foreach($this->Core->getCoUnitedModulesByType('billing', $this->mod, 'cms') as $i => $e){
			$this->addFormBlock(
				$fObj,
				'partner_sums',
				array('obj' => $this->Core->callModule($i), 'mod' => $i, 'isPersonal' => $isPersonal, 'cur' => $this->getMainCurrencyName()),
				array(),
				'block'.$y
			);

			$fObj->setParam('caption'.$y, $e);
			$y ++;
		}

		return $y;
	}


	/********************************************************************************************************************************************************************

																			Отчисления рефералам

	*********************************************************************************************************************************************************************/

	protected function func_referalSums(){
		/*
			Суммы отчислений рефералам
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'referalSums2',
						'referalSums2',
						array(
							'caption' => '{Call:Lang:modules:partner:nastrojkaotc}'
						)
					),
					'referal_orders',
					array(
						'levels' => $this->Core->getParam('partnerReferals', $this->mod)
					)
				),
				$this->getEstimations('referals'),
				array(),
				'big'
			)
		);
	}

	protected function func_referalSums2(){
		/*
			Устанавливает настройки по тарифам
		*/

		if(!$this->check()) return false;

		$vars = $this->values;
		unset($vars['ava_form_transaction_id'], $vars['mod'], $vars['func']);
		$this->DB->Upd(array('order_pays_estimations', array('vars' => Library::cmpSerialize($vars)), "`name`='referals'"));
		$this->refresh('referalSums');
	}


	/********************************************************************************************************************************************************************

																				Банеры

	*********************************************************************************************************************************************************************/

	protected function func_banners(){
		/*
			Устанавливает настройки по тарифам
		*/

		if(empty($this->values['type_action'])){
			$this->setContent(
				$this->getFormText(
					$this->addFormBlock(
						$this->newForm(
							'bannerDefaults',
							'bannerDefaults',
							array(
								'caption' => '{Call:Lang:modules:partner:otchisleniia4}'
							)
						),
						'banner_defaults'
					),
					$this->getEstimations('banners'),
					array(),
					'big'
				)
			);
		}

		$fields = array();
		$siteGroups = $this->getSiteGroups();

		if(!empty($this->values['type_action'])){
			switch($this->values['type_action']){
				case 'new':
					if(!$this->check()) return false;

					$fields = $this->fieldValues(array('type', 'name', 'text', 'link', 'content', 'code', 'code_gen_type', 'pay_type', 'click_pay', 'view_pay', 'sort', 'show'));
					if($this->values['type'] == 'image') $this->values['content'] = $this->values['image'] = $this->values['image'] ? regExp::replace(_W, _D, $this->values['image']) : $this->values['content'];
					if($this->values['code_gen_type'] == 'auto' || $this->values['code_gen_type'] == 'js') $fields['code'] = $this->Core->readAndReplace($this->Core->getModuleTemplatePath($this->mod).'bannercode.tmpl', $this, $this->values);

					$fields['vars'] = $this->values;
					unset($fields['vars']['type_action'], $fields['vars']['ava_form_transaction_id']);
					foreach($fields as $i => $e) unset($fields['vars'][$i]);

					break;
			}
		}

		$this->typicalMain(
			array(
				'name' => 'banners',
				'caption' => '{Call:Lang:modules:partner:dobavitbaner}',
				'formData' => array(
					'siteGroups' => $siteGroups
				),
				'modifyData' => array(
					'extract' => array('vars')
				),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'text' => '{Call:Lang:modules:partner:imia}',
							'name' => '{Call:Lang:modules:partner:identifikato}',
							'type' => '{Call:Lang:modules:partner:tip}',
							'link' => '{Call:Lang:modules:partner:ssylka}',
							'content' => '{Call:Lang:modules:partner:tekstbanera}',
							'code' => '{Call:Lang:modules:partner:kodbanera}',
							'code_gen_type' => '{Call:Lang:modules:partner:kodgenerirue}',
							'click_pay' => '{Call:Lang:modules:partner:stoimostklik:'.Library::serialize(array($this->getMainCurrencyName())).'}',
							'view_pay' => '{Call:Lang:modules:partner:stoimostpoka:'.Library::serialize(array($this->getMainCurrencyName())).'}',
							'clicks' => '{Call:Lang:modules:partner:vsegoklikov}',
							'views' => '{Call:Lang:modules:partner:vsegopokazov}',
							'show' => '{Call:Lang:modules:partner:dostupnost}'
						),
						'orderFields' => array(
							'text' => '{Call:Lang:modules:partner:imeni}',
							'name' => '{Call:Lang:modules:partner:identifikato4}',
							'type' => '{Call:Lang:modules:partner:tipu}',
							'link' => '{Call:Lang:modules:partner:ssylke}',
							'click_pay' => '{Call:Lang:modules:partner:stoimostikli}',
							'view_pay' => '{Call:Lang:modules:partner:stoimostipok}',
							'clicks' => '{Call:Lang:modules:partner:vsegoklikov1}',
							'views' => '{Call:Lang:modules:partner:vsegopokazov1}'
						),
						'searchMatrix' => array(
							'type' => array(
								'type' => 'select',
								'additional' => array(
									'' => '{Call:Lang:modules:partner:liuboj}',
									'text' => '{Call:Lang:modules:partner:tekstovyj}',
									'image' => '{Call:Lang:modules:partner:graficheskij}'
								)
							),
							'code_gen_type' => array(
								'type' => 'select',
								'additional' => array(
									'' => '{Call:Lang:modules:partner:liubojsposob}',
									'auto' => '{Call:Lang:modules:partner:avtomatiches2}',
									'manual' => '{Call:Lang:modules:partner:vruchnuiu}'
								)
							),
							'click_pay' => array('type' => 'gap'),
							'view_pay' => array('type' => 'gap'),
							'clicks' => array('type' => 'gap'),
							'views' => array('type' => 'gap'),
						),
						'isBe' => array('type' => 1, 'code_gen_type' => 1)
					),
					'form_actions' => array(
						'suspend' => '{Call:Lang:modules:partner:zakryt}',
						'unsuspend' => '{Call:Lang:modules:partner:otkryt}',
						'delete' => '{Call:Lang:modules:partner:udalit}',
					),
					'actions' => array(
						'text' => 'banners&type_action=modify'
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:partner:spisokbanero}'
				),
				'fields' => $fields,
				'formTemplName' => 'big',
				'listTemplName' => 'big',
			)
		);
	}

	protected function func_bannerDefaults(){
		/*
			Устанавливает настройки по тарифам
		*/

		if(!$this->check()) return false;

		$vars = $this->values;
		unset($vars['ava_form_transaction_id'], $vars['mod'], $vars['func']);
		$this->DB->Upd(array('order_pays_estimations', array('vars' => Library::cmpSerialize($vars)), "`name`='banners'"));
		$this->refresh('banners');
	}



	/********************************************************************************************************************************************************************

																				Партнеры

	*********************************************************************************************************************************************************************/

	protected function func_partner_groups(){
		/*
			Группы партнеров
			В пределах группы партнеров можно:
				1. Назначить специальные параметры отчислений за заказы
				2. Назначить специальные условия отчислений за клики и показы
				3. Назначить условия автоматического перехода в группу:
					- Количество рефералов
					- Количество клиентов
					- Количество переходов
					- Сумма зачислений
					- Время которое партнер участвует
					- При регистрации
				4. Назначить банеры к которым имеют доступ только товарищи входящие в группу
				5. Назначить специальные настройки
		*/

		$this->typicalMain(
			array(
				'name' => 'partner_groups',
				'caption' => '{Call:Lang:modules:partner:dobavitgrupp}',
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'text' => '{Call:Lang:modules:partner:imia}',
							'name' => '{Call:Lang:modules:partner:identifikato}',
							'add_auto' => '{Call:Lang:modules:partner:prisvaivaemy}',
							'add_reg' => '{Call:Lang:modules:partner:prisvaivaimu}',
							'add_refs' => '{Call:Lang:modules:partner:prisvaivaemy1}',
							'add_orders' => '{Call:Lang:modules:partner:prisvaivaemy2}',
							'add_clicks' => '{Call:Lang:modules:partner:prisvaivaemy3}',
							'add_pays' => '{Call:Lang:modules:partner:prisvaivaemy4}',
							'add_time' => '{Call:Lang:modules:partner:prisvaivaemy5}',
						),
						'orderFields' => array(
							'text' => '{Call:Lang:modules:partner:imeni}',
							'name' => '{Call:Lang:modules:partner:identifikato4}'
						),
						'searchMatrix' => array(
							'add_auto' => array('type' => 'checkbox'),
							'add_reg' => array('type' => 'checkbox'),
							'add_refs' => array('type' => 'gap'),
							'add_orders' => array('type' => 'gap'),
							'add_clicks' => array('type' => 'gap'),
							'add_pays' => array('type' => 'gap'),
							'add_time' => array('type' => 'gap'),
						)
					),
					'actions' => array(
						'text' => 'partner_groups&type_action=modify',
						'settings' => 'partnerGroupSettings',
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:partner:spisokgruppp}'
				)
			)
		);
	}

	protected function func_partnerGroupSettings(){
		/*
			Специальные настройки отчислений партнерам
		*/

		$groupData = $this->DB->rowFetch(array('partner_groups', array('name', 'text', 'vars'), "`id`='{$this->values['id']}'"));
		$fObj = $this->newForm('partnerGroupSettings2', 'partnerGroupSettings2', array('caption' => '{Call:Lang:modules:partner:nastrojkiotc3:'.Library::serialize(array($groupData['text'])).'}'));
		$this->groupSettingsMatrix($fObj);

		$this->setContent($this->getFormText($fObj, Library::cmpUnserialize($groupData['vars']), array('id' => $this->values['id']), 'multiblock'));
		$this->pathFunc = 'partner_groups';
		$this->funcName = '{Call:Lang:modules:partner:nastrojkiotc3:'.Library::serialize(array($groupData['text'])).'}';
	}

	protected function __ava__groupSettingsMatrix($fObj){
		/*
			Матрица настроек по группам и партнерам
		*/

		$y = $this->sumsMatrix($fObj, true);

		$this->addFormBlock(
			$fObj,
			'partner_banner_pay_settings',
			array(
				'banners' => $this->DB->columnFetch(array('banners', 'text', 'name', "", "`sort`")),
				'siteGroups' => $this->getSiteGroups()
			),
			array(),
			'block'.$y
		);
		$fObj->setParam('caption'.$y, '{Call:Lang:modules:partner:klikiiprosmo}');
		$y ++;

		$this->addFormBlock(
			$fObj,
			'partner_referal_pay_settings',
			array('levels' => $this->Core->getParam('partnerReferals', $this->mod)),
			array(),
			'block'.$y
		);
		$fObj->setParam('caption'.$y, '{Call:Lang:modules:partner:referalskieo}');
		$y ++;

		$this->addFormBlock(
			$fObj,
			'partner_special_settings',
			array(),
			array(),
			'block'.$y
		);
		$fObj->setParam('caption'.$y, '{Call:Lang:modules:partner:nastrojki}');
		return $y;
	}

	protected function func_partnerGroupSettings2(){
		/*
			Устанавливает настройки по тарифам
		*/

		if(!$this->check()) return false;

		$vars = $this->values;
		unset($vars['ava_form_transaction_id'], $vars['mod'], $vars['func'], $vars['id']);
		$this->DB->Upd(array('partner_groups', array('vars' => Library::cmpSerialize($vars)), "`id`='{$this->values['id']}'"));
		$this->refresh('partner_groups');
	}

	protected function func_partners(){
		/*
			Управление партнерами
		*/

		$userGroups = $this->Core->getUserGroups();
		$partnerGroups = $this->getPartnerGroups();

		$this->setContent(
			$this->getListText(
				$this->newList(
					'partners_list',
					array(
						'req' => array('partners', '*'),
						'extraReqs' => array(
							array(
								'req' => array('users', array('group', 'date', 'login', 'code', 'name', 'eml', 'utc', 'comment', 'show')),
								'DB' => $this->Core->DB,
								'unitedFld1' => 'user_id',
								'unitedFld2' => 'id',
								'prefix' => 'user_',
								'search' => array('login' => true, 'name' => true, 'eml' => true, 'code' => true, 'group' => true, 'comment' => true)
							)
						),
						'actions' => array(
							'login' => 'partnerData',
							'stat' => 'partnerStatistic',
							'settings' => 'partnerSettings',
							'sites' => 'partnerSites'
						),
						'form_actions' => array(
							'unsuspend' => '{Call:Lang:modules:partner:dopustitkuch}',
							'banpartner' => '{Call:Lang:modules:partner:zablokirovat}',
							'ban' => '{Call:Lang:modules:partner:zablokirovat1}',
							'unpartner' => '{Call:Lang:modules:partner:ubratizchisl}',
							'delete' => '{Call:Lang:modules:partner:udalit}',
						),
						'action' => 'partnersActions',
						'searchForm' => array(
							'searchFields' => array(
								'user_login' => '{Call:Lang:modules:partner:login}',
								'login' => '{Call:Lang:modules:partner:psevdonim}',
								'user_name' => '{Call:Lang:modules:partner:imiapolzovat}',
								'user_eml' => 'E-mail',
								'user_code' => '{Call:Lang:modules:partner:kodvosstanov}',
								'date' => '{Call:Lang:modules:partner:dataregistat}',
								'user_group' => '{Call:Lang:modules:partner:gruppapolzov}',
								'grp' => '{Call:Lang:modules:partner:gruppapartne}',
								'refered_by' => '{Call:Lang:modules:partner:porekomendat1}',
								'user_comment' => '{Call:Lang:modules:partner:kommentarij}',
								'balance' => '{Call:Lang:modules:partner:balans}',
								'all_pays' => '{Call:Lang:modules:partner:vsegozachisl}',
								'status' => '{Call:Lang:modules:partner:status}',
							),
							'orderFields' => array(
								'date' => '{Call:Lang:modules:partner:dateregistra}',
								'balance' => '{Call:Lang:modules:partner:balansu}',
								'all_pays' => '{Call:Lang:modules:partner:obshchimzach}',
								'refered_by' => '{Call:Lang:modules:partner:psevdonimure}',
								'login' => '{Call:Lang:modules:partner:psevdonimu}',
							),
							'searchMatrix' => array(
								'grp' => array('type' => 'select', 'additional' => Library::array_merge(array('' => '{Call:Lang:modules:partner:vse}'), $partnerGroups)),
								'user_group' => array('type' => 'select', 'additional' => Library::array_merge(array('' => '{Call:Lang:modules:partner:vse}'), $userGroups)),
								'status' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:modules:partner:vse}',
										'0' => '{Call:Lang:modules:partner:nedopushchen}',
										'1' => '{Call:Lang:modules:partner:rabotaiushch}',
										'-1' => '{Call:Lang:modules:partner:zablokirovan2}',
										'-2' => '{Call:Lang:modules:partner:udalennye}',
									)
								),
								'balance' => array('type' => 'gap'),
								'all_pays' => array('type' => 'gap'),
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:spisokpartne}'
					)
				),
				'big'
			)
		);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'addPartner',
						'addPartner',
						array('caption' => '{Call:Lang:modules:partner:dobavitpartn}')
					),
					'add_partner',
					array(
						'groups' => Library::array_merge(array('' => '{Call:Lang:modules:partner:nenaznachat}'), $userGroups),
						'partnerGroups' => Library::array_merge(array('' => '{Call:Lang:modules:partner:nenaznachat}'), $partnerGroups),
					)
				),
				array(),
				array(),
				'big'
			)
		);
	}

	protected function func_addPartner(){
		/*
			Добавление нового партнера
		*/

		if($this->Core->DB->cellFetch(array('users', 'id', "`login`='{$this->values['login']}'"))){
			$this->setError('login', '{Call:Lang:modules:partner:takojloginuz}');
		}
		elseif($this->DB->cellFetch(array('partners', 'id', "`login`='{$this->values['login']}'"))){
			$this->setError('login', '{Call:Lang:modules:partner:takojpsevdon}');
		}

		if($this->values['refered_by'] && !$this->DB->cellFetch(array('partners', 'id', "`login`='{$this->values['refered_by']}'"))){
			$this->setError('refered_by', '{Call:Lang:modules:partner:takogopartne}');
		}

		if(!$this->check()) return false;

		$this->values['date'] = $this->values['date'];
		$userId = $this->Core->callModule('main')->addUser(Library::array_merge(array('show' => 1), $this->fieldValues(array('login', 'eml', 'pwd', 'utc', 'comment', 'name', 'date', 'group'))));

		if(!$userId){
			$this->back('partners', '{Call:Lang:modules:partner:oshibkaneuda}');
			return false;
		}

		$inserts = $this->fieldValues(array('login', 'grp', 'date', 'refered_by', 'status'));
		$inserts['user_id'] = $userId;

		$return = $this->DB->Ins(array('partners', $inserts));
		$this->refresh('partners');
		return $return;
	}

	protected function func_partnerData(){
		/*
			Данные партнера
		*/

		$pData = $this->DB->rowFetch(array('partners', '*', "`id`='{$this->values['id']}'"));
		$this->pathFunc = 'partners';
		$this->funcName = '{Call:Lang:modules:partner:nastrojkipar:'.Library::serialize(array($pData['login'])).'}';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'partnerData2',
						'partnerData2',
						array(
							'caption' => '{Call:Lang:modules:partner:nastrojkipar:'.Library::serialize(array($pData['login'])).'}'
						)
					),
					'partner_data',
					array(
						'partnerGroups' => Library::array_merge(array('' => '{Call:Lang:modules:partner:net}'), $this->getPartnerGroups())
					)
				),
				$pData,
				array('modify' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_partnerData2(){
		/*
			Выставляем параметры партнера
		*/

		return $this->typeIns('partners', $this->fieldValues(array('status', 'grp')), 'partners');
	}

	protected function func_partnerSettings(){
		/*
			Настройка персональных расценок и прочего
		*/

		$pData = $this->DB->rowFetch(array('partners', array('login', 'vars'), "`id`='{$this->values['id']}'"));
		$this->pathFunc = 'partners';
		$this->funcName = 'Специальные настройки для партнера "'.$pData['login'].'"';

		$fObj = $this->newForm('partnerSettings2', 'partnerSettings2', array('caption' => '{Call:Lang:modules:partner:nastrojkiotc4:'.Library::serialize(array($pData['login'])).'}'));
		$this->groupSettingsMatrix($fObj);
		$this->setContent($this->getFormText($fObj, Library::cmpUnserialize($pData['vars']), array('id' => $this->values['id']), 'multiblock'));
	}

	protected function func_partnerSettings2(){
		/*
			Сохраняет персональные настройки партнера
		*/

		if(!$this->check()) return false;
		$vars = $this->values;
		unset($vars['ava_form_transaction_id'], $vars['mod'], $vars['func'], $vars['id']);

		$this->DB->Upd(array('partners', array('vars' => Library::cmpSerialize($vars)), "`id`='{$this->values['id']}'"));
		$this->Core->reauthUserSession($this->getUserByPartnerId($this->values['id']));
		$this->refresh('partners');
	}

	protected function func_partnersActions(){
		if(empty($this->values['entry'])){
			$this->back('partners', '{Call:Lang:modules:partner:neotmechenon}');
			return false;
		}

		$where = $this->getEntriesWhere();
		$show = 1;

		switch($this->values['action']){
			case 'ban':
				$this->Core->DB->Upd(array('users', array('show' => -1), $this->getEntriesWhere($this->DB->columnFetch(array('partners', 'id', 'user_id', $where)))));

			case 'banpartner': $show = -1;
			case 'unsuspend':
				$return = $this->DB->Upd(array('partners', array('status' => $show), $where));
				break;

			case 'delete':
				$this->Core->DB->Del(array('users', $this->getEntriesWhere($this->DB->columnFetch(array('partners', 'id', 'user_id', $where)))));

			case 'unpartner':
				$return = $this->DB->Del(array('partners', $where));
				break;
		}

		$this->refresh('partners');
		return $return;
	}

	protected function func_partnerForm(){
		/*
			Форма регистрации партнеров
		*/

		return $this->formFields('partner_reg_form', array(), array('table' => 'partners'));
	}


	/********************************************************************************************************************************************************************

																				Сайты

	*********************************************************************************************************************************************************************/

	protected function func_sites(){
		/*
			Список сайтов партнера
		*/

		$fields = array();
		if(!empty($this->values['type_action'])){
			if($this->values['type_action'] == 'delete' && $this->values['action'] != 'delete'){
				$this->DB->Upd(array('sites', array('status' => $this->values['action']), $this->getEntriesWhere()));
				$this->refresh('sites');
				return true;
			}
			elseif($this->values['type_action'] == 'new'){
				if(!$this->DB->cellFetch(array('partners', 'id', "`login`='{$this->values['partner_id']}'"))){
					$this->setError('partner_id', '{Call:Lang:modules:partner:takogopartne}');
				}

				$fields = $this->fieldValues(array('name', 'url', 'partner_id', 'grp', 'status', 'sort'));
				if(empty($this->values['modify'])) $fields['date'] = time();
			}
		}

		return $this->typicalMain(
			array(
				'name' => 'sites',
				'caption' => '{Call:Lang:modules:partner:dobavitsajt}',
				'formData' => array(
					'groups' => Library::array_merge(array('' => '{Call:Lang:modules:partner:nenaznachat}'), $this->getSiteGroups()),
					'inAdmin' => true
				),
				'fields' => $fields,
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'name' => '{Call:Lang:modules:partner:imia}',
							'url' => 'URL',
							'partner_id' => '{Call:Lang:modules:partner:psevdonimpar}',
							'status' => '{Call:Lang:modules:partner:status}',
							'grp' => '{Call:Lang:modules:partner:gruppa}',
							'date' => '{Call:Lang:modules:partner:dobavlen}',
							'clicks' => '{Call:Lang:modules:partner:vsegoklikov}',
							'views' => '{Call:Lang:modules:partner:vsegopokazov}',
						),
						'orderFields' => array(
							'name' => '{Call:Lang:modules:partner:imeni}',
							'url' => 'URL',
							'date' => '{Call:Lang:modules:partner:dobavlen1}',
							'clicks' => '{Call:Lang:modules:partner:klikam}',
							'views' => '{Call:Lang:modules:partner:pokazam}',
						),
						'searchMatrix' => array(
							'status' => array(
								'type' => 'select',
								'additional' => array(
									'' => '{Call:Lang:modules:partner:vse}',
									'0' => '{Call:Lang:modules:partner:vozhidaniipr}',
									'1' => '{Call:Lang:modules:partner:rabotaiushch}',
									'-1' => '{Call:Lang:modules:partner:zabanennye}'
								)
							),
							'grp' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:modules:partner:vse}'), $this->getSiteGroups())
							),
							'clicks' => array('type' => 'gap'),
							'views' => array('type' => 'gap'),
						),
						'isBe' => array('status' => 1, 'grp' => 1)
					),
					'form_actions' => array(
						'1' => '{Call:Lang:modules:partner:razreshitrab}',
						'-1' => '{Call:Lang:modules:partner:zabanit}',
						'delete' => '{Call:Lang:modules:partner:udalit}',
					),
					'actions' => array(
						'name' => 'sites&type_action=modify'
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:partner:spisoksajtov}'
				)
			)
		);
	}

	protected function func_site_groups(){
		/*
			Группы сайтов
		*/

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:modules:partner:dobavitgrupp1}',
				'listParams' => array(
					'actions' => array(
						'text' => $this->func.'&type_action=modify'
					),
					'searchForm' => array(
						'searchFields' => array(
							'text' => '{Call:Lang:modules:partner:imia}',
							'name' => '{Call:Lang:modules:partner:identifikato}'
						),
						'orderFields' => array(
							'text' => '{Call:Lang:modules:partner:imeni}',
							'name' => '{Call:Lang:modules:partner:identifikato4}'
						)
					),
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:partner:spisokgrupps}'
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																			Запросы оплаты

	*********************************************************************************************************************************************************************/

	protected function func_payReqs(){
		/*
			Запросы оплаты
		*/

		$this->setContent(
			$this->getListText(
				$this->newList(
					'pay_orders',
					array(
						'req' => array('pay_orders', '*', '', "`date` DESC"),
						'form_actions' => array(
							'1' => '{Call:Lang:modules:partner:pometitispol}',
							'0' => '{Call:Lang:modules:partner:pometitneisp}',
							'-1' => '{Call:Lang:modules:partner:pometitotkaz}'
						),
						'action' => 'payReqsActions',
						'searchForm' => array(
							'searchFields' => array(
								'login' => '{Call:Lang:modules:partner:partner}',
								'date' => '{Call:Lang:modules:partner:zakazan}',
								'payed' => '{Call:Lang:modules:partner:oplachen}',
								'sum' => '{Call:Lang:modules:partner:summa1}',
								'payment' => '{Call:Lang:modules:partner:sposoboplaty}',
								'status' => '{Call:Lang:modules:partner:status}',
								'init' => '{Call:Lang:modules:partner:initsiirovan}'
							),
							'orderFields' => array(
								'date' => '{Call:Lang:modules:partner:datezakaza}',
								'payed' => '{Call:Lang:modules:partner:dateoplaty}',
								'sum' => '{Call:Lang:modules:partner:summe}',
								'login' => '{Call:Lang:modules:partner:psevdonimupa}',
							),
							'searchMatrix' => array(
								'payment' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:partner:vse}'), $this->getPayment())
								),
								'status' => array(
									'type' => 'select',
									'additional' => array('' => '{Call:Lang:modules:partner:vse}', '0' => '{Call:Lang:modules:partner:neobrabotann}', '1' => '{Call:Lang:modules:partner:obrabotannye}', '-1' => '{Call:Lang:modules:partner:otkazannye}')
								),
								'init' => array(
									'type' => 'select',
									'additional' => array('' => '{Call:Lang:modules:partner:vse}', 'a' => '{Call:Lang:modules:partner:adminom}', 'u' => '{Call:Lang:modules:partner:polzovatelem}')
								),
								'payed' => array('type' => 'calendar'),
								'sum' => array('type' => 'gap'),
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:zaprosyplate}'
					)
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																				Статистика

	*********************************************************************************************************************************************************************/

	protected function func_partnerStatistic(){
		/*
			Статистика партнера
		*/

		$filter = "`partner_id`='{$this->values['id']}'";

		$this->setContent(
			$this->Core->readAndReplace(
				$this->Core->getModuleTemplatePath($this->mod).'stat.tmpl',
				$this,
				array(
					'sites' => (float)$this->DB->Count(array('sites', "`status`>=-1 AND ".$filter)),
					'sites_work' => (float)$this->DB->Count(array('sites', "`status`=1 AND ".$filter)),
					'sites_wait' => (float)$this->DB->Count(array('sites', "`status`=0 AND ".$filter)),
					'sites_ban' => (float)$this->DB->Count(array('sites', "`status`=-1 AND ".$filter)),
					'sites_del' => (float)$this->DB->Count(array('sites', "`status`=-2 AND ".$filter)),

					'views' => (float)$this->DB->Count(array('view', $filter)),
					'clicks' => (float)$this->DB->Count(array('click', $filter)),

					'enrolled' => (float)$this->DB->Sum(array('pays', 'sum', $filter)),
					'enrolled_cnt' => (float)$this->DB->Count(array('pays', $filter)),
					'enrolled_view' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='view' AND ".$filter)),
					'enrolled_view_cnt' => (float)$this->DB->Count(array('pays', "`type`='view' AND ".$filter)),
					'enrolled_click' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='click' AND ".$filter)),
					'enrolled_click_cnt' => (float)$this->DB->Count(array('pays', "`type`='click' AND ".$filter)),
					'enrolled_order' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='order' AND ".$filter)),
					'enrolled_order_cnt' => (float)$this->DB->Count(array('pays', "`type`='order' AND ".$filter)),
					'enrolled_referals' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='referals' AND ".$filter)),
					'enrolled_referals_cnt' => (float)$this->DB->Count(array('pays', "`type`='referals' AND ".$filter)),
					'enrolled_admin' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='admin' AND ".$filter)),
					'enrolled_admin_cnt' => (float)$this->DB->Count(array('pays', "`type`='admin' AND ".$filter)),

					'payed' => (float)$this->DB->Sum(array('pay_orders', 'sum', "`status`=1 AND ".$filter)),
					'payed_cnt' => (float)$this->DB->Count(array('pay_orders', "`status`=1 AND ".$filter)),
					'payedWait' => (float)$this->DB->Sum(array('pay_orders', 'sum', "`status`=0 AND ".$filter)),
					'payedWait_cnt' => (float)$this->DB->Count(array('pay_orders', "`status`=0 AND ".$filter)),
					'failPayed' => (float)$this->DB->Sum(array('pay_orders', 'sum', "`status`=-1 AND ".$filter)),
					'failPayed_cnt' => (float)$this->DB->Count(array('pay_orders', "`status`=-1 AND ".$filter)),
				)
			)
		);
	}

	protected function func_stat(){
		/*
			Сводная Статистика
		*/

		$this->setContent(
			$this->Core->readAndReplace(
				$this->Core->getModuleTemplatePath($this->mod).'stat.tmpl',
				$this,
				array(
					'partners' => (float)$this->DB->Count(array('partners', "`status`>=-1")),
					'partners_work' => (float)$this->DB->Count(array('partners', "`status`=1")),
					'partners_wait' => (float)$this->DB->Count(array('partners', "`status`=0")),
					'partners_ban' => (float)$this->DB->Count(array('partners', "`status`=-1")),
					'partners_del' => (float)$this->DB->Count(array('partners', "`status`=-2")),

					'sites' => (float)$this->DB->Count(array('sites', "`status`>=-1")),
					'sites_work' => (float)$this->DB->Count(array('sites', "`status`=1")),
					'sites_wait' => (float)$this->DB->Count(array('sites', "`status`=0")),
					'sites_ban' => (float)$this->DB->Count(array('sites', "`status`=-1")),
					'sites_del' => (float)$this->DB->Count(array('sites', "`status`=-2")),

					'views' => (float)$this->DB->Count(array('view')),
					'clicks' => (float)$this->DB->Count(array('click')),

					'enrolled' => (float)$this->DB->Sum(array('pays', 'sum')),
					'enrolled_cnt' => (float)$this->DB->Count(array('pays')),
					'enrolled_view' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='view'")),
					'enrolled_view_cnt' => (float)$this->DB->Count(array('pays', "`type`='view'")),
					'enrolled_click' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='click'")),
					'enrolled_click_cnt' => (float)$this->DB->Count(array('pays', "`type`='click'")),
					'enrolled_order' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='order'")),
					'enrolled_order_cnt' => (float)$this->DB->Count(array('pays', "`type`='order'")),
					'enrolled_referals' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='referals'")),
					'enrolled_referals_cnt' => (float)$this->DB->Count(array('pays', "`type`='referals'")),
					'enrolled_admin' => (float)$this->DB->Sum(array('pays', 'sum', "`type`='admin'")),
					'enrolled_admin_cnt' => (float)$this->DB->Count(array('pays', "`type`='admin'")),

					'payed' => (float)$this->DB->Sum(array('pay_orders', 'sum', "`status`=1")),
					'payed_cnt' => (float)$this->DB->Count(array('pay_orders', "`status`=1")),
					'payedWait' => (float)$this->DB->Sum(array('pay_orders', 'sum', "`status`=0")),
					'payedWait_cnt' => (float)$this->DB->Count(array('pay_orders', "`status`=0")),
					'failPayed' => (float)$this->DB->Sum(array('pay_orders', 'sum', "`status`=-1")),
					'failPayed_cnt' => (float)$this->DB->Count(array('pay_orders', "`status`=-1")),
				)
			)
		);
	}

	protected function func_clickStat(){
		/*
			Статистика кликов
		*/

		$searchFields = array(
			'partner_id' => '{Call:Lang:modules:partner:partner}',
			'date' => '{Call:Lang:modules:partner:data}',
			'referer' => 'URL',
			'banner' => '{Call:Lang:modules:partner:baner}',
			'ip' => 'IP',
			'sum' => '{Call:Lang:modules:partner:zaplacheno}',
		);

		$searchMatrix = array(
			'banner' => array(
				'type' => 'select',
				'additional' => Library::array_merge(array('' => '{Call:Lang:modules:partner:vse}'), $this->getBanners())
			),
			'sum' => array('type' => 'gap'),
		);

		$p = $this->DB->getPrefix();
		$t1 = $p.'click';
		$t2 = $p.'pays';

		$filter = '';
		$order = 't1.date DESC';

		if(!empty($this->values['in_search'])){
			$filter = 'WHERE '.$this->getListSearchWhere(
				$this->values,
				$searchFields,
				array('partner_id' => 't1', 'date' => 't1', 'referer' => 't1', 'banner' => 't1', 'ip' => 't1', 'sum' => 't2'),
				array(),
				$searchMatrix
			);

			if(!empty($this->values['search_sort'])){
				$order = $this->getListSearchOrder(
					$this->values['search_sort'],
					$this->values['search_direction'],
					array('partner_id' => 't1', 'date' => 't1', 'referer' => 't1', 'banner' => 't1', 'ip' => 't1', 'sum' => 't2')
				);
			}
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					'click_stat',
					array(
						'req' => "SELECT t1.*, t2.date AS pay_date, t2.sum FROM $t1 AS t1
							LEFT JOIN $t2 AS t2 ON t1.id=t2.entry_id AND t2.type='click' $filter ORDER BY $order",
						'searchForm' => array(
							'searchFields' => $searchFields,
							'orderFields' => array(
								'date' => '{Call:Lang:modules:partner:date}',
								'ip' => 'IP',
								'referer' => 'URL',
								'sum' => '{Call:Lang:modules:partner:summe}',
								'partner_id' => '{Call:Lang:modules:partner:psevdonimupa}',
							),
							'searchMatrix' => $searchMatrix
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:perekhodypob}'
					)
				)
			)
		);
	}

	protected function func_viewStat(){
		/*
			Статистика кликов
		*/

		$searchFields = array(
			'partner_id' => '{Call:Lang:modules:partner:partner}',
			'date' => '{Call:Lang:modules:partner:data}',
			'url' => 'URL',
			'banner' => '{Call:Lang:modules:partner:baner}',
			'ip' => 'IP',
			'sum' => '{Call:Lang:modules:partner:zaplacheno}',
		);

		$searchMatrix = array(
			'banner' => array(
				'type' => 'select',
				'additional' => Library::array_merge(array('' => '{Call:Lang:modules:partner:vse}'), $this->getBanners())
			),
			'sum' => array('type' => 'gap'),
		);

		$p = $this->DB->getPrefix();
		$t1 = $p.'view';
		$t2 = $p.'pays';

		$filter = '';
		$order = 't1.date DESC';

		if(!empty($this->values['in_search'])){
			$filter = 'WHERE '.$this->getListSearchWhere(
				$this->values,
				$searchFields,
				array('partner_id' => 't1', 'date' => 't1', 'url' => 't1', 'banner' => 't1', 'ip' => 't1', 'sum' => 't2'),
				array(),
				$searchMatrix
			);

			if(!empty($this->values['search_sort'])){
				$order = $this->getListSearchOrder(
					$this->values['search_sort'],
					$this->values['search_direction'],
					array('partner_id' => 't1', 'date' => 't1', 'url' => 't1', 'banner' => 't1', 'ip' => 't1', 'sum' => 't2')
				);
			}
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					'view_stat',
					array(
						'req' => "SELECT t1.*, t2.date AS pay_date, t2.sum FROM $t1 AS t1
							LEFT JOIN $t2 AS t2 ON t1.id=t2.entry_id AND t2.type='view' $filter ORDER BY $order",
						'searchForm' => array(
							'searchFields' => $searchFields,
							'orderFields' => array(
								'date' => '{Call:Lang:modules:partner:date}',
								'ip' => 'IP',
								'url' => 'URL',
								'sum' => '{Call:Lang:modules:partner:summe}',
								'partner_id' => '{Call:Lang:modules:partner:psevdonimupa}',
							),
							'searchMatrix' => $searchMatrix
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:prosmotryban}'
					)
				)
			)
		);
	}

	protected function func_orderStat(){
		/*
			Статистика кликов
		*/

		$searchFields = array(
			'partner_id' => '{Call:Lang:modules:partner:partner}',
			'date' => '{Call:Lang:modules:partner:data}',
			'object_type' => '{Call:Lang:modules:partner:otchisleniia5}',
			'object_id' => '{Call:Lang:modules:partner:idobekta}',
			'client_id' => '{Call:Lang:modules:partner:idklienta}',
			'sum' => '{Call:Lang:modules:partner:summa1}'
		);

		$searchMatrix = array(
			'object_type' => array(
				'type' => 'select',
				'additional' => array('' => '{Call:Lang:modules:partner:vse}', 'order_services' => '{Call:Lang:modules:partner:otchisleniia3}', 'payment_transactions' => '{Call:Lang:modules:partner:otchisleniia6}')
			),
			'sum' => array('type' => 'gap'),
		);

		$p = $this->DB->getPrefix();
		$t1 = $p.'order';
		$t2 = $p.'pays';

		$filter = '';
		$order = 't1.date DESC';

		if(!empty($this->values['in_search'])){
			$filter = 'WHERE '.$this->getListSearchWhere(
				$this->values,
				$searchFields,
				array('partner_id' => 't1', 'date' => 't1', 'object_type' => 't1', 'object_id' => 't1', 'client_id' => 't1', 'sum' => 't2'),
				array(),
				$searchMatrix
			);

			if(!empty($this->values['search_sort'])){
				$order = $this->getListSearchOrder(
					$this->values['search_sort'],
					$this->values['search_direction'],
					array('partner_id' => 't1', 'date' => 't1', 'url' => 't1', 'banner' => 't1', 'ip' => 't1', 'sum' => 't2')
				);
			}
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					'order_stat',
					array(
						'req' => "SELECT t1.*, t2.date AS pay_date, t2.sum FROM $t1 AS t1
							LEFT JOIN $t2 AS t2 ON t1.id=t2.entry_id AND t2.type='order' $filter ORDER BY $order",
						'searchForm' => array(
							'searchFields' => $searchFields,
							'orderFields' => array(
								'date' => '{Call:Lang:modules:partner:date}',
								'sum' => '{Call:Lang:modules:partner:summe}',
								'object_id' => '{Call:Lang:modules:partner:idobekta}',
								'client_id' => '{Call:Lang:modules:partner:idklienta}',
								'partner_id' => '{Call:Lang:modules:partner:psevdonimupa}',
							),
							'searchMatrix' => $searchMatrix
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:otchisleniia7}'
					)
				)
			)
		);
	}

	protected function func_referalsStat(){
		/*
			Статистика кликов
		*/

		$searchFields = array(
			'partner_id' => '{Call:Lang:modules:partner:partner}',
			'date' => '{Call:Lang:modules:partner:data}',
			'referal_id' => '{Call:Lang:modules:partner:referal}',
			'pay_type' => '{Call:Lang:modules:partner:tipplatezha}',
			'pay_id' => '{Call:Lang:modules:partner:idplatezha}',
			'level' => '{Call:Lang:modules:partner:urovenrefera}',
			'sum' => '{Call:Lang:modules:partner:summa1}'
		);

		$searchMatrix = array(
			'pay_type' => array(
				'type' => 'select',
				'additional' => array('' => '{Call:Lang:modules:partner:vse}', 'click' => '{Call:Lang:modules:partner:otklikov}', 'view' => '{Call:Lang:modules:partner:otbaneropoka}', 'order' => '{Call:Lang:modules:partner:otzakazov}')
			),
			'sum' => array('type' => 'gap'),
		);

		$p = $this->DB->getPrefix();
		$t1 = $p.'referals';
		$t2 = $p.'pays';

		$filter = '';
		$order = 't1.date DESC';

		if(!empty($this->values['in_search'])){
			$filter = 'WHERE '.$this->getListSearchWhere(
				$this->values,
				$searchFields,
				array('partner_id' => 't1', 'date' => 't1', 'referal_id' => 't1', 'pay_type' => 't1', 'pay_id' => 't1', 'level' => 't1', 'sum' => 't2'),
				array(),
				$searchMatrix
			);

			if(!empty($this->values['search_sort'])){
				$order = $this->getListSearchOrder(
					$this->values['search_sort'],
					$this->values['search_direction'],
					array('partner_id' => 't1', 'date' => 't1', 'referal_id' => 't1', 'pay_id' => 't1', 'level' => 't1', 'sum' => 't2')
				);
			}
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					'order_stat',
					array(
						'req' => "SELECT t1.*, t2.date AS pay_date, t2.sum FROM $t1 AS t1
							LEFT JOIN $t2 AS t2 ON t1.id=t2.entry_id AND t2.type='referals' $filter ORDER BY $order",
						'searchForm' => array(
							'searchFields' => $searchFields,
							'orderFields' => array(
								'date' => '{Call:Lang:modules:partner:date}',
								'sum' => '{Call:Lang:modules:partner:summe}',
								'referal_id' => '{Call:Lang:modules:partner:loginurefera}',
								'pay_id' => '{Call:Lang:modules:partner:idplatezha}',
								'level' => '{Call:Lang:modules:partner:urovniurefer}',
								'partner_id' => '{Call:Lang:modules:partner:psevdonimupa}',
							),
							'searchMatrix' => $searchMatrix
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:otchisleniia2}'
					)
				)
			)
		);
	}



	/********************************************************************************************************************************************************************

																				Валюты

	*********************************************************************************************************************************************************************/

	protected function func_currencies(){
		/*
			Валюты
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'currency',
						'currencyNew',
						array(
							'caption' => '{Call:Lang:modules:partner:dobavitvaliu}'
						)
					),
					'currency',
					array(
						'currency' => $this->getMainCurrencyName(),
						'billMods' => $this->Core->getCoUnitedModulesByType('billing', $this->mod, 'cms')
					)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'currency_list',
					array(
						'req' => array( 'currency', '*', '', "`sort`" ),
						'actions' => array(
							'text' => 'currencyData',
							'default' => 'currencyDefault',
							'del' => 'currencyDel'
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:ustanovlenny}'
					)
				)
			)
		);
	}

	protected function func_currencyNew(){
		/*
			Добавляет валюту
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->values['name'] = strtolower($this->values['name']);
		$this->isUniq( 'currency', array('name' => '{Call:Lang:modules:partner:takojidentif}', 'text' => '{Call:Lang:modules:partner:takoenazvani}'), $id);

		$fields = $this->fieldValues(array('name', 'text', 'exchange', 'sort'));
		foreach($this->Core->getCoUnitedModulesByType('billing', $this->mod, 'cms') as $i => $e) $fields['billing_exchanges'][$i] = $this->values['exchange_'.$i];
		return $this->typeIns('currency', $fields, 'currencies');
	}

	protected function func_currencyData(){
		$values = $this->DB->rowFetch(array('currency', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$values['billing_exchanges'] = Library::unserialize($values['billing_exchanges']);
		foreach($this->Core->getCoUnitedModulesByType('billing', $this->mod, 'cms') as $i => $e) $values['exchange_'.$i] = $values['billing_exchanges'][$i];

		$this->typeModify(
			false,
			'currency',
			'currencyNew',
			array(
				'caption' => '{Call:Lang:modules:partner:parametryval}',
				'values' => $values,
				'formData' => array(
					'currency' => $this->getMainCurrencyName(),
					'extra' => '1',
					'billMods' => $this->Core->getCoUnitedModulesByType('billing', $this->mod, 'cms')
				)
			)
		);
	}

	protected function func_currencyDel(){
		/*
			Удаляет специфицированную валюту
		 */

		$id = db_main::Quot($this->values['id']);
		$data = $this->DB->rowFetch(array('currency', array('name', 'default'), "`id`='$id'"));

		if($data['default']){
			$this->back('currency', '{Call:Lang:modules:partner:vynemozheteu}');
			return false;
		}

		if($this->DB->Del(array('currency', "`id`='$id'"))){
			$this->refresh('currencies');
			return true;
		}
		else{
			$this->back('currencies');
			return false;
		}
	}

	protected function func_currencyDefault(){
		/*
			Устанавливает валюту по умолчанию
			Пересчитывает:
				-
		*/

		$this->DB->trStart();
		$this->DB->Upd(array('currency', array('default' => '')));
		$this->DB->Upd(array('currency', array('default' => '1'), "`id`='{$this->values['id']}'"));

		$this->DB->trEnd(true);
		$this->refresh('currencies');
		return true;
	}



	/********************************************************************************************************************************************************************

																		Движение денежныхъ средствъ

	*********************************************************************************************************************************************************************/

	protected function func_pays(){
		/*
			Просмотр движения денежныхъ средствъ
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'pays2',
						'pays2',
						array(
							'caption' => '{Call:Lang:modules:partner:vnestiplatez}'
						)
					),
					'add_pay'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'pays',
					array(
						'req' => array('pays', '*', "", "`date` DESC"),
						'searchForm' => array(
							'searchFields' => array(
								'login' => '{Call:Lang:modules:partner:psevdonimpar}',
								'type' => '{Call:Lang:modules:partner:tipplatezha}',
								'date' => '{Call:Lang:modules:partner:data}',
								'sum' => '{Call:Lang:modules:partner:summa:'.Library::serialize(array($this->getMainCurrencyName())).'}'
							),
							'orderFields' => array(
								'date' => '{Call:Lang:modules:partner:date}',
								'sum' => '{Call:Lang:modules:partner:summe}'
							),
							'searchMatrix' => array(
								'type' => array(
									'type' => 'select',
									'additional' => array(
										'click' => '{Call:Lang:modules:partner:zakliki}',
										'view' => '{Call:Lang:modules:partner:zaprosmotryb}',
										'order' => '{Call:Lang:modules:partner:zazakazy}',
										'referals' => '{Call:Lang:modules:partner:otreferalov}',
										'admin' => '{Call:Lang:modules:partner:vnesennyeadm}'
									)
								),
								'sum' => array('type' => 'gap')
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:spisokzachis}'
					)
				)
			)
		);
	}

	protected function func_pays2(){
		/*
			Собственно внесение платежа
		*/

		if(!$this->DB->cellFetch(array('partners', 'id', "`login`='{$this->values['login']}'"))){
			$this->setError('login', '{Call:Lang:modules:partner:takogopartne}');
		}

		if(!$this->check()) return false;

		$return = $this->setPartnerPay($this->values['login'], 'admin', $this->values['sum'], 0, $this->values['date']);
		$this->refresh('pays');
		return $return;
	}


	/********************************************************************************************************************************************************************

																				Способы оплаты

	*********************************************************************************************************************************************************************/

	protected function func_payments(){
		/*
			Способы оплаты партнерки
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'payments',
						'paymentsNew',
						array(
							'caption' => '{Call:Lang:modules:partner:dobavitsposo}'
						)
					),
					'payments',
					array(
						'currencyList' => $this->getCurrency(),
						'extensions' => Library::array_merge(array('' => '{Call:Lang:modules:partner:neispolzuets}'), $this->getPayExtensions())
					)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'payments_list',
					array(
						'req' => array( 'payments', '*', '', "`sort`" ),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:partner:skryt}',
							'unsuspend' => '{Call:Lang:modules:partner:otkryt}',
							'delete' => '{Call:Lang:modules:partner:udalit}'
						),
						'actions' => array(
							'text' => 'paymentsData',
							'default' => 'paymentsDefault',
							'del' => 'paymentsDel'
						),
						'action' => 'paymentsActions'
					),
					array(
						'caption' => '{Call:Lang:modules:partner:ustanovlenny1}'
					)
				)
			)
		);
	}

	protected function func_paymentsNew(){
		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->isUniq( 'payments', array('name' => '{Call:Lang:modules:partner:takojidentif}', 'text' => '{Call:Lang:modules:partner:takoenazvani}'), $id);

		if($this->values['extension'] && $id){
			$this->values['vars'] = Library::serialize($this->callPaymentExtension($this->values['extension'], 'checkNewPaymentForm', $this->values['name']));
		}

		if(($newId = $this->typeIns('payments', $this->fieldValues(array('name', 'text', 'extension', 'currency', 'show', 'sort', 'vars')), 'payments')) && !$id){
			$this->redirect('paymentsData&id='.$newId);
		}
	}

	protected function func_paymentsData(){
		$values = $this->DB->rowFetch(array('payments', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$values = Library::array_merge($values, Library::unserialize($values['vars']));

		$form = $this->newForm(
			'payments',
			'paymentsNew',
			array(
				'caption' => '{Call:Lang:modules:partner:parametryopl:'.Library::serialize(array($values['text'])).'}'
			)
		);

		$this->addFormBlock(
			$form,
			'payments',
			array(
				'currencyList' => $this->getCurrency(),
				'extensions' => Library::array_merge(array('' => '{Call:Lang:modules:partner:neispolzuets}'), $this->getPayExtensions()),
				'extra' => '1'
			)
		);

		if($values['extension']) $this->callPaymentExtension($values['extension'], 'setNewPaymentForm', $values['name'], array('fObj' => $form, 'values' => $values));
		$this->setContent($this->getFormText($form, $values, array('modify' => $this->values['id']), 'big'));
	}

	protected function func_paymentsActions(){
		return $this->typeActions('payments', 'payments');
	}
}

?>