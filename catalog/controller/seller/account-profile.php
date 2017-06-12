<?php

class ControllerSellerAccountProfile extends ControllerSellerAccount {
	public function jxUploadSellerAvatar() {
		$json = array();
		$file = array();

		$json['errors'] = $this->MsLoader->MsFile->checkPostMax($_POST, $_FILES);

		if ($json['errors']) {
			return $this->response->setOutput(json_encode($json));
		}
		
		foreach ($_FILES as $name=>$file) {
			$errors = $this->MsLoader->MsFile->checkImage($file);
			if ($errors) {
				$json['errors'] = array_merge($json['errors'], $errors);
			} else {
				if($name === 'ms-avatar') {
					$fileName = $this->MsLoader->MsFile->uploadImage($file);
					$thumbUrl = $this->MsLoader->MsFile->resizeImage($this->config->get('msconf_temp_image_path') . $fileName, $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
					$json['files'][] = array(
						'name' => $fileName,
						'thumb' => $thumbUrl
					);	
				} else if($name === 'ms-banner') {
					$fileName = $this->MsLoader->MsFile->uploadImage($file);
					$thumbUrl = $this->MsLoader->MsFile->resizeImage($this->config->get('msconf_temp_image_path') . $fileName, $this->config->get('msconf_product_seller_banner_width')/2, $this->config->get('msconf_product_seller_banner_height')/2);
					$json['files'][] = array(
						'name' => $fileName,
						'thumb' => $thumbUrl
					);
				}
			}
		}

		return $this->response->setOutput(json_encode($json));
	}

	public function jxSaveSellerInfo() {
		/** @var \MultiMerch\Module\MultiMerch $MultiMerchModule */
		$MultiMerchModule = $this->MsLoader->load('\MultiMerch\Module\MultiMerch');
		$serviceLocator = $MultiMerchModule->getServiceLocator();
		$mailTransport = $serviceLocator->get('MailTransport');
		$mails = new \MultiMerch\Mail\Message\MessageCollection();

		$data = $this->request->post;

		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());

		$json = array();
		$json['redirect'] = $this->url->link('account/account');

		if (!empty($seller) && (in_array($seller['ms.seller_status'], array(MsSeller::STATUS_DISABLED, MsSeller::STATUS_DELETED)))) {
			return $this->response->setOutput(json_encode($json));
		}

		if ($this->config->get('msconf_change_seller_nickname') || empty($seller)) {
			// seller doesn't exist yet
			if (empty($data['seller']['nickname'])) {
				$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_empty');
			} else if (utf8_strlen($data['seller']['nickname']) < 4 || utf8_strlen($data['seller']['nickname']) > 128 ) {
				$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_length');
			} else if ( ($data['seller']['nickname'] != $seller['ms.nickname']) && ($this->MsLoader->MsSeller->nicknameTaken($data['seller']['nickname'])) ) {
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
		} else {
			$data['seller']['nickname'] = $seller['ms.nickname'];
		}

		if (empty($seller)) {
			if ($this->config->get('msconf_seller_terms_page')) {
				$this->load->model('catalog/information');
				$information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));

				if ($information_info && !isset($data['seller']['terms'])) {
	 				$json['errors']['seller[terms]'] = htmlspecialchars_decode(sprintf($this->language->get('ms_error_sellerinfo_terms'), $information_info['title']));
				}
			}
		}

		$validator = $this->MsLoader->MsValidator;
		$default = 1;
		$languages = $this->model_localisation_language->getLanguages();
		foreach ($languages as $language){
			$language_id = $language['language_id'];
			$primary = true;
			if($language_id != $default){
				$primary = false;
			}

			// seller description
			if (!$validator->validate(array(
				'name' => $this->language->get('ms_account_sellerinfo_description'),
				// count seller description length without tags (as video script tag)
				'value' => strip_tags(htmlspecialchars_decode($data['seller']['description'][$language_id]['description']))
			),
				array(
					array('rule' => 'max_len,5000')
				)
			)) $json['errors']["seller_description[$language_id]"] = $validator->get_errors($language_id);

			//copy fields data from main language
			if(!$primary) {
				if (empty($data['seller']['description'][$language_id]['description'])){
					$data['seller']['description'][$language_id]['description'] = $data['seller']['description'][$default]['description'];
				}
			}

		}

		if (isset($data['seller']['avatar_name']) && !empty($data['seller']['avatar_name'])) {
			if (!$this->MsLoader->MsFile->checkFileAgainstSession($data['seller']['avatar_name'])) {
				$json['errors']['seller[avatar]'] = $this->language->get('ms_error_file_upload_error');
			}
		}

		// strip disallowed tags in description
		if ($this->config->get('msconf_enable_rte')) {
			if ($this->config->get('msconf_rte_whitelist') != '') {
				$allowed_tags = explode(",", $this->config->get('msconf_rte_whitelist'));
				$allowed_tags_ready = "";
				foreach($allowed_tags as $tag) {
					$allowed_tags_ready .= "<".trim($tag).">";
				}
				foreach ($languages as $language){
					$data['seller']['description'][$language['language_id']]['description'] = htmlspecialchars(strip_tags(htmlspecialchars_decode($data['seller']['description'][$language['language_id']]['description'], ENT_COMPAT), $allowed_tags_ready), ENT_COMPAT, 'UTF-8');
				}
			}
		} else {
			foreach ($languages as $language){
				$data['seller']['description'][$language['language_id']]['description'] = htmlspecialchars(nl2br($data['seller']['description']), ENT_COMPAT, 'UTF-8');
			}
		}

		if (empty($json['errors'])) {

			if ($this->config->get('msconf_change_seller_nickname') || empty($seller)) {
				// SEO urls generation for sellers
				if ($this->config->get('msconf_enable_seo_urls_seller')) {
					$latin_check = '/[^\x{0030}-\x{007f}]/u';
					$non_latin_chars = preg_match($latin_check, $data['seller']['nickname']);
					if ($this->config->get('msconf_enable_non_alphanumeric_seo') && $non_latin_chars) {
						$data['seller']['keyword'] = implode("-", str_replace("-", "", explode(" ", strtolower($data['seller']['nickname']))));
					}
					else {
						$data['seller']['keyword'] = trim(implode("-", str_replace("-", "", explode(" ", preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($data['seller']['nickname']))))), "-");
					}
				}
			}

			if (empty($seller) || (!empty($seller) && $seller['ms.seller_status'] == MsSeller::STATUS_INCOMPLETE)) {
				$data['seller']['approved'] = 0;
				// create new seller
				switch ($this->config->get('msconf_seller_validation')) {

					case MsSeller::MS_SELLER_VALIDATION_APPROVAL:
						$MailSellerAwaitingModeration = $serviceLocator->get('MailSellerAwaitingModeration', false)
							->setTo($this->registry->get('customer')->getEmail())
							->setData(array('addressee' => $this->registry->get('customer')->getFirstname()));
						$mails->add($MailSellerAwaitingModeration);

						$MailAdminSellerAwaitingModeration = $serviceLocator->get('MailAdminSellerAwaitingModeration', false)
							->setTo($MultiMerchModule->getNotificationEmail())
							->setData(array(
								//'addressee' => $this->registry->get('customer')->getFirstname(),
								'seller_name' => $data['seller']['nickname'],
								'customer_name' => $this->customer->getFirstname() . ' ' . $this->customer->getLastname(),
								'customer_email' => $this->MsLoader->MsSeller->getSellerEmail($this->customer->getId()),
							));
						$mails->add($MailAdminSellerAwaitingModeration);

						$data['seller']['status'] = MsSeller::STATUS_INACTIVE;
						if ($this->config->get('msconf_allow_inactive_seller_products')) {
							$json['redirect'] = $this->url->link('account/account');
						} else {
							$json['redirect'] = $this->url->link('seller/account-profile');
						}
						break;

					case MsSeller::MS_SELLER_VALIDATION_NONE:
					default:
						$MailSellerAccountCreated = $serviceLocator->get('MailSellerAccountCreated', false)
							->setTo($this->registry->get('customer')->getEmail())
							->setData(array('addressee' => $this->registry->get('customer')->getFirstname()));
						$mails->add($MailSellerAccountCreated);

						$MailAdminSellerAccountCreated = $serviceLocator->get('MailAdminSellerAccountCreated', false)
							->setTo($MultiMerchModule->getNotificationEmail())
							->setData(array(
								'seller_name' => $data['seller']['nickname'],
								'customer_name' => $this->customer->getFirstname() . ' ' . $this->customer->getLastname(),
								'customer_email' => $this->MsLoader->MsSeller->getSellerEmail($this->customer->getId()),
							));
						$mails->add($MailAdminSellerAccountCreated);

						$data['seller']['status'] = MsSeller::STATUS_ACTIVE;
						$data['seller']['approved'] = 1;
						break;
				}

				$data['seller']['seller_id'] = $this->customer->getId();

				if (!empty($seller) && $seller['ms.seller_status'] == MsSeller::STATUS_INCOMPLETE) {
					$this->MsLoader->MsSeller->editSeller($data['seller']);
				} else {
					$this->MsLoader->MsSeller->createSeller($data['seller']);
				}

				$mailTransport->sendMails($mails);


				$this->session->data['success'] = $this->language->get('ms_account_sellerinfo_saved');
			} else {
				// edit seller
				$data['seller']['seller_id'] = $seller['seller_id'];
				$this->MsLoader->MsSeller->editSeller($data['seller']);

				$this->session->data['success'] = $this->language->get('ms_account_sellerinfo_saved');
			}

            /*------------------------------Remove seller cache-----------------------------------------*/
            $this->cache->delete("seller" . $data['seller']['seller_id']);
            /*----------------------------------------------------------------------------------------------------*/
		}

		$this->response->setOutput(json_encode($json));
	}

	public function index() {
		$this->document->addScript('catalog/view/javascript/plupload/plupload.js');
		$this->document->addScript('catalog/view/javascript/plupload/plupload.html5.js');
		$this->document->addScript('catalog/view/javascript/account-seller-profile.js');

		// rte
		if($this->config->get('msconf_enable_rte')) {
			$this->document->addScript('catalog/view/javascript/multimerch/ckeditor/ckeditor.js');
		}

		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->data['salt'] = $this->MsLoader->MsSeller->getSalt($this->customer->getId());
		$this->data['statusclass'] = 'warning';

		if ($seller) {
			switch ($seller['ms.seller_status']) {
				case MsSeller::STATUS_UNPAID:
				case MsSeller::STATUS_INCOMPLETE:
					$this->data['statusclass'] = 'warning';
					break;
				case MsSeller::STATUS_ACTIVE:
					$this->data['statusclass'] = 'success';
					break;
				case MsSeller::STATUS_DISABLED:
				case MsSeller::STATUS_DELETED:
					$this->data['statusclass'] = 'danger';
					break;
			}

			$this->data['seller'] = $seller; unset($this->data['seller']['banner']);

			if (!empty($seller['ms.avatar'])) {
				$this->data['seller']['avatar']['name'] = $seller['ms.avatar'];
				$this->data['seller']['avatar']['thumb'] = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
				$this->session->data['multiseller']['files'][] = $seller['ms.avatar'];
			} else {
				$this->data['seller']['avatar']['name'] = $this->data['seller']['avatar']['thumb'] = '';
			}

			$this->data['statustext'] = '';

			if ($seller['ms.seller_status'] != MsSeller::STATUS_INCOMPLETE) {
				$this->data['statustext'] = $this->language->get('ms_account_status') . $this->language->get('ms_seller_status_' . $seller['ms.seller_status']);
			}

			if ($seller['ms.seller_status'] == MsSeller::STATUS_INACTIVE && !$seller['ms.seller_approved']) {
				$this->data['statustext'] .= $this->language->get('ms_account_status_tobeapproved');
			}

			if ($seller['ms.seller_status'] == MsSeller::STATUS_INCOMPLETE) {
				$this->data['statustext'] .= $this->language->get('ms_account_status_please_fill_in');
			}

			foreach ($this->data['languages'] as $language){
				if(!isset($this->data['seller']['descriptions'][$language['language_id']]['description'])){
					$this->data['seller']['descriptions'][$language['language_id']]['description'] = '';
				}
			}

			$this->data['ms_account_sellerinfo_terms_note'] = '';
		} else {
			$this->data['seller'] = FALSE;


			$this->data['statustext'] = $this->language->get('ms_account_status_please_fill_in');

			if ($this->config->get('msconf_seller_terms_page')) {
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));

				if ($information_info) {
					$this->data['ms_account_sellerinfo_terms_note'] = sprintf($this->language->get('ms_account_sellerinfo_terms_note'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('msconf_seller_terms_page'), 'SSL'), $information_info['title'], $information_info['title']);
				} else {
					$this->data['ms_account_sellerinfo_terms_note'] = '';
				}
			} else {
				$this->data['ms_account_sellerinfo_terms_note'] = '';
			}
		}


		$this->data['seller_validation'] = $this->config->get('msconf_seller_validation');
		$this->data['link_back'] = $this->url->link('account/account', '', 'SSL');
		$this->document->setTitle($this->language->get('ms_account_sellerinfo_heading'));

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_dashboard_breadcrumbs'),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_sellerinfo_breadcrumbs'),
				'href' => $this->url->link('seller/account-profile', '', 'SSL'),
			)
		));

		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('account-profile');
		$this->response->setOutput($this->load->view($template, array_merge($this->data, $children)));
	}
}
?>
