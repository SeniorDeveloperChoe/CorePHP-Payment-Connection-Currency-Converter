<?php
class ContactUs {
	function __construct($module = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
	}

	public function getPageContent() {
		$firstName = $lastName = $email = $location = "";
		$html = (new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd"))->compile();
		$cancel_url = get_link("contact-us");

		return $html;
	}

	public function contactUsSubmit($data){
		extract($_POST);

		$objPost = new stdClass();
		$objPost->email = (!empty($email) ? strtolower(trim($email)): '');
		$objPost->contact_type = (!empty($contact_type) ? strtolower(trim($contact_type)): '');
		$objPost->message = (!empty($message) ? nl2br(trim($message)): '');
	    $objPost->firstName = (!empty($first_name) ? trim($first_name) : '');
	    $objPost->lastName = (!empty($last_name) ? trim($last_name) : '');
	    $objPost->location = (!empty($location) ? trim($location) :  '');
	    $objPost->createdDate = date('Y-m-d H:i:s');
	    $objPost->ip_address = get_ip_address();
	    $chkpoint = (!empty($chkpoint) ? $chkpoint : '');

		if(!empty($chkpoint) && $chkpoint == checkToken($chkpoint, "frmContactUs")) {
			if(preventMultilpeDbRequests('tbl_contact_us', 'createdDate', 'ip_address')) {
				if(!empty($objPost->email) && !empty($objPost->message) && !empty($objPost->firstName) && !empty($objPost->lastName) && !empty($objPost->location)) {
			        $this->db->insert('tbl_contact_us', (array)$objPost);
			        $arrayCont = array(
			        	"greetings"=>ucfirst($objPost->firstName),
						"NAME" => ucfirst(filtering($objPost->firstName)).' '.ucfirst(filtering($objPost->lastName)),
						"EMAIL" => filtering($objPost->email),
						"LOCATION" => filtering($objPost->location),
						"MESSAGE" => filtering($objPost->message)
			    	);
					sendMail($objPost->email, 'contact_mail', $arrayCont, $newsletter=false, $pref_lang=true, $this->sessUserId);
			        $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => MSG_YOUR_QUERY_SUBMITTED_ADMIN_WILL_CONTACT_SOON));
		        } else {
		        	$_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => MSG_FILL_ALL_THE_REQ_VALS));
		        }
	        } else {
	        	$_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => SOMETHING_WENT_WRONG));
	        }
    	} else {
    		$_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => SOMETHING_WENT_WRONG));
    	}
    	redirectPage(get_link("contact-us"));
	}
}
?>