<?php

class Atom_Paynetz_StandardController extends Mage_Core_Controller_Front_Action
{
    protected $_order;

	public function indexAction(){		
		if($_POST['f_code']=="Ok"){
			$this->successAction();
		}else{
			$this->failureAction();
		}
	}


    public function getDebug ()
    {
        return Mage::getSingleton('paynetz/config')->getDebug();
    }

    public function getOrder ()
    {
        if ($this->_order == null) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }

    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setHdfcStandardQuoteId($session->getQuoteId());

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('paynetz')->__('Customer was redirected to payment')
        );
        $order->save();

        $this->getResponse()
            ->setBody($this->getLayout()
                ->createBlock('paynetz/standard_redirect')
                ->setOrder($order)
                ->toHtml());

        $session->unsQuoteId();
    }

    public function  successAction()
    {
		$BaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'index.php/';

		//$order = Mage::getModel('sales/order')->loadByIncrementId($getUDF1);
		$order = $this->getOrder();
		
		$state = 'processing';
		$status = 'complete';
		$comment = 'Payment Completed Successfully';
		$isCustomerNotified = false;
		$order->setState($state, $status, $comment, $isCustomerNotified);
		//$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
		$order->save();
		$order->sendNewOrderEmail();
		$REDIRECT = $BaseUrl."checkout/onepage/success";
		$html = '<html><body onLoad="Redirect();">';
				$html.= '<script type="text/javascript">function Redirect(){window.location="'.$REDIRECT.'";}</script>';
				$html.= '</body></html>';
				echo $html;
							  

	
		/********************************************************/
		
		//$BaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'index.php/';
		
		//$strResponseIPAdd = getenv('REMOTE_ADDR');
		/* Check whether the IP Address from where response is received is PG IP */
		
			/*
			if($strResponseIPAdd == "221.134.101.174" || $strResponseIPAdd == "221.134.101.169" || $strResponseIPAdd == "198.64.129.10" || $strResponseIPAdd == "198.64.133.213")
			{
				$ResErrorText= isset($_POST['ErrorText']) ? $_POST['ErrorText'] : ''; 	//Error Text/message
				$ResPaymentId = isset($_POST['paymentid']) ? $_POST['paymentid'] : '';	//Payment Id
				$ResTrackID = isset($_POST['trackid']) ? $_POST['trackid'] : '';        //Merchant Track ID
				$ResErrorNo = isset($_POST['Error']) ? $_POST['Error'] : '';            //Error Number
		
				//To collect transaction result
				$ResResult = isset($_POST['result']) ? $_POST['result'] : '';           //Transaction Result
				$ResPosdate = isset($_POST['postdate']) ? $_POST['postdate'] : '';      //Postdate
				//To collect Payment Gateway Transaction ID, this value will be used in dual verification request
				$ResTranId = isset($_POST['tranid']) ? $_POST['tranid'] : '';           //Transaction ID
				$ResAuth = isset($_POST['auth']) ? $_POST['auth'] : '';                 //Auth Code		
				$ResAVR = isset($_POST['avr']) ? $_POST['avr'] : '';                    //TRANSACTION avr					
				$ResRef = isset($_POST['ref']) ? $_POST['ref'] : '';                    //Reference Number also called Seq Number
				//To collect amount from response
				$ResAmount = isset($_POST['amt']) ? $_POST['amt'] : '';                 //Transaction Amount
		
				$Resudf1 = isset($_POST['udf1']) ? $_POST['udf1'] : '';                  //UDF1
				$Resudf2 = isset($_POST['udf2']) ? $_POST['udf2'] : '';                  //UDF2
				$Resudf3 = isset($_POST['udf3']) ? $_POST['udf3'] : '';                  //UDF3
				$Resudf4 = isset($_POST['udf4']) ? $_POST['udf4'] : '';                  //UDF4
				$Resudf5 = isset($_POST['udf5']) ? $_POST['udf5'] : '';                  //UDF5
				
			    $resource = Mage::getSingleton('core/resource');
			    $Connection = $resource->getConnection('core_write');
				$getConnection = $resource->getConnection('core_read');
			    $query = "update hdfc_payment_detail set result='".$ResResult."' where trackid='".$ResTrackID."'";
			    $result = $Connection->query($query);
				foreach($result as $hdfcdetail){
					$DBResult = $hdfcdetail["result"];
				}
				
				//save result in db
				
				if ($ResErrorNo == '')
				{
					//check result is captured or approved i.e. successful
					//if ($ResResult == 'CAPTURED' || $ResResult == 'APPROVED') // replace $ResResult with db variable name
					if ($DBResult == 'CAPTURED' || $DBResult == 'APPROVED')
					{
				   		//result is successful, hence create dual verification request
						if ($ResPaymentId == '' || $ResTrackID == '' || $ResTranId == '' || $ResAuth == '' || $ResRef == '' || $ResAmount == '')
				        {
							
					        $REDIRECT = "REDIRECT=".$BaseUrl."hdfc/standard/failure/?Message=PARMETER VALIDATION FAILED";
					        echo $REDIRECT;
				        }
						else
						{
							//ID given by bank to Merchant (Tranportal ID), same iD that was passed in initial request
							$ReqTranportalId = "<id>90002825</id>";
		
							//Password given by bank to merchant (Tranportal Password), same password that was passed in initial request
							$ReqTranportalPassword = "<password>password1</password>";
		
							// Pass DUAL VERIFICATION action code, always pass "8" for DUAL VERIFICATION
							$INQAction = "<action>8</action>";
		
							//Pass PG Transaction ID for Dual Verification
							$INQTransId  = "<transid>".$ResTranId."</transid>";
					
							//create string for request of input parameters
							$INQRequest=$ReqTranportalId.$ReqTranportalPassword.$INQAction.$INQTransId;
					   
							//DUAL VERIFIACTION URL, this is test environment URL, contact bank for production DUAL Verification URL
							$INQUrl = "https://securepgtest.fssnet.co.in/pgway/servlet/TranPortalXMLServlet";
			   
						   //PHP FUNCTION for connection and posting the request ..starts here
						    $dvreq = curl_init() or die(curl_error()); 
							curl_setopt($dvreq, CURLOPT_POST,1); 
							curl_setopt($dvreq, CURLOPT_POSTFIELDS,$INQRequest); 
							curl_setopt($dvreq, CURLOPT_URL,$INQUrl); 
							curl_setopt($dvreq, CURLOPT_PORT, 443);
							curl_setopt($dvreq, CURLOPT_RETURNTRANSFER, 1); 
							curl_setopt($dvreq, CURLOPT_SSL_VERIFYHOST,0); 
							curl_setopt($dvreq, CURLOPT_SSL_VERIFYPEER,0); 
							$dataret=curl_exec($dvreq) or die(curl_error());
							curl_close($dvreq);
						   //PHP FUNCTION for connection and posting the request ..ends here
			
						   //XML response received for DUAL VERIFICATION.
						   /* 
						   NOTE - MERCHANT MUST LOG THE RESPONSE RECEIVED IN LOGS AS PER BEST PRACTICE
						   */
						   /*
						   $TranInqResponse = $dataret;
						   //print_r $DVresponse;
						   $GEnXMLForm="<xmltg>".$TranInqResponse."</xmltg>";
						   $xmlSTR = simplexml_load_string( $GEnXMLForm,null,true);
					   
						   //Collect DUAL VERIFICATION RESULT
						   $INQCheck = $xmlSTR->result;
						   
						   //If DUAL VERIFICATION RESULT is CAPTURED or APPROVED
			   
							if ($INQCheck == 'CAPTURED' || $INQCheck == 'APPROVED' || $INQCheck == 'SUCCESS')
							{
								  
								  $INQResResult = $xmlSTR->result;//It will give DualInquiry Result 
								  $INQResAmount = $xmlSTR->amt;//It will give Amount
						          $INQResTrackId = $xmlSTR->trackid;//It will give TrackID ENROLLED
						          $INQResPayid = $xmlSTR->payid;//It will give payid
						          $INQResRef = $xmlSTR->ref;//It will give Ref.NO.
						          $INQResTranid = $xmlSTR->tranid;//It will give tranid
								  $getUDF1 = $xmlSTR->udf1;
				
								  $order = Mage::getModel('sales/order')->loadByIncrementId($getUDF1);
								  $state = 'processing';
								  $status = 'complete';
								  $comment = 'Payment Completed Successfully';
								  $isCustomerNotified = false;
								  $order->setState($state, $status, $comment, $isCustomerNotified);
								  //$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
								  $order->save();
								  $order->sendNewOrderEmail();
							  
								  $resource = Mage::getSingleton('core/resource');
								  $readConnection = $resource->getConnection('core_read');
								  $table 	= $resource->getTableName('hdfc_payment_detail');
								  $query 	= 'SELECT * FROM ' . $table . ' WHERE payment_id ='.$INQResPayid;
								  $sku 	= $readConnection->fetchAll($query);
								
								  $dbAmnt = $sku[0]['amount'];
								  $dbAmnt = (int)$dbAmnt;
								
								  $pgAmt = explode (".",$INQResAmount);
								  $pgAmt = $pgAmt[0];
								  $pgAmt = (int)$pgAmt;
	
									if(($sku[0]['payment_id']==$INQResPayid) and ($dbAmnt==$pgAmt) and ($sku[0]['trackid']==$INQResTrackId))
									{
										$transaction_id = $INQResTranid;
										$transaction = Mage::getModel('sales/order_payment_transaction');
										$transaction->setOrderPaymentObject($order->getPayment());
										$transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
										$transaction->setTxnId($transaction_id);
										$transaction->save();
										$REDIRECT = "REDIRECT=".$BaseUrl."checkout/onepage/success";
										
									}
									
									else
									{
										$transaction_id = $INQResTranid;
										$transaction = Mage::getModel('sales/order_payment_transaction');
										$transaction->setOrderPaymentObject($order->getPayment());
										$transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
										$transaction->setTxnId($transaction_id);
										$transaction->save();
										$REDIRECT = "REDIRECT=".$BaseUrl."hdfc/standard/failure/?message=ERROR - Payment Failed In Dual Verification (".$INQCheck.")&ME_TX_ID=".$ResTrackID;
									}
									
							  echo $REDIRECT;  
						     }
				
							else
							{	
								$transaction_id = $INQResTranid;
								$transaction = Mage::getModel('sales/order_payment_transaction');
								$transaction->setOrderPaymentObject($order->getPayment());
								$transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
								$transaction->setTxnId($transaction_id);
								$transaction->save();							
								$REDIRECT = "REDIRECT=".$BaseUrl."hdfc/standard/failure/?message=ERROR - Payment Failed In Dual Verification (".$INQCheck." )&ME_TX_ID=".$ResTrackID;
								echo $REDIRECT;
							}
						
					  }

					}
			
					else
					{
						$order = Mage::getModel('sales/order')->loadByIncrementId($ResTrackID);
						$transaction = Mage::getModel('sales/order_payment_transaction');
						$transaction->setOrderPaymentObject($order->getPayment());
						$transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
						$transaction->setTxnId($transaction_id);
						$transaction->save();
						$REDIRECT = "REDIRECT=".$BaseUrl."hdfc/standard/failure/?message=Payment Failed (".$ResResult." )&ME_TX_ID=".$ResTrackID;
						echo $REDIRECT;
					}

				}
			
				else 
				{
					$order = Mage::getModel('sales/order')->loadByIncrementId($ResTrackID);
					$transaction = Mage::getModel('sales/order_payment_transaction');
					$transaction->setOrderPaymentObject($order->getPayment());
					$transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
					$transaction->setTxnId($transaction_id);
					$transaction->save();
					$REDIRECT = "REDIRECT=".$BaseUrl."hdfc/standard/failure/?message=Error In Processing Payment (".$ResErrorText." )&ME_TX_ID=".$ResTrackID;
					echo $REDIRECT;
				}
			}


			else
			{
				$REDIRECT = $BaseUrl."hdfc/standard/failure/?message=Transaction Denied Due To IP Address Mismatch";
				$html = '<html><body onLoad="Redirect();">';
				$html.= '<script type="text/javascript">function Redirect(){window.location="'.$REDIRECT.'";}</script>';
				$html.= '</body></html>';
				echo $html;
			}*/

    }

    public function failureAction()
    {
		$session = Mage::getSingleton('checkout/session');
		 $session->addError('Failure : Your Transaction couldnot be processed.');
          if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
               // $order->cancel()->save();
            }
        }
        $this->_redirect('checkout/cart');

        $errorMsg = Mage::helper('paynetz')->__('There was an error occurred during paying process.');
        $order = $this->getOrder();

        if (!$order->getId()) {
			$this->_forward('failurerefresh');			
			//$this->norouteAction();
			$session = Mage::getSingleton('checkout/session');
			$session->addError('Failure : Your Transaction could not be processed.');
			$this->_redirect('checkout/cart');
            return;
        }

        if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
            $order->addStatusToHistory($order->getStatus(), $errorMsg);
            $order->cancel();
            $order->save();
        }
		$session = Mage::getSingleton('checkout/session');
		$session->addError('Failure : Your Transaction could not be processed.');
		$this->_redirect('checkout/cart');

        $this->loadLayout();
        $this->renderLayout();
        Mage::getSingleton('checkout/session')->unsLastRealOrderId();
    }
	
	public function failurerefreshAction()
	{
		$errorMsg = Mage::helper('paynetz')->__('There was an error occurred during paying process.');
		$this->loadLayout();
		$this->renderLayout();
	   
	}
	
}
