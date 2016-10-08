<?php

$pid=$_REQUEST["pid"];
$template=$_REQUEST["template"];

header('Content-Type: text/html; charset=TIS-620');


function get_data($projectId,$templateType) {
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_URL,"https://process3.gprocurement.go.th/egp2procmainWeb/jsp/procsearch.sch?pid=59075015207&servlet=gojsp&proc_id=ShowHTMLFile&processFlows=Procure");
	curl_setopt($ch, CURLOPT_URL, 'https://process3.gprocurement.go.th/egp2procmainWeb/jsp/procsearch.sch?proc_id=ShowHTMLFile');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,"projectId=".$projectId."&templateType=".$templateType."&temp_Announ=A&ipaddress=58.8.96.91:");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);

	$data = curl_exec ($ch);
	curl_close ($ch);

	return $data;
}


function thainumToarabic($str) {

	$str=str_replace(utf8_to_tis620("๐"), "0", $str);
	$str=str_replace(utf8_to_tis620("๑"), "1", $str);
	$str=str_replace(utf8_to_tis620("๒"), "2", $str);
	$str=str_replace(utf8_to_tis620("๓"), "3", $str);
	$str=str_replace(utf8_to_tis620("๔"), "4", $str);
	$str=str_replace(utf8_to_tis620("๕"), "5", $str);
	$str=str_replace(utf8_to_tis620("๖"), "6", $str);
	$str=str_replace(utf8_to_tis620("๗"), "7", $str);
	$str=str_replace(utf8_to_tis620("๘"), "8", $str);
	$str=str_replace(utf8_to_tis620("๙"), "9", $str);
	return $str;
}

function utf8_to_tis620($string) {
   $str = $string;
   $res = "";
   for ($i = 0; $i < strlen($str); $i++) {
      if (ord($str[$i]) == 224) {
        $unicode = ord($str[$i+2]) & 0x3F;
        $unicode |= (ord($str[$i+1]) & 0x3F) << 6;
        $unicode |= (ord($str[$i]) & 0x0F) << 12;
        $res .= chr($unicode-0x0E00+0xA0);
        $i += 2;
      } else {
        $res .= $str[$i];
      }
   }
   return $res;
}

function clean_for_extraction($string) {
	$res = preg_replace("/(#660066\">)/sm", "", $string);
	$res = preg_replace("/(<)/sm", "", $res);
	$res = strip_tags ($res);
	$res = preg_replace("/&#?[a-z0-9]+;/i","",$res);
   return $res;
}

$returned_content = get_data($pid,$template);
$content=$returned_content;

//cleansing for one pattern of important data
$content=str_replace("rgb(102, 0, 102)", "#660066", $content);
$content=str_replace(" font-family: Angsana New; font-size: 16pt; border-right-color: rgb(0, 0, 0); border-right-width: 1px; border-right-style: solid;", "", $content);
$content=str_replace(" font-family: TH Sarabun New,Cordia New; font-size: 16pt; vertical-align: top;", "", $content);
$content=str_replace("<span style=\"font-size: 21.3333px;\">", "", $content);
$content=str_replace(" font-family: Angsana New; font-size: 18pt;", "", $content);
$content=str_replace(" font-family: Angsana New; font-size: 16pt;", "", $content);
$content=str_replace(" font-size: 16pt; border-right: #000 1px solid", "", $content);
$content=str_replace(" font-size: 16pt; border-right-width: 1px; border-right-style: solid", "", $content);
$content=str_replace(" font-size: 16pt", "", $content);
$content=str_replace(" font-size: 18pt", "", $content);
$content=str_replace("#660066;", "#660066", $content);

$content=thainumToarabic($content);

$pattern = '/(#660066\">).*?(<)/sm';
	preg_match_all($pattern, $content, $matches);

foreach ($matches[0] as &$m) {
    $m=clean_for_extraction($m);
    echo $m;
    echo "<br>";
}

echo "<br>";

preg_match_all('/(announcewin_.*?_)/sm', $content, $templatename_matches);

foreach ($templatename_matches[0] as &$m) {
	$webtemplatename=$m;
}


if(strpos($template,"W")!==false){
	//for pattern like table
	//if((strpos($webtemplatename,'announcewin_15099')!==false) || strpos($webtemplatename,'announcewin_31000')!==false || strpos($webtemplatename,'announcewin_5')!==false){
	if($matches[0][0]==$matches[0][1]){
		$deptname=$matches[0][0];
		$subject=$matches[0][2];
		$docdate=$matches[0][3];
		$numberofcandidate=$matches[0][4];
		$vatincluded=true;
		
		if(strpos($content,'<input checked="checked" name="c2" onclick="this.checked = true;" type="checkbox" value="on" />')!==false){
			$vatincluded=false;	
		}

		$winner_item=array();
		$winner_name=array();
		$winner_price=array();
		$index=5;
		$pos=false;
		while($pos===false){
			$pos = strpos($matches[0][$index+3],utf8_to_tis620("ประกาศ"));
			array_push($winner_item,$matches[0][$index]);
			array_push($winner_name,$matches[0][$index+1]);
			array_push($winner_price,preg_replace('/[,]/', '', $matches[0][$index+2]));
			$index=$index+3;
		}

	}

	//for pattern like document
	//if($webtemplatename=='announcewin_0200400079_'){
	if($matches[0][0]==$matches[0][2]){
		$deptname=$matches[0][0];
		$subject=$matches[0][1];
		$docdate=$matches[0][6];
		$numberofcandidate="";

		$vatincluded=false;
		if(strpos($content,utf8_to_tis620(') รวมภาษี'))!==false){
			$vatincluded=true;	
		}



		$winner_item=array();
		$winner_name=array();
		$winner_price=array();
		$index=7;
		$pos=false;
		while(($pos===false)&&($index<=50)){
			$pos = strpos($matches[0][$index+2],"(");
			if ($pos===false){
				array_push($winner_item,$matches[0][$index]);
				array_push($winner_name,$matches[0][$index+1]);
				array_push($winner_price,preg_replace('/[,]/', '', $matches[0][$index+2]));
			}
			$index=$index+3;

			
		}
	}

	echo "templatename : ".$webtemplatename;
	echo "<br>";
	echo "deptname : ".$deptname;
	echo "<br>";
	echo "subject : ".$subject;
	echo "<br>";
	echo "docdate : ".$docdate;
	echo "<br>";
	echo "numberofcandidate : ".$numberofcandidate;
	echo "<br>";
	echo "vat included : ".$vatincluded;
	echo "<br>";

	echo "pricedata : ";
	echo "<br>";

	for ($i=0;$i<count($winner_item);$i++) {
		echo "[".($i+1)."]";
	    echo $winner_name[$i]. " : ";
	    echo $winner_item[$i]. " : ";
	    echo $winner_price[$i];
	    echo "<br>";
	}

}
//echo strip_tags($returned_content);
echo "<br>";
echo $returned_content;

?>