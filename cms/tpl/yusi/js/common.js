$(document).ready(function()
{	
	$(".bookshelf").click(
	function(){
		var opurl;
		var opname;
		opurl=$(this).attr("href");
		opname=$(this).html();
		if(opurl!='')
			$.getJSON(opurl+'&ajax='+(Math.random()*1000+1),function(json){
									alert(json.message);
								})
		return false
		})
	$("#bookshelf").click(
	function(){
		var opurl;
		var opname;
		opurl=$(this).attr("href");
		opname=$(this).html();
		if(opurl!='')
			$.getJSON(opurl+'&ajax='+(Math.random()*1000+1),function(json){
									alert(json.message);
								})
		return false
		})
})
function loadnewcomment(newopurl){
		if(newopurl!=''){
				$.ajax({  
						url:newopurl, 
			           cache: false,
			           success: function(message) 
			            {	  //alert('msg'+message);
			                //return message;
			                $("#tabcontent0").html("<li>"+message+"</li>");        
			            }
			        });			        
				}			
	
}

function xdyqw_GetCookie(sName)
{
  var aCookie = document.cookie.split("; ");
  for (var i=0; i < aCookie.length; i++){
    var aCrumb = aCookie[i].split("=");
    if (encodeURIComponent(sName) == aCrumb[0])
      return decodeURIComponent(aCrumb[1]);
  }
  return null;
}
function addcookiefav(cid,aid){
	$.get("/cookiebookshelf.php?action=add&cid="+cid+"&aid="+aid, function(data){
  		//alert("Data Loaded: " + data);
	}); 
}
//check pm
/*
function checkpmnum(){//取值方法,参数i表示页数 
  $.get("/islogin.php?action=checkpm",updatepmnum); 
} 
function updatepmnum(data){
	$("#updatepmnum").html('(<img src=/images/pmmsg.gif width=16 height=16><font color=red>'+data+'</font>)'); 
}
function resetpmnum(){
	$("#updatepmnum").html('');
}
*/
