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


class gen_ticket extends ModuleInterface{

	private $admins = array();
	private $supports = array();
	private $supportAdmins = array();

	private $departments = array();
	private $departmentsByName = array();

	private $workedDepartments = array();
	private $workedDepartmentsByName = array();
	private $departmentParams = array();

	//Статусы сообщений
	private $msgStatuses = false;
	private $msgStatuses4user = array();
	private $msgStatuses4support = array();
	private $msgStatusesAuto = array();

	public function __ava____map($obj){
		/*
			Карта сайта
				- Регистрация в партнерке
		*/

		return array(array('name' => 'Задать вопрос', 'link' => 'index.php?mod='.$this->mod.'&func=ticket'));
	}


	/***************************************************************************************************************************************************************

																				Саппорты

	****************************************************************************************************************************************************************/

	public function supportAdmins(){
		if(empty($this->admins)){
			$this->admins = $this->Core->DB->columnFetch(array('admins', 'login', 'id', "", "`login`"));
			foreach($this->DB->columnFetch(array('supports', 'admin_id')) as $e){
				unset($this->admins[$e]);
			}
		}

		return $this->admins;
	}

	private function fetchSupports(){
		if(!$this->supports){
			$this->supports = $this->DB->columnFetch(array('supports', '*', 'id'));
			foreach($this->supports as $i => $e){
				$this->supports[$i]['departments'] = Library::str2arrKeys($this->supports[$i]['departments']);
				$this->supportAdmins[$e['admin_id']] = $i;
			}
		}
	}

	public function __ava__getSupportIdByAdminId($id){
		$this->fetchSupports();
		return $this->supportAdmins[$id];
	}

	public function __ava__getSupportParams($supportId){
		$this->fetchSupports();
		return $this->supports[$supportId];
	}

	public function __ava__getSupportName($supportId){
		/*
			Имя сотрудника поддержки
		*/

		$this->fetchSupports();
		return $this->supports[$supportId]['name'];
	}

	private function fetchDepartments(){
		if(!$this->departments){
			$this->departmentParams = $this->DB->columnFetch(array('departments', '*', 'id', "", "`sort`"));

			foreach($this->departmentParams as $i => $e){
				$this->departments[$i] = $e['text'];
				$this->departmentsByName[$e['name']] = $e['text'];

				if($e['show']){
					$this->workedDepartments[$i] = $e['text'];
					$this->workedDepartmentsByName[$e['name']] = $e['text'];
				}

				$this->departmentParams[$i]['access'] = Library::unserialize($this->departmentParams[$i]['access']);
			}
		}
	}

	public function getDepartments(){
		$this->fetchDepartments();
		return $this->departments;
	}

	public function getDepartmentsByName(){
		$this->fetchDepartments();
		return $this->departmentsByName;
	}

	public function getWorkedDepartments(){
		$this->fetchDepartments();
		return $this->workedDepartments;
	}

	public function getWorkedDepartmentsByName(){
		$this->fetchDepartments();
		return $this->workedDepartmentsByName;
	}


	/********************************************************************************************************************************************************************

																			Управление сообщениями

	*********************************************************************************************************************************************************************/

	public function __ava__setTicket($userId, $department, $name, $values = false){
		/*
			Добавляет тикет в систему
		*/

		$code = Library::inventPass(8);

		$id = $this->DB->Ins(
			array(
				'tickets',
				array(
					'user_id' => $userId,
					'department' => $department,
					'date' => time(),
					'name' => $name,
					'code' => $code,
					'vars' => $this->getGeneratedFormValues(array('message_form', '*', "`show`"), $values)
				)
			)
		);

		return array($id, $code);
	}

	public function __ava__setTicketMessage($ticketId, $author, $authorType, $text, $attaches, $status, $statusMom, $eml){
		/*
			Добавляет тикет в систему
		*/

		if($return = $this->DB->Ins(array('messages', array('ticket_id' => $ticketId, 'date' => time(), 'author' => $author, 'author_type' => $authorType, 'text' => $text, 'attaches' => $attaches)))){
			$this->DB->Upd(array('tickets', array('eml' => $eml), "`id`='$ticketId'"));
			if(!$status) $status = $this->getAutoStatus($statusMom);
			$this->setTicketStatus($ticketId, $status, $authorType, 2);
			$this->sendTicketMail($return);
		}
		return $return;
	}

	public function __ava__sendTicketMail($msgId){
		/*
			Отправляет сообщение о создании тикета или сообщения
		*/

		$data['msg'] = $this->DB->rowFetch(array('messages', '*', "`id`='{$msgId}'"));
		$data = Library::array_merge($data, $this->getTicketData($data['msg']['ticket_id']));

		if($data['msg']['author_type'] == 'support'){
			$tmpl = 'newMessage';
			$data['link'] = _D.'index.php?mod='.$this->mod.'&func=ticketView&id='.$data['id'].'&code='.$data['code'];
			return $this->mail($data['eml'], $this->getTmplParams($tmpl), $data);
		}
		else{
			$tmpl = 'newMessage4admin';
			$data['link'] = _D.ADMIN_FOLDER.'/index.php?mod='.$this->mod.'&func=answerTicket&id='.$data['id'];
			$this->fetchSupports();

			foreach($this->supports as $i => $e){
				if(!empty($e['departments']['@all']) || !empty($e['departments'][$data['department']])){
					$return[$i] = $this->mail($this->Core->getAdminEmlById($e['admin_id']), $this->getTmplParams($tmpl), $data);
				}
			}

			return $return;
		}
	}

	public function __ava__setTicketStatus($ticketId, $status, $by, $priv, $force = false){
		$data = $this->getMsgStatusData($status);
		if($data['superpriv']) $priv = 4;
		$this->DB->Upd(array('tickets', array('status' => $status, 'status_by' => $by, 'status_priv' => $priv), "`id`='$ticketId'".($force ? "" : " AND `status_priv`<4 AND (`status_by`!='$by' OR `status_priv`<=$priv)")));
	}

	public function getAttaches(){
		$return = array();
		for($i = 1; $i <= 6; $i ++){
			if(!empty($this->values['attach'.$i])) $return[] = $this->values['attach'.$i];
		}

		return $return;
	}

	public function __ava__viewMessages($ticketId, $type = 'user'){
		$ticketData = $this->getTicketData($ticketId);
		$form = '';

		if($type == 'user'){
			$statusData = $this->getMsgStatusData($ticketData['status']);
			if(!empty($statusData['rights']['show'])) throw new AVA_Access_Exception('Вы не можете просмотреть этот тикет');
			if(!empty($statusData['rights']['answer'])) $noForm = true;
		}

		if(empty($noForm)){
			$form = $this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'answerTicket2',
						'answerTicket2',
						array('caption' => '{Call:Lang:modules:ticket:otvetit}')
					),
					'new_message',
					array('status' => $this->getMsgStatuses($type))
				),
				array('eml' => $ticketData['eml']),
				array('id' => $ticketId),
				$type == 'user' ? '' : 'big'
			);
		}

		$tmpl = $this->Core->getModuleTemplatePath($this->getMod()).'messages.tmpl';
		return $this->Core->readBlockAndReplace(
			$tmpl,
			'messages',
			$this,
			array(
				'ticketData' => $ticketData,
				'tickets' => $this->DB->columnFetch(array('messages', '*', 'id', "`ticket_id`='{$ticketId}'", "`date`")),
				'form' => $form,
				'type' => $type,
				'extra' => $this->getDescription(array('message_form', '*', "`show`"), $tmpl, $ticketData['vars'])
			),
			'cover'
		);
	}

	public function __ava__getTicketData($ticketId){
		$return = $this->DB->rowFetch(array('tickets', '*', "`id`='{$ticketId}'"));
		$return['vars'] = library::unserialize($return['vars']);
		return $return;
	}

	public function getUsersByDepartment($groups){
		/*
			Возвращает всех пользователей имеющих доступ в департамент
		*/

		return array();
	}


	/********************************************************************************************************************************************************************

																			Статусы

	*********************************************************************************************************************************************************************/

	private function fetchStatuses(){
		if($this->msgStatuses === false){
			$this->msgStatuses = $this->DB->columnFetch(array('message_status', '*', 'name', '', "`sort`"));

			foreach($this->msgStatuses as $i => $e){
				$this->msgStatuses[$i]['rights'] = Library::str2arrKeys($e['rights']);
				if($e['use_user']) $this->msgStatuses4user[$i] = $e['text'];
				if($e['use_support']) $this->msgStatuses4support[$i] = $e['text'];

				if(!empty($e['auto_set_open'])) $this->msgStatusesAuto['open'] = $e['name'];
				if(!empty($e['auto_set_show_user'])) $this->msgStatusesAuto['show_user'] = $e['name'];
				if(!empty($e['auto_set_show_support'])) $this->msgStatusesAuto['show_support'] = $e['name'];
				if(!empty($e['auto_set_answer_user'])) $this->msgStatusesAuto['answer_user'] = $e['name'];
				if(!empty($e['auto_set_answer_support'])) $this->msgStatusesAuto['answer_support'] = $e['name'];
			}
		}
	}

	public function __ava__getMsgStatusData($name){
		$this->fetchStatuses();
		return $this->msgStatuses[$name];
	}

	public function __ava__getMsgStatuses($type){
		$this->fetchStatuses();
		return $this->{'msgStatuses4'.$type};
	}

	public function __ava__getAutoStatus($type){
		$this->fetchStatuses();
		return isset($this->msgStatusesAuto[$type]) ? $this->msgStatusesAuto[$type] : '';
	}

	public function __ava__getMsgStatusName($status){
		$this->fetchStatuses();
		return $this->msgStatuses[$status]['text'];
	}
}

?>