<?php

class Atom_Paynetz_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
			$logs = Mage::getStoreConfig('payment/hdfc_standard');
			$create_log = $logs['Atompg_logging'];

			$standard = Mage::getModel('paynetz/standard');
			$form = new Varien_Data_Form();
			$servermode=Mage::getSingleton('paynetz/config')->getServerMode();
			$url = $standard->getSecurePaynetzUrl($servermode);
			$param = '';
			foreach ($standard->setOrder($this->getOrder())->getStandardCheckoutFormFields() as $field => $value) 
			{
			   if($param != ''){
				$param.= "&".$field."=".$value;
			   }else{
				$param = $field."=".$value;
			   }
			}
			
			$sendurl = $url."?".$param;

			if($create_log!=0){
				Mage::log($sendUrl , null, 'system.log');
			}
			/*
			$ch = curl_init() or die(curl_error()); 
			curl_setopt($ch, CURLOPT_POST,1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS,$param);
			curl_setopt($ch, CURLOPT_PORT, 443); // port 443
			curl_setopt($ch, CURLOPT_URL,$url);// here the request is sent to payment gateway 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0); //create a SSL connection object server-to-server
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0); */

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $sendurl);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_PORT , 80); 
			curl_setopt($ch, CURLOPT_SSLVERSION,3);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$data1 = curl_exec($ch);
			curl_close($ch);		
			
			if($create_log!=0){
				Mage::log($data1 , null, 'system.log');
			}

			$parser = xml_parser_create('');
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); 
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parse_into_struct($parser, trim($data1), $xml_values);
			xml_parser_free($parser);

			$returnArray = array();
			$returnArray['url'] = $xml_values[3]['value'];
			$returnArray['tempTxnId'] = $xml_values[5]['value'];
			$returnArray['token'] = $xml_values[6]['value'];
			
			$xmlObjArray = $returnArray;
			$url = $xmlObjArray['url'];
			$postFields  = "";
			$postFields .= "&ttype=NBfundTransfer";
			$postFields .= "&tempTxnId=".$xmlObjArray['tempTxnId'];
			$postFields .= "&token=".$xmlObjArray['token'];
			$postFields .= "&txnStage=1";
			$r = $url."?".$postFields;
			if($create_log!=0){
				Mage::log($r , null, 'system.log');
			}
			/*
			$response = $data1;
			// If Payment Gateway response has Payment ID & Pay page URL		
		    $i =  strpos($response,":");
			// Merchant MUST map (update) the Payment ID received with the merchant Track Id in his database at this place.
            $paymentId = substr($response, 0, $i);
            $paymentPage = substr( $response, $i + 1);
			// here redirecting the customer browser from ME site to Payment Gateway Page with the Payment ID
			
			$order = $this->getOrder();
			$Amount = round($order->getBaseGrandTotal(),2);
			$TrackId = $order->getRealOrderId();
			//$TrackId = "11220";
			$order_date = $order->getCreatedAt();
			
			//$Famt = explode (".",$Amount);
			//$Famt = $Famt[0];
			
			//$resource = Mage::getSingleton('core/resource');
			//$Connection = $resource->getConnection('core_write');
			//$query = "insert into hdfc_payment_detail(payment_id,amount,trackid,order_date) values ('".$paymentId."','".$Famt."','".$TrackId."','".$order_date."')";	$result = $Connection->query($query);
			
			//$r = $paymentPage . "?PaymentID=" . $paymentId;
			*/
			$html = '<html><body onLoad="Redirect();">';
			$html.= $this->__('You will be redirected to Payment gateway in a few seconds.');
			$html.= $form->toHtml();
			$html.= '<script type="text/javascript">function Redirect(){window.location="'.$r.'";}</script>';
			$html.= '</body></html>';
			return $html;
		
    }
}