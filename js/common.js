

function getBrowserWindowH(){
	return document.documentElement.clientHeight;
}

function getBrowserWindowW(){
	return document.documentElement.clientWidth;
}

function getBlockX(id){	if(!document.getElementById(id)) return false;
	return document.getElementById(id).offsetLeft;}

function getBlockY(id){
	if(!document.getElementById(id)) return false;
	return document.getElementById(id).offsetTop;
}

function getBlockW(id){
	if(!document.getElementById(id)) return false;	return document.getElementById(id).offsetWidth;}

function getBlockH(id){
	if(!document.getElementById(id)) return false;
	return document.getElementById(id).offsetHeight;
}

function getScrollV(){	return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);}

function getScrollH(){
	return self.pageXOffset || (document.documentElement && document.documentElement.scrollLeft) || (document.body && document.body.scrollLeft);
}

function scroll(posH, posV){	window.scrollBy(posH, posV);}

function getFloat(num){	num = parseFloat(num);
	if(isNaN(num)){		num = 0;	}

	return num;}

function switchBlock(id){	var d = document.getElementById(id);
	if(d.style.display == 'none') d.style.display = 'block';
	else d.style.display = 'none';}

function setOpacity(id, opacity, max, speed){	//Плавная установка затемнения

	var obj = document.getElementById(id);

	obj.style.zoom = 1;
	obj.style.filter = 'alpha(opacity=' + (opacity * 100) + ')';

	obj.style.opacity = opacity;
	opacity = opacity + 0.05;
	if(opacity >= max) return;

	setTimeout('setOpacity("' + id + '", ' + opacity + ', ' + max + ', ' + speed + ')', speed);
}

function appendUrlVar(url, variable, value){	if(/\?/.test(url)) url = url + '&';
	else url = url + '?';

	return url + variable + '=' + value;
}

function showHTML(block){	var d = document.createElement('div');
	d.innerHTML = block;
	var ds = d.style;

	ds.display = 'block';
	ds.position = 'absolute';
	ds.left = (event.pageX + 15) + 'px';
	ds.top = (event.pageY + 20) + 'px';

	document.getElementsByTagName('body')[0].appendChild(d);}

function ge(id){
	return document.getElementById(id);}

function vis(id){	if(ge(id).style.display == 'none') return false;
	else return true;}

function strToObj(str){
	var arr = str.split(/,/);
	var rslt = {};

	for(var k in arr){
		rslt[arr[k]] = true;
	}

	return rslt;
}

function roundCeil(num, round){	if(round <= 0) return num;	round = Math.pow(10, round);	return Math.round(num / round) * round;}

