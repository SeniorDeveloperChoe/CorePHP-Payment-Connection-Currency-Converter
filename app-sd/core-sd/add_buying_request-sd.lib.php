<?php
class addBuyingReq extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {


		if(!empty($_GET)){extract($_GET);}
		if(!empty($_SESSION['temp_rfq_data'])){extract($_SESSION['temp_rfq_data']);}
		$actions="";
		if(empty($_GET)){
			$actions="add";
		}
		if(empty($_GET['action'])){
			$_GET['action'] = "";
		}

		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$edit_mark_hide_cls = '';
		$chkMark_urgentYes=$required_quantity=$chkMark_urgentNo=$checkGet_extraYES=$checkGet_extraNO =$request_title=$Temp_type_1=$Temp_type_2=$Temp_type_3 = "";
		$maincatName="m.maincatName_".$this->curr_language." AS maincatName";
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$unitName="un.unit_value_".$this->curr_language." AS unitName";
		$chkFobYes=$chkFobNo=$chkPremiumYes=$chkPremiumNo=$product_id=$dynamicQuestion="";
		$action="add";
		$edit_get_ext_hide_cls = '';
		
		if(!empty($this->id)){	
			$action="edit";
			$getProductData=$this->db->pdoQuery("SELECT b.*,$maincatName,$catName,$subcatName,s.id as SubCatId,$unitName
											FROM tbl_buying_request AS b
											INNER JOIN tbl_product_main_category AS m ON m.id=b.main_cat_id
											INNER JOIN tbl_product_category AS c ON c.id=b.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=b.subcat_id 
											INNER JOIN tbl_unit_value AS un ON un.id=b.unit_id
											WHERE b.id = ?
											",array($this->id))->result();		

			$product_id=$getProductData['id'];
			$edit_mark_hide_cls = ($getProductData['mark_urgent']=='y') ? "": 'hide';
			$edit_get_ext_hide_cls = ($getProductData['get_extra_quote']=='y') ? "": 'hide';
				if($getProductData['mark_urgent']=='y'){
					$chkMark_urgentYes="checked";
				}else{
					$chkMark_urgentNo="checked";
				}

				if($getProductData['get_extra_quote']=='y'){
					$checkGet_extraYES = "checked";
				}else{
					$checkGet_extraNO = "checked";
				}

				if($getProductData['template_type']=='1'){
					$Temp_type_1 = "selected";
				}else if($getProductData['template_type']=='2'){
					$Temp_type_2 = "selected";
				}
				else{
					$Temp_type_3 = "selected";
				}
				$request_title = $getProductData['request_title'];
				$required_quantity = $getProductData['required_quantity'];
				
		}
		else if((!empty($_GET) && $_GET['action']!="rfq" && empty($this->id)) || (!empty($_SESSION['temp_rfq_data']) && $_GET['action']!="rfq")) {

			$request_title = !empty($title)?$title:"";
			$required_quantity = !empty($req_amount)?$req_amount:"";
			if(!empty($template_type) && $template_type=='1'){
					$Temp_type_1 = "selected";
				}else if(!empty($template_type) && $template_type=='2'){
					$Temp_type_2 = "selected";
				}
				else{
					$Temp_type_3 = "selected";
				}
			
		}
		$maincat="maincatName_".$this->curr_language." AS maincatName";
		$cat="categoryName_".$this->curr_language." AS catName";
		$subcat="subcatName_".$this->curr_language." AS subCatName";
		$unit="unit_value_".$this->curr_language." AS unitName";



		/*For Similar RFQ*/

			if($_GET['action']=="rfq" && !empty($_GET['slug'])){
				$similar_rfq_data=$this->db->pdoQuery("SELECT b.*,$maincatName,$catName,$subcatName,s.id as SubCatId,$unitName
											FROM tbl_buying_request AS b
											INNER JOIN tbl_product_main_category AS m ON m.id=b.main_cat_id
											INNER JOIN tbl_product_category AS c ON c.id=b.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=b.subcat_id 
											INNER JOIN tbl_unit_value AS un ON un.id=b.unit_id
											WHERE request_slug = ?
											",array($_GET['slug']))->result();	
				$getProductData['maincatName']=$similar_rfq_data['maincatName'];
				$getProductData['catName']=$similar_rfq_data['catName'];
				$getProductData['subCatName']=$similar_rfq_data['subCatName'];
				$getProductData['unitName']=$similar_rfq_data['unitName'];
				$getProductData['req_description']=$similar_rfq_data['req_description'];
				$required_quantity=$similar_rfq_data['required_quantity'];

			}

		/*END For Similar RFQ*/




		$mainCategory=$Category=$subCategory="";

			$main_cat_data=$this->db->pdoQuery("
				SELECT mc.id,$maincat FROM tbl_product_main_category as mc
				JOIN tbl_product_category as pc ON( pc.maincatID = mc.id) AND pc.isActive ='y'
				JOIN tbl_product_subcategory as ps ON( ps.catId = pc.id) AND ps.isActive ='y'
				WHERE mc.isActive=? group by mc.id ORDER BY maincatName_".$this->curr_language." ASC",array('y'))->results();
			foreach ($main_cat_data as $key => $value) {
				$selected="";
				if (!empty($this->id) || $_GET['action']=="rfq") {
					if($getProductData['maincatName']==$value['maincatName'])	{
						$selected="selected";
					}
				}
				$mainCategory.="<option value='".$value['id']."' ".$selected.">".$value['maincatName']."</option>";
			}

		/*End Main Category secation*/


		/*Get Category*/

		if (!empty($this->id) || $_GET['action']=="rfq") {
			$cat_data=$this->db->pdoQuery("SELECT id,$cat FROM tbl_product_category WHERE isActive=? ORDER BY categoryName_".$this->curr_language." ASC",array('y'))->results();

			foreach ($cat_data as $key => $value) {
				$selected="";				
					if($getProductData['catName']==$value['catName'])	{
						$selected="selected";					
				} 
				$Category.="<option value='".$value['id']."' ".$selected.">".$value['catName']."</option>";
			}
		}

		/*End Category secation*/


		/*Get Sub Category*/

		if (!empty($this->id) || $_GET['action']=="rfq") {
			$sub_cat_data=$this->db->pdoQuery("SELECT id,$subcat FROM tbl_product_subcategory WHERE isActive=? ORDER BY subcatName_".$this->curr_language." ASC",array('y'))->results();
			foreach ($sub_cat_data as $key => $value) {
				$selected="";				
					if($getProductData['subCatName']==$value['subCatName'])	{
						$selected="selected";					
				}
				$subCategory.="<option value='".$value['id']."' ".$selected.">".$value['subCatName']."</option>";
			}
		}

		/*End Sub Category secation*/



		/*Get Unite Values*/

			$unit_data=$this->db->pdoQuery("SELECT id,$unit FROM tbl_unit_value WHERE isActive=? Order by id",array('y'))->results();
			$unitValues='';
			foreach ($unit_data as $key => $value) {
				$selected="";
				if (!empty($this->id) || $_GET['action']=="rfq") {
					if($getProductData['unitName']==$value['unitName'])	{
						$selected="selected";
					}
				} else if(!empty($_GET && empty($this->id)) || (!empty($_SESSION['temp_rfq_data']) && $_GET['action']!="rfq")){
					if($_GET['action']!="rfq" && !empty($unit_id) && $unit_id==$value['id'])	{
						$selected="selected";
					}
				}
				$unitValues.="<option value='".$value['id']."' ".$selected.">".$value['unitName']."</option>";
			}

		/*End Unit Value saction*/
		$random=genrateRandom();
	
		/*print_r($_GET);exit();*/
		$first_progressbar = $second_progressbar = $third_progressbar = "";
		$hide_fair_part = (!empty($_GET) || !empty($_SESSION['temp_rfq_data'])) ? '':'hide';

		if(!empty($this->id)){
			$first_progressbar = $second_progressbar = $third_progressbar = "";
		}
		if(empty($this->id) && (!empty($_GET) || !empty($_SESSION['temp_rfq_data']))){
			$first_progressbar =  "";
			$second_progressbar = $third_progressbar = "hide";
		}
		if(empty($this->id) && (empty($_GET) && empty($_SESSION['temp_rfq_data']))){			
			$first_progressbar = $second_progressbar = $third_progressbar = "hide";
		}

		if($actions=="add" && empty($_SESSION['temp_rfq_data'])){
			$first_progressbar = $second_progressbar = $third_progressbar = "hide";
		}

		
		$leftPanel=$this->getleftPanel();

		$expireDate = '-';
		$urgent_date_chk = !empty($getProductData['urgent_date']) ? $getProductData['urgent_date'] : '';
		$checkIsUrgentRequest = checkIsUrgentRequest($urgent_date_chk);

		if ($checkIsUrgentRequest) {
			$expireDate = date('Y-m-d', strtotime($getProductData['urgent_date']));
		}

		$fields = array(
			"%LEFT_PANEL%"=>$leftPanel,
			"%BUYING_REQUEST_HEADING%" => !empty($this->id)?EDIT_BUYING_REQUEST:ADD_NEW_BUYING_REQUEST,
			"%SUBMIT_BTN_TXT%" => !empty($this->id)?UPDATE_BUYING_REQUEST:ADD_BUYING_REQUEST,
			"%FIRST_PROGRESSBAR%"=>$first_progressbar,
			"%SECOND_PROGRESSBAR%"=>$second_progressbar,
			"%THIRD_PROGRESSBAR%"=>$third_progressbar,
			"%HIDE_FAIR_PART%"=>$hide_fair_part,
			"%ID%"=>$this->id,
			"%MAIN_CATEGORY%"=>$mainCategory,
			"%CATEGORY%"=>$Category,
			"%SUB_CATEGORY%"=>$subCategory,
			"%UNIT_VALUES%"=>$unitValues,
			"%REQUEST_TITLE%"=>!empty($request_title)?$request_title:"",
			"%REQUIRED_QUANTITY%"=>!empty($required_quantity)?$required_quantity:"",
			"%REQ_DESCRIPTION%"=>!empty($getProductData['req_description'])?$getProductData['req_description']:"",
			"%LAST_DATE_OF_QUOTE%"=>((!empty($this->id)) ? date('d-m-Y', strtotime($getProductData['last_date_of_quote'])) : ''),
			"%ORDER_QUANTITY%"=>!empty($getProductData['order_quantity'])?$getProductData['order_quantity']:"",
			"%PRODUCT_LOCATION%"=>!empty($getProductData['product_location'])?$getProductData['product_location']:"",
			"%PRODUCT_TAGS%"=>!empty($getProductData['product_tags'])?$getProductData['product_tags']:"",
			"%SUPPLY_ABILITY%"=>!empty($getProductData['supply_ability'])?$getProductData['supply_ability']:"",
			"%PRODUCT_DESCRIPTION%"=>!empty($getProductData['product_description'])?$getProductData['product_description']:"",
			"%CHKMARK_URGENTYES%"=>$chkMark_urgentYes,
			"%CHKMARK_URGENTNO%"=>($action!="add") ? $chkMark_urgentNo: 'checked',
			"%CHECKGET_EXTRAYES%"=>$checkGet_extraYES,
			"%CHECKGET_EXTRANO%"=>($action!="add") ? $checkGet_extraNO: 'checked',
			"%HIDE_URGENT_OPTION%"=>(!empty($this->id) && $getProductData['mark_urgent']=="y")?"hide":"",
			"%HIDE_EXTRA_QUOTE_OPTION%"=>(!empty($this->id) && $getProductData['get_extra_quote']=="y")?"hide":"",
			"%TEMP_TYPE_1%"=>$Temp_type_1,
			"%TEMP_TYPE_2%"=>$Temp_type_2,
			"%TEMP_TYPE_3%"=>$Temp_type_3,
			"%USER_IMAGES%"=>($action!="add")?$this->getAllPics('',$this->id) :'',
			"%USER_IMAGES_FILES%"=>($action!="add")?$this->getAllPics('',$this->id, 'images', 'containimg'):'',
			"%ACTION%"=>$action,
			"%NO_DAYS%"=>!empty($getProductData['no_days'])?$getProductData['no_days']:"0",
			"%TOTAL%"=>!empty($getProductData['mark_urgent_price'])?$getProductData['mark_urgent_price']:"0",
			"%NO_EXT_QUOTE%"=>!empty($getProductData['no_ext_quote'])?$getProductData['no_ext_quote']:"0",
			"%TOTAL_EXTRA_QUOTE_PRICE%"=>!empty($getProductData['total_extra_quote_price'])?$getProductData['total_extra_quote_price']:"0",
			"%TOKEN%"=>genrateRandom(),
			"%IMG_TOKEN%"=>genrateRandom(),
			"%MARK_HIDE_CLS%"=>($action=="add")? 'hide':$edit_mark_hide_cls,
			"%GET_EXT_HIDE_CLS%"=>($action=="add")? 'hide':$edit_get_ext_hide_cls,
			"%TOTAL_QUOTES%"=>!empty($getProductData['total_quotes']) ? $getProductData['total_quotes'] : 0,
			"%EXPIRE_URGENT_DATE%"=> $expireDate,

		);
		unset($_SESSION['temp_rfq_data']);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	public function getAllPics($token="",$buying_REQ_ID="", $field ='attachmemnt', $class ='') {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();
		$attachmemnt = '';
		if(!empty($token)){

			$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();
		}else{

			$getProductImg=$this->db->select("tbl_buying_request",array("id",$field),array("id"=>$this->id))->result();
			if(!empty($getProductImg[$field])){
				$getImages=explode(',',$getProductImg[$field]);
			}
		}

		if(!empty($getImages)) {
			$ImgNo=0;
			foreach($getImages AS $key => $values) {
				if(!empty($buying_REQ_ID)){
					$Id=$field."-".$buying_REQ_ID."-".$ImgNo++;
					$file_name=$values;
				}else{
					$Id=$values['id'];
					$file_name=$values['file_name'];
				}
				$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$file_name, PATHINFO_EXTENSION);
				if($ext=="pdf") { 
					$attachmemnt = SITE_UPD.'pdf.png';}
				else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$attachmemnt = SITE_UPD.'doc.png';}
				else { 
					$attachmemnt = SITE_BUYING_REQUEST_IMG.$file_name; }
				$active = (empty($key) ? 'active' : '');
				
				$fields = array(
					"%ID%" => $Id,
					"%IMAGES%" => $attachmemnt,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active,
					"%CONTAINING_CLASS%" =>$class
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
		}		
		return $final_content;
	}
	public function getAllPicsAfterDelete($buying_REQ_ID="", $field ='attachmemnt', $class ='') {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();
		$attachmemnt = '';
		if(!empty($token)){

			$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();
		}else{
			
			$getProductImg=$this->db->select("tbl_buying_request",array("id",$field),array("id"=>$buying_REQ_ID))->result();
			if(!empty($getProductImg[$field])){
				$getImages=explode(',',$getProductImg[$field]);
			}
		}

		if(!empty($getImages)) {
			$ImgNo=0;
			foreach($getImages AS $key => $values) {
				if(!empty($buying_REQ_ID)){
					$Id=$field."-".$buying_REQ_ID."-".$ImgNo++;
					$file_name=$values;
				}else{
					$Id=$values['id'];
					$file_name=$values['file_name'];
				}
				$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$file_name, PATHINFO_EXTENSION);
				if($ext=="pdf") { 
					$attachmemnt = SITE_UPD.'pdf.png';}
				else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$attachmemnt = SITE_UPD.'doc.png';}
				else { 
					$attachmemnt = SITE_BUYING_REQUEST_IMG.$file_name; }
				$active = (empty($key) ? 'active' : '');
				
				$fields = array(
					"%ID%" => $Id,
					"%IMAGES%" => $attachmemnt,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active,
					"%CONTAINING_CLASS%" =>$class
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
		}		
		return $final_content;
	}
	public function getSinglePics($lastImgId="0") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();

		$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('id'=>$lastImgId), 'ORDER BY id ASC')->results();
		if(!empty($getImages)) {
			$ImgNo=0;
			foreach($getImages AS $key => $values) {
				$Id=$values['id'];
				$file_name=$values['file_name'];
			
				$active = '';
				$fields = array(
					"%ID%" => $Id,
					"%IMAGE_ID%" => $file_name,
					/*"%IMAGES%" => getImage(DIR_PRODUCT_IMG.$file_name, "153", "148"),*/
					"%IMAGES%" => SITE_BUYING_REQUEST_IMG.$file_name,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active,
					"%CONTAINING_CLASS%" =>'containimg'
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		}
		return $final_content;
	}


	public function addProduct($data) {

		extract($data);
		//dd($data);
		///echo "<pre>";print_r($data);exit;
//		$this->id=$id;

		$objPost = new stdClass();
		$date = date("Y-m-d H:i:s");

		$objPost->request_title=!empty($request_title)?$request_title:'';
		
		if(!empty($this->id)){
			$objPost->request_slug = slug($objPost->request_title).'-'.$this->id;
			

		}
		$objPost->main_cat_id=!empty($MainCategory)?$MainCategory:'';
		
		$objPost->cat_id=!empty($Category)?$Category:'';
		
		$objPost->subcat_id=!empty($subCategory)?$subCategory:'';
		
		$objPost->req_description=!empty($req_description)?$req_description:'';
		
		$objPost->unit_id=!empty($unit)?$unit:'';
		
		$objPost->required_quantity=!empty($required_quantity)?$required_quantity:'';

		$objPost->last_date_of_quote = (!empty($last_date_of_quote) ? date('Y-m-d H:i:s' ,strtotime(trim($last_date_of_quote))) : '');

		$objPost->template_type=!empty($template_type)?$template_type:'';
		
		$objPost->mark_urgent=!empty($mark_urgent)?$mark_urgent:'';
		
		$objPost->get_extra_quote=!empty($get_extra_quote)?$get_extra_quote:'';
		$objPost->no_days=!empty($no_days)?$no_days:'0';
		$objPost->mark_urgent_price=!empty($mark_urgent_price)?$mark_urgent_price:'0';
		$objPost->total_extra_quote_price=!empty($total_extra_quote_price)?$total_extra_quote_price:'0';
		$objPost->no_ext_quote=!empty($no_ext_quote)?$no_ext_quote:'0';
		
		$objPost->user_id=$_SESSION['userId'];
		$objPost->attachmemnt="";
		$frmToken=!empty($frmToken)?$frmToken:'';
		$frmImgToken=!empty($frmImgToken)?$frmImgToken:'';
		if(!empty($this->id)){
			$alrady_data = $this->db->select('tbl_buying_request', array('no_ext_quote', 'no_days','payment_status','get_extra_quote','pay_for'), array('user_id'=>$this->sessUserId,"id"=>$this->id))->result();
			if($get_extra_quote == 'y' || $mark_urgent == 'y'){
				$objPost->pay_for="b";
				$objPost->payment_status = 'n';
			}else{
				$objPost->pay_for="n";
				$objPost->payment_status = 'y';
			}
		}else{

			if($get_extra_quote == 'y' || $mark_urgent == 'y'){
				$objPost->pay_for="b";
				$objPost->payment_status = 'n';
			}else{
				$objPost->pay_for="n";
				$objPost->payment_status = 'y';
			}

			$objPost->total_quotes = NO_QUOTES_BUYING_REQUEST;
		}
		if($objPost->mark_urgent == 'y'){
			//$objPost->urgent_date = date('Y-m-d H:i:s');
		}/*else{
			$objPost->urgent_date = '0000-00-00 00:00:00';	
		}*/
			$getImgs=$this->db->select("temp_product_images",array("file_name","actual_file_name"),array("user_id"=>$_SESSION['userId'],"token"=>$frmImgToken))->results();
			$productImgs="";
			$actualImgName="";

			if(!empty($getImgs)){
				$lastImg=end($getImgs);

				foreach ($getImgs as $key => $value) {
					if($value['file_name']==$lastImg['file_name']){
						$productImgs.=$value['file_name'];
						$actualImgName.=$value['actual_file_name'];
					}else{
						$productImgs.=$value['file_name'].",";
						$actualImgName.=$value['actual_file_name'].",";
					}
				}
				$objPost->images=$productImgs;
				$objPost->actual_images=$actualImgName;
	     		
				$getImgs=$this->db->delete("temp_product_images",array("user_id"=>$_SESSION['userId'],"token"=>$frmImgToken));
			}

			

			$getImgs=$this->db->select("temp_product_images",array("file_name","actual_file_name"),array("user_id"=>$_SESSION['userId'],"token"=>$frmToken))->results();
			$productImgs="";
			$actualImgName="";

			if(!empty($getImgs)){
				$lastImg=end($getImgs);

				foreach ($getImgs as $key => $value) {
					if($value['file_name']==$lastImg['file_name']){
						$productImgs.=$value['file_name'];
						$actualImgName.=$value['actual_file_name'];
					}else{
						$productImgs.=$value['file_name'].",";
						$actualImgName.=$value['actual_file_name'].",";
					}
				}
				$objPost->attachmemnt=$productImgs;
				$objPost->actual_attechment=$actualImgName;
	     		
	     		$getImgs=$this->db->delete("temp_product_images",array("user_id"=>$_SESSION['userId'],"token"=>$frmToken));
			}

			$deleteImgs   = $this->db->pdoQuery("select * From temp_product_images where user_id =".$_SESSION['userId'])->results();
			
			if(!empty($deleteImgs)){

				foreach ($deleteImgs as $imgData) {
						$productImgs =$imgData['file_name'];
					if (file_exists(DIR_BUYING_REQUEST_IMG.$productImgs)) {
						unlink(DIR_BUYING_REQUEST_IMG.$productImgs);
					}
					$this->db->delete("temp_product_images",array("id"=>$imgData['id']));
				}

			}

			if(!empty($this->id)){
				$oldImg=$this->db->select("tbl_buying_request",array("images","actual_images", "attachmemnt","actual_attechment"),array("id"=>$this->id))->result();
				if(!empty($oldImg['images'])){
					$objPost->images=!empty($objPost->images)?$oldImg['images'].",".$objPost->images:$oldImg['images'];
					$objPost->actual_images=!empty($objPost->actual_images)?$oldImg['actual_images'].",".$objPost->actual_images:$oldImg['actual_images'];
				}
				if(!empty($oldImg['attachmemnt'])){
					$objPost->attachmemnt=!empty($objPost->attachmemnt)?$oldImg['attachmemnt'].",".$objPost->attachmemnt:$oldImg['attachmemnt'];
					$objPost->actual_attechment=!empty($objPost->actual_attechment)?$oldImg['actual_attechment'].",".$objPost->actual_attechment:$oldImg['actual_attechment'];
				}
			}


			if(!empty($objPost->request_title) && !empty($objPost->main_cat_id) && !empty($objPost->cat_id) && !empty($objPost->subcat_id) && !empty($objPost->req_description) && !empty($objPost->unit_id) && !empty($objPost->required_quantity) && !empty($objPost->last_date_of_quote) && !empty($objPost->template_type) && !empty($objPost->mark_urgent) && !empty($objPost->get_extra_quote) ){
				$id="";
				

				if($get_extra_quote == 'n'){
					$objPost->no_ext_quote="0";
					$objPost->total_extra_quote_price="0";
				}	
				if($mark_urgent == 'n'){
					$objPost->mark_urgent_price="0";
					$objPost->no_days="0";
				}

				if(!empty($this->id)){
					$objPostArray = (array) $objPost;
					$id=$this->id;
				}else{
					$objPost->created_date=$date;
					$objPostArray = (array) $objPost;
					$id = $this->db->insert('tbl_buying_request',$objPostArray)->getLastInsertId();  
					$request_slug = slug($objPost->request_title).'-'.$id;

					$this->db->update('tbl_buying_request',["request_slug" => $request_slug],array("id"=>$id));
					
				}
				if($mark_urgent == 'y'){
					$userName=$this->db->select("tbl_users",array("first_name","last_name"),array("id"=>$_SESSION['userId']))->result();
					$notifyConstant =str_replace(
							array("#PRODUCT_TITLE#", "#USER_NAME#"),
							array($objPost->request_title, $userName['first_name'].' '.$userName['last_name']),
							MARK_PROJECT_URGENT
					);

					$notifyUrl = 'manage_buying_request-sd?notiId='.$id;
					add_admin_notification($notifyConstant, $notifyUrl);
				}
				if($get_extra_quote == 'y'){
					$userName=$this->db->select("tbl_users",array("first_name","last_name"),array("id"=>$_SESSION['userId']))->result();
					$notifyConstant =str_replace(
							array("#PRODUCT_TITLE#", "#USER_NAME#"),
							array($objPost->request_title, $userName['first_name'].' '.$userName['last_name']),
							GET_EXTRA_QUOTE_ON_REQUEST
					);

					$notifyUrl = 'manage_buying_request-sd?notiId='.$id;
					add_admin_notification($notifyConstant, $notifyUrl);
					
				}

				if(!empty($this->id)){	
					$this->db->update('tbl_buying_request',$objPostArray,array("id"=>$this->id)); 
					$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>BUYING_REQUEST_UPDATED_SUCCESSFULLY));
				}else{					
                	$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>BUYING_REQUEST_ADDED_SUCCESSFULLY));
				}
				$main_id = (!empty($this->id)) ? base64_encode(base64_encode($this->id)) :base64_encode(base64_encode($id));
				if($get_extra_quote == 'n' && $mark_urgent == 'n'){
					redirectPage(SITE_URL.'buying-request');					
					/*return "insert";*/
				} else{
					if(!empty($this->id)){
						
						if(($alrady_data['no_ext_quote'] != $no_ext_quote) && ($alrady_data['no_days'] == $no_days)){
							redirectPage(SITE_URL.'buying-request');
						} 
						else if(($alrady_data['no_days'] != $no_days) && ($alrady_data['no_ext_quote'] == $no_ext_quote)){
							redirectPage(SITE_URL.'buying-request');
						} else if(($alrady_data['no_ext_quote'] != $no_ext_quote) && ($alrady_data['no_days'] != $no_days)){
							redirectPage(SITE_URL.'buying-request');
						} else{
							$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>BUYING_REQUEST_ADDED_SUCCESSFULLY));
							redirectPage(SITE_URL.'buying-request');
						}
					}else{
							redirectPage(SITE_URL.'buying-request');
					}
				}
			}else{
				$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE));
				redirectPage(SITE_URL.'add-buying-request');
				/*return "fill_all_value";*/
			}

		/*End Insertion Saction*/
		
	}
}

?>