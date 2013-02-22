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


$GLOBALS['Core']->loadExtension('billing', 'installServiceObject');

class installModulesBill_domains extends installServiceObject implements InstallModuleInterface{

	public function Install(){
		/*
			Инсталляция пакета
		 */

		$this->createAllTables();
		$this->setAllDefaults($this->obj->values);
		$this->installExtension();

		$this->installService($this->obj->values['text_'.$this->ext], $this->prefix, 'year', 'day', 'oneinmonth','paidto', 'v');
		$this->setConnectExtensions($this->getConnectExtensions($this->obj->values));
		$this->setWhois($this->getWhois());

		return true;
	}

	public function prepareInstall(){
		return true;
	}

	public function checkInstall(){
		return true;
	}

	public function Uninstall(){
		$this->dropAllTables();
		$this->dropAllDefaults();
		$this->dropExtension();
		$this->dropService();

		return true;
	}

	public function checkUninstall(){
		return true;
	}

	public function Update($oldVersion, $newVersion){
		$v = $this->obj->values;
		$v['sites'] = $this->iObj->Core->getModuleSites($this->prefix);

		$this->updateAllTables();
		$this->updateAllDefaults($v);
		$this->setConnectExtensions($this->getConnectExtensions($v));
		$this->setWhois($this->getWhois());

		return true;
	}

	public function checkUpdate($oldVersion, $newVersion){
		return true;
	}

	public function getTables(){
		/*
			Создает таблицы
		*/

		$return['domain_owners'] = array(
			array(
				'id' => '',
				'client_id' => 'INT',					//ID-клиента - владельца анкеты
				'name' => '',							//Имя анкеты
				'vars' => '',							//Данные анкеты
				'correspond' => '',						//Указание что анкета соответствует определенным доменным зонам (пока - RU, SU, РФ, US, ASIA (CED))
				'sort' => ''
			),
			array(
				'uni' => array(
					array('client_id', 'name')
				)
			)
		);

		$return['contacts'] = array(
			array(
				'id' => '',
				'domain_owners_id' => 'INT',
				'name' => 'VARCHAR(64)',
				'pwd' => '',
				'connection' => 'VARCHAR(32)'
			),
			array(
				'uni' => array(
					array('domain_owners_id', 'connection')
				)
			)
		);

		$return['clients'] = array(
			array(
				'id' => '',
				'domain_owners_id' => 'INT',
				'name' => 'VARCHAR(64)',
				'pwd' => '',
				'connection' => 'VARCHAR(32)'
			),
			array(
				'uni' => array(
					array('domain_owners_id', 'connection')
				)
			)
		);

		$return['whois_servers'] = array(
			array(
				'id' => '',
				'zone' => 'VARCHAR(32)',				//Зона
				'host' => '',
				'port' => '',							//Порт
				'pattern' => '',						//Паттерн проверки
				'inverse' => 'CHAR(1)',					//Инвертировать
				'sort' => ''
			),
			array(
				'uni' => array(
					array('zone')
				)
			)
		);

		return $return;
	}

	public function getDefaultAdminMenu($params){
		/*
			Дефолтные настройки уровня ядра
		*/

		$return[$this->billObj->getMod()] = array(
			'parent_id' => $this->obj->Core->DB->cellFetch(array('admin_menu', 'id', "`pkg`='".$this->billObj->getMod()."' AND !`parent_id`")),
			'text' => $this->obj->values['text_bill_domains'],
			'pkg' => $this->prefix,
			'submenu' => array(
				array(
					'text' => 'Whois',
					'pkg' => $this->prefix,
					'url' => '?mod='.$this->prefix.'&func=whois_servers',
				),
				array(
					'text' => 'Анкеты',
					'pkg' => $this->prefix,
					'url' => '?mod='.$this->prefix.'&func=blanks',
				),
			)
		);

		return $return;
	}

	public function getDefaultModuleLinks($params){
		$return = array(
			array(
				'name' => 'domainClientBlanks',
				'text' => '{Call:Lang:modules:bill_domains:anketyadmini}',
				'url' => 'index.php?mod='.$this->prefix.'&func=domainClientBlanks',
				'mod' => $this->prefix,
				'eval' => 'return empty($GLOBALS["Core"]->User->extraParams["'.$this->billObj->getMod().'"]["clientId"]) ? false : true;',
				'usedCmsLevel' => array('usermenu')
			),
		);

		return $return;
	}

	public function getDefaultSettings($params){
		$return = array();

		foreach($params['sites'] as $i => $e){
			$return[$i][$this->prefix]['']['ns1'] = array(
				'text' => 'NS1 сервер по умолчанию',
				'vars' => array(
					'matrix' => array(
						'comment' => 'Если необходимо указать IP, укажите через пробел'
					)
				),
			);

			$return[$i][$this->prefix]['']['ns2'] = array(
				'text' => 'NS2 сервер по умолчанию',
			);

			$return[$i][$this->prefix]['']['ns3'] = array(
				'text' => 'NS3 сервер по умолчанию',
			);

			$return[$i][$this->prefix]['']['ns4'] = array(
				'text' => 'NS4 сервер по умолчанию',
			);

			$return[$i][$this->prefix]['']['docScanFolder'] = array(
				'value' => 'storage/attaches/',
				'text' => 'Папка хранения сканов документов',
			);
		}

		return $return;
	}

	public function getWhois(){
		return array(
			'ru' => array('host' => 'whois.ripn.net', 'pattern' => 'No entries found for the selected source'),
			'su' => array('host' => 'whois.ripn.net', 'pattern' => 'No entries found for the selected source'),
			'рф' => array('host' => 'whois.ripn.net', 'pattern' => 'No entries found for the selected source'),
			'com' => array('host' => 'whois.internic.net', 'pattern' => 'No match for'),
			'net' => array('host' => 'whois.internic.net', 'pattern' => 'No match for'),
			'org' => array('host' => 'whois.corenic.net', 'pattern' => 'NOT FOUND'),
			'biz' => array('host' => 'whois.nic.biz', 'pattern' => 'Not found:'),
			'com.ru' => array('host' => 'whois.ripn.net', 'pattern' => 'No entries found for the selected source'),
			'net.ru' => array('host' => 'whois.ripn.net', 'pattern' => 'No entries found for the selected source'),
			'org.ru' => array('host' => 'whois.ripn.net', 'pattern' => 'No entries found for the selected source'),
			'pp.ru' => array('host' => 'whois.ripn.net', 'pattern' => 'No entries found for the selected source'),
			'info' => array('host' => 'whois.afilias.net', 'pattern' => 'NOT FOUND'),
			'aero' => array('host' => 'whois.aero', 'pattern' => 'NOT FOUND'),
			'be' => array('host' => 'whois.dns.be', 'pattern' => 'Status:      FREE'),
			'biz.ua' => array('host' => 'whois.biz.ua', 'pattern' => 'No entries'),
			'cc' => array('host' => 'whois.nic.cc', 'pattern' => 'No match'),
			'cn' => array('host' => 'whois.cnnic.net.cn', 'pattern' => 'no matching record'),
			'com.ua' => array('host' => 'whois.com.ua', 'pattern' => '% No entries'),
			'edu.ua' => array('host' => 'whois.com.ua', 'pattern' => '% No entries'),
			'gov.ua' => array('host' => 'whois.com.ua', 'pattern' => '% No entries'),
			'kiev.ua' => array('host' => 'whois.com.ua', 'pattern' => '% No entries'),
			'net.ua' => array('host' => 'whois.com.ua', 'pattern' => '% No entries'),
			'org.ua' => array('host' => 'whois.com.ua', 'pattern' => '% No entries'),
			'eu' => array('host' => 'whois.eu', 'pattern' => '#Status:\s+FREE|AVAILABLE#Us'),
			'in' => array('host' => 'whois.inregistry.net', 'pattern' => 'NOT FOUND'),
			'in.ua' => array('host' => 'whois.in.ua', 'pattern' => '% No records'),
			'it' => array('host' => 'whois.nic.it', 'pattern' => 'AVAILABLE'),
			'mobi' => array('host' => 'whois.dotmobiregistry.net', 'pattern' => 'NOT FOUND'),
			'msk.ru' => array('host' => 'whois.relcom.ru', 'pattern' => 'No entries found'),
			'spb.ru' => array('host' => 'whois.relcom.ru', 'pattern' => 'No entries found'),
			'name' => array('host' => 'whois.nic.name', 'pattern' => 'No match'),
			'tv' => array('host' => 'whois.www.tv', 'pattern' => 'not available'),
			'ua' => array('host' => 'whois.net.ua', 'pattern' => '% No entries'),
			'us' => array('host' => 'whois.nic.us', 'pattern' => 'Not found'),
			'ws' => array('host' => 'whois.website.ws', 'pattern' => 'No match'),
			'ком' => array('host' => 'whois.i-dns.net', 'pattern' => 'NOMATCH'),
			'нет' => array('host' => 'whois.i-dns.net', 'pattern' => 'NOMATCH'),
			'орг' => array('host' => 'whois.i-dns.net', 'pattern' => 'NOMATCH'),
			'ру' => array('host' => 'ru.whois.i-dns.net', 'port' => 4300, 'pattern' => 'NOMATCH'),
			'asia' => array('host' => 'whois.nic.asia', 'pattern' => 'NOT FOUND'),
			'me' => array('host' => 'whois.nic.me', 'pattern' => 'NOT FOUND'),
			'tw' => array('host' => 'whois.twnic.net.tw', 'pattern' => 'No Found'),
			'uz' => array('host' => 'whois.cctld.uz', 'pattern' => 'not found'),
			'kz' => array('host' => 'whois.nic.kz', 'pattern' => 'Nothing found')
		);
	}

	public function setWhois($params){
		$params = $this->paramReplaces($params);
		foreach($params as $i => $e){
			$e['zone'] = $i;
			$this->iObj->DB->Ins(array('whois_servers', $e, "`zone`='$i'"));
		}
	}
}

?>