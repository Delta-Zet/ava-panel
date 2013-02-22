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


class installModulesPartner extends InstallModuleObject implements InstallModuleInterface{

	public function Install(){
		/*
		  Пункты меню админки для CMS
		*/

		$this->createAllTables();
		$this->iObj->DB->Ins(array('order_pays_estimations', array('vars' => '', 'name' => 'orders')));
		$this->iObj->DB->Ins(array('order_pays_estimations', array('vars' => '', 'name' => 'referals')));
		$this->iObj->DB->Ins(array('order_pays_estimations', array('vars' => '', 'name' => 'banners')));

		$this->setAllDefaults($this->obj->values);
		$this->setDefaultPaymentExtensions($this->getDefaultPaymentExtensions($this->obj->values));
		$this->setDefaultCurrency($this->getDefaultCurrency($this->obj->values));
		$this->setDefaultPayments($this->getDefaultPayments($this->obj->values));

		return true;
	}

	public function prepareInstall(){
		return true;
	}

	public function checkInstall(){
		return true;
	}

	public function Uninstall(){
		$this->dropAllTables();
		$this->dropAllDefaults();
		return true;
	}

	public function checkUninstall(){
		return true;
	}

	public function Update($oldVersion, $newVersion){
		$v = $this->obj->values;
		$v['sites'] = $this->iObj->Core->getModuleSites($this->prefix);

		$this->updateAllTables();
		$this->updateAllDefaults($v);
		$this->setDefaultPaymentExtensions($this->getDefaultPaymentExtensions($v));

		return true;
	}

	public function checkUpdate($oldVersion, $newVersion){
		return true;
	}

	public function getTables(){
		/*
			Создает таблицы
		*/

		$return['partners'] = array(
			array(
				'id' => '',
				'user_id' => 'INT',
				'login' => '',
				'grp' => '',
				'refered_by' => '',
				'date' => '',
				'balance' => 'DECIMAL(16,6)',
				'all_pays' => 'DECIMAL(16,6)',
				'status' => 'TINYINT',				//0 - только зареген, 1 - работает, -1 - заблокирован, -2 - удален
				'status_reason' => '',				//Причина выставления статуса: byOwn - добровольно, cheat - мошенник
				'vars' => '',
				'extra' => ''
			),
			array(
				'uni' => array(
					array('user_id'),
					array('login')
				)
			)
		);

		$return['partner_groups'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'add_auto' => 'CHAR(1)',
				'add_reg' => 'CHAR(1)',
				'add_refs' => 'INT',
				'add_orders' => 'INT',
				'add_clicks' => 'INT',
				'add_pays' => 'DECIMAL(12,2)',
				'add_time' => 'INT',
				'add_logic' => 'VARCHAR(3)',
				'vars' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['partner_reg_form'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'type' => '',
				'vars' => '',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['payment_extensions'] = array(
			array(
				'id' => '',
				'mod' => '',
				'name' => '',
				'sort' => '',
			),
			array(
				'uni' => array(
					array('mod'),
					array('name')
				)
			)
		);

		$return['currency'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(3)',
				'text' => '',
				'exchange' => 'DECIMAL(16,6)',
				'billing_exchanges' => '',
				'default' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text')
				)
			)
		);

		$return['payments'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(32)',
				'text' => '',
				'currency' => '',
				'extension' => '',
				'vars' => '',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text')
				)
			)
		);

		$return['sites'] = array(
			array(
				'id' => '',
				'name' => '',
				'url' => '',
				'group' => '',
				'partner_id' => '',
				'date' => '',
				'views' => 'INT',
				'clicks' => 'INT',
				'status' => 'TINYINT',					//0 - ожидает проверки, 1 - проверен, работает, -1 - заблокирован, -2 - удален
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('url')
				)
			)
		);

		$return['site_groups'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text')
				)
			)
		);

		$return['partner_users'] = array(
			array(
				'user_id' => 'INT',
				'partner_id' => '',
			),
			array(
				'uni' => array(
					array('user_id')
				)
			)
		);

		$return['order_pays_estimations'] = array(
			array(
				'name' => 'VARCHAR(8)',
				'vars' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['pay_orders'] = array(
			array(
				'id' => '',
				'partner_id' => '',
				'date' => '',						//Дата заказа
				'payed' => '',						//Дата оплаты
				'sum' => 'DECIMAL(16,7)',
				'payment' => '',
				'vars' => '',
				'status' => 'TINYINT',				//Статус заказа. 0 - в обработке, 1 - выполнен, -1 - отклонен
				'init' => 'CHAR(1)'					//a - Инициирован админом, u - пользователем
			)
		);

		$return['pays'] = array(
			array(
				'id' => '',
				'partner_id' => '',
				'type' => 'VARCHAR(16)',			//view, click, order, referals
				'entry_id' => 'INT',
				'date' => '',
				'sum' => 'DECIMAL(16,7)',
				'descript' => ''
			),
			array(
				'uni' => array(
					array('type', 'entry_id')
				)
			)
		);

		$return['view'] = array(
			array(
				'id' => '',
				'partner_id' => '',
				'date' => '',
				'url' => '',
				'banner' => 'INT',
				'ip' => 'VARCHAR(32)'
			)
		);

		$return['click'] = array(
			array(
				'id' => '',
				'partner_id' => '',
				'date' => '',
				'referer' => '',
				'banner' => 'INT',
				'ip' => 'VARCHAR(32)'
			)
		);

		$return['order'] = array(
			array(
				'id' => '',
				'order_prepare_id' => 'INT',
				'partner_id' => '',
				'date' => '',
				'mod' => '',
				'client_id' => 'INT',
				'object_type' => 'VARCHAR(8)',		//Тип объекта pay или order
				'object_id' => 'INT',				//ID объекта на основании которого произошло зачисление средств, из payment_transactions или order_entries соответственно
				'part' => 'VARCHAR(16)',			//Для зачисляемых по услуге - term, term2, install, modify и т.п.
				'base_sum' => 'DECIMAL(11,2)',		//Базовая сумма (из которой исчисляется платеж)
				'pay' => 'DECIMAL(16,7)',			//К оплате
				'period_start' => 'INT',			//Начало периода платежа
				'period_end' => 'INT',
				'pay_moment' => 'INT',				//Момент оплаты.
				'service' => '',					//Справочно: услуга
				'pkg' => '',						//Справочно: пакет
				'status' => 'TINYINT'				//0 - опционально зачислено, 1 - зачислено, -1 - отказано
			),
			array(
				'uni' => array(
					array('mod', 'object_type', 'object_id', 'period_start', 'part'),
				)
			)
		);

		$return['order_prepares'] = array(
			array(
				'id' => '',
				'partner_id' => '',
				'date' => '',
				'mod' => '',
				'client_id' => 'INT',
				'object_type' => 'VARCHAR(8)',
				'object_id' => 'INT',
				'sum' => 'DECIMAL(16,7)',
				'base_sum' => 'DECIMAL(11,2)',
				'vars' => '',
				'status' => ''						//0 - в процессе, 1 - в обработке, 2 - обработан
			),
			array(
				'uni' => array(
					array('mod', 'object_type', 'object_id'),
				)
			)
		);

		$return['referals'] = array(
			array(
				'id' => '',
				'partner_id' => '',					//Получатель выгоды
				'date' => '',
				'referal_id' => '',					//Реферал принесший выгоду
				'pay_type' => '',					//Тип платежа
				'pay_id' => '',						//ID платежа
				'level' => 'INT',					//Уровень вложенности реферала
			)
		);

		$return['banners'] = array(
			array(
				'id' => '',
				'type' => 'VARCHAR(8)',
				'name' => '',
				'text' => '',
				'link' => '',
				'content' => 'TEXT',
				'code' => 'TEXT',
				'code_gen_type' => 'VARCHAR(8)',
				'click_style' => 'TINYINT',
				'pay_type' => 'VARCHAR(8)',
				'click_pay' => 'DECIMAL(16,6)',
				'view_pay' => 'DECIMAL(16,6)',
				'clicks' => 'INT',
				'views' => 'INT',
				'vars' => '',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text')
				)
			)
		);

		return $return;
	}

	public function getDefaultSettings($params){
		$return = array();

		foreach($params['sites'] as $i => $e){
			$return[$i][$this->prefix]['']['partnerClientMemory'] = array(
				'value' => 'first',
				'text' => '{Call:Lang:modules:partner:zapominatkli}',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'first' => '{Call:Lang:modules:partner:popervomuper}',
							'last' => '{Call:Lang:modules:partner:poposlednemu}',
						)
					)
				),
			);

			$return[$i][$this->prefix]['']['partnerClicksRegStyle'] = array(
				'value' => 2,
				'text' => '{Call:Lang:modules:partner:uchityvatkli}',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'1' => '{Call:Lang:modules:partner:tolkosprover}',
							'2' => '{Call:Lang:modules:partner:sliubykhsajt}',
							'3' => '{Call:Lang:modules:partner:otkudaugodno}',
						)
					)
				),
			);

			$return[$i][$this->prefix]['']['partnerOrderRegStyle'] = array(
				'value' => '3',
				'text' => '{Call:Lang:modules:partner:uchityvatpar}',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'1' => '{Call:Lang:modules:partner:tolkosprover}',
							'2' => '{Call:Lang:modules:partner:sliubykhsajt}',
							'3' => '{Call:Lang:modules:partner:otkudaugodno}',
						)
					)
				),
			);

			$return[$i][$this->prefix]['']['partnerViewReferals'] = array(
				'value' => 2,
				'text' => '{Call:Lang:modules:partner:pokazyvatkli}',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'1' => '{Call:Lang:modules:partner:net}',
							'2' => '{Call:Lang:modules:partner:da}',
							'3' => '{Call:Lang:modules:partner:dairazreshit}',
						)
					)
				)
			);

			$return[$i][$this->prefix]['']['partnerIDStyle'] = array(
				'value' => 2,
				'text' => 'Использовать в качестве идентификатора партнера',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'' => 'Логин пользователя',
							'id' => 'ID пользователя',
							'free' => 'Разрешить свободно вводить любой не занятый идентификатор',
						),
						'comment' => 'Изменение этого параметра актуально только для новых партнеров. Старые будут использовать прежние идентификаторы.'
					)
				)
			);

			$return[$i][$this->prefix]['']['partnerCookieLife'] = array(
				'value' => '180',
				'text' => '{Call:Lang:modules:partner:srokzhiznico}',
			);

			$return[$i][$this->prefix]['']['partnerClickInterval'] = array(
				'value' => '30',
				'text' => '{Call:Lang:modules:partner:minimalnyjin}',
			);

			$return[$i][$this->prefix]['']['partnerReferals'] = array(
				'value' => '1',
				'text' => '{Call:Lang:modules:partner:chislorefera}',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:partner:referalamiia}'
					)
				),
			);

			$return[$i][$this->prefix]['']['partnerBannerFolder'] = array(
				'value' => 'storage/banner/',
				'text' => '{Call:Lang:modules:partner:papkadliakhr}',
			);

			$return[$i][$this->prefix]['']['partnerRegFree'] = array(
				'value' => '1',
				'type' => 'checkbox',
				'text' => '{Call:Lang:modules:partner:registratsii1}',
			);

			$return[$i][$this->prefix]['']['partnerSiteRegFree'] = array(
				'value' => '1',
				'type' => 'checkbox',
				'text' => '{Call:Lang:modules:partner:registratsii}',
			);

			$return[$i][$this->prefix]['']['viewClients'] = array(
				'value' => '1',
				'type' => 'checkbox',
				'text' => 'Партнер может видеть клиентов пришедших по его ссылке',
			);

			$return[$i][$this->prefix]['']['viewReferals'] = array(
				'value' => '1',
				'type' => 'checkbox',
				'text' => 'Партнер может видеть других партнеров (рефералов) пришедших по его ссылке',
			);
		}

		return $return;
	}

	public function getDefaultAdminMenu($params){
		/*
			Дефолтные настройки уровня ядра
		*/

		$return[$this->prefix] = array(
			'text' => $params['text_'.$this->params['name']],
			'pkg' => $this->prefix,
			'submenu' => array(
				array(
					'text' => '{Call:Lang:modules:partner:partnery}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:partner:partnery}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=partners'),
						array('text' => '{Call:Lang:modules:partner:gruppypartne}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=partner_groups'),
						array('text' => '{Call:Lang:modules:partner:formaregistr}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=partnerForm'),
					)
				),
				array(
					'text' => '{Call:Lang:modules:partner:sajty}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:partner:sajty}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=sites'),
						array('text' => '{Call:Lang:modules:partner:gruppysajtov}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=site_groups'),
					)
				),
				array(
					'text' => '{Call:Lang:modules:partner:instrumenty}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:partner:banery}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=banners'),
						array('text' => '{Call:Lang:modules:partner:otchisleniia1}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=sums'),
						array('text' => '{Call:Lang:modules:partner:otchisleniia2}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=referalSums'),
					)
				),
				array(
					'text' => '{Call:Lang:modules:partner:platezhi}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:partner:zachisleniia}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=pays'),
						array('text' => '{Call:Lang:modules:partner:zaprosyoplat}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=payReqs'),
						array('text' => '{Call:Lang:modules:partner:valiuty}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=currencies'),
						array('text' => '{Call:Lang:modules:partner:sposobyoplat}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=payments'),
					)
				),
				array(
					'text' => '{Call:Lang:modules:partner:statistika}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:partner:svodnaia}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=stat'),
						array('text' => '{Call:Lang:modules:partner:klikipobaner}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=clickStat'),
						array('text' => '{Call:Lang:modules:partner:baneropokazy}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=viewStat'),
						array('text' => '{Call:Lang:modules:partner:otchisleniia3}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=orderStat'),
						array('text' => '{Call:Lang:modules:partner:otchisleniia2}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=referalsStat'),
					)
				),
			)
		);

		return $return;
	}

	public function getDefaultModuleLinks($params){
		$return = array(
			array(
				'name' => 'partnerReg',
				'text' => '{Call:Lang:modules:partner:partnerka}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=partnerReg',
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["partnerId"]) ? true : false;',
				'usedCmsLevel' => array('mainmenu', 'menu1')
			),
 			array(
				'name' => 'partnerSites',
				'text' => '{Call:Lang:modules:partner:sajtypartner}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=partnerSites',
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["partnerId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu')
			),
			array(
				'name' => 'partnerBanners',
				'text' => '{Call:Lang:modules:partner:banery}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=partnerBanners',
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["partnerId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu')
			),
			array(
				'name' => 'partnerPay',
				'text' => '{Call:Lang:modules:partner:zaprosoplaty}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=partnerPay',
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["partnerId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu')
			),
			array(
				'name' => 'partnerStat',
				'text' => '{Call:Lang:modules:partner:statistikapa}',
				'mod' => $this->prefix,
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["partnerId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu'),
				'submenu' => array(
					array(
						'name' => 'bannersStat',
						'text' => 'Переходы по банерам',
						'mod' => $this->prefix,
						'url' => 'index.php?mod='.$this->prefix.'&func=bannersStat',
						'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["partnerId"]) ? false : true;',
						'usedCmsLevel' => array('usermenu')
					),
					array(
						'name' => 'clientsStat',
						'text' => 'Клиенты',
						'mod' => $this->prefix,
						'url' => 'index.php?mod='.$this->prefix.'&func=clientsStat',
						'eval' => 'return (empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["partnerId"]) || !$GLOBALS["Core"]->callModule("'.$this->prefix.'")->canView("Clients")) ? false : true;',
						'usedCmsLevel' => array('usermenu')
					),
					array(
						'name' => 'referalsStat',
						'text' => 'Рефералы',
						'mod' => $this->prefix,
						'url' => 'index.php?mod='.$this->prefix.'&func=referalsStat',
						'eval' => 'return (empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["partnerId"]) || !$GLOBALS["Core"]->callModule("'.$this->prefix.'")->canView("Referals")) ? false : true;',
						'usedCmsLevel' => array('usermenu')
					),
				)
			),
		);

		return $return;
	}

	public function getDefaultCronjobs($params){
		/*
			крон-задачи
		*/

		$return[$this->prefix]['partnersOrderPays'] = array(
			'month' => '*',
			'day' => '*',
			'week' => '*',
			'hour' => '6',
			'minute' => '20',
			'command' => 'return $GLOBALS["Core"]->callModule("'.$this->prefix.'", "partnersOrderPays", array(), 0);'."\n",
			'comment' => 'Начисление средств партнерам',
		);

		return $return;
	}

	public function getDefaultMailTemplates($params){
		/*
			Шаблоны писем
		*/

		$return[$this->prefix]['newPartner'] = array(
			'text' => '{Call:Lang:modules:partner:uvedomlenieo}',
			'subj' => '{Call:Lang:modules:partner:vyzaregistri}',
			'body' => '{Call:Lang:modules:partner:zdrastvujten}',
		);

		$return[$this->prefix]['approvePartner'] = array(
			'text' => '{Call:Lang:modules:partner:uvedomlenieo1}',
			'subj' => '{Call:Lang:modules:partner:vashazaiavka}',
			'body' => '{Call:Lang:modules:partner:zdrastvujten1}',
		);

		$return[$this->prefix]['disapprovePartner'] = array(
			'text' => '{Call:Lang:modules:partner:uvedomlenieo2}',
			'subj' => '{Call:Lang:modules:partner:vashazaiavka1}',
			'body' => '{Call:Lang:modules:partner:zdrastvujten2}',
		);

		$return[$this->prefix]['approveSite'] = array(
			'text' => '{Call:Lang:modules:partner:uvedomlenieo3}',
			'subj' => '{Call:Lang:modules:partner:vashazaiavka2}',
			'body' => '{Call:Lang:modules:partner:zdrastvujten3}',
		);

		$return[$this->prefix]['disapproveSite'] = array(
			'text' => '{Call:Lang:modules:partner:uvedomlenieo4}',
			'subj' => '{Call:Lang:modules:partner:vashazaiavka3}',
			'body' => '{Call:Lang:modules:partner:zdrastvujten4}',
		);

		$return[$this->prefix]['deletePartner'] = array(
			'text' => '{Call:Lang:modules:partner:uvedomlenieo5}',
			'subj' => '{Call:Lang:modules:partner:vashpartners}',
			'body' => '{Call:Lang:modules:partner:zdrastvujten5}',
		);

		$return[$this->prefix]['newPartnerPayReq'] = array(
			'text' => '{Call:Lang:modules:partner:zaprosnavypl}',
			'subj' => '{Call:Lang:modules:partner:vamiotpravle}',
			'body' => '{Call:Lang:modules:partner:zdrastvujten6}',
		);

		return $return;
	}

	public function getDefaultPaymentExtensions(){
		return array(
			'WebMoney' => 'WebMoney',
			'Bank' => '{Call:Lang:modules:partner:bankovskijpe}',
		);
	}

	public function setDefaultPaymentExtensions($ex, $type = 'Ins'){
		$ex = $this->paramReplaces($ex);
		foreach($ex as $i => $e){
			$this->iObj->DB->$type(array('payment_extensions', array('mod' => $i, 'name' => $e), "`mod`='$i'"));
		}
	}

	public function getDefaultCurrency(){
		return array(
			'rur' => array('text' => '{Call:Lang:modules:partner:rub}', 'exchange' => '1', 'default' => 1),
			'wmz' => array('text' => 'WMZ', 'exchange' => '0.031'),
			'wmr' => array('text' => 'WMR', 'exchange' => '1'),
			'wme' => array('text' => 'WME', 'exchange' => '0.023'),
			'wmu' => array('text' => 'WMU', 'exchange' => '0.1'),
		);
	}

	public function setDefaultCurrency($cur, $type = 'Ins'){
		$cur = $this->paramReplaces($cur);
		$j = 0;

		foreach($cur as $i => $e){
			$e['name'] = $i;
			$e['sort'] = $j;
			$this->iObj->DB->$type(array('currency', $e, "`name`='$i'"));
			$j ++;
		}
	}

	public function getDefaultPayments(){
		return array(
			'wmr' => array('text' => '{Call:Lang:modules:partner:webmoneyrkos}', 'currency' => 'wmr', 'extension' => 'WebMoney'),
			'wmz' => array('text' => '{Call:Lang:modules:partner:webmoneyzkos}', 'currency' => 'wmz', 'extension' => 'WebMoney'),
			'wme' => array('text' => '{Call:Lang:modules:partner:webmoneyekos}', 'currency' => 'wme', 'extension' => 'WebMoney'),
			'wmu' => array('text' => '{Call:Lang:modules:partner:webmoneyukos}', 'currency' => 'wmu', 'extension' => 'WebMoney'),
			'bank' => array('text' => '{Call:Lang:modules:partner:bankovskijpe}', 'currency' => 'rur', 'extension' => 'Bank'),
		);
	}

	public function setDefaultPayments($pays, $type = 'Ins'){
		$pays = $this->paramReplaces($pays);
		$j = 0;

		foreach($pays as $i => $e){
			$e['name'] = $i;
			$e['sort'] = $j;
			$e['show'] = 1;
			$this->iObj->DB->$type(array('payments', $e, "`name`='$i'"));
			$j ++;
		}
	}
}

?>