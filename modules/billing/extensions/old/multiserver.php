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



/*
  

 $available_cp -    
 $available_services -     = array( ...)
*/

$available_cp = array(
	'da'=>'Direct Admin',
	'cp'=>'cPanel',
	'isp' => 'ISP Manager',
	'plesk' => 'Plesk',
	'vds' => 'VDS Manager',
	'rucenter'=>$GLOBALS['_lang']['_rucenter'],
	'webnames'=>$GLOBALS['_lang']['_webnames'],
	'reg'=>'Reg.Ru',
	'naunet'=>$GLOBALS['_lang']['_naunet'],
	'directi' => $GLOBALS['_lang']['_directi'],
	'started' => ' Started.ru',
	'_default' => $GLOBALS['_lang']['_panels']
);

$direct_cp_values['da'] = array('domain', 'eml');
$direct_cp_values['cp'] = array('domain', 'eml');
$direct_cp_values['isp'] = array('domain', 'eml');
$direct_cp_values['plesk'] = array('domain', 'eml', 'name', 'company');
$direct_cp_values['vds'] = array('domain', 'eml');
$direct_cp_values['rucenter'] = array('domain_owner', 'c_name', 'eml', 'country', 'region', 'city', 'street', 'zipcode', 'komu', 'phone', 'fax', 'birthday', 'passport', 'passport_issue', 'passport_issue_day', 'register_addr', 'inn', 'kpp', 'org', 'name_org', 'parent_org', 'org_u_address');

$direct_cp_values['webnames'] = array('domain_owner', 'c_name', 'eml', 'country', 'region', 'city', 'street', 'zipcode', 'komu', 'phone', 'fax', 'birthday', 'passport', 'passport_issue', 'passport_issue_day', 'register_addr', 'inn', 'kpp', 'org', 'name_org', 'parent_org', 'org_u_address', 'bank');
$direct_cp_values['reg'] = array('domain_owner', 'c_name', 'eml', 'country', 'region', 'city', 'street', 'zipcode', 'komu', 'phone', 'fax', 'birthday', 'passport', 'passport_issue', 'passport_issue_day', 'register_addr', 'inn', 'kpp', 'org', 'name_org', 'parent_org', 'org_u_address');
$direct_cp_values['naunet'] = array('domain_owner', 'c_name', 'eml', 'country', 'region', 'city', 'street', 'zipcode', 'komu', 'phone', 'fax', 'birthday', 'passport', 'passport_issue', 'passport_issue_day', 'register_addr', 'inn', 'kpp', 'org', 'name_org', 'parent_org', 'org_u_address', 'ogrn', 'regdocuments');
$direct_cp_values['directi'] =  array('domain_owner', 'c_name', 'eml', 'country', 'region', 'city', 'street', 'zipcode', 'komu', 'phone', 'fax', 'birthday', 'passport', 'passport_issue', 'passport_issue_day', 'register_addr', 'inn', 'kpp', 'org', 'name_org', 'parent_org', 'org_u_address');
$direct_cp_values['started'] = array('domain_owner', 'c_name', 'eml', 'country', 'region', 'city', 'street', 'zipcode', 'komu', 'phone', 'fax', 'birthday', 'passport', 'passport_issue', 'passport_issue_day', 'register_addr', 'inn', 'kpp', 'org', 'name_org', 'parent_org', 'org_u_address');


$cp_login_patterns['da'] = array('login'=>'^[a-zA-Z]\w{3,9}$', 'pwd'=>'.{6,64}', 'new_object'=>false);
$cp_login_patterns['cp'] = array('login'=>'^[a-zA-Z]\w{3,9}$', 'pwd'=>'.{6,64}', 'new_object'=>false);
$cp_login_patterns['isp'] = array('login'=>'^[a-zA-Z]\w{3,9}$', 'pwd'=>'.{6,64}', 'new_object'=>false);
$cp_login_patterns['plesk'] = array('login'=>'^[a-zA-Z]\w{3,9}$', 'pwd'=>'.{6,64}', 'new_object'=>false);
$cp_login_patterns['vds'] = array('login'=>'^[a-zA-Z]\w{3,9}$', 'pwd'=>'.{6,64}', 'new_object'=>false);

$cp_login_patterns['rucenter'] = array('login'=>'^(\d{1,15}\/NIC-D|NewObject)$', 'pwd'=>'[A-Za-z0-9]{1,30}', 'new_object'=>true);
$cp_login_patterns['webnames'] = array('pwd'=>'.{1,16}', 'new_object'=>true);
$cp_login_patterns['reg'] = array('pwd'=>'.{1,16}', 'new_object'=>true);
$cp_login_patterns['naunet'] = array('pwd'=>'.{1,16}', 'new_object'=>true);
$cp_login_patterns['directi'] = array('pwd'=>'.{1,16}', 'new_object'=>true);
$cp_login_patterns['started'] = array('pwd'=>'.{1,16}', 'new_object'=>true);

$available_services['service_hosting'] = array('da'=>'da', 'cp'=>'cp', 'isp' => 'isp', 'plesk' => 'plesk');
$available_services['service_reseller'] = array('isp' => 'isp', 'da' => 'da');
$available_services['service_vds'] = array('vds' => 'vds');
$available_services['service_domain'] = array('rucenter'=>'rucenter', 'webnames'=>'webnames', 'directi'=>'directi', 'reg' => 'reg', 'naunet' => 'naunet', 'started' => 'started');

foreach($GLOBALS['services'] as $i=>$e){	$available_services[$i]['_default'] = '_default';}

$rate_components['da'] = array(
	'bandwidth' => array('text' => $GLOBALS['_lang']['_trafik'], 'type' => 'text'),
	'quota' => array('text' => $GLOBALS['_lang']['_diskovoe'], 'type' => 'text'),
	'vdomains' => array('text' => $GLOBALS['_lang']['_kolichest'], 'type' => 'text'),
	'nsubdomains' => array('text' => $GLOBALS['_lang']['_kolichest1'], 'type' => 'text'),
	'nemails' => array('text' => $GLOBALS['_lang']['_kolichest2'], 'type' => 'text'),
	'nemailf' => array('text' => $GLOBALS['_lang']['_emailp'], 'type' => 'text'),
	'nemailml' => array('text' => $GLOBALS['_lang']['_listyra'], 'type' => 'text'),
	'nemailr' => array('text' => $GLOBALS['_lang']['_avtootve'], 'type' => 'text'),
	'mysql' => array('text' => $GLOBALS['_lang']['_mysqlba'], 'type' => 'text'),
	'domainptr' => array('text' => $GLOBALS['_lang']['_domennye'], 'type' => 'text'),
	'ftp' => array('text' => 'FTP', 'type' => 'text'),
	'aftp' => array('text' => $GLOBALS['_lang']['_anonimny'], 'type' => 'checkbox'),
	'cgi' => array('text' => 'CGI', 'type' => 'checkbox'),
	'php' => array('text' => 'PHP', 'type' => 'checkbox'),
	'spam' => array('text' => 'SpamAssassin', 'type' => 'checkbox'),
	'ssl' => array('text' => 'SSL', 'type' => 'checkbox'),
	'ssh' => array('text' => 'SSH', 'type' => 'checkbox'),
	'cron' => array('text' => 'Cron', 'type' => 'checkbox'),
	'sysinfo' => array('text' => $GLOBALS['_lang']['_sistemna'], 'type' => 'checkbox'),
	'dnscontrol' => array('text' => $GLOBALS['_lang']['_upravlen25'], 'type' => 'checkbox'),
	'ips' => array('text' => $GLOBALS['_lang']['_kolichest9'], 'type' => 'text'),
	'catchall' => array('text' => 'Catch-all e-mail', 'type' => 'checkbox'),
	'userssh' => array('text' => $GLOBALS['_lang']['_sshdostup'], 'type' => 'checkbox'),
	'oversell' => array('text' => $GLOBALS['_lang']['_overselling'], 'type' => 'checkbox'),
	'serverip' => array('text' => $GLOBALS['_lang']['_obshiip'], 'type' => 'checkbox')
);

$rate_defaults['da'] = array(
	'suspend_at_limit' => 'ON',
	'notify' => 'yes',
	'language' => 'ru',
	'dns' => 'OFF',
	'ip' => 'shared'
);

$rate_components['cp'] = array(
	'quota' => array('text' => $GLOBALS['_lang']['_diskovoe'], 'type' => 'text'),
	'ip' => array('text' => $GLOBALS['_lang']['_vydelennip'], 'type' => 'checkbox'),
	'cgi' => array('text' => 'CGI', 'type' => 'checkbox'),
	'frontpage' => array('text' => 'Frontpage', 'type' => 'checkbox'),
	'maxftp' => array('text' => 'FTP', 'type' => 'text'),
	'maxsql' => array('text' => $GLOBALS['_lang']['_mysqlba'], 'type' => 'text'),
	'maxpop' => array('text' => $GLOBALS['_lang']['_kolichest2'], 'type' => 'text'),
	'maxlst' => array('text' => $GLOBALS['_lang']['_listyra'], 'type' => 'text'),
	'maxsub' => array('text' => $GLOBALS['_lang']['_kolichest1'], 'type' => 'text'),
	'maxpark' => array('text' => $GLOBALS['_lang']['_domennye'], 'type' => 'text'),
	'maxaddon' => array('text' => $GLOBALS['_lang']['_kolichest'], 'type' => 'text'),
	'hasshell' => array('text' => 'SSH', 'type' => 'checkbox'),
	'bwlimit' => array('text' => $GLOBALS['_lang']['_trafik'], 'type' => 'text')
);

$rate_defaults['cp'] = array(
	'cpmod' => 'x3',
	'featurelist' => 'default',
	'lang' => 'russian'
);

$rate_components['isp'] = array(
	'bandwidthlimit' => array('text' => $GLOBALS['_lang']['_trafik'], 'type' => 'text'),
	'disklimit' => array('text' => $GLOBALS['_lang']['_diskovoe'], 'type' => 'text'),
	'domainlimit' => array('text' => $GLOBALS['_lang']['_kolichest'], 'type' => 'text'),
	'webdomainlimit' => array('text' => 'WWW-', 'type' => 'text'),
	'maillimit' => array('text' => $GLOBALS['_lang']['_kolichest2'], 'type' => 'text'),
	'maildomainlimit' => array('text' => 'Mail-', 'type' => 'text'),
	'baselimit' => array('text' => ' ', 'type' => 'text'),
	'baseuserlimit' => array('text' => '  ', 'type' => 'text'),
	'ftplimit' => array('text' => 'FTP', 'type' => 'text'),
	'shell' => array('text' => 'Shell', 'type' => 'checkbox'),
	'ssl' => array('text' => 'SSL', 'type' => 'checkbox'),
	'phpmod' => array('text' => 'PHP   Apache', 'type' => 'checkbox'),
	'phpcgi' => array('text' => 'PHP  CGI', 'type' => 'checkbox'),
	'phpfcgi' => array('text' => 'PHP  fast CGI', 'type' => 'checkbox'),
	'safemode' => array('text' => 'PHP   Safe Mode', 'type' => 'checkbox'),
	'cgi' => array('text' => 'CGI', 'type' => 'checkbox'),
	'ssi' => array('text' => 'SSI', 'type' => 'checkbox'),
	'cpulimit' => array('text' => '  CPU', 'type' => 'text'),
	'memlimit' => array('text' => '  ', 'type' => 'text'),
	'proclimit' => array('text' => '   ', 'type' => 'text'),
	'reseller_status' => array('text' => ' ', 'type' => 'checkbox'),
	'userlimit' => array('text' => '    (  )', 'type' => 'text')
);

$rate_defaults['isp'] = array(
	'confirm' => 'on'
);

$rate_components['plesk'] = array(
	'disk_space' => 			array('text' => ' ', 'type' => 'text'),
	'max_db' => 				array('text' => '   ', 'type' => 'text'),
	'max_traffic' => 			array('text' => '  ', 'type' => 'text'),
	'max_dom' => 				array('text' => '  ', 'type' => 'text'),
	'max_dom_aliases' => 		array('text' => '   ', 'type' => 'text'),
	'max_subdom' => 			array('text' => '  ', 'type' => 'text'),
	'max_wu' => 				array('text' => '  -', 'type' => 'text'),
	'max_box' => 				array('text' => '   ', 'type' => 'text'),
	'mbox_quota' => 			array('text' => '     ', 'type' => 'text'),
	'max_redir' => 				array('text' => '   ', 'type' => 'text'),
	'max_mg' => 				array('text' => '   ', 'type' => 'text'),
	'max_resp' => 				array('text' => '   ', 'type' => 'text'),
	'max_maillists' => 			array('text' => '   ', 'type' => 'text'),
	'max_webapps' => 			array('text' => '  -', 'type' => 'text'),
	'create_domains' => 		array('text' => ' ', 'type' => 'checkbox'),
	'manage_phosting' => 		array('text' => '  ', 'type' => 'checkbox'),
	'manage_quota' => 			array('text' => '    ', 'type' => 'checkbox'),
	'manage_domain_aliases' => 	array('text' => '   ', 'type' => 'checkbox'),
	'manage_subdomains' => 		array('text' => ' ', 'type' => 'checkbox'),
	'manage_dns' => 			array('text' => '  DNS', 'type' => 'checkbox'),
	'manage_log' => 			array('text' => '   ', 'type' => 'checkbox'),
	'manage_anonftp' => 		array('text' => '  FTP', 'type' => 'checkbox'),
	'manage_webapps' => 		array('text' => ' - Tomcat', 'type' => 'checkbox'),
	'remote_access_interface' =>array('text' => '   XML ', 'type' => 'checkbox'),
	'cp_access' => 				array('text' => '   ', 'type' => 'checkbox'),
	'manage_dashboard' => 		array('text' => '  ', 'type' => 'checkbox'),
	'manage_php_safe_mode' => 	array('text' => '   PHP', 'type' => 'checkbox'),
	'manage_crontab' => 		array('text' => '  ', 'type' => 'checkbox'),
	'change_limits' => 			array('text' => '  ', 'type' => 'checkbox'),
	'manage_webstat' => 		array('text' => ' -', 'type' => 'checkbox'),
	'manage_maillists' => 		array('text' => '  ', 'type' => 'checkbox'),
	'manage_spamfilter' => 		array('text' => '  ', 'type' => 'checkbox'),
	'manage_virusfilter' => 	array('text' => '  ', 'type' => 'checkbox'),
	'manage_performance' => 	array('text' => '   ', 'type' => 'checkbox'),
	'allow_ftp_backups' => 		array('text' => '      ,      FTP-', 'type' => 'checkbox'),
	'allow_local_backups' =>	array('text' => '      ,        ', 'type' => 'checkbox')
);

$rate_defaults['plesk'] = array(
	'use_sbnet' => '',
	'selected_ip' => '62.152.34.5'
);

$rate_defaults['reg'] = array(
	'fail_if_no_money' => '1',
	'no_bill_notify' => '0',
	'private_person_flag' => '1'
);

$rate_defaults['naunet'] = array(
	'private-whois' => 'no'
);

$rate_components['vds'] = array(
	'disk' => array('text' => $GLOBALS['_lang']['_diskovoe'], 'type' => 'text'),
	'mem' => array('text' => '', 'type' => 'text'),
	'cpu' => array('text' => '', 'type' => 'text'),
	'proc' => array('text' => '', 'type' => 'text'),
	'desc' => array('text' => ' ', 'type' => 'text'),
	'traf' => array('text' => '', 'type' => 'text')
);

$rate_defaults['vds'] = array(
	'disktempl' => ''
);

?>
