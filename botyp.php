<?php


header('Content-Type: text/html; charset=UTF-8');


function get_data($rpp,$pageno) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://searchapi.yellowpages.co.th/api.jsp?id=&language=th&hits='.$rpp.'&page='.$pageno.'');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);

	$dataxml = curl_exec ($ch);
	curl_close ($ch);

    $json = json_encode(simplexml_load_string($dataxml));
    $obj = json_decode($json,TRUE);
	return $obj['documents']['document'];

}

function get_count_all() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://searchapi.yellowpages.co.th/api.jsp?id=&language=th&hits=1&page=1');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);

	$dataxml = curl_exec ($ch);
	curl_close ($ch);

    $json = json_encode(simplexml_load_string($dataxml));
    $obj = json_decode($json,TRUE);
	return intval($obj['total']);
}

$rpp=1000; //row per page
$count_all=get_count_all();
echo "count all : ".$count_all ."<br>";
echo "row per page : ".$rpp ."<br>";
echo "page count : ".ceil($count_all/$rpp) ."<br>";


for($pageno=1;$pageno<=2;$pageno++){

	$content = get_data($rpp,$pageno);
	foreach($content as $rows => $rowdata){
		foreach($rowdata as $colname => $value){
			if (is_array($value)){$value="";}
			if ($colname=='businessid'){
				echo $colname."=".$value.",</br>";
			}
		}
	}

}

?>