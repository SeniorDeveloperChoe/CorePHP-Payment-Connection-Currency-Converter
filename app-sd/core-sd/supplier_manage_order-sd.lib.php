<?php
class MyOrders_Supplier extends Home {
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
		$searchKeyword=$time_left =$all_attechment = $all_attechment_design =$pending_selected = $placed_selected = $dispatched_selected = $delivered_selected = $dispute_selected = '';
		$FavBuyingRequestLoopData=$loop_tags=$accepted_selected_req=$pending_selected_req="";
		$where = "WHERE o.supplier_id = ".$_SESSION['userId'];
		
		$searchKeyword="";

		if(!empty($_GET['keywords'])){
			$searchKeyword=$_GET['keywords'];
			$where = "WHERE o.supplier_id = ".$_SESSION['userId']." AND p.product_title LIKE '%".$_GET['keywords']."%'";
		}
		if($search_data == 'all' || empty($search_data)){
			$where = 'WHERE o.supplier_id = '.$_SESSION['userId'].'';
		}else{
			$where = "WHERE o.supplier_id = ".$_SESSION['userId']." AND p.product_title LIKE '%".$search_data."%'";
		}
		$unitName="un.unit_value_".$this->curr_language." AS unitName";
		$getSupplierOrder=$this->db->pdoQuery("SELECT p.isNegotiable,u.phone_no,u.email,o.*,p.user_id AS cID,o.id As or_id,u.id AS USeRId,u.profile_img,u.user_location,$unitName,c.contact_no_1,c.company_mail,p.id AS ProId,c.user_id AS cID,c.location,c.verified,o.created_date AS cdate,c.company_name,c.logo,p.product_image,p.product_title,p.product_slug,p.unit_id,u.first_name,u.last_name FROM tbl_manage_orders AS o
											INNER JOIN tbl_users AS u ON(u.id = o.buyer_id)
											INNER JOIN tbl_products AS p ON(p.id = o.product_id)
											INNER JOIN tbl_unit_value AS un ON(un.id = p.unit_id)
											INNER JOIN tbl_company AS c ON(c.user_id = p.user_id)
											".$where."
											ORDER BY id desc LIMIT $starLimit, $endLimit
											")->results();
		$SearchResultRows=$this->db->pdoQuery("SELECT p.isNegotiable,u.phone_no,u.email,o.*,p.user_id AS cID,o.id As or_id,u.id AS USeRId,u.profile_img,u.user_location,$unitName,c.contact_no_1,c.company_mail,p.id AS ProId,c.user_id AS cID,c.location,c.verified,o.created_date AS cdate,c.company_name,c.logo,p.product_image,p.product_title,p.product_slug,p.unit_id,u.first_name,u.last_name FROM tbl_manage_orders AS o
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
       
		foreach ($getSupplierOrder as $key => $value) {
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/order_loop_data-sd.skd"))->compile();
			$getProductImg=explode(',',$value['product_image']);
			$getAttechmentsImg_first=!empty($getProductImg[0])?SITE_PRODUCT_IMG.$getProductImg[0]:SITE_UPD."no_prodcut_img.png";
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
			if($value['order_status'] == 'pending'){
					$placed_selected = $dispatched_selected = $delivered_selected = $dispute_selected = '';
					$pending_selected = 'selected';
			}else if($value['order_status'] == 'placed'){
				$pending_selected =$dispatched_selected = $delivered_selected = $dispute_selected = '';
					$placed_selected = 'selected';
			}else if($value['order_status'] == 'dispatched'){
				$pending_selected = $placed_selected = $delivered_selected = $dispute_selected = '';
					$dispatched_selected = 'selected';
			} else if($value['order_status'] == 'delivered'){
				$pending_selected = $placed_selected = $dispatched_selected = $dispute_selected = '';
					$delivered_selected = 'selected';
			}else {
				$pending_selected = $placed_selected = $dispatched_selected = $delivered_selected ='';
					$dispute_selected = 'selected';
			}
			$editStatus_btn=$request_Status_btn=$order_status=$order_lbl_status="";

			$order_status=(($value['order_status'] == 'pending') ? PENDING :(($value['order_status'] == 'placed') ? PLACED : (($value['order_status'] == 'dispatched') ? DISPATCHED : (($value['order_status'] == 'delivered') ? DELIVERED : (($value['order_status'] == 'in-dispute') ? IN_DISPUTE : (($value['order_status'] == 'not-complat') ? NOT_COMPLATE : (($value['order_status'] == 'dispute-resolved') ? LBL_DISPUTE_RESOLVED : COMPLAT)))))));
			$order_lbl_status=(($value['order_status'] == 'pending') ? '<span class="label label-warning">'.PENDING.'</span>' :(($value['order_status'] == 'placed') ? '<span class="label label-success">'.PLACED.'</span>' : (($value['order_status'] == 'dispatched') ? '<span class="label label-default">'.DISPATCHED.'</span>' : (($value['order_status'] == 'delivered') ? '<span class="label label-info">'.DELIVERED.'</span>' : (($value['order_status'] == 'in-dispute') ? '<span class="label label-info">'.IN_DISPUTE.'</span>' : (($value['order_status'] == 'not-complat') ? '<span class="label label-info">'.NOT_COMPLATE.'</span>' :  (($value['order_status'] == 'dispute-resolved') ? '<span class="label label-info">'.LBL_DISPUTE_RESOLVED.'</span>' : '<span class="label label-danger">'.COMPLAT.'</span>')  ))))));

			if($value['request_approval_status']=="reject"){
				$order_status=LBL_CANCELLED;
				$order_lbl_status='<span class="label label-info">'.LBL_CANCELLED.'</span>';
			}

			$order_status_class='';
			if($value['request_approval_status'] == 'pending'){
					$order_status_class='hide';
					$accepted_selected_req = '';
					$pending_selected_req = 'selected';
			}else{
					$pending_selected_req = '';
					$accepted_selected_req = 'selected';
			}


			if(!empty($_SESSION['moderatorId'])){
				if(checkModeratorAction($this->module,"edit")){
					if($order_status!=DELIVERED && $order_status!=IN_DISPUTE && $order_status!=COMPLAT && $order_status!=NOT_COMPLATE && $value['request_approval_status'] != 'reject'){
						if($value['pay_status']=="y" || $value['total_amount']>START_ORDER_LIMIT){
							$editStatus_btn='<a class="edit_status '.$order_status_class.'" id="edit_status" title="Edit Status" data-placed_selected="'.$placed_selected.'" data-pending_selected="'.$pending_selected.'" data-dispatched_selected="'.$dispatched_selected.'" data-delivered_selected="'.$delivered_selected.'" data-dispute_selected="'.$dispute_selected.'" data-Oid="'.$value["or_id"].'" data-toggle="modal" data-target="#editStatus">
				              <i class="fa fa-edit"></i>
				            </a>';
				        }
			        }
			        if($value['isNegotiable'] == 'y' && $order_status!=IN_DISPUTE && $value['request_approval_status'] == 'pending'){
						$request_Status_btn='<a class="request_edit_status_btn" title="Edit Status" data-accepted_selected="'.$accepted_selected_req.'" data-pending_selected="'.$pending_selected_req.'" data-Oid="'.$value["or_id"].'" data-toggle="modal" data-target="#request_editStatus">
			              <i class="fa fa-edit"></i>
			            </a>';
					} 
				}else{
					$editStatus_btn="";
					$request_Status_btn = '';
				}
			}else{
				if($order_status!=DELIVERED && $order_status!=IN_DISPUTE && $order_status!=COMPLAT && $order_status!=NOT_COMPLATE && $value['request_approval_status'] != 'reject' && $order_status!=LBL_DISPUTE_RESOLVED){
					

					if($value['pay_status']=="y" || $value['total_amount']>START_ORDER_LIMIT){
						$editStatus_btn='<a class="edit_status '.$order_status_class.'" title="Edit Status" data-placed_selected="'.$placed_selected.'" data-pending_selected="'.$pending_selected.'" data-dispatched_selected="'.$dispatched_selected.'" data-delivered_selected="'.$delivered_selected.'" data-dispute_selected="'.$dispute_selected.'" data-Oid="'.$value["or_id"].'" data-toggle="modal" data-target="#editStatus">
			              <i class="fa fa-edit"></i>
			            </a>';
			        }
				}

				if($value['isNegotiable'] == 'y' && $order_status!=IN_DISPUTE && $value['request_approval_status'] == 'pending'){
					$request_Status_btn='<a class="request_edit_status_btn" title="Edit Status" data-accepted_selected="'.$accepted_selected_req.'" data-pending_selected="'.$pending_selected_req.'" data-Oid="'.$value["or_id"].'" data-toggle="modal" data-target="#request_editStatus">
		              <i class="fa fa-edit"></i>
		            </a>';				
				} else{
					$request_Status_btn = '';
				}
				
			}


			$is_Raise_a_dispute=$this->db->pdoQuery("SELECT id FROM tbl_manage_order_dispute WHERE product_id = ? AND order_id = ? AND dispute_created_id = ? AND against_dispute_id = ?",array($value['ProId'],$value['order_id'], $_SESSION['userId'],$value['buyer_id']))->affectedrows();
			$data_Raise_a_dispute=$this->db->pdoQuery("SELECT * FROM tbl_manage_order_dispute WHERE product_id = ? AND dispute_created_id = ? AND against_dispute_id = ?",array($value['ProId'],$_SESSION['userId'],$value['buyer_id']))->result();	
			$shipping_ountry_name = getTablevalue('tbl_country', 'countryName', array('CountryId'=>$value['selected_country']));
			$checkOrderDelivered = 0;
			if ($value['order_status'] == 'delivered' && $value['buyer_delivery_response'] == 'y') {
				$checkOrderDelivered = 1;
			}
			$getShippingDays = getShippingDays($value['product_id'],$value['quantity'], $value['selected_country'], $value['shipping_method']);

			$Quote_loop_fields=array(
					"%SHIPPING_DAYS_TEXT%" => $getShippingDays,
					"%ORDER_ID%"=>$value['id'],
					"%REQUEST_TITLE%"=>ucfirst($value['product_title']),
					"%PRODUCT_SLUG%"=>ucfirst($value['product_slug']),
					"%BUYING_REQ_IMAGE%"=>$getAttechmentsImg_first,
					"%BUYER_NM%"=>ucfirst($value['first_name'].' '.$value['last_name']),
					"%USER_IMG%"=>SITE_UPD.'users-sd/'.$value['USeRId'].'/'.$value['profile_img'],
					"%USER_LOCATION%"=>ucfirst($value['user_location']),
					"%POSTED_DATE%"=>date('dS F Y', strtotime($value['cdate'])),
					"%SHIPPING_METHOD%"=>($value['shipping_method'] == 'standard') ? LBL_STANDARD : EXPRESS,
					"%ORDER_STATUS%"=>$order_status,
					"%ORDER_STATUS_LBL%"=>$order_lbl_status,
					"%DESCRIPTION%"=>ucfirst($value['description']), 
					"%QUANTITY%"=>ucfirst($value['quantity']), 
					"%YOUR_OFFER%"=>$value['your_offer'].' '.CURRENCY_CODE, 
					"%TOTAL_AMOUNT%"=>$value['total_amount'].' '.CURRENCY_CODE, 
					"%OTER_ATTECHMENT%"=>$all_attechment_design,
					"%BUYER_ID%"=>$value['cID'],
					"%MSG_BUYER_ID%"=>$value['buyer_id'],
					"%BUYING_REQ_ID%"=>$value['ProId'],
					"%USEREMAIL%"=>$value['email'],
					"%CCNO%"=>$value['phone_no'],
					"%DISPUTE_DESC%"=>$data_Raise_a_dispute['description'],
					"%DISPUTE_DATE%"=>date('dS F Y', strtotime($data_Raise_a_dispute['created_date'])),
					"%DISPUTE_STATUS%"=>($data_Raise_a_dispute['status'] == 'pending') ? PENDING : SOLVED,
					"%RAISE_A_DISPUTE%"=>($checkOrderDelivered == 0) ? 'hide' : '0',
					"%VIEW_A_DISPUTE%"=>($is_Raise_a_dispute == 0) ? 'hide' : '',
					"%UNIT_NM%" =>$value['unitName'],
					"%PLACED_SELECTED%" => $placed_selected,
					"%PENDING_SELECTED%" => $pending_selected,
					"%DISPATCHED_SELECTED%" => $dispatched_selected,
					"%DELIVERED_SELECTED%" => $delivered_selected,
					"%DISPUTE_SELECTED%" => $dispute_selected,
					"%ORDER_ID_FIRST%" => $value["or_id"],
					"%EDITSTATUS_BTN%"=>$editStatus_btn,
					"%REQUEST_STATUS_BTN%"=>$request_Status_btn,
					"%REQUEST_STATUS%"=>($value['request_approval_status'] == 'pending') ? PENDING : (($value['request_approval_status'] == 'reject') ? LBL_CANCELLED : ACCEPTED),
					"%SHIPPING_OUNTRY_NAME%"=>$shipping_ountry_name,
					"%DISPATCH_DESCRIPTION%"=>!empty($value['dispatch_description']) ? $value['dispatch_description'] : '-',

				);

			$FavBuyingRequestLoopData .= str_replace(array_keys($Quote_loop_fields), array_values($Quote_loop_fields), $loop_content);
		}
		$totalRow =$SearchResultRows;
		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);
		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));
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
			"%SEARCH_KEYWORD%"=>$searchKeyword
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