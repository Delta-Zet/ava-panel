
Calculator = {

	sum: 0,
	installSum: 0,

	calculate : function(id, data){		this.getCalculation(data)		this.insCalculation(id);	},

	insCalculation : function(id){
		document.getElementById(id).innerHTML = this.sum.toFixed(2);
		document.getElementById(id + '_install').innerHTML = this.installSum.toFixed(2);
	},

	getCalculation : function(data){
		var p = getFloat(data.price);
		var ip = getFloat(data.installPrice);
		var c = data.constructor;

		for(var i in c){			if(c[i].type == 'text'){				if(v = document.getElementById(i).value){
					if(v == 'Unlimit'){						p += getFloat(c[i].price_unlimit);						ip += getFloat(c[i].price_install_unlimit);
					}
					else{						p += getFloat(c[i].price * v);						ip += getFloat(c[i].price_install * v);
					}
				}
			}
			else if(c[i].type == 'select'){				var ind = document.getElementById(i).value;				if(c[i].price[ind]) p += c[i].price[ind];
				if(c[i].price_install[ind]) ip += c[i].price_install[ind];
			}
			else if(c[i].type == 'checkbox'){				var d = document.getElementById(i);				if(d && d.checked){					p += getFloat(c[i].price);
					ip += getFloat(c[i].price_install);
				}			}
		}
		this.sum = isNaN(p) ? 0 : p;
		this.installSum = isNaN(ip) ? 0 : ip;	}

}


