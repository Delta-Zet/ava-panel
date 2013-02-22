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


foreach($services as $i => $e){
	if(isset($packages[$i])){
		$matrix['services_capt_'.$i]['text'] = $e;
		$matrix['services_capt_'.$i]['type'] = 'caption';
		$sParams = $this->serviceData($i);

		foreach($packages[$i] as $i1 => $e1){
			$matrix['services_capt_'.$i.'_'.$i1]['text'] = $e1;
			$matrix['services_capt_'.$i.'_'.$i1]['type'] = 'caption';

			$matrix['count_'.$i.'_'.$i1]['text'] = 'Количество';
			$matrix['count_'.$i.'_'.$i1]['type'] = 'text';
			$matrix['count_'.$i.'_'.$i1]['warn_function'] = 'regExp::digit';

			if($sParams['type'] == 'prolonged'){
				$matrix['term_'.$i.'_'.$i1]['text'] = 'Срок, '.Dates::termsListVars($sParams['base_term'], 2);
				$matrix['term_'.$i.'_'.$i1]['type'] = 'text';
				$matrix['term_'.$i.'_'.$i1]['warn_function'] = 'regExp::digit';
			}
		}
	}
}

?>