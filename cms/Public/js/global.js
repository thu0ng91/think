// 添加风格
function theme_add(){
	var name  = $("#style_name").attr("value");
	var value = $("#style_value").attr("value");
	if(name == "" || value == ""){
		alert("风格名称和目录名称不能为空！"); return ;
	}
	var url = "?s=admin/style/add/";
	$.getJSON(url, {name:name, value:value}, function(res){
		show_res(res);
	});
}

// 显示风格修改
function show_edit(id, name, value){
	$("#edit_id").attr("value", id);
	$("#edit_name").attr("value", name);
	$("#edit_value").attr("value", value);
	$("#theme_edit").show();
}

// 编辑风格
function theme_edit(){
	var id  = $("#edit_id").attr("value");
	var name = $("#edit_name").attr("value");
	var value = $("#edit_value").attr("value");
	if(name == "" || value == ""){
		alert("风格名称和目录名称不能为空！"); return ;
	}
	var url = "?s=admin/style/edit";
	$.getJSON(url, {id:id, name:name, value:value}, function(res){
		show_res(res);
	});
}

// 删除风格
function theme_del(id){
	if(confirm('同时会删除该风格的所有模板和文件，确认删除吗？')){
		var url = "?s=admin/style/delete/id/" + id;
		$.getJSON(url, function(res){
			show_res(res);
		});
	}
}

// 设置默认风格
function theme_set(id){
	var url = "?s=admin/style/set/id/" + id;
	$.getJSON(url, function(res){
		show_res(res);
	});
}

// 显示ajax返回信息
function show_res(res){
	if(res.status == 2){
		window.location.reload();
	}else{
		alert(res.info);
	}
}

// 复选框全选
function select_all(iname){
	var id = "input[name='" + iname + "']";
	$("input.select_all").click(function(){
		$(id).attr("checked", $(this).attr("checked"));
	});
	
	$(".do_select a").click(function(){
		var data = if_checked(iname);
		if(data != false){
			ajax_submit($(this), {"id[]":data});
		}else{
			alert("请至少选择一项！");
		}
		return false;
	});
	
	$(".td_flag a").click(function(){
		ajax_submit($(this), {});
		return false;
	});
}

// .td_flag 内 select 处理
function do_select(id){
	var obj = $("#" + id + "_go");
	var val = $("#" + id).val();
	var href = val == "0" ? "#" : (obj.attr("res") + val);
	obj.attr("href", href);
}

// ajax提交，根据 $('a') 的href值，res值进行判断是否需要确认，进行提交
function ajax_submit(obj, data){
	var url = obj.attr("href");
	if(url == "#"){ return false; }
	
	var flag = obj.attr("res") == "confirm" ? true : false;
	if(flag && !confirm('请确认此操作！')){ return false; }
	
	data.ajax = 1;
	$.getJSON(url, data, function(res){
		show_res(res);
	});
}

// 复选框是否有选择，有则返回所选值数组，无则返回false
function if_checked(iname){
	var data = [];
	$("input[name='" + iname + "']").each(function(){
		if($(this).attr("checked")){
			data.push($(this).attr("value"));
		}
	});
	return data.length == 0 ? false : data;
}