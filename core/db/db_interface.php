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


interface db_interface{

	public function __construct($host, $user, $pwd, $params, $nl = false, $pConnect = false);

	//Соединение и работа с БД
	public function connect();					//Устанавливает соединение
	public function setDB($db, $prefix);		//Устанавливает БД

	//Возвращает параметры
	public function GetHost();					//Возвращает хост
	public function GetUser();					//Возвращает пользователя
	public function GetParams();				//Возвращает параметры соединения

	public function GetDBName();				//Возвращает имя текущей БД
	public function getPrefix();				//Возвращает префикс БД
	public function getTblPrefix();				//Возвращает часть префикса БД относящуюся к таблице

	public function GetConnection();			//Возвращает ресурс соединения
	public function GetStatus();				//Статус соединения
	public function Ping();						//Пинг

	//Работа с запросами
	public function Req($req);					//Отправляет произвольный запрос

	//Запросы типа Select
	public function rowFetch($req);				//Извлекает строку запроса
	public function cellFetch($req);			//Извлекает ячейку
	public function columnFetch($req);			//Извлекает стлбец
	public function Count($req);				//Подсчет числа строк
	public function Min($req);					//Минимальное значение столбца
	public function Max($req);					//Максимальное значение столбца
	public function Sum($req);					//Сумма всех значений столбца
	public function Rnd($req);					//Случайная запись

	//Прочие запросы
	public function Ins($req);					//Запрос Insert
	public function Upd($req);					//Запрос Update
	public function Del($req);					//Запрос Delete
	public function CT($req);					//Запрос Create Table.
	public function UT($req);					//Обновление тоблицы
	public function Drop($table);				//Запрос Drop Table
	public function Truncate($table);			//Запрос Truncate Table
	public function Grant($req);				//Запрос Grant
	public function Rev($req);					//Запрос Revoke
	public function Alter($req);				//Запрос Revoke

	//Блокировка таблиц
	public function lock($tbl);					//Lock Table
	public function unlock();					//Unlock Table
	public function isLock($tbl);				//Возвращает lock или нет

	//Параметры таблиц
	public function GetTables();				//Все таблицы
	public function GetFields($table, &$extra = array());			//Все поля таблицы
	public function issetTable($table);			//Проверяет существует ли таблица
	public function issetField($table, $field);	//Проверяет существует ли поле
	public function getTableStructure($table);	//Возвращает структуру таблицы
	public function copyTable($oldTable, $newTable, $newDB = false);	//Копирование таблицы

	//Транзакции
	public function trStart();					//Объявляет начало транзакции
	public function trEnd($result);				//Сворачивает транзакцию

	//Дамп БД
	public function GetDump($tables = 'all', $params = array());	//Создает дамп БД
	public function AddDump($dump, $params = array());				//Восстанавливает БД из дампа

	//Ошибки
	public function error($msg, $rObj = false);		//Сообщение об ошибке

	//Разрушение соединения
	public function Disconnect();				//Разрывает соединение
	public function __destruct();

	//Прочие
	public static function checkConnect($host, $user, $pwd, $db);	//Проверка - можно ли подключиться с данными параметрами
	public static function getConnectMatrix($params = array());		//Создает форму для внесение данных соединения
	public static function getConnectParams(Moduleinterface $obj, $params);		//Отдает параметры внесенные в форму
}

?>