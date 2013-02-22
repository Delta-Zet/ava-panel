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


if(!empty($params['modules'])){
	foreach($params['modules'] as $e){
		if($this->getVersion($e['type'], $e, $params['defaults'])) continue;
		$matrix['caption_mod_'.$e['installator']]['text'] = 'Модуль "'.$e['text'].'"';
		$matrix['caption_mod_'.$e['installator']]['type'] = 'caption';

		$modName = $e['name'];
		$requiredModules = isset($e['requirements']['requiredModules']) ? $e['requirements']['requiredModules'] : array();
		require(_W.'modules/core/forms/clone_pkg.php');
		$matrix['settings']['type'] = 'hidden';
	}
}


if(!empty($params['templates'])){
	foreach($params['templates'] as $e){
		if($this->getVersion($e['type'], $e, $params['defaults'])) continue;
		$matrix['caption_tmpl_'.$e['installator']]['text'] = '{Call:Lang:core:core:shablontipa:'.Library::serialize(array($e['text'], $e['tmplType'])).'}';
		$matrix['caption_tmpl_'.$e['installator']]['type'] = 'caption';

		$matrix['name_'.$e['installator']]['text'] = '{Call:Lang:core:core:imiashablona}';
		$matrix['name_'.$e['installator']]['type'] = 'text';
		$matrix['name_'.$e['installator']]['comment'] = '{Call:Lang:core:core:liuboeponiat2}';
		$matrix['name_'.$e['installator']]['warn'] = '{Call:Lang:core:core:vydolzhnyuka}';

		$matrix['folder_'.$e['installator']]['text'] = '{Call:Lang:core:core:papkashablon}';
		$matrix['folder_'.$e['installator']]['type'] = 'text';
		$matrix['folder_'.$e['installator']]['comment'] = '{Call:Lang:core:core:tolkolatinsk1}';
		$matrix['folder_'.$e['installator']]['warn'] = '{Call:Lang:core:core:vydolzhnyuka1}';
		$matrix['folder_'.$e['installator']]['warn_function'] = 'regExp::ident';

		if($e['type'] == 'main' || $e['type'] == 'admin'){
			$matrix['language_'.$e['installator']]['text'] = '{Call:Lang:core:core:iazykispolzu}';
			$matrix['language_'.$e['installator']]['type'] = 'select';
			$matrix['language_'.$e['installator']]['warn'] = '{Call:Lang:core:core:vydolzhnyuka2}';
			$matrix['language_'.$e['installator']]['additional'] = $langList;

			if($e['type'] == 'main' && !empty($cmsModules)){
				$matrix['cmsMod_'.$e['installator']]['text'] = '{Call:Lang:core:core:modulcmsupra}';
				$matrix['cmsMod_'.$e['installator']]['type'] = 'select';
				$matrix['cmsMod_'.$e['installator']]['additional'] = $cmsModules;
			}

			if(!empty($dependSysTmp)){
				$matrix['sys_depend_tmp_'.$e['installator']]['text'] = '{Call:Lang:core:core:ispolzuemyjs}';
				$matrix['sys_depend_tmp_'.$e['installator']]['type'] = 'select';
				$matrix['sys_depend_tmp_'.$e['installator']]['additional'] = $dependSysTmp;
			}

			if(!empty($dependBlockTmp)){
				$matrix['block_depend_tmp_'.$e['installator']]['text'] = '{Call:Lang:core:core:ispolzuemyjs1}';
				$matrix['block_depend_tmp_'.$e['installator']]['type'] = 'select';
				$matrix['block_depend_tmp_'.$e['installator']]['additional'] = $dependBlockTmp;
			}

			if(!empty($dependModuleTmps['dependModuleTmps']) && is_array($dependModuleTmps['dependModuleTmps'])){
				foreach($dependModuleTmps['dependModuleTmps'] as $i1 => $e1){
					$depModName = empty($dependModuleTmps['depModNames'][$i1]) ? $i1 : $dependModuleTmps['depModNames'][$i1];

					$matrix['depend_tmp_'.$i1.'_'.$e['installator']]['text'] = '{Call:Lang:core:core:ispolzuemyjs2:'.Library::serialize(array($depModName)).'}';
					$matrix['depend_tmp_'.$i1.'_'.$e['installator']]['type'] = 'select';
					$matrix['depend_tmp_'.$i1.'_'.$e['installator']]['additional'] = $e1;
				}
			}
		}
	}
}

?>