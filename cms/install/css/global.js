function step1(){
	if(!$('input[name=accept]').attr('checked')){
		alert('请先阅读并接受许可协议');
		return false;
	}else{
		return true;
	}
}

function step2(){
	if($("input[name=envi]").attr("value") != 1){
		alert('非常抱歉！您的服务器不能很好的兼容BookCms，请检查以下表格中红色字的提示并检查修复相应的项目(注：环境检查中的项目不兼容可能需要您对服务器相应软件进行调整，目录和文件检查中的项目只需要您添加一下对应项目的可写[可修改]权限即可)。若您已检查并修复相应的项目，请点击“重新检查”按钮。');
		return false;
	}
	return true;
}

function check_envi(){
	var item, extra, flag = 1;
	$.ajaxSetup({ cache: false });
	$.getJSON('ajax.php', function(res){
		$("#environment li:gt(0)").remove();
		for(var i in res.envi){
			item = res.envi[i];
			if(item.nv < item.rv){ flag = 0; extra = 'failed'; }else{ extra = 'pass'; }
			_html  = '<li><span>' + item.name + '</span><span>&gt;= ' + item.rv + '</span>';
			_html += '<span class="ico ' + extra + '">' + item.nv + '</span></li>';
			$("#environment").append(_html);
		}
		$("#writable li:gt(0)").remove();
		for(var i in res.write){
			item = res.write[i];
			if(item.nv != ''){ flag = 0; extra = 'failed'; }else{ extra = 'pass'; }
			_html  = '<li><span>' + item.name + '</span><span>' + item.rv + '</span>';
			_html += '<span class="ico ' + extra + '">' + item.nv + item.rv + '</span></li>';
			$("#writable").append(_html);
		}
		$("input[name=envi]").attr("value", flag);
	});
}


/* step 4 */
function step4(){
	if($("#result").attr("value") == "1"){
		$("#message").html('恭喜您！BookCms安装成功！感谢您使用BookCms，请在下面选择您的去向');
		$('#go_back').hide();
	}else{
		$('#go_home').hide();
		$('#go_admin').hide();
	}
}
