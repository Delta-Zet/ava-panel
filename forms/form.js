
function Submit(name, chObj, id_pref, action){	var errors = '';
	var n = "\n";
	var list = document.getElementById(name).getElementsByTagName("*");
	var objects = {};

	for(var i in list){
		if(list.item(i) && (list.item(i).tagName == 'INPUT' || list.item(i).tagName == 'SELECT' || list.item(i).tagName == 'TEXTAREA')){			var id = list.item(i).getAttribute('id');
			if(id){
				id = id.replace(/\W+/, '_');
				eval("objects." + id + " = list.item(i);");
			}
		}
	}

	for(var i in chObj.warns){
		eval("var d = objects." + id_pref + i);

		if(d || chObj.types[i] == 'checkbox_array' || chObj.types[i] == 'radio'){
			if(chObj.types[i] == 'checkbox'){				if(!getBlockW(id_pref + i)) continue;				if(!d.checked) errors += chObj.warns[i] + n;			}
			else if(chObj.types[i] == 'checkbox_array' || chObj.types[i] == 'radio'){				var isCheck = false;

				eval('var chObj2 = chObj.addit.' + i);				for(var i1 in chObj2){					if(!getBlockW(id_pref + i + '_' + i1)){						isCheck = true;
						break;					}
					var d1 = document.getElementById(id_pref + i + '_' + i1);

					if(!d1) break;
					else if(d1.checked){						isCheck = true;
						break;					}				}
				if(!isCheck) errors += chObj.warns[i] + n;
			}
			else if(chObj.types[i] == 'file'){
				if(!getBlockW(id_pref + i)) continue;
				if(!d.value && (!document.getElementById(id_pref + i + '_hidden') || !document.getElementById(id_pref + i + '_hidden').value)){					errors += chObj.warns[i] + n;				}			}
			else if(!d.value && getBlockW(id_pref + i)) errors += chObj.warns[i] + n;
		}
	}

	for(var i in chObj.warnPatterns){		var d = document.getElementById(id_pref + i);
		if(d && d.value && !chObj.warnPatterns[i].test(d.value) && !chObj.warnPatterns[i].test(d.value)) errors += chObj.warnPatternsText[i] + n;	}
	if(errors.length > 0){		alert(errors);		return false;
	}

	if(action) ge(name).action = action;
	ge(name).submit();
	return false;}

function SubmitMultiblock(name, chObj, id_pref){	var i = 0;

	while(document.getElementById('block' + i)){
		document.getElementById('block' + i).style.display = 'block';
		i ++;
	}

	Submit(name, chObj, id_pref);
}

function formBlocksSwitch(id){	var i = 0;
	while(document.getElementById('block' + i)){		document.getElementById('block' + i).style.display = 'none';
		document.getElementById('caption' + i).className = '';
		i ++;	}

	if(document.getElementById('block' + id) && document.getElementById('caption' + id)){
		document.getElementById('block' + id).style.display = 'block';
		document.getElementById('caption' + id).className = 'active';
	}
}

function showFormBlock(id){	if(document.getElementById(id)){
		document.getElementById(id).style.display = 'block';
	}}

function hideFormBlock(id){
	if(document.getElementById(id)){
		document.getElementById(id).style.display = 'none';
	}
}

function switchFormBlocks(hide, show){	if(!hide) hide = {};
	if(!show) show = {};
	for(var k in hide){		if(d = document.getElementById(k)) d.style.display = 'none';
	}

	for(var k in show){
		if(d = document.getElementById(k)) d.style.display = 'block';
	}
}

function switchByCheckbox(id, blockId){	if(document.getElementById(id).checked) document.getElementById(blockId).style.display = 'block';
	else document.getElementById(blockId).style.display = 'none';
}

function switchByValue(id, params){
	var d = document.getElementById(id);	if(d.type == 'checkbox' && d.checked) var v = 1;
	else if(d.type != 'checkbox') var v = d.value;
	for(var k in params.blocks){		hideFormBlock(k);	}
	if(params[v]){
		for(var k in params[v]){
			showFormBlock(k);
		}
	}
}

function showTechBlock(id){
	var d = document.getElementById('block' + id);
	var d2 = document.getElementById('caption' + id);
	if(d.style.display != 'block'){ d.style.display = 'block'; d2.innerHTML='-'; }
	else{ d.style.display = 'none'; d2.innerHTML='+'; }
}

function inputType(id, type){	document.getElementById(id).type = type;
}

function setOptions(obj, options){	/*
		Добавляет поля в определенный select
	*/

	var v = obj.value;

	if(obj.hasChildNodes()) {
		for(var i = 0; i < obj.childNodes.length; i++){
			var cur = obj.childNodes[i];
			if(cur.nodeName.toLowerCase() == "option") {
				obj.removeChild(cur);
			}
		}
	}
	obj.innerHTML = '';

	for(var k in options){
		var optElm = document.createElement("option");
		if(v && k == v) optElm.setAttribute('selected', true);

		if(k == '$___empty') optElm.setAttribute('value', '');
		else optElm.setAttribute('value', k);
		optElm.innerHTML = options[k];
		obj.appendChild(optElm);
	}}

