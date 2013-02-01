$(function(){
	check_login();
});

// 检测登录状态
function check_login(){
	var url = baseurl + 'index.php?s=ajax/checklogin';
	$.getJSON(url, function(res){
		var _html = '';
		if(res.status){
			_html += '您好，<a href="' + baseurl + 'index.php?s=user/">' + res['username'] + '</a> | ';
			_html += '<a href="' + baseurl + 'index.php?s=login/logout">退出</a>';
		}else{
			_html += '欢迎您的到来，请<a href="' + baseurl + 'index.php?s=login/">登录</a>';
			_html += '或<a class="orange" href="' + baseurl + 'index.php?s=register/">注册</a>';
		}
		$("#user_info").html(_html);
	});
}

function CheckSearch(search)
{
	if(search.kw.value == "")
	{
		alert("搜索关键字不能为空！");
		search.kw.focus();
		return false;
	}
	search.Submit.disabled = true;
}
function mhHover(cls)
{
	event.srcElement.className = cls;
}

function CopyURL() { try { 
	window.clipboardData.setData("text",location.href); 
	alert("复制成功，请粘贴到你的论坛/QQ/MSN上推荐给你的好友，谢谢。"); 
	} catch(e) {}
}

// 根据书名搜索
function do_search(){
	var keyword	= document.getElementById('skw').value;
	if(keyword == "请输入书名或者作者进行搜索" && keyword == ""){
		document.getElementById('skw').value='请输入书名或者作者进行搜索';
	}else{
		window.location.href = baseurl + 'index.php?s=book/search/keyword/' + keyword;
	}
	return false;
}

// 获取投票信息
function get_vote(book_id){
	$.getJSON(baseurl + 'index.php?s=vote/lists/book_id/' + book_id, function(res){
		if(res == null) return;
		var _html = '', vote;
		for(var i=0;i<res.length;i++){
			vote   = res[i];
			_html += "<a href='" + baseurl + "index.php?s=vote/index/vote_id/" + vote.vote_id + "' target='_blank'>" + vote.subject + "</a>";
		}
		$(".vote_list span").html(_html);
	});
}

// 获取评论列表
function get_review(book_id, page){
	var url = baseurl + 'index.php?s=review/lists/book_id/' + book_id + "/p/" + page;
	$.getJSON(url, function(res){
		var _str = '';
		$(".reader_comment").html(res.contents);
		
		for(var i=1; i<=res.pages; i++){
			var fn = "get_review(" + book_id + "," + i + ")";
			if(page != i){
				_str += '<a href="javascript:;" onclick="' + fn + '">第' + i + '页</a>';
			}else{
				_str += '<a href="javascript:;" class="thePage">第' + i + '页</a>';
			}
		}
		$(".selectLink").html(_str);
	});
}

// 显示回复列表
function show_reply(review_id, is_reply){
	var url = baseurl + 'index.php?s=review/index/id/' + review_id;
	url += is_reply ? '#review' : '';
	document.location.href = url;
}

// 发表评论或回复
function review_post(id, is_reply){
	var subject = $("#review_subject").attr("value");
	var detail = $("#review_detail").attr("value");
	if(subject == "" || detail == ""){
		alert('评论主题和评论内容不能为空！'); return; 
	}
	var url = baseurl + 'index.php?s=review/';
	url += is_reply ? 'reply' : 'post';
	$.getJSON(url, {id:id, subject:subject, detail:detail}, function(res){
		alert(res.info);
		if(res.status){
			$("#review_subject").attr("value", "");
			$("#review_detail").attr("value", "");
			if(is_reply){
				window.location.reload();
			}else{
				get_review(book_id, 1);
			}
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

// 书籍下载
function book_down(book_id, type){
	var url = baseurl + 'index.php?s=ajax/down/id/' + book_id + '/type/' + type;
	document.location.href = url;
}

// 加入收藏夹
function add_to_favor(book_id, chapter_id){
	var url = baseurl + 'index.php?s=user/favor/add/bid/' + book_id + '/cid/' + chapter_id;
	$.getJSON(url, function(res){
		alert(res.info);
	});
}

// 查看收藏夹
function open_favor(){
	document.location.href = baseurl + 'index.php?s=user/&t=favor';
}

// 投推荐票（普通用户）
function add_recommend(book_id){
	var url = baseurl + 'index.php?s=ajax/recommend/id/' + book_id;
	$.getJSON(url, function(res){
		alert(res.info);
	});
}

// 投票检测
function do_vote(){
	var flag = false;
	$("input[name=items]").each(function(){
		if(!flag){ flag = $(this).attr("checked"); }
	});
	if(!flag){ alert("请至少选择 1 项！"); }
	return flag;
}

// 显示投票结果
function show_vote_result(vote_id){
	var url = baseurl + 'index.php?s=vote/result/id/' + vote_id;
	document.location.href = url;
}