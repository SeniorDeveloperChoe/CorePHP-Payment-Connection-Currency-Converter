<?php
    class PlacedQuotes extends Home {
    	function __construct($module = "placed_quotes-sd") {
    		$this->module = $module;
            parent::__construct();
    	}

        public function getQuotes($type="") {
            $quotes_content = '';
            $main_content = (new MainTemplater(DIR_TMPL . $this->module . "/placed_quotes_inner-sd.skd"))->compile();
            $whereCond = 'sq.proId = ?'; $wArray = array($this->sessUserId);
            if(!empty($type)) {
                if($type=='er') {
                    $whereCond .= ' AND quotes_status = ? AND serviceStatus = ?';
                    $wArray[] = 't';
                    $wArray[] = 'a';
                } else if($type=='a') {
                    $whereCond .= ' AND quotes_status = ? AND serviceStatus = ?';
                    $wArray[] = 'a';
                    $wArray[] = 'a';
                } else {
                    $whereCond .= ' AND serviceStatus = ?';
                    $wArray[] = $type;
                }
            }

            $quotes = $this->db->pdoQuery("
                SELECT sq.*, sq.createdDate AS placed_quote_date, DATE_ADD(sr.request_date, INTERVAL sr.request_hour HOUR) AS req_date_time, sr.customerId, sr.estBudget, sr.serviceTitle, sr.serviceSlug, sr.createdDate, CONCAT(sr.city,', ',sr.state,', ',sr.country) AS location,sr.formatted_address,sr.request_hour, sr.serviceStatus, sc.categoryName_$this->curr_language AS categoryName, ssc.subcatName_$this->curr_language AS subcategory_name, ssc.subcatCredits, CONCAT(u.firstName,' ',u.lastName) AS username, u.user_slug, u.profile_img
                FROM tbl_service_quotes AS sq
                INNER JOIN tbl_service_requests AS sr ON(sr.id = sq.serviceId AND sr.isActive='y')
                INNER JOIN tbl_service_category AS sc ON(sc.id = sr.catId AND sc.isActive='y')
                INNER JOIN tbl_service_subcategory AS ssc ON(ssc.id = sr.subcatId AND sc.isActive='y')
                INNER JOIN tbl_users AS u ON(u.id = sr.customerId AND u.isActive='y')
                WHERE $whereCond
                Order by sr.createdDate desc
            ", $wArray)->results();

            if(!empty($quotes)) {
                foreach ($quotes as $key => $value) {
                    $view_ques_btn = '';
                    if($value['serviceStatus'] != 'r') {
                        $view_ques_btn = '
                            <a href="'.get_link('service-detail', $value['serviceSlug']).'#questionnaire" target="" title="'.LBL_VIEW_DETAIL.'" class="btn btn-default btn-arrow">
                                <span>
                                '.LBL_VIEW_DETAIL.'
                                </span>
                            </a>
                        ';
                    }

                    if($value['serviceStatus']=='a') {
                        if($value['quotes_status']=="a") {
                            $status_class = "info";
                            $status = ((date('Y-m-d H:i:s') > $value['req_date_time']) ? LBL_COMPLETED : LBL_HIRED);
                        } else {
                            $status = (($value['quotes_status']=='r') ? EXPIRED : (($value['quotes_status']=='t') ? LBL_REJECTED : OPEN));
                            $status_class = (($value['quotes_status']=='r') ? "danger" : (($value['quotes_status']=='t') ? "danger" : "success"));
                        }
                    } else {
                        $status_class = (($value['serviceStatus']=='r') ? 'danger' : 'success');
                        $status = (($value['serviceStatus']=='r') ? EXPIRED : OPEN);
                    }

                    $fields = array(
                        "%USER_LINK%" => get_link('user', $value['user_slug']),
                        "%USER_IMG%" => getImage(DIR_USER_IMG.$value['customerId'].'/'.$value['profile_img'], 52, 52),
                        "%USER_NAME%" => $value['username'],
                        "%REQUEST_TITLE%" => ucfirst($value['serviceTitle']),
                        "%CATEGORIES%" => $value['categoryName'],
                        "%SUBCATEGORIES%" => $value['subcategory_name'],
                        "%LOCATION%" => $value['formatted_address'],
                        "%DT_TIME%" => date('dS M, Y h:i A', strtotime($value['placed_quote_date'])),
                        "%HOURS%" => $value['request_hour'],
                        "%REQUIREMENT%" => $value['quotes_credit'],
                        "%BUDGET%" => $value['estBudget'].' '.CURRENCY_CODE,
                        "%STATUS%" => $status,
                        "%STATUS_CALSS%" => $status_class,
                        "%VIEW_URL%" => $view_ques_btn,
                        "%SERVICE_URL%" => get_link('service-detail', $value['serviceSlug'])
                    );

                    $quotes_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
                }
            } else {
                $quotes_content = '
                <li>
                    <div class="row">
                        <div class="posted_status">
                            <div class="col-md-12 col-xs-12">
                                <div class="service_budget">
                                    <h4>
                                        '.str_replace('#STATUS#', (($type=='a') ? 'Hired' : (($type=='r') ? EXPIRED : OPEN)), NO_QUOTES_PLACED).'
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                ';
            }
            return $quotes_content;
        }

    	public function getPageContent() {
            $main_content = (new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd"))->compile();
            $fields = array(
                "%LEFT_PANEL%" => $this->getleftPanel(),
                "%QUOTES%" => $this->getQuotes()
            );
            $main_content = str_replace(array_keys($fields), array_values($fields), $main_content);
    		return $main_content;
    	}
    }
?>