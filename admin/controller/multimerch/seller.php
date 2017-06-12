<?php

class ControllerMultimerchSeller extends ControllerMultimerchBase {
	public function __construct($registry) {
		parent::__construct($registry);
	}

	public function getTableData() {
		$colMap = array(
			'seller' => '`c.name`',
			'email' => 'c.email',
			'date_created' => '`ms.date_created`',
			'status' => 'ms.seller_status'
		);

		$sorts = array('seller', 'email', 'total_offers', 'status', 'date_created');
		$filters = $sorts;

		list($sortCol, $sortDir) = $this->MsLoader->MsHelper->getSortParams($sorts, $colMap);
		$filterParams = $this->MsLoader->MsHelper->getFilterParams($filters, $colMap);

		$results = $this->MsLoader->MsSeller->getSellers(
			array(),
			array(
				'order_by'  => $sortCol,
				'order_way' => $sortDir,
				'filters' => $filterParams,
				'offset' => $this->request->get['iDisplayStart'],
				'limit' => $this->request->get['iDisplayLength']
			),
			array(
				'total_offers' => 1
			)
		);

		$total = isset($results[0]) ? $results[0]['total_rows'] : 0;

		$columns = array();
		foreach ($results as $result) {
			// actions
			$actions = "";

			// login as seller
			$this->load->model('setting/store');
			$actions .= "<div class='btn-group' data-toggle='tooltip' title='" . $this->language->get('button_login') . "'>";
			$actions .= "<button type='button' data-toggle='dropdown' class='btn btn-info dropdown-toggle'><i class='fa fa-lock'></i></button>";
			$actions .= "<ul class='dropdown-menu pull-right'>";
			$actions .= "<li><a href='" . $this->url->link('customer/customer/login', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['seller_id'] . '&store_id=0', 'SSL') . "' target='_blank'>" . $this->language->get('text_default') . "</a></li>";
			foreach ($this->model_setting_store->getStores() as $store) {
				$actions .= "<li><a href='" . $this->url->link('customer/customer/login', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['seller_id'] . '&store_id=' . $store['store_id'], 'SSL') . "' target='_blank'>" . $store['name'] . "</a></li>";
			}
			$actions .= "</ul>";
			$actions .= "</div> ";

			$actions .= "<a class='btn btn-primary' href='" . $this->url->link('multimerch/seller/update', 'token=' . $this->session->data['token'] . '&seller_id=' . $result['seller_id'], 'SSL') . "' data-toggle='tooltip' title='".$this->language->get('button_edit')."'><i class='fa fa-pencil'></i></a> ";
			$actions .= "<a class='btn btn-danger ms-button-delete' style='background-image: none;' href='" . $this->url->link('multimerch/seller/delete', 'token=' . $this->session->data['token'] . '&seller_id=' . $result['seller_id'], 'SSL') . "' data-toggle='tooltip' title='".$this->language->get('button_delete')."'><i class='fa fa-trash-o'></i></a> ";

			// build table data
			$columns[] = array_merge(
				$result,
				array(
					'checkbox' => "<input type='checkbox' name='selected[]' value='{$result['seller_id']}' />",
					'seller' => "<a href='".$this->url->link('customer/customer/edit', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['seller_id'], 'SSL')."'>{$result['c.name']}({$result['ms.nickname']})</a>",
					'email' => $result['c.email'],
					'total_offers' =>"<a href='" . $this->url->link('multimerch/seller/update', 'token=' . $this->session->data['token'] . '&seller_id=' . $result['seller_id'], 'SSL') . "'>" . $this->MsLoader->MsSeller->getTotalOffers($result['seller_id']) . "<br/>(view)</a>",
					'status' => $this->language->get('ms_seller_status_' . $result['ms.seller_status']),
					'date_created' => date($this->language->get('date_format_short'), strtotime($result['ms.date_created'])),
					'actions' => $actions
				)
			);
		}

		$this->response->setOutput(json_encode(array(
			'iTotalRecords' => $total,
  			'iTotalDisplayRecords' => $total,
			'aaData' => $columns
		)));
	}

	public function jxSaveSellerInfo() {
		$serviceLocator = $this->MsLoader->load('\MultiMerch\Module\MultiMerch')->getServiceLocator();
		$mailTransport = $serviceLocator->get('MailTransport');
		$mails = new \MultiMerch\Mail\Message\MessageCollection();

		$this->validate(__FUNCTION__);
		$data = $this->request->post;
		$seller = $this->MsLoader->MsSeller->getSeller($data['seller']['seller_id']);
		$json = array();
		$this->load->model('customer/customer');

		if (empty($data['seller']['seller_id'])) {
			// creating new seller
			if (empty($data['seller']['nickname'])) {
				$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_empty');
			} else if (utf8_strlen($data['seller']['nickname']) < 4 || utf8_strlen($data['seller']['nickname']) > 128 ) {
				$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_length');
			} else if ($this->MsLoader->MsSeller->nicknameTaken($data['seller']['nickname'])) {
				$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_taken');
			} else {
				switch($this->config->get('msconf_nickname_rules')) {
					case 1:
						// extended latin
						if(!preg_match("/^[a-zA-Z0-9_\-\s\x{00C0}-\x{017F}]+$/u", $data['seller']['nickname'])) {
							$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_latin');
						}
						break;

					case 2:
						// utf8
						if(!preg_match("/((?:[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}){1,100})./x", $data['seller']['nickname'])) {
							$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_utf8');
						}
						break;

					case 0:
					default:
						// alnum
						if(!preg_match("/^[a-zA-Z0-9_\-\s]+$/", $data['seller']['nickname'])) {
							$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_alphanumeric');
						}
						break;
				}
			}
			if (empty($data['customer']['customer_id'])) {
				// creating new customer
				$this->language->load('customer/customer');
				if ((utf8_strlen($data['customer']['firstname']) < 1) || (utf8_strlen($data['customer']['firstname']) > 32)) {
			  		$json['errors']['customer[firstname]'] = $this->language->get('error_firstname');
				}

				if ((utf8_strlen($data['customer']['lastname']) < 1) || (utf8_strlen($data['customer']['lastname']) > 32)) {
			  		$json['errors']['customer[lastname]'] = $this->language->get('error_lastname');
				}

				if ((utf8_strlen($data['customer']['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $data['customer']['email'])) {
			  		$json['errors']['customer[email]'] = $this->language->get('error_email');
				}

				$customer_info = $this->model_customer_customer->getCustomerByEmail($data['customer']['email']);

				if (!isset($this->request->get['customer_id'])) {
					if ($customer_info) {
						$json['errors']['customer[email]'] = $this->language->get('error_exists');
					}
				} else {
					if ($customer_info && ($this->request->get['customer_id'] != $customer_info['customer_id'])) {
						$json['errors']['customer[email]'] = $this->language->get('error_exists');
					}
				}

				if ($data['customer']['password'] || (!isset($this->request->get['customer_id']))) {
			  		if ((utf8_strlen($data['customer']['password']) < 4) || (utf8_strlen($data['customer']['password']) > 20)) {
						$json['errors']['customer[password]'] = $this->language->get('error_password');
			  		}

			  		if ($data['customer']['password'] != $data['customer']['password_confirm']) {
						$json['errors']['customer[password_confirm]'] = $this->language->get('error_confirm');
			  		}
				}
			}
		}
		if (isset($data['seller']['company']) && strlen($data['seller']['company']) > 50 ) {
			$json['errors']['seller[company]'] = 'Company name cannot be longer than 50 characters';
		}

		if (isset($data['seller']['offer_limit']) && ($data['seller']['offer_limit'] < 0 || $data['seller']['offer_limit'] == '') ) {
			$json['errors']['seller[offer_limit]'] = 'Offer limit cannot be less than 0';
		}

		if (empty($json['errors'])) {
			if (empty($data['seller']['seller_id'])) {
				// creating new seller
				if (empty($data['customer']['customer_id'])) {
					// creating new customer
					$this->model_customer_customer->addCustomer(
						array_merge(
							$data['customer'],
							array(
								'telephone' => '',
								'fax' => '',
								'customer_group_id' => $this->config->get('config_customer_group_id'),
								'newsletter' => 1,
								'status' => 1,
								'approved' => 1,
								'safe' => 1,
							)
						)
					);

					$customer_info = $this->model_customer_customer->getCustomerByEmail($data['customer']['email']);
					$this->db->query("UPDATE " . DB_PREFIX . "customer SET approved = '1' WHERE customer_id = '" . (int)$customer_info['customer_id'] . "'");

					$data['seller']['seller_id'] = $customer_info['customer_id'];
				} else {
					$data['seller']['seller_id'] = $data['customer']['customer_id'];
				}
				$this->MsLoader->MsSeller->createSeller(
					array_merge(
						$data['seller'],
						array(
							'approved' => 1,
						),
						array('settings' => array())
					)
				);
			} else {
				// edit seller
				$MailSellerAccountModified = $serviceLocator->get('MailSellerAccountModified', false)
					->setTo($seller['c.email'])
					->setData(array(
						'addressee' => $seller['ms.nickname'],
						'ms_seller_status' => $data['seller']['status'],
						'message' => (isset($data['seller']['message']) ? $data['seller']['message'] : ''),
					));

				$mails->add($MailSellerAccountModified);

				switch ($data['seller']['status']) {
					case MsSeller::STATUS_INACTIVE:
					case MsSeller::STATUS_DISABLED:
					case MsSeller::STATUS_DELETED:
					case MsSeller::STATUS_INCOMPLETE:

						$data['seller']['approved'] = 0;
						break;
					case MsSeller::STATUS_ACTIVE:
						$data['seller']['approved'] = 1;
						break;
				}
				$this->MsLoader->MsSeller->adminEditSeller(
					array_merge(
						$data['seller'],
						array(
							'approved' => 1,
						),
                        array('settings' => array())
					)
				);
			}

			if (isset($data['seller']['notify']) && $data['seller']['notify']) {
				$mailTransport->sendMails($mails);
			}
			$this->session->data['success'] = 'Seller account data saved.';
		}

		$this->response->setOutput(json_encode($json));
	}

	public function index() {

		$this->document->addScript('//code.jquery.com/ui/1.11.2/jquery-ui.min.js');
		$this->document->addScript('view/javascript/multimerch/seller-payout.js');
		$this->document->addStyle('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css');

		$this->validate(__FUNCTION__);


		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$this->data['token'] = $this->session->data['token'];
		$this->data['heading'] = $this->language->get('ms_catalog_sellers_heading');
		$this->document->setTitle($this->language->get('ms_catalog_sellers_heading'));

		$this->data['link_create_seller'] = $this->url->link('multimerch/seller/create', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
			array(
				'text' => $this->language->get('ms_menu_multiseller'),
				'href' => $this->url->link('multimerch/dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_catalog_sellers_breadcrumbs'),
				'href' => $this->url->link('multimerch/seller', '', 'SSL'),
			)
		));

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('multiseller/seller.tpl', $this->data));
	}

	public function create() {

		$this->validate(__FUNCTION__);
		$this->load->model('localisation/country');
		$this->load->model('tool/image');
		$this->data['countries'] = $this->model_localisation_country->getCountries();
		$this->data['customers'] = $this->MsLoader->MsSeller->getCustomers();
		$this->data['seller'] = FALSE;


		$this->data['currency_code'] = $this->config->get('config_currency');
		$this->data['token'] = $this->session->data['token'];
		$this->data['heading'] = $this->language->get('ms_catalog_sellerinfo_heading');
		$this->document->setTitle($this->language->get('ms_catalog_sellerinfo_heading'));

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
			array(
				'text' => $this->language->get('ms_menu_multiseller'),
				'href' => $this->url->link('multimerch/dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_catalog_sellers_breadcrumbs'),
				'href' => $this->url->link('multimerch/seller', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_catalog_sellers_newseller'),
				'href' => $this->url->link('multimerch/seller/create', 'SSL'),
			)
		));

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('multiseller/seller-form.tpl', $this->data));
	}

	public function update() {
		$this->validate(__FUNCTION__);

		$seller_id = (int)$this->request->get['seller_id'];

		if (!$seller_id) {
			return $this->response->redirect($this->url->link('multimerch/seller', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->load->model('localisation/country');
		$this->load->model('tool/image');

		$seller = $this->MsLoader->MsSeller->getSeller($this->request->get['seller_id']);

		if (!empty($seller)) {

			$this->data['seller'] = $seller;

			if (!empty($seller['ms.avatar'])) {
				$this->data['seller']['avatar']['name'] = $seller['ms.avatar'];
				$this->data['seller']['avatar']['thumb'] = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
			}
		}

		$this->data['currency_code'] = $this->config->get('config_currency');
		$this->data['token'] = $this->session->data['token'];
		$this->data['heading'] = $this->language->get('ms_catalog_sellerinfo_heading');
		$this->document->setTitle($this->language->get('ms_catalog_sellerinfo_heading'));

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
			array(
				'text' => $this->language->get('ms_menu_multiseller'),
				'href' => $this->url->link('multimerch/dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_catalog_sellers_breadcrumbs'),
				'href' => $this->url->link('multimerch/seller', '', 'SSL'),
			),
			array(
				'text' => $seller['ms.nickname'],
				'href' => $this->url->link('multimerch/seller/update', '&seller_id=' . $seller['seller_id'], 'SSL'),
			)
		));

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		foreach ($this->data['languages'] as $language){
			if(!isset($this->data['seller']['descriptions'][$language['language_id']]['description'])){
				$this->data['seller']['descriptions'][$language['language_id']]['description'] = '';
			}
		}

		$this->data['is_edit'] = true;

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('multiseller/seller-form.tpl', $this->data));
	}

	public function delete() {
		$seller_id = isset($this->request->get['seller_id']) ? $this->request->get['seller_id'] : 0;
		$this->MsLoader->MsSeller->deleteSeller($seller_id);
		$this->response->redirect($this->url->link('multimerch/seller', 'token=' . $this->session->data['token'], 'SSL'));
	}

}
