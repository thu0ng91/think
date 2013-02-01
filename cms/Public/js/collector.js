/* 
 * collector.js
 * 这里有在采集过程中调用的函数
 */

function shortText(strText,intLen){
    var strTemp = strText;
    var lenTemp = intLen;
    if(strTemp.length > lenTemp){
        strTemp = strTemp.substr(0,lenTemp - 1) + "...";
    }
    return strTemp;
}

function js_quote(str){
    var strTest = str;
    var strCheck = "\\+*?[^]$(){}=!|<>/";
    for(var i = 0; i < strCheck.length; i++){

        strTest = strTest.replace(strCheck[i],"\\\\" + strCheck[i]);
    }
    return strTest;
}

function showText(strId){
    var text = $("#text" + strId).html() + "<p><a href='javascript:hideText()'>返回</a></p>";
    $("#showText").html(text);
    $("#showText").fadeIn("slow");
    $(".main").hide();
}
function hideText(){
    $("#showText").html("");
    $("#showText").fadeOut("slow");
    $(".main").show();
}
