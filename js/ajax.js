
function ajaxInsert(obj){
	document.getElementById(obj.insert).innerHTML = obj.getParsed(obj.phpVar);
}

function ajaxNewPage(obj){
	if(obj.closeWait) eval(obj.closeWait + ';');
	var text = obj.getParsed(obj.phpVar);
	text = text.replace(/<!DOCTYPE[^>]+>/gi, '');
	document.getElementsByTagName('body')[0].innerHTML = text;
}

function ajaxResult(id){	var obj = ajaxObjects.load(id);

	if (obj.request.readyState == 4){
		if (obj.request.status == 200){
			obj.result = obj.request.responseText;
			obj.parseResult();
			if(obj.callBackFunc) eval( obj.callBackFunc + '(obj)' );
		}
	}
}

function AVA_ajaxInterface(){
	this.sendLinkByObj = function(obj, openWait, closeWait, phpVar){		return this.sendUrl(obj.href, '', phpVar, 'ajaxNewPage', openWait, closeWait)	}

	this.sendFormByObj = function(obj, openWait, closeWait, phpVar){
		return this.sendForm(obj.getAttribute('id'), '', phpVar, 'ajaxNewPage', openWait, closeWait)
	}

	this.sendUrl = function(url, insert, phpVar, callBackFunc, openWait, closeWait){		var ajax = this.getAjax(insert, phpVar, callBackFunc, openWait, closeWait);
		return ajax.ajaxCall(url);
	}
	this.sendForm = function (name, insert, phpVar, callBackFunc, openWait, closeWait){		/*
			Отправляет форму с использованием ajax
		*/
		var ajax = this.getAjax(insert, phpVar, callBackFunc, openWait, closeWait);
		ajax = this.setFormParams(ajax, name);
		return ajax.ajaxCallPost(document.getElementById(name).action);
	}

	this.getAjax = function(insert, phpVar, callBackFunc, openWait, closeWait){		/*
			Создает объект ajax
		*/
		var ajax = new AVA_ajax();
		if(!callBackFunc && insert) callBackFunc = 'ajaxInsert';
		ajax.callBackFunc = callBackFunc;

		if(openWait){
			eval(openWait + ';');
			ajax.closeWait = closeWait;
		}

		ajax.phpVar = phpVar;
		ajax.insert = insert;
		return ajax;
	}

	this.setFormParams = function (ajaxObj, name){		/*
			Устанавливает параметры из формы в объект Ajax
		*/
		var n = document.getElementById(name);
		for(var i = 0; i <= n.elements.length; i++){
			if(n.elements[i]){
				if((n.elements[i].type == 'checkbox' || n.elements[i].type == 'radio') && !n.elements[i].checked) continue;
				ajaxObj.addPostVar(n.elements[i].name, n.elements[i].value);
			}
		}

		return ajaxObj;
	}}


function AVA_ajax(){

	this.request = false;
	this.result = '';

	this.parsedBlocks = new Array;
	this.parsedIds = new Array;

	this.postVars = new Array;
	this.postValues = new Array;

	this.insert = false;

	this.phpVar = '';
	this.objNum = '';
	this.callBackFunc = '';

	/*
		Обращение к удаленному
	*/

	this.ajaxCall = function (url){		/*
			Отправляет запрос на обращение по указанному URL. Обращается методом GET, запрашивая переменную phpVar
			Устанавливает для ответа responseFunc
		 */

		this.objNum = ajaxObjects.append(this);
		url = this.setPhpVarToUrl(url);
		if(!this.createAjaxObj()) return false;

		this.request.open('GET', url, true);
		eval('this.request.onreadystatechange = function(){ ajaxResult(' + this.objNum + '); };');
		this.request.send(null);

		return true;	}

	this.createAjaxObj = function(){		//Создает объект Ajax
		try{
			this.request = new XMLHttpRequest();
		}
		catch(trymicrosoft){
			try {
				this.request = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(othermicrosoft){
				try{
					this.request = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch(failed){
					this.request = false;
				}
			}
		}

		if(!this.request){
			alert("Error initializing XMLHttpRequest!");
			return false;
		}

		return true;
	}

	this.setPhpVarToUrl = function(url, phpVar, objNum){		if(/\?/.test(url)) url = url + '&';
		else url = url + '?';
		return url + 'AVA_varOnly=' + (this.phpVar ? this.phpVar : '') + '&backObj=' + this.objNum + '&rnd=' + Math.random();
	}


	/*
		Обработка ответа
	*/

	this.parseResult = function(){		var parsed = this.result.split('|');
		for(var i = 0; i < parsed.length; i ++){			var pair = parsed[i].split(':');
			if( pair.length < 2 ) continue;

			this.parsedIds[i] = pair[0];
			this.parsedBlocks[i] = Base64.decode(pair[1]);
		}
	}

	this.getParsed = function(id){
		if(!id) return this.result;
		for(var i = 0; i < this.parsedIds.length; i ++){
			if(this.parsedIds[i] == id) return this.parsedBlocks[i];		}

		return this.result;	}


	/*
		Отправка методом POST
	*/

	this.addPostVar = function(v, val){		var n = this.postVars.length;		this.postVars[n] = v;
		this.postValues[n] = val;
	}

	this.getPost = function (){		var r = '';
		for(var i = 0; i < this.postVars.length; i ++){			r += this.postVars[i] + '=' + escape(this.postValues[i]) + '&';		}

		return r;
	}

	this.ajaxCallPost = function (url){
		/*
			Отправляет запрос на обращение по указанному URL. Обращается методом POST, запрашивая переменную phpVar
			Устанавливает для ответа responseFunc
		 */

		this.objNum = ajaxObjects.append(this);
		url = this.setPhpVarToUrl(url);
		if(!this.createAjaxObj()) return false;

		this.request.open('POST', url, true);
		eval('this.request.onreadystatechange = function(){ ajaxResult(' + this.objNum + '); };');
		this.request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		this.request.send(this.getPost());

		return true;
	}
}


/*
  base64 декодер

  copyRight:
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*/

var Base64 = {

	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},

	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},

	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

}


/*
  Объекты ajax
*/

function ajaxObjects(){

	this.objects = new Array;

	this.append = function(obj){
		var elms = this.objects.length;
		this.objects[elms] = obj;

		return elms;
	}

	this.load = function(id){
		return this.objects[id];
	}
}

ajaxObjects = new ajaxObjects();
ajax = new AVA_ajaxInterface();

