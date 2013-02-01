
function shareBookChat(){
	window.clipboardData.setData('text',"向您推荐一本好书《也纯也暧昧》，地址：" + window.location)
	alert('网址复制完成，您可以通过QQ、MSN、邮件等方式发送给您的好友，共同分享阅读的快乐！');
}
function refbook2db(){
	url = "http://www.douban.com/recommend/?url=" + encodeURIComponent(location) + "&title=" + encodeURIComponent(document.title);
	window.open(url);
}
function refbook2rr(){
	url = "http://share.renren.com/share/buttonshare.do?link=" + encodeURIComponent(location) + "&title=" + encodeURIComponent(document.title);
	window.open(url);
}
function refbook2kxw(){
	url = "http://www.kaixin001.com/repaste/share.php?rurl=" + encodeURIComponent(location) + "&rcontent=" + encodeURIComponent(location) + "&rtitle=" + encodeURIComponent(document.title);
	window.open(url);
}
function refbook2bsh(){
	url = "http://bai.sohu.com/share/blank/addbutton.do?from=qidian&link=" + encodeURIComponent(location) + "&title=" + encodeURIComponent(document.title);
	window.open(url);
}

var timer;
var speed = 5;
var currentpos = 1;

function setSpeed(num){
	if(num < 1 || num > 10){
	   speed = 5;
	   $("#scrollspeed").val(speed);
	}else{
		speed = num;
	}
}

function stopScroll(){
    clearInterval(timer);
}

function beginScroll(){
	timer = setInterval("scrolling()", 300/speed);
}

function scrolling(){
    currentpos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    window.scroll(0, ++currentpos);
    currentpos2 = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    if(currentpos != currentpos2) clearInterval(timer);
}
function setCookies(cookieName,cookieValue, expirehours){
	var today = new Date();
	var expire = new Date();
	expire.setTime(today.getTime() + 3600000 * 356 * 24);
	document.cookie = cookieName+'='+escape(cookieValue)+ ';expires='+expire.toGMTString();
}

function ReadCookies(cookieName){
	var theCookie = ''+document.cookie;
	var ind = theCookie.indexOf(cookieName);
	if (ind==-1 || cookieName=='') return '';
	var ind1=theCookie.indexOf(';',ind);
	if (ind1==-1) ind1=theCookie.length;
	return unescape(theCookie.substring(ind+cookieName.length+1,ind1));
}

function saveSet(){
	setCookies("bcolor", $("#bcolor").val());
	setCookies("txtcolor", $("#txtcolor").val());
	setCookies("txtsize", $("#txtsize").val());
	setCookies("scrollspeed", $("#scrollspeed").val());
}

function jumpPage(){
	if(event.keyCode == 37 && preview_page != '') document.location.href = preview_page;
	if(event.keyCode == 39 && next_page != '') document.location.href = next_page;
	if(event.keyCode == 13) document.location.href = index_page;
}

function initRead(is_full){
	var value;
	value = ReadCookies("bcolor");
	if(value != ''){
		$("#bcolor").val(value);
		$('body').css('background', value);
	}

	value = ReadCookies("txtcolor");
	if(value != ''){
		$("#txtcolor").val(value);
		$('#txt_contents').css('color',value);
	}

	value = ReadCookies("txtsize");
	if(value != ''){
		$("#txtsize").val(value);
		$('#txt_contents').css('font-size', value);
	}

	value = ReadCookies("scrollspeed");
	if(value != ''){
		$("#scrollspeed").val(value);
		speed = value;
	}

	set_mouse_key(is_full);
}

function CtrlKeyDown(){
	if(event.keyCode == 67 && event.ctrlKey){
		//clipboardData.setData('text','');
		return false;
	}
}

function set_mouse_key(is_full){
	$('body').dblclick(function(){ beginScroll(); });
	$('body').keydown(function(){
		if(is_full === false) { jumpPage(); }
		CtrlKeyDown();
	});
	$('body').mousedown(function(){
		stopScroll();
		if(event.button == 2){ return false; }
	});
	document.onselectstart = function(){ return false; };
}