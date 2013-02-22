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


$matrix['action_type']['type'] = 'radio';
$matrix['action_type']['additional'] = array('server' => '{Call:Lang:modules:billing:vypolnitivbi}', 'billing' => '{Call:Lang:modules:billing:vypolnittolk}');
$matrix['action_type']['warn'] = '{Call:Lang:modules:billing:neukazanogde}';
$matrix['action_type']['value'] = 'server';

switch($action){
	case 'prolong':
		$matrix['days']['type'] = 'text';
		$matrix['days']['text'] = '{Call:Lang:modules:billing:dobavitdnej}';
		$matrix['days']['warn'] = '{Call:Lang:modules:billing:neukazanosko1}';

		$matrix['sum']['type'] = 'text';
		$matrix['sum']['text'] = '{Call:Lang:modules:billing:sniatsoschet:'.Library::serialize(array($this->getMainCurrencyName())).'}';
		break;

	case 'modify':
		$matrix['pkg']['type'] = 'select';
		$matrix['pkg']['text'] = '{Call:Lang:modules:billing:novyjtarif}';
		$matrix['pkg']['warn'] = '{Call:Lang:modules:billing:neukazannovy}';
		$matrix['pkg']['additional'] = $pkgs;

		$matrix['recalc']['type'] = 'radio';
		$matrix['recalc']['text'] = '{Call:Lang:modules:billing:pereschitato}';
		$matrix['recalc']['warn'] = '{Call:Lang:modules:billing:vyneukazalip2}';
		$matrix['recalc']['additional'] = array('proportional' => '{Call:Lang:modules:billing:proportsiona}', 'fixing' => '{Call:Lang:modules:billing:poukazannojp}');
		$matrix['recalc']['additional_style'] = array('proportional' => 'onClick="hideFormBlock(\'recalc_prop_div\');"', 'fixing' => 'onClick="showFormBlock(\'recalc_prop_div\');"');
		$values['recalc'] = 'proportional';

		$matrix['recalc_prop']['type'] = 'text';
		$matrix['recalc_prop']['text'] = '{Call:Lang:modules:billing:proportsiiap}';
		$matrix['recalc_prop']['warn'] = '{Call:Lang:modules:billing:vyneukazalip3}';
		$matrix['recalc_prop']['pre_text'] = '<div id="recalc_prop_div" style="display: none;">';
		$matrix['recalc_prop']['post_text'] = '</div><script type="text/javascript">
				if(document.getElementById("recalc_fixing").checked) showFormBlock(\'recalc_prop_div\');
				else hideFormBlock(\'recalc_prop_div\');
			</script>';
		$values['recalc_prop'] = 1;

		$matrix['modify']['type'] = 'checkbox';
		$matrix['modify']['text'] = '{Call:Lang:modules:billing:sozdatperson}';
		$matrix['modify']['comment'] = '{Call:Lang:modules:billing:esliparametr}';

		$matrix['unsuspend']['type'] = 'checkbox';
		$matrix['unsuspend']['text'] = '{Call:Lang:modules:billing:razblokirova}';

		break;

	case 'transmit':
		unset($matrix['action_type']);
		$matrix['new_owner']['type'] = 'text';
		$matrix['new_owner']['text'] = '{Call:Lang:modules:billing:loginbilling}';
		$matrix['new_owner']['warn'] = '{Call:Lang:modules:billing:neukazannovy1}';
		break;

	case 'suspend':
		$matrix['reason']['type'] = 'text';
		$matrix['reason']['text'] = '{Call:Lang:modules:billing:prichinablok}';
		break;

	case 'delete':
		$matrix['reason']['type'] = 'text';
		$matrix['reason']['text'] = '{Call:Lang:modules:billing:prichinaudal}';
		break;
}

$matrix['notify']['type'] = 'checkbox';
$matrix['notify']['text'] = '{Call:Lang:modules:billing:uvedomitpolz}';
$matrix['notify']['value'] = 1;

?>