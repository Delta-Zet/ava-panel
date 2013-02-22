
function showTopPanel(offset){	//Паказывает верхнюю панель

	if(offset >= 250){		window.clearTimeout(intl);
		return;	}

	var d = document.getElementById('toppanelblock');
	var d1 = document.getElementById('toppanel');
	d1.style.display = 'none';

	d.style.top = (-250 + offset) + 'px';
	var int = 10;

	if(offset > 200) int = 3;
	if(offset > 220) int = 2;
	if(offset > 235) int = 1;

	var n = Math.ceil(offset + int);
	intl = window.setTimeout("showTopPanel(" + n + ")", 20);
}

function hideTopPanel(offset){	var d = document.getElementById('toppanelblock');
	var d1 = document.getElementById('toppanel');

	if(offset > 250){		d.style.top = '-250px';
		d1.style.display = 'block';
		window.clearTimeout(intl);

		return;	}

	d.style.top = (0 - offset) + 'px';
	var n = offset + 10;
	intl = window.setTimeout("hideTopPanel(" + n + ")", 20);
}

function openMainMenu(id){	var d = document.getElementById(id);
	if(!d) return;

	d.style.display = 'block';
	d.style.position = 'absolute';
}

function hideMainMenu(id){
	var d = document.getElementById(id);
	if(!d) return;

	d.style.display = 'none';
	d.style.position = 'absolute';
}








