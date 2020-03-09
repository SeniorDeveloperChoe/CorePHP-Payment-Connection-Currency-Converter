<?php
class PaymentHistory extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($starLimit=0, $endLimit=PAGE_DISPLAY_LIMITS, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();
		$getPaymentHistoryData=$this->db->pdoQuery("SELECT * FROM tbl_payment_history WHERE user_id=? LIMIT $starLimit, $endLimit",array($_SESSION['userId']))->results();	
		$SearchResultRows=$this->db->pdoQuery("SELECT * FROM tbl_payment_history WHERE user_id=?",array($_SESSION['userId']))->affectedrows();

		$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }

		$payment_histry_loop_data="";
		if(empty($getPaymentHistoryData)){			
			$html = (new MainTemplater(DIR_TMPL.$this->module."/no_payment_history-sd.skd"))->compile();
		}else{
			foreach ($getPaymentHistoryData as $key => $value) {
				$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/payment_loop_data-sd.skd"))->compile();

			$payment_type=(($value['payment_type']=="p")?"Product":(($value['payment_type']=="m")?"Membership":(($value['payment_type']=="r")?"Redeem":(($value['payment_type']=="w")?"Wallet":(($value['payment_type']=="a")?"Admin Commision":(($value['payment_type']=="b")?"Buying Request":""))))));

			$wallet_status=(($value['wallet_status']=="onHold")?"Hold":(($value['wallet_status']=="inWallet")?"In Wallet":(($value['wallet_status']=="reqRedeem")?"Redeem":(($value['wallet_status']=="redeemed")?"Redeemed":(($value['wallet_status']=="refund")?"Refund":(($value['wallet_status']=="cancel")?"Cancel":(($value['wallet_status']=="adminPaid")?"Admin Paid":(($value['wallet_status']=="paid")?"Paid":(($value['wallet_status']=="pending")?"Pending":"")))))))));			


				$loop_fields = array("%TITLE%"=>($value['payment_type'] == 'r') ? REDEEM : (($value['payment_type'] == 'p') ? PRODUCT :(($value['payment_type'] == 'm') ? MEMBERSHIP_PLAN :WALLET)),
									"%STATUS%"=>($value['status'] == 'r') ? LBL_REJECTED : (($value['status'] == 'p')? PENDING : LBL_COMPLETED),
									"%TRANSACTION_ID%"=>$value['transaction_id'],
									"%AMOUNT%"=>$value['amount'].' '.CURRENCY_CODE,
									"%CREATED_DATE%"=>date('dS F Y', strtotime($value['payment_date'])));
				$payment_histry_loop_data .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);
			}
		}



		

		$totalRow =$SearchResultRows;

		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMITS);

		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMITS || $totalRow < PAGE_DISPLAY_LIMITS) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));



		$fields=array("%LEFT_PANEL%"=>$leftPanel,
					  "%PAYMENTHISTORY_LOOP%"=>$payment_histry_loop_data,
					  "%TOTAL_PAGES%" => $totalPages,
					  "%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMITS,
					  "%CURRENT_PAGE%" => $page,
					  "%HIDECLASS%" => $hide_class
					  );
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$payment_histry_loop_data);
		return $data;
	}
}
?>