<?php
class ProductStartOrder extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	
	public function send_notification($buyer_id="",$buying_req_id="",$supplier_id="" ,$orderId = 0)
	{
			$userDate=$this->db->pdoQuery("SELECT first_name,last_name FROM tbl_users WHERE id=?",array($buyer_id))->result();
	   		$pronm=$this->db->pdoQuery("SELECT product_title,user_id FROM tbl_products WHERE id=? ",array($buying_req_id))->result();
	   		$order_request_noti=$this->db->pdoQuery("SELECT first_name,last_name,order_request_noti,email FROM tbl_users WHERE id=? AND (user_type = ? OR user_type = ?)",array($supplier_id,2,3))->result();
			
			$notifyConstant = 'New order is placed on '.$pronm['product_title']. ' by '.$userDate['first_name'].' '.$userDate['last_name'];

			$notifyUrl = 'manage_orders-sd?notiId='.$orderId;
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
	}


	public function getPageContent($getProductId="") {

		$html = new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd");
		$html = $html->compile();
		$getProductData = $getAttechmentsImg = $Country_select=$shipping_methods=$all_country='';
		$shipping_amount='0';
		if(empty($this->id)){
			$this->id=$getProductId;
		}
		if(!empty($this->id)){	
			$unitName="un.unit_value_".$this->curr_language." AS unitName";

			$getProductData=$this->db->pdoQuery("SELECT p.*,c.user_id AS supplier_id,CONCAT(u.first_name,' ',u.last_name) AS Username,c.company_name,c.logo,c.contact_person_name,c.contact_no_1,c.contact_no_2,c.location,$unitName
												FROM tbl_products AS p
												LEFT JOIN tbl_company AS c ON c.user_id=p.user_id
												INNER JOIN tbl_users AS u ON u.id=p.user_id
												INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id
												WHERE p.id = ?
												",array($this->id['id']))->result();

			if($getProductData['shipping_type'] != 'others'){
				$Country_select_all=$this->db->pdoQuery("SELECT CountryId,countryName FROM tbl_country WHERE isActive=?",array('y'))->results();
					foreach ($Country_select_all as $key => $value){
						$selected="";
						$all_country.=" <option value='".$value['CountryId']."' ".$selected.">".$value['countryName']."</option>";
						$Country_select="<select class='form-control country_id' name='country_id' id='country_id'>
                                            <option selected='selected' disabled='disabled'>---".SELECT_COUNTRY."---</option>                                          
                                           ".$all_country."
                                            </select>";
					}
					if($getProductData['shipping_type'] == 'everywhere'){
						$shipping_amount = $getProductData['shipping_detail'];
					}else{
						$shipping_amount = '0';
					}
					
                    $shipping_methods = '<select class="form-control shipping_method" name="shipping_method" id="shipping_method"><option value="">---'.SELECT_SHIPPING_METHODS.'---</option></select>';
			}else{
				$shipping_methods='<select class="form-control shipping_method" name="shipping_method" id="shipping_method_other">
				<option value="">---'.SELECT_SHIPPING_METHODS.'---</option></select>';
				$all_json_same_country =array();
				$obj = (array)json_decode($getProductData['shipping_detail'],true);
				foreach ($obj as $k=>$val){	  
				   $Country_select_all=$this->db->pdoQuery("SELECT CountryId,countryName 
				   											FROM tbl_country WHERE isActive=?",array('y'))->results();
					foreach ($Country_select_all as $key => $value) 
					{				
						if($val['country_id']==$value['CountryId']){
							if(in_array($val['country_id'],$all_json_same_country)){

							}else{
								$all_country.="<option value='".$value['CountryId']."'>".$value['countryName']."</option>" ;
							}
							$Country_select="<select class='form-control country_id' name='country_id_other' id='country_id_other'>
	                                            <option selected='selected' disabled='disabled'>---".SELECT_COUNTRY."---</option>                                          
	                                           ".$all_country."
	                                            </select>";
	                        array_push($all_json_same_country, $val['country_id']);
						}else{
							$Country_select.='';
						}
					}
				}
			}

			$product_price_html="";
			$product_sub_total_amount="0";
			$purchase_button="";
			$total_payable_amount="0";
			$show_product_default_price=0;
			if($getProductData['isNegotiable']=="n"){
				$product_price_html='<input type="number" min="1" class="ds_your_offer" name="your_offer" id="your_offer" value="'.$getProductData['min_price'].'" readonly="readonly">';
				$total_payable_amount=$product_sub_total_amount=$getProductData['min_price']*$getProductData['order_quantity'];
				$purchase_button='<button class="btn btn-system buy_now" name="payNow" value="payNow">'.BUY_NOW.'</button>';
				$show_product_default_price=$getProductData['min_price'].' '.CURRENCY_CODE;
			}else{
				$product_price_html='<input type="number" min="1" class="ds_your_offer" name="your_offer" id="your_offer" placeholder="'.LBL_YOUROFFER.'">';
				$purchase_button='<button type="submit" class="btn btn-system start_order_buying_req">'.START_ORDER.'</button>';
				$show_product_default_price=$getProductData['min_price'].' - '.$getProductData['max_price'].' '.CURRENCY_CODE;
			}

			$getAttechmentsImg=explode(',',$getProductData['product_image']);
			if(!empty($getProductData)){
					$selected_payment_methods = '';
					$a_select ='';
					$b_select ='';
					$c_select ='';
					if(!empty($getProductData['payment_methods'])){
						$selected_payment_methods = $getProductData['payment_methods'];
					}

				$all_country="";
				$getAllCountry=$this->db->pdoQuery("SELECT CountryId,countryName FROM tbl_country WHERE isActive=?",array('y'))->results();
				foreach ($getAllCountry as $country_value){
					$selected="";
					$all_country.=" <option value='".$country_value['CountryId']."' ".$selected.">".$country_value['countryName']."</option>";
					$Country_select="<select class='form-control country_id' name='country_id' id='country_id'>
                                        <option selected='selected' disabled='disabled'>---".SELECT_COUNTRY."---</option>                                          
                                       ".$all_country."
                                        </select>";

				}

				$fields = array(
					"%PRODUCT_STOCK%"=>$getProductData['product_stock'],
					"%MIN_ORDER_QUANTITY%"=>$getProductData['order_quantity'],
					"%ALL_COUNTRIES%"=>$Country_select,
					"%SHIPPING_METHODS%"=>$shipping_methods,
					/*"%SHIPPING_AMOUNT%"=>$shipping_amount,*/
					"%SHIPPING_AMOUNT%"=>0,
					"%PRODUCT_TITLE%"=>!empty($getProductData['product_title'])?$getProductData['product_title']:"",
					"%USERNAME%"=>!empty($getProductData['Username'])?$getProductData['Username']:"",
					"%COMPANY_NAME%"=>!empty($getProductData['company_name'])?$getProductData['company_name']:"",
					"%PAYMENT_METHODS_SELECTED%"=>$selected_payment_methods,
					"%CONTACT_PERSON_NAME%"=>!empty($getProductData['contact_person_name'])?$getProductData['contact_person_name']:"",
					"%CONTACT_NO_1%"=>!empty($getProductData['contact_no_1'])?$getProductData['contact_no_1']:"",
					"%LOCATION%"=>!empty($getProductData['location'])?$getProductData['location']:"",					
					"%IMAGE_BUYING_REQ%"=> !empty($getAttechmentsImg[0])?SITE_PRODUCT_IMG.$getAttechmentsImg[0]:SITE_UPD.'no_prodcut_img.png',
					"%UNIT_ID%"=> $getProductData['unitName'],
					"%SLUG%"=> $getProductData['product_slug'],
					"%PID%"=> $getProductData['id'],
					"%SUPPLIER_ID%"=> $getProductData['supplier_id'],
					"%BUYER_ID%"=> !empty($_SESSION['userId'])?$_SESSION['userId']:0,
					"%PRODUCT_PRICE_HTML%"=>$product_price_html,
					"%PRODUCT_SUB_TOTAL_AMOUNT%"=>$product_sub_total_amount,
					"%TOTAL_PAYABLE_AMOUNT%"=>$total_payable_amount,
					"%PURCHASE_BUTTON%"=>$purchase_button,
					"%SHOW_PRODUCT_DEFAULT_PRICE%"=>$show_product_default_price
				);
				$html = str_replace(array_keys($fields), array_values($fields), $html);
			} else{
				$html = MSG_NO_RECORDS_FOUND;	
			}
			return $html;
		}
	}

}

?>