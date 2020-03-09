<?php
class ContactSupplier extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$getProductData=$this->db->pdoQuery("SELECT p.product_slug,p.product_title,p.user_id FROM tbl_products AS p 
											WHERE p.id=?",array($this->id))->result();
		extract($getProductData);
		$fields=array(
					"%TOKEN%"=>genrateRandom(),
					"%RECEIVER_ID%"=>$user_id,
					"%PRODUCT_SLUG%"=>$product_slug
				);
		$html = str_replace(array_keys($fields), array_values($fields), $html);	
		return $html;
	}
	public function getAllPics($token="",$productId="") {
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
					$attachmemnt = SITE_UPD.'pdf.png'; }
				else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$attachmemnt = SITE_UPD.'doc.png'; }
				else { 
					$attachmemnt = SITE_MSG_DOCS.$file_name; }

				$active = (empty($key) ? 'active' : '');
				$fields = array(
					"%ID%" => $Id,
					"%IMAGES%" => $attachmemnt,
					"%ACTIVE%" => $active
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
		}		
		return $final_content;
	}


	public function sendInquiry($data,$productId){
		extract($data);
		$objPost = new stdClass();
		

		$objPost->msg=!empty($description)?filtering($description):"";
		$objPost->sender=$_SESSION['userId'];
		$objPost->receiver=!empty($receiver)?$receiver:"";
		$objPost->product_id=$productId;
		$objPost->ipAddress=get_ip_address();
		$objPost->created_date=date('Y-m-d H:i:s');


		/*Get Attachments section*/

		$objPost->db_file_name=$objPost->actual_file_name="";

			$frmToken=!empty($frmToken)?$frmToken:'';

			$getImgs=$this->db->select("temp_product_images",array("file_name","actual_file_name"),array("user_id"=>$_SESSION['userId'],"token"=>$frmToken))->results();
			$db_name_attachment=$actual_name_attachments="";

			if(!empty($getImgs)){
				$objPost->msg_type="b";
				$lastImg=end($getImgs);

				foreach ($getImgs as $key => $value) {
					if($value['file_name']==$lastImg['file_name']){
						$db_name_attachment.=$value['file_name'];
						$actual_name_attachments.=$value['actual_file_name'];
					}else{
						$db_name_attachment.=$value['file_name'].",";
						$actual_name_attachments.=$value['actual_file_name'].",";
					}
				}
				$objPost->db_file_name=$db_name_attachment;
				$objPost->actual_file_name=$actual_name_attachments;
				$getImgs=$this->db->delete("temp_product_images",array("user_id"=>$_SESSION['userId'],"token"=>$frmToken));
			}else{
				$objPost->msg_type="t";
			}

		/*End Get Attachments Section*/



		$objPostArray = (array) $objPost;

		$lastinserted_id=$this->db->insert("tbl_messages",$objPostArray);
		$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>YOUR_INQUIRY_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
		redirectPage(SITE_URL.'product-detail/'.$product_slug);



	}
}
?>