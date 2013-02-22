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



$GLOBALS['Core']->loadExtension('billing', 'servconnectObject');

class serverHostingObject extends servconnectObject{

	protected function __ava__insertSpecialParams($params, $unsetNoUse = false){		/*
			Устанавливает специальные параметры в settings
		*/

		$this->loadServerExtensionParams();
		$return = array();

		foreach($this->extensionParams['extra']['cpParams'] as $i => $e){			if(!empty($params[$i]) && $e['type'] == 'checkbox' && isset($e['ch'])) $return[$i] = $e['ch'];			elseif(empty($params[$i]) && $e['type'] == 'checkbox' && isset($e['noch'])) $return[$i] = $e['noch'];			elseif($e['type'] == 'caption' || (empty($params[$i]) && $e['type'] == 'checkbox')) continue;			elseif(empty($params[$i]) && $e['type'] == 'text' && empty($e['noFunc'])) $return[$i] = 0;
			elseif(isset($e['unlimitAlias']) && mod_billing::isUnlimit($params[$i])){				$return[$e['unlimitAlias']] = $e['unlimit'];
				$params[$e['unlimitAlias']] = $params[$i];			}
			elseif(isset($e['unlimit']) && mod_billing::isUnlimit($params[$i])) $return[$i] = $e['unlimit'];
			elseif(!empty($e['k'])) $return[$i] = $e['k'] * $params[$i];
			else $return[$i] = isset($params[$i]) ? $params[$i] : '';
		}

		if($unsetNoUse) foreach($return as $i => $e) if(!isset($params[$i])) unset($return[$i]);
		return $return;	}

	protected function __ava__idna($domain){		/*
			Кодирует домен в IDNA
		*/

		$GLOBALS['Core']->loadExtension('bill_domains', 'whois');
		return whois::getIDNDomain($domain);
	}
}

?>