<?php
class TradeAlert extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent($starLimit=0, $endLimit=PAGE_DISPLAY_LIMITS, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		$leftPanel=$this->getleftPanel();

		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";

		$getTradeAlertData=$this->db->pdoQuery("SELECT t.*,$subcatName,p.product_title,p.id AS Product_Id,c.company_name FROM tbl_tradealert_subscriber AS t
												INNER JOIN tbl_product_subcategory AS s ON s.id=t.sub_cat_id
												INNER JOIN tbl_products AS p ON p.id=t.product_id
												INNER JOIN tbl_company AS c ON c.user_id=p.user_id
												WHERE t.user_id = ?
												LIMIT $starLimit, $endLimit
												",array($_SESSION['userId']))->results();

		$SearchResultRows=$this->db->pdoQuery("SELECT t.*,$subcatName,p.product_title,p.id AS Product_Id,c.company_name FROM tbl_tradealert_subscriber AS t
												INNER JOIN tbl_product_subcategory AS s ON s.id=t.sub_cat_id
												INNER JOIN tbl_products AS p ON p.id=t.product_id
												INNER JOIN tbl_company AS c ON c.user_id=p.user_id
												WHERE t.user_id = ?
												",array($_SESSION['userId']))->affectedrows();

		$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }

		
		$tradeAlert_loop_data="";

		if(empty($getTradeAlertData)){
			
			$html = (new MainTemplater(DIR_TMPL.$this->module."/no_alerts-sd.skd"))->compile();

		}else{

			foreach ($getTradeAlertData as $key => $value) {

				$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/trade_alert_loop_data-sd.skd"))->compile();

				$loop_fields = array(
									"%SUBCAT_NAME%"=>$value['subCatName'],
									"%PRODUCT_TITLE%"=>$value['product_title'],
									"%COMPANY_NAME%"=>$value['company_name'],
									"%CREATED_DATE%"=>date('dS F Y', strtotime($value['created_date'])),
									"%ALERT_ID%"=>base64_encode(base64_encode($value['id']))
									);
				$tradeAlert_loop_data .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);

			}

		}
		
		$totalRow =$SearchResultRows;

		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMITS);

		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMITS || $totalRow < PAGE_DISPLAY_LIMITS) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));


		$fields=array("%LEFT_PANEL%"=>$leftPanel,
					 "%TRADEALERT_LOOP_DATA%"=>$tradeAlert_loop_data,
					 "%TOTAL_PAGES%" => $totalPages,
					 "%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMITS,
					 "%CURRENT_PAGE%" => $page,
					 "%HIDECLASS%" => $hide_class
					);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$tradeAlert_loop_data);
		return $data;
	}

}
?>