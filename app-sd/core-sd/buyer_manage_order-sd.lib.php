<?php
class MyOrders extends Home {
	function __construct($module = "",$id="", $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($search_data="all",$starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();
		$hide_class = "";
		$searchKeyword="";
		$time_left ='';
		$all_attechment = '';
		$all_attechment_design = '';
		$FavBuyingRequestLoopData=$loop_tags="";
		$where = "WHERE o.buyer_id = ".$_SESSION['userId'];
		
		$searchKeyword="";

		if(!empty($_GET['keywords'])){
			$searchKeyword=$_GET['keywords'];
			$where = "WHERE o.buyer_id = ".$_SESSION['userId']." AND p.product_title LIKE '%".$_GET['keywords']."%'";
		}
		if($search_data == 'all' || empty($search_data)){
			$where = 'WHERE o.buyer_id = '.$_SESSION['userId'].'';
		}else{
			$where = "WHERE o.buyer_id = ".$_SESSION['userId']." AND p.product_title LIKE '%".$search_data."%'";
		}
		$unitName="un.unit_value_".$this->curr_language." AS unitName";
		$getBuyerOrder=$this->db->pdoQuery("SELECT o.*,p.product_slug,$unitName,c.contact_no_1,c.company_mail,p.id AS ProId,c.user_id AS cID,c.location,c.verified,c.company_name,c.logo,p.product_image,p.product_title,p.isNegotiable,p.unit_id,u.first_name,u.last_name FROM tbl_manage_orders AS o
											INNER JOIN tbl_users AS u ON(u.id = o.buyer_id)
											INNER JOIN tbl_products AS p ON(p.id = o.product_id)
											INNER JOIN tbl_unit_value AS un ON(un.id = p.unit_id)
											INNER JOIN tbl_company AS c ON(c.user_id = p.user_id)
											".$where."
											ORDER BY id desc LIMIT $starLimit, $endLimit
											")->results();
		$SearchResultRows=$this->db->pdoQuery("SELECT o.*,p.product_slug,$unitName,c.contact_no_1,c.company_mail,p.id AS ProId,c.user_id AS cID,c.location,c.verified,c.company_name,c.logo,p.product_image,p.product_title,p.isNegotiable,p.unit_id,u.first_name,u.last_name FROM tbl_manage_orders AS o
											INNER JOIN tbl_users AS u ON(u.id = o.buyer_id)
											INNER JOIN tbl_products AS p ON(p.id = o.product_id)
											INNER JOIN tbl_unit_value AS un ON(un.id = p.unit_id)
											INNER JOIN tbl_company AS c ON(c.user_id = p.user_id)
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
		foreach ($getBuyerOrder as $key => $value) {
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/order_loop_data-sd.skd"))->compile();

				
			$getProductImg=explode(',',$value['product_image']);
			$getProductImg_first=!empty($getProductImg[0])?SITE_PRODUCT_IMG.$getProductImg[0]:SITE_UPD."no_prodcut_img.png";
			$all_attechment_design  = '';
			foreach ($getProductImg as $key_all_atechment => $value_attechment) {			
				if($key_all_atechment >= 1){
					$all_attechment = !empty($value_attechment)?SITE_PRODUCT_IMG.$value_attechment:SITE_UPD."no_prodcut_img.png";
					$all_attechment_design .= '<li>
									              <div class="file-img">
									                <img src="'.$all_attechment.'" alt="" class="file">
									              </div>
									            </li>';
				}	
				
			}
			$is_Raise_a_dispute=$this->db->pdoQuery("SELECT id FROM tbl_manage_order_dispute WHERE product_id = ? AND order_id = ? AND dispute_created_id = ? AND against_dispute_id = ?",array($value['ProId'],$value['order_id'],$_SESSION['userId'],$value['cID']))->affectedrows();
			$data_Raise_a_dispute=$this->db->pdoQuery("SELECT * FROM tbl_manage_order_dispute WHERE product_id = ? AND dispute_created_id = ? AND against_dispute_id = ?",array($value['ProId'],$_SESSION['userId'],$value['cID']))->result();
			$edit_quantity_btn = '';
			if($value['request_approval_status'] == 'pending'){
				$edit_quantity_btn = ' <span class="edit">
					            <a href="#" id="edit_Oid_data" class="edit_Oid_data btn btn-system" title="Edit Status" data-toggle="modal" data-price="'.$value['your_offer'].'" data-Oid="'.$value['id'].'" data-amount="'.$value['quantity'].'" data-target="#editquantity">
					              '.EDIT_ORDER.'
					            </a>
					          </span>';
			}
			else{
				if($value['isNegotiable']=="y" && $value['total_amount']<START_ORDER_LIMIT && ($value['pay_status']=="n" || $value['pay_status']=="") && $value['request_approval_status'] != 'reject'){
					$edit_quantity_btn = '<button id="pay_now" class="btn btn-system pay_now" data-Oid="'.$value['id'].'" data-amount="'.$value['quantity'].'" data-price="'.$value['your_offer'].'">'.PAY_NOW.'</button>';
				}else{
					$edit_quantity_btn = "";
				}
			}
			$after_approved_supplier = '';
			if($value['order_status'] == 'delivered' && $value['order_status'] != 'complate' && $value['order_status'] != 'not-complate' && $value['buyer_delivery_response'] == 'n'){
				$after_approved_supplier = '<span class="btn btn-success yes_delivered" data-status="yes" data-oId="'.$value['id'].'" data-amount="'.$value['total_amount'].'">Yes</span>
          									<span class="btn btn-danger directRaiseDis" data-status="no" data-oId="'.$value['id'].'" data-order_id="'.$value['order_id'].'" data-pro_id="'.$value['product_id'].'" data-amount="'.$value['total_amount'].'">No</span>';
			}else{
				$after_approved_supplier = '';
			}

			$shipping_ountry_name = getTablevalue('tbl_country', 'countryName', array('CountryId'=>$value['selected_country']));

			$order_status=(($value['order_status'] == 'pending') ? PENDING :(($value['order_status'] == 'placed') ? PLACED : (($value['order_status'] == 'dispatched') ? DISPATCHED : (($value['order_status'] == 'delivered') ? DELIVERED : (($value['order_status'] == 'in-dispute') ? IN_DISPUTE : (($value['order_status'] == 'not-complat') ? NOT_COMPLATE : (($value['order_status'] == 'dispute-resolved') ? LBL_DISPUTE_RESOLVED : COMPLAT)))))));
			$order_status_lbl=(($value['order_status'] == 'pending') ? '<span class="label label-warning">'.PENDING.'</span>' :(($value['order_status'] == 'placed') ? '<span class="label label-success">'.PLACED.'</span>' : (($value['order_status'] == 'dispatched') ? '<span class="label label-default">'.DISPATCHED.'</span>' : (($value['order_status'] == 'delivered') ? '<span class="label label-info">'.DELIVERED.'</span>' : (($value['order_status'] == 'in-dispute') ? '<span class="label label-info">'.IN_DISPUTE.'</span>' : (($value['order_status'] == 'not-complat') ? '<span class="label label-info">'.NOT_COMPLATE.'</span>' : (($value['order_status'] == 'dispute-resolved') ? '<span class="label label-info">'.LBL_DISPUTE_RESOLVED.'</span>' : '<span class="label label-danger">'.COMPLAT.'</span>')))))));
			if($value['request_approval_status']=="reject"){
				$order_status=LBL_CANCELLED;
				$order_status_lbl='<span class="label label-info">'.LBL_CANCELLED.'</span>';
			}
			
			$checkOrderDelivered = 0;
			if ($value['order_status'] == 'delivered' && $value['buyer_delivery_response'] == 'y') {
				$checkOrderDelivered = 1;
				
			}
			$getShippingDays = getShippingDays($value['product_id'],$value['quantity'], $value['selected_country'], $value['shipping_method']);
			$Quote_loop_fields=array(
					"%SHIPPING_DAYS_TEXT%" => $getShippingDays,
					"%AFTER_APPROVED_SUPPLIER%"=>$after_approved_supplier,
					"%PRODUCT_TITLE%"=>ucfirst($value['product_title']),
					"%EDIT_QUANTITY_BTN%"=>$edit_quantity_btn,
					"%ORDER_ID%"=>$value['id'],
					"%PRODUCT_SLUG%"=>ucfirst($value['product_slug']),
					"%PRO_IMG%"=>$getProductImg_first,
					"%COMPANY_NAME%"=>ucfirst($value['company_name']),
					"%COMPANY_LOGO%"=>SITE_UPD.'supplier_logo/'.$value['logo'],
					"%CLOCATION%"=>ucfirst($value['location']),
					"%POSTED_DATE%"=>date('dS F Y', strtotime($value['created_date'])),
					"%SHIPPING_METHOD%"=>($value['shipping_method'] == 'standard') ? LBL_STANDARD : EXPRESS,
					"%ORDER_STATUS%"=>$order_status,
					"%ORDER_STATUS_LBL%"=>$order_status_lbl,
					"%DESCRIPTION%"=>ucfirst($value['description']), 
					"%QUANTITY%"=>$value['quantity'], 
					"%YOUR_OFFER%"=>$value['your_offer'].' '.CURRENCY_CODE, 
					"%TOTAL_AMOUNT%"=>$value['total_amount'].' '.CURRENCY_CODE, 
					"%HIDE_VERIFIED_CLASS%"=>($value['verified'] == 'y') ? '':'hide',
					"%OTER_ATTECHMENT%"=>$all_attechment_design,
					"%SUPPLIER_ID%"=>$value['cID'],
					"%PRO_ID%"=>$value['ProId'],
					"%CEMAIL%"=>$value['company_mail'],
					"%CCNO%"=>$value['contact_no_1'],
					"%DISPUTE_DESC%"=>$data_Raise_a_dispute['description'],
					"%DISPUTE_DATE%"=>date('dS F Y', strtotime($data_Raise_a_dispute['created_date'])),
					"%DISPUTE_STATUS%"=>($data_Raise_a_dispute['status'] == 'pending') ? PENDING : SOLVED,
					"%RAISE_A_DISPUTE%"=>($checkOrderDelivered == 0) ? 'hide' : '0',
					"%VIEW_A_DISPUTE%"=>($is_Raise_a_dispute == 0) ? 'hide' : '',
					"%UNIT_NM%" =>$value['unitName'],
					"%SHIPPING_OUNTRY_NAME%"=>$shipping_ountry_name,
					"%DISPATCH_DESCRIPTION%"=>!empty($value['dispatch_description']) ? $value['dispatch_description'] : '-',

				);

			$FavBuyingRequestLoopData .= str_replace(array_keys($Quote_loop_fields), array_values($Quote_loop_fields), $loop_content);
		}
		$totalRow =$SearchResultRows;
		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);
		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));
		$user_wallet_amount=getTablevalue('tbl_users', 'wallet_amount', array('id'=>$_SESSION['userId']));
		if ($this->sessUserType == 1) {
			$typeTitle = ORDER_PLACED;
		} else {
			$typeTitle = MY_ORDERS_AS_BUYER;
		}
		$fields = array(
			"%HIDE_NO_RECORD%" => $hide_no_record,
			"%HIDE_MORE_RECORD%" => $hide_more_record,
			"%LEFT_PANEL%"=>$leftPanel,
			"%LOOP_BUYER_ORDER%"=>$FavBuyingRequestLoopData,
			"%HIDECLASS%" => $hide_class,
			"%TOTAL_PAGES%" => $totalPages,
			"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMIT,
			"%CURRENT_PAGE%" => $page,
			"%TOKEN%"=>genrateRandom(),
			"%SEARCH_KEYWORD%"=>$searchKeyword,
			"%USER_WALLET_AMOUNT%"=>!empty($user_wallet_amount)?$user_wallet_amount:0,
			"%TYPE_TITLE%"=>$typeTitle

		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$FavBuyingRequestLoopData,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}
	public function getAllcerty($token="",$id_comapany="") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images_certy-sd.skd"))->compile();

		if(!empty($token)){
			$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();
		}else{
			$getProductImg=$this->db->select("tbl_company",array("id","company_certificates"),array("id"=>$id_comapany))->result();
			if(!empty($getProductImg['company_certificates'])){
				$getImages=explode(',',$getProductImg['company_certificates']);
			}
		}
		if(!empty($getImages)) {			
			$ImgNo = 0;
			foreach($getImages AS $key => $values) {
				
				if(!empty($id_comapany)){			
					$Id="pro-".$id_comapany."-".$ImgNo++;
					$file_name=$values;
				}else{
					$Id=$values['id'];
					$file_name=$values['file_name'];
				}			
				$active = (empty($key) ? 'active' : '');
				$fields = array(
					"%ID%" => $Id,
					"%IMAGES%" => SITE_UPD.'dispute_images/'.$file_name,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
		}		
		return $final_content;
	}

}
?>