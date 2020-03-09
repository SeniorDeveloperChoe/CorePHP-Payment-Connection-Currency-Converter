<?php
class similarRfq extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$slug='';
		$slug = $this->db->select('tbl_buying_request', array('request_slug'), array('id'=>$this->id['id']))->result();
		$random=genrateRandom();
		$fields = array(
			"%USER_IMAGES%"=>'',
			"%SLUG%"=>$slug['request_slug'],
			"%MAIN_ID%"=>$this->id['id'],
			"%TOKEN%"=>genrateRandom()
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	public function getAllPics($token="",$buying_REQ_ID="") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();

		$attachmemnt = '';
		if(!empty($token)){

			$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();
		}
		if(!empty($getImages)) {			
			$ImgNo=0;
			foreach($getImages AS $key => $values) {
					$Id=$values['id'];
					$file_name=$values['file_name'];
				$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$file_name, PATHINFO_EXTENSION);
				if($ext=="pdf") { 
					$attachmemnt = getImage(DIR_UPD.'pdf.png',"153","148"); }
				else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$attachmemnt = getImage(DIR_UPD.'doc.png',"153","148"); }
				else { 
					$attachmemnt = SITE_BUYING_REQUEST_IMG.$file_name; }
				$active = (empty($key) ? 'active' : '');
				
				$fields = array(
					"%ID%" => $Id,
					"%IMAGES%" => $attachmemnt,
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


	public function addProduct($data) {

		$getProductData=$this->db->pdoQuery("SELECT b.* FROM tbl_buying_request AS b WHERE b.id = ?",$this->id)->result();
		extract($data);

		$objPost = new stdClass();
		$date = date("Y-m-d");
		$objPost->request_title=!empty($request_title)?$request_title:'';
		$objPost->request_slug = slug($objPost->request_title);
		$objPost->main_cat_id=!empty($getProductData['main_cat_id'])?$getProductData['main_cat_id']:'';		
		$objPost->cat_id=!empty($getProductData['cat_id'])?$getProductData['cat_id']:'';		
		$objPost->subcat_id=!empty($getProductData['subcat_id'])?$getProductData['subcat_id']:'';		
		$objPost->req_description=!empty($getProductData['req_description'])?$getProductData['req_description']:'';		
		$objPost->unit_id=!empty($getProductData['unit_id'])?$getProductData['unit_id']:'1';		
		$objPost->required_quantity=!empty($required_quantity)?$required_quantity:'';
		$objPost->last_date_of_quote = (!empty($getProductData['last_date_of_quote']) ? date('Y-m-d H:i:s' ,strtotime(trim($getProductData['last_date_of_quote']))) : '');
		$objPost->template_type=!empty($getProductData['template_type'])?$getProductData['template_type']:'';		
		$objPost->mark_urgent=!empty($mark_urgent)?$mark_urgent:'';
		$objPost->no_days=!empty($no_days)?$no_days:'0';
		$objPost->mark_urgent_price=!empty($mark_urgent_price)?$mark_urgent_price:'0';		
		$objPost->get_extra_quote='n';		
		$objPost->payment_status='y';
		$objPost->created_date=$date;
		$objPost->user_id=$_SESSION['userId'];
		$objPost->images="";
		$frmToken=!empty($frmToken)?$frmToken:'';
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
			
     		$objPost->images=$productImgs;
			$objPost->actual_images=$actualImgName;
			$getImgs=$this->db->delete("temp_product_images",array("user_id"=>$_SESSION['userId'],"token"=>$frmToken));
		}

		$deleteImgs   = $this->db->pdoQuery("select * From temp_product_images where user_id =".$_SESSION['userId']. " AND token !='$frmToken'")->results();
		if(!empty($deleteImgs)){

			foreach ($deleteImgs as $imgData) {
					$productImgs =$imgData['file_name'];
				if (file_exists(DIR_BUYING_REQUEST_IMG.$productImgs)) {
					unlink(DIR_BUYING_REQUEST_IMG.$productImgs);
				}
				$this->db->delete("temp_product_images",array("id"=>$imgData['id']));
			}

		}
		$this->db->update('tbl_buying_request',array("no_of_buyers"=>$getProductData['no_of_buyers']+1),array("id"=>$getProductData['id']));

		if(!empty($objPost->request_title) && !empty($objPost->main_cat_id) && !empty($objPost->cat_id) && !empty($objPost->subcat_id) && !empty($objPost->req_description) && !empty($objPost->unit_id) && !empty($objPost->required_quantity) && !empty($objPost->last_date_of_quote) && !empty($objPost->template_type) && !empty($objPost->mark_urgent) && !empty($objPost->get_extra_quote) && !empty($objPost->images)){
			$id="";
			$objPostArray = (array) $objPost;
			$id = $this->db->insert('tbl_buying_request',$objPostArray)->getLastInsertId();  
			$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>BUYING_REQUEST_ADDED_SUCCESSFULLY));
			redirectPage(SITE_URL.'buying-request');		
		}else{
			$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE));
			redirectPage(SITE_URL.'add-buying-request');
		}
		
	}
}

?>