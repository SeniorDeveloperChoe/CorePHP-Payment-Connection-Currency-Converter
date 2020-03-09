<?php
class Signup {
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
		return $main_content;
	}
	

	public function SignupSubmit($data)
	{
		extract($_POST);


		/******GET USER Details******/		

		$objPost = new stdClass();

		$objPost->first_name = (!empty($firstName) ? trim($firstName) : '');

		$objPost->last_name = (!empty($lastName) ? trim($lastName) : '');
		
		$objPost->user_type = (!empty($utype) ? trim($utype) : '');

		$objPost->email = (!empty($email) ? strtolower(trim($email)) : '');

		$objPost->user_location=(!empty($location)?(trim($location)):'');

		$objPost->password = (!empty($password) ? trim($password) : '');		

		$objPost->user_slug = slug($objPost->first_name);
		$objPost->loginWith = 'email';


		if($objPost->user_type=="buyer"){
			$objPost->user_type='1';
		}elseif($objPost->user_type=="supplier"){
			$objPost->user_type='2';
		}elseif($objPost->user_type=="Both"){
			$objPost->user_type='3';
		}
		

        /******AD UNIQUE ID GENERATION PROCESS IN AD DETAIL PHASE******/
        
        $adUniqueId   = $this->db->pdoQuery('select MAX(id) as uid From tbl_users')->result();
        $unique_user_id = 'UF000001';
        $len          = $adUniqueId['uid'] + 1;
        $clen         = strlen($len);
        if ($clen > 6) {
            $diff    = $clen - 6;
            $suffixd = '';
            for ($i = 0; $i < $diff; $i++) {
                $suffixd .= '0';
            }
            $unique_user_id .= $suffixd;
        }
        $objPost->unique_user_id = substr_replace($unique_user_id, $len, -$clen);
        
        		
		
			if(!empty($objPost->first_name) && !empty($objPost->last_name) && !empty($objPost->email) && !empty($objPost->user_location) && !empty($objPost->password)) 
			{

				if($data['captcha'] != $_COOKIE['digit']) {
					    $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>THE_CAPTCHA_CODE_ENTERED_WAS_INCORRECT));
			        	RedirectPage(SITE_URL.'SignUp');
				    	session_destroy();
				} else{	
					
					 $isExist = $this->db->select('tbl_users',array('id'),array('email'=>$objPost->email), ' LIMIT 1');
					
					 			if($isExist->affectedRows() > 0) {

						         	$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>MSG_EMAIL_ALREADY_EXISTS));

						           	redirectPage(SITE_URL."SignUp");
						        } else {

									$objPost->created_date=date('Y-m-d H:i:s'); 
									$objPost->password = md5($objPost->password);
									
									$objPost->isActive = 'n';

									$objPost->hash = generateRandString();
									$LastUid = $this->db->insert('tbl_users', (array)$objPost)->getLastInsertId();
									$notifyConstant = $objPost->first_name.' '.$objPost->last_name.' is registered in '.SITE_NM;

									$notifyUrl = 'users-sd?notiId='.$LastUid;
									add_admin_notification($notifyConstant, $notifyUrl);

										if ($objPost->user_type != 1) {
											$ComapntId = $this->db->insert('tbl_company',array("company_name"=>'',"user_id"=>$LastUid))->lastInsertId();
										}

									/*End Add Company*/

									/*Send Mail to user*/

									$to = $email;
									
									$extra_details = "";

									$activationLink = SITE_URL.'activation-user/'.base64_encode($objPost->email).'/'.base64_encode($objPost->hash);
						        	$arrayCont = array('greetings'=>$objPost->first_name,'activationLink'=>$activationLink,'EXTRA_DETAILS'=>$extra_details);


						        	$arrayCont = array("greetings"=>ucfirst($objPost->first_name), "activationLink"=>get_link('activation_link', base64_encode($objPost->email).'/'.base64_encode($objPost->hash)), 'EXTRA_DETAILS'=>'');
									sendMail($objPost->email, 'user_register', $arrayCont);

									/*Send Mail to user*/

									freePlan($LastUid);

						        	$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>PLZ_ACTIVATE_ACCOUNT));
						        	RedirectPage(SITE_URL);
					        	}
	        		}				
			}		
		}
	}
?>
