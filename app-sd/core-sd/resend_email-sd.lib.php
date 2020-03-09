<?php
class ResendEmail {
	function __construct($module = "", $id = 0, $token = "",$reffToken="") 
	{
		foreach ($GLOBALS as $key => $values) 
		{
			$this->$key = $values;

		}
		$this->module = $module;
		$this->id = $id;
		
	}
	public function getPageContent() 
	{

		$main_content = new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd");
		$main_content = $main_content->compile();


        //$content = str_replace(array_keys($fields), array_values($fields), $main_content);
       return sanitize_output($main_content);
	}


	public function SignupSubmit($data)
	{
		extract($data);

		 if (isset($email) && isset($password)) {

            $selQuery="";
            $moderatorActive="";
            $selQuery = $this->db->select("tbl_users", array("id", "email_veri_status", "user_type", "first_name", "last_name","email", "isActive"), array("email" => $email, "password" => md5($password)));
            
            if ($selQuery->affectedRows() < 1) {
                 $selQuery = $this->db->select("tbl_moderators", array("id", "supplier_id", "first_name", "email", "isActive"), array("email" => $email, "password" => md5($password)));
                   if ($selQuery->affectedRows() >= 1) {
                        $moderatorActive="y";
                   }
            }

            
            if ($selQuery->affectedRows() >= 1) {
                $result = $selQuery->result();
                if ($result != false) {
                    extract($result);

                     if (isset($isRemember) && $isRemember == 'y') {
                        setcookie('email',($email), time() + 3600 * 24 * 30, '/');
                        setcookie('password',custom_encoder($password), time() + 3600 * 24 * 30, '/');
                        setcookie('rememberme', 'y', time() + 3600 * 24 * 30, '/');
                    } else {
                        setcookie('email', '', time() - 3600, '/');
                        setcookie('password', '', time() - 3600, '/');
                        setcookie('rememberme', '', time() - 3600, '/');
                    }

                    if ($email_veri_status == "n" && $isActive == "n") {
                         $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>USER_EMAIL_NOT_ACTIVATED));
                        redirectPage(SITE_URL);
                    }else if ($isActive == "n" && $email_veri_status == "y" ) {
                         $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>MSG_USER_ACCOUNT_DEACTIVE." ".SITE_NM.". ".PLEASE_CONTACT_TO_ADMIN_OF ." ".SITE_NM." ".MSG_TO_ACTIVATE));
                        redirectPage(SITE_URL);
                    } else {

                        if( $moderatorActive=="y"){
                            $_SESSION["userId"] = $supplier_id;
                            $_SESSION["moderatorId"] = $id;
                            $_SESSION["user_type"] = "3";
                        }else{
                            $_SESSION["userId"] = $id;
                            $_SESSION["moderatorId"] = 0;
                            $_SESSION["user_type"] = $user_type;
                        }
                        $objPost = new stdClass();
                        $objPost->userId=$id;
                        $objPost->login_type='e';
                        $objPost->login_time=date('Y-m-d H:i:s');
                        $objPost->ipAddress=get_ip_address();

                        $objPostArray = (array) $objPost;
                        $id = $this->db->insert('tbl_login_history',$objPostArray)->getLastInsertId();

                        /*Redirect to Moderator first module*/
                        if( $moderatorActive=="y"){
                            $getModeratorFirstModule=$this->db->pdoQuery("SELECT m.module_id,f.page_nm FROM tbl_moderators_permission AS m INNER JOIN tbl_front_modules as f on f.id=m.module_id where m.user_id = ?ORDER BY m.module_id ASC",array($_SESSION["moderatorId"]))->result();
                            if(!empty($getModeratorFirstModule)){
                                $_SESSION["toastr_message"] =disMessage(array('type'=>'suc','var'=>YOU_ARE_SUCCESSFULLY_LOGIN));
                                redirectPage(get_link($getModeratorFirstModule['page_nm']));
                            }else{

                            }
                        }
                        /*End Redirect to Moderator first module*/

                        $_SESSION["toastr_message"] =disMessage(array('type'=>'suc','var'=>YOU_ARE_SUCCESSFULLY_LOGIN));
                        
                         if(isset($_SESSION['current_url'])){
                            redirectPage($_SESSION['current_url']);
                        }else{
                            if ($_SESSION["user_type"] != 1) {
                                if (checkCompleteProfile($_SESSION["userId"]) !=100) {
                                    redirectPage(SITE_URL.'user-profile');
                                }
                            }
							redirectPage(SITE_URL.'user-dashboard');
                            //redirectPage(SITE_URL);
                        }
                    }
                } 

            } 
            else {
                $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>INVALID_USERNAME_PASSWORD_NOT_MATCH));
                redirectPage(SITE_URL."login");
            }
        } else {
            $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE_BEFORE_LOGIN_IN));
        }

	}
}
?>
