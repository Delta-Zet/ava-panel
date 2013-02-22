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


class mod_admin_ticket extends gen_ticket{


	/********************************************************************************************************************************************************************

																			Настройка разделов

	*********************************************************************************************************************************************************************/

	protected function func_departments(){
		/*
			Управление департаментами
		*/

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:modules:ticket:dobavitrazde}',
				'isUniq' => array('name' => 'Такое имя уже используется', 'name' => 'Такой идентификатор уже используется'),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array('text' => '{Call:Lang:modules:ticket:nazvanierazd}', 'name' => '{Call:Lang:modules:ticket:identifikato}', 'access_type' => '{Call:Lang:modules:ticket:tipdostupa}', 'show' => ''),
						'orderFields' => array('text' => '{Call:Lang:modules:ticket:nazvaniiu}', 'name' => '{Call:Lang:modules:ticket:identifikato1}'),
						'searchMatrix' => array(
							'access_type' => array(
								'type' => 'select',
								'additional' => array(
									'' => '{Call:Lang:modules:ticket:liuboj}',
									'0' => '{Call:Lang:modules:ticket:vse}',
									'1' => '{Call:Lang:modules:ticket:zaregistriro}',
									'2' => '{Call:Lang:modules:ticket:spetsialnyen}'
								)
							)
						),
						'isBe' => array('access_type' => 1)
					),
					'actions' => array(
						'specialAccess' => 'specialAccess',
						'text' => 'departments&type_action=modify'
					)
				)
			)
		);
	}

	protected function func_specialAccess(){
		/*
			Специальные настройки доступа
		*/

		$dData = $this->DB->rowFetch(array('departments', array('name', 'text', 'access', 'access_type'), "`id`='".db_main::quot($this->values['id'])."'"));
		$dData['access'] = Library::unserialize($dData['access']);
		if($dData['access']) $dData['access']['access_type'] = $dData['access_type'];

		$this->pathFunc = 'departments';
		$this->funcName = '{Call:Lang:modules:ticket:spetsialnyen2:'.Library::serialize(array($dData['text'])).'}';

		$fObj = $this->newForm('specialAccess2', 'specialAccess2', array('caption' => $this->funcName));
		$this->addFormBlock($fObj, 'ticket_access', array('access_type' => $dData['access_type']));
		$this->Core->callAllModules('ticketAccessForm', $this, array('fObj' => $fObj));

		$lastMatrix = Library::lastKey($fObj->matrix);
		$this->addFormBlock($fObj, 'ticket_access', array('lastMatrix' => $lastMatrix, 'lastMatrixText' => isset($fObj->matrix[$lastMatrix]['post_text']) ? $fObj->matrix[$lastMatrix]['post_text'] : ''));
		$this->setContent($this->getFormText($fObj, $dData['access'], array('id' => $this->values['id']), 'big'));

		return $dData['access'];
	}

	protected function func_specialAccess2(){
		if(!$this->check()) return false;
		$vars = $this->values;
		unset($vars['ava_form_transaction_id'], $vars['mod'], $vars['func'], $vars['id'], $vars['access_type']);
		$this->DB->Upd(array('departments', array('access' => $vars, 'access_type' => $this->values['access_type']), "`id`='".db_main::quot($this->values['id'])."'"));
		$this->refresh('departments');
	}


	/********************************************************************************************************************************************************************

																			Настройка саппортов

	*********************************************************************************************************************************************************************/

	protected function func_supports(){
		/*
			Управление департаментами
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'supports',
						'supportsNew',
						array(
							'caption' => '{Call:Lang:modules:ticket:dobavitsotru}',
						)
					),
					'supports',
					array(
						'admins' => $this->supportAdmins(),
						'departments' => Library::array_merge(array('@all' => '{Call:Lang:modules:ticket:vse}'), $this->getWorkedDepartmentsByName())
					)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'supports_list',
					array(
						'req' => array('supports', '*', '', '`sort`'),
						'extraReqs' => array(
							array(
								'req' => array('admins', '*'),
								'DB' => $this->Core->DB,
								'unitedFld1' => 'admin_id',
								'unitedFld2' => 'id',
								'prefix' => 'admin_'
							)
						),
						'form_actions' => array(
							'1' => '{Call:Lang:modules:ticket:vystavitstat}',
							'2' => '{Call:Lang:modules:ticket:vystavitstat1}',
							'0' => '{Call:Lang:modules:ticket:vystavitstat2}',
							'delete' => '{Call:Lang:modules:ticket:udalit}'
						),
						'actions' => array(
							'name' => 'supportModify'
						),
						'action' => 'supportActions',
						'searchForm' => array(
							'searchFields' => array(
								'admin_id' => '{Call:Lang:modules:ticket:administrato}',
								'name' => '{Call:Lang:modules:ticket:imiasotrudni}',
								'status' => '{Call:Lang:modules:ticket:status}',
								'departments' => '{Call:Lang:modules:ticket:razdel}',
								'auto_status_change' => '{Call:Lang:modules:ticket:mozhetsamseb}'
							),
							'orderFields' => array('name' => '{Call:Lang:modules:ticket:imeni}'),
							'searchMatrix' => array(
								'admin_id' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:ticket:vse}'), $this->Core->DB->columnFetch(array('admins', 'login', 'id', "", "`login`")))
								),
								'departments' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:ticket:vse}'), Library::concatPrefixArrayKey($this->getDepartments(), ',', ','))
								),
								'status' => array(
									'type' => 'select',
									'additional' => array('' => '{Call:Lang:modules:ticket:vse}', '1' => '{Call:Lang:modules:ticket:rabotaet}', '0' => '{Call:Lang:modules:ticket:nerabotaet}', '2' => '{Call:Lang:modules:ticket:votpuske}')
								),
								'auto_status_change' => array('type' => 'checkbox')
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:ticket:spisoksotrud}'
					)
				)
			)
		);
	}

	protected function func_supportsNew(){
		/*
			Новый саппорт
		*/

		$fields = $this->fieldValues(array('name', 'admin_id', 'status', 'auto_status_change', 'sort'));
		$fields['date'] = time();
		$fields['departments'] = Library::arrKeys2str($this->values['departments']);
		return $this->typeIns('supports', $fields, 'supports');
	}

	protected function func_supportModify(){
		/*
			Модифицировать саппорта
		*/

		$id = db_main::Quot($this->values['id']);
		$values = $this->DB->rowFetch(array('supports', '*', "`id`='".$id."'"));
		$values['departments'] = Library::str2arrKeys($values['departments']);

		$this->typeModify(
			false,
			'supports',
			'supportsNew',
			array(
				'formData' => array(
					'admins' => Library::array_merge(array($values['admin_id'] => $this->Core->DB->cellFetch(array('admins', 'login', "`id`='{$id}'"))), $this->supportAdmins()),
					'departments' => Library::array_merge(array('@all' => '{Call:Lang:modules:ticket:vse}'), $this->getWorkedDepartmentsByName())
				),
				'params' => array('caption' => '{Call:Lang:modules:ticket:sotrudnik:'.Library::serialize(array($values['name'])).'}'),
				'values' => $values
			)
		);
	}

	protected function func_supportActions(){
		if($this->values['action'] == 'delete') return $this->typeActions('supports', 'supports');

		if(!$this->values['entry']){
			$this->back('supports', '{Call:Lang:modules:ticket:nevybranonio}');
			return false;
		}

		$this->refresh('supports');
		return $this->DB->Upd(array('supports', array('status' => $this->values['action']), $this->getEntriesWhere(false, 'id')));
	}


	/********************************************************************************************************************************************************************

																				Статусы тикетов

	*********************************************************************************************************************************************************************/

	protected function func_message_status(){
		/*
			Статусы сообщений.
		*/

		$return = $this->typicalMain(
			array(
				'caption' => '{Call:Lang:modules:ticket:dobavitstatu}',
				'isUniq' => array('name' => 'Такое имя уже используется', 'name' => 'Такой идентификатор уже используется'),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array('text' => '{Call:Lang:modules:ticket:imia}', 'name' => '{Call:Lang:modules:ticket:identifikato}', 'show' => ''),
						'orderFields' => array('text' => '{Call:Lang:modules:ticket:nazvaniiu}', 'name' => '{Call:Lang:modules:ticket:identifikato1}'),
					),
					'actions' => array(
						'text' => 'message_status&type_action=modify'
					),
					'form_actions' => array(
						'delete' => 'Удалить'
					)
				),
				'modifyData' => array(
					'extract' => array('rights')
				)
			)
		);

		if($return && !empty($this->values['type_action']) && $this->values['type_action'] == 'new'){
			$id = !empty($this->values['modify']) ? $this->values['modify'] : $return;
			if(!empty($this->values['auto_set_open'])) $this->DB->Upd(array('message_status', array('auto_set_open' => ''), "`id`!='$id'"));
			if(!empty($this->values['auto_set_show_user'])) $this->DB->Upd(array('message_status', array('auto_set_show_user' => ''), "`id`!='$id'"));

			if(!empty($this->values['auto_set_show_support'])) $this->DB->Upd(array('message_status', array('auto_set_show_support' => ''), "`id`!='$id'"));
			if(!empty($this->values['auto_set_answer_user'])) $this->DB->Upd(array('message_status', array('auto_set_answer_user' => ''), "`id`!='$id'"));
			if(!empty($this->values['auto_set_answer_support'])) $this->DB->Upd(array('message_status', array('auto_set_answer_support' => ''), "`id`!='$id'"));
		}

		return $return;
	}


	/********************************************************************************************************************************************************************

																					Тикеты

	*********************************************************************************************************************************************************************/

	protected function func_tickets(){
		/*
			Все тикеты
		*/

		$p = $this->DB->getPrefix();
		$t1 = $p.'tickets';
		$t2 = $p.'supports';
		$t3 = $p.'message_status';

		foreach($this->getMsgStatuses('support') as $i => $e){
			$actions['status_'.$i] = 'Выставить статус "'.$e.'"';
		}
		$actions['delete'] = '{Call:Lang:modules:ticket:udalit}';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'tickets_list',
					array(
						'req' => "SELECT t1.*, t2.name AS support_name, t3.text AS status_name
							FROM $t1 AS t1
							LEFT JOIN $t2 AS t2 ON t1.support_id=t2.id
							LEFT JOIN $t3 AS t3 ON t1.status=t3.name
							ORDER BY t1.date DESC",
						'extraReqs' => array(
							array(
								'req' => array('users', '*'),
								'DB' => $this->Core->DB,
								'unitedFld1' => 'user_id',
								'unitedFld2' => 'id',
								'prefix' => 'user_'
							)
						),
						'form_actions' => $actions,
						'actions' => array(
							'name' => 'answerTicket'
						),
						'action' => 'ticketActions'
					),
					array(
						'caption' => '{Call:Lang:modules:ticket:spisokvopros}'
					)
				)
			)
		);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'newTicket',
						'newTicket',
						array('caption' => '{Call:Lang:modules:ticket:dobavittiket}')
					),
					'new_ticket',
					array(
						'departments' => $this->getDepartments(),
						'status' => $this->getMsgStatuses('support'),
						'groups' => Library::array_merge(array('@all' => '{Call:Lang:modules:ticket:vsepolzovate}', '' => '{Call:Lang:modules:ticket:bezgruppy}'), $this->Core->getUserGroups())
					)
				),
				array(),
				array(),
				'big'
			)
		);
	}

	protected function func_answerTicket(){
		/*
			Ответ на "вопрос" пользователя
		*/

		$this->setTicketStatus($this->values['id'], $this->getAutoStatus('show_support'), 'support', 1);
		$this->setContent($this->viewMessages($this->values['id'], 'support'));
	}

	protected function func_answerTicket2(){
		/*
			Записывает ответ на тикет
		*/

		if(!$this->check()) return false;
		$this->setTicketMessage($this->values['id'], $this->getSupportIdByAdminId($this->Core->userIsAdmin()), 'support', $this->values['text'], $this->getAttaches(), $this->values['status'], 'answer_support', $this->values['eml']);
		$this->refresh('answerTicket&id='.$this->values['id']);
	}

	protected function func_newTicket(){
		/*
			Добавление нового тикета
		*/

		if($this->values['rcpt_style'] == 'personal'){
			if(!$userId = $this->Core->DB->cellFetch(array('users', 'id', "`login`='{$this->values['rcpt_login']}'"))){
				$this->setError('rcpt_login', '{Call:Lang:modules:ticket:takogopolzov}');
			}
		}
		elseif($this->values['rcpt_style'] == 'group' && empty($this->values['rcpt_grp'])){
			$this->setError('rcpt_grp', '{Call:Lang:modules:ticket:neotmechenon}');
		}

		if(!$this->check()) return false;

		if($this->values['rcpt_style'] == 'personal'){
			if(list($ticketId, $code) = $this->setTicket($userId, $this->values['department'], $this->values['name'])){
				$this->setTicketMessage($ticketId, $this->getSupportIdByAdminId($this->Core->userIsAdmin()), 'support', $this->values['text'], $this->getAttaches(), $this->values['status'], 'open', $this->Core->getUserEml($userId));
			}
		}
		elseif($this->values['rcpt_style'] == 'group'){
			$aid = $this->getSupportIdByAdminId($this->Core->userIsAdmin());
			foreach($this->getUsersByDepartment($this->values['rcpt_grp']) as $i => $e){
				if(list($ticketId, $code) = $this->setTicket($i, $this->values['department'], $this->values['name'])){
					$this->setTicketMessage($ticketId, $aid, 'support', $this->values['text'], $this->getAttaches(), $this->values['status'], 'open', $this->Core->getUserEml($i));
				}
			}
		}

		$this->refresh('tickets');
	}

	protected function func_ticketActions(){
		/*
			actions для тикетов
		*/

		switch($this->values['action']){
			case 'delete':
				$this->DB->Del(array('messages', $this->getEntriesWhere(false, 'ticket_id')));
				$this->DB->Del(array('tickets', $this->getEntriesWhere()));
				break;

			default:
				if(regExp::Match("|^status_(.*)$|", $this->values['action'], true, true, $m)){
					foreach($this->values['entry'] as $i => $e){
						$this->setTicketStatus($i, $m['1'], 'support', 1, true);
					}
				}
				break;
		}

		$this->refresh('tickets');
	}


	/********************************************************************************************************************************************************************

																				Форма запроса

	*********************************************************************************************************************************************************************/

	protected function func_message_form(){
		/*
			Управление формой запроса
		*/

		return $this->formFields('message_form');
	}
}

?>