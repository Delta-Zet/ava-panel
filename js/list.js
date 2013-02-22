
function selectAllEntries(check, lim){	for(var i = 1; i <= lim; i ++){		if(document.getElementById('entry' + i) && !document.getElementById('entry' + i).disabled) document.getElementById('entry' + i).checked = check;	}
}

function saveSort(name, action){
	document.getElementById(name).setAttribute('action', action);
	document.getElementById(name).submit();
}

