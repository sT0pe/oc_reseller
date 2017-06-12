<?php

class ControllerAccountMSMessage extends Controller {
	private $data = array();

	public function __construct($registry) {
		parent::__construct($registry);
		
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/msconversation', '', 'SSL');
			return $this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}
		
		if (!$this->config->get('mmess_conf_enable')) return $this->response->redirect($this->url->link('account/account', '', 'SSL'));
	}
	
	public function jxSendMessage() {
		$this->data = array_merge($this->data, $this->load->language('multiseller/multiseller'));
		$conversation_id = $this->request->post['conversation_id'];

		if (!$conversation_id || $conversation_id == 0) {

			$customer_name = $this->customer->getFirstname() . ' ' . $this->customer->getLastname();

			$title = $this->request->post['title'] . ': ' . $customer_name;
			$offer_id = isset($this->request->post['offer_id']) ? (int)$this->request->post['offer_id'] : 0;

			$conversation_id = $this->MsLoader->MsConversation->createConversation(
				array(
					'title' => $title,
					'conversation_from' => $this->customer->getId(),
					'offer_id' => $offer_id
				)
			);

			$participants = array($this->customer->getId());

			$this->MsLoader->MsConversation->addConversationParticipants($conversation_id, $participants);
		}

		$conversation_participants_ids = $this->MsLoader->MsConversation->getConversationParticipantsIds($conversation_id);
		if (!in_array($this->customer->getId(), $conversation_participants_ids)){
			return;
		}
		
		$conversation = $this->MsLoader->MsConversation->getConversations(array(
			'conversation_id' => $conversation_id,
			'single' => 1
		));
		
		if (!$conversation) return;

		$message_text = trim($this->request->post['ms-message-text']);
	
		$json = array();
	
		if (empty($message_text)) {
			$json['errors'][] = $this->language->get('ms_error_empty_message');
			$this->response->setOutput(json_encode($json));
			return;
		}

		if (mb_strlen($message_text) > 2000) {
			$json['errors'][] = $this->language->get('ms_error_contact_text');
		}
		
		if (!isset($json['errors'])) {
			$message_id = $this->MsLoader->MsMessage->createMessage(
				array(
					'conversation_id' => $conversation_id,
					'from' => $this->customer->getId(),
					'message' => $message_text
				)
			);

			if(isset($this->request->post['attachments'])) {
				$this->load->model('tool/upload');
				foreach ($this->request->post['attachments'] as $attachment_code) {
					$upload = $this->model_tool_upload->getUploadByCode($attachment_code);
					if(isset($upload['upload_id'])) $this->MsLoader->MsMessage->createMessageAttachment($message_id, $upload['upload_id']);
				}
			}

			$this->MsLoader->MsConversation->sendMailForParticipants($conversation_id, $message_text);
			
			$json['success'] = $this->language->get('ms_sellercontact_success');
			$json['redirect'] = $this->url->link('account/msmessage&conversation_id=' . $conversation_id, '', 'SSL');
		}
		$this->response->setOutput(json_encode($json));
	}


	public function jxUploadAttachment() {
		$this->load->language('tool/upload');
		$this->load->language('multiseller/multiseller');

		$json = array();

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Validate file extension
			$json['error'] = $this->MsLoader->MsFile->checkFile($this->request->files['file'], $this->config->get('msconf_msg_allowed_file_types'));

			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);

			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (empty($json['error'])) {
			unset($json['error']);

			// Hide the uploaded file name so people can not link to it directly.
			$file = $filename . '.' . token(32);

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

			$this->load->model('tool/upload');
			$json['code'] = $this->model_tool_upload->addUpload($filename, $file);
			$json['filename'] = $filename;
			$json['success'] = $this->language->get('text_upload');
		}

		return $this->response->setOutput(json_encode($json));
	}


	public function index() {
		$this->document->addScript('catalog/view/javascript/multimerch/account-message.js');
		$this->MsLoader->MsHelper->addStyle('multimerch_messaging');
		$this->data = array_merge($this->data, $this->load->language('multiseller/multiseller'));
		$this->language->load('account/account');
		$customer_id = $this->customer->getId();

		if(isset($this->request->get['offer_id']) && $this->MsLoader->MsConversation->getOfferConversation($this->request->get['offer_id'])){
			$conversation_id = $this->MsLoader->MsConversation->getOfferConversation($this->request->get['offer_id']);
		} else if (isset($this->request->get['offer_id'])){
			$this->data['offer_id'] = $this->request->get['offer_id'];
			$conversation_id = isset($this->request->get['conversation_id']) ? $this->request->get['conversation_id'] : false;
		} else {
			$conversation_id = isset($this->request->get['conversation_id']) ? $this->request->get['conversation_id'] : false;
		}

		if($conversation_id === 0){
			if (!$conversation_id || !$this->MsLoader->MsConversation->isParticipant($conversation_id, array('participant_id' => $customer_id)))
				return $this->response->redirect($this->url->link('account/msconversation', '', 'SSL'));

		}

		$messages = $this->MsLoader->MsMessage->getMessages(
			array(
				'conversation_id' => $conversation_id
			),
			array(
				'order_by'  => 'date_created',
				'order_way' => 'ASC',
			)
		);

		foreach ($messages as $m) {
			$sender_type_id = $m['from_admin'] ? MsConversation::SENDER_TYPE_ADMIN : ($m['seller_sender'] ? MsConversation::SENDER_TYPE_SELLER : MsConversation::SENDER_TYPE_CUSTOMER);
			$sender = $m['from_admin'] ? $m['user_sender'] : ($m['seller_sender'] ? $m['seller_sender'] : $m['customer_sender']);

			$this->data['messages'][] = array_merge(
				$m,
				array(
					'date_created' => date($this->language->get('datetime_format'), strtotime($m['date_created'])),
					'sender_type_id' => $sender_type_id,
					'sender' => ((utf8_strlen($sender) > 20) ? utf8_substr($sender, 0, 20) . '..' : $sender) . ($m['from_admin'] ? ' (' . $this->language->get('ms_account_conversations_sender_type_' . MsConversation::SENDER_TYPE_ADMIN) . ')': '')
				)
			);
		}
		
		$this->data['conversation'] = $this->MsLoader->MsConversation->getConversations(array(
			'conversation_id' => $conversation_id,
			'single' => 1
		));

		if(isset($this->data['offer_id'])){
			$this->data['conversation']['title'] = sprintf($this->language->get('ms_conversation_title_offer'), $this->data['offer_id']);
		}

		$this->document->setTitle($this->language->get('ms_account_messages_heading'));

		// Breadcrumbs
		$breadcrumbs = array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_conversations_breadcrumbs'),
				'href' => $this->url->link('account/msconversation', '', 'SSL'),
			),
			array(
				'text' => isset($this->data['conversation']['title']) ? $this->data['conversation']['title'] : $this->language->get('ms_new_conversation'),
				'href' => $this->url->link('account/msmessage', '&conversation_id=' . $conversation_id, 'SSL'),
			)
		);
		
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs($breadcrumbs);
		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('account-message');
		$this->response->setOutput($this->load->view($template, array_merge($this->data, $children)));
	}
}

?>
