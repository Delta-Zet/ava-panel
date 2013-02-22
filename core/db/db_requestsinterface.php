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


interface db_RequestsInterface{

	public function __construct($connection);

	// 
	public function Send($query);				// 
	public function ReSend();					// 

	//  
	public function GetQuery();					//  
	public function GetResult();				//  
	public function GetRows();					//  

	public function GetId();					// mysql_insert_id
	public function GetInfo();					// mysql_info

	//  
	public function Seek($row);					//   
	public function Fetch($row = false);		//   
	public function FetchArray($row = false);	//   
	public function CellFetch($row, $cell);		//   

	//
	public function GetError();					//  
	public function GetErrorCode();				//   

	//
	public function Clear();					//  
	public function __destruct();

}

?>