<?php
class Messages extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent($starLimit=0, $endLimit=PAGE_DISPLAY_LIMITS, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		/*Get PMB left panel process*/
			$getPmbSidePanel=$this->db->pdoQuery("SELECT m.*,m.id AS messageId,u.user_slug AS sndSlug,ur.user_slug AS rcvSlug,u.profile_img AS SenderImg,u.user_location AS senderLocation,u.id AS SenderId,CONCAT(u.first_name,' ',u.last_name) AS UserName,ur.profile_img AS urImg,ur.user_location AS urLocation,ur.id AS urId,CONCAT(ur.first_name,' ',ur.last_name) AS urName, max(m.created_date) AS maxDate
												FROM tbl_messages AS m 
												INNER JOIN tbl_users AS u ON u.id=m.sender
												INNER JOIN tbl_users AS ur ON ur.id=m.receiver
												WHERE (m.receiver=? OR m.sender=?) AND (m.is_deleted IS NULL OR m.is_deleted = '' OR NOT FIND_IN_SET(".$this->sessUserId.", m.is_deleted))
												GROUP BY UserName,urName ORDER BY maxDate DESC",array($_SESSION['userId'],$_SESSION['userId']))->results();


			$pmb_leftPanel_loop=$senderID="";
			$arrayUserIds=array();
			if(empty($getPmbSidePanel)){
				$html = (new MainTemplater(DIR_TMPL . "$this->module/no_pmb-sd.skd"))->compile();
			}else{
					foreach ($getPmbSidePanel as $key => $value) {
						$pmb_left_panel_content=(new MainTemplater(DIR_TMPL.$this->module."/pmb_side_panel-sd.skd"))->compile();

						$activeClass=$senderName=$senderLocation=$senderImg="";
						/*This Sender Id for get dynamic messages from side panel*/
						$senderID_I=($_SESSION['userId']==$value['SenderId'])?$value['urId']:$value['SenderId'];

						if($key==0){
							/*This Sender Id for load first user messages*/
							$senderID=($_SESSION['userId']==$value['SenderId'])?$value['urId']:$value['SenderId'];
							$activeClass="active";
						}

						$chkDeleted=explode(",", $value['is_deleted']);


						if($_SESSION['userId']!=$value['SenderId']){					
								if(in_array($value['sndSlug'], $arrayUserIds)){
								}else{
										$senderName = !empty($value['UserName'])?$value['UserName']:"";
										$senderLocation = !empty($value['senderLocation'])?$value['senderLocation']:"";
										$senderImg = !empty($value['SenderImg'])?SITE_USER_IMG.$value['SenderId'].'/'.$value['SenderImg']	:SITE_UPD."no_user_image.png";

										$pmb_left_panel_fields = array(
																"%SENDER_ID%"=>base64_encode(base64_encode($senderID_I)),
																"%SENDER_NAME%"=>$senderName,
																"%SENDER_LOCATION%"=>$senderLocation,
																"%SENDER_IMG%"=>$senderImg,
																"%ACTIVE_CLASS%"=>$activeClass
																);
										$pmb_leftPanel_loop.=str_replace(array_keys($pmb_left_panel_fields), array_values($pmb_left_panel_fields), $pmb_left_panel_content);
										array_push($arrayUserIds,$value['sndSlug']);
								
								}
							
							}else{								
									if(in_array($value['rcvSlug'], $arrayUserIds)){
									}else{
											$senderName = !empty($value['urName'])?$value['urName']:"";
											$senderLocation = !empty($value['urLocation'])?$value['urLocation']:"";
											$senderImg = !empty($value['urImg'])?SITE_USER_IMG.$value['urId'].'/'.$value['urImg']	:SITE_UPD."no_user_image.png";

											$pmb_left_panel_fields = array(
																	"%SENDER_ID%"=>base64_encode(base64_encode($senderID_I)),
																	"%SENDER_NAME%"=>$senderName,
																	"%SENDER_LOCATION%"=>$senderLocation,
																	"%SENDER_IMG%"=>$senderImg,
																	"%ACTIVE_CLASS%"=>$activeClass
																	);
											$pmb_leftPanel_loop.=str_replace(array_keys($pmb_left_panel_fields), array_values($pmb_left_panel_fields), $pmb_left_panel_content);
											array_push($arrayUserIds,$value['rcvSlug']);				
									}							
							}
						$senderUserImg = getTablevalue('tbl_messages', 'id', array('sender'=>$value['SenderId'],'receiver'=>$_SESSION['userId']));
					}
			}	
		/*END Get PMB left panel process*/		
			$leftPanel=$this->getleftPanel();
			$getMessages=$this->get_messages($senderID);
			$fields = array(
						"%PMB_LEFTPANEL_LOOP%"=>$pmb_leftPanel_loop,
						"%USER_NAME%"=>$getMessages['UserName'],
						"%USER_IMG%"=>$getMessages['userImg'],
						"%SENDER_ID%"=>base64_encode(base64_encode($senderID)),
						"%CHATS%"=>$getMessages['final_messages'],
						"%LEFTPANEL%"=>$leftPanel
						);
			$html=str_replace(array_keys($fields), array_values($fields), $html);



		return $html;
	}

	public function getFileLogo($fileName=""){
		$ext = pathinfo(DIR_MSG_DOCS.$fileName, PATHINFO_EXTENSION);
				if($ext=="pdf") { 
					$attachmemnt = SITE_UPD.'pdf.png'; }
				else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$attachmemnt = SITE_UPD.'doc.png'; }
				else { 
					$attachmemnt = SITE_MSG_DOCS.$fileName; }
		return $attachmemnt;
	}


	public function get_messages($UserId){
		/*Get PMB Header Content*/
			$getUserDetail=$this->db->pdoQuery("SELECT CONCAT(u.first_name,' ',u.last_name) AS UserName,u.profile_img AS UserImg FROM tbl_users AS u WHERE u.id=?",array($UserId))->result();

			$userName = !empty($getUserDetail['UserName'])?$getUserDetail['UserName']:"";
			$userHeaderImg = !empty($getUserDetail['UserImg'])?SITE_USER_IMG.$UserId.'/'.$getUserDetail['UserImg']:SITE_UPD."no_user_image.png";
		/*End Get PMB Header Content*/

		/*Query For Get Full Chat*/
		$getUserChats=$this->db->pdoQuery("SELECT m.*,u.profile_img,u.id AS UID
										   FROM tbl_messages AS m
										   INNER JOIN tbl_users AS u ON u.id=m.sender
										   WHERE ((m.sender=? AND m.receiver=?) OR (m.sender=? AND m.receiver=?)) AND (m.is_msgs_deleted IS NULL OR m.is_msgs_deleted = '' OR NOT FIND_IN_SET(".$this->sessUserId.", m.is_msgs_deleted))
										   ORDER BY m.created_date
											",array($UserId,$_SESSION['userId'],$_SESSION['userId'],$UserId))->results();	

		$final_messages="";
		if(empty($getUserChats)){
			$final_messages='<div class="msg-box-content noconversation">
                          	<div class="no-msg">
                              <img src="'.SITE_IMG.'icons/mail.png" alt="">
                              <h2>'.NO_MESSAGES_YET.'</h2>
                              <p class="no-msg-tag">'.YOU_HAVE_NOT_INITIATED_CONVAERSATION.'</p>
                          	</div>
                        </div>';
		}else{
				$this->db->pdoQuery("UPDATE tbl_messages SET is_read=? WHERE receiver=?",array('y',$_SESSION['userId']));

				foreach ($getUserChats as $key => $value) {		
					$chats_content = (new MainTemplater(DIR_TMPL.$this->module."/chats-sd.skd"))->compile();
					$head_chat_class=$sub_chat_class=$chatSkeletons="";
					if($value['sender']==$_SESSION['userId']){
						$head_chat_class='right-side-user';
						$sub_chat_class='pull-right';
					}else{
						$head_chat_class='left-side-user';
						$sub_chat_class='pull-left';
					}

					/*Get Message If Type is Text Or Both*/
					$textMsg=$fileMsg="";
					$msgText_content = (new MainTemplater(DIR_TMPL.$this->module."/msgText-sd.skd"))->compile();
					if($value['msg_type']=="t" || $value['msg_type']=="b"){
				        $text_fields = array("%MSG_TEXT%"=>$value['msg']);
						$textMsg=str_replace(array_keys($text_fields), array_values($text_fields), $msgText_content);
					}
					/*End Get Messages Text*/


					/*Get Message If Type is Files Or Both*/
					if($value['msg_type']=="f" || $value['msg_type']=="b"){
						$msgDocs_content = (new MainTemplater(DIR_TMPL.$this->module."/msgDocs-sd.skd"))->compile();
						$fileNames=explode(',', $value['db_file_name']);
						$fileNamesActual=explode(',', $value['actual_file_name']);
						$randomKey=generateRandString();
						
						if(!empty($fileNames[0])){
							if(!empty($fileNames[1])){
								$key=0;
								foreach ($fileNames as $img_key => $img_value) {
									$attachmemnt=$this->getFileLogo($img_value);
				                     /*Fill Messages DOCS skd When multiple docs*/
							            $doc_fields = array(
												"%ACTIAL_FILE_NAME%"=>$fileNamesActual[$key],
												"%DB_FILE_NAME%"=>$attachmemnt,
												"%DOWNLOAD_FILE_LINK%"=>get_link('download_file',base64_encode($value['id']).'-'.$key.'-'.base64_encode($fileNames[$key])).'-'.$randomKey
												);

										$fileMsg.=str_replace(array_keys($doc_fields), array_values($doc_fields), $msgDocs_content);
									/*END Fill Messages DOCS skd When multiple docs*/
				                    $key=$key+1;
								}

							}else{
								$attachmemnt=$this->getFileLogo($fileNames[0]);					
			                    /*Fill Messages DOCS skd When single docs*/
						            $doc_fields = array(
											"%ACTIAL_FILE_NAME%"=>$fileNamesActual[0],
											"%DB_FILE_NAME%"=>$attachmemnt,
											"%DOWNLOAD_FILE_LINK%"=>get_link('download_file',base64_encode($value['id']).'-0-'.base64_encode($fileNames[0])).'-'.$randomKey
											);

									$fileMsg=str_replace(array_keys($doc_fields), array_values($doc_fields), $msgDocs_content);
								/*END Fill Messages DOCS skd When single docs*/
							}
						}else{
							$doc_fields = array(
									"%NO_FILE%"=>"hide"
									);

									$fileMsg=str_replace(array_keys($doc_fields), array_values($doc_fields), $msgDocs_content);
						}
					}
					/*End Get Messages Docs*/


					/*Fill Full Chat Messages in SKd Process*/
						$userImg=!empty($value['profile_img'])?SITE_USER_IMG.$value['UID'].'/'.$value['profile_img']:SITE_UPD."no_user_image.png";

						$getMessagesTime=dateDifference($value['created_date']);


						$chat_fields = array(
								"%TEXT_MSG%"=>$textMsg,
								"%FILE_MSG%"=>$fileMsg,
								"%HEAD_CHAT_CLASS%"=>$head_chat_class,
								"%SUB_CHAT_CLASS%"=>$sub_chat_class,
								"%USER_IMG%"=>$userImg,
								"%GET_MESSAGES_TIME%"=>$getMessagesTime
								);

						$final_messages.=str_replace(array_keys($chat_fields), array_values($chat_fields), $chats_content);
					/*END Fill Full Chat Messages in SKd Process*/
				}
		}
		/*Return Messages*/
		$returnArray=array("UserName"=>$userName,"userImg"=>$userHeaderImg,"final_messages"=>$final_messages);
		return $returnArray;
	}

}
?>