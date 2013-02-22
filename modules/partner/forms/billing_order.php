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


$matrix['refered_by']['text'] = '{Call:Lang:modules:partner:porekomendat}';
$matrix['refered_by']['type'] = 'text';
$values['refered_by'] = $referer;

if(Library::constVal('IN_ADMIN')){
	$matrix['refered_by']['value'] = $referer;
}
else{
	if($this->Core->getParam('partnerViewReferals', $this->mod) < 3){
		if($this->Core->getParam('partnerViewReferals', $this->mod) < 2){
			$matrix['refered_by']['type'] = 'hidden';
		}
		else $matrix['refered_by']['disabled'] = 1;
	}
}

?>