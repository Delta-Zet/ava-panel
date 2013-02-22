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


class mod_ticket extends gen_ticket{

	protected function func_ticket(){
		/*
			Запросы в поддержку
		*/

		$this->setMeta('{Call:Lang:modules:ticket:zaprosvpodde}');
		list($matrix, $values) = $this->getMatrixArray(array('message_form', '*', "`show`"));
		$values['eml'] = $this->Core->getUserEml();

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'newTicket',
						'newTicket'
					),
					array('ticket', $matrix, 'new_message', $this->needCaptcha() ? 'type_captcha' : array()),
					array(
						'departments' => $this->getWorkedDepartmentsByName(),
						'status' => $this->getMsgStatuses('user')
					)
				),
				$values
			)
		);

		if($userId = $this->Core->User->getUserId()){
			$p = $this->DB->getPrefix();
			$t1 = $p.'tickets';
			$t2 = $p.'departments';
			$t3 = $p.'message_status';

			$this->setContent(
				$this->getListText(
					$this->newList(
						'user_tickets_list',
						array(
							'req' => "SELECT t1.id, t1.support_id, t1.department, t1.date, t1.name, t1.status, t1.code, t2.text AS `dep_name`, t3.text AS `status_name` ".
								"FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.department=t2.name LEFT JOIN $t3 AS t3 ON t1.status=t3.name ".
								"WHERE t1.user_id='$userId' AND (t3.rights NOT REGEXP(',show,') OR t3.rights IS NULL) ORDER BY `date` DESC",
							'actions' => array(
								'name' => 'ticketView'
							)
						),
						array(
							'caption' => 'Список тикетов'
						)
					),
					'usertable'
				)
			);
		}
	}

	private function needCaptcha(){
		/*
			Проверяет нужна ли CAPTCHA в сообщении
		*/

		if($this->Core->getParam('captchaStyle', $this->mod) == 'all' || ($this->Core->getParam('captchaStyle', $this->mod) == 'anonymous' && !$this->Core->getUserId())) return true;
		return false;
	}

	protected function func_ticketView(){
		/*
			Просмотр тикета
		*/

		$ticketData = $this->getTicketData($this->values['id']);
		if(($this->Core->User->getUserId() && $ticketData['user_id'] == $this->Core->User->getUserId()) || (!empty($this->values['code']) && $ticketData['code'] == $this->values['code'])){
			$this->setMeta($ticketData['name']);
			$this->setTicketStatus($this->values['id'], $this->getAutoStatus('show_user'), 'user', 1);
			$this->setContent($this->viewMessages($this->values['id']));
		}
		else throw new AVA_Access_Exception('{Call:Lang:modules:ticket:uvasnetprava}');
	}

	protected function func_newTicket(){
		/*
			Новый запрос в поддержку
		*/

		if(!$this->check()) return false;
		list($id, $code) = $this->setTicket($this->Core->User->getUserId(), $this->values['department'], $this->values['name']);
		$this->setTicketMessage($id, $this->Core->User->getUserId(), 'user', $this->values['text'], $this->getAttaches(), $this->values['status'], 'open', $this->values['eml']);
		$this->refresh('ticketView&id='.$id.'&code='.$code);
		return $id;
	}

	protected function func_answerTicket2(){
		if(!$this->check()) return false;
		$ticketData = $this->getTicketData($this->values['id']);
		$this->setTicketMessage($this->values['id'], $this->Core->User->getUserId(), 'user', $this->values['text'], $this->getAttaches(), $this->values['status'], 'answer_user', $this->values['eml']);
		$this->refresh('ticketView&id='.$this->values['id'].'&code='.$ticketData['code']);
	}
}

?>