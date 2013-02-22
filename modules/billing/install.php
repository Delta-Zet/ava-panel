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


class installModulesBilling extends InstallModuleObject implements InstallModuleInterface{

	public function Install(){
		/*
		  Пункты меню админки для CMS
		*/

		$this->createAllTables();
		$this->setAllDefaults($this->obj->values);

		$this->setDefaultPaymentExtensions($this->getDefaultPaymentExtensions($this->obj->values));
		$this->setDefaultSmsExtensions($this->getDefaultSmsExtensions($this->obj->values));
		$this->setDefaultCurrency($this->getDefaultCurrency($this->obj->values));
		$this->setDefaultPayments($this->getDefaultPayments($this->obj->values));
		$this->setDefaultSms($this->getDefaultSms($this->obj->values));

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
		switch(true){
			case !Library::versionCompare('0.0.1.0', $oldVersion):
				$this->iObj->DB->Drop('modify_service_orders');
				$this->iObj->DB->Alter(array('order_entries', array('drop' => array('uniq_id' => ''))));
				$this->iObj->DB->Alter(array('order_services', array('drop' => array('uniq_id' => ''))));

				$this->iObj->Core->DB->Del(array('mail_templates', "`mod`='{$this->prefix}' AND (`name`='newService' OR `name`='newServiceAdmin' OR `name`='newServiceFailAdmin')"));
				$this->iObj->DB->Upd(array('order_services', array('step' => 0), "`step`=-1"));
				$this->iObj->DB->Upd(array('order_services', array('step' => -1), "`step`<0"));
				$this->iObj->DB->Upd(array('order_services', array('step' => 1), "`step`=2"));

			case !Library::versionCompare('0.0.1.6', $oldVersion):
				$this->iObj->DB->Drop(array('suspend_service_periods', 'unsuspend_service_orders', 'suspend_service_orders'));

			case !Library::versionCompare('0.0.1.9', $oldVersion):
				$this->iObj->Core->DB->Del(array('cron', "`module`='{$this->prefix}' AND (`name`='blocking' OR `name`='notifier')"));
				$this->iObj->Core->DB->Del(array('mail_templates', "`mod`='{$this->prefix}' AND `name`='prolongService'"));

			case !Library::versionCompare('0.0.1.13', $oldVersion):
				$this->iObj->Core->DB->Del(array('mail_templates', "`mod`='{$this->prefix}' AND (`name`='deleteService' OR `name`='deleteServiceAdmin')"));
				$this->iObj->Core->DB->Del(array('mail_templates', "`mod`='{$this->prefix}' AND (`name`='balanceMotion' OR `name`='balanceMotionAdmin')"));
		}

		$v = $this->obj->values;
		$v['sites'] = $this->iObj->Core->getModuleSites($this->prefix);
		$this->updateAllTables();

		$this->updateAllDefaults($v);
		$this->setDefaultPaymentExtensions($this->getDefaultPaymentExtensions($v));
		$this->setDefaultSmsExtensions($this->getDefaultSmsExtensions($this->obj->values));

		return true;
	}

	public function checkUpdate($oldVersion, $newVersion){
		return true;
	}

	public function getTables(){
		/*
			Создает таблицы
		*/

		$return['services'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(32)',
				'text' => '',
				'extension' => '',
				'type' => 'VARCHAR(16)',
				'base_term' => '',
				'test_term' => '',
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

		$return['service_extensions'] = array(
			array(
				'id' => '',
				'mod' => '',
				'name' => ''
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
					array('name'),
				)
			)
		);

		$return['connections'] = array(
			array(
				'id' => '',
				'extension' => '',
				'name' => '',
				'text' => '',
				'host' => '',
				'login_host' => '',
				'login' => 'TEXT',
				'pwd' => 'TEXT',
				'comment' => '',
				'vars' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text')
				)
			)
		);

		$return['service_extensions_connect'] = array(
			array(
				'id' => '',
				'mod' => 'VARCHAR(64)',
				'name' => 'VARCHAR(128)',
				'service' => 'VARCHAR(64)',
				'extra' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('service', 'mod'),
					array('service', 'name'),
				)
			)
		);

		$return['server_reply'] = array(
			array(
				'id' => '',
				'date' => '',
				'body' => '',
				'connection_id' => '',				//ибемтиикатор соебинения
				'transaction_id' => '',				//ID транзакции если есть
				'object_type' => '',				//Тип объекта (orders и т.п.)
				'object_id' => 'TEXT',				//id объектов как строка ,1,2,3,4, и т.д.
				'code' => 'INT',					//Внутренний код ошибки
				'description' => ''					//Описание ошибки
			)
		);

		$return['package_groups'] = array(
			array(
				'id' => '',
				'service' => 'VARCHAR(32)',
				'name' => 'VARCHAR(64)',
				'text' => '',
				'main' => 'CHAR(1)',
				'pkg_table_mode' => 'CHAR(1)',
				'hide_if_none' => 'CHAR(1)',
				'compact_if_alike' => 'CHAR(1)',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('service', 'name'),
					array('service', 'text'),
				)
			)
		);

		$return['package_descripts'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(64)',
				'text' => '',
				'type' => '',
				'vars' => '',
				'service' => 'VARCHAR(32)',
				'apkg' => 'INT',
				'aacc' => 'INT',
				'opkg' => 'INT',
				'mpkg' => 'INT',
				'pkg_list' => 'INT',
				'cp' => '',
				'use_if_no_conformity' => 'CHAR(1)',
				'use_if_no_panel' => 'CHAR(1)',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('service', 'name')
				)
			)
		);

		$return['orders'] = array(
			array(
				'id' => '',
				'client_id' => 'INT',
				'date' => '',							//Момент начала заказа
				'ordered' => 'INT',						//Момент подачи заказа на оформление
				'step' => 'INT',						//Шаг заказа (0 при внесении счета (1 шаг заказа), 3 - когда в счете появляется хотябы 1 заказ, 4 - переход к оплате, 5 - начат прием оплаты, 6 - закончен прием оплаты, -1 - отказ)
				'sum' => '',							//Расчетная стоимость заказа в валюте по умолчанию
				'discount' => 'DECIMAL(11,2)',			//Расчетная стоимость скидки
				'total' => 'DECIMAL(11,2)',				//Итого
			)
		);

		$return['payment_transactions'] = array(
			array(
				'id' => '',
				'client_id' => 'INT',
				'object_type' => 'VARCHAR(32)',			//order, complex - тип назначения платежа. При пустом - просто пополнение баланса
				'object_id' => 'INT',					//ID объекта. Если определены object_type и object_id, при окончании транзакции они будут исполнены для этих объектов
				'uniq' => '',							//Если платежная система предполагает какой-либо уникальный идентификатор платежа - он храниться тут
				'status' => 'INT',						//Статус. 0 - оплата не начата, 1 - в стадии оплаты, 2 - оплата завершена
				'date' => '',							//Дата начала оплаты
				'pay' => 'INT',							//Дата поступления оплаты
				'sum' => '',							//Cумма оплаты (в валюте оплаты)
				'currency' => '',						//Валюта оплаты
				'vars' => '',
				'payment' => 'VARCHAR(32)',				//Способ оплаты,
				'payment_type' => 'CHAR(1)'				//s - SMS, в противном случае обычный
			)
		);

		$return['operations'] = array(					//Операции (проведение счета, модификации и т.п.)
			array(
				'id' => '',
				'client_id' => 'INT',
				'object_type' => 'VARCHAR(32)',			//order, complex - тип назначения платежа. При пустом - просто пополнение баланса
				'object_id' => 'INT',					//ID объекта. Если определены object_type и object_id, при окончании транзакции они будут исполнены для этих объектов
				'date' => '',
				'result' => '',
				'log' => 'TEXT'
			),
			array(
				'uni' => array(
					array('object_type', 'object_id')
				)
			)
		);

		$return['payment_log'] = array(
			array(
				'id' => '',
				'pay_id' => 'INT',
				'transaction_id' => 'INT',
				'date' => '',
				'ip' => 'VARCHAR(32)',				//С какого IP был запрос
				'params' => 'TEXT',					//Что отправлено в запросе
				'output' => 'TEXT',					//Что отправлено в ответе
				'answer_status' => 'CHAR(1)',		//Статус ответа (0 - success, 1 - fail)
				'transaction_status' => 'CHAR(1)',	//Статус транзакции при исполнении запроса. 0 - не менялся, 1 - выставлен как в процессе обработки
				'extra_output' => 'TEXT',			//Все что не вошло в основной вывод
				'error' => 'INT',					//Код ошибки: 2 - неопределенная ошибка, 4 - доступ запрещен, 10 - неправильный код, 30 - ошибка разбора запроса, более 100 - прочие ошибки
				'error_msg' => ''					//Текст сообщения об ошибке
			)
		);

		$return['pays'] = array(
			array(
				'id' => '',
				'client_id' => 'INT',
				'service_id' => 'INT',				//ID услуги для которой выполняется операция. Может отсутствовать, если операция без определения услуги
				'object_type' => 'VARCHAR(32)',		//Тип объекта (имя таблицы)
				'object_id' => 'INT',				//ID объекта по которому осуществляется операция
				'date' => '',						//Дата осуществления операции
				'real_date' => 'INT',				//Реальная дата
				'sum' => '',						//Сумма в основной валюте
				'foundation' => '',
				'foundation_type' => ''
			)
		);

		$return['order_services'] = array(
			array(
				'id' => '',
				'service' => '',
				'client_id' => 'INT',
				'ident' => '',
				'package' => '',
				'server' => '',
				'date' => '',							//Момент внесения записи
				'created' => 'INT',						//Момент получения сообщения что услуга удачно создана
				'last_paid' => 'INT',
				'paid_to' => 'INT',
				'price' => 'DECIMAL(11,2)',				//Текущая стоимость продления (без наворотов), если при смене цены принято менять цену для текущих клиентов, она пересчитывается (тип смены 1 и 2), при этом если тип 2, то пересчитывается еще и paid_to
				'modify_price' => 'DECIMAL(11,2)',		//Текущая стоимость продления всех модификаций
				'ind_price' => 'INT(1)',				//Индикатор индивидуального расчета стоимости услуги
				'all_payments' => 'DECIMAL(11,2)',		//Зачислено на оплату услуги
				'history' => 'TEXT',
				'step' => 'TINYINT',					//Состояние: 1 - работает, 0 - заблокирована, -1 - удалена
				'suspend_reason' => 'VARCHAR(16)',		//Причина блокировки или удаления: 'accord' => 'Добровольно', 'term' => 'Истечение срока', 'policy' => 'Нарушение', 'other' => 'Другая причина',
				'suspend_reason_descript' => '',
				'auto_prolong' => 'TINYINT',			//На какой срок осуществляется автопродление
				'auto_prolong_fract' => 'INT(1)',		//Допускается ли дробление срока при автопродлении при недостатке денег
				'vars' => '',							//Настройки инд. тарифа
				'extra' => ''							//Дополнительные данные, например NS-сервера, домен и т.п.
			)
		);

		$return['order_service_terms'] = array(
			array(
				'id' => '',
				'service_id' => 'INT',					//ID услуги которой меняется срок
				'pay_id' => 'INT',						//ID платежа основания для смены срока
				'object_type' => 'VARCHAR(32)',			//Таблица хранящая основание операции
				'object_id' => '',						//ID объекта на основании которого изменен срок
				'date' => '',							//Дата осуществления операции
				'real_date' => 'INT',					//Реальная дата
				'old_term' => 'INT',
				'new_term' => 'INT'
			)
		);

		$return['complex'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'vars' => '',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['complex_orders'] = array(
			array(
				'id' => '',
				'order_id' => 'INT',
				'complex' => ''
			),
			array(
				'uni' => array(
					array('order_id'),
				)
			)
		);

		$return['sms_extensions'] = array(
			array(
				'id' => '',
				'mod' => '',
				'name' => '',
				'sort' => '',
			),
			array(
				'uni' => array(
					array('mod'),
					array('name'),
				)
			)
		);

		$return['sms'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(32)',
				'text' => '',
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

		$return['sms_numbers'] = array(
			array(
				'id' => '',
				'sms' => 'VARCHAR(32)',
				'number' => '',
				'sum' => '',					//Сумма зачисляемая при оплате SMS. Если пусто - определяется автоматически
				'currency' => '',				//Валюта
				'comment' => 'TEXT',
				'vars' => '',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('sms', 'number')
				)
			)
		);

		$return['order_entries'] = array(
			array(
				'id' => '',
				'entry_type' => 'VARCHAR(32)',				//Тип записи: new - новая услуга, prolong - продление, modify - бабло за модификацию, delete - бабло за удаление
				'entry_caption' => '',
				'ident' => '',
				'order_id' => 'INT',
				'client_id' => 'INT',
				'order_service_id' => 'INT',				//ID заказанной услуги (для prolong, new), ID модификации - для modify
				'service' => '',
				'package' => '',
				'date' => '',
				'enrolled' => '',
				'price' => 'DECIMAL(11,2)',					//Цена за ед. расчетного периода без доп. услуг
				'price2' => 'DECIMAL(11,2)',				//Цена за ед. расчетного периода продление
				'prolong_price' => 'DECIMAL(11,2)',			//Цена для продления (не только для автоматизированного)
				'modify_price' => 'DECIMAL(11,2)',			//Цена модификаций за ед. расчетного периода
				'install_price' => 'DECIMAL(11,2)',			//Цена установки
				'modify_install_price' => 'DECIMAL(11,2)',	//Цена установки модификаций
				'sum' => 'DECIMAL(11,2)',					//Суммарно без скидки
				'total' => 'DECIMAL(11,2)',					//Суммарно со скидкой (к списанию)
				'paid_to' => 'INT',
				'discount' => 'DECIMAL(11,2)',				//Расчетная стоимость скидки за весь период
				'discounts' => 'TEXT',						//Описания включенных скидок
				'term' => 'TINYINT',						//Срок (базовых ебениц)
				'test_term' => 'TINYINT',					//Тестовый срок (базовых ебениц)
				'auto_prolong' => 'TINYINT',				//На какой срок осуществляется автопродление
				'auto_prolong_fract' => 'INT(1)',			//Допускается ли дробление срока при автопродлении при недостатке денег
				'ind_price' => 'INT(1)',					//Индикатор индивидуального расчета стоимости услуги
				'promo_code' => '',							//Использованный при заказе промо-код
				'extra' => '',								//Прочие параметры
				'status' => 'INT(1)'						//0,1 - в состоянии заказа, 2 - добавлена, -1 - удалена
			)
		);

		$return['modify_service_main_orders'] = array(
			array(
				'id' => '',
				'date' => '',
				'init' => 'CHAR(1)',										//Инициализировано a | u
				'service' => '',
				'pkg' => '',
				'vars' => ''												//Дополнитеотные параметры
			)
		);

		$return['modify_service_orders'] = array(
			array(
				'id' => '',
				'main_id' => 'INT',
				'service_order_id' => 'INT',
				'modified' => 'INT',
				'status' => 'TINYINT',										//Шаг. 0 - Только начато, 1 => Внесение данных о новом ТП, 2 => Просмотр заявки, 3 => Переход к оплате, 4 - Ожидает выполнения админом, 5 - Начато выполнение, 6 - выполнена, -1 - отменена
				'base_price' => 'DECIMAL(11,2)',							//Базовая цена
				'base_price2' => 'DECIMAL(11,2)',							//Базовая цена
				'base_prolong_price' => 'DECIMAL(11,2)',					//Базовая цена
				'base_modify_price' => 'DECIMAL(11,2)',						//Базовая цена
				'base_install_price' => 'DECIMAL(11,2)',
				'base_install_modify_price' => 'DECIMAL(11,2)',
				'old_paid_to' => 'INT',										//Прежний заказ оплачен до X числа
				'new_paid_to' => 'INT',										//Новый заказ оплачен до
				'old_term_stay' => 'INT',									//Остаток старого срока
				'new_term_stay' => 'INT',									//Остаток нового срока (если balance - совпадает)
				'old_pay_stay' => 'DECIMAL(11,2)',							//Цена старого срока
				'new_calculate' => 'DECIMAL(11,2)',							//Новая расчетная цена
				'new_pay_stay' => 'DECIMAL(11,2)',							//Цена нового срока (если paidto - совпадает)
				'difference' => 'DECIMAL(11,2)',							//Разница в цене old_pay_stay - new_pay_stay
				'install_calculate' => 'DECIMAL(11,2)',						//Расчетная стоимость установки
				'install_price' => 'DECIMAL(11,2)',							//Стоимость установки нового ТП - если включается в срок = 0
				'change_calculate' => 'DECIMAL(11,2)',						//Расчетная стоимость смены пакета
				'change_price' => 'DECIMAL(11,2)',							//Стоимость смены пакета - если включается в срок = 0
				'total' => 'DECIMAL(11,2)',									//К оплате
				'old_vars' => 'TEXT',										//Параметры старого тарифа
				'extra' => ''
			)
		);

		$return['delete_service_orders'] = array(
			array(
				'id' => '',
				'service_order_id' => 'INT',
				'stay' => 'DECIMAL(11,2)',				//Остаточная стоимость
				'delete_price' => 'DECIMAL(11,2)',		//Штраф за удаление
				'total' => 'DECIMAL(11,2)',				//К оплате
				'date' => '',
				'type' => 'VARCHAR(8)',					//Тип accord - добровольно, term - истек срок, policy - нарушение
				'reason' => '',							//Причина удаления (текст просто)
				'status' => 'INT(1)'					//Шаг. 0 - Только внесена, 1 => Начато исполнение, 2 => Исполнена
			)
		);

		$return['transmit_service_orders'] = array(
			array(
				'id' => '',
				'service_order_id' => 'INT',
				'old_client_id' => 'INT',
				'new_client_id' => 'INT',
				'date' => '',
				'status' => 'INT(1)'					//Шаг. 0 - Только внесена, 1 => Начато исполнение, 2 => Исполнена
			)
		);

		$return['suspend_service_orders'] = array(
			array(
				'id' => '',
				'service_order_id' => 'INT',
				'period_id' => 'INT',
				'price' => 'DECIMAL(11,2)',				//Сумма снимаемая за установку на паузу
				'discount' => 'DECIMAL(11,2)',			//Скидка
				'discounts' => 'TEXT',
				'total' => 'DECIMAL(11,2)',				//К оплате
				'date' => '',							//Дата заказа
				'type' => 'VARCHAR(8)',					//Тип accord - добровольно, term - истек срок, policy - нарушение
				'reason' => '',							//Причина блокировки (текст просто)
				'status' => 'INT(1)'					//Шаг. 0 - Только внесена, 1 => Начато исполнение, 2 => Исполнен
			)
		);

		$return['unsuspend_service_orders'] = array(
			array(
				'id' => '',
				'service_order_id' => 'INT',
				'period_id' => 'INT',
				'price' => 'DECIMAL(11,2)',				//Сумма снимаемая за установку на паузу
				'discount' => 'DECIMAL(11,2)',			//Скидка
				'discounts' => 'TEXT',
				'total' => 'DECIMAL(11,2)',				//К оплате
				'date' => '',							//Дата заказа
				'type' => 'VARCHAR(8)',					//Тип accord - добровольно, term - истек срок, policy - нарушение
				'reason' => '',							//Причина удаления (текст просто)
				'status' => 'INT(1)'					//Шаг. 0 - Только внесена, 1 => Начато исполнение, 2 => Исполнен
			)
		);

		$return['suspend_service_periods'] = array(
			array(
				'id' => '',
				'service_order_id' => '',
				'run' => 'INT',						//Начат
				'end' => 'INT',						//Закончен
				'status' => ''						//Статус: 0 - открыт, 1 - закрыт
			)
		);

		$return['order_packages'] = array(
			array(
				'id' => '',
				'service' => 'VARCHAR(32)',
				'name' => 'VARCHAR(32)',
				'text' => '',
				'server_name' => '',
				'server' => '',
				'main_group' => '',
				'groups' => '',
				'show' => '',
				'price' => 'DECIMAL(11,2)',
				'price2' => 'DECIMAL(11,2)',
				'prolong_price' => 'DECIMAL(11,2)',
				'install_price' => 'DECIMAL(11,2)',
				'change_down_price' => 'DECIMAL(11,2)',
				'change_up_price' => 'DECIMAL(11,2)',
				'change_srv_price' => 'DECIMAL(11,2)',
				'change_grp_price' => 'DECIMAL(11,2)',
				'change_modify_price' => 'DECIMAL(11,2)',
				'del_price' => 'DECIMAL(11,2)',
				'pause_start_price' => 'DECIMAL(11,2)',
				'pause_stop_price' => 'DECIMAL(11,2)',
				'currency' => '',
				'terms' => '',
				'prolong_terms' => '',
				'test' => 'INT',
				'max_test_accs' => 'INT',
				'pay_test_install' => 'CHAR(1)',
				'pay_test_modify' => 'CHAR(1)',
				'inner_test' => 'CHAR(1)',
				'fract_prolong' => 'CHAR(1)',
				'vars' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name', 'service'),
					array('text', 'service')
				)
			)
		);

		$return['currency'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(3)',
				'text' => '',
				'exchange' => 'DECIMAL(16,6)',
				'coin' => '',
				'coincount' => 'INT',
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

		$return['currency_changes'] = array(
			array(
				'id' => '',
				'date' => '',
				'old_value' => 'DECIMAL(16,6)',
				'new_value' => 'DECIMAL(16,6)',
				'currency' => '',
				'main_currency' => ''
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
				'comment' => 'TEXT',
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

		$return['clients'] = array(
			array(
				'id' => '',
				'user_id' => 'INT',
				'loyal_level' => '',
				'date' => '',
				'balance' => 'DECIMAL(10,2)',
				'all_payments' => 'DECIMAL(10,2)',
				'all_payed_services' => 'DECIMAL(10,2)'
			),
			array(
				'uni' => array(
					array('user_id')
				)
			)
		);

		$return['loyalty_levels'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(64)',
				'text' => '',
				'add_with_registry' => 'CHAR(1)',			//выставить при регистрации
				'add_with_all_payments' => 'INT',			//по сумме всех зачислений
				'add_with_all_payed_services' => 'INT',		//по объему оплаченных услуг
				'add_with_logic' => 'ENUM("OR", "AND")',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text')
				)
			)
		);

		$return['promocodegroups'] = array(
			array(
				'id' => '',
				'name' => '',
				'code_distrib_style' => 'CHAR(1)',			//Кто имеет право раздавать коды. 0 - только админ. 1 - админ и клиент
				'code_distrib_client_levels' => 'TEXT',		//Уровни клиентов имеющих доступ к нерозданным кодам
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['promocodes'] = array(
			array(
				'id' => '',
				'date' => '',
				'started' => 'INT',			//Когда стартует.
				'actually' => 'INT',		//До какого актуален.
				'group' => 'INT',
				'code_style' => 'CHAR(1)',					//Стиль работы кода. 1 - одноразовые коды, 2 - многоразовые коды
				'code' => ''
			),
			array(
				'uni' => array(
					array('code')
				)
			)
		);

		$return['bonuses'] = array(
			array(
				'id' => '',
				'name' => '',
				'date' => '',
				'start' => 'INT',
				'end' => 'INT',
				'min_sum' => 'DECIMAL(11,2)',
				'bonus' => 'DECIMAL(11,2)',
				'client_loyalty_levels' => 'TEXT',
				'bonus_type' => 'ENUM("percent", "money")',
				'show' => ''
			)
		);

		$return['discounts'] = array(
			array(
				'id' => '',
				'service' => '',
				'name' => '',
				'text' => '',
				'type' => 'VARCHAR(16)',
				'date' => '',
				'start' => 'INT',
				'end' => 'INT',
				'client_loyalty_levels' => 'TEXT',
				'vars' => '',
				'sort' => '',
				'in_pkg_list' => '',
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

	public function getDefaultSettingsCaptions($params){
		return array(
			'billing_clients' => '{Call:Lang:modules:billing:klienty}',
			'billing_services' => 'Услуги',
			'billing_pays' => '{Call:Lang:modules:billing:priemplatezh}',
		);
	}

	public function getDefaultSettings($params){
		$return = array();

		foreach($params['sites'] as $i => $e){
			$return[$i][$this->prefix]['billing_services']['maximumTestAccs'] = array(
				'value' => '1',
				'text' => 'Максимальное число тестовых аккаунтов для одного клиента'
			);

			$return[$i][$this->prefix]['billing_services']['termAutoProlong'] = array(
				'value' => '15',
				'text' => 'За сколько суток до окончания заказа продлить услугу автоматически'
			);

			$return[$i][$this->prefix]['billing_services']['termFinishNotify'] = array(
				'value' => '15,10,5,3,2,1',
				'text' => '{Call:Lang:modules:billing:zaskolkosuto}',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:billing:naprimer}'
					)
				),
			);

			$return[$i][$this->prefix]['billing_services']['termFinishSuspend'] = array(
				'value' => '0',
				'text' => '{Call:Lang:modules:billing:cherezskolko2}',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:billing:esliuslugane}'
					)
				),
			);

			$return[$i][$this->prefix]['billing_services']['termFinishDel'] = array(
				'value' => '20',
				'text' => '{Call:Lang:modules:billing:cherezskolko3}',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:billing:esliuslugadl}'
					)
				),
			);

			$return[$i][$this->prefix]['billing_services']['recalculatePayPrice'] = array(
				'value' => '1',
				'text' => '{Call:Lang:modules:billing:pereschityva2}',
				'type' => 'select',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:billing:dannaianastr}',
						'additional' => array(
							'' => '{Call:Lang:modules:billing:nepereschity}',
							'1' => '{Call:Lang:modules:billing:pereschityva}',
							'2' => '{Call:Lang:modules:billing:pereschityva1}'
						)
					)
				),
			);

			$return[$i][$this->prefix]['billing_services']['autoAddStyle'] = array(
				'value' => '1',
				'text' => 'Автоматически создавать услуги, минуя отображение счета',
				'type' => 'select',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'' => 'Не создавать',
							'1' => 'Если расчетная стоимость равна нулю',
							'2' => 'Если расчетная стоимость менее имеющейся на балансе суммы'
						)
					)
				),
			);

			$return[$i][$this->prefix]['billing_services']['addAccsWithQueue'] = array(
				'text' => '{Call:Lang:modules:billing:sozdavatnovy}',
				'type' => 'checkbox',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:billing:eslivkliuchi}'
					)
				),
			);

			$return[$i][$this->prefix]['billing_services']['addOrdersWithQueue'] = array(
				'text' => '{Call:Lang:modules:billing:priavtomatic}',
				'type' => 'checkbox',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:billing:eslivkliuchi1}'
					)
				),
			);

			$return[$i][$this->prefix]['billing_services']['notifyBillAdmin'] = array(
				'text' => 'Отправлять уведомления от биллинга',
				'type' => 'select',
				'vars' => array(
					'eval' => 'return array("additional" => Library::array_merge(array("" => "Всегда суперадмину"), $GLOBALS["Core"]->getAdminsList()));'
				),
			);

			$return[$i][$this->prefix]['billing_services']['addAccsSuccessMail'] = array(
				'value' => '1',
				'text' => '{Call:Lang:modules:billing:otpravliatad}',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['addAccsFailMail'] = array(
				'value' => '1',
				'text' => '{Call:Lang:modules:billing:otpravliatad1}',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['modifyServiceAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о изменении услуги',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['modifyServiceFailAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о изменении услуги',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['prolongServiceAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о продлении услуг',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['prolongServiceFailAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о продлении если возникли проблемы',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['transmitServiceAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о передаче услуги под другой аккаунт',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['termFinishMail'] = array(
				'text' => 'Уведомлять админа о приближении окончания срока обслуживания',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['suspendServiceAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о блокировании услуги',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['unsuspendServiceAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о разблокировании услуги',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['deleteServiceAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о удалении услуги',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['balanceMotionAdminMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять админа о движении денежных средств',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['balanceMotionUserMail'] = array(
				'value' => '1',
				'text' => 'Уведомлять пользователя о движении денежных средств',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_services']['captcha4test'] = array(
				'value' => '1',
				'text' => '{Call:Lang:modules:billing:ispolzovatca}',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_clients']['addAccsQueueLength'] = array(
				'value' => '3',
				'text' => '{Call:Lang:modules:billing:kolichestvon}',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:billing:esliustanovl}'
					)
				),
			);

			$return[$i][$this->prefix]['billing_clients']['autoConfirmReg'] = array(
				'value' => '1',
				'text' => '{Call:Lang:modules:billing:avtopodtverz}',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_clients']['autoRegClient'] = array(
				'value' => '1',
				'text' => '{Call:Lang:modules:billing:priregistrat}',
				'type' => 'checkbox',
			);

			$return[$i][$this->prefix]['billing_pays']['defaultBank'] = array(
				'value' => 'bank',
				'text' => '{Call:Lang:modules:billing:profilbankov}',
				'type' => 'select',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:modules:billing:eslioplataby}',
					),
					'eval' => 'return array("additional" => $GLOBALS["Core"]->callModule("'.$this->prefix.'")->paymentsByExtension("Bank"));'
				),
			);
		}

		return $return;
	}

	public function getDefaultCronjobs($params){
		/*
			крон-задачи
		*/

		$return[$this->prefix]['notifier'] = array(
			'month' => '*',
			'day' => '*',
			'week' => '*',
			'hour' => '6',
			'minute' => '10',
			'command' => 'return $GLOBALS["Core"]->callModule("'.$this->prefix.'", "sendNotifies");',
			'comment' => '{Call:Lang:modules:billing:otpravkapolz}',
		);

		$return[$this->prefix]['prolong'] = array(
			'month' => '*',
			'day' => '*',
			'week' => '*',
			'hour' => '5',
			'minute' => '10',
			'command' => 'return $GLOBALS["Core"]->callModule("'.$this->prefix.'", "autoProlongAccs");',
			'comment' => 'Автоматическое продление аккаунтов',
		);

		$return[$this->prefix]['blocking'] = array(
			'month' => '*',
			'day' => '*',
			'week' => '*',
			'hour' => '0,3,6,9,12,15,18,21',
			'minute' => '0',
			'command' => 'return $GLOBALS["Core"]->callModule("'.$this->prefix.'", "suspendAccs");',
			'comment' => '{Call:Lang:modules:billing:blokirovkaiu}',
		);

		return $return;
	}

	public function getDefaultMailTemplates($params){
		$return[$this->prefix]['newService'] = array(
			'text' => '{Call:Lang:modules:billing:uvedomlenieo3}',
			'subj' => '{Call:Lang:modules:billing:vampredostav}',
			'body' => '{Call:Lang:modules:billing:zdrastvujten}',
		);

		$return[$this->prefix]['newServiceAdmin'] = array(
			'text' => 'Уведомление админа о создании услуги',
			'subj' => 'Создана услуга "<?=$pkgData[\'service_textname\']?>" для "<?=$eData["ident"]?>"',
			'body' => 'Создана услуга <?=$pkgData[\'service_textname\']?> по тарифу <?=$pkgData[\'text\']?> для <?=$eData["ident"]?>.'."\n\n".'<? echo isset($connectData["login_host"]) ? "Точка доступа: ".$connectData["login_host"]."'."\n".'" : ""; ?>Логин доступа: <?=$eData["ident"]?>'."\n".'Пароль доступа: <?=$eData["extra"]["params1"]["pwd"]?>'."\n\n{log}",
		);

		$return[$this->prefix]['newServiceFailAdmin'] = array(
			'text' => 'Уведомление админа о неудаче в процессе создания услуги',
			'subj' => 'Обнаружена проблема при создании услуги "<?=$pkgData[\'service_textname\']?>" для "<?=$eData["ident"]?>"',
			'body' => 'В процессе создания услуги <?=$pkgData[\'service_textname\']?> по тарифу <?=$pkgData[\'text\']?> для <?=$eData["ident"]?> возникла ошибка.'."\n\n".'{log}',
		);

		$return[$this->prefix]['balanceMotion'] = array(
			'text' => '{Call:Lang:modules:billing:uvedomlenieo4}',
			'subj' => '{Call:Lang:modules:billing:dvizhenieden}',
			'body' => 'Здраствуйте!'."\n\n".
				'На счету вашего аккаунта <?=$uData["login"]?> произошло движение денежных средств. <? if($sum < 0) echo "Была списана сумма {-$sum}$currency ({-$defSum}$defCurrency)"; else echo "Была зачислена сумма {$sum}$currency ({$defSum}$defCurrency)"; ?>. Основание движения: {foundation}',
		);

		$return[$this->prefix]['balanceMotionAdmin'] = array(
			'text' => 'Уведомление админа о движении денежных средств',
			'subj' => 'Движение денежных средств у пользоателя <?=$uData["login"]?>',
			'body' => 'На счету аккаунта <?=$uData["login"]?> произошло движение денежных средств. <? if($sum < 0) echo "Была списана сумма {-$sum}$currency ({-$defSum}$defCurrency)"; else echo "Была зачислена сумма {$sum}$currency ({$defSum}$defCurrency)"; ?>. Основание движения: {foundation}',
		);

		$return[$this->prefix]['modifyService'] = array(
			'text' => 'Уведомление о изменении услуги',
			'subj' => 'Для аккаунта <?=$data[\'ident\']?> изменен тариф',
			'body' => 'Здраствуйте!'."\n\n".'Для аккаунта <?=$data[\'ident\']?> изменен тариф с <?=$data[\'pkgParams\'][\'text\']?> на <?=$extra[\'newPkgData\'][\'text\']?> ',
		);

		$return[$this->prefix]['modifyServiceAdmin'] = array(
			'text' => 'Уведомление админа о изменении услуги',
			'subj' => 'Для аккаунта <?=$data[\'ident\']?> изменен тариф',
			'body' => 'Проведено изменение тарифов на <?=$extra[\'newPkgData\'][\'text\']?> для аккаунтов:'.
				'<? '.
				'foreach($entries as $i => $e){'.
				'$cData = $CURRENT_PARENT_OBJ->getUserByClientId($e[\'client_id\']);'.
				'echo ($result[$i] ? \'Удачно: \' : \'Неудачно: \')."{$e[\'ident\']}, клиент {$cData[\'name\']} ({$cData[\'login\']}), с {$e[\'pkgParams\'][\'text\']}\n";'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['modifyServiceFailAdmin'] = array(
			'text' => 'Уведомление админа о неудаче при изменении услуги',
			'subj' => 'Для аккаунта <?=$data[\'ident\']?> изменен тариф',
			'body' => 'Проведено изменение тарифов на <?=$extra[\'newPkgData\'][\'text\']?> для аккаунтов:'.
				'<? '.
				'foreach($entries as $i => $e){'.
				'$cData = $CURRENT_PARENT_OBJ->getUserByClientId($e[\'client_id\']);'.
				'echo ($result[$i] ? \'Удачно: \' : \'Неудачно: \')."{$e[\'ident\']}, клиент {$cData[\'name\']} ({$cData[\'login\']}), с {$e[\'pkgParams\'][\'text\']}\n";'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['prolongService'] = array(
			'text' => 'Уведомление о продлении услуги',
			'subj' => 'Продлена услуга <?=$CURRENT_PARENT_OBJ->getServiceName($data[\'service\'])?> для <?=$data[\'ident\']?>',
			'body' => 'Здраствуйте, <?=$userData["name"]?>!'."\n\n".
				'Услуга <?=$pkgData["service_textname"]?> для аккаунта <?=$sData["ident"]?> продлена на <? echo Dates::rightCaseTerm($pkgData["base_term"], $eData["term"]);?> '
		);

		$return[$this->prefix]['prolongServiceAdmin'] = array(
			'text' => 'Уведомление админа о продлении услуги',
			'subj' => 'Пользователям продлены услуги',
			'body' => 'Проведено продление услуг для аккаунтов:'."\n\n".
				'<? '.
				'foreach($entries as $i => $e){'.
				'	$cData = $CURRENT_PARENT_OBJ->getUserByClientId($e[\'client_id\']);'.
				'	echo "Услуга {$e[\'pkgParams\'][\'service_textname\']} для {$e[\'ident\']}, продление на ".Dates::rightCaseTerm(\'day\', $values[\'days\']).": ".($result[$i] ? \'удачно\' : \'неудачно\');'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['prolongServiceFailAdmin'] = array(
			'text' => 'Уведомление админа о неудаче при продлении услуги',
			'subj' => 'Пользователям продлены услуги',
			'body' => 'Проведено продление услуг для аккаунтов:'."\n\n".
				'<? '.
				'foreach($entries as $i => $e){'.
				'	$cData = $CURRENT_PARENT_OBJ->getUserByClientId($e[\'client_id\']);'.
				'	echo "Услуга {$e[\'pkgParams\'][\'service_textname\']} для {$e[\'ident\']}, продление на ".Dates::rightCaseTerm(\'day\', $values[\'days\']).": ".($result[$i] ? \'удачно\' : \'неудачно\');'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['transmitService'] = array(
			'text' => 'Уведомление о передаче услуги в другой аккаунт',
			'subj' => 'Передано управление услугой',
			'body' => '<? $cData = $GLOBALS[\'Core\']->getUserParamsById($values[\'new_owner\']); ?>Здраствуйте!'."\n\n".
				'Услуга <?=$CURRENT_PARENT_OBJ->getServiceName($data[\'pkgParams\'][\'service\'])?> для <?=$data[\'ident\']?> передана под управление клиента <?=$cData[\'name\']?> (<?=$cData[\'login\']?>).',
		);

		$return[$this->prefix]['transmitServiceNewClient'] = array(
			'text' => 'Уведомление нового пользователя о передаче ему услуги',
			'subj' => 'Вам передано управление услугой',
			'body' => '<? $cData = $GLOBALS[\'Core\']->getUserParamsById($values[\'new_owner\']); ?>Здраствуйте!'."\n\n".
				'Под ваш биллинг-аккаунт (идентификатор <?=$cData[\'login\']?>) передано управление услугой <?=$CURRENT_PARENT_OBJ->getServiceName($data[\'pkgParams\'][\'service\'])?> для <?=$data[\'ident\']?>',
		);

		$return[$this->prefix]['transmitServiceAdmin'] = array(
			'text' => 'Уведомление админа о передаче услуги в другой аккаунт',
			'subj' => 'Произведена передача услуг под действие другого аккаунта',
			'body' => '<? $cData = $GLOBALS[\'Core\']->getUserParamsById($values[\'new_owner\']); ?>'.
				'Пользователю <?=$cData[\'name\']?> (<?=$cData[\'login\']?>) переданы следующие услуги:'.
				'<? '.
				'foreach($entries as $i => $e){'.
				'  $cData = $CURRENT_PARENT_OBJ->getUserByClientId($e[\'client_id\']);'.
				'  echo "Услуга {$e[\'pkgParams\'][\'service_textname\']} для {$e[\'ident\']}, от клиента {$cData[\'name\']} ({$cData[\'login\']})\n";'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['termFinishService'] = array(
			'text' => '{Call:Lang:modules:billing:uvedomlenieo5}',
			'subj' => '{Call:Lang:modules:billing:uvaszakanchi}',
			'body' => '{Call:Lang:modules:billing:zdrastvujten2}',
		);

		$return[$this->prefix]['termFinishServiceAdmin'] = array(
			'text' => 'Уведомление админа о окончании срока действия услуг',
			'subj' => '{Call:Lang:modules:billing:uvaszakanchi2}',
			'body' => '{Call:Lang:modules:billing:zdrastvujten22}',
		);

		$return[$this->prefix]['suspendService'] = array(
			'text' => '{Call:Lang:modules:billing:uvedomlenieo6}',
			'subj' => 'Блокирование аккаунта <?=$data[\'ident\']?>',
			'body' => 'Здраствуйте!'."\n\n".
				'Услуга <?=$CURRENT_PARENT_OBJ->getServiceName($data[\'service\'])?> аккаунт <?=$data[\'ident\']?> заблокирована.'.
				'<? '.
				'if($values[\'reason\']){'.
				'echo "Причина блокировки: ".$values[\'reason\'];'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['suspendServiceAdmin'] = array(
			'text' => 'Уведомление админа о блокировании услуг',
			'subj' => 'Блокирование аккаунтов',
			'body' => '<? $cData = $GLOBALS[\'Core\']->getUserParamsById($values[\'new_owner\']); ?>Заблокированы следующие услуги:'.
				'<? '.
				'foreach($entries as $i => $e){'.
				'  $cData = $CURRENT_PARENT_OBJ->getUserByClientId($e[\'client_id\']);'.
				'  echo "Услуга {$e[\'pkgParams\'][\'service_textname\']}, аккаунт {$e[\'ident\']}. Основание: {$values[\'reason\']}\n";'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['unsuspendService'] = array(
			'text' => 'Уведомление о разблокировании',
			'subj' => 'Снята блокировка аккаунта <?=$data[\'ident\']?>',
			'body' => 'Здраствуйте!'."\n\n".
				'Услуга <?=$CURRENT_PARENT_OBJ->getServiceName($data[\'service\'])?> аккаунт <?=$data[\'ident\']?> разблокирована.'.
				'<? '.
				'if($values[\'reason\']){'.
				'echo "Причина снятия блокировки: ".$values[\'reason\'];'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['unsuspendServiceAdmin'] = array(
			'text' => 'Уведомление админа о разблокировании услуг',
			'subj' => 'Снятие блокировки аккаунтов',
			'body' => '<? $cData = $GLOBALS[\'Core\']->getUserParamsById($values[\'new_owner\']); ?>Разблокированы следующие услуги:'.
				'<? '.
				'foreach($entries as $i => $e){'.
				'  $cData = $CURRENT_PARENT_OBJ->getUserByClientId($e[\'client_id\']);'.
				'  echo "Услуга {$e[\'pkgParams\'][\'service_textname\']}, аккаунт {$e[\'ident\']}. Основание: {$values[\'reason\']}\n";'.
				'}'.
				' ?>',
		);

		$return[$this->prefix]['deleteService'] = array(
			'text' => 'Уведомление о удалении услуг',
			'subj' => 'Удаление аккаунта <?=$data[\'ident\']?>',
			'body' => 'Здраствуйте!'."\n\n".
				'Аккаунт <?=$sData["ident"]?> услуги <?=$pkgData["service_textname"]?> по тарифу <?=$pkgData["text"]?> был удален. '.
				'<? if($reason = $CURRENT_PARENT_OBJ->getDeleteServiceReasonDescript($dData["reason"])){ ?>Причина удаления: <?=$reason?>.<? } ?>'
		);

		$return[$this->prefix]['deleteServiceAdmin'] = array(
			'text' => 'Уведомление админа о удалении услуг',
			'subj' => 'Удаление аккаунтов',
			'body' => 'Аккаунт <?=$sData["ident"]?> услуги <?=$pkgData["service_textname"]?> по тарифу <?=$pkgData["text"]?> был удален. '.
				'<? if($reason = $CURRENT_PARENT_OBJ->getDeleteServiceReasonDescript($dData["reason"])){ ?>Причина удаления: <?=$reason?>.<? } ?>'
		);

		return $return;
	}

	public function getDefaultPaymentExtensions($params){
		$return = array();
		if(!is_array($params['payExtensions'])) $params['payExtensions'] = array($params['payExtensions']);

		foreach($params['payExtensions'] as $i => $e){
			$GLOBALS['Core']->loadExtension('billing', 'payments/pay'.$e);
			$p = call_user_func(array('pay'.$e, 'getInstallParams'));
			$return[$e] = $p[0];
		}

		return $return;
	}

	public function setDefaultPaymentExtensions($ex, $type = 'Ins'){
		$ex = $this->paramReplaces($ex);
		foreach($ex as $i => $e){
			$this->iObj->DB->$type(array('payment_extensions', array('mod' => $i, 'name' => $e), "`mod`='$i'"));
		}
	}

	public function getDefaultSmsExtensions($params){
		$return = array();
		if(!is_array($params['smsExtensions'])) $params['smsExtensions'] = array($params['smsExtensions']);

		foreach($params['smsExtensions'] as $i => $e){
			$GLOBALS['Core']->loadExtension('billing', 'sms/sms'.$e);
			$p = call_user_func(array('sms'.$e, 'getInstallParams'));
			$return[$e] = $p[0];
		}

		return $return;
	}

	public function setDefaultSmsExtensions($ex, $type = 'Ins'){
		$ex = $this->paramReplaces($ex);
		foreach($ex as $i => $e){
			$this->iObj->DB->$type(array('sms_extensions', array('mod' => $i, 'name' => $e), "`mod`='$i'"));
		}
	}

	public function getDefaultCurrency(){
		return array(
			'rur' => array('text' => '{Call:Lang:modules:billing:rub}', 'exchange' => '1', 'coin' => 'коп.', 'coincount' => 100, 'default' => 1),
			'uah' => array('text' => '{Call:Lang:modules:billing:grn}', 'exchange' => '0.26', 'coin' => 'коп.', 'coincount' => 100),
			'blr' => array('text' => '{Call:Lang:modules:billing:belrub}', 'exchange' => '282'),
			'tng' => array('text' => '{Call:Lang:modules:billing:tenge}', 'exchange' => '4.77', 'coin' => 'тиын', 'coincount' => 100),
			'usd' => array('text' => '$', 'exchange' => '0.032', 'coin' => '&cent;', 'coincount' => 100),
			'eur' => array('text' => '&euro;', 'exchange' => '0.024', 'coin' => '&cent;', 'coincount' => 100),
			'wmz' => array('text' => 'WMZ', 'exchange' => '0.032', 'coincount' => 100),
			'wmr' => array('text' => 'WMR', 'exchange' => '1', 'coincount' => 100),
			'wme' => array('text' => 'WME', 'exchange' => '0.024', 'coincount' => 100),
			'wmu' => array('text' => 'WMU', 'exchange' => '0.26', 'coincount' => 100),
			'wmb' => array('text' => 'WMB', 'exchange' => '282'),
			'wmg' => array('text' => 'WMG', 'exchange' => '0.0006'),
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
			'wmr' => array('text' => '{Call:Lang:modules:billing:webmoneyrkos}', 'currency' => 'wmr', 'extension' => 'WebMoney'),
			'wmz' => array('text' => '{Call:Lang:modules:billing:webmoneyzkos}', 'currency' => 'wmz', 'extension' => 'WebMoney'),
			'wme' => array('text' => '{Call:Lang:modules:billing:webmoneyekos}', 'currency' => 'wme', 'extension' => 'WebMoney'),
			'wmu' => array('text' => '{Call:Lang:modules:billing:webmoneyukos}', 'currency' => 'wmu', 'extension' => 'WebMoney'),
			'wmb' => array('text' => '{Call:Lang:modules:billing:webmoneybkos}', 'currency' => 'wmb', 'extension' => 'WebMoney'),
			'wmg' => array('text' => '{Call:Lang:modules:billing:webmoneygkos}', 'currency' => 'wmg', 'extension' => 'WebMoney'),
			'bank' => array('text' => '{Call:Lang:modules:billing:bankovskijpe}', 'currency' => 'rur', 'extension' => 'Bank'),
			'ym' => array('text' => '{Call:Lang:modules:billing:iandeksdengi}', 'currency' => 'rur', 'extension' => 'Yandex'),
			'sber' => array('text' => '{Call:Lang:modules:billing:kvitantsiejc}', 'currency' => 'rur', 'extension' => 'Sber'),
			'mb' => array('text' => 'MoneyBookers', 'currency' => 'usd', 'extension' => 'MoneyBookers'),
			'paypal' => array('text' => 'PayPal', 'currency' => 'usd', 'extension' => 'PayPal'),
			'rbc' => array('text' => 'RBK.Money', 'currency' => 'rur', 'extension' => 'Rbc'),
			'zp' => array('text' => 'Z-Payment', 'currency' => 'rur', 'extension' => 'ZPayment'),
			'osmp' => array('text' => '{Call:Lang:modules:billing:terminalyosm}', 'currency' => 'rur', 'extension' => 'OSMP'),
			'robox' => array('text' => 'RoboxChange', 'currency' => 'rur', 'extension' => 'Robox'),
			'liq' => array('text' => 'LiqPay', 'currency' => 'usd', 'extension' => 'Liqpay'),
			'ik' => array('text' => '{Call:Lang:modules:billing:interkassa}', 'currency' => 'usd', 'extension' => 'Interkassa'),
			'lsr' => array('text' => '{Call:Lang:modules:billing:vkreditchere}', 'currency' => 'wmr', 'extension' => 'LS'),
			'lsz' => array('text' => '{Call:Lang:modules:billing:vkreditchere1}', 'currency' => 'wmz', 'extension' => 'LS'),
			'lse' => array('text' => '{Call:Lang:modules:billing:vkreditchere2}', 'currency' => 'wme', 'extension' => 'LS'),
			'lsu' => array('text' => '{Call:Lang:modules:billing:vkreditchere3}', 'currency' => 'wmu', 'extension' => 'LS')
		);
	}

	public function setDefaultPayments($pays, $type = 'Ins'){
		$pays = $this->paramReplaces($pays);
		$j = 0;

		foreach($pays as $i => $e){
			$e['name'] = $i;
			$e['sort'] = $j;
			$this->iObj->DB->$type(array('payments', $e, "`name`='$i'"));
			$j ++;
		}
	}

	public function getDefaultSms(){
		return array(
			'a1' => array('text' => 'SMS на короткий номер', 'extension' => 'a1')
		);
	}

	public function setDefaultSms($pays, $type = 'Ins'){
		$pays = $this->paramReplaces($pays);
		$j = 0;

		foreach($pays as $i => $e){
			$e['name'] = $i;
			$e['sort'] = $j;
			$this->iObj->DB->$type(array('sms', $e, "`name`='$i'"));
			$j ++;
		}
	}

	public function getDefaultAdminMenu($params){
		/*
			Дефолтные настройки уровня ядра
		*/

		$return[] = array(
			'text' => $params['text_'.$this->params['name']],
			'pkg' => $this->prefix,
			'submenu' => array(
				array(
					'text' => '{Call:Lang:modules:billing:uslugi}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:billing:uslugi}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=services'),
						array('text' => 'Комплексные заказы', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=complex'),
					)
				),
				array(
					'text' => '{Call:Lang:modules:billing:skidki}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:billing:promokody}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=promoCodeGroups'),
						array('text' => '{Call:Lang:modules:billing:bonusy}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=bonuses'),
					)
				),
				array(
					'text' => '{Call:Lang:modules:billing:podkliucheni}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:billing:soedineniia}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=connections'),
						array('text' => '{Call:Lang:modules:billing:rasshireniia}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=connectExtensions'),
					)
				),
				array(
					'text' => '{Call:Lang:modules:billing:klienty}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:billing:akkaunty}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=clients'),
						array('text' => '{Call:Lang:modules:billing:urovnidoveri}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=loyalLevels'),
					)
				),
				array('text' => '{Call:Lang:modules:billing:zakazy}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=bills'),
				array('text' => '{Call:Lang:modules:billing:platezhi}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=pays'),
				array('text' => '{Call:Lang:modules:billing:valiuty}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=currency'),
				array(
					'text' => '{Call:Lang:modules:billing:platezhnyesi}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:billing:sposobyoplat}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=payments'),
						array('text' => '{Call:Lang:modules:billing:rasshireniia}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=payExtensions'),
						array('text' => 'SMS-платежи', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=sms'),
						array('text' => 'Расширения SMS', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=sms_extensions'),
					)
				),
/*				array(
					'text' => '{Call:Lang:modules:billing:statistika}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:billing:svodnaia}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=stat'),
						array('text' => '{Call:Lang:modules:billing:platezhi}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=payStat'),
						array('text' => '{Call:Lang:modules:billing:uslugi}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=serviceStat'),
						array('text' => '{Call:Lang:modules:billing:klienty}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=clientStat')
					)
				),*/
			)
		);

		return $return;
	}

	public function getDefaultModuleLinks($params){
		return array(
			array(
				'name' => 'packages',
				'text' => '{Call:Lang:modules:billing:tarify}',
				'mod' => $this->prefix,
				'usedCmsLevel' => array('mainmenu', 'menu1')
			),
			array(
				'name' => 'services',
				'text' => '{Call:Lang:modules:billing:uslugi}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=myServices',
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["clientId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu')
			),
			array(
				'name' => 'balance',
				'text' => '{Call:Lang:modules:billing:popolneniesc}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=myBalance',
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["clientId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu')
			),
			array(
				'name' => 'orders',
				'text' => '{Call:Lang:modules:billing:zakazy}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=myBills',
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["clientId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu')
			),
			array(
				'name' => 'documents',
				'text' => '{Call:Lang:modules:billing:dokumenty}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=myDocs',
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->prefix.'"]["clientId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu')
			),
		);
	}

	public function getDefaultUserFormTypes($params){
		return array(
			'person' => 'Физическое лицо',
			'organization' => 'Юридическое лицо',
		);
	}

	public function getDefaultUserFormFields($params){
		return array(
			'organization_name' => array(
				'form_types' => array('organization'),
				'warn' => 'Вы не указали название организации',
				'text' => 'Полное имя организации'
			),
			'short_organization_name' => array(
				'form_types' => array('organization'),
				'warn' => 'Вы не указали название организации',
				'text' => 'Сокращенное имя организации'
			),
			'organization_address' => array(
				'form_types' => array('organization'),
				'warn' => 'Вы не указали юридический адрес',
				'text' => 'Юридический адрес'
			),
			'organization_post_address' => array(
				'form_types' => array('organization'),
				'warn' => 'Вы не указали почтовый адрес',
				'text' => 'Почтовый адрес'
			),
			'inn' => array(
				'form_types' => array('organization'),
				'warn_pattern' => "|\d{10,12}|",
				'warn' => 'Вы не указали ИНН',
				'text' => 'ИНН'
			),
			'kpp' => array(
				'form_types' => array('organization'),
				'warn_pattern' => "|\d{9}|",
				'text' => 'КПП'
			),
		);
	}
}

?>