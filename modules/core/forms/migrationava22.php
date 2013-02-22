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


if($this->values['newBilling']){
	@include($this->values['path'].'inc/settings.php');
	$sList = library::array_merge(array('' => '{Call:Lang:core:core:neperenosit}', '@new' => '{Call:Lang:core:core:sozdatnovuiu}'), $GLOBALS['Core']->callModule($this->values['newBilling'])->getServices());

	foreach($services as $i => $e){
		$matrix['service_'.$i]['text'] = '{Call:Lang:core:core:perenestiusl:'.Library::serialize(array(regExp::win($e))).'}';
		$matrix['service_'.$i]['type'] = 'select';
		$matrix['service_'.$i]['additional'] = $sList;
	}
}

?>