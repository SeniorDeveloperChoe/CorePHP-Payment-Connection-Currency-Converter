<?php
class UserProfile extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		$leftPanel=$this->getleftPanel();

		$userData=$this->db->pdoQuery("SELECT * FROM tbl_users WHERE id=?",array($_SESSION['userId']))->result();
		
		$userFirstName=!empty($userData['first_name'])?$userData['first_name']:"";
		$userLastName=!empty($userData['last_name'])?$userData['last_name']:"";
		$userID=!empty($userData['unique_user_id'])?$userData['unique_user_id']:"";
		$userGender=!empty($userData['gender'])?$userData['gender']:"";
		$userImage=!empty($userData['profile_img'])?SITE_UPD.'users-sd/'.$_SESSION['userId'].'/'.$userData['profile_img']:SITE_UPD.'no_user_image.png';
		$userContact=!empty($userData['phone_no'])?$userData['phone_no']:"";
		$userCountryCode=!empty($userData['country_code'])?$userData['country_code']:"";
		$userAboutMe=!empty($userData['description'])?$userData['description']:"";
		$userAddress_line1=!empty($userData['address_line1'])?$userData['address_line1']:"";
		$userAddress_line2=!empty($userData['address_line2'])?$userData['address_line2']:"";
		$userCity_name=!empty($userData['city_name'])?$userData['city_name']:"";
		$userRegistrationDate=!empty($userData['created_date'])?$userData['created_date']:"";
		$zipcode=!empty($userData['zipcode'])?$userData['zipcode']:"";
		$userJoinOn = date('dS F Y', strtotime($userRegistrationDate));
		$genderMale=$genderFemale=$genderOther="";
		$userAddress=$userAddress_line1;
		if(!empty($userAddress_line2)){
			$userAddress.=', '.$userAddress_line2;
		}
		$userAddress.=', '.$zipcode.', '.$userCity_name;
		$userImg="";
		if(!empty($userImage)){
			$userImg="<img width='250' hight='250' class='userimg' src='".$userImage."' />";
		}

		if($userGender=='m'){
			$userGender="Male";
			$genderMale="checked";
		}elseif($userGender=='f'){
			$userGender="Female";
			$genderFemale="checked";
		}elseif($userGender=='o'){
			$userGender="Other";
			$genderOther="checked";
		}
		$userEmail = $userData['email'];
		$fields = array(
			"%LEFT_PANEL%"=>$leftPanel,
			"%USER_FIRSTNAME%"=>$userFirstName,
			"%OLD_IMG_NAME%"=>$userData['profile_img'],
			"%USER_LASTNAME%"=>$userLastName,
			"%USER_ID%"=>$userID,
			"%USER_GENDER%"=>$userGender,
			"%USER_IMAGE%"=>$userImage,
			"%USER_ADDRESS%"=>$userAddress,
			"%USER_CONTACT%"=>$userContact,
			"%USER_CONTACT_SHOW%"=>(!empty($userCountryCode && $userCountryCode != '--')?'+'.$userCountryCode:'').$userContact,
			"%USER_COUNTRY_CODE%"=>$userCountryCode,
			"%USER_ABOUTME%"=>$userAboutMe,
			"%USER_JOINON%"=>$userJoinOn,
			"%GENDER_MALE%"=>$genderMale,
			"%GENDER_FEMALE%"=>$genderFemale,
			"%GENDER_OTHER%"=>$genderOther,
			"%USERADDRESS_LINE1%"=>$userAddress_line1,
			"%USERADDRESS_LINE2%"=>$userAddress_line2,
			"%USERCITY_NAME%"=>$userCity_name,
			"%USERZIPCODE%"=>$zipcode,
			"%USERIMG%"=>$userImg,
			"%USER_EMAIL%"=>$userEmail,

		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}


	public function submitProfile($data) {

		extract($data);

		$objPost = new stdClass();

		$objPost->first_name=!empty($firstName)?$firstName:'';
		$objPost->last_name=!empty($lastName)?$lastName:'';
		$objPost->address_line1=!empty($addressLine1)?$addressLine1:'';
		$objPost->address_line2=!empty($addressLine2)?$addressLine2:'';
		$objPost->city_name=!empty($city_name)?$city_name:'';
		$objPost->zipcode=!empty($zipcode)?$zipcode:'';
		$objPost->phone_no=!empty($mobile_no)?$mobile_no:'';
		$objPost->country_code=!empty($country_code)?$country_code:'';
		$objPost->description=!empty($description)?$description:'';
		$objPost->gender=!empty($genderRedio)?$genderRedio:'';
		$objPost->user_slug = slug($objPost->first_name);

		$LastUid = $this->db->update('tbl_users', (array)$objPost,array('id'=>$_SESSION['userId']));
		$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>PROFILE_UPDATED_SUCCESSFULLY));

		if ($_SESSION["user_type"] != 1) {
            if (checkCompleteProfile($_SESSION["userId"]) !=100) {
                redirectPage(SITE_URL.'supplier-profile');
            }
        }
		RedirectPage(SITE_URL.'user-profile');
		
	}
}
?>