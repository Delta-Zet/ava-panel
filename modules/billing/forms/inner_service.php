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


switch($step){
	case 1:
		$matrix['client_id']['text'] = '{Call:Lang:modules:billing:idpolzovatel}';
		$matrix['client_id']['type'] = 'text';
		$matrix['client_id']['warn'] = '{Call:Lang:modules:billing:neukazanidpo}';

		$matrix['pkg']['text'] = '{Call:Lang:modules:billing:tarifnyjplan}';
		$matrix['pkg']['type'] = 'select';
		$matrix['pkg']['additional'] = $packages;

		$field_to = '';
		$value_to = '';

		$field = 'date';
		$text = '{Call:Lang:modules:billing:zakazana}';
		require(_W.'forms/type_calendar2.php');

		$field = 'created';
		$text = '{Call:Lang:modules:billing:sozdana}';
		require(_W.'forms/type_calendar2.php');

		$field = 'last_paid';
		$text = '{Call:Lang:modules:billing:oplataprinia}';
		require(_W.'forms/type_calendar2.php');

		$field = 'paid_to';
		$text = '{Call:Lang:modules:billing:oplachenado}';
		require(_W.'forms/type_calendar2.php');
		$matrix['paid_to']['warn'] = '{Call:Lang:modules:billing:neukazanopok}';

		$matrix['modify']['text'] = '{Call:Lang:modules:billing:modifitsirva}';
		$matrix['modify']['type'] = 'checkbox';

		$matrix['auto']['text'] = '{Call:Lang:modules:billing:sozdatavtoma}';
		$matrix['auto']['type'] = 'checkbox';

		break;

	case 2:
		$matrix['price_capt']['type'] = 'caption';
		$matrix['price_capt']['text'] = '{Call:Lang:modules:billing:dannyedliara}';

		$matrix['total']['text'] = '{Call:Lang:modules:billing:spisatsosche:'.Library::serialize(array($currency)).'}';
		$matrix['total']['type'] = 'text';
		$matrix['total']['comment'] = '{Call:Lang:modules:billing:ehtasummabud}';
		$matrix['total']['warn'] = '{Call:Lang:modules:billing:neukazanasum}';

		$matrix['price']['text'] = '{Call:Lang:modules:billing:raschetnaias4:'.Library::serialize(array(Dates::termsListVars($baseTerm), $currency)).'}';
		$matrix['price']['type'] = 'text';
		$matrix['price']['warn'] = '{Call:Lang:modules:billing:neukazanaras}';

		$matrix['ind_price']['text'] = '{Call:Lang:modules:billing:politikapere}';
		$matrix['ind_price']['type'] = 'select';
		$matrix['ind_price']['additional'] = array(
			'0' => '{Call:Lang:modules:billing:nepereschity}',
			'1' => '{Call:Lang:modules:billing:pereschityva}',
			'2' => '{Call:Lang:modules:billing:pereschityva1}',
		);

		$matrix['create_capt']['type'] = 'caption';
		$matrix['create_capt']['text'] = '{Call:Lang:modules:billing:dannyedliaso}';

		$matrix[$prefix.'ident'.$id]['text'] = 'Идентифицировать как';
		$matrix[$prefix.'ident'.$id]['type'] = 'text';
		$matrix[$prefix.'ident'.$id]['warn'] = 'Вы не указали идентификатор';

		break;
}

?>