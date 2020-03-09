<?php
class favouriteBuyingReq extends Home {
	function __construct($module = "",$id="", $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();
		$hide_class = "";
		$searchKeyword="";
		$time_left ='';
		$FavBuyingRequestLoopData=$loop_tags="";
		$where = "WHERE fp.item_type = 'b' AND fp.user_id = ".$_SESSION['userId'];
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$unit_name="u.unit_value_".$this->curr_language." AS unit_value";
		$subcatName="sc.subcatName_".$this->curr_language." AS subcatName";
		$getFavouriteData=$this->db->pdoQuery("SELECT fp.*,b.last_date_of_quote,b.id AS bId,b.request_slug,b.request_slug,b.request_title,b.required_quantity,b.attachmemnt,$subcatName,$unit_name,$catName,$unit_name,CONCAT(user.first_name,' ',user.last_name) AS userName,user.id AS UID,user.profile_img, user.user_location, b.total_quotes
											FROM tbl_favorite_product AS fp
											INNER JOIN tbl_buying_request AS b ON b.id = fp.item_id
											INNER JOIN tbl_product_category AS c ON c.id = b.cat_id
											INNER JOIN tbl_unit_value AS u ON u.id = b.unit_id
											INNER JOIN tbl_product_subcategory AS sc ON sc.id = b.subcat_id
											INNER JOIN tbl_users AS user ON user.id = b.user_id
											".$where."
											ORDER BY id desc LIMIT $starLimit, $endLimit
											")->results();
		$SearchResultRows=$this->db->pdoQuery("SELECT fp.*,b.last_date_of_quote,b.id AS bId,b.request_slug,b.request_slug,b.request_title,b.required_quantity,b.attachmemnt,$subcatName,$unit_name,$catName,$unit_name,CONCAT(user.first_name,' ',user.last_name) AS userName,user.id AS UID,user.profile_img
											FROM tbl_favorite_product AS fp
											INNER JOIN tbl_buying_request AS b ON b.id = fp.item_id
											INNER JOIN tbl_product_category AS c ON c.id = b.cat_id
											INNER JOIN tbl_unit_value AS u ON u.id = b.unit_id
											INNER JOIN tbl_product_subcategory AS sc ON sc.id = b.subcat_id
											INNER JOIN tbl_users AS user ON user.id = b.user_id
											".$where."
											")->affectedrows();

		if($SearchResultRows == 0 ){
            $hide_class = ''; 
            $hide_no_record = '';
            $hide_more_record = 'hide';
        }
        else{
            $hide_class = "hidden";
 			$hide_no_record = 'hide';
            $hide_more_record = '';
        }
		foreach ($getFavouriteData as $key => $value) {
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/quote_loop_data-sd.skd"))->compile();
			$attachmemnt=explode(',',$value['attachmemnt']);
			$imgFound="";
			foreach ($attachmemnt as $img_key => $img_value) {
				$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$img_value, PATHINFO_EXTENSION); 
					if(empty($imgFound)){
						if ($ext == 'png' || $ext == 'PNG' || $ext == 'JPG' || $ext == 'jpg' || $ext == 'JPEG' || $ext == 'jpeg') {
							$attachmemnt=!empty($img_value)?SITE_UPD.'buying_request_attechments-sd/'.$img_value:SITE_UPD."no_prodcut_img.png"; 
							$imgFound="Yes";
						}else{
							$attachmemnt = SITE_UPD."no_prodcut_img.png"; 
						}
					}
			}
			$total_pace_quote=$this->db->pdoQuery("SELECT count(id) AS total_quote FROM tbl_quotes WHERE buying_request_id = ? ",array($value['bId']))->result();
			
			$category=!empty($value['catName'])?$value['catName']:"";
			$UNIT_NM=!empty($value['unit_value'])?$value['unit_value']:"";
			$subcatename=!empty($value['subcatName'])?$value['subcatName']:"";
			$request_title=!empty($value['request_title'])?$value['request_title']:"";
			$required_quantity=!empty($value['required_quantity'])?$value['required_quantity']:"";
			$fav_pro_id=base64_encode(base64_encode($value['id']));
			$datestr=$value['last_date_of_quote'];
			$date=strtotime($datestr);
			$diff=$date-time();
			$days=floor($diff/(60*60*24));
			$hours=round(($diff-$days*60*60*24)/(60*60));
			$time_left = ($days < 0 ) ? '0':"$days days $hours hours remain";
			$profile_img=!empty($value['profile_img'])?SITE_UPD.'users-sd/'.$value['UID'].'/'.$value['profile_img']:SITE_UPD.'no_user_image.png';


			$delete_btn=$total_delete_chkBox="";
			if(!empty($_SESSION['moderatorId'])){
				if(checkModeratorAction($this->module,"delete")){
					$delete_btn = ' <a href="#" class="removeProduct delete_from_fav" data-id="'.$fav_pro_id.'"><i class="fa fa-trash"></i></a>';
					$total_delete_chkBox='<input type="checkbox" name="checkboxlist" value="'.$fav_pro_id.'" />';
				}else{
					$delete_btn=$total_delete_chkBox="";
				}
			}else{
				$delete_btn = ' <a href="#" class="removeProduct delete_from_fav" data-id="'.$fav_pro_id.'"><i class="fa fa-trash"></i></a>';
					$total_delete_chkBox='<input type="checkbox" name="checkboxlist" value="'.$fav_pro_id.'" />';
			}
			
			$noQuoteLeft = $value['total_quotes'];


			$Quote_loop_fields=array(
					"%ATTECHMENTS_IMG%"=>$attachmemnt,
					"%CATEGORY%"=>$category,
					"%SUBCATEGORY%"=>$subcatename,
					"%UNIT_NM%"=>$UNIT_NM,
					"%REQUEST_TITLE%"=>$request_title,
					"%REQUIRED_QUANTITY%"=>$required_quantity,
					"%PROFILE_IMG%"=>$profile_img,
					"%USERNAME%"=>$value['userName'],
					"%FAV_PRO_ID%"=>$fav_pro_id,
					"%TOTAL_PLACE_QUOTE%"=>$total_pace_quote['total_quote'],
					"%PRODUCT_SLUG%" => $value['request_slug'],
					"%TIME_LEFT%" => $time_left,
					"%LAST_DATE_OF_QUOTE%" => date('dS F Y', $date),
					"%DELETE_BTN%"=>$delete_btn,
					"%TOTAL_DELETE_CHKBOX%"=>$total_delete_chkBox,
					"%BUYER_LOCATION%" => !empty($value['user_location']) ? $value['user_location'] : "-",
					"%NO_QUOTE_LEFT%" => $noQuoteLeft

				);
			$FavBuyingRequestLoopData .= str_replace(array_keys($Quote_loop_fields), array_values($Quote_loop_fields), $loop_content);
		}
		$totalRow =$SearchResultRows;
		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);
		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));

		$removeAll_btn="";
		if(!empty($_SESSION['moderatorId'])){
			if(checkModeratorAction($this->module,"delete")){
				$removeAll_btn='<label>
		                            <input type="checkbox" id="DeleteAllRecord" onclick="checkAll(this)" value="">
		                            '.SELECT_ALL_VISIBLE_RFQS.'
		                          </label>
		                          
		                          <a href="#" class="deleteAll" id="deleteAll"> | '.REMOVE_SELECTED_RFQS.' <i class="fa fa-trash"></i></a>';
		    }else{
		    	$removeAll_btn="";
		    }
	    }else{
	    	$removeAll_btn='<label>
		                            <input type="checkbox" id="DeleteAllRecord" onclick="checkAll(this)" value="">
		                            '.SELECT_ALL_VISIBLE_RFQS.'
		                          </label>
		                          
		                          <a href="#" class="deleteAll" id="deleteAll"> | ' .REMOVE_SELECTED_RFQS.' <i class="fa fa-trash"></i></a>';
	    }

		$fields = array(
			"%HIDE_NO_RECORD%" => $hide_no_record,
			"%HIDE_MORE_RECORD%" => $hide_more_record,
			"%LEFT_PANEL%"=>$leftPanel,
			"%LOOP_BUYING_REQ%"=>$FavBuyingRequestLoopData,
			"%HIDECLASS%" => $hide_class,
			"%TOTAL_PAGES%" => $totalPages,
			"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMIT,
			"%CURRENT_PAGE%" => $page,
			"%SEARCH_KEYWORD%"=>$searchKeyword,
			"%REMOVEALL_BTN%"=>$removeAll_btn
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$FavBuyingRequestLoopData,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}

}
?>