<?php
class BuyingRequest extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($search_data="all",$starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$hide_class = "";
		$leftPanel=$this->getleftPanel();
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$unitName="un.unit_value_".$this->curr_language." AS unitName";

		$searchKeyword="";

		if(!empty($_POST['keyword'])){
			$searchKeyword=$_POST['keyword'];
			$search_data = $_POST['keyword'];
		}
		if($search_data == 'all' || empty($search_data)){
			$where = 'WHERE b.user_id = '.$_SESSION['userId'].'';
		}else{
			$where = "WHERE  b.user_id = ".$_SESSION['userId']." AND b.request_title LIKE '%".$search_data."%'";
		}

		$getProductData=$this->db->pdoQuery("SELECT b.id as buying_req_id,b.*,$catName,$subcatName,$unitName
											FROM tbl_buying_request AS b
											INNER JOIN tbl_product_category AS c ON c.id=b.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=b.subcat_id 
											INNER JOIN tbl_unit_value AS un ON un.id=b.unit_id
											INNER JOIN tbl_users AS u ON u.id=b.user_id
											".$where." ORDER BY mark_urgent asc LIMIT $starLimit, $endLimit")->results();
		$SearchResultRows=$this->db->pdoQuery("SELECT b.*,$catName,$subcatName,$unitName
											FROM tbl_buying_request AS b
											INNER JOIN tbl_product_category AS c ON c.id=b.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=b.subcat_id 
											INNER JOIN tbl_unit_value AS un ON un.id=b.unit_id
											INNER JOIN tbl_users AS u ON u.id=b.user_id
											".$where."")->affectedrows();
		$hide_class = ($SearchResultRows == 0 ) ? '': "hidden";
		$final_product_loop_content="";

		foreach ($getProductData as $key => $value) {
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/product_loop_data-sd.skd"))->compile();
			$getAttechmentsImg=explode(',',$value['images']);
			$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$getAttechmentsImg[0], PATHINFO_EXTENSION); 
			if($ext=="pdf") { 
					$getAttechmentsImg = /*getImage(DIR_UPD.'pdf.png',"153","148");*/SITE_UPD."no_prodcut_img.png"; 
			}
			else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
				$getAttechmentsImg = /*getImage(DIR_UPD.'doc.png',"153","148");*/ SITE_UPD."no_prodcut_img.png";
			}
			else { 
				$getAttechmentsImg=!empty($getAttechmentsImg[0])?SITE_BUYING_REQUEST_IMG.$getAttechmentsImg[0]:SITE_UPD."no_prodcut_img.png"; 
			}		
			$deleteId=base64_encode(base64_encode($value['id']));
			
			$total_quote_accepted=$this->db->pdoQuery("SELECT * FROM tbl_quotes WHERE buying_request_id =".$value['id'])->affectedrows();
			$start_order_hide = ($total_quote_accepted >= 1) ? '' : 'hide';

			$btn_pay_now="";
			if($value['payment_status']=="n" && ($value['mark_urgent']=="y" || $value['get_extra_quote']=="y" )){
				$btn_pay_now='<a id="'.$value['id'].'" class="btn btn-system place-order get_payment_detail" title='.PAY_NOW.'>'.PAY_NOW.'</a>';
			}

			$btn_view_req="";
			if($total_quote_accepted > 0){
				$reqSlug = $value['request_slug'];
				$btn_view_req='<a href="'.SITE_URL.'view-quote/'.$reqSlug.'" class="btn btn-system btn-view-quote" title='.VIEW_QUOTES.'>'.VIEW_QUOTES.'</a>';
			}


			$urgent_expire = $value['urgent_date'];
			$curdate=date('Y-m-d H:i:s');
			$urgent_data = '';
			$checkIsUrgentRequest = checkIsUrgentRequest($urgent_expire);
			if($checkIsUrgentRequest){
					$urgent_data='<span class="premium-class urgent-class ">'.URGENT.'</span>';
			}
			/*get the no of quotes*/
			$quotes = $this->db->pdoQuery("SELECT * FROM tbl_quotes WHERE buying_request_id = ".$value['buying_req_id'])->affectedRows();
			$viewMoreBtn = "<a href=".SITE_URL."buying-request-detail/".$value['request_slug']." class='text-danger'>".VIEW_MORE_CONTENT."</a>";
			
			$totalUnseenQuote = $this->db->pdoQuery("SELECT * FROM tbl_quotes WHERE buying_request_id =".$value['id']." AND is_seen = 'n' ")->affectedrows();

			$noQuoteLeft = $value['total_quotes'];


			$loop_fields = array(
					"%ID%"=>$value['id'],
					"%PRODUCT_IMG%" => $getAttechmentsImg,
					"%PRO_NM%" => $value['request_title'],
					"%REQUIRED_QUANTITY%" => $value['required_quantity'],
					"%TEMPLATE_TYPE%" => ($value['template_type'] == 1 ? REQUEST_PRICE:($value['template_type'] == 2 ? REQUEST_SAMPLE :REQUEST_QUOTATION)),
					"%DESC%" => myTruncate(filtering($value['req_description']), 100, $viewMoreBtn),
					"%CAT_NAME%" => $value['catName'],
					"%SUBCAT_NAME%" => $value['subCatName'],
					"%START_ORDER_HIDE%" => $start_order_hide,
					"%SLUG%" => $value['request_slug'],
					"%BTN_PAY_NOW%" => $btn_pay_now,
					"%URGENT_TAG%" => $urgent_data,
					"%VIEW_QUOTE%" => $btn_view_req,

					/*"%NO_QUOTES%" => $value['no_ext_quote'],*/
					"%NO_QUOTES%" => $quotes,
					"%LAST_DATE_OF_QUOTE%" => date('dS F Y', strtotime($value['last_date_of_quote'])),
					"%TOTAL_NEW_QUOTE%" => $totalUnseenQuote,
					"%NO_QUOTE_LEFT%" => $noQuoteLeft

			);
			$final_product_loop_content .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);
		}
		$totalRow =$SearchResultRows;
		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);
		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));
		
		$fields = array(
			"%LEFT_PANEL%"=>$leftPanel,
			"%PRODUCTS%"=>$final_product_loop_content,
			"%TOTAL_PAGES%" => $totalPages,
			"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMIT,
			"%CURRENT_PAGE%" => $page,
			"%HIDECLASS%" => $hide_class,
			"%SEARCH_KEYWORD%"=>$searchKeyword
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$final_product_loop_content,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}

}
?>