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


require(_W.'forms/type_neweml.php');

$matrix['status']['text'] = '{Call:Lang:modules:ticket:status}';
$matrix['status']['type'] = 'select';
$matrix['status']['additional'] = Library::array_merge(array('' => 'Выставить автоматически'), $status);

$matrix['text']['text'] = '{Call:Lang:modules:ticket:tekstsoobshc}';
$matrix['text']['type'] = 'textarea';
$matrix['text']['warn'] = '{Call:Lang:modules:ticket:nettekstavop}';

if(Library::constVal('IN_ADMIN')){
	$matrix['text']['template'] = 'content';
	$matrix['text']['post_text'] = '
		<script type="text/javascript" src="'._D.'js/tinymce/tiny_mce.js"></script>
		<script type="text/javascript">
			tinyMCE.init({
				// General options
				mode : "textareas",
				theme : "advanced",
				plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,images,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount",
				editor_selector : "maintextarea",

				// Theme options
				theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect,|,images",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
				theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
				theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,

				// Example content CSS (should be your site CSS)
				content_css : MTMPL + "style.css",

				// Drop lists for link/image/media/template dialogs
				template_external_list_url : "lists/template_list.js",
				external_link_list_url : "lists/link_list.js",
				external_image_list_url : "lists/image_list.js",
				media_external_list_url : "lists/media_list.js",

				// Replace values for the template plugin
				template_replace_values : {
					username : "Some User",
					staffid : "991234"
				}
			});
		</script>';
}
else{
	$matrix['text']['template'] = 'big';
	$matrix['text']['post_text'] = '</div>';
	$matrix['text']['pre_text'] = '<div class="support_form"><script type="text/javascript">

		function add_format(tag1, tag2, id){
			var range = document.selection;

			if(range){
				range = range.createRange();
				range.text = repl_func(range.text, tag1, tag2);
				range.select();
			}
			else{
				var obj = document.getElementById(id);
				var start = obj.selectionStart;
				var end = obj.selectionEnd;

				var rs = repl_func(obj.value.substr(start, end-start), tag1, tag2);
				obj.value = obj.value.substr(0,start) + rs + obj.value.substr(end);
				obj.setSelectionRange(end,end);
			}
		}

		function repl_func(str, tag1, tag2){
			return \'[\' + tag1 + \']\' + str + \'[/\' + tag2 + \']\';
		}

	</script>
	<div style="margin-top: 20px; float: left;">
		<input type="button" value=" B " style="font-weight: bold;" onClick="add_format(\'b\', \'b\', \'text\');" />
		<input type="button" value=" U " style="text-decoration: underline;" onClick="add_format(\'u\', \'u\', \'text\');" />
		<input type="button" value=" I "  style="font-style: italic;" onClick="add_format(\'i\', \'i\', \'text\');" />
		<input type="button" value=" RED " style="color: #D00;" onClick="add_format(\'color=#D00\', \'color\', \'text\');" />
	</div>';
}

if($aof = $this->Core->getParam('attachesOnForm', $this->mod)){
	$matrix['attach_capt']['text'] = '{Call:Lang:modules:ticket:priattachitf}';
	$matrix['attach_capt']['type'] = 'caption';

	$t = time().'-'.rand(0, 1000);
	for($i = 1; $i <= $aof; $i ++){
		$matrix['attach'.$i]['text'] = '';
		$matrix['attach'.$i]['type'] = 'file';
		$matrix['attach'.$i]['additional'] = array(
			'allow_ext' => array('.gif', '.jpg', '.bmp', '.png', '.pcx', '.psd', '.pdf', '.zip', '.rar', '.tar', '.gz', '.arj', '.7z'),
			'dstFolder' => $this->Core->getParam('supportMessagesAttachFolder', $this->mod),
			'newName' => $t.'-'.$i
		);
	}
}

?>