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


$matrix['time_restrict']['text'] = 'Ограничить возможность заказа по времени';
$matrix['time_restrict']['type'] = 'calendar';

$matrix['time_restrict_prolong']['text'] = 'Ограничить возможность продления по времени';
$matrix['time_restrict_prolong']['comment'] = 'Здесь указывается до какого (предельно) числа может быть продлен тариф';
$matrix['time_restrict_prolong']['type'] = 'calendar2';

$matrix['user_types_restrict']['text'] = 'Ограничить по';
$matrix['user_types_restrict']['type'] = 'checkbox_array';
$matrix['user_types_restrict']['additional'] = array(
	'types' => 'типам анкет',
	'groups' => 'группам пользователей',
	'client_levels' => 'уровням доверия клиентам'
);

$matrix['user_types_restrict']['additional_style'] = array(
	'types' => 'onClick="showRestrictBlocks();"',
	'groups' => 'onClick="showRestrictBlocks();"',
	'client_levels' => 'onClick="showRestrictBlocks();"'
);

$matrix['types_restrict']['text'] = 'Типы пользователей, имеющие право заказать тариф';
$matrix['types_restrict']['type'] = 'checkbox_array';
$matrix['types_restrict']['additional'] = $GLOBALS['Core']->getUserFormTypes();
$matrix['types_restrict']['additional_entry_style'] = ' id="types_restrict_env" style="display: none;"';

$matrix['groups_restrict']['text'] = 'Группы пользователей, имеющие право заказать тариф';
$matrix['groups_restrict']['type'] = 'checkbox_array';
$matrix['groups_restrict']['additional'] = Library::array_merge(array('@none' => 'Кому не присвоена группа'), $GLOBALS['Core']->getUserGroups());
$matrix['groups_restrict']['additional_entry_style'] = ' id="groups_restrict_env" style="display: none;"';

$matrix['client_levels_restrict']['text'] = 'Уровни клиентов, имеющие право заказать тариф';
$matrix['client_levels_restrict']['type'] = 'checkbox_array';
$matrix['client_levels_restrict']['additional'] = Library::array_merge(array('@none' => 'Кому не присвоен уровень доверия'), $this->getLoyaltyLevels());
$matrix['client_levels_restrict']['additional_entry_style'] = ' id="client_levels_restrict_env" style="display: none;"';

$matrix['user_types_restrict_logic']['text'] = 'Логика применения ограничений пользователей';
$matrix['user_types_restrict_logic']['type'] = 'select';
$matrix['user_types_restrict_logic']['additional'] = array(
	'OR' => 'ИЛИ',
	'AND' => 'И'
);

$matrix['user_types_restrict_logic']['additional_entry_style'] = ' id="user_types_restrict_logic_env" style="display: none;"';
$matrix['user_types_restrict_logic']['post_text'] = '<script type="text/javascript">
	function showRestrictBlocks(){
		if(document.getElementById("user_types_restrict_types").checked && document.getElementById("types_restrict_env")){
			document.getElementById("types_restrict_env").style.display = "block";
			document.getElementById("user_types_restrict_logic_env").style.display = "block";
		}
		if(document.getElementById("user_types_restrict_groups").checked && document.getElementById("groups_restrict_env")){
			document.getElementById("groups_restrict_env").style.display = "block";
			document.getElementById("user_types_restrict_logic_env").style.display = "block";
		}
		if(document.getElementById("user_types_restrict_client_levels").checked && document.getElementById("client_levels_restrict_env")){
			document.getElementById("client_levels_restrict_env").style.display = "block";
			document.getElementById("user_types_restrict_logic_env").style.display = "block";
		}
	}

	showRestrictBlocks();
</script>';

?>