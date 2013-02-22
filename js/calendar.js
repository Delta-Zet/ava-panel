

function calendar(){

	this.monthLen = new Array;
	this.monthLen[1] = 31;
	this.monthLen[2] = 28;
	this.monthLen[3] = 31;
	this.monthLen[4] = 30;
	this.monthLen[5] = 31;
	this.monthLen[6] = 30;
	this.monthLen[7] = 31;
	this.monthLen[8] = 31;
	this.monthLen[9] = 30;
	this.monthLen[10] = 31;
	this.monthLen[11] = 30;
	this.monthLen[12] = 31;

	this.monthNames = new Array;
	this.monthNames[1] = 'Январь';
	this.monthNames[2] = 'Февраль';
	this.monthNames[3] = 'Март';
	this.monthNames[4] = 'Апрель';
	this.monthNames[5] = 'Май';
	this.monthNames[6] = 'Июнь';
	this.monthNames[7] = 'Июль';
	this.monthNames[8] = 'Август';
	this.monthNames[9] = 'Сентябрь';
	this.monthNames[10] = 'Октябрь';
	this.monthNames[11] = 'Ноябрь';
	this.monthNames[12] = 'Декабрь';

	this.iconL = '<';
	this.iconR = '>';
	this.iconUp = '&uarr;';
	this.iconDn = '&darr;';
	this.iconClose = 'X';

	this.calendarId = '';
	this.fieldId = '';

	this.openCalendar = function(fieldId, calendarId, tm, dt){		this.calendarId = calendarId;
		this.fieldId = fieldId;
		document.getElementById(fieldId).blur();
		var date = document.getElementById(fieldId).value;
		if(date.length != 10 && date.length != 19) date = this.getNow(tm, dt);

		date = date.split(" ");
		date['0'] = date['0'].split('.');
		date['1'] = date['1'].split(':');
		this.drawCalendar(Math.ceil(date['0']['0']), Math.ceil(date['0']['1']), date['0']['2'], date['1']['0'], date['1']['1'], date['1']['2']);

		document.getElementById(calendarId).style.display = 'block';		document.getElementById(calendarId).style.position = 'absolute';
		document.getElementById(calendarId).style.left = (getBlockX(fieldId) + getBlockW(fieldId) - 22) + 'px';
		document.getElementById(calendarId).style.top = (getBlockY(fieldId) + getBlockH(fieldId) + 10) + 'px';
	}

	this.drawCalendar = function(d, m, y, h, i, s){
		var now = new Date(y, m - 1, 1, h, i, s);

		var dow = now.getDay();
		if(!dow) dow = 7;

		var days = this.monthLen[m];
		if(m == 1 && this.isLeapYear(y)) days = 29;

		var calendarText = '';
		var day = 0;

		for(weeks = 1; weeks <= 6; weeks ++){			calendarText += '<tr class="calendar_day">';

			for(wd = 1; wd <= 7; wd ++){				if(!day && wd == dow) day = 1;

				if(day > days || !day){					calendarText += '<td>&#160;</td>';
					continue;				}

				calendarText += '<td><a href="javascript:calendar.insertDay(' + day + ')">' + day + '</a></td>';
				day ++;			}			calendarText += '</tr>';
		}

		var text = '<table cellpadding="0" cellspacing="0">' +
			'<tr><td colspan="7" class="close_calendar"><a href="javascript:calendar.closeCalendar()">' + this.iconClose + '</a></td></tr>' +
			'<tr><td colspan="7"><div class="calendar_time">' +
					'<span><a href="javascript:calendar.changeHour(1)">' + this.iconUp + '</a><input type="text" name="h" id="calendar_h" value="' + h + '" />' +
						'<a href="javascript:calendar.changeHour(-1)">' + this.iconDn + '</a></span><span class="dlm">:</span>' +
					'<span><a href="javascript:calendar.changeMinute(1)">' + this.iconUp + '</a><input type="text" name="i" id="calendar_i" value="' + i + '" />' +
						'<a href="javascript:calendar.changeMinute(-1)">' + this.iconDn + '</a></span><span class="dlm">:</span>' +
					'<span><a href="javascript:calendar.changeSecond(1)">' + this.iconUp + '</a><input type="text" name="s" id="calendar_s" value="' + s + '" />' +
						'<a href="javascript:calendar.changeSecond(-1)">' + this.iconDn + '</a></span>' +
					'</div>' +
				'<div class="calendar_caption"><a href="javascript:calendar.changeMonth(-1)">' + this.iconL + '</a><span id="calendar_m_text">' + this.monthNames[m] +
					'</span><a href="javascript:calendar.changeMonth(1)">' + this.iconR + '</a><input type="hidden" name="m" id="calendar_m" value="' + m + '" /></div>' +
				'<div class="calendar_caption"><a href="javascript:calendar.changeYear(-1)">' + this.iconL + '</a><span id="calendar_y_text">' + y +
					'</span><a href="javascript:calendar.changeYear(1)">' + this.iconR + '</a><input type="hidden" name="y" id="calendar_y" value="' + y + '" /></div>' +
			'</td></tr>' +
			'<tr class="calendar_day_caption"><td>ПН</td><td>ВТ</td><td>СР</td><td>ЧТ</td><td>ПТ</td><td>СБ</td><td>ВС</td></tr>' +
			calendarText +
		'</table>';

		document.getElementById(this.calendarId).innerHTML = text;	}

	this.changeHour = function(add){
		add = Math.ceil(document.getElementById('calendar_h').value) + add;
		if(add < 0) add = 23;
		else if(add > 23) add = 0;

		add = this.insertZero(add);
		document.getElementById('calendar_h').value = add;	}

	this.changeMinute = function(add){
		add = Math.ceil(document.getElementById('calendar_i').value) + add;
		if(add < 0) add = 59;
		else if(add > 59) add = 0;

		add = this.insertZero(add);
		document.getElementById('calendar_i').value = add;
	}

	this.changeSecond = function(add){
		add = Math.ceil(document.getElementById('calendar_s').value) + add;
		if(add < 0) add = 59;
		else if(add > 59) add = 0;

		add = this.insertZero(add);
		document.getElementById('calendar_s').value = add;
	}

	this.changeMonth = function(add){
		add = Math.ceil(document.getElementById('calendar_m').value) + add;
		if(add < 1) add = 12;
		else if(add > 12) add = 1;

		document.getElementById('calendar_m').value = add;
		document.getElementById('calendar_m_text').innerHTML = this.monthNames[add];
		this.drawCalendar(1, document.getElementById('calendar_m').value, document.getElementById('calendar_y').value, document.getElementById('calendar_h').value, document.getElementById('calendar_i').value, document.getElementById('calendar_s').value);
	}

	this.changeYear = function(add){
		add = Math.ceil(document.getElementById('calendar_y').value) + add;
		document.getElementById('calendar_y').value = add;
		document.getElementById('calendar_y_text').innerHTML = add;
		this.drawCalendar(1, document.getElementById('calendar_m').value, document.getElementById('calendar_y').value, document.getElementById('calendar_h').value, document.getElementById('calendar_i').value, document.getElementById('calendar_s').value);
	}

	this.insertDay = function(day){		document.getElementById(this.fieldId).value = this.insertZero(day) + '.' + this.insertZero(document.getElementById('calendar_m').value) + '.' + document.getElementById('calendar_y').value + ' ' + document.getElementById('calendar_h').value + ':' + document.getElementById('calendar_i').value + ':' + document.getElementById('calendar_s').value;
		this.closeCalendar();	}

	this.closeCalendar = function(){		document.getElementById(this.calendarId).style.display = 'none';
	}

	this.isLeapYear = function(y){
		if(!(y % 400)) return true;
		if(!(y % 100)) return false;		if(!(y % 4)) return true;

		return false;	}

	this.insertZero = function(num){		num += '';
		if(num.length == 0) return '00';
		else if(num.length == 1) return '0' + num;
		return num;	}

	this.getNow = function(tm, dt){		var now = new Date();
		if(!tm) tm = this.insertZero(now.getHours()) + ':' + this.insertZero(now.getMinutes()) + ':' + this.insertZero(now.getSeconds());
		if(!dt) dt = this.insertZero(now.getDate()) + '.' + this.insertZero(now.getMonth() + 1) + '.' + now.getFullYear();

		return dt + ' ' + tm;
	}
}

var calendar = new calendar();

