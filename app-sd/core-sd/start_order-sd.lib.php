<?php
class StartOrder extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$getProductData = $getAttechmentsImg = '';
		if(!empty($this->id)){	
			$unitName="un.unit_value_".$this->curr_language." AS unitName";

			$getProductData=$this->db->pdoQuery("SELECT b.user_id AS buyer_id,c.user_id AS supplier_id,b.id,b.request_slug,b.unit_id,b.attachmemnt,b.request_title,CONCAT(u.first_name,' ',u.last_name) AS Username,c.company_name,c.logo,c.contact_person_name,c.contact_no_1,c.contact_no_2,c.location,$unitName
											FROM tbl_quotes AS q
											INNER JOIN tbl_buying_request AS b ON b.id=q.buying_request_id
											INNER JOIN tbl_users AS u ON u.id=q.user_id
											INNER JOIN tbl_unit_value AS un ON un.id=b.unit_id
											INNER JOIN tbl_company AS c ON c.user_id=q.user_id
											WHERE b.id = ? AND q.status= ?
											",array($this->id['id'],'a'))->result();
			$getAttechmentsImg=explode(',',$getProductData['attachmemnt']);
			$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$getAttechmentsImg[0], PATHINFO_EXTENSION); 
			if($ext=="pdf") { 
				$getAttechmentsImg = SITE_UPD."no_prodcut_img.png"; 
			}
			else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
				$getAttechmentsImg = SITE_UPD."no_prodcut_img.png";
			}
			else { 
				$getAttechmentsImg=!empty($getAttechmentsImg[0])?SITE_BUYING_REQUEST_IMG.$getAttechmentsImg[0]:SITE_UPD."no_prodcut_img.png"; 
			}
			if(!empty($getProductData)){
				$fields = array(
					"%REQUEST_TITLE%"=>!empty($getProductData['request_title'])?$getProductData['request_title']:"",
					"%USERNAME%"=>!empty($getProductData['Username'])?$getProductData['Username']:"",
					"%COMPANY_NAME%"=>!empty($getProductData['company_name'])?$getProductData['company_name']:"",
					"%CONTACT_PERSON_NAME%"=>!empty($getProductData['contact_person_name'])?$getProductData['contact_person_name']:"",
					"%CONTACT_NO_1%"=>!empty($getProductData['contact_no_1'])?$getProductData['contact_no_1']:"",
					"%LOCATION%"=>!empty($getProductData['location'])?$getProductData['location']:"",					
					"%IMAGE_BUYING_REQ%"=> $getAttechmentsImg,
					"%UNIT_ID%"=> $getProductData['unitName'],
					"%SLUG%"=> $getProductData['request_slug'],
					"%BID%"=> $getProductData['id'],
					"%SUPPLIER_ID%"=> $getProductData['supplier_id'],
					"%BUYER_ID%"=> $getProductData['buyer_id']
				);

				$html = str_replace(array_keys($fields), array_values($fields), $html);
			} else{
				$html = MSG_NO_RECORDS_FOUND;	
			}
			return $html;
		}
	}

	public function getAllPics($token="",$buying_REQ_ID="") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();
		$attachmemnt = '';
		if(!empty($token)){

			$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();
		}else{
			$getProductImg=$this->db->select("tbl_buying_request",array("id","attachmemnt"),array("id"=>$this->id['id']))->result();
			if(!empty($getProductImg['attachmemnt'])){
				$getImages=explode(',',$getProductImg['attachmemnt']);
			}
		}

		if(!empty($getImages)) {			
			$ImgNo=0;
			foreach($getImages AS $key => $values) {
				if(!empty($buying_REQ_ID)){					
					$Id="pro-".$buying_REQ_ID."-".$ImgNo++;
					$file_name=$values;
				}else{
					$Id=$values['id'];
					$file_name=$values['file_name'];
				}
				$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$file_name, PATHINFO_EXTENSION);
				if($ext=="pdf") { 
					$attachmemnt = getImage(DIR_UPD.'pdf.png',"153","148"); }
				else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$attachmemnt = getImage(DIR_UPD.'doc.png',"153","148"); }
				else { 
					$attachmemnt = getImage(DIR_BUYING_REQUEST_IMG.$file_name, "153", "148"); }
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

		extract($data);
		$objPost = new stdClass();
		$date = date("Y-m-d");
		$objPost->request_title=!empty($request_title)?$request_title:'';
		/*if(empty($this->id)){		*/
			$objPost->request_slug = slug($objPost->request_title);
		/*}*/
		$objPost->main_cat_id=!empty($MainCategory)?$MainCategory:'';
		
		$objPost->cat_id=!empty($Category)?$Category:'';
		
		$objPost->subcat_id=!empty($subCategory)?$subCategory:'';
		
		$objPost->req_description=!empty($req_description)?$req_description:'';
		
		$objPost->unit_id=!empty($unit_id)?$unit_id:'1';
		
		$objPost->required_quantity=!empty($required_quantity)?$required_quantity:'';

		$objPost->last_date_of_quote = (!empty($last_date_of_quote) ? date('Y-m-d H:i:s' ,strtotime(trim($last_date_of_quote))) : '');

		$objPost->template_type=!empty($template_type)?$template_type:'';
		
		$objPost->mark_urgent=!empty($mark_urgent)?$mark_urgent:'';
		
		$objPost->get_extra_quote=!empty($get_extra_quote)?$get_extra_quote:'';
		
		$objPost->created_date=$date;

		$objPost->user_id=$_SESSION['userId'];
		$objPost->attachmemnt="";
		$frmToken=!empty($frmToken)?$frmToken:'';

			$getImgs=$this->db->select("temp_product_images",array("file_name"),array("user_id"=>$_SESSION['userId'],"token"=>$frmToken))->results();
			$productImgs="";

			if(!empty($getImgs)){
				$lastImg=end($getImgs);

				foreach ($getImgs as $key => $value) {
					if($value['file_name']==$lastImg['file_name']){
						$productImgs.=$value['file_name'];
					}else{
						$productImgs.=$value['file_name'].",";
					}
				}
				$objPost->attachmemnt=$productImgs;
	     		
				$getImgs=$this->db->delete("temp_product_images",array("user_id"=>$_SESSION['userId'],"token"=>$frmToken));
			}
			
			if(!empty($this->id)){
				$oldImg=$this->db->select("tbl_buying_request",array("attachmemnt"),array("id"=>$this->id['id']))->result();
				if(!empty($oldImg['attachmemnt'])){
					$objPost->attachmemnt=!empty($objPost->attachmemnt)?$oldImg['attachmemnt'].",".$objPost->attachmemnt:$oldImg['attachmemnt'];
				}
			}

		/*Insert Product*/
			if(!empty($objPost->request_title) && !empty($objPost->main_cat_id) && !empty($objPost->cat_id) && !empty($objPost->subcat_id) && !empty($objPost->req_description) && !empty($objPost->unit_id) && !empty($objPost->required_quantity) && !empty($objPost->last_date_of_quote) && !empty($objPost->template_type) && !empty($objPost->mark_urgent) && !empty($objPost->get_extra_quote) && !empty($objPost->attachmemnt)){
				$id="";
				$objPostArray = (array) $objPost;
				if(!empty($this->id)){
					$id=$this->id['id'];
				}else{
					$id = $this->db->insert('tbl_buying_request',$objPostArray)->getLastInsertId();  
				}
				if(!empty($this->id)){					
					$this->db->update('tbl_buying_request',$objPostArray,array("id"=>$this->id['id'])); 
					$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>BUYING_REQUEST_UPDATED_SUCCESSFULLY));
					redirectPage(SITE_URL.'buying-request'); 
				}else{					
                	$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>BUYING_REQUEST_ADDED_SUCCESSFULLY));
					redirectPage(SITE_URL.'buying-request');
				}


			}else{
				$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE));
				redirectPage(SITE_URL.'add-buying-request');
			}

		/*End Insertion Saction*/
		
	}
}

?>