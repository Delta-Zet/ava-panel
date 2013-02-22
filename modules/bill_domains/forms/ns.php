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


$matrix[$prefix.'ns1_'.$id]['text'] = '{Call:Lang:modules:bill_domains:pervyjnsserv}';
$matrix[$prefix.'ns1_'.$id]['comment'] = '{Call:Lang:modules:bill_domains:esliostavite}';
$matrix[$prefix.'ns1_'.$id]['type'] = 'text';
$matrix[$prefix.'ns1_'.$id]['warn_pattern'] = '^[\w\.]{4,100}\s*[\d\.]{0,16}';

$matrix[$prefix.'ns2_'.$id]['text'] = '{Call:Lang:modules:bill_domains:vtorojnsserv}';
$matrix[$prefix.'ns2_'.$id]['type'] = 'text';
$matrix[$prefix.'ns2_'.$id]['warn_pattern'] = '^[\w\.]{4,100}\s*[\d\.]{0,16}';

$matrix[$prefix.'ns3_'.$id]['text'] = '{Call:Lang:modules:bill_domains:tretijnsserv}';
$matrix[$prefix.'ns3_'.$id]['type'] = 'text';
$matrix[$prefix.'ns3_'.$id]['warn_pattern'] = '^[\w\.]{4,100}\s*[\d\.]{0,16}';

$matrix[$prefix.'ns4_'.$id]['text'] = '{Call:Lang:modules:bill_domains:chetvertyjns}';
$matrix[$prefix.'ns4_'.$id]['type'] = 'text';
$matrix[$prefix.'ns4_'.$id]['warn_pattern'] = '^[\w\.]{4,100}\s*[\d\.]{0,16}';

?>