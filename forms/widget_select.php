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


if(empty($field)) $field = 'widget';
if(empty($widgetId)) $widgetId = $field;

$matrix[$field]['text'] = 'Вставить виджет';
$matrix[$field]['type'] = 'select';
$matrix[$field]['additional'] = $GLOBALS['Core']->getWidgets();
$matrix[$field]['additional_text'] = ' <input type="button" value="Вставить" onClick="widgetForm();" class="b">';
$matrix[$field]['additional_style'] = ' style="width: 280px;"';
$matrix[$field]['post_text'] = '<div id="widgetForm"></div>
<script type="text/javascript">
	function setWidget(){
		var widget = document.getElementById("'.$widgetId.'").value;
		if(!widget){
			alert("Не выбран виджет");
			return false;
		}

		var range = document.selection;
		var text = "{Call:Plugin:" + widget + getExtraWidgetStr() + "}";

		if(range){
			range = range.createRange();
			range.text = text;
			range.select();
		}
		else{
			var obj = document.getElementById("'.$textField.'");
			var start = obj.selectionStart;
			var end = obj.selectionEnd;

			obj.value = obj.value.substr(0, start) + text + obj.value.substr(end);
			obj.setSelectionRange(end, end);
		}
	}

	function getExtraWidgetStr(){
		var form = document.getElementById("form_" + document.getElementById("'.$widgetId.'").value);
		var r = "";

		if(form){
			for(var i = 0; i <= n.elements.length; i++){
				if(n.elements[i]){
					if((n.elements[i].type == "checkbox" || n.elements[i].type == "radio") && !n.elements[i].checked) continue;
					r += n.elements[i].name + "=" + escape(n.elements[i].value) + "&";
				}
			}
			if(r) r = ":" + r;
		}

		document.getElementById("widgetForm").innerHTML = "";
		document.getElementById("widgetForm").style.display = false;
		return r;
	}

	function widgetForm(){
		var widgetForms = '.Library::jsHash($GLOBALS['Core']->getWidgetForms()).';
		var widget = document.getElementById("'.$widgetId.'").value;

		if(widgetForms[widget]){
			document.getElementById("widgetForm").innerHTML = widgetForms[widget];
			document.getElementById("widgetForm").style.display = true;
			return false;
		}
		else return setWidget();
	}
</script>';

?>