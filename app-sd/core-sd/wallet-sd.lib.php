<?php
class wallet extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($starLimit=0, $endLimit=PAGE_DISPLAY_LIMITS, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$getuser=$this->db->pdoQuery("SELECT wallet_amount FROM tbl_users WHERE id=? ",array($_SESSION['userId']))->result();

		$get_transaction_history=$this->db->pdoQuery("SELECT * FROM tbl_payment_history WHERE user_id = ? AND payment_type != ?",array($_SESSION['userId'],'a'))->results();
		$transactions=$hideClass="";

		if(empty($get_transaction_history)){

			$transactions = (new MainTemplater(DIR_TMPL.$this->module."/no_wallet_data-sd.skd"))->compile();
			$hideClass="hide";

		}else{
			foreach ($get_transaction_history as $key => $value) {
				$transaction_content = (new MainTemplater(DIR_TMPL.$this->module."/wal_payment_loop_data-sd.skd"))->compile();

				$payment_type=(($value['payment_type']=="p")?PRODUCT:(($value['payment_type']=="m")?PLAN_PURCHASE:(($value['payment_type']=="r")?REDEEM:(($value['payment_type']=="w")?WALLET_CREDIT:(($value['payment_type']=="a")?LBL_ADMIN_COMMISION:(($value['payment_type']=="b")?BUYING_REQUEST:(($value['payment_type']=="d")?DISPUTE_REFUND:(($value['payment_type']=="eq")?EXTRA_QUOTES:(($value['payment_type']=="ur")?URGENT_RFQ:(($value['payment_type']=="pp")?PRODUCT_PURCHASE:(($value['payment_type']=="sp")?SELL_PRODUCT:(($value['payment_type']=="dc")?DISPUTE_CREDIT:""))))))))))));

				$wallet_status=(($value['wallet_status']=="onHold")?HOLD:(($value['wallet_status']=="inWallet")?PAID:(($value['wallet_status']=="reqRedeem")?REDEEM:(($value['wallet_status']=="redeemed")?REDEEMED:(($value['wallet_status']=="refund")?REFUND:(($value['wallet_status']=="cancel")?CANCEL:(($value['wallet_status']=="adminPaid")?ADMIN_PAID:(($value['wallet_status']=="paid")?PAID:(($value['wallet_status']=="pending")?PENDING:(($value['wallet_status']=="credited")?LBL_CREDITED:(($value['wallet_status']=="dispute-resolved")?LBL_DISPUTE_RESOLVED:(($value['wallet_status']=="in-dispute")?IN_DISPUTE:""))))))))))));

				$get_actual_currency=$this->db->pdoQuery("SELECT currency FROM tbl_currency WHERE id = ?",array($value['currency_id']))->result();			

				// echo $get_actual_currency['currency'];
				// exit();

				$loop_fields = array(
						"%TRANSACTION_ID%"=>!empty($value['transaction_id'])?$value['transaction_id']:"-",
						"%TRANSACTION_TYPE%"=>$payment_type,
						//[20-03-07 by ming lau...] choose the actual currency
						//"%AMOUNT%"=>$value['amount'].' '.CURRENCY_CODE,
						"%AMOUNT%"=>$value['amount'].' '.$get_actual_currency['currency'],
						"%STATUS%"=>$wallet_status,
						"%DATE%"=>date('dS F Y', strtotime($value['payment_date']))
				);
				
				$transactions .= str_replace(array_keys($loop_fields), array_values($loop_fields), $transaction_content);
			}
		}

		// $on_hold_amount=$this->db->pdoQuery("SELECT SUM(amount) AS total_hold_amount FROM tbl_payment_history WHERE user_id = ? AND payment_type != ? AND wallet_status = ?",array($_SESSION['userId'],'a',"onHold"))->result();

		$on_hold_amount=$this->db->pdoQuery("SELECT * FROM tbl_payment_history WHERE user_id = ? AND payment_type != ? AND wallet_status = ?",array($_SESSION['userId'],'a',"onHold"))->results();

		$total_hold_amount = 0;
		foreach ($on_hold_amount as $key => $value) {
			$currency_rate = $this->db->pdoQuery("SELECT * FROM tbl_currency WHERE id = ?",array($value['currency_id']))->result();

			$change_currency_amount = round(floatval($value['amount'] / $currency_rate['rate']), 2);
			$total_hold_amount += $change_currency_amount;
		}
		
		$currency_rate_usd = $this->db->pdoQuery("SELECT * FROM tbl_currency WHERE currency = ?",array("USD"))->result();

		$total_hold_amount_usd = floatval($total_hold_amount * $currency_rate_usd['rate']);
		$total_wallet_amount_usd = floatval($getuser['wallet_amount'] * $currency_rate_usd['rate']);

		$leftPanel=$this->getleftPanel();

		$fields=array("%WALLET_AMOUNT%" => number_format($getuser['wallet_amount'],2).' '.CURRENCY_CODE,
						"%WALLET_AMOUNT_USD%" => number_format($total_wallet_amount_usd,2).' '."USD",
					  "%WALLET_AMOUNT_VALIDATION%" => $getuser['wallet_amount'],
					  "%TRANSACTIONS%"=>$transactions,
					  "%HIDE_CLASS%"=>$hideClass,
					  "%LEFTPANEL%"=>$leftPanel,
					  "%ON_HOLD_AMOUNT%"=>(!empty($total_hold_amount))?number_format($total_hold_amount,2).' '.CURRENCY_CODE:"0".' '.CURRENCY_CODE,
					  "%ON_HOLD_AMOUNT_USD%"=>(!empty($total_hold_amount))?number_format($total_hold_amount_usd,2).' '."USD":"0".' '."USD"
			);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html);
		return $data;
	}
}
?>