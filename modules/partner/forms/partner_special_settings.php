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


$matrix['usePersonalSettings']['text'] = '';
$matrix['usePersonalSettings']['type'] = 'radio';
$matrix['usePersonalSettings']['additional'] = array(
	'default' => '{Call:Lang:modules:partner:ispolzovatna}',
	'hand' => '{Call:Lang:modules:partner:vystavitspet}'
);
$matrix['usePersonalSettings']['additional_style'] = array(
	'default' => 'onClick="hideFormBlock(\'forSettings\');"',
	'hand' => 'onClick="showFormBlock(\'forSettings\');"'
);
$values['usePersonalSettings'] = 'default';

$matrix['usePersonalSettingsCaption']['pre_text'] = '<div id="forSettings" style="display: none;">';
$matrix['usePersonalSettingsCaption']['text'] = '';
$matrix['usePersonalSettingsCaption']['type'] = 'caption';

$matrix['partnerClicksRegStyle']['text'] = '{Call:Lang:modules:partner:uchityvatkli}';
$matrix['partnerClicksRegStyle']['type'] = 'select';
$matrix['partnerClicksRegStyle']['additional'] = array(
	'0' => '{Call:Lang:modules:partner:tolkosprover}',
	'1' => '{Call:Lang:modules:partner:sliubykhsajt}',
	'2' => '{Call:Lang:modules:partner:otkudaugodno}',
);

$matrix['partnerOrderRegStyle']['text'] = '{Call:Lang:modules:partner:uchityvatpar}';
$matrix['partnerOrderRegStyle']['type'] = 'select';
$matrix['partnerOrderRegStyle']['additional'] = array(
	'0' => '{Call:Lang:modules:partner:tolkosprover}',
	'1' => '{Call:Lang:modules:partner:sliubykhsajt}',
	'2' => '{Call:Lang:modules:partner:otkudaugodno}',
);

$matrix['partnerCookieLife']['text'] = '{Call:Lang:modules:partner:srokzhiznico}';
$matrix['partnerCookieLife']['type'] = 'text';

$matrix['partnerClickInterval']['text'] = '{Call:Lang:modules:partner:minimalnyjin}';
$matrix['partnerClickInterval']['type'] = 'text';

$matrix['viewClients']['text'] = 'Партнер может видеть клиентов пришедших по его ссылке';
$matrix['viewClients']['type'] = 'checkbox';

$matrix['viewReferals']['text'] = 'Партнер может видеть других партнеров (рефералов) пришедших по его ссылке';
$matrix['viewReferals']['type'] = 'checkbox';

$matrix['partnerSiteRegFree']['text'] = '{Call:Lang:modules:partner:registratsii}';
$matrix['partnerSiteRegFree']['type'] = 'checkbox';
$matrix['partnerSiteRegFree']['post_text'] = '</div><script type="text/javascript">
	if(document.getElementById("usePersonalSettings_hand").checked) showFormBlock(\'forSettings\');
</script>';

?>