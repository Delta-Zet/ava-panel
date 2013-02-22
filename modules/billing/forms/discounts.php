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


$matrix['text']['text'] = '{Call:Lang:modules:billing:imiaskidki}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:billing:neukazanoimi2}';

$matrix['name']['text'] = '{Call:Lang:modules:billing:identifikato3}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazaniden1}';
$matrix['name']['warn_function'] = 'regExp::ident';

$matrix['basic_type']['text'] = '{Call:Lang:modules:billing:predostavlia}';
$matrix['basic_type']['type'] = 'checkbox_array';
$matrix['basic_type']['warn'] = '{Call:Lang:modules:billing:neukazanonac}';
$matrix['basic_type']['additional'] = array(
	'install' => '{Call:Lang:modules:billing:ustanovku}',
	'term' => '{Call:Lang:modules:billing:pervyjperiod}',
	'term2' => '{Call:Lang:modules:billing:posleduiushc}',
	'prolong' => '{Call:Lang:modules:billing:prodlenie}',
	'modify' => '{Call:Lang:modules:billing:modifikatsii}',
	'other' => '{Call:Lang:modules:billing:inyeoplatysh}'
);

$matrix['type']['text'] = '{Call:Lang:modules:billing:za}';
$matrix['type']['type'] = 'select';
$matrix['type']['warn'] = '{Call:Lang:modules:billing:neukazanozac}';
$matrix['type']['additional'] = array(
	'term' => '{Call:Lang:modules:billing:srokzakaza1}',
	'order_sum' => '{Call:Lang:modules:billing:obshchuiusto}',
	'other_services' => '{Call:Lang:modules:billing:vsviaziszaka}',
	'promocode' => '{Call:Lang:modules:billing:ukazanieprom}',
	'baseless' => '{Call:Lang:modules:billing:bezosnovanij}',
	'other' => '{Call:Lang:modules:billing:inyeosobenno}'
);

if(!empty($extra)){
	$matrix['name']['disabled'] = true;
	$matrix['type']['disabled'] = true;

	$field = 'start';
	$text = '{Call:Lang:modules:billing:ispolzovats}';
	require(_W.'forms/type_calendar2.php');

	$field = 'end';
	$text = '{Call:Lang:modules:billing:po}';
	require(_W.'forms/type_calendar2.php');

	$matrix['pkgs']['text'] = '{Call:Lang:modules:billing:tarifynakoto}';
	$matrix['pkgs']['type'] = 'checkbox_array';
	$matrix['pkgs']['additional'] = $packages;

	$matrix['client_loyalty_levels']['text'] = '{Call:Lang:modules:billing:skidkupoluch}';
	$matrix['client_loyalty_levels']['type'] = 'checkbox_array';
	$matrix['client_loyalty_levels']['additional'] = $clientGroups;

	$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
	$matrix['sort']['type'] = 'text';

	$matrix['in_pkg_list']['text'] = '{Call:Lang:modules:billing:pokazyvatvsp}';
	$matrix['in_pkg_list']['type'] = 'checkbox';

	$matrix['show']['text'] = '{Call:Lang:modules:billing:skidkavkliuc}';
	$matrix['show']['type'] = 'checkbox';

	switch($type){
		case 'term':
			$matrix['discounts']['text'] = '{Call:Lang:modules:billing:skidki}';
			$matrix['discounts']['type'] = 'textarea';
			$matrix['discounts']['comment'] = '{Call:Lang:modules:billing:ukazhitekaks:'.Library::serialize(array(Dates::termsListVars($baseTerm, '2'), Dates::termsListVars($baseTerm, '1'))).'}';
			$matrix['discounts']['warn_function'] = 'regExp::hashBlockFloat';
			$matrix['discounts']['warn'] = '{Call:Lang:modules:billing:neukazanyski}';
			break;


		case 'order_sum':
			$matrix['discounts']['text'] = '{Call:Lang:modules:billing:skidki}';
			$matrix['discounts']['type'] = 'textarea';
			$matrix['discounts']['comment'] = '{Call:Lang:modules:billing:ukazhitekaks1:'.Library::serialize(array($this->getMainCurrencyName(), $this->getMainCurrencyName())).'}';
			$matrix['discounts']['warn_function'] = 'regExp::hashBlockFloat';
			$matrix['discounts']['warn'] = '{Call:Lang:modules:billing:neukazanyski}';
			break;


		case 'other_services':
			$matrix['discount']['text'] = '{Call:Lang:modules:billing:skidkaprotse}';
			$matrix['discount']['type'] = 'text';
			$matrix['discount']['warn_function'] = 'regExp::float';
			$matrix['discount']['warn'] = '{Call:Lang:modules:billing:neukazanaski}';

			$matrix['other_services']['text'] = '{Call:Lang:modules:billing:uslugizazaka}';
			$matrix['other_services']['type'] = 'checkbox_array';
			$matrix['other_services']['additional'] = $services;
			$matrix['other_services']['additional_style'] = array();

			$matrix['discount_logic']['text'] = '{Call:Lang:modules:billing:logikaprimen}';
			$matrix['discount_logic']['comment'] = '{Call:Lang:modules:billing:eslilogikail}';
			$matrix['discount_logic']['type'] = 'select';
			$matrix['discount_logic']['warn'] = '{Call:Lang:modules:billing:neukazanalog}';
			$matrix['discount_logic']['additional'] = array(
				'OR' => '{Call:Lang:modules:billing:ili}',
				'AND' => 'И'
			);

			$js = '';
			$last = 'other_services_count';

			foreach($services as $i => $e){
				if(!$pkgs = $this->getPackages($i)) continue;
				$pData = $this->serviceData($i);
				$matrix['other_services']['additional_style'][$i] = "onClick='var s = document.getElementById(\"other_services_block_{$i}\").style; if(this.checked) s.display = \"block\"; else s.display = \"none\";'";
				$js .= "if(!document.getElementById(\"other_services_{$i}\").checked) document.getElementById(\"other_services_block_{$i}\").style.display = \"none\";";

				$matrix['discount_pkg_logic_'.$i]['pre_text'] = "<div id='other_services_block_{$i}'>";
				$matrix['discount_pkg_logic_'.$i]['text'] = '{Call:Lang:modules:billing:logikaprimen1:'.Library::serialize(array($e)).'}';
				$matrix['discount_pkg_logic_'.$i]['comment'] = '{Call:Lang:modules:billing:eslilogikail1}';
				$matrix['discount_pkg_logic_'.$i]['type'] = 'select';
				$matrix['discount_pkg_logic_'.$i]['additional'] = array(
					'OR' => '{Call:Lang:modules:billing:ili}',
					'AND' => 'И'
				);

				$matrix['other_services_pkgs_'.$i]['text'] = '{Call:Lang:modules:billing:tarifyuslugi:'.Library::serialize(array($e)).'}';
				$matrix['other_services_pkgs_'.$i]['type'] = 'checkbox_array';
				$matrix['other_services_pkgs_'.$i]['additional'] = $pkgs;

				foreach($matrix['other_services_pkgs_'.$i]['additional'] as $i1 => $e1){
					$matrix['other_services_pkgs_'.$i]['additional_style'][$i1] = "onClick='var s = document.getElementById(\"other_services_pkgs_block_{$i}_{$i1}\").style; if(this.checked) s.display = \"block\"; else s.display = \"none\";'";
					$js .= "if(!document.getElementById(\"other_services_pkgs_{$i}_{$i1}\").checked) document.getElementById(\"other_services_pkgs_block_{$i}_{$i1}\").style.display = \"none\";";

					$matrix['other_services_count_'.$i1.'_'.$i]['text'] = '{Call:Lang:modules:billing:kolichestvoa:'.Library::serialize(array($e1)).'}';
					$matrix['other_services_count_'.$i1.'_'.$i]['type'] = 'text';
					$matrix['other_services_count_'.$i1.'_'.$i]['comment'] = '{Call:Lang:modules:billing:minimalnokol:'.Library::serialize(array($e1)).'}';
					$matrix['other_services_count_'.$i1.'_'.$i]['warn_function'] = 'regExp::digit';
					$matrix['other_services_count_'.$i1.'_'.$i]['value'] = 1;
					$matrix['other_services_count_'.$i1.'_'.$i]['pre_text'] = "<div id='other_services_pkgs_block_{$i}_{$i1}'>";

					$matrix['other_services_term_'.$i1.'_'.$i]['text'] = '{Call:Lang:modules:billing:srokzakazapo:'.Library::serialize(array($e1, Dates::termsListVars($pData['base_term'], '2'))).'}';
					$matrix['other_services_term_'.$i1.'_'.$i]['type'] = 'text';
					$matrix['other_services_term_'.$i1.'_'.$i]['comment'] = '{Call:Lang:modules:billing:minimalnokol:'.Library::serialize(array($e1)).'}';
					$matrix['other_services_term_'.$i1.'_'.$i]['warn_function'] = 'regExp::digit';
					$matrix['other_services_term_'.$i1.'_'.$i]['post_text'] = '</div>';
				}

				$matrix['other_services_term_'.$i1.'_'.$i]['post_text'] .= "</div>";
				$last = 'other_services_term_'.$i1.'_'.$i;
			}

			$matrix[$last]['post_text'] .= "<script type=\"text/javascript\">\n{$js}\n</script>";
			break;


		case 'promocode':
			$matrix['promocodegroup']['text'] = '{Call:Lang:modules:billing:gruppapromok}';
			$matrix['promocodegroup']['type'] = 'select';
			$matrix['promocodegroup']['additional'] = $promocodegroups;
			$matrix['promocodegroup']['warn'] = '{Call:Lang:modules:billing:neukazanagru}';

			$matrix['discount']['text'] = '{Call:Lang:modules:billing:skidkaprotse}';
			$matrix['discount']['type'] = 'text';
			$matrix['discount']['warn_function'] = 'regExp::float';
			$matrix['discount']['warn'] = '{Call:Lang:modules:billing:neukazanaski}';
			break;


		case 'baseless':
			$matrix['discount']['text'] = '{Call:Lang:modules:billing:skidkaprotse}';
			$matrix['discount']['type'] = 'text';
			$matrix['discount']['warn_function'] = 'regExp::float';
			$matrix['discount']['warn'] = '{Call:Lang:modules:billing:neukazanaski}';
			break;


		case 'other':
			break;
	}

	if($discounts){
		$matrix['disable_discounts_cap']['text'] = '{Call:Lang:modules:billing:otnosheniesd}';
		$matrix['disable_discounts_cap']['type'] = 'caption';

		foreach($discounts as $i => $e){
			$matrix['disable_discounts_'.$i]['text'] = $e;
			$matrix['disable_discounts_'.$i]['type'] = 'select';
			$matrix['disable_discounts_'.$i]['additional'] = array(
				'' => '{Call:Lang:modules:billing:summirovat}',
				'completely' => '{Call:Lang:modules:billing:nesummirovat}',
				'proportional' => '{Call:Lang:modules:billing:ispolzovattu}'
			);
		}
	}
}

?>