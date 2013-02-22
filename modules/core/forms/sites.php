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


$matrix['url']['type'] = 'text';
$matrix['url']['text'] = '{Call:Lang:core:core:urlsajtavkli}';
$matrix['url']['warn'] = '{Call:Lang:core:core:neukazanurl}';
$matrix['url']['warn_pattern'] = '/^(http|https)\:\/\/(\S+)$/iU';

$matrix['name']['type'] = 'text';
$matrix['name']['text'] = '{Call:Lang:core:core:imiasajta}';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazanoimi3}';

$matrix['sort']['type'] = 'text';
$matrix['sort']['text'] = '{Call:Lang:core:core:pozitsiiavsp}';

$matrix['access']['type'] = 'radio';
$matrix['access']['additional'] = array(
	'0' => '{Call:Lang:core:core:otkrytvsem}',
	'1' => '{Call:Lang:core:core:otkryttolkoa}',
	'2' => '{Call:Lang:core:core:otkryttolkos}'
);

$matrix['default']['type'] = 'checkbox';
$matrix['default']['text'] = '{Call:Lang:core:core:sdelatsajtos}';

?>