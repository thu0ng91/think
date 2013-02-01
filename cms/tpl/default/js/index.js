$(function(){
	$(".xianhua_fw").click(function(){
		$(".xianhua_fw").removeClass("checked");
		$(this).addClass("checked");
		$("#xhb_bb_id ul").css("display", "none");
		$(".ul_" + $(this).attr("id")).css("display", "block");
	});
});