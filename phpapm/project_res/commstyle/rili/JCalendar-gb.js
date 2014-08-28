/***************************
 *JCalendar�����ؼ�
 *@author brull
 *@email [email]brull@163.com[/email]
 *@date 2007-4-16
 *@���� 2007-5-27
 *@version 1.0 beta
 ***************************/

/*
 *@param year ���,[��ѡ]
 *@param month �·ݣ�[��ѡ]
 *@param date ���ڣ�[��ѡ]
 *�������Ժ��߼���������ڣ����磺2007-4-27
 */
 
function JCalendar (year,month,date) {
	if($$("calendar"))return;//Ψһʵ��
	var _date = null;
	if(arguments.length == 3) _date = new Date(year,month-1,date);
	else if(arguments.length == 1 && typeof arguments[0] == "string"){
		var tmp = arguments[0].split("-");
		_date = new Date(tmp[0],tmp[1] - 1, tmp[2]);
	}
	//���û�в������ͳ�ʼ��Ϊ��������
	else if(arguments.length == 0) _date = new Date();
	this.year = _date.getFullYear();
	this.month = _date.getMonth() + 1;
	this.date = _date.getDate();
	this.FIRSTYEAR = 1949;
	this.LASTYEAR = 2049;
	JCalendar.cur_year = this.year;
	JCalendar.cur_month = this.month;
	JCalendar.cur_date = this.date;
	JCalendar.cur_obj_id = null;//��Ϊ����ؼ�ʱ���浱ǰ�ı����id
}

/**
 *����������������˵�����ݷ�Χ
 *@first ��һ����ݽ���
 *@last �ڶ�����ݽ���
 *��������˳����Եߵ�
 */
JCalendar.prototype.setYears = function(first,last){
	if(isNaN(first) || isNaN(last)) return;
	this.FIRSTYEAR = Math.min(first,last);
	this.LASTYEAR = Math.max(first,last);
}

/**
 *��HTML�����������ؼ���HTML����
 */
JCalendar.prototype.toString = function(){
	var fday = new Date(this.year,this.month-1,1).getDay();//ÿ�µ�һ���������
	var select_year = new Array();//��������˵�
	var select_month = new Array();//�·������˵�
	//�������ÿ����Ԫ������ݣ�Ԥ�ȶ���һ�ο����飬��Ӧ�������һ�ܿյ�λ�á�[ע���������Ӧ������0]
	var date = new Array(fday > 0 ? fday : 0);
	var dayNum = new Date(this.year,this.month,0).getDate();//ÿ�µ�����
	var html_str = new Array();//���������ؼ���HTML����
	var date_index = 0;//date���������
	var weekDay = ["��","һ","��","��","��","��","��"];
	
	//�����������˵�
	select_year.push("<select id='select_year'  style='display:none' onblur =\"hide(this);show('title_year')\" onchange='JCalendar.update(this.value,JCalendar.cur_month)'>");
	for(var i = this.FIRSTYEAR; i <= this.LASTYEAR; i++){
		if(i == this.year)
			select_year.push("<option value='" + i + "' selected='selected'>" + i +"</option>");
		else
			select_year.push("<option value='" + i + "'>" + i +"</option>");
	}
	select_year.push("</select>");
	
	//����·������˵�
	select_month.push("<select  id='select_month' style='display:none'  onblur =\"hide(this);show('title_month')\" onchange='JCalendar.update(JCalendar.cur_year,this.value)'>");
	for(var i = 1; i <= 12; i++){
		if(i == this.month)
			select_month.push("<option value='" + i + "' selected='selected'>" + i +"��</option>");
		else
			select_month.push("<option value='" + i + "'>" + i +"��</option>");
	}
	select_month.push("</select>");

	//��ʼ��date����
	for(var j = 1; j <= dayNum; j++){
		date.push(j);
	}
	//��ʼ���������ؼ���HTML����
	html_str.push("<table id='calendar'>");
	//�������caption
	html_str.push("<caption>" + "<a href='#'  id='prev_month' title='��һ�·�' onclick=\"JCalendar.update(JCalendar.cur_year,JCalendar.cur_month-1);return false;\"><</a><a href='#' id='title_year' title='���ѡ�����' onclick=\"hide(this);show('select_year');$$('select_year').focus();return false\">" + this.year + "��</a>" + select_year.join("") + "<a href='#' id='title_month' title='���ѡ���·�' onclick=\"hide(this);show('select_month');$$('select_month').focus();return false\">" + this.month + "��</a>" + select_month.join("") + "<a href='#' id='next_month' title='��һ�·�' onclick=\"JCalendar.update(JCalendar.cur_year,JCalendar.cur_month+1);return false;\">></a></caption>");
	//�������ͷ
	html_str.push("<thead><tr>");
	for(var i = 0; i < 7; i++){//�������ͷ
		html_str.push("<td>" + weekDay[i] + "</td>");
	}
	html_str.push("</tr></thead>");
	//��������
	var tmp;
	html_str.push("<tbody>");
	for(var i = 0; i < 6; i++){//������ڣ�6��7��
		html_str.push("<tr>");
		for(var j = 0; j < 7; j++){
			tmp = date[date_index++];
			if(!tmp) tmp = "";
			html_str.push("<td ");
			if(tmp == this.date) html_str.push("id='c_today' ");
			html_str.push("onmouseover='JCalendar.over(this)' onmouseout='JCalendar.out(this)' onclick='JCalendar.click(this)'>" + tmp + "</td>");
		}
		html_str.push("</tr>");
	}
	html_str.push("</tbody></table>");
	return html_str.join("");
}

/**
 *�ر���ʾ�ؼ��죬�������Ӳ��͵�����
 * ʵ��ԭ��Ϊÿ���ؼ���ı��Ԫ���һ��class,����Ϊkeydate,CSS��ʽ��Ҫ�Լ�д������Ӹ�����֮���
 *@param ���ڵ����飬���磺[1,4,6,9]
 */
JCalendar.prototype.setKeyDate = function(){
	var dates = arguments[0];
	var tds = $TN("td",$$("calendar"));
	var reg = null;
	for(var i = 0; i < dates.length; i++){
		reg = new RegExp("\\b" + dates[i] + "\\b");
		for(var j = 7; j < tds.length; j++){//���Ա��ͷ
			if(reg.test(tds[j].innerText)){
				tds[j].className = "keydate";
				break;
			}
		}
	}
}

/**
 *���Խ������ؼ����ĳ���ı����ڵ���ı����ʱ�򣬻���directionָ���ķ��򵯳�����,���Զ�ε������ﶨ����ı���
 *@ param obj_id ��Ҫ��������ı����id
 *@ param direction �������ֵ�������ı���ķ��� [��ѡ] Ĭ��Ϊright
 */
JCalendar.prototype.bind = function(obj_id,direction){
	var obj = $$(obj_id);
	var direction = direction ? direction : "right";
	if(!obj)return;
	if(!$$("calendar_container")){//Ψһ����
		var contain = $DC("div");
		var s = contain.style;
		s.visibility = "hidden";
		s.position = "absolute";
		s.top = "-200px";//����ռ��ҳ��ռ�
		s.zIndex = 65530;
		contain.id = "calendar_container";
		contain.innerHTML = this.toString();
		document.body.appendChild(contain);
		if(isIE){
			var ifm = $DC("iframe");
			var s = ifm.style;
			ifm.frameBorder = 0;
			ifm.height = (contain.clientHeight - 3) + "px";
			s.visibility = "inherit";
			s.filter = "alpha(opacity=0)";
			s.position = "absolute";
			s.top = "-200px";//����ռ��ҳ��ռ�
			//s.left ="-200px;";
			s.width = $$("calendar_container").offsetWidth;
			s.zIndex = -1;
			contain.insertAdjacentElement("afterBegin",ifm);
		}
	}
	//���������¼�
	JCalendar.onupdate = function () {};
	JCalendar.onclick = function (year,month,date){
		var obj = $$(JCalendar.cur_obj_id);
		if(/^\d{1,2}:\d{1,2}(?:\d{1,2})*$/.test(obj.value)) obj.value = year + '-' + month + '-' + date + " " + obj.value;
		else obj.value = obj.value.replace(/^[^\s]*/i,year + '-' + month + '-' + date);
		//���onchange�¼�
		try{obj.onchange();}catch(e){}
		hide("calendar_container");
	}
	//��¼�
	document.attachEvent("onclick",function(){
		if($$("calendar_container").style.visibility="visible")hide("calendar_container");
	});
	obj.attachEvent("onclick",function(e){
		var obj = e.srcElement;
		var dates =obj.value.split(/\s/)[0].split("-");//�ı�����������,�ı������ݿ�����ʱ���������ִ�����:2007-5-26 15:39
		var x,y,left,top;
		var contain = $$("calendar_container");
		var body = isDTD ? document.documentElement : document.body;
		left = body.scrollLeft + e.clientX - e.offsetX;
		top = body.scrollTop + e.clientY - e.offsetY;
		switch(direction){
			case "right" : x = left + obj.offsetWidth; y = top;break;
			case "bottom" : x = left; y = top + obj.offsetHeight;break;
		}
		contain.style.top = y + "px";
		contain.style.left = x + "px";
		//������������
		if(dates.length == 3 && (JCalendar.cur_year != dates[0] || JCalendar.cur_month != dates[1] || JCalendar.cur_date != dates[2]))
			JCalendar.update(dates[0],dates[1],dates[2]);//����ı�����ʱ�������ʱ�䵽�ı����ʱ��
		else if (dates.length != 3){
			var now = new Date();
			JCalendar.update(now.getFullYear(),now.getMonth() + 1,now.getDate());
		}
		if($$("calendar_container").style.visibility="hidden")show("calendar_container");
		e.cancelBubble = true;
		JCalendar.cur_obj_id = obj.id;
	});
	$$("calendar_container").attachEvent("onclick",function(e){e.cancelBubble = true;});
}

/*===========================��̬����=======================================*/
/**
 *������������
 */
JCalendar.update = function(_year,_month,_date){
	date = new Date(_year,_month-1,1);
	var fday = date.getDay();//ÿ�µ�һ���������
	var year = date.getFullYear();
	var month = date.getMonth() + 1;
	var dayNum = new Date(_year,_month,0).getDate();//ÿ�µ�����
	var tds = $TN("td",$$("calendar"));
	var years = $$("select_year").options;
	var months = $$("select_month").options;
	var _date = _date ? _date : JCalendar.cur_date;
	//���µ�ǰ����
	JCalendar.cur_year = year;
	JCalendar.cur_month = month;
	if(_date && _date <= dayNum) JCalendar.cur_date = _date;
	else if(_date > dayNum) JCalendar.cur_date = _date - dayNum;
	$$("title_year").innerText = year + "��";
	$$("title_month").innerText = month + "��";
	//������������˵�ѡ����
	for(var i = years.length - 1; i >= 0; i-- ){
		if(years[i].value == year){
			$$("select_year").selectedIndex = i;
			break;
		}
	}
	//�����·������˵�ѡ����
	for(var i = months.length - 1; i >= 0; i-- ){
		if(months[i].value == month){
			$$("select_month").selectedIndex = i;
			break;
		}
	}
	//�����������,��������ͷ������һ��
	for(var i = 7; i < tds.length; i++) tds[i].innerText = "";
	if(	$$("c_today"))$$("c_today").removeAttribute("id");
	for(var j = 1; j <= dayNum; j++){
		tds[6 + fday + j].innerText = j;
		if(j == JCalendar.cur_date) tds[6 + fday + j].id = "c_today";
	}
	JCalendar.onupdate(year,month,JCalendar.cur_date);
}

JCalendar.click = function(obj){
	var tmp = $$("c_today");
	if(tmp && tmp == obj){
		JCalendar.onclick(JCalendar.cur_year,JCalendar.cur_month,JCalendar.cur_date);
	}
	else if(obj.innerText != ""){
		if(tmp) tmp.removeAttribute("id");
		JCalendar.cur_date = parseInt(obj.innerText);
		obj.id = "c_today";
		JCalendar.onclick(JCalendar.cur_year,JCalendar.cur_month,JCalendar.cur_date);
	}
}

JCalendar.over = function(obj){
	if(obj.innerText != "") obj.className = "over";
}

JCalendar.out = function(obj){
	if(obj.innerText != "") obj.className = "";
}

//��������ʱִ�еĺ��������Ը���Ϊ�Լ���Ҫ����,�ؼ����ݹ����Ĳ���Ϊ��ǰ����
JCalendar.onupdate = function(year,month,date){
	alert("�����Ѹ��ģ���ǰ�������ڣ�" + year + "��" + month + "��" + date + "��");
}

//�������ʱִ�еĺ��������Ը���Ϊ�Լ���Ҫ����,�ؼ����ݹ����Ĳ���Ϊ��ǰ����
JCalendar.onclick = function(year,month,date){
	alert( "��ǰ���������ڣ�" + year + "��" + month + "��" + date + "��");
}