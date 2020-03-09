<?php
class AddProduct extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent($search_data="all",$starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		$leftPanel=$this->getleftPanel();

		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$unitName="un.unit_value_".$this->curr_language." AS unitName";

		$searchKeyword="";

		if(!empty($_GET['keywords'])){
			$searchKeyword=$_GET['keywords'];
			$where = "WHERE  p.user_id = ".$_SESSION['userId']." AND p.product_title LIKE '%".$_GET['keywords']."%'";
		}
		if($search_data == 'all' || empty($search_data)){
			$where = 'WHERE p.user_id = '.$_SESSION['userId'].'';
		}else{
			$where = "WHERE  p.user_id = ".$_SESSION['userId']." AND p.product_title LIKE '%".$search_data."%'";
		}

		$getProductData=$this->db->pdoQuery("SELECT p.*,$catName,$subcatName,$unitName
											FROM tbl_products AS p
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=p.sub_cat_id 
											INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id
											".$where."
											ORDER BY p.id DESC
											LIMIT $starLimit, $endLimit	")->results();

		$SearchResultRows=$this->db->pdoQuery("SELECT p.*,$catName,$subcatName,$unitName
											FROM tbl_products AS p
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=p.sub_cat_id 
											INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id
											".$where."")->affectedrows();


		$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }
		$final_product_loop_content="";
		

		foreach ($getProductData as $key => $value) {
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/product_loop_data-sd.skd"))->compile();

			$chkFavId=$this->db->select("tbl_favorite_product",array("id"),array("item_id"=>$value['id']))->result();
			$favCalss="";
			if(empty($chkFavId)){
				$favCalss="fa fa-heart-o";
			}else{
				$favCalss="fa fa-heart";
			}

			$fob=$status=$tags="";
			$premium = ($value['mark_premium'] == 'y') ? '':'hide';
			$prdImage = "";
			
			$getProductImg=explode(',',$value['product_image']);

			/*multiple product images*/
			foreach ($getProductImg as $key => $Imgvalue) {
				$prdImage .='<a href="'.SITE_URL.'product-detail/'.$value['product_slug'].'" target="">
                  <img src="'.SITE_UPD.'product-sd/'.$Imgvalue.'" alt=""/>
                  <span class="premium-class '.$premium.'">
                        '.PREMIUM.'
                  </span>
               </a>';
			}
			$getProductImg=!empty($getProductImg[0])?SITE_PRODUCT_IMG.$getProductImg[0]:SITE_UPD."no_prodcut_img.png";
			$selected_delivery_terms = '-';
			if (!empty($value['delivery_terms'])) {
				$deliveryTermsData = $this->db->pdoQuery("SELECT * FROM tbl_delivery_terms WHERE id in (".$value['delivery_terms'].") ",[])->results();
				$deliveryTermsData = array_column($deliveryTermsData, 'delivery_terms');


				$selected_delivery_terms = !empty($deliveryTermsData) ? implode(',',$deliveryTermsData) : '';

			}


			$userDeactiveStatusSelection = $userActiveStatusSelection = '';
			if($value['deactive_by_user']=="y"){
				$userDeactiveStatusSelection = 'selected';
			}else{
				$userActiveStatusSelection = 'selected';
			}
		
			
			if(!empty($value['product_tags'])){
				$tag=explode(',',$value['product_tags']);
				$tags.='<div class="pro-tags">';
				foreach ($tag as $key => $tagValue) {
						if (!empty($tagValue)) {
							$tags.='<label class="label label-default">
										<label>'.$tagValue.'</label>
										<span id="tag'.$key.'" class="delete-tag" data-id ="'.$key.'" data-p-id="'.$value['id'].'" ><i class="fa fa-times" aria-hidden="true"></i>
</span>
									</label>';

						}
				}
				$tags.='</div>';	
			}

			$deleteId=base64_encode(base64_encode($value['id']));

			$BuyNow_or_StartOrder_price="";
			if($value['isNegotiable']=='n'){
				$BuyNow_or_StartOrder_price ='<p class="price">'.$value['min_price'].' '.CURRENCY_CODE.'<span> / '.$value['unitName'].'</span></p>';
			}else{
				$BuyNow_or_StartOrder_price ='<p class="price">'.$value['min_price'].'-'.$value['max_price'].' '.CURRENCY_CODE.'<span> / '.$value['unitName'].'</span></p>';
			}

			$edit_btn=$view_btn=$delete_btn="";
			if(!empty($_SESSION['moderatorId'])){
				if(checkModeratorAction($this->module,"edit")){
					$edit_btn = '<a href="'.SITE_URL.'edit-product/'.$value['product_slug'].'" title="'.EDIT_DETAILS.'"><span><i class="fa fa-pencil"></i></span></a>';
				}else{
					$edit_btn="";
				}
				if(checkModeratorAction($this->module,"view")){
					$view_btn = '<a href="'.SITE_URL.'product-detail/'.$value['product_slug'].'" title="'.VIEW_DETAILS.'"><span><i class="fa fa-eye"></i></span></a> ';
				}else{
					$view_btn="";
				}
				if(checkModeratorAction($this->module,"delete")){
					$delete_btn = '<a title="'.DELETE.'" class="deleteItems" id="'.$deleteId.'"><span><i class="fa fa-trash"></i></span></a>';
				}else{
					$delete_btn="";
				}
			}else{
				$edit_btn = '<a href="'.SITE_URL.'edit-product/'.$value['product_slug'].'" title="'.EDIT_DETAILS.'"><span><i class="fa fa-pencil"></i></span></a>';
				$view_btn = '<a href="'.SITE_URL.'product-detail/'.$value['product_slug'].'" title="'.VIEW_DETAILS.'"><span><i class="fa fa-eye"></i></span></a> ';
				$delete_btn = '<a title="'.DELETE.'" class="deleteItems" id="'.$deleteId.'"><span><i class="fa fa-trash"></i></span></a>';
			}
			$week_month = '';
			if (!empty($value['week_month'])) {
				$week_month = $value['week_month'] == 'week' ? LBL_WEEK : LBL_MONTH;
			}
			$loop_fields = array(
					"%ID%"=>$value['id'],
					"%PRODUCT_ID%"=>base64_encode(base64_encode($value['id'])),
					"%PRODUCT_IMG%" => $getProductImg,
					"%PRODUCT_TITLE%" => $value['product_title'],
					"%CAT_NAME%" => $value['catName'],
					"%SUBCAT_NAME%" => $value['subCatName'],
					"%MIN_PRICE%"=>$value['min_price'],
					"%MAX_PRICE%"=>$value['max_price'],
					"%UNIT_NAME%"=>$value['unitName'],
					"%ORDER_QUANTITY%"=>$value['order_quantity'],
					"%PRODUCT_LOCATION%"=>$value['product_location'],
					"%FOB%"=>$selected_delivery_terms,
					"%SUPPLY_ABILITY%"=>$value['supply_ability'],
					"%STATUS%"=>$status,
					"%TAGS%"=>$tags,
					"%PRODUCT_SLUG%"=>$value['product_slug'],
					"%DELETE_ID%"=>$deleteId,
					"%FAV_CALSS%"=>$favCalss,
					"%DETAILPAGE_LINK%"=>SITE_URL.'product-detail/'.$value['product_slug'],
					"%BUYNOW_OR_STARTORDER_PRICE%"=>$BuyNow_or_StartOrder_price,
					"%EDIT_BTN%"=>$edit_btn,
					"%VIEW_BTN%"=>$view_btn,
					"%DELETE_BTN%"=>$delete_btn,
					"%MARK_PREMIUM_CLS%"=>($value['mark_premium'] == 'y') ? '':'hide',
					"%PRD_IMG%" => $prdImage,
					"%WEEK_MONTH%" => $week_month,
					"%STATUS_DEA_SELECT%" => $userDeactiveStatusSelection,
					"%STATUS_A_SELECT%" => $userActiveStatusSelection,
			);
			
			$final_product_loop_content .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);

		}

		$totalRow =$SearchResultRows;

		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);

		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));
		$add_btn="";
		if(!empty($_SESSION['moderatorId'])){
			if(checkModeratorAction($this->module,"add")){
				$add_btn = '<a class="btn btn-system" href="'.SITE_URL.'add-product"><i class="fa fa-plus"></i> '.ADD_NEW_PRODUCT.'</a>';
			}else{
				$add_btn="";
			}
		}else{
			$add_btn = '<a class="btn btn-system" href="'.SITE_URL.'add-product"><i class="fa fa-plus"></i> '.ADD_NEW_PRODUCT.'</a>';
		}

		$fields = array(
			"%LEFT_PANEL%"=>$leftPanel,
			"%SEARCH_KEYWORD%"=>$searchKeyword,
			"%PRODUCTS%"=>$final_product_loop_content,
			"%TOTAL_PAGES%" => $totalPages,
			"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMIT,
			"%CURRENT_PAGE%" => $page,
			"%HIDECLASS%" => $hide_class,
			"%ADD_BTN%"=>$add_btn
			);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$final_product_loop_content,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}
}
?>