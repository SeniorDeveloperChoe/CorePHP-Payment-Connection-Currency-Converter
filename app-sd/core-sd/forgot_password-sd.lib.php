<?php
class ForgotPassword {
  function __construct($module = "", $id = 0, $token = "",$reffToken="") {
    foreach ($GLOBALS as $key => $values) {
      $this->$key = $values;
    }
    $this->module = $module;
    $this->id = $id;
    }

  public function getPageContent() {
    return (new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd"))->compile();
  }


  public function forgotProcedure($data) {
        extract($data);
        $uEmail = isset($email) ? $email : '';
        $value = new stdClass();
        
        if($uEmail !=  ""){

        $exist = $this->db->pdoQuery("SELECT id, email, first_name FROM tbl_users WHERE LOWER(email) = ? and isActive = ?", array($uEmail,'y'))->result();

       
            if(!empty($chkpoint) && checkToken($chkpoint, 'loginform')) {

               if ($exist > 0) { 

                  
                      
                      $updated_password = generateRandString(6);
                      $this->db->update('tbl_users', array('password' => md5($updated_password)), array('id' => $exist['id']));


                      $arrayCont = array(
                      "login_link" => get_link('login'),
                      "password" => $updated_password,
                      "greetings" => ucfirst($exist['first_name'])
                    );
                      sendMail($exist['email'], 'forgot_password_user', $arrayCont, false, true, $exist['id']);
                      $return_array['type'] = 'success';
                      $return_array['messages'] = MSG_FORGOT_PASS_SUC;
                      $return_array['url'] = get_link('home');
                        $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>MSG_FORGOT_PASS_SUC));
                        redirectPage(SITE_URL);

                     }else{
                      
                        $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>THIS_EMAIL_IS_NOT_REGISTERED));
                        redirectPage(SITE_URL);
                   }
            }
       }else{
            $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>ERR_PLEASE_ENTER_EMAIL));
            redirectPage(SITE_URL."login");
       }
   }


}

?>
