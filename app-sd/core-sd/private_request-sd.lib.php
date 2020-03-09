<?php
class PrivateRequest extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($search_data="all",$starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		/*print_r($search_data);exit();*/
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$hide_class = "";
		$leftPanel=$this->getleftPanel();
		$where = ' WHERE  1 = 1 ';
		
		$searchKeyword="";

		if(!empty($_POST['keyword']) && $_POST['keyword'] !='all'){
			$searchKeyword=$_POST['keyword'];
			$where .= " AND cm.user_id = ".$_SESSION['userId']." AND p.product_title LIKE '%".$_POST['keyword']."%'";
			$search_data = $_POST['keyword'];
		}

		if($search_data == 'all' || empty($search_data)){
			$where .= ' AND cm.user_id ='.$_SESSION['userId'];
		}else{
			//$where .= " AND cm.user_id = ".$_SESSION['userId']." AND p.product_title LIKE '%".$_POST['keyword']."%'";
		}

		$getProductData=$this->db->pdoQuery("SELECT p.product_image,p.id AS pro_id,p.product_title,p.product_slug, inq.*, u.first_name,u.last_name FROM tbl_products AS p
		INNER JOIN tbl_inquiry AS inq ON inq.product_id=p.id 
		INNER JOIN tbl_users AS u ON u.id=inq.user_id 
		INNER JOIN tbl_company AS cm ON cm.user_id=p.user_id 

		 $where ORDER BY inq.id desc LIMIT $starLimit, $endLimit")->results();

		$searchResultRows=$this->db->pdoQuery("SELECT p.id AS pro_id,p.product_title, inq.*, u.first_name,u.last_name FROM tbl_products AS p
			INNER JOIN tbl_inquiry AS inq ON inq.product_id=p.id 
			INNER JOIN tbl_users AS u ON u.id=inq.user_id 
			INNER JOIN tbl_company AS cm ON cm.user_id=p.user_id 

			$where")->affectedrows();

		$hide_class = ($searchResultRows == 0 ) ? '': "hidden";
		$final_product_loop_content="";

		foreach ($getProductData as $key => $value) {
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/private_loop_data-sd.skd"))->compile();
			$getProductImgArr =explode(',',$value['product_image']);
			$proImage=!empty($getProductImgArr[0])? SITE_UPD.'product-sd/'.$getProductImgArr[0]:SITE_UPD."no_prodcut_img.png"; 

			$curdate=date('Y-m-d H:i:s');
			/*get the no of quotes*/
			$btn_view_req = '';
			if ($value['is_reply'] == 'n') {
				$btn_view_req = '<a class="btn btn-system place-order btn_reply" href="javascript:void(0)" data-id="'.$value['user_id'].'"  data-inq_id="'.$value['id'].'"  data-pro_id="'.$value['product_id'].'" title="'.BTN_REPLY.'">'.BTN_REPLY.'</a>';
			}
			$viewMoreBtn = "<a href=".SITE_URL."buying-request-detail/".$value['product_slug']." class='text-danger'>".VIEW_MORE_CONTENT."</a>";
			$loop_fields = array(
					"%ID%"=>$value['id'],
					"%PRODUCT_IMG%" => $proImage,
					"%PRO_NM%" => $value['product_title'],
					"%REQUIRED_QUANTITY%" => $value['required_quantity'],
					"%DESC%" => filtering($value['description']),
					"%SLUG%" => $value['product_slug'],
					"%VIEW_QUOTE%" => $btn_view_req,
					"%BUYER_NAME%" => $value['first_name']. ' '.$value['last_name'],

					"%INQUERY_DATE%" => date('dS F Y', strtotime($value['created_date'])),
			);
			$final_product_loop_content .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);
		}
		$totalRow =$searchResultRows;
		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);
		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));
		if (empty($final_product_loop_content)) {
			$final_product_loop_content = '<div class="no-msg">
	             <img src="'.SITE_IMG.'icons/alert.png" alt="">
	             <h2>'.OPPS.'</h2>
	             <p>'.LBL_NO_PRIVATE_REQUEST.'</p>
	            </div>';
		}
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