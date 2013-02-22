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


class mod_billing extends gen_billing{


	/********************************************************************************************************************************************************************

																Просмотр списка тарифов

	*********************************************************************************************************************************************************************/

	protected function func_packages(){
		/*
			Создает список всех тарифов с расположением ТП горизонтально, параллельно верхнему краю.
			Должна уметь выдавать его с расположением названий по горизонтали, по вертикали и блоками
			Get-запрос должен устанавливать:
				1. Идентификатор услуги *
				2. Идентификатор группы (если не установлено, ищет во всех)
				3. Список тарифов как массив (если установлен, выводит только их)
				4. Лимит
				5. Валюту расчета
				6. Сортировку
				7. Коэфициент цены

			Тариф делится на:
				1. Кнопка заказа и кнопка теста
				2. Блок цен
				3. Тестовый срок (если есть хотябы для 1 ТП)
				4. Минимальный срок заказа
				5. Блок описания

			Образует массив:
				$list [ИмяБлока] [НомерСтрокиВблоке] [values] [ИмяТП] => Значение
													 [block] => Дублирует имя блока
													 [linecapt] => Название параметра

			После обработки объект биллинга передается в объект расширения для услуги, функцию packagesDescript, которая дополняет список своими параметрами, что-то
			возможно удаляет.

			После этого массив отправляется на генерацию
		*/

		$service = db_main::Quot($this->values['service']);
		$grp = empty($this->values['grp']) ? '' : db_main::Quot($this->values['grp']);
		$pkg = empty($this->values['pkg']) ? '' : db_main::Quot($this->values['pkg']);

		$data = $this->serviceData($service);
		if($data['show'] != '1') throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:takojuslugin}', 404);

		$meta = '{Call:Lang:modules:billing:tarify1:'.Library::serialize(array($data['text'])).'}';
		$wh = "";

		if($grp){
			if(!$grpData = $this->DB->rowFetch(array('package_groups', '*', "`name`='$grp'"))){
				throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:dliaehtojgru}', 404);
			}

			$wh .= " AND (t1.`groups` REGEXP(',$grp,') OR t1.`main_group`='$grp')";
			$meta = $grpData['text'];
		}

		if($pkg){
			$wh .= " AND t1.`name`='$pkg'";
			$meta = empty($grpData['text']) ? '' : $grpData['text'];
		}

		$this->setMeta($meta);
		$list = array();
		$prices = array();

		$descripts = array();
		$pkgsInd = array();
		$packages = array();

		$t1 = $this->DB->getPrefix().'order_packages';
		$t2 = $this->DB->getPrefix().'packages_'.$service;
		$dbObj = $this->DB->Req("SELECT t1.*, t2.* FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.`id`=t2.`package_id`
			WHERE t1.`service`='$service'{$wh} AND t1.`show` ORDER BY `sort`");

		while($r = $dbObj->Fetch()){
			$vars = Library::unserialize($r['vars']);
			if(!$this->pkgIsOrdered($service, $r['name'])) continue;
			$terms = RegExp::Split(',', $r['terms']);

			$list['caption']['0']['values'][$r['name']] = $r['text'];
			$list['caption']['0']['block'] = 'caption';

			if($data['type'] != 'onetime'){
				asort($terms, SORT_NUMERIC);

				$list['test']['0']['values'][$r['name']] = $r['test'] ? Dates::rightCaseTerm($data['test_term'], $r['test']) : '';
				$list['test']['0']['block'] = 'test';
				$list['test']['0']['linecapt'] = '{Call:Lang:modules:billing:testovyjsrok1}';

				$list['term']['0']['values'][$r['name']] = Dates::rightCaseTerm($data['base_term'], $terms['0']);
				$list['term']['0']['block'] = 'term';
				$list['term']['0']['linecapt'] = '{Call:Lang:modules:billing:minimalnyjsr}';
			}

			$list['order']['0']['block'] = 'order';
			$list['order']['0']['values'][$r['name']] = _D.'index.php?mod='.$this->mod.'&func=order&service='.$service.'&pkg_'.$service.'='.$r['name'];

			$prices[$r['name']] = array(
				'price' => $r['price'],
				'cur' => $this->currencyParams($r['currency']),
				'prolong_price' => $r['prolong_price'],
				'install_price' => $r['install_price'],
				'terms' => $terms
			);

			$descripts[$r['name']] = $vars['params'];
			$pkgsInd[$r['name']] = $vars['extraDescript'];
			$packages[$r['name']] = $r['text'];
		}

		if(!$list) throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:nenajdenonio}', 404);
		$list['descript'] = $this->getDescript($descripts, $pkgsInd, $service, $grp, $data['extension'], $extra);
		$list = Library::array_merge($list, $this->getPriceList($prices, $data['base_term']));

		$list = $this->insertStartTerms($service, $list);
		$list = $this->insertDiscounts($service, $list);
		if(isset($list['calc'])) ksort($list['calc']);

		$lObj = $this->newList(
			'packages',
			array(
				'arr' => array()
			),
			array(
				'caption' => ''
			),
			$this->Core->getModuleTemplatePath($this->mod).'packages.tmpl'
		);

		$lObj->setParam('cols', count($list['caption']['0']['values']));
		$lObj->setParam('rows', count($list));
		$lObj->readTemplates();

		$mode = empty($grpData['pkg_table_mode']) ? $data['pkg_table_mode'] : $grpData['pkg_table_mode'];
		$hidePkgSettingsIfNone = empty($grp) ? $data['hide_if_none'] : $grpData['hide_if_none'];
		$compactPkgSettingsIfAlike = empty($grp) ? $data['compact_if_alike'] : $grpData['compact_if_alike'];

		$this->callServiceObj('setPkgsListEntriesPreset', $service, array('lObj' => $lObj, 'list' => &$list, 'group' => $grp, 'pkgs' => $packages));
		if($mode == 'v') $this->getPkgTblV($lObj, $list, $hidePkgSettingsIfNone, $compactPkgSettingsIfAlike, $extra);
		else $this->getPkgTblH($lObj, $list, $hidePkgSettingsIfNone, $compactPkgSettingsIfAlike, $extra);

		$this->callServiceObj('setPkgsListEntries', $service, array('lObj' => $lObj, 'group' => $grp, 'pkgs' => $packages));
		$this->setContent($this->getListText($lObj, 'list'.$mode));
	}

	protected function __ava__getPkgTblH($lObj, $list, $hidePkgSettingsIfNone, $compactPkgSettingsIfAlike, $extra){
		/*
			Строiтъ таблiцу съ горизонтальнiмъ расположенiемъ шапки тарiфовъ
		*/

		foreach($list as $i => $e){
			if($hidePkgSettingsIfNone || $i == 'order'){
				foreach($e as $i1 => $e1){
					$issetValue = false;

					foreach($e1['values'] as $i2 => $e2){
						if($e2){
							$issetValue = true;
							break;
						}
					}

					if(!$issetValue) unset($e[$i1]);
				}
			}

			foreach($e as $i1 => $e1){
				if((empty($extra[$i1]['pkg_list_group']) && $compactPkgSettingsIfAlike) || (!empty($extra[$i1]['pkg_list_group']) && $extra[$i1]['pkg_list_group'] == 'group')){
					$prev = false;

					foreach($e1['values'] as $i2 => $e2){
						if($prev === false) $prev = $e2;
						elseif($prev != $e2) continue 2;
					}

					$e[$i1]['values'] = $prev;
				}
			}

			$lObj->setParam($i, $lObj->getEntries($e, 0, 'packagesh'));
		}
	}

	protected function __ava__getPkgTblV($lObj, $list, $hidePkgSettingsIfNone, $compactPkgSettingsIfAlike, $extra){
		/*
			Строiтъ таблiцу съ вертiкальнiмъ расположенiемъ шапки тарiфовъ
		*/

		$entries = array();
		$caption = array();
		$isset = array();

		foreach($list as $i => $e){
			foreach($e as $i1 => $e1){
				$caption['entry'][$i][$i1] = empty($e1['linecapt']) ? '' : $e1['linecapt'];
				foreach($e1['values'] as $i2 => $e2){
					$entries['entry'.$i2][$i][$i1] = $e2;
					if($e2) $isset[$i][$i1] = true;
				}
			}
		}

		if($hidePkgSettingsIfNone){
			foreach($entries as $i => $e){
				foreach($e as $i1 => $e1){
					foreach($e1 as $i2 => $e2){
						if(empty($isset[$i1][$i2])){
							unset($entries[$i][$i1][$i2]);
							unset($caption['entry'][$i1][$i2]);
						}
					}
				}
			}
		}

		$lObj->setParam('caption', $lObj->getEntries($caption, 0, 'packagesv_caption'));
		$lObj->setParam('list', $lObj->getEntries($entries, 0, 'packagesv'));
	}

	protected function __ava__getPriceList($data, $baseTerm){
		/*
			Принимает массив цен и скидок на заказы и на установку. В зависимости от этого выполняет расчет
			Возвращает:

			[Строка] [ИмяТП] => Цена
		 */

		$return['price'] = array();
		$return['calc'] = array();

		if($baseTerm){
			$return['price'][0]['linecapt'] = '{Call:Lang:modules:billing:tsena}';
			$return['price'][100]['linecapt'] = '{Call:Lang:modules:billing:tsenaprodlen1}';
		}
		$return['price'][200]['linecapt'] = '{Call:Lang:modules:billing:ustanovka}';

		$instEmpty = true;
		$prolEmpty = true;
		$calcEmpty = true;

		foreach($data as $i => $e){
			if($baseTerm){
				$return['price'][0]['values'][$i] = $e['price'] > 0 ? Library::humanCurrency($e['price']).' '.$e['cur']['text'] : '';
				$return['price'][100]['values'][$i] = $e['prolong_price'] > 0 ? Library::humanCurrency($e['prolong_price']).' '.$e['cur']['text'] : '';
				if($e['prolong_price'] != $e['price']) $prolEmpty = false;
			}

			$return['price'][200]['values'][$i] = $e['install_price'] > 0 ? Library::humanCurrency($e['install_price']).' '.$e['cur']['text'] : '';
			if($e['install_price'] > 0){
				$instEmpty = false;
				$calcEmpty = false;
			}

			if($baseTerm){
				$return['calc'][10]['values'][$i] = Library::humanCurrency($e['price'] + $e['install_price']).' '.$e['cur']['text'];
				$return['calc'][10]['linecapt'] = '{Call:Lang:modules:billing:itogozapervy:'.Library::serialize(array(Dates::termsListVars($baseTerm, 0))).'}';

				for($i1 = 1; $i1 < $e['terms']['0']; $i1 ++){
					if(isset($return['calc'][$i1 * 10]['values'][$i])) $return['calc'][$i1 * 10]['values'][$i] = '';
				}
			}
		}

		if($instEmpty) unset($return['price'][200]);
		if($prolEmpty) unset($return['price'][100]);
		if($calcEmpty) unset($return['calc']);

		return $return;
	}

	protected function __ava__insertStartTerms($service, $list){
		/*
			Стартовые сроки заказа
		*/

		if(isset($list['calc'])){
			$startTerms = array();
			foreach($list['calc'][10]['values'] as $i => $e){
				$pkgData = $this->serviceData($service, $i);
				if($pkgData['terms'][0] != 1) $startTerms[$pkgData['terms'][0]] = $pkgData['terms'][0];
			}

			foreach($startTerms as $i => $e){
				foreach($list['calc'][10]['values'] as $i1 => $e1){
					$pkgData = $this->serviceData($service, $i1);
					$list['calc'][9 + $i]['values'][$i1] = $pkgData['install_price'] + $pkgData['price'] + ($pkgData['prolong_price'] * ($i - 1));
					$list['calc'][9 + $i]['values'][$i1] .= ' '.$this->currencyName($pkgData['currency']);
					if(!isset($list['calc'][9 + $i]['linecapt'])) $list['calc'][9 + $i]['linecapt'] = 'За '.Dates::rightCaseTerm($pkgData['base_term'], $i);
				}
			}
		}

		return $list;
	}

	protected function __ava__insertDiscounts($service, $list){
		/*
			Вставляет данные о скидках в таблицу
		*/

		$discounts = array();
		foreach($this->discountsByType('term', $service) as $i => $e){
			if(!empty($e['in_pkg_list'])){
				foreach($e['vars']['discounts'] as $i1 => $e1){
					$discounts[$i1][] = $e;
				}
			}
		}

		if(isset($list['calc'])){
			foreach($list['calc'][10]['values'] as $i => $e){
				$pkgData = $this->serviceData($service, $i);

				foreach($discounts as $i1 => $e1){
					if(!in_array($i1, $pkgData['terms'])){
						$list['calc'][9 + $i1]['values'][$i] = '';
						continue;
					}

					$d1 = $d2 = $d3 = 0;

					foreach($e1 as $i2 => $e2){
						if(!empty($e2['vars']['pkgs'][$i])){
							if(!empty($e2['vars']['basic_type']['install'])) $d1 += $this->getTermDiscount($i1, $e2['vars']['discounts']);
							if(!empty($e2['vars']['basic_type']['term'])) $d2 += $this->getTermDiscount($i1, $e2['vars']['discounts']);
							if(!empty($e2['vars']['basic_type']['term2'])) $d3 += $this->getTermDiscount($i1, $e2['vars']['discounts']);
						}
					}

					if($d1 > 100) $d1 = 100;
					if($d2 > 100) $d2 = 100;
					if($d3 > 100) $d3 = 100;

					$d1 = 1 - ($d1 / 100);
					$d2 = 1 - ($d2 / 100);
					$d3 = 1 - ($d3 / 100);

					$list['calc'][9 + $i1]['values'][$i] = ($pkgData['install_price'] * $d1) + ($pkgData['price'] * $d2) + ($pkgData['prolong_price'] * $d3 * ($i1 - 1));
					$list['calc'][9 + $i1]['values'][$i] .= ' '.$this->currencyName($pkgData['currency']);
					if(!isset($list['calc'][9 + $i1]['linecapt'])) $list['calc'][9 + $i1]['linecapt'] = 'За '.Dates::rightCaseTerm($pkgData['base_term'], $i1);
				}
			}
		}

		return $list;
	}

	protected function getDescript($descripts, $pkgsInd, $service, $grp, $extension, &$extra = array()){
		/*
			Создает часть тарифа которая отвечает за описание
		 */

		$return = array();
		$y = 10;

		$dbObj = $this->DB->Req(array('package_descripts', array('text', 'name', 'type', 'vars', 'pkg_list'), "`service`='".db_main::Quot($service)."' AND `pkg_list` AND `show` ORDER BY `sort`"));
		while($r = $dbObj->Fetch()){
			$r['vars'] = Library::unserialize($r['vars']);
			$j = Library::getEmptyIndex($return, $y * (empty($r['vars']['extra']['pkg_list_sort']) ? (empty($r['vars']['extra']['pkg_list_sort_'.$grp]) ? 1 : $r['vars']['extra']['pkg_list_sort_'.$grp]) : $r['vars']['extra']['pkg_list_sort']), 0.001);
			$return[$j] = array();

			foreach($descripts as $i => $e){
				$dtParams = array();
				if($r['pkg_list'] == 1){
					$dtParams = array(
						'pkg_list_value' => empty($r['vars']['extra']['pkg_list_value']) ? '' : $r['vars']['extra']['pkg_list_value'],
						'pkg_list_ind_pkg_value' => empty($r['vars']['extra']['pkg_list_ind_pkg_value']) ? '' : $r['vars']['extra']['pkg_list_ind_pkg_value'],
						'pkg_list_group' => empty($r['vars']['extra']['pkg_list_group']) ? '' : $r['vars']['extra']['pkg_list_group'],
					);
				}
				elseif($r['vars']['extra']['descr_type_pkg_list'] == 2){
					$dtParams = array(
						'pkg_list_value' => empty($r['vars']['extra']['pkg_list_value_'.$grp]) ? '' : $r['vars']['extra']['pkg_list_value_'.$grp],
						'pkg_list_ind_pkg_value' => empty($r['vars']['extra']['pkg_list_ind_pkg_value_'.$grp]) ? '' : $r['vars']['extra']['pkg_list_ind_pkg_value_'.$grp],
						'pkg_list_group' => empty($r['vars']['extra']['pkg_list_group_'.$grp]) ? '' : $r['vars']['extra']['pkg_list_group_'.$grp],
					);
				}

				if(empty($dtParams['pkg_list_ind_pkg_value'])) $return[$j]['values'][$i] = empty($e[$r['name']]) ? '' : $e[$r['name']];
				elseif($dtParams['pkg_list_ind_pkg_value'] == 'default') $return[$j]['values'][$i] = $dtParams['pkg_list_value'];
				elseif($dtParams['pkg_list_ind_pkg_value'] == 'ind') $return[$j]['values'][$i] = $pkgsInd[$i]['pkg_list_value2_'.$r['name']];
				else throw new AVA_Exception('{Call:Lang:modules:billing:neizvestnyjp:'.Library::serialize(array($dtParams['pkg_list_ind_pkg_value'])).'}');

				if($r['type'] == 'checkbox') $return[$j]['values'][$i] = empty($return[$j]['values'][$i]) ? '' : 'yes';
				$return[$j]['linecapt'] = $r['text'];
				$extra[$j]['pkg_list_group'] = $dtParams['pkg_list_group'];
			}

			$y += 10;
		}

		ksort($return);
		return $return;
	}



	/********************************************************************************************************************************************************************

																		Оформление заказа

	*********************************************************************************************************************************************************************/

	public function func_orderSelectService(){
		/*
			Выбор услуги для заказа
		*/

		$services = array();
		$pkgs = $this->fetchServicesData();

		foreach($this->fetchServicesData2() as $i => $e){
			if(!empty($pkgs[$i])) $services[$i] = $e;
		}

		$this->setContent($this->Core->readBlockAndReplace($this->Core->getModuleTemplatePath($this->mod).'packages.tmpl', 'services', $this, array('services' => $services), 'cover'));
		return true;
	}

	protected function func_order(){
		/*
			Предварительная подготовка заказа.
			Если пользователь не авторизован ему предлагается авторизоваться или зарегиться, иначе происходит сразу переход к шагу 2

			Если зарегин и не админ - продолжается заказ.
			Если зареген и админ - предлагается ввести имя юзера для кого оформить заказ

			Оформление заказа, добавляет услугу в списк, выводит форму в которой должны присутствовать список сроков, поле для камментов и доп. поля создаваемые модулем расширения
			Заказ оформляется по принципу интернет-магазина. Все купленные услуги складываются в корзину, после чего происходит заказ.

			Последовательность оформления заказа:
				1. Открывается форма где вносятся все данные заказа в т.ч. срок
				2. Данные из формы сохраняются в БД
				3. Расчитывается стоимость всех имеющихся в корзине услуг с учетом скидок
				4. Данные о заказе отображаются пользователю с предложением продолжить поиск товаров или приступить к заказу

			Сведения об услугах передаются как:
				['service'] => 'hosting' или ['service']['0'] => 'hosting'
				['pkg'] => 'somepkg' или ['pkg']['0'] => 'somepkg' или ['pkg_hosting']['0'] => 'somepkg'

			Все формы в модулях для услуг должны создавать поля с постфиксом соответствующим id тарифа в списке
		*/

		if(!$clientId = $this->redirectToRegistration()) return false;

		if(empty($this->values['service'])){
			$this->redirect('orderSelectService');
			return;
		}
		elseif(!is_array($this->values['service'])) $this->values['service'] = array($this->values['service']);

		$hiddens = array('orderId' => $this->getOrderId(), 'entries' => array());
		$this->callCoMods('__billing_order');

		foreach($this->values['service'] as $sName){
			if(isset($this->values['pkg_'.$sName])){
				if(!is_array($this->values['pkg_'.$sName])) $this->values['pkg_'.$sName] = array($this->values['pkg_'.$sName]);
				foreach($this->values['pkg_'.$sName] as $id => $pkg){
					if(!$this->pkgIsOrdered($sName, $pkg)) throw new AVA_Access_Exception('Вам запрещен заказ аккаунтов по тарифу "'.$pkgData['text'].'"');
					$eId = $this->addNewOrderEntry('new', $clientId, $sName, $pkg, $hiddens['orderId']);
					$hiddens['entries'][$eId] = $eId;
					if(isset($this->values['ident_'.$sName][$id])) $this->setOrderEntryIdent($eId, $this->values['ident_'.$sName][$id]);
				}
			}
		}

		if(!$hiddens['entries']) $this->redirect('packages&service='.$this->values['service'][0]);
		else $this->redirect('order2&'.Library::deparseStr($hiddens));
	}

	protected function func_order2(){
		/*
			Форма оформления заказу
		*/

		if(!$clientId = $this->redirectToRegistration()) return false;
		$this->setMeta('{Call:Lang:modules:billing:oformlenieza}');
		$fObj = $this->newForm('order3', 'order3', array(), $this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl');

		$j = 0;
		$this->callCoMods('__billing_order2', $fObj, $j);
		$hiddens = $this->values;

		foreach($this->values['entries'] as $i => $e){
			$eData = $this->getOrderEntry($e);
			if($eData['client_id'] != $clientId) throw new AVA_Access_Exception('Этот заказ вам не принадлежит');
			$this->orderServiceForm($fObj, $e, $i, $j);
			$j ++;
		}

		if($fObj->matrixIsEmpty()){
			$hiddens = Library::array_merge($hiddens, $fObj->getHiddens());
			$this->redirect('order3&ava_form_transaction_id='.$this->getFormId('order3').'&'.Library::deparseStr($hiddens));
		}
		else $this->setContent($this->getFormText($fObj, array(), $hiddens, 'constructor'));
	}

	protected function func_order3(){
		/*
			Добавляет заказанную услугу в список заказанных услуг
		*/

		$oData = $this->getOrderParams($this->values['orderId'], true);
		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');
		if($oData['client_id'] != $clientId) throw new AVA_Access_Exception('Этот заказ вам не принадлежит');

		$this->setMeta('{Call:Lang:modules:billing:zakaz:'.Library::serialize(array($this->values['orderId'])).'}');

		foreach($this->values['entries'] as $i => $e){
			if(!$eData = $this->getOrderEntry($e)) throw new AVA_NotFound_Exception('Неопределенная запись заказа - '.$e.'');
			elseif($eData['client_id'] != $clientId) throw new AVA_Access_Exception('Этот заказ вам не принадлежит');
			elseif($this->values['orderId'] != $eData['order_id']) throw new AVA_Exception('Нарушен порядок оформления заказа');
			elseif(!($pkgData = $this->serviceData($eData['service'], $eData['package'])) || !$pkgData['show'] || empty($pkgData['rights']['new'])){
				throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:tarifanenajd:'.Library::serialize(array($eData['package'])).'}');
			}
			elseif(!$this->pkgIsOrdered($eData['service'], $eData['package'])){
				throw new AVA_Access_Exception('Вам запрещен заказ аккаунтов по тарифу "'.$pkgData['text'].'"');
			}

			$this->checkAccOrderMatrix($e, '', $i);

			if(!empty($this->values['promo_code'.$i])){
				if(!$this->canUsePromoCode($eData['service'])) $this->setError('promo_code'.$i, '{Call:Lang:modules:billing:ehtotpromoko}');
				elseif(!$this->promoCodeIsUsable($this->values['promo_code'.$i])) $this->setError('promo_code'.$i, 'Такой промо-код не существует, либо срок его действия истек');
				elseif(!$this->promoCodeIsNotUsed($this->values['promo_code'.$i])) $this->setError('promo_code'.$i, 'Этот промо-код уже использован');
			}

			if($pkgData['service_type'] == 'prolonged' && empty($this->values['term'.$i]) && !$this->canUseTest($eData['service'], $eData['package'], $clientId)){
				$this->setError('term'.$i, 'Вы не можете заказать тестовый аккаунт');
			}
		}

		$this->callCoMods('__billing_check_order3');
		if(!$this->check()) return false;
		$this->callCoMods('__billing_order3');
		$accData = array();

		foreach($this->values['entries'] as $i => $e){
			$eData = $this->getOrderEntry($e);
			$accData[$i]['params1'] = $this->getServiceCreateParams($e, '', $i);

			if(!empty($this->values['modified'.$i])){
				$pkgData = $this->serviceData($eData['service'], $eData['package']);
				$accData[$i]['params3'] = $this->getPkgParams($eData['service'], 'mpkg', 'mpkg_', $i, array($this->getConnectionCp($pkgData['server'])));
			}

			$this->setOrderEntryUserParams(
				$e,
				isset($this->values['term'.$i]) ? $this->values['term'.$i] : '',
				isset($this->values['ident'.$i]) ? $this->values['ident'.$i] : '',
				isset($this->values['auto_prolong'.$i]) ? $this->values['auto_prolong'.$i] : '',
				isset($this->values['auto_prolong_fract'.$i]) ? $this->values['auto_prolong_fract'.$i] : '',
				isset($this->values['promo_code'.$i]) ? $this->values['promo_code'.$i] : '',
				$accData[$i]
			);
		}

		$this->redirect('showOrder&id='.$this->values['orderId']);
		return true;
	}

	protected function func_showOrder(){
		/*
			Показывает расчет заказа
		*/

		if(!$oData = $this->getOrderParams($this->values['id'])){
			throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:zakaznenajde:'.Library::serialize(array($id)).'}');
		}
		elseif(!$this->getClientId() || $this->getClientId() != $oData['client_id']){
			throw new AVA_Access_Exception('{Call:Lang:modules:billing:vynemozhetep2}');
		}

		$this->calcOrder($this->values['id']);
		$this->DB->Upd(array('orders', array('step' => '3', 'ordered' => time()), "`id`='{$this->values['id']}' AND `step`<4"));
		if($this->getComplexId($this->values['id'])) $this->showComplex();
		else $this->showOrder();
	}

	private function showOrder(){
		/*
			Отображение счета
		*/

		$this->setMeta('{Call:Lang:modules:billing:zakaz:'.Library::serialize(array($this->values['id'])).'}');
		$oData = $this->getOrderParams($this->values['id'], true);
		$cData = $this->getClientData($this->getClientId());

		if(
			($oData['step'] < 0) &&
			(
				($this->Core->getParam('autoAddStyle', $this->mod) == 1 && ($oData['total'] == 0)) ||
				($this->Core->getParam('autoAddStyle', $this->mod) == 2 && ($oData['total'] < $cData['balance']))
			)
		){
			$this->redirect('orderByBalance&id='.$this->values['id']);
		}
		else{
			$this->setContent(
				$this->showOrderedServices(
					$this->values['id'],
					array(
						'calcOnly' => ($oData['step'] > 4) ? true : false,
						'payPage' => $oData['step'] < 5 ? $this->showPayPage($this->getTransactionByObjectId($this->values['id'], 'orders')) : '',
						'canOrder' => ($oData['total'] <= $cData['balance']) ? $this->path.'?mod='.$this->mod.'&func=orderByBalance&id='.$this->values['id'] : ''
					)
				)
			);
		}
	}

	private function showComplex(){
		/*
			Отображение комплекса
		*/

		if(!$ocParams = $this->getComplexOrderParamsByOrder($this->values['id'])) throw new AVA_NotFound_Exception('Такой комплект не найден в заказах');
		$params = $this->getComplexParams($ocParams['complex']);
		if(!$params) throw new AVA_NotFound_Exception('Такой комплект не найден');
		elseif(!$params['show']) throw new AVA_Access_Exception('Заказы этого комплекта запрещены');

		$this->setMeta($params['text'].' &mdash; заказ');
		$oData = $this->getOrderParams($this->values['id'], true);
		$cData = $this->getClientData($this->getClientId());

		$ppText = '';
		if($oData['step'] < 5){
			$psmsParams = array('payments' => array(), 'path' => $this->Core->getModuleTemplatePath($this->mod), 'urlPath' => $this->Core->getModuleTemplateUrl($this->mod));

			foreach($params['vars']['smsPays'] as $i => $e){
				if($e){
					$psmsParams['payments'][$i] = array('number' => $this->getSmsNumberParams($e), 'agregator' => $this->smsParams($i), 'currency' => $this->currencyBySmsNumber($e), 'transactionId' => $this->getTransactionByObjectId($this->values['id'], 'orders'));
					$psmsParams['payments'][$i]['num'] = $psmsParams['payments'][$i]['number']['number'];

					if($psmsParams['payments'][$i]['agregator']['extension']){
						$psmsParams['payments'][$i]['number']['comment'] = $this->callSmsExtension($psmsParams['payments'][$i]['agregator']['extension'], 'getSmsComment', $psmsParams['payments'][$i]['agregator']['name'], $psmsParams['payments'][$i]);
					}
				}
			}

			$ppParams = array('payments' => array(), 'price' => array(), 'path' => $this->Core->getModuleTemplatePath($this->mod), 'urlPath' => $this->Core->getModuleTemplateUrl($this->mod));

			foreach($params['vars']['pays'] as $i => $e){
				if($e){
					$curParams = $this->currencyByPayment($i);
					$ppParams['payments'][$i] = $this->paymentParams($i);
					$ppParams['price'][$i] = Library::humanCurrency($e).' '.$curParams['text'];
					$ppParams['urls'][$i] = _D.'index.php?mod='.$this->mod.'&func=payOrder&id='.$this->values['id'].'&payment='.$i;
				}
			}

			$ppText = $this->showPaySmsPage($oData['total'], $psmsParams).$this->showPayPage(0, '', $ppParams);
		}

		$this->setContent($this->showOrderedServices($this->values['id'], array('calcOnly' => true, 'payPage' => $ppText)));
	}

	protected function __ava__showPaySmsPage($sum, $params = array()){
		/*
			Выводит страницу смс-оплат
		*/

		return $this->Core->readBlockAndReplace($params['path'].'payments.tmpl', 'smspayments', $this, $params, 'cover');
	}

	protected function func_orderDel(){
		/*
			Удаляет счет из сессии
		 */

		unset($this->Core->User->tempParams['orderId']);
		$this->DB->Upd(array('orders', array('step' => '-1'), "`id`='".db_main::Quot($this->values['id'])."'"));
		$this->refresh('', '{Call:Lang:modules:billing:zakazotmenen}');
	}

	protected function func_orderFinish(){
		/*
			Удаляет счет из сессии
		 */

		unset($this->Core->User->tempParams['orderId']);
		$this->refresh('', 'Заказ завершен');
	}

	protected function func_orderModify(){
		/*
			Модификация заказа
		*/

		if(empty($this->values['entry'])){
			$this->back('showOrder&id='.$this->values['id'], '', '', 'Не выделено ни одной записи');
			return false;
		}

		$oData = $this->getOrderParams($this->values['id'], true);
		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');
		if($oData['client_id'] != $clientId) throw new AVA_Access_Exception('Этот заказ вам не принадлежит');

		$fObj = $this->newForm('orderModify2', 'orderModify2', array(), $this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl');
		$values = array();
		$this->setMeta('Изменить параметры заказа №'.$this->values['id']);

		if(empty($this->values['delete'])){
			$j = 0;

			foreach($this->getOrderEntriesByFilter($this->getEntriesWhere(false, 'id', 't1.')) as $i => $e){
				if($e['entry_type'] == 'new'){
					$this->orderServiceForm($fObj, $i, $i, $j);
				}
				elseif($e['entry_type'] == 'prolong'){
					$this->addFormBlock($fObj, array('caption'.$j => array('type' => 'caption', 'text' => $e['entry_caption'])), array(), array(), 'order'.$j);
					$this->setAccProlongMatrix($fObj, $i, '', $i, 'order'.$j);
				}

				foreach($e as $i1 => $e1) $values[$i1.$i] = $e1;
				foreach($e['extra']['params1'] as $i1 => $e1) $values[$i1.$i] = $e1;
				foreach($e['extra']['params3'] as $i1 => $e1) $values['mpkg_'.$i1.$i] = $e1;

				$j ++;
			}

			$this->setContent($this->getFormText($fObj, $values, $this->values, 'constructor'));
		}
		else{
			$this->DB->Upd(array('order_entries', array('status' => -1), $this->getEntriesWhere()));
			$this->redirect('showOrder&id='.$this->values['id']);
		}
	}

	protected function func_orderModify2(){
		/*
			Проверяет существование
		*/

		$oData = $this->getOrderParams($this->values['id'], true);
		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');
		if($oData['client_id'] != $clientId) throw new AVA_Access_Exception('Этот заказ вам не принадлежит');

		$entries = $this->getOrderEntriesByFilter($this->getEntriesWhere(false, 'id', 't1.'));
		foreach($entries as $i => $e){
			if($e['client_id'] != $clientId) throw new AVA_Access_Exception('Этот заказ вам не принадлежит');
			elseif($this->values['id'] != $e['order_id']) throw new AVA_Exception('Нарушен порядок оформления заказа');
			elseif(!($pkgData = $this->serviceData($e['service'], $e['package'])) || !$pkgData['show'] || empty($pkgData['rights']['new'])){
				throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:tarifanenajd:'.Library::serialize(array($e['package'])).'}');
			}
			elseif(!$this->pkgIsOrdered($e['service'], $e['package'])){
				throw new AVA_Access_Exception('Вам запрещен заказ аккаунтов по тарифу "'.$pkgData['text'].'"');
			}

			if($e['entry_type'] == 'new'){
				$this->checkAccOrderMatrix($i, '', $i);

				if(!empty($this->values['promo_code'.$i])){
					if(!$this->canUsePromoCode($e['service'])) $this->setError('promo_code'.$i, '{Call:Lang:modules:billing:ehtotpromoko}');
					elseif(!$this->promoCodeIsUsable($this->values['promo_code'.$i])) $this->setError('promo_code'.$i, 'Такой промо-код не существует, либо срок его действия истек');
					elseif(!$this->promoCodeIsNotUsed($this->values['promo_code'.$i], $i)) $this->setError('promo_code'.$i, 'Этот промо-код уже использован');
				}

				if($pkgData['service_type'] == 'prolonged' && empty($this->values['term'.$i]) && !$this->canUseTest($e['service'], $e['package'], $clientId)){
					$this->setError('term'.$i, 'Вы не можете заказать тестовый аккаунт');
				}
			}
			elseif($e['entry_type'] == 'prolong'){
				$this->checkAccUserProlongMatrix($i, '', $i);
			}
		}

		$this->setMeta('{Call:Lang:modules:billing:zakaz:'.Library::serialize(array($this->values['id'])).'}');
		if(!$this->check()) return false;
		$accData = array();

		foreach($entries as $i => $e){
			if($e['entry_type'] == 'new'){
				$accData[$i]['params1'] = $this->getServiceCreateParams($i, '', $i);

				if(!empty($this->values['modified'.$i])){
					$pkgData = $this->serviceData($e['service'], $e['package']);
					$accData[$i]['params3'] = $this->getPkgParams($e['service'], 'mpkg', 'mpkg_', $i, array($this->getConnectionCp($pkgData['server'])));
				}

				$this->setOrderEntryUserParams(
					$i,
					isset($this->values['term'.$i]) ? $this->values['term'.$i] : '',
					isset($this->values['ident'.$i]) ? $this->values['ident'.$i] : '',
					isset($this->values['auto_prolong'.$i]) ? $this->values['auto_prolong'.$i] : '',
					isset($this->values['auto_prolong_fract'.$i]) ? $this->values['auto_prolong_fract'.$i] : '',
					isset($this->values['promo_code'.$i]) ? $this->values['promo_code'.$i] : '',
					$accData[$i]
				);
			}
			elseif($e['entry_type'] == 'prolong'){
				$this->setServiceProlongEntry($i, '', $i);
			}
		}

		$this->redirect('showOrder&id='.$this->values['id']);
	}

	protected function func_orderByBalance(){
		/*
			Дирестивно проводит счет без оплаты
		*/

		if(!$oData = $this->getOrderParams($this->values['id'])){
			throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:zakaznenajde:'.Library::serialize(array($id)).'}');
		}
		elseif(!$this->getClientId() || $this->getClientId() != $oData['client_id']){
			throw new AVA_Access_Exception('{Call:Lang:modules:billing:vynemozhetep2}');
		}

		$opId = $this->enrollOrder($this->values['id']);
		$this->setContent($this->getOperationResultText($opId));
	}

	private function showPayPage($id, $func = 'payForm'){
		/*
			Размещает на странице ссылки на оплату
		*/

		$tData = $this->getTransactionParams($id, true);
		if($tData['status'] > 1) return '';
		$params = array('payments' => $this->fetchPayments(true), 'path' => $this->Core->getModuleTemplatePath($this->mod), 'urlPath' => $this->Core->getModuleTemplateUrl($this->mod));

		foreach($params['payments'] as $i => $e){
			$curParams = $this->currencyByPayment($i);
			$params['price'][$i] = Library::humanCurrency($this->convertCurrency($tData['sum'], $tData['currency'], $curParams['name'])).' '.$curParams['text'];
			$params['urls'][$i] = _D.'index.php?mod='.$this->mod.'&func='.$func.'&id='.$id.'&payment='.$i;
		}
		return $this->Core->readBlockAndReplace($params['path'].'payments.tmpl', 'payments', $this, $params, 'cover');
	}

	protected function func_payForm(){
		/*
			Создает платежную форму по ID транзакции
		*/

		$tData = $this->getTransactionParams($this->values['id'], true);
		if($tData['status'] > 1) throw new AVA_Exception('Этот платеж уже выполнен');
		$payParams = $this->paymentParams($this->values['payment']);

		$this->setPaymentTransactionSum($this->values['id'], $this->convertCurrency($tData['sum'], $tData['currency'], $payParams['currency']), $payParams['currency'], $this->values['payment']);
		$tData = $this->getTransactionParams($this->values['id'], true);

		if($payParams['extension']){
			$params['sum'] = $tData['sum'];
			$params['id'] = $tData['id'];

			switch($tData['object_type']){
				case 'orders': $params['descript'] = 'Оплата заказа №'.$tData['object_id']; break;
				default: $params['descript'] = 'Платеж №'.$this->values['id'];
			}

			$this->setMeta($params['descript']);
			$this->callPaymentExtension($payParams['extension'], 'paymentForm', $this->values['payment'], $params);
		}

		$this->setContent($this->Core->replace($payParams['comment'], $this, $tData));
	}


	/********************************************************************************************************************************************************************

																		Оформление комплексного заказа

	*********************************************************************************************************************************************************************/

	protected function func_complexOrder(){
		/*
			Оформление комплексного заказа
				При комплексном заказе пользователю предлагается внести данные как если бы оформление находилось на 2 стадии
		*/

		$params = $this->getComplexParams($this->values['complex']);
		if(!$params) throw new AVA_NotFound_Exception('Такой комплект не найден');
		elseif(!$params['show']) throw new AVA_Access_Exception('Заказы этого комплекта запрещены');

		$this->setMeta($params['text'].' &mdash; заказ');
		if(!$clientId = $this->redirectToRegistration()) return false;
		$orderId = $this->getOrderId(true);

		$fObj = $this->newForm('complexOrder2', 'complexOrder2', array(), $this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl');
		$hiddens = array('orderId' => $orderId, 'complex' => $this->values['complex'], $entries = array());
		$j = 0;

		$this->callCoMods('__billing_order', $fObj, $j);

		foreach($params['vars']['services'] as $i => $e){
			foreach($e as $i1 => $e1){
				for($y = 0; $y < $e1['count']; $y ++){
					if(!$this->pkgIsOrdered($i, $i1)){
						throw new AVA_Access_Exception('Вам запрещен заказ аккаунтов по тарифу "'.$pkgData['text'].'"');
					}

					$hiddens['entries'][$j] = $this->addNewOrderEntry('new', $clientId, $i, $i1, $orderId);
					$this->orderServiceForm($fObj, $hiddens['entries'][$j], $j, $j);
					$fObj->setExcludes('promo_code'.$j);
					$j ++;
				}
			}
		}

		$this->setMeta('{Call:Lang:modules:billing:oformlenieza}');

		if($fObj->matrixIsEmpty()){
			$hiddens = Library::array_merge($hiddens, $fObj->getHiddens());
			$this->redirect('complexOrder2&ava_form_transaction_id='.$this->getFormId('complexOrder2').'&'.Library::deparseStr($hiddens));
		}
		else $this->setContent($this->getFormText($fObj, array(), $hiddens, 'constructor'));
	}

	protected function func_complexOrder2(){
		/*
			Завершение оформления комплексного заказа
				- проверка верности введенных на предыдущем шаге данных
				- формирование заказа комплекса
		*/

		$params = $this->getComplexParams($this->values['complex']);
		if(!$params) throw new AVA_NotFound_Exception('Такой комплект не найден');
		elseif(!$params['show']) throw new AVA_Access_Exception('Заказы этого комплекта запрещены');

		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');
		$this->setMeta($params['text'].' &mdash; заказ');
		$accData = array();

		foreach($this->values['entries'] as $i => $e){
			if(!$eData = $this->getOrderEntry($e)) throw new AVA_NotFound_Exception('Неопределенная запись заказа - '.$e.'');
			elseif($eData['client_id'] != $clientId) throw new AVA_Access_Exception('Этот заказ вам не принадлежит');
			elseif($this->values['orderId'] != $eData['order_id']) throw new AVA_Exception('Нарушен порядок оформления заказа');
			elseif(!($pkgData = $this->serviceData($eData['service'], $eData['package'])) || !$pkgData['show'] || empty($pkgData['rights']['new'])){
				throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:tarifanenajd:'.Library::serialize(array($eData['package'])).'}');
			}
			elseif(!$this->pkgIsOrdered($eData['service'], $eData['package'])){
				throw new AVA_Access_Exception('Вам запрещен заказ аккаунтов по тарифу "'.$pkgData['text'].'"');
			}

			$this->checkAccOrderMatrix($e, '', $i);
		}

		if(!$this->check()) return false;

		$this->getComplexId($this->values['orderId'], $this->values['complex']);
		foreach($this->values['entries'] as $i => $e){
			$eData = $this->getOrderEntry($e);
			$accData[$i]['params1'] = $this->getServiceCreateParams($e, '', $i);

			$this->setOrderEntryUserParams(
				$e,
				$params['vars']['services'][$eData['service']][$eData['package']]['term'],
				$this->values['ident'.$i],
				isset($this->values['auto_prolong'.$i]) ? $this->values['auto_prolong'.$i] : '',
				isset($this->values['auto_prolong_fract'.$i]) ? $this->values['auto_prolong_fract'.$i] : '',
				'',
				$accData[$i]
			);
		}

		$this->redirect('showOrder&id='.$this->values['orderId']);
		return true;
	}


	/********************************************************************************************************************************************************************

																Функции относящиеся к личному кабинету

	*********************************************************************************************************************************************************************/

	protected function func_myServices(){
		/*
			Список заказанных услуг
		*/

		if(!$userId = $this->Core->User->getUserId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');
		if(!$clientId = $this->getClientId()) return $this->func_order();

		$fActions = array(
			'prolong' => '{Call:Lang:modules:billing:prodlit}',
			'stop' => '{Call:Lang:modules:billing:ostanovitavt}',
			'run' => '{Call:Lang:modules:billing:vosstanovita}',
			'suspend' => '{Call:Lang:modules:billing:postavitnapa}',
			'unsuspend' => '{Call:Lang:modules:billing:sniatspauzy}',
			'delete' => '{Call:Lang:modules:billing:udalit}'
		);

		$filter = "AND `step`>-1";
		if(!empty($this->values['activeSearchVar'])){
			switch($this->values['activeSearchVar']){
				case 'work': $filter = "AND `step`=1"; break;
				case 'suspend':
					unset($fActions['prolong'], $fActions['suspend']);
					$filter = "AND `step`=0";
					break;
			}
		}

		$services = $this->getServicesByClient($clientId, $filter, isset($this->values['services']) ? $this->values['services'] : false);
		if($services){
			foreach($services as $i => $e){
				$modTmplPath = $this->Core->getModuleTemplatePath($this->mod);
				$fk = Library::firstKey($e);
				foreach($e as $i1 => $e1) $e[$i1]['canUseChangePkg'] = (bool)$this->canUseChangePkgsList($i, $e1['package']);

				$list = $this->newList(
					'user_services_list_'.$i,
					array(
						'arr' => $e,
						'entryTemplate' => 'user_services_list',
						'actions' => array(
							'params' => 'myServiceParams',
							'changePkg' => 'myServiceChangePkg',
							'modifyPkg' => 'myServiceChangePkg&optionsOnly=1',
						),
						'action' => 'myServicesActions&service='.$i,
						'form_actions' => $fActions,
						'quickSearchParams' => array(
							'work' => array('text' => 'Работающие', 'params' => array()),
							'suspend' => array('text' => 'Заблокированные', 'params' => array())
						)
					),
					array(
						'caption' => $e[$fk]['s_text']
					),
					file_exists($modTmplPath.'user_services_list.tmpl') ? $modTmplPath.'user_services_list.tmpl' : ''
				);

				$this->callServiceObj('setOrderListEntries', $i, array('lObj' => $list));
				$this->setContent($this->getListText($list, 'users'));
			}
		}
		else{
			$this->back(empty($this->values) ? '' : 'myServices', '', '', 'Не найдено ни одной услуги');
		}

		$this->setMeta('{Call:Lang:modules:billing:zakazannyeva}');
	}

	protected function func_myServicesActions(){
		/*
			Массовые действия с услугами
		*/

		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');
		$entries = $this->getOrderedServicesByEntries($this->values['service']);

		foreach($entries as $i => $e){
			if($e['client_id'] != $clientId) throw new AVA_Access_Exception("Заказ {$e['id']} вам не принадлежит");

			if($e['step'] == -1){
				$this->setError('entry'.$i, "Нельзя выполнять какие-либо операции с удаленной услугой");
				$this->back('myServices');
				return false;
			}
			elseif(!$this->canUseAction($this->values['action'], $i, $msg)){
				$this->setError('entry'.$i, $msg);
				$this->back('myServices');
				return false;
			}
		}

		switch($this->values['action']){
			case 'prolong': return $this->prolongServices($entries);
			case 'stop': return $this->stopServices();
			case 'run': return $this->runServices();
			case 'suspend': return $this->suspendServices();
			case 'unsuspend': return $this->unsuspendServices();
			case 'delete': return $this->deleteServices();
		}
	}

	private function prolongServices($entries){
		/*
			Продляет выбранные услуги на установленный срок
		*/

		$fObj = $this->newForm('prolongServices2', 'prolongServices2');
		$this->setMeta('{Call:Lang:modules:billing:prodlenieusl1}');

		$hiddens = $this->values;
		$hiddens['orderId'] = $this->getOrderId();
		$j = 0;

		foreach($entries as $i => $e){
			$hiddens['entries'][$j] = $this->addNewOrderEntry('prolong', $e['client_id'], $e['service'], $e['package'], $hiddens['orderId'], $i);
			$this->setAccProlongMatrix($fObj, $hiddens['entries'][$j], '', $j, 'form');
			$j ++;
		}

		$this->setContent($this->getFormText($fObj, array(), $hiddens));
	}

	protected function func_prolongServices2(){
		/*
			Продляет выбранные услуги на установленный срок
		*/

		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');
		$this->setMeta('{Call:Lang:modules:billing:prodlenieusl1}');
		foreach($this->values['entries'] as $i => $e) $this->checkAccUserProlongMatrix($e, '', $i);

		if(!$this->check()) return false;
		foreach($this->values['entries'] as $i => $e) $this->setServiceProlongEntry($e, '', $i);
		$this->redirect('showOrder&id='.$this->values['orderId']);
	}

	public function stopServices(){
		/*
			Останавливает автопродление
		*/

		$this->DB->Upd(array('order_services', array('auto_prolong' => 0), $this->getEntriesWhere()));
		$this->refresh('myServices');
		return true;
	}

	public function runServices(){
		/*
			Восстанавливает автопродление
		*/

		$this->DB->Upd(array('order_services', array('auto_prolong' => 1), $this->getEntriesWhere()));
		$this->refresh('myServices');
		return true;
	}

	public function deleteServices(){
		/*
			Создает заказ на удаление. Выводит страницу конфирмации.
		*/

		$orders = array();
		foreach($this->values['entry'] as $i => $e){
			$orders[$i] = $this->addDeleteOrder($i, 'accord');
		}

		$this->setMeta('Заявка на удаление услуг');
		$this->setContent($this->showDeleteConfirmForm($orders));
	}

	protected function func_deleteServices2(){
		/*
			Окончательное удаление услуг
		*/

		$list = array();
		foreach($this->values['entry'] as $i => $e){
			$sData = $this->getOrderedService($i);
			$list[$e] = $this->getActionServiceValues($i);

			if($sData['step'] == -1){
				$this->setError('entry'.$i, "Услуга для {$sData['ident']} уже удалена");
				$this->back('myServices');
				return false;
			}
			elseif(!$this->canUseAction("delete", $i, $msg)){
				$this->setError('entry'.$i, $msg);
				$this->back('myServices');
				return false;
			}
		}

		if(!empty($sData['service'])){
			$this->setDeleteServiceList($sData['service'], $list);
			$this->refresh('myServices');
		}
		else{
			$this->refresh('myServices', 'Не выбрано ни одной услуги для удаления');
		}
	}

	public function __ava__suspendServices($unsuspend = false){
		/*
			Добровольная блокировка услуг
		*/

		$orders = array();
		foreach($this->values['entry'] as $i => $e){
			$orders[$i] = $this->addSuspendOrder($i, 'accord');
		}

		$this->setMeta('Заявка на блокировку услуг');
		$this->setContent($this->showSuspendConfirmForm($orders));
	}

	protected function func_suspendServices2(){
		/*
			Окончательное удаление услуг
		*/

		$list = array();
		foreach($this->values['entry'] as $i => $e){
			$sData = $this->getOrderedService($i);
			$list[$e] = $this->getActionServiceValues($i);
		}

		if(!empty($sData['service'])){
			$this->setSuspendServiceList($sData['service'], $list);
			$this->refresh('myServices');
		}
		else{
			$this->refresh('myServices', 'Не выбрано ни одной услуги для блокировки');
		}
	}

	public function __ava__unsuspendServices(){
		/*
			Добровольная блокировка услуг
		*/

		$orders = array();
		foreach($this->values['entry'] as $i => $e){
			$orders[$i] = $this->addUnsuspendOrder($i, 'accord');
		}

		$this->setMeta('Заявка на разблокировку услуг');
		$this->setContent($this->showUnsuspendConfirmForm($orders));
	}

	protected function func_unsuspendServices2(){
		/*
			Окончательное удаление услуг
		*/

		$list = array();
		foreach($this->values['entry'] as $i => $e){
			$sData = $this->getOrderedService($i);
			$list[$e] = $this->getActionServiceValues($i);
		}

		if(!empty($sData['service'])){
			$this->setUnsuspendServiceList($sData['service'], $list);
			$this->refresh('myServices');
		}
		else{
			$this->refresh('myServices', 'Не выбрано ни одной услуги для разблокировки');
		}
	}














































































	/********************************************************************************************************************************************************************

																	Функции оповещения о платеже

	*********************************************************************************************************************************************************************/

	protected function func_paymentNotify(){
		/*
			Собставенно оповещение о приходе оплаты
		*/

		if(!$payParams = $this->paymentParams($this->values['payId'])){
			$this->savePayLog(0, $this->printParams(), '', 0, 0, 11);
			throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:nenajdenukaz}');
		}

		$this->paymentHandler($this->callPaymentExtension($payParams['extension'], 'payment', $this->values['payId']), $this->values['payId']);
	}

	protected function func_smsNotify(){
		/*
			Собставенно оповещение о приходе оплаты
		*/

		if(!$payParams = $this->smsParams($this->values['payId'])){
			$this->savePayLog(0, $this->printParams(), '', 0, 0, 11);
			throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:nenajdenukaz}');
		}

		$this->paymentHandler($this->callSmsExtension($payParams['extension'], 'acceptSms', $this->values['payId']), $this->values['payId'], 's');
	}

	private function paymentHandler($params, $payId, $payType = ''){
		/*
			Производит разбор сведений о платеже. Осуществляет зачисление
			$params - это набор сформированный разборщиком для расширения
		*/

		if(!$params['logId']) throw new AVA_Exception('{Call:Lang:modules:billing:netzapisiopr}');
		elseif($params['params']){
			if(!$trData = $this->getTransactionParams($params['id'])) throw new AVA_Exception('{Call:Lang:modules:billing:nenajdenatra}');
			$this->enrollTransaction($params['id'], $params['params']['sum'], $params['params']['currency'], $payId, $payType, time(), $params['params']);

			switch($trData['object_type']){
				case 'orders':
					if($cData = $this->getComplexOrderParamsByOrder($trData['object_id'])) $this->enrollComplex($cData['id']);
					else $this->enrollOrder($trData['object_id']);
					break;
			}
		}

		$this->saveExtraLog($params['logId'], Library::getOutput());
		$this->Core->setFlag('rawOutput');
		$this->setContent($params['output']);
	}

	protected function func_paymentSuccess(){
		/*
			Страница сообщения о удачном платеже
		*/

		return $this->callPaymentExtensionByPayId(empty($this->values['payId']) ? false : $this->values['payId'], 'success');
	}

	protected function func_paymentFail(){
		/*
			Страница сообщения о неудачном платеже
		*/

		return $this->callPaymentExtensionByPayId(empty($this->values['payId']) ? false : $this->values['payId'], 'fail');
	}

	public function printParams(){
		/*
			Печатаемые параметры запроса
		*/

		$return = 'REQUEST_URI: '.$this->Core->getGPCVar('s', 'REQUEST_URI')."\n\nPOST DATA:\n";
		foreach($this->Core->getGPCArr('p') as $i => $e){
			$return .= "$i: $e\n";
		}

		return $return;
	}













































	/********************************************************************************************************************************************************************

																Функции относящиеся к личному кабинету

	*********************************************************************************************************************************************************************/

	protected function func_myServiceChangePkg(){
		/*
			Изменение тарифа
		*/

		$sData = $this->getOrderedService($this->values['id']);
		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');
		elseif($sData['client_id'] != $clientId) throw new AVA_Access_Exception('Эта услуга вам не принадлежит');

		$pkgData = $this->serviceData($sData['service'], $sData['package']);
		$this->setMeta('{Call:Lang:modules:billing:smenatarifas4:'.Library::serialize(array($pkgData['text'], $sData['ident'])).'}');

		if($pkgData['service_modify_minus'] == 'disabled' && $sData['paid_to'] <= time()){
			$this->back('myServices', '', '', 'Смена тарифа для услуг с отрицательным или нулевым остаточным сроком оплаты запрещена');
			return false;
		}

		$fObj = $this->newForm('myServiceChangePkg2', 'myServiceChangePkg2');
		$this->setAccModifyMatrix($fObj, $sData['service'], $sData['package']);
		$this->setContent($this->getFormText($fObj, array(), array('id' => $this->values['id'])));
	}

	protected function func_myServiceChangePkg2(){
		/*
			Выводит таблицу для мудификации ТП, либо переправляет сразу на myServiceChangePkg3 если предыдущее невозможно
		*/

		$sData = $this->getOrderedService($this->values['id']);
		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');
		elseif($sData['client_id'] != $clientId) throw new AVA_Access_Exception('Эта услуга вам не принадлежит');

		$this->checkAccModifyMatrix($sData['service'], $sData['package']);
		if(!$this->check()) return false;

		$mmId = $this->newServiceMainModify($sData['service'], $this->values['pkg']);
		$mId = $this->newServiceModify($mmId, $this->values['id']);
		$this->setMeta('{Call:Lang:modules:billing:modifitsirov:'.Library::serialize(array($this->pkgParam($sData['service'], $this->values['pkg'], 'text'))).'}');

		$fObj = $this->newForm('myServiceChangePkg3', 'myServiceChangePkg3', array(), $this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl');
		$this->setAccModifyMatrix2($fObj, $mmId, 'calculate0');
		$fObj->setHidden('id', $mId);

		if($fObj->matrixIsEmpty()) $this->redirect('myServiceChangePkg3&ava_form_transaction_id='.$this->getFormId('myServiceChangePkg3').'&'.Library::deparseStr($fObj->getHiddens()));
		else $this->setContent($this->getFormText($fObj, array(), array(), 'constructor'));
	}

	protected function func_myServiceChangePkg3(){
		/*
			Собственно выводит расчет предлагая провести изменения либо пополнить баланс (если недостаточно), либо отказаться от изменений
		*/

		$mData = $this->getServiceModifyData($this->values['id']);
		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');
		elseif($mData['client_id'] != $clientId) throw new AVA_Access_Exception('Эта услуга вам не принадлежит');

		$this->checkAccModifyMatrix2($mData['main_id']);
		if(!$this->check()) return false;

		$this->setServiceModifyExtraParams($mData['main_id']);
		$this->prepareServiceModifyBasePrice($this->values['id']);
		$this->prepareServiceModifyPayData($this->values['id']);

		$this->redirect('myServiceChangePkg4&id='.$this->values['id']);
	}

	protected function func_myServiceChangePkg4(){
		/*
			Собственно выводит расчет предлагая провести изменения либо пополнить баланс (если недостаточно), либо отказаться от изменений
		*/

		$mData = $this->getServiceModifyData($this->values['id'], true);
		if(!$clientId = $this->getClientId()) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');
		elseif($clientId != $mData['client_id']) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo}');

		$this->setContent($this->showModifyConfirmForm($this->values['id']));
		if($mData['total'] > 0 && $mData['status'] < 5){
			$trId = $this->newPaymentTransaction($mData['total'], '', 'modify_service_orders', $this->values['id'], $clientId);
			$this->setContent($this->showPayPage($trId));
		}
	}

	protected function func_modifyByBalance(){
		/*
			Выполняет модификацию из средств находящихся на балансе
		*/

		$mData = $this->getServiceModifyData($this->values['id']);
		$this->modifyServicesEnd($mData['main_id'], array($this->values['id'] => array('auto' => 1, 'server' => $mData['server'])));
		$this->refresh('myServices');
	}

	protected function func_myServiceParams(){
		/*
			Выводит параметры заказанной услуги
		*/

		$sData = $this->getOrderedService($this->values['id']);
		$pkgData = $this->serviceData($sData['service'], $sData['package']);
		$serviceData = $this->serviceData($sData['service']);

		$capt = $this->callServiceObj('getServiceCaption', $sData['service'], array('id' => $this->values['id']));
		if(!$capt) $capt = '{Call:Lang:modules:billing:uslugapotari9:'.Library::serialize(array($serviceData['text'], $pkgData['text'], $sData['ident'])).'}';
		$this->setMeta($capt);

		$pkgData['curName'] = $this->currencyName($pkgData['currency']);
		$sData['total'] = $sData['price'] + $sData['modify_price'];

		$this->setContent($this->Core->readBlockAndReplace(
			$this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl',
			'service_descript',
			$this,
			array(
				'sData' => $sData,
				'pkgData' => $pkgData,
				'pkgDescript' => $this->getBasePkgDescript($this->getPkgBase(
					$this->sumParams($pkgData['vars'], $sData['vars'], $sData['service']),
					$this->getPkgDescriptForm($sData['service'], $pkgData['name'], 'mpkg', 'mpkg_', '', $v, array($this->getConnectionCp($sData['server'])), array('pkgData' => $pkgData))
				)),
			),
			'cover'
		));

		if($pTerms = $this->getProlongTermsList($sData['service'], $sData['package'])){
			$this->setContent(
				$this->getFormText(
					$this->addFormBlock(
						$this->newForm(
							'myServiceParams2',
							'myServiceParams2'
						),
						'myservice_params',
						array('pTerms' => $pTerms, 'fract' => $pkgData['fract_prolong'])
					),
					$sData,
					array('modify' => $this->values['id'])
				)
			);
		}
	}

	protected function func_myServiceParams2(){
		/*
			Установка параметров услуги
		*/

		$this->typeIns('order_services', array('auto_prolong' => $this->values['auto_prolong'], 'auto_prolong_fract' => $this->values['auto_prolong_fract']), 'myServices');
	}
















































	/********************************************************************************************************************************************************************

																	Управление балансом

	*********************************************************************************************************************************************************************/

	protected function func_myBalance(){
		/*
			Форма на пополнение баланса и список всех операций
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($clientId = $this->getClientId())) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');
		$mainCur = $this->getMainCurrencyName();

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'myBalance2',
						'myBalance2'
					),
					'mybalance',
					array('currency' => $mainCur)
				),
				array('sum' => isset($this->values['sum']) ? $this->values['sum'] : '')
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'user_balances_list',
					array(
						'req' => array('pays', '*', "`client_id`='$clientId'", "`date` DESC")
					),
					array(
						'caption' => '{Call:Lang:modules:billing:operatsii}',
						'currency' => $mainCur
					)
				),
				'usertable'
			)
		);

		$this->setMeta('{Call:Lang:modules:billing:popolnenieba}');
	}

	protected function func_myBalance2(){
		/*
			Пополнение баланса - выбор способа оплаты
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($clientId = $this->getClientId())) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');
		$id = $this->newPaymentTransaction($this->values['sum']);
		$this->setContent($this->showPayPage($id));
	}

	protected function func_myBalance3(){
		/*
			Вывод формы для пополнения баланса
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($clientId = $this->getClientId())) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');

		$this->setMeta('{Call:Lang:modules:billing:popolnenieba}');

		$params['clientId'] = $clientId;
		$params['sum'] = $this->values['sum'];
		$params['id'] = $this->values['id'];
		$params['descript'] = '{Call:Lang:modules:billing:popolnenieba1:'.Library::serialize(array($this->values['id'])).'}';

		$this->showPayForm($params);
	}



	/********************************************************************************************************************************************************************

																	Документооборот

	*********************************************************************************************************************************************************************/

	protected function func_myBills(){
		/*
			Список заказов
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($clientId = $this->getClientId())) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');

		$this->setContent(
			$this->getListText(
				$this->newList(
					'user_bills_list',
					array(
						'req' => array('orders', '*', "`client_id`='$clientId' AND `step`>0", "`date` DESC"),
						'actions' => array(
							'show' => 'showOrder',
							'act' => 'actByOrder',
							'invoice' => 'invoiceByOrder'
						)
					)
				),
				'users'
			)
		);

		$this->setMeta('{Call:Lang:modules:billing:spisokzakazo}');
	}

	protected function func_myDocs(){
		/*
			Выводит список доступных счетов-фактур и АВР, отобранных по месяцамъ
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($clientId = $this->getClientId())) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');
		$this->setMeta('{Call:Lang:modules:billing:zakryvaiushc}');
		$entries = array();

		foreach($this->getOrderEntriesByFilter("t1.client_id='$clientId' AND t1.status=2", false, "t1.date DESC") as $i => $e){
			$sData = $this->serviceData($e['service']);
			if($sData['invoice_type'] == 'immediate') $entries[library::getEmptyIndex($entries, $e['date'])] = $e;
			elseif($sData['invoice_type'] == 'oneinmonth') $this->splitServiceTerm($entries, $e, $e['date'], $e['s_paid_to']);
		}

		krsort($entries);
		$this->setContent(
			$this->getListText(
				$this->newList(
					'documents',
					array(
						'arr' => $entries,
						'actions' => array(
							'act' => 'document&document=act',
							'invoice' => 'document&document=invoice',
						)
					)
				),
				'usertable'
			)
		);
	}

	private function splitServiceTerm(&$entries, $entry, $start, $end){
		/*
			Разбивает услугу на участки длиной месяц вплоть до окончания ее действия либо до последнего числа предыдущего месяца, смотря что раньше
		*/

		$e = ($end <= time()) ? dates::date('Ym', $end) : dates::date('Ym');
		$s = dates::date('Ym', $start);

		for($i = $s; $i <= $e; $i ++){
			if(!$t = $this->dateByMonth($i)) continue;
			$entry['date'] = $t;
			$entry['id'] = $entry['id'].'&period='.$i;
			$entries[library::getEmptyIndex($entries, $t)] = $entry;
		}
	}

	public function dateByMonth($m, $day = 1, $mCorrect = 1){
		/*
			Выдает int дату по месяцу и году
		*/

		$m = (string)$m;
		if($m[4].$m[5] > 12) return false;
		return dates::mkTime(0, 0, 1, (($m[4].$m[5]) + $mCorrect), $day, $m[0].$m[1].$m[2].$m[3]);
	}

	protected function func_document(){
		/*
			Выводит акт выполненных работ
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($clientId = $this->getClientId())) throw new AVA_Access_Exception('{Call:Lang:modules:billing:vyneavtorizo1}');

		$this->Core->setTempl('empty');
		$entry = $this->getOrderEntry($this->values['id']);
		$tData = $this->getTransactionParams($this->getTransactionByObjectId($entry['order_id'], 'orders'));
		$pEx = $this->paymentExtension($tData['payment']);

		$this->callPaymentExtension(
			'Bank',
			$this->values['document'],
			($pEx == 'payBank' ? $tData['payment'] : $this->Core->getParam('defaultBank', $this->mod)),
			array(
				'id' => $this->values['id'],
				'actId' => empty($this->values['period']) ? $this->values['id'] : $this->values['id'].'/'.$this->values['period'],
				'actDate' => empty($this->values['period']) ? dates::date('d.m.Y', $entry['date']) : date('d.m.Y', $this->dateByMonth($this->values['period'])),
				'entry' => $entry,
				'period' => empty($this->values['period']) ? '' : $this->values['period']
			)
		);
	}


	/********************************************************************************************************************************************************************

																				Прочее

	*********************************************************************************************************************************************************************/

	protected function func_connectionResult(){
		/*
			Результат выполнения запроса
		*/

		if(!$values = $this->DB->rowFetch(array('server_reply', '*', "`id`='".db_main::Quot($this->values['id'])."'"))) throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:takojzaprosn}');
		elseif(!$values['description'] && $values['code']) $values['description'] = '{Call:Lang:modules:billing:otsutstvueto}';
		elseif(!$values['description'] && !$values['code']) $values['description'] = '{Call:Lang:modules:billing:zaprosvypoln}';

		$values['forUser'] = true;
		$this->setContent($this->Core->readAndReplace($this->Core->getModuleTemplatePath($this->mod).'server_reply.tmpl', $this, $values));
		$this->Core->setTempl('empty.tmpl');
	}
}

?>