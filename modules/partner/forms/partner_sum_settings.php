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


$matrix['grp_caption_'.$mod.'_'.$type.'_'.$grp]['text'] = $grpName;
$matrix['grp_caption_'.$mod.'_'.$type.'_'.$grp]['type'] = 'caption';

$matrix['pay_style_'.$mod.'_'.$type.'_'.$grp]['text'] = '';
$matrix['pay_style_'.$mod.'_'.$type.'_'.$grp]['type'] = 'radio';
$matrix['pay_style_'.$mod.'_'.$type.'_'.$grp]['additional'] = array(
	'order' => '{Call:Lang:modules:partner:nachisliatko}',
	'pay' => '{Call:Lang:modules:partner:nachisliatko1}',
);
$matrix['pay_style_'.$mod.'_'.$type.'_'.$grp]['additional_style'] = array(
	'order' => 'onClick="showFormBlock(\'pay_order_block_'.$mod.'_'.$type.'_'.$grp.'\'); hideFormBlock(\'pay_pay_block_'.$mod.'_'.$type.'_'.$grp.'\');"',
	'pay' => 'onClick="hideFormBlock(\'pay_order_block_'.$mod.'_'.$type.'_'.$grp.'\'); showFormBlock(\'pay_pay_block_'.$mod.'_'.$type.'_'.$grp.'\');"',
);
$values['pay_style_'.$mod.'_'.$type.'_'.$grp] = 'order';

$matrix['caption_balance_price_'.$mod.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:nastrojkiotc1}';
$matrix['caption_balance_price_'.$mod.'_'.$type.'_'.$grp]['type'] = 'caption';
$matrix['caption_balance_price_'.$mod.'_'.$type.'_'.$grp]['pre_text'] = '<div id="pay_pay_block_'.$mod.'_'.$type.'_'.$grp.'" style="display: none;">';

$matrix['balance_price_'.$mod.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:protsentnach}';
$matrix['balance_price_'.$mod.'_'.$type.'_'.$grp]['type'] = 'text';
$matrix['balance_price_'.$mod.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

$matrix['balance_price2_'.$mod.'_'.$type.'_'.$grp]['text'] = 'Процент начисляемый при повторных пополнениях баланса';
$matrix['balance_price2_'.$mod.'_'.$type.'_'.$grp]['type'] = 'text';
$matrix['balance_price2_'.$mod.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

$matrix['balance_max_'.$mod.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:maksimalnaia:'.Library::serialize(array($cur)).'}';
$matrix['balance_max_'.$mod.'_'.$type.'_'.$grp]['type'] = 'text';
$matrix['balance_max_'.$mod.'_'.$type.'_'.$grp]['comment'] = '{Call:Lang:modules:partner:esliostavitp}';
$matrix['balance_max_'.$mod.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';
$matrix['balance_max_'.$mod.'_'.$type.'_'.$grp]['post_text'] = '</div>';

$first2 = false;

foreach($obj->getServices() as $i => $e){
	$matrix['capt'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = $e;
	$matrix['capt'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'caption';
	if(!$first2) $first2 = 'capt'.$mod.'_'.$i.'_'.$type.'_'.$grp;

	$matrix['pay_service_style_'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '';
	$matrix['pay_service_style_'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'radio';
	$matrix['pay_service_style_'.$mod.'_'.$i.'_'.$type.'_'.$grp]['additional'] = array(
		'service' => '{Call:Lang:modules:partner:edinyenastro}',
		'pkg' => '{Call:Lang:modules:partner:personalnyen}',
	);
	$matrix['pay_service_style_'.$mod.'_'.$i.'_'.$type.'_'.$grp]['additional_style'] = array(
		'service' => 'onClick="showFormBlock(\'service_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\'); hideFormBlock(\'pkg_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\');"',
		'pkg' => 'onClick="hideFormBlock(\'service_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\'); showFormBlock(\'pkg_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\');"',
	);
	$values['pay_service_style_'.$mod.'_'.$i.'_'.$type.'_'.$grp] = 'service';

	$matrix['price_install_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['pre_text'] = '<div id="service_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'" style="display: none;">';
	$matrix['price_install_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep3}';
	$matrix['price_install_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'text';
	$matrix['price_install_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

	$matrix['price_modify_install_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = 'Отчисление партнеру за установку модификаций, процентов';
	$matrix['price_modify_install_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'text';
	$matrix['price_modify_install_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

	$matrix['price_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep}';
	$matrix['price_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'text';
	$matrix['price_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

	$matrix['price_price2'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep1}';
	$matrix['price_price2'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'text';
	$matrix['price_price2'.$mod.'_'.$i.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

	$matrix['price_prolong_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = 'Отчисления за продление, процентов';
	$matrix['price_prolong_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'text';
	$matrix['price_prolong_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

	$matrix['price_modify_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep2}';
	$matrix['price_modify_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'text';
	$matrix['price_modify_price'.$mod.'_'.$i.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

	$matrix['price_other'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep4}';
	$matrix['price_other'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'text';
	$matrix['price_other'.$mod.'_'.$i.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

	$matrix['max'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:maksimalnaia1:'.Library::serialize(array($cur)).'}';
	$matrix['max'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'text';
	$matrix['max'.$mod.'_'.$i.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

	$matrix['pay_order'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisliatpr}';
	$matrix['pay_order'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'select';
	$matrix['pay_order'.$mod.'_'.$i.'_'.$type.'_'.$grp]['additional'] = array(
		'new' => 'Первом заказе',				//Т.е. если у клиента ранее никогда заказов не было
		'all' => 'Любом заказе'					//Если у клиента есть заказы других услуг
	);

	$matrix['pay_moment'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = 'Начисляются';
	$matrix['pay_moment'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'select';
	$matrix['pay_moment'.$mod.'_'.$i.'_'.$type.'_'.$grp]['additional'] = array(
		'immediate' => 'Сразу',
		'portioned-pre' => 'Распределенно при начале очередного базового срока',
		'portioned-post' => 'Распределенно при окончании очередного базового срока',
	);
	$matrix['pay_moment'.$mod.'_'.$i.'_'.$type.'_'.$grp]['post_text'] = '</div>';

	if($isPersonal){
		$matrix['usePersonalSettings'.$mod.'_'.$i.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:ispolzovatpe}';
		$matrix['usePersonalSettings'.$mod.'_'.$i.'_'.$type.'_'.$grp]['type'] = 'checkbox';
		$matrix['usePersonalSettings'.$mod.'_'.$i.'_'.$type.'_'.$grp]['additional_style'] = 'onClick="switchByCheckbox(\'usePersonalSettings'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\', \'block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\');"';
		$matrix['usePersonalSettings'.$mod.'_'.$i.'_'.$type.'_'.$grp]['post_text'] = '<script type="text/javascript">
			switchByCheckbox(\'usePersonalSettings'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\', \'block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\');
		</script>';

		$matrix['pay_service_style_'.$mod.'_'.$i.'_'.$type.'_'.$grp]['pre_text'] = '<div id="block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'" style="display: none">';
		$matrix['pay_moment'.$mod.'_'.$i.'_'.$type.'_'.$grp]['post_text'] .= '</div>';
		$last = 'usePersonalSettings'.$mod.'_'.$i.'_'.$type.'_'.$grp;
	}
	else $last = 'pay_moment'.$mod.'_'.$i.'_'.$type.'_'.$grp;

	$first = false;

	foreach($obj->getPackages($i) as $i1 => $e1){
		if(!$first) $first = 'capt'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp;
		$matrix['capt'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:tarif:'.Library::serialize(array($e1)).'}';
		$matrix['capt'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'caption';

		$matrix['price_install_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep3}';
		$matrix['price_install_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'text';
		$matrix['price_install_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

		$matrix['price_modify_install_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = 'Отчисление партнеру за установку модификаций, процентов';
		$matrix['price_modify_install_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'text';
		$matrix['price_modify_install_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

		$matrix['price_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep}';
		$matrix['price_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'text';
		$matrix['price_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

		$matrix['price_price2'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep1}';
		$matrix['price_price2'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'text';
		$matrix['price_price2'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

		$matrix['price_prolong_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = 'Отчисления за продление, процентов';
		$matrix['price_prolong_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'text';
		$matrix['price_prolong_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

		$matrix['price_modify_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep2}';
		$matrix['price_modify_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'text';
		$matrix['price_modify_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

		$matrix['price_other'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisleniep4}';
		$matrix['price_other'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'text';
		$matrix['price_other'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

		$matrix['max'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:maksimalnaia1:'.Library::serialize(array($cur)).'}';
		$matrix['max'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'text';
		$matrix['max'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['warn_function'] = 'regExp::float';

		$matrix['pay_order'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:otchisliatpr}';
		$matrix['pay_order'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'select';
		$matrix['pay_order'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['additional'] = array(
			'new' => 'Первом заказе',
			'all' => 'Любом заказе'
		);

		$matrix['pay_moment'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = 'Начисляются';
		$matrix['pay_moment'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'select';
		$matrix['pay_moment'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['additional'] = array(
			'immediate' => 'Сразу',
			'portioned-pre' => 'Распределенно при начале очередного базового срока',
			'portioned-post' => 'Распределенно при окончании очередного базового срока',
		);
		$last = 'pay_moment'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp;

		if($isPersonal){
			$matrix['usePersonalSettings'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['text'] = '{Call:Lang:modules:partner:ispolzovatpe}';
			$matrix['usePersonalSettings'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['type'] = 'checkbox';
			$matrix['usePersonalSettings'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['additional_style'] = 'onClick="switchByCheckbox(\'usePersonalSettings'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp.'\', \'block_'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp.'\');"';
			$matrix['usePersonalSettings'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['post_text'] = '<script type="text/javascript">
				switchByCheckbox(\'usePersonalSettings'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp.'\', \'block_'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp.'\');
			</script>';

			$matrix['price_install_price'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['pre_text'] = '<div id="block_'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp.'" style="display: none">';
			$matrix['pay_moment'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp]['post_text'] = '</div>';
			$last = 'usePersonalSettings'.$mod.'_'.$i.'_'.$i1.'_'.$type.'_'.$grp;
		}
	}

	if(!$first){
		$matrix[$last]['post_text'] .= '<div id="pkg_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'" style="display: none;"></div>';
		$last2 = $last;
	}
	else{
		$matrix[$first]['pre_text'] = '<div id="pkg_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'" style="display: none;">';
		$matrix[$last]['post_text'] = '</div>';
		$last2 = $last;
	}

	$matrix[$last2]['post_text'] .= '<script type="text/javascript">
		if(document.getElementById("pay_service_style_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'_service").checked){
			showFormBlock(\'service_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\');
			hideFormBlock(\'pkg_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\');
		}
		else if(document.getElementById("pay_service_style_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'_pkg").checked){
			hideFormBlock(\'service_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\');
			showFormBlock(\'pkg_block_'.$mod.'_'.$i.'_'.$type.'_'.$grp.'\');
		}
	</script>';
}

if($first2){
	$matrix[$first2]['pre_text'] = '<div id="pay_order_block_'.$mod.'_'.$type.'_'.$grp.'" style="display: none;">';
	$matrix[$last2]['post_text'] .= '</div>';
}
else{
	$last2 = 'balance_max_'.$mod.'_'.$type.'_'.$grp;
	$matrix[$last2]['post_text'] .= '<div id="pay_order_block_'.$mod.'_'.$type.'_'.$grp.'" style="display: none;"></div>';
}

$matrix[$last2]['post_text'] .= '<script type="text/javascript">
	if(document.getElementById("pay_style_'.$mod.'_'.$type.'_'.$grp.'_order").checked){
		showFormBlock(\'pay_order_block_'.$mod.'_'.$type.'_'.$grp.'\');
		hideFormBlock(\'pay_pay_block_'.$mod.'_'.$type.'_'.$grp.'\');
	}
	else if(document.getElementById("pay_style_'.$mod.'_'.$type.'_'.$grp.'_pay").checked){
		hideFormBlock(\'pay_order_block_'.$mod.'_'.$type.'_'.$grp.'\');
		showFormBlock(\'pay_pay_block_'.$mod.'_'.$type.'_'.$grp.'\');
	}
</script>';

?>