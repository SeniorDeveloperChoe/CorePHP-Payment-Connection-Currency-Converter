<?php
class Cart extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent($starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$where =(isset($_SESSION['temp_userid'])) ? $_SESSION['temp_userid'] : $_SESSION['userId'] ;
		$field =(isset($_SESSION['temp_userid'])) ? "ca.tmp_userid" : "ca.user_id" ;

		$getCartProductData=$this->db->pdoQuery("SELECT ca.id AS cartId,ca.shipping_charge,ca.product_id AS cartProductId,p.*,p.id AS ProductId,$catName,co.company_name,co.logo,co.location,co.business_type_id,co.verified,co.id AS supplierId,CONCAT(u.first_name,' ',u.last_name) AS supplierName
											FROM tbl_cart AS ca
											INNER JOIN tbl_products AS p ON p.id=ca.product_id
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id 
											LEFT JOIN tbl_company AS co ON co.user_id=p.user_id
											INNER JOIN tbl_users AS u ON u.id=co.user_id
											WHERE $field=?
											",array($where))->results();
		$totalItem=count($getCartProductData);
		$totalItemPrice=0;
		$total_shipping_charge=0;
		$cartProducts="";

		if(empty($getCartProductData)){
			$html = (new MainTemplater(DIR_TMPL.$this->module."/empty_cart-sd.skd"))->compile();
			$cartProducts=$html;
		}else{
			
			foreach ($getCartProductData as $key => $value) {

				$cart_loop_content = (new MainTemplater(DIR_TMPL.$this->module."/cart_loop_data-sd.skd"))->compile();

				$product_img='';
				if(!empty($value['product_image'])){
					$productImg=explode(',', $value['product_image']);
					$product_img=!empty($value['product_image'])?SITE_PRODUCT_IMG.$productImg[0]:SITE_UPD.'no_prodcut_img.png';
				}

				$totalProductPrice=(int)$value['min_price']*(int)$value['order_quantity'];
				$totalItemPrice=(int)$totalItemPrice+(int)$totalProductPrice;
				$total_shipping_charge+=$value['shipping_charge'];
				$cart_loop_fields = array(
						"%ID%"=>base64_encode(base64_encode($value['cartId'])),
						"%SUPPLIER_ID%"=>base64_encode(base64_encode($value['supplierId'])),
						"%PRODUCT_IMG%"=>$product_img,
						"%PRODUCT_TITLE%"=>!empty($value['product_title'])?$value['product_title']:"",
						"%CAT_NAME%"=>!empty($value['catName'])?$value['catName']:"",
						"%TOTAL_PRICE%"=>!empty($totalProductPrice)?$totalProductPrice.' '.CURRENCY_CODE:"",
						"%MIN_PRICE%"=>!empty($value['min_price'])?$value['min_price'].' '.CURRENCY_CODE:"",
						"%ORDER_QUANTITY%"=>!empty($value['order_quantity'])?$value['order_quantity']:"",
						"%PRODUCT_SLUG%"=>!empty($value['product_slug'])?$value['product_slug']:"",
						"%MESSAGE_DESC%"=>"msg_[".$value["ProductId"].']',
						"%SHIPPING_DESC%"=>"shipping_[".$value["ProductId"].']',
						"%QUENTITY%"=>"quentity_[".$value["ProductId"].']',
						"%AMOUNT%"=>"amount_[".$value["ProductId"].']',
						"%TOTAL_AMOUNT%"=>!empty($totalProductPrice)?$totalProductPrice:"",
						"%PRODUCT_ID%"=>base64_encode(base64_encode($value['ProductId']))
				);
				
				$cartProducts .= str_replace(array_keys($cart_loop_fields), array_values($cart_loop_fields), $cart_loop_content);

					
			}	
		}
		$total_payable_amount=$totalItemPrice+$total_shipping_charge;
		$fields = array(
					"%CART_PRODUCTS%"=>$cartProducts,
					"%TOTAL_ITEM%"=>$totalItem,
					"%TOTAL_ITEM_PRICE%"=>$totalItemPrice.' '.CURRENCY_CODE,
					"%TOTAL_PAYABLE_AMOUNT%"=>$total_payable_amount.' '.CURRENCY_CODE,
					"%TOKEN%"=>genrateRandom(),
					"%TOTAL_SHIPPING_CHARGE%"=>$total_shipping_charge.' '.CURRENCY_CODE
			);
			
		$html = str_replace(array_keys($fields), array_values($fields), $html);

		return $html;
	}


	public function getAllPics($token="") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();

		$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();

		if(!empty($getImages)) {			
			foreach($getImages AS $key => $values) {
				
				$Id=$values['id'];
				$file_name=$values['file_name'];
				$attachmemnt="";
				$ext = pathinfo(DIR_MSG_DOCS.$file_name, PATHINFO_EXTENSION);
				if($ext=="pdf") { 
					$attachmemnt = getImage(DIR_UPD.'pdf.png',"153","148"); }
				else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$attachmemnt = getImage(DIR_UPD.'doc.png',"153","148"); }
				else { 
					$attachmemnt = getImage(DIR_MSG_DOCS.$file_name, "153", "148"); }

				$active = (empty($key) ? 'active' : '');
				$fields = array(
					"%ID%" => $Id,
					"%IMAGES%" => $attachmemnt,
					"%ACTIVE%" => $active
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			
		}		
		return $final_content;
	}



	public function addProduct($data){

		 $objPost = new stdClass();		 
		 $OrderPost = new stdClass();
		extract($data);
		$orderIDs="";
		foreach ($msg_ as $key => $value) {            
                $objPost->product_id=$key;
                $desc=$value;
                $message="";
                
                $getProductDetail=$this->db->select("tbl_products",array("user_id","product_title","product_slug","min_price"),array("id"=>$objPost->product_id))->result();

                if(!empty($desc)){
                	$message .= ''.PRODUCT_NAME.': <a href='.SITE_URL.'product-detail/'.$getProductDetail['product_slug'].'>'.$getProductDetail['product_title'].'</a><br/>
                				'.PRODUCT_DESCRIPTION.': '.$desc.'<br/>';
                }
                $Wheretoken=base64_encode(base64_encode($key)).'_'.$token;
        		$getAttachments=$this->db->select("temp_product_images",array("file_name","actual_file_name"),array("token"=>$Wheretoken))->results();
        		$lastAttachment=end($getAttachments);
        		$Actual_attachmemnts_name="";
        		$Db_attachmemnts_name="";
        		$order_id = 'OI000001';
        		foreach ($getAttachments as $keyy => $img_value) {
        			if($img_value['file_name']==$lastAttachment['file_name']){
        				$Actual_attachmemnts_name.=$img_value['actual_file_name'];
        				$Db_attachmemnts_name.=$img_value['file_name'];
        			}else{
        				$Actual_attachmemnts_name.=$img_value['actual_file_name'].',';
        				$Db_attachmemnts_name.=$img_value['file_name'].',';
        			}
        		}
        		 $objPost->msg=$message;
        		 if($message!="" && $getAttachments!=""){
        		 		$objPost->msg_type="b";
        		 }elseif($message!="" && $getAttachments==""){
						$objPost->msg_type="t";
        		 }else{
						$objPost->msg_type="f";
        		 }
        		 $objPost->sender=$_SESSION['userId'];
        		 $objPost->receiver=$getProductDetail['user_id'];
        		 $objPost->actual_file_name=$Actual_attachmemnts_name;
        		 $objPost->db_file_name=$Db_attachmemnts_name;
        		 $objPost->is_read='n';
        		 $objPost->created_date=date("Y-m-d H:i:s");
        		 $objPost->ipAddress=get_ip_address();
        		 $objPostArray=(array)$objPost;

        		 if($desc == "" && $getAttachments == ""){

        		 }else{
        		 	//$this->db->insert("tbl_messages",$objPostArray);
        		 }

        		$orderUniqueId  = $this->db->pdoQuery('select MAX(id) as oid From tbl_temp_orders')->result();
		        $len   = $orderUniqueId['oid'] + 1;
		        $clen  = strlen($len);
		        if ($clen > 6) {
		            $diff    = $clen - 6;
		            $suffixd = '';
		            for ($i = 0; $i < $diff; $i++) {
		                $suffixd .= '0';
		            }
		            $order_id .= $suffixd;
		        }
		        $order_id = substr_replace($order_id, $len, -$clen);
		        $get_cart_detail=$this->db->select("tbl_cart",array("*"),array("user_id"=>$_SESSION['userId'],"product_id"=>$objPost->product_id))->result();


		        $OrderPost->description=$get_cart_detail['shipping_desc'];
        		$OrderPost->shipping_method=$get_cart_detail['shipping_method'];
        		$OrderPost->selected_country=$get_cart_detail['shipping_country'];

        		$totalPayableAmount=$amount_[$key]+$get_cart_detail['shipping_charge'];

		        $OrderPost->order_id=$order_id;
        		$OrderPost->quantity=$quentity_[$key];
        		$OrderPost->product_id=$key;
				$OrderPost->supplier_id=$getProductDetail['user_id'];
				$OrderPost->buyer_id=$_SESSION['userId'];
				$OrderPost->order_status="pending";
				$OrderPost->request_approval_status="accept";
				$OrderPost->total_amount=(string)$totalPayableAmount;
				$OrderPost->isActive='y';
				$OrderPost->your_offer=$getProductDetail['min_price'];
				$OrderPost->created_date=date("Y-m-d H:i:s");
				$OrderobjPostArray=(array)$OrderPost;
				$order_Id=$this->db->insert("tbl_temp_orders",$OrderobjPostArray)->lastInsertId();
				$orderIDs.=$order_Id."-";
				$this->db->delete("tbl_cart",array("user_id"=>$_SESSION['userId'],"product_id"=>$objPost->product_id));

        } 


        $encoded_ids=base64_encode(base64_encode(base64_encode($orderIDs)));

		$ids=explode('-', $orderIDs);
		$total_order_amount=$orderIds="";
		$date = date("Y-m-d H:i:s");
		foreach ($ids as $key => $value) {
			if(!empty($value)){
				$get_product_amount=$this->db->pdoQuery("SELECT total_amount FROM tbl_temp_orders WHERE id=?",array($value))->result();
				$total_order_amount+=$get_product_amount['total_amount'];
			}
		}

        $userWalletAmount=getTableValue("tbl_users",'wallet_amount',array('id' => $_SESSION['userId']));
	   		$updated_user_wallet_amount=$userWalletAmount-$total_order_amount;
			$need_to_pay="";
			if($userWalletAmount==0){
				$need_to_pay=$total_order_amount;
			}elseif($updated_user_wallet_amount<1){
				$need_to_pay=$total_order_amount-$userWalletAmount;
			}	

			 if($need_to_pay==""){
				foreach ($ids as $key => $value) {
	   				$uniqueOrderId = getUniqueOrderId();
	   				$uniqueTransactionId = getUniqueTransactionId();
					if(!empty($value)){
						$get_order_detail=$this->db->pdoQuery("SELECT * FROM tbl_temp_orders WHERE id=?",array($value))->result();
						$order_id = $this->db->insert('tbl_manage_orders', array('order_id'=>$uniqueOrderId,'product_id'=>$get_order_detail['product_id'],"buyer_id"=>$get_order_detail['buyer_id'],"supplier_id"=>$get_order_detail['supplier_id'],'quantity'=>$get_order_detail['quantity'],"order_status"=>$get_order_detail['order_status'],"request_approval_status"=>$get_order_detail['request_approval_status'],"your_offer"=>$get_order_detail['your_offer'],"selected_country"=>$get_order_detail['selected_country'],"description"=>$get_order_detail['description'],"shipping_method"=>$get_order_detail['shipping_method'],"total_amount"=>$get_order_detail['total_amount'], 'order_type' => 'on',"created_date"=>$date))->lastInsertId();

						$orderIds.=$order_id."-";
						$data = new stdClass();
						$data->payment_type = 'pp';
						$data->item_id=$get_order_detail['product_id'];
						$amount=$get_order_detail['total_amount'];
						$data->user_id =(int)$_SESSION['userId'];
						$data->amount =(string)$amount;
						$data->transaction_id =$uniqueTransactionId;
						$data->payment_date = date('Y-m-d H:i:s');
						$data->status = "c";
						$data->wallet_status = "onHold";
						$data->order_id = $uniqueOrderId;
						$data->ip_address = get_ip_address();
						$this->db->insert('tbl_payment_history', (array)$data);

						$this->db->delete("tbl_temp_orders",array("id"=>$get_order_detail['id']));

						$getOrderDetail=$this->db->select("tbl_manage_orders",array("*"),array('id'=>$order_id))->result();
						$this->db->update("tbl_manage_orders",array("pay_status"=>"y"),array("id"=>$order_id));

						/*Send Notifications*/
				   		$userDate=$this->db->pdoQuery("SELECT first_name,last_name FROM tbl_users WHERE id=?",array($getOrderDetail['buyer_id']))->result();
				   		$pronm=$this->db->pdoQuery("SELECT product_title,user_id FROM tbl_products WHERE id=? ",array($getOrderDetail['product_id']))->result();
				   		$order_request_noti=$this->db->pdoQuery("SELECT first_name,last_name,order_request_noti,email FROM tbl_users WHERE id=? AND (user_type = ? OR user_type = ?)",array($getOrderDetail['supplier_id'],2,3))->result();
				  		$notifyConstant = 'New order is placed on '.$pronm['product_title']. ' by '.$userDate['first_name'].' '.$userDate['last_name'];

				  		$notifyUrl = 'manage_orders-sd?notiId='.$order_id;
						add_admin_notification($notifyConstant, $notifyUrl);

						$msg_place_quote =str_replace(
										array("#USERNAME#", "#PRODUCT_TITLE#"),
										array($userDate['first_name'].' '.$userDate['last_name'], $pronm['product_title']),
										START_ORDER_BY_SUPPLIER
							);
						$notifyUrl = 'supplier-manage-order';
						add_notification($msg_place_quote, $pronm['user_id'], $notifyUrl);

						$start_order_your_product =str_replace(
										array("#PRODUCT_TITLE#"),
										array($pronm['product_title']),
										START_ORDER_YOUR_PRODUCT
							);
						$notifyUrl = 'buyer-manage-order';

						add_notification($start_order_your_product, $_SESSION['userId'], $notifyUrl);

						if($order_request_noti['order_request_noti'] == 'y'){
							$arrayCont = array("Username"=>ucfirst($userDate['first_name'].' '.$userDate['last_name']),"greetings"=>ucfirst($order_request_noti['first_name'].' '.$order_request_noti['last_name']), "productDetailLink"=>SITE_URL.'supplier-manage-order');
							sendMail($order_request_noti['email'], 'notify_order_request_noti', $arrayCont);	
		   				}

		   				$supplier_id=getTablevalue('tbl_products', 'user_id', array('id'=>$data->item_id));

		   				$admin_commision_amount=(ADMIN_COMMISION*$data->amount)/100;
	  	 				$supplier_amount=$data->amount-$admin_commision_amount;

		   				$this->db->pdoQuery('UPDATE tbl_users set wallet_amount=wallet_amount-'.$data->amount.' WHERE id=?',array($data->user_id));
		   				$this->db->insert("tbl_payment_history",array('order_id'=>$data->order_id,'user_id'=>$supplier_id,'amount'=>(string)$supplier_amount,'item_id'=>$data->item_id,'payment_type'=>'sp','status'=>'c','wallet_status'=>'onHold', 'transaction_id' =>$uniqueTransactionId ,'payment_date'=>date('Y-m-d H:i:s'),"ip_address"=>get_ip_address()));
					}
				}
				$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>YOU_ARE_SUCCESSFULLY_PLACED_YOUR_ORDER));
        		redirectPage(SITE_URL.'buyer-manage-order');
              }else if($need_to_pay!=""){
        		redirectPage(SITE_URL."cart-order/".$encoded_ids);
              } 


	}


}
?>