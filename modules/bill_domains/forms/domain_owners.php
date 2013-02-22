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


$matrix['blankName']['text'] = '{Call:Lang:modules:bill_domains:imiaankety}';
$matrix['blankName']['type'] = 'text';
$matrix['blankName']['comment'] = '{Call:Lang:modules:bill_domains:liuboeponiat}';
$matrix['blankName']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalii}';

$matrix['type']['text'] = '{Call:Lang:modules:bill_domains:domenregistr}';
$matrix['type']['type'] = 'radio';
$matrix['type']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalik}';
$matrix['type']['additional'] = array(
	'person' => '{Call:Lang:modules:bill_domains:fizicheskogo}',
	'organization' => '{Call:Lang:modules:bill_domains:iuridichesko}',
	'ip' => '{Call:Lang:modules:bill_domains:individualno}'
);

$matrix['type']['additional_style'] = array(
	'person' => ' onClick="showExtraDomainFields();"',
	'organization' => ' onClick="showExtraDomainFields();"',
	'ip' => ' onClick="showExtraDomainFields();"',
);

$matrix['correspond']['text'] = 'Анкета должна позволять регистрировать домены';
$matrix['correspond']['type'] = 'checkbox_array';
$matrix['correspond']['additional'] = array(
	'ru' => 'RU, SU, РФ',
	'tj' => 'UZ, TJ',
	'asia' => 'ASIA',
	'us' => 'US',
	'eu' => 'EU'
);

$matrix['correspond']['additional_style'] = array(
	'ru' => ' onClick="showExtraDomainFields();"',
	'tj' => ' onClick="showExtraDomainFields();"',
	'asia' => ' onClick="showExtraDomainFields();"',
	'us' => ' onClick="showExtraDomainFields();"',
);

$matrix['fname']['text'] = '{Call:Lang:modules:bill_domains:imia}';
$matrix['fname']['type'] = 'text';
$matrix['fname']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalii1}';

$matrix['pname']['text'] = '{Call:Lang:modules:bill_domains:otchestvo}';
$matrix['pname']['type'] = 'text';
$matrix['pname']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalio}';

$matrix['lname']['text'] = '{Call:Lang:modules:bill_domains:familiia}';
$matrix['lname']['type'] = 'text';
$matrix['lname']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalif}';

$matrix['country']['text'] = '{Call:Lang:modules:bill_domains:strana}';
$matrix['country']['type'] = 'select';
$matrix['country']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalis}';
$matrix['country']['additional'] = Geo::getCountries();

$matrix['region']['text'] = '{Call:Lang:modules:bill_domains:region}';
$matrix['region']['type'] = 'text';
$matrix['region']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalir}';

$matrix['city']['text'] = '{Call:Lang:modules:bill_domains:gorodseloder}';
$matrix['city']['type'] = 'text';
$matrix['city']['warn'] = '{Call:Lang:modules:bill_domains:vyneimianase}';

$matrix['street']['text'] = '{Call:Lang:modules:bill_domains:adresnaprime}';
$matrix['street']['type'] = 'text';
$matrix['street']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalit}';

$matrix['zip']['text'] = '{Call:Lang:modules:bill_domains:pochtovyjind}';
$matrix['zip']['type'] = 'text';
$matrix['zip']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalip}';

$matrix['phone']['text'] = '{Call:Lang:modules:bill_domains:telefonvtchk}';
$matrix['phone']['type'] = 'text';
$matrix['phone']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalit1}';
$matrix['phone']['warn_function'] = 'regExp::Phone';

$matrix['fax']['text'] = '{Call:Lang:modules:bill_domains:faksnaprimer}';
$matrix['fax']['type'] = 'text';
$matrix['fax']['warn_function'] = 'regExp::Phone';

$matrix['eml']['text'] = 'E-mail';
$matrix['eml']['type'] = 'text';
$matrix['eml']['warn'] = '{Call:Lang:modules:bill_domains:vyneukazalie}';

$matrix['birth']['pre_text'] = '<div id="ru_person_block" style="display: none">';
$matrix['birth']['text'] = '{Call:Lang:modules:bill_domains:datarozhdeni}';
$matrix['birth']['warn'] = 'Вы не указали дату рождения';
$matrix['birth']['checkConditions'] = array('type' => 'person', 'correspond' => 'ru');
$matrix['birth']['type'] = 'calendar2';

$matrix['passport']['text'] = '{Call:Lang:modules:bill_domains:seriiainomer}';
$matrix['passport']['warn'] = 'Вы не указали номер паспорта';
$matrix['passport']['checkConditions'] = array('type' => 'person', 'correspond' => 'ru');
$matrix['passport']['type'] = 'text';

$matrix['passportIssue']['text'] = '{Call:Lang:modules:bill_domains:kemvydanpasp}';
$matrix['passportIssue']['warn'] = 'Вы не указали кем выдан паспорт';
$matrix['passportIssue']['type'] = 'text';
$matrix['passportIssue']['checkConditions'] = array('type' => 'person', 'correspond' => 'ru');

$matrix['passportIssueDay']['text'] = '{Call:Lang:modules:bill_domains:kogdavydanpa}';
$matrix['passportIssueDay']['type'] = 'calendar2';
$matrix['passportIssueDay']['warn'] = 'Вы не указали дату выдачи паспорта';
$matrix['passportIssueDay']['checkConditions'] = array('type' => 'person', 'correspond' => 'ru');

$matrix['registration']['text'] = '{Call:Lang:modules:bill_domains:adresregistr}';
$matrix['registration']['type'] = 'textarea';

$matrix['document1']['text'] = 'Скан-копия паспорта';
$matrix['document1']['comment'] = 'Необходимо загрузить скан-копию (либо цветную цифровую фотографию страниц паспорта с фотографией, сведениями о владельце и месте регистрации)';
$matrix['document1']['type'] = 'file';
$matrix['document1']['warn'] = 'Не загружен скан страницы паспорта';
$matrix['document1']['checkConditions'] = array('type' => 'person', 'correspond' => 'ru');
$matrix['document1']['additional'] = array(
	'allow_ext' => array('.gif', '.jpg'),
	'dstFolder' => $this->Core->getParam('docScanFolder', $this->mod),
	'newName' => time().'-1'
);

$matrix['document2']['text'] = 'Скан-копия паспорта';
$matrix['document2']['comment'] = 'При необходимости можно загрузить несколько страниц';
$matrix['document2']['type'] = 'file';
$matrix['document2']['additional'] = array(
	'allow_ext' => array('.gif', '.jpg'),
	'dstFolder' => $this->Core->getParam('docScanFolder', $this->mod),
	'newName' => time().'-2'
);

$matrix['document3']['text'] = 'Скан-копия паспорта';
$matrix['document3']['comment'] = 'При необходимости можно загрузить несколько страниц';
$matrix['document3']['type'] = 'file';
$matrix['document3']['post_text'] = '</div>';
$matrix['document3']['additional'] = array(
	'allow_ext' => array('.gif', '.jpg'),
	'dstFolder' => $this->Core->getParam('docScanFolder', $this->mod),
	'newName' => time().'-3'
);

$matrix['ipInn']['pre_text'] = '<div id="ru_ip_block" style="display: none">';
$matrix['ipInn']['post_text'] = '</div>';
$matrix['ipInn']['text'] = '{Call:Lang:modules:bill_domains:innindividua}';
$matrix['ipInn']['warn'] = 'Не указан ИНН';
$matrix['ipInn']['checkConditions'] = array('type' => 'ip', 'correspond' => 'ru');
$matrix['ipInn']['type'] = 'text';

$matrix['company']['pre_text'] = '<div id="org_block" style="display: none">';
$matrix['company']['text'] = '{Call:Lang:modules:bill_domains:nazvaniekomp}';
$matrix['company']['warn'] = 'Не указано имя компании';
$matrix['company']['checkConditions']['type'] = 'organization';
$matrix['company']['type'] = 'text';

$matrix['inn']['pre_text'] = '<div id="ru_org_block" style="display: none">';
$matrix['inn']['text'] = '{Call:Lang:modules:bill_domains:inn}';
$matrix['inn']['warn'] = 'Не указан ИНН';
$matrix['inn']['checkConditions'] = array('type' => 'organization', 'correspond' => array('ru', 'tj'));
$matrix['inn']['type'] = 'text';

$matrix['kpp']['text'] = '{Call:Lang:modules:bill_domains:kpp}';
$matrix['kpp']['warn'] = 'Не указан КПП';
$matrix['kpp']['checkConditions'] = array('type' => 'organization', 'correspond' => array('ru', 'tj'));
$matrix['kpp']['type'] = 'text';

$matrix['ogrn']['text'] = '{Call:Lang:modules:bill_domains:nomersvidete}';
$matrix['ogrn']['warn'] = 'Не указан ОГРН';
$matrix['ogrn']['checkConditions'] = array('type' => 'organization', 'correspond' => array('ru', 'tj'));
$matrix['ogrn']['type'] = 'text';

$matrix['address']['text'] = '{Call:Lang:modules:bill_domains:iuridicheski}';
$matrix['address']['warn'] = 'Не указан юридический адрес';
$matrix['address']['checkConditions'] = array('type' => 'organization', 'correspond' => array('ru', 'tj'));
$matrix['address']['type'] = 'textarea';

$matrix['paddress']['text'] = '{Call:Lang:modules:bill_domains:pochtovyjadr}';
$matrix['paddress']['warn'] = 'Не указан почтовый адрес';
$matrix['paddress']['checkConditions'] = array('type' => 'organization', 'correspond' => array('ru', 'tj'));
$matrix['paddress']['type'] = 'textarea';

$matrix['bank']['pre_text'] = '<div id="org_tj_block">';
$matrix['bank']['text'] = '{Call:Lang:modules:bill_domains:bank}';
$matrix['bank']['warn'] = 'Не указан банк';
$matrix['bank']['checkConditions'] = array('type' => 'organization', 'correspond' => 'tj');
$matrix['bank']['type'] = 'text';

$matrix['bankNum']['text'] = '{Call:Lang:modules:bill_domains:nomerbankovs}';
$matrix['bankNum']['warn'] = 'Не указан номер банковского счета';
$matrix['bankNum']['checkConditions'] = array('type' => 'organization', 'correspond' => 'tj');
$matrix['bankNum']['type'] = 'text';

$matrix['bankBik']['text'] = '{Call:Lang:modules:bill_domains:bik}';
$matrix['bankBik']['warn'] = 'Не указан БИК (МФО)';
$matrix['bankBik']['type'] = 'text';
$matrix['bankBik']['checkConditions'] = array('type' => 'organization', 'correspond' => 'tj');
$matrix['bankBik']['post_text'] = '</div>';

$matrix['document4']['text'] = 'Скан-копия свидетельства о регистрации в реестре ЕГРЮЛ';
$matrix['document4']['comment'] = 'Необходимо загрузить скан-копию (либо цветную цифровую фотографию свидетельства о регистрации юридического лица в ЕГРЮЛ)';
$matrix['document4']['type'] = 'file';
$matrix['document4']['warn'] = 'Не загружено свидетельство о регистрации';
$matrix['document4']['checkConditions'] = array('type' => 'organization', 'correspond' => array('ru', 'tj'));
$matrix['document4']['additional'] = array(
	'allow_ext' => array('.gif', '.jpg'),
	'dstFolder' => $this->Core->getParam('docScanFolder', $this->mod),
	'newName' => time().'-4'
);

$matrix['document5']['text'] = 'Скан-копия свидетельства о регистрации в реестре ЕГРЮЛ';
$matrix['document5']['comment'] = 'При необходимости можно загрузить несколько страниц';
$matrix['document5']['type'] = 'file';
$matrix['document5']['additional'] = array(
	'allow_ext' => array('.gif', '.jpg'),
	'dstFolder' => $this->Core->getParam('docScanFolder', $this->mod),
	'newName' => time().'-5'
);
$matrix['document5']['post_text'] = '</div></div>';

$matrix['us_caption']['pre_text'] = '<div id="us_block" style="display: none;">';
$matrix['us_caption']['text'] = 'Для доменов US';
$matrix['us_caption']['type'] = 'caption';

$matrix['purpose']['text'] = 'Назначение домена';
$matrix['purpose']['warn'] = 'Не указано назначение домена';
$matrix['purpose']['checkConditions'] = array('type' => 'organization', 'correspond' => 'us');
$matrix['purpose']['type'] = 'select';
$matrix['purpose']['additional'] = array(
	'P1' => 'Для бизнеса и коммерции',
	'P2' => 'Религиозные или общественные организации',
	'P3' => 'Личное пользование',
	'P4' => 'Для образования',
	'P5' => 'Государственное назначение'
);

$matrix['us_citizen']['text'] = 'Для США вы';
$matrix['us_citizen']['warn'] = 'Не указано какое отношение вы имеете к США';
$matrix['us_citizen']['checkConditions'] = array('type' => 'organization', 'correspond' => 'us');
$matrix['us_citizen']['type'] = 'select';
$matrix['us_citizen']['additional'] = array('C11' => 'Гражданин США', 'C12' => 'Лицо постоянно проживающее в США');
$matrix['us_citizen']['post_text'] = '</div>';

$matrix['asia_caption']['pre_text'] = '<div id="asia_block" style="display: none;">';
$matrix['asia_caption']['text'] = 'Для доменов ASIA';
$matrix['asia_caption']['type'] = 'caption';

$matrix['default_ced']['text'] = 'Использовать default CED-контакт';
$matrix['default_ced']['comment'] = 'Для регистрации домена .ASIA вам необходимо использовать CED-контакт по умолчанию, либо заполнить поля доказывающие вашу причастность к Азиатско-Тихоокеанскому региону.';
$matrix['default_ced']['type'] = 'checkbox';
$matrix['default_ced']['additional_style'] = ' onClick="showCED();"';

$matrix['entity_type']['pre_text'] = '<div id="asia_ced_block" style="display: none;">';
$matrix['entity_type']['text'] = 'Тип контакта';
$matrix['entity_type']['warn'] = 'Не указан тип CED-контакта';
$matrix['entity_type']['checkConditions'] = array('correspond' => 'asia', 'default_ced' => '');
$matrix['entity_type']['type'] = 'select';
$matrix['entity_type']['additional'] = array(
	'naturalPerson' => 'Для частного лица',
	'corporation' => 'Для организации',
	'cooperative' => 'Кооперативный',
	'partnership' => 'Партнерский',
	'government' => 'Государственный',
	'politicalParty' => 'Для политической партии',
	'society' => 'Для общественной организации',
	'institution' => 'Для образовательного учреждения',
	'other' => 'Другое',
);

$matrix['ident_form']['text'] = 'Документ для идентификации';
$matrix['ident_form']['warn'] = 'Не указан документ для идентификации';
$matrix['ident_form']['checkConditions'] = array('correspond' => 'asia', 'default_ced' => '');
$matrix['ident_form']['type'] = 'select';
$matrix['ident_form']['additional'] = array(
	'passport' => 'Паспорт',
	'certificate' => 'Сертификат',
	'legislation' => 'Закон',
	'societiesRegistry' => 'Регистрация общественной организации',
	'politicalPartyRegistry' => 'Регистрация политической партии',
	'other' => 'Другое',
);

$matrix['ident_number']['text'] = 'Идентификационный номер';
$matrix['ident_number']['comment'] = 'Это номер документа указанного для идентификации - паспорта, свидетельства о регистрации юр. лица или другого';
$matrix['ident_number']['warn'] = 'Не указан идентификационный номер';
$matrix['ident_number']['checkConditions'] = array('correspond' => 'asia', 'default_ced' => '');
$matrix['ident_number']['type'] = 'text';
$matrix['ident_number']['post_text'] = '</div></div><script type="text/javascript">
	function showExtraDomainFields(){
		if(document.getElementById("type_person").checked && document.getElementById("correspond_ru").checked) showFormBlock("ru_person_block");
		else hideFormBlock("ru_person_block");

		if(document.getElementById("type_organization").checked){
			showFormBlock("org_block");

			if(document.getElementById("correspond_ru").checked || document.getElementById("correspond_tj").checked) showFormBlock("ru_org_block");
			else hideFormBlock("ru_org_block");

			if(document.getElementById("correspond_tj").checked) showFormBlock("org_tj_block");
			else hideFormBlock("org_tj_block");
		}
		else hideFormBlock("org_block");

		if(document.getElementById("type_ip").checked && document.getElementById("correspond_ru").checked) showFormBlock("org_ru_block");
		else hideFormBlock("org_ru_block");

		if(document.getElementById("correspond_asia").checked) showFormBlock("asia_block");
		else hideFormBlock("asia_block");

		if(document.getElementById("correspond_us").checked) showFormBlock("us_block");
		else hideFormBlock("us_block");
	}

	function showCED(){
		if(!document.getElementById("default_ced").checked) showFormBlock("asia_ced_block");
		else hideFormBlock("asia_ced_block");
	}

	showCED();
	showExtraDomainFields();
</script>';

?>