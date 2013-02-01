if(typeof(baseurl) === 'undefined'){
	var baseurl = '{baseurl}';
}

$(function(){
	check_login();
});

// 检测登录状态
function check_login(){
	var url = baseurl + 'index.php?s=ajax/checklogin';
	$.getJSON(url, function(res){
		var _html = '';
		if(res.status){
			_html += '您好，<a href="' + baseurl + 'index.php?s=user/" id="global_username">' + res['username'] + '</a>';
			_html += '，<a href="' + baseurl + 'index.php?s=login/logout">退出</a>';
			$(".zlload").html(_html);
		}
	});
}

// 登陆检测
function do_login(){
	if($("#user_name").attr("value") == ""){
		$(".login_info").html("请输入您的账号！");
		$("#user_name").focus(); return false;
	}
	if($("#user_pwd").attr("value") == ""){
		$(".login_info").html("请输入您的密码！");
		$("#user_pwd").focus(); return false;
	}
        if($("#check_code").attr("value") == ""){
		$(".login_info").html("请输入验证码！");
		$("#check_code").focus(); return false;
	}
	return true;
}

// 注册时检测
function do_register(){
	if($("#registe_name").attr("value") == ""){
		$(".login_info").html("请输入您要注册的账号！");
		$("#registe_name").focus(); return false;
	}
	if($("#registe_pwd1").attr("value") == ""){
		$(".login_info").html("请输入密码！");
		$("#registe_pwd1").focus(); return false;
	}
	if($("#registe_pwd2").attr("value") == ""){
		$(".login_info").html("请再输一次密码！");
		$("#registe_pwd2").focus(); return false;
	}
	if($("#registe_pwd1").attr("value") != $("#registe_pwd2").attr("value")){
		$(".login_info").html("两次密码输入不一致，请重新输入！");
		$("#registe_pwd1").focus(); return false;
	}
	if($("#registe_email").attr("value") == ""){
		$(".login_info").html("请输入您的Email！");
		$("#registe_email").focus(); return false;
	}
	if($("#check_code").attr("value") == ""){
		$(".login_info").html("请输入验证码！");
		$("#check_code").focus(); return false;
	}
	return true;
}

 //重载验证码
function freshVerify() {
   $("#verifyImg").attr("src", baseurl + "index.php?s=home/ajax/vcode/" + Math.random());
}

// 根据书名搜索
function do_search(){
	var tid = $("#s_tid").val();
	var keyword	= $("#s_keyword").attr('value');
	if(keyword != ""){
		document.location.href	= baseurl + '?s=book/search/tid/' + tid + '/keyword/' + keyword;
	}else{
		alert("请输入搜索关键词！");
	}
	return false;
}

// 获取评论列表
function get_review(book_id, page){
	var url = baseurl + 'index.php?s=review/lists/book_id/' + book_id + "/p/" + page;
	$.getJSON(url, function(res){
		if(res.status == 0){
			var _html = "<div class='review_close'>" + res.info + "</div>";
			$(".tlqbottom").html(_html);
			return;
		}
		
		var _str = '';
		$("#review_list").html(res.contents);
		
		for(var i=1; i<=res.pages; i++){
			var fn = "get_review(" + book_id + "," + i + ")";
			if(page != i){
				_str += '<a href="javascript:;" onclick="' + fn + '">第' + i + '页</a>';
			}else{
				_str += '<a href="javascript:;" class="thePage">第' + i + '页</a>';
			}
		}
		$(".spzj_right").html(_str);
		
		_str = '本书共有 <font style="font-weight: bold; color: #FF0000">' + res.total + '</font> 条评论';
		$(".spzj_left").html(_str);
	});
}

// 发表评论或回复
function review_post(book_id){
	var subject = $("#sp_title").attr("value");
	var detail = $("#sp_content").attr("value");
	if(subject == "" || detail == ""){
		alert('评论主题和评论内容不能为空！'); return; 
	}
	subject = $("#sp_title_pre").val() + subject;
	
	var url = baseurl + 'index.php?s=review/post';
	$.getJSON(url, {id:book_id, subject:subject, detail:detail, ajax:1}, function(res){
		alert(res.info);
		if(res.status){
			$("#sp_title").attr("value", "");
			$("#sp_content").attr("value", "");
			get_review(book_id, 1);
		}
	});
}

// 快速回复
function quick_reply(review_id, type){
	var subject = "re：" + $("#sptitle_" + review_id).html();
	var detail = $("#sp_content_" + review_id).attr("value");
	if(detail == ""){
		alert('回复内容不能为空！'); return; 
	}
	var url = baseurl + 'index.php?s=review/reply';
	$.getJSON(url, {id:review_id, subject:subject, detail:detail, ajax:1}, function(res){
		if(type == 1){
			alert(res.info);
			if(res.status){
				var num = parseInt($("#sp_num_"+review_id).html(), 10) + 1;
				$("#sp_num_"+review_id).html(num);
			}
			$("#spreply_"+review_id).hide();
		}else{
			show_res(res, true);
		}
	});
}

// 显示快速回复
function show_quick_reply(review_id){
	if(show_login()){
		$('#spreply_' + review_id).show();
	}
}

// 评论锁定提示
function reply_lock(){
	alert('该评论已被锁定，无法回复！');
	return false;
}

// 弹窗快速登录
function quick_login(){
	var user_name = $("#cms_pop_username").attr("value");
	if(user_name == ""){ $("#cms_pop_username").focus(); return false; }
	var user_pwd = $("#cms_pop_password").attr("value");
	if(user_pwd == ""){ $("#cms_pop_password").focus(); return false; }
	var check_code = $("#cms_pop_vcode").attr("value");
	if(check_code == ""){ $("#cms_pop_vcode").focus(); return false; }
	var url = baseurl + '?s=login/login';
	$.getJSON(url, {user_name:user_name, user_pwd: user_pwd, check_code:check_code, ajax:1}, function(res){
		if(res.status){
			window.location.reload();//close_pop();
		}else{
			$("#cms_pop_msg").html(res.info);
		}
	});
}

// 检测并显示登录窗口
function show_login(){
	if($("#global_username").html() == null){
		var _html = '<div class="in">账　号：<input type="text" id="cms_pop_username" /></div>';
		_html += '<div class="in">密　码：<input type="password" id="cms_pop_password" /></div>';
		_html += '<div class="in">验证码：<input type="text" id="cms_pop_vcode" class="vcode" />';
		_html += '<img src="' + baseurl + '?s=login/check_code" onClick="freshVerify()" id="verifyImg" /></div>';
		_html += '<div class="submit" id="cms_pop_msg"></div>';
		_html += '<div class="submit"><input type="button" value="登录" onClick="quick_login();" />';
		_html += '<input type="button" value="取消" onClick="close_pop();" />';
		show_pop('请先登录', _html);
		return false;
	}else{
		return true;
	}
}

// 显示弹出窗口
function show_pop(title, html){
	var _html = '<div id="bookcms_pop_bg"></div>';
	_html += '<div id="bookcms_pop_contents">';
	_html += '<div id="bookcms_pop_title"><span id="bookcms_pop_title_txt">' + title + '</span>';
	_html += '<span id="bookcms_pop_close" onClick="close_pop();">关闭</span></div>';
	_html += '<div id="bookcms_pop_detail">' + html + '</div>';
	_html += '</div>';

	var top = 400 + getScrollTop();
	$(_html).appendTo('body').show();
	$("#bookcms_pop_contents").css('top', top);
}

// 关闭弹出窗口
function close_pop(){
	$("#bookcms_pop_bg").remove();
	$("#bookcms_pop_contents").remove();
}


// 加入收藏夹
function add_to_favor(book_id, chapter_id){
	if(show_login()){
		var url = baseurl + 'index.php?s=user/favor/add/bid/' + book_id + '/cid/' + chapter_id;
		$.getJSON(url, function(res){
			alert(res.info);
		});
	}
}

// 查看收藏夹
function open_favor(){
	if(show_login()){
		document.location.href = baseurl + 'index.php?s=user/favor';
	}
}

// 投推荐票（普通用户）
function add_recommend(book_id){
	if(show_login()){
		var url = baseurl + 'index.php?s=ajax/recommend/id/' + book_id;
		$.getJSON(url, function(res){
			alert(res.info);
		});
	}
}

// 投票检测
function do_vote(){
	var items = [];
	var flag = false;
	$(".vlist input").each(function(){
		if($(this).attr("checked")){
			items.push($(this).attr('value'));
			flag = true;
		}
	});
	if(flag){
		var url = baseurl + "?s=vote/add/vote_id/" + $("#vote_id").attr("value");
		if($(".vlist input").eq(0).attr("type") == "radio"){
			var data = {'items':items, 'ajax':1};
		}else{
			var data = {'items[]':items, 'ajax':1};
		}
		
		$.getJSON(url, data, function(res){
			show_res(res, true);
		});
	}else{
		alert("请至少选择 1 项！");
	}
	return false;
}

// 显示投票结果
function show_vote_result(){
	$(".vote_list span").show();
}

// 显示ajax返回信息
function show_res(res, flag){
	if(res.status == 2){
		if(flag && res.info != ""){ alert(res.info); }
		window.location.reload();
	}else{
		alert(res.info);
	}
}

// 书籍下载
function book_down(book_id, type){
	var url = baseurl + 'index.php?s=ajax/down/id/' + book_id + '/type/' + type;
	document.location.href = url;
}

// 删除回复
function reply_del(id){
	var url = baseurl + '?s=admin/review/reply_del';
	$.getJSON(url, {'id':id, 'ajax':1}, function(res){
		show_res(res);
	});
}

// 显示或屏蔽回复
function reply_show(id, value){
	var url = baseurl + '?s=admin/review/reply_show';
	$.getJSON(url, {'id':id, 'value':value, 'ajax':1}, function(res){
		show_res(res);
	});
}

// 加入收藏
function AddFavorite(sURL, sTitle) {
	try {
		window.external.addFavorite(sURL, sTitle);
	} catch (e) {
		try {
			window.sidebar.addPanel(sTitle, sURL, "");
		} catch (e) {
			alert("加入收藏失败，请使用Ctrl+D进行添加");
		}
	}
}

// 设置首页
function SetHome(obj, vrl) {
	try {
		obj.style.behavior = 'url(#default#homepage)';
		obj.setHomePage(vrl);
	} catch (e) {
		if (window.netscape) {
			try {
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			} catch (e) {
				alert("该浏览器安全设置不允许此操作，请在浏览器选项中手工设置浏览器首页");
			}
			var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
			prefs.setCharPref('browser.startup.homepage', vrl);
		}
	}
}

function getScrollTop(){
	var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0; 
	return scrollTop;
}
