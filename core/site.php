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



class Site extends objectInterface{
	public $params;
	public $url;
	public $urlParse;
	private $siteId;
	public function __ava__loadParams($params){
		$this->params = $params;
		$this->siteId = $params['id'];
		$this->url = $params['url'];
		$this->urlParse = parse_url($params['url']);
	}

	public function isOpen(){
		if(!$this->params['access']) return true;
		elseif(($this->params['access'] == 1) && ($GLOBALS['Core']->userIsAdmin())) return true;
		elseif(($this->params['access'] == 2) && ($GLOBALS['Core']->userIsRoot())) return true;
		return false;
	}

	public function getSiteId(){		return $this->siteId;	}
}

?>