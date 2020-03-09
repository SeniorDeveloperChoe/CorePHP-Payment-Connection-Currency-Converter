<?php
class viewQuote extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$random=genrateRandom();
		$fields = array(
			"%QUOTE_LOOP%"=> $this->Quote_loop($this->id)
		);

		$this->db->pdoQuery("UPDATE tbl_quotes set is_seen='y' WHERE buying_request_id = ? ",$this->id);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	public function Quote_loop($id) {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/quote_loop-sd.skd"))->compile();
		if(!empty($id)){
			$quote_loop_data = $this->db->select('tbl_quotes', array('*'), array('buying_request_id'=>$id['id']), 'ORDER BY id DESC')->results();
		}
		if(!empty($quote_loop_data)) {		
			foreach($quote_loop_data AS $key => $values) {
				$quote_status = ($values['status'] =='a') ? ORDER_PLACED : (($values['status'] == 'r') ? LBL_REJECTED : PENDING);
				$pending_class = ($values['status'] =='r' || $values['status'] =='a') ? 'hide' : '' ;
				$supplier_detail = $this->db->select('tbl_company', array('company_slug','company_name','logo'), array('user_id'=>$values['user_id']))->result();
				$product_detail = $this->db->select('tbl_products', array('product_slug','product_title','isNegotiable'), array('id'=>$values['product_id']))->result();
				$fields = array(
					"%ID%" => $values['id'],
					"%SUPPLIER_NM%" => $supplier_detail['company_name'],
					"%SLUG%" => $supplier_detail['company_slug'],
					"%SUPPLIER_LOGO%" => SITE_UPD.'supplier_logo/'.$supplier_detail['logo'],
					"%QUOTE_AMOUNT%" => $values['quotation_amount'].' '.CURRENCY_CODE,
					"%CREATED_DATE%" => date('dS F Y', strtotime($values['created_date'])),
					"%ACTIVE%" => $quote_status,
					"%PENDING_CLASS%" => $pending_class,
					"%START_ORDER_BTN%" => ($product_detail['isNegotiable'] == 'n') ? '<button class="btn btn-accept btn-block status_chng_quote" data-status="accept" data-id="'.$values['id'].'" data-req_slug="'.$product_detail['product_slug'].'" data-buyreq="'.$id['id'].'">'.BUY_NOW.'</button>': '<button class="btn btn-accept btn-block status_chng_quote" data-status="accept" data-id="'.$values['id'].'" data-req_slug="'.$product_detail['product_slug'].'" data-buyreq="'.$id['id'].'">'.START_ORDER.'</button>',
					"%PRODUCT_SLUG%" => $product_detail['product_slug'],
					"%PRODUCT_NAME%" => $product_detail['product_title'],
					"%BUYING_REQ_ID%" => $id['id'],
					"%REQ_SLUG%" => $_GET['id']
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_QUOTE."</div></div>";
		}		
		return $final_content;
	}
}

?>