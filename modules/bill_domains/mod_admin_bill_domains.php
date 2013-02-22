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


class mod_admin_bill_domains extends gen_bill_domains{

	protected function func_whois_servers(){
		/*
			Управление whois доменов
		*/

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:modules:bill_domains:dobavitwhois}',
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'zone' => '{Call:Lang:modules:bill_domains:domennaiazon}',
							'host' => '{Call:Lang:modules:bill_domains:server}',
							'port' => '{Call:Lang:modules:bill_domains:port}',
							'pattern' => '{Call:Lang:modules:bill_domains:pattern}',
							'inverse' => '{Call:Lang:modules:bill_domains:tolkoinverti}'
						),
						'orderFields' => array(
							'port' => '{Call:Lang:modules:bill_domains:portu}',
							'zone' => '{Call:Lang:modules:bill_domains:zone}',
							'host' => '{Call:Lang:modules:bill_domains:serveru}',
							'pattern' => '{Call:Lang:modules:bill_domains:patternu}'
						),
						'searchMatrix' => array(
							'inverse' => array('type' => 'checkbox')
						)
					),
					'actions' => array(
						'zone' => 'whois_servers&type_action=modify'
					)
				)
			)
		);
	}

	protected function func_blanks(){
		/*
			Анкеты регистрантов
		*/

		$modifyData = array();
		if(!empty($this->values['type_action'])){
			if($this->values['type_action'] == 'new'){
				if(!$client = $this->getBillingMod()->getClientByIdOrLogin($this->values['login'])) $this->setError('login', 'Пользователь не найден или не имеет статуса клиенты');
				if(!$this->check()) return false;
				if($this->setNewBlank($this, $client)) $this->refresh('blanks');
				return;
			}
			elseif($this->values['type_action'] == 'modify'){
				$values = $this->DB->rowFetch(array('domain_owners', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
				$modifyData['values'] = Library::unserialize($values['vars']);
				$modifyData['values']['login'] = $this->getBillingMod()->getUserLoginByClientId($values['client_id']);
				$modifyData['values']['correspond'] = Library::str2arrKeys($values['correspond']);
				$modifyData['values']['blankName'] = $values['name'];
			}
		}

		return $this->typicalMain(
			array(
				'name' => 'domain_owners',
				'form' => array('domain_client', 'domain_owners'),
				'caption' => '{Call:Lang:modules:bill_domains:dobavitanket}',
				'listParams' => array(
					'req' => array('domain_owners', '*', "", "`id` DESC"),
					'actions' => array(
						'name' => 'blanks&type_action=modify',
						'reg' => 'registratorBlanks',
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:bill_domains:spisokanket}'
				),
				'modifyReq' => false,
				'modifyData' => $modifyData,
				'formTemplName' => 'big',
				'listTemplName' => 'big',
				'listName' => 'domain_owners_admin_list'
			)
		);
	}

	protected function func_registratorBlanks(){
		/*
			Анкеты доменов у регистраторов
		*/

		$fields = $modifyData = array();
		if(!empty($this->values['type_action'])){
			if($this->values['type_action'] == 'new'){
				$mId = !empty($this->values['modify']) ? $this->values['modify'] : 0;
				if($this->DB->cellFetch(array('contacts', 'id', "`domain_owners_id`='{$this->values['domain_owners_id']}' AND `connection`='{$this->values['connection']}' && `id`!='{$mId}'"))){
					$this->setError('connection', 'Для этого соединения уже есть анкета');
				}
				$fields = $this->fieldValues(array('name', 'pwd', 'connection', 'domain_owners_id'));
			}
			elseif($this->values['type_action'] == 'modify'){
				$modifyData['values'] = $this->DB->rowFetch(array('contacts', '*', "`id`='{$this->values['id']}'"));
				$modifyData['hiddens'] = array('modify' => $this->values['id'], 'domain_owners_id' => $this->values['domain_owners_id']);
			}
		}

		return $this->typicalMain(
			array(
				'name' => 'contacts',
				'caption' => 'Анкета у регистратора',
				'listParams' => array(
					'req' => array('contacts', '*', "`domain_owners_id`='{$this->values['id']}'"),
					'actions' => array(
						'name' => 'registratorBlanks&type_action=modify&domain_owners_id='.$this->values['id'],
					)
				),
				'formData' => array(
					'connections' => $this->getBillingMod()->getConnections($this->mod)
				),
				'fields' => $fields,
				'formHiddens' => array('domain_owners_id' => $this->values['id']),
				'modifyData' => $modifyData,
				'modifyReq' => false,
				'func' => 'registratorBlanks&id='.(isset($this->values['domain_owners_id']) ? $this->values['domain_owners_id'] : $this->values['id'])
			)
		);
	}
}

?>