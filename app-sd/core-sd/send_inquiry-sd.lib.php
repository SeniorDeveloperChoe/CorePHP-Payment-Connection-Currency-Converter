<?php
class SendInquiry extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		$getProductData=$this->db->pdoQuery("SELECT p.product_slug,p.product_title,c.company_name,p.user_id FROM tbl_products AS p 
											INNER JOIN tbl_company AS c ON c.user_id=p.user_id
											WHERE p.id=?",array($this->id))->result();
		extract($getProductData);
		$fields=array(
					"%PRODUCT_TITLE%"=>$product_title,
					"%COMPANY_NAME%"=>$company_name,
					"%RECEIVER_ID%"=>$user_id,
					"%PRODUCT_SLUG%"=>$product_slug
				);

		$html = str_replace(array_keys($fields), array_values($fields), $html);

		return $html;
	}

	public function sendInquiry($data){
		extract($data);

		$objPost = new stdClass();
		$quentity=!empty($quentity)?$quentity:"";
		$description=!empty($description)?$description:"";
		$company_name=!empty($company_name)?$company_name:"";
		$product_title=!empty($product_title)?$product_title:"";
		$receiverId=!empty($receiver)?$receiver:"";
		$product_slug=!empty($product_slug)?$product_slug:"";

		$productId = getTableValue("tbl_products", "id", array("product_slug"=>$product_slug));
		$supplierId = getTableValue("tbl_products", "user_id", array("product_slug"=>$product_slug));

		$companyId = getTableValue("tbl_company", "id", array("company_name"=>$company_name));

		$getUser = $this->db->pdoQuery("SELECT first_name, last_name FROM tbl_users WHERE id=?",array($_SESSION['userId']))->result();

		$msg_place_quote =str_replace(
						array("#USERNAME#", "#PRODUCT_TITLE#"),
						array($getUser['first_name'].' '.$getUser['last_name'], $product_title),
						PRIVATE_INQUIRY_BY_BUYER
			);
		$notifyUrl = 'private-request';
		add_notification($msg_place_quote, $supplierId, $notifyUrl);

		$inquiryData = [
			'user_id' => $_SESSION['userId'],
			'company_id' => $companyId,
			'product_id' => $productId,
			'required_quantity' => $quentity,
			'description' => $description,
		];
		$this->db->insert("tbl_inquiry",$inquiryData);
		$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>YOUR_INQUIRY_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
		redirectPage(SITE_URL.'product-detail/'.$product_slug);
	}
}
?>