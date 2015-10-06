<script>
$('input, textarea').focus(function(){hotkeysDisabled=true;}).blur(function(){hotkeysDisabled=false});
$( "#seniorities_back" ).click(function(event) {
	event.stopPropagation();
  $("div.seniorities").html("");
  $( "div.seniorities" ).css("left","150px");
  $( "div.seniorities" ).css("top","250px");
  $( "div.seniorities" ).css("width","600px");
  $( "div.seniorities" ).css("height","310px");
  $("#person").attr("seniority","seniority");
  $("div.seniorities").load("../seniorities/"+$("#person").attr("number"));
   
});
function checkDate(str){
	if(str == "__.__.____")return 0;
	var date = new Date();
	str2=str.split(".");
	var year_delta_top = date.getFullYear() - str2[2];
	var year_delta_bottom = str2[2] - 1900;
	str2=str2[2] +'-'+ str2[1]+'-'+ str2[0];
	if(new Date(str2)=='Invalid Date' || year_delta_top<0 || year_delta_bottom<0)return -1;
	return 1;
}
function dateDifference(date1,date2,nvl){
	var a_month = new Array(31,31,28,31,30,31,30,31,31,30,31,30,31);
	var str1 = date1.split(".");
	var str2 = date2.split(".");
	var date = str2[0] - str1[0];
	if(date>-1){
		var month = str2[1] - str1[1];
	}else{
		date += a_month[str2[1]-1];
		var month = str2[1] - str1[1]-1;
	}
	if(month > -1){
			var year = str2[2] - str1[2];
	}else{
		month +=12; 
		var year = str2[2] - str1[2]-1;
	}
	//alert(nvl);
	if(nvl>1){
	//alert(nvl+" eeeee ");
		date *= nvl;
		var month_plus = Math.ceil(date/30)-1;
		date = date - month_plus*30;
		
		month = month*nvl + month_plus;
		var year_plus = Math.ceil(month/12)-1;
		month = month - year_plus*12;
		year = year*nvl + year_plus;
		
	}
	$("#sen_cal_y").html(year);
	$("#sen_cal_m").html(month);
	$("#sen_cal_d").html(date);
	$("#sen_cal_add").css("display","");
	
//	alert(year + " - " + month + "-" + date );

}
$("#sen_cal_date1,#sen_cal_date2").blur(function(){
	var check_date = checkDate($(this).val());
	if(check_date<0)$(this).addClass("wrong_date");
	else $(this).removeClass("wrong_date");
});
function getNVL(){
	var nvl = 1;
	var id = 0;
	if($("#seniorities_calendar").parent().attr("id") == "new_lp_container"){
		nvl = $("#lp_select > font").attr("nvl");
		id = $("#lp_select > font").attr("id");
	} 
	return [id,nvl];
}
$("#sen_cal_name").focus(function(){
	var nvl= getNVL();
	if(checkDate($("#sen_cal_date1").val())>0 && checkDate($("#sen_cal_date2").val())>0){
		dateDifference($("#sen_cal_date1").val(), $("#sen_cal_date2").val(), nvl[1]);
	}else alert("неверная дата");
});
$("#add_seniority_lp").click(function(event){
	event.stopPropagation();
	$("#lp_select").css("display","");
	$("#lp_select").load("../loadServicePeriodSelect?parent=0");
	
    $("#seniorities_calendar").appendTo($("#new_lp_container"));
	clearSenContainer();	
});
$("#add_seniority_calendar").click(function(event) {
	event.stopPropagation();
    $("#seniorities_calendar").appendTo($("#new_calendar_container"));
	$(".lp").css("display","none");
	clearSenContainer();
});
$("#add_seniority_study").click(function(event) {
	event.stopPropagation();
    $("#seniorities_calendar").appendTo($("#new_study_container"));
	$(".lp").css("display","none");
	clearSenContainer();
});

$("#sen_cal_del").click(function(event) {
	event.stopPropagation();
   $("#seniorities_calendar").appendTo($("#seniorities_hide_div"));
   $("#seniorities_calendar").css("display","none");
});

$("#sen_cal_add").click(function(){
	var _nvl = getNVL();
	var a = [];
	//if($("#lp_select > font").attr("nvl") )
    $.ajax({
		url : '../servicePeriodSave',
		async : true,
		type : 'GET',
		data : {
			  	date1:$("#sen_cal_date1").val(),
			  	date2:$("#sen_cal_date2").val(),
			  	name:$("#sen_cal_name").val(),
			  	nvl:_nvl[0],
			  	nvl2:_nvl[1],
				id:$("#person").attr("number"),
				klg:$($("#seniorities_calendar").parent()).prop("id"),
				type:"save",
		    },
		processData : true,
		contentType : 'application/x-www-form-urlencoded',
		dataType : 'json',
        success: function (data) { 
			if(data.status == "ok"){
				loadServicePeriods($($("#seniorities_calendar").parent()).prop("id"));
				loadPeriodSumma($($("#seniorities_calendar").parent()).prop("id"));
				$("#seniorities_calendar").appendTo($("#seniorities_hide_div"));
				$("#seniorities_calendar").css("display","none");
				$("tr").removeClass("error_date");
			}else if(data.status == "error"){
				alert("Ошибка сохранения периода службы.");
			}else if(data.status == "cross_date"){
				$("tr").removeClass("error_date");
				$("#"+data.id).addClass("error_date");
				alert("Период пересекается с уже имеющимися.");
			}
        }
    });        
	
});
function loadPeriodSumma(klg){
	if(klg.indexOf("new_")>-1)klg = klg.substr(4);
	klg = klg.substr(0,-10);
	alert(klg);
	
}
function loadServicePeriods(klg){
	if(klg.indexOf("new_")>-1)klg = klg.substr(4);
	$("#"+klg).html("");
	$("#"+klg).load("../servicePeriodLoad?person="+$("#person").attr("number")+"&klg="+klg);
	
}
$(document).ready(function(){
  $("#get_in").mask("99.99.9999");
  $("#sen_cal_date1").mask("99.99.9999");
  $("#sen_cal_date2").mask("99.99.9999");
  $("#sen_cal_date2").mask("99.99.9999");
  
  $("#caldr").html($("#calendar_y").val());
  loadServicePeriods("calendar_container");
  loadServicePeriods("study_container");
  loadServicePeriods("lp_container");
});

</script>
<style>
.seniority_count_content td{
	vertical-align:top;
	margin:2px;
	xborder:1px solid red;
	padding-top:10px;
}
input.wrong_date{
	background-color:red;
	color:#fff;
}
#seniorities_calendar td{
	padding:0px;
	margin:0px;
}
.error_date td.date{
	color:white;
	background-color:red;
}
</style>
<?php
$out = array();
    $content = Seniorities::model()->findAllByAttributes(array("person"=>$model->id));
	//var_dump($content);
	if(isset($content))
    foreach($content as $one){
	    if(!isset($out[$one->class]))$out[$one->class] = array();
	    $out[$one->class][$one->type] = $one->value;
    }
	$seniorities_classes = Seniorities::getSenioritiesClasses();
	$a_vl = array();
	foreach($seniorities_classes as $one){
		if(!isset($out[$one]))$a_vl[$one] = "0 0 0";
		else $a_vl[$one] = (isset($out[$one]["y"])? $out[$one]["y"] : "0")."&nbsp;".(isset($out[$one]["m"])? $out[$one]["m"] : "0")."&nbsp;".(isset($out[$one]["d"])? $out[$one]["d"] : "0");
	}
echo Ranks::getName($model->rank)." "; 
echo "<b>".$model->second_name." ".$model->first_name." ".$model->third_name."</b>";
echo "<br />Дата рождения <b>".$model->birth_date."</b> Дата увольнения <b>".$model->dismiss_date."</b> Возраст:<b>".$model->age."</b>";
echo "<br /> Поступило: ";
echo CHtml::textField("get_in", "", array("size"=>9,"id"=>"get_in","placeholder"=>"__.__.____") );
echo "&nbsp;Подразделение:<b>".Comms::getName($model->cases3->comm)."</b>.";
$summa1 = ServicePeriods::PeriodsSumma($model->id,1);
$summa2 = ServicePeriods::PeriodsSumma($model->id,2);
$summa3 = ServicePeriods::PeriodsSumma($model->id,3);
//var_dump($summa1);

?>		
<br /><br />
<table class='seniority_count_content'><tr>
<td width='50%' style='background-color:#56a4b5'>
<table width='100%' style=''>
<tr><td style='background-color:#5665e5;'>Календарная служба <b id='add_seniority_calendar' style='cursor:pointer;'>+</b></td></tr>
<tr><td style='background-color:#5665e5;' id='new_calendar_container'></td></tr>
<tr><td style='background-color:#5665e5;height:100px;' id='calendar_container'></td></tr>
<tr><td style='background-color:#5665e5;color:#ddd;' id='calendar_summa'>Срок, зачисляемый в в/лет -
<?php echo $a_vl["calendar"];?></td></tr>
<tr><td  style='background-color:#d345a4;'>Учеба <b id='add_seniority_study' style='cursor:pointer;'>+</b></td></tr>
<tr><td style='background-color:#d345a4;' id='new_study_container'></td></tr>
<tr><td style='background-color:#d345a4;height:100px;' id='study_container'></td></tr>
<tr><td  style='background-color:#d345a4;color:#ddd;'  id='study_summa'>Срок, зачисляемый в в/лет -<?php echo $a_vl["study"];?></td></tr>
</table>
</td>
<td width='50%'  style='background-color:#56d4e5'> 
<table width='100%' style='padding:4px;margin:4px;'>
<tr><td  style='background-color:#34a412;'>Льготные периоды <b id='add_seniority_lp' style='cursor:pointer;'>+</b></td></tr>
<tr><td style='background-color:#34a412;' id='new_lp_container'></td></tr>
<tr><td style='background-color:#34a412;height:100px;' id='lp_container'></td></tr>
<tr><td  style='background-color:#34a412;color:#ddd;' id='lp_summa'>Срок, зачисляемый в в/лет -<?php echo $a_vl["privilege"];?></td></tr>
<tr><td></td></tr>
</table>

</td>
</tr>
</table>
<br />
<font id='seniorities_back' class='button'>Назад</font>
<div id='seniorities_hide_div'>
<table id='seniorities_calendar' style='display:none;border-collapse:collapse;padding:0px;'>
<thead>
<th class='lp'>ЛП</th>
<th>Период с</th>
<th>Период по</th>
<th>ЛгРайПерД</th>
<th>Лет</th>
<th>Мес</th>
<th>Дн</th>
</thead>
<tr>

<td id='lp_select' koef='1' class='lp'></td>
<td><input type='text' id='sen_cal_date1' name='sen_cal_date1' placeholder="__.__.____" size='9' style='font-size:14px'></td>
<td><input type='text' id='sen_cal_date2' name='sen_cal_date2' placeholder="__.__.____" size='9' style='font-size:14px'></td>
<td><input type='text' id='sen_cal_name' name='sen_cal_name' size='8' style='font-size:14px'></td>
<td id='sen_cal_y'></td>
<td id='sen_cal_m'></td>
<td id='sen_cal_d'></td>
<td id='sen_cal_add' style='display:none;cursor:pointer'>></td>
<td id='sen_cal_del' style='cursor:pointer;'>X</td>


</tr>
</table>



			
			
