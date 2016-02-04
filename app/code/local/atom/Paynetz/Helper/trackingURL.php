<?php
function transactionTracking($trasactionURL,$merchantId,$merchanttxnId,$amount,$transactionDate){
	$url = $trasactionURL;
	$getFields  = "";
	$getFields .= "&merchantid=".$merchantId;
	$getFields .= "&merchanttxnid=".$merchanttxnId;
	$getFields .= "&amt=".$amount;
	$getFields .= "&tdate=".$transactionDate;
	$r = $url."?".$getFields;	
	
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_URL, $r);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	$data1 = curl_exec($ch);	
	curl_close($ch);		
	
	$parser = xml_parser_create('');
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); 
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($data1), $xml_values);
	xml_parser_free($parser);
	return $xml_values;
}
?>