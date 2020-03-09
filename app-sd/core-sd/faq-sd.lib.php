<?php
	class FAQ extends Home {
		function __construct($module = "") {
			$this->module = $module;
			parent::__construct();
		}

		public function getPageContent(){
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/".$this->module.".skd"))->compile();

			$faqs = $this->getFAQS();

			$fields = array(
				"%FAQ_CATS%" => $faqs['categories'],
				"%FAQS%" => $faqs['questions'],
			);

			$main_content = str_replace(array_keys($fields), array_values($fields), $main_content);
			return $main_content;
		}

		public function getFAQS($searchKeyword="") {
			$faq_category_content = array('categories'=>'', 'questions'=>'');
			$faq_category = $this->db->pdoQuery("
				SELECT fc.*, (SELECT COUNT(id) FROM tbl_faq WHERE faq_category = fc.id AND isActive = 'y') AS questions
				FROM tbl_faq_category AS fc
				WHERE isActive = ?
			", array('y'))->results();
			if(!empty($faq_category)) {
				foreach ($faq_category as $key => $value) {
					if(!empty($value['questions'])) {
						$act_class = (empty($key) ? "active" : "");
						
						$faq_category_content['categories'] .= '
                              <li class="'.$act_class.'"><a data-toggle="pill" href="#'.$value['id'].'">'.$value["categoryName_$this->curr_language"].'</a></li>
						';

						$faq_category_content['questions'] .= $this->getFAQQuestions($searchKeyword,$value['id'], $key,$value["categoryName_$this->curr_language"]);
					}
				}
			}
			return $faq_category_content;
		}

		public function getFAQQuestions($searchKeyword="",$category = 0, $seq,$catName) {

			$faqs_content = '';			
			
			if(!empty($searchKeyword)){
				$colFaqQues='faq_question_'.$this->curr_language;
				$colFaqAns='faq_answer_'.$this->curr_language;
				$faqs = $this->db->pdoQuery("SELECT * FROM tbl_faq WHERE isActive='y' AND faq_category='".$category."' AND (".$colFaqQues." LIKE '%".$searchKeyword."%' OR ".$colFaqAns." LIKE '%".$searchKeyword."%') ")->results();
			}else{
				$faqs = $this->db->select('tbl_faq', array('*'), array('isActive'=>'y', 'faq_category'=>$category))->results();
			}


			$act_class = (empty($seq) ? "active" : "");

			if(!empty($faqs)) {
				$faqs_content .= '
									<div id="'.$category.'" class="tab-pane fade in '.$act_class.'">
		                              <h3>'.$catName.'</h3>
		                              	<div class="panel-group" id="faq-accordian">';

					        foreach ($faqs as $key => $value) {

								$faqs_content .= '
		                                 <div class="panel panel-default">
		                                    <div class="panel-heading">
		                                       <h4 class="panel-title">
		                                          <a data-toggle="collapse" data-parent="#faq-accordian" href="#'.$value['id'].'_'.$category.'">'.$value["faq_question_$this->curr_language"].'</a>
		                                       </h4>
		                                    </div>
		                                    <div id="'.$value['id'].'_'.$category.'" class="panel-collapse collapse">
		                                       <div class="panel-body">
		                                      '.$value["faq_answer_$this->curr_language"].'
		                                       </div>
		                                    </div>
		                                 </div>';

							}


							$faqs_content .= '
							 </div>
		                  </div>';
			}
			return $faqs_content;
		}
	}
?>