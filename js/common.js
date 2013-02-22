

function getBrowserWindowH(){
	return document.documentElement.clientHeight;
}

function getBrowserWindowW(){
	return document.documentElement.clientWidth;
}

function getBlockX(id){
	return document.getElementById(id).offsetLeft;

function getBlockY(id){
	if(!document.getElementById(id)) return false;
	return document.getElementById(id).offsetTop;
}

function getBlockW(id){
	if(!document.getElementById(id)) return false;

function getBlockH(id){
	if(!document.getElementById(id)) return false;
	return document.getElementById(id).offsetHeight;
}

function getScrollV(){

function getScrollH(){
	return self.pageXOffset || (document.documentElement && document.documentElement.scrollLeft) || (document.body && document.body.scrollLeft);
}

function scroll(posH, posV){

function getFloat(num){
	if(isNaN(num)){

	return num;

function switchBlock(id){
	if(d.style.display == 'none') d.style.display = 'block';
	else d.style.display = 'none';

function setOpacity(id, opacity, max, speed){

	var obj = document.getElementById(id);

	obj.style.zoom = 1;
	obj.style.filter = 'alpha(opacity=' + (opacity * 100) + ')';

	obj.style.opacity = opacity;
	opacity = opacity + 0.05;
	if(opacity >= max) return;

	setTimeout('setOpacity("' + id + '", ' + opacity + ', ' + max + ', ' + speed + ')', speed);
}

function appendUrlVar(url, variable, value){
	else url = url + '?';

	return url + variable + '=' + value;
}

function showHTML(block){
	d.innerHTML = block;
	var ds = d.style;

	ds.display = 'block';
	ds.position = 'absolute';
	ds.left = (event.pageX + 15) + 'px';
	ds.top = (event.pageY + 20) + 'px';

	document.getElementsByTagName('body')[0].appendChild(d);

function ge(id){
	return document.getElementById(id);

function vis(id){
	else return true;

function strToObj(str){
	var arr = str.split(/,/);
	var rslt = {};

	for(var k in arr){
		rslt[arr[k]] = true;
	}

	return rslt;
}

function roundCeil(num, round){
