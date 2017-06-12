<?php
use Dompdf\Dompdf;
use Dompdf\Options;
class ControllerMultimerchAccountOffer extends Controller
{
	private $error = array();
	private $logo = 'ms_no_image.jpg';

	public function index() {

		$offer_quantity = $this->MsLoader->MsOffer->getOfferQuantity($this->customer->getId());
		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());
		$offer_limit = $seller['ms.offer_limit'];

		if($offer_quantity >= $offer_limit){
			$this->session->data['error'] = $this->language->get('ms_offer_limit_error');
			$this->response->redirect($this->url->link('seller/account-offer'));

		} else {

			$this->document->addStyle('catalog/view/javascript/bootstrap/css/bootstrap.min.css');
			$this->document->addScript('catalog/view/javascript/ms-common.js');
			$this->document->addScript('catalog/view/javascript/account-offer.js');
			$this->document->addScript('catalog/view/javascript/plupload/plupload.js');
			$this->document->addScript('catalog/view/javascript/plupload/plupload.html5.js');

			$this->load->model('catalog/product');
			$this->load->language('checkout/cart');
			$this->load->language('multiseller/multiseller');
			$this->document->setTitle($this->language->get('ms_offer_title'));

			$data['breadcrumbs'] = array();
			$data['breadcrumbs'][] = array(
				'href' => $this->url->link('common/home'),
				'text' => $this->language->get('text_home')
			);
			$data['breadcrumbs'][] = array(
				'href' => $this->url->link('account/account', '', 'SSL'),
				'text' => $this->language->get('ms_text_account')
			);
			$data['breadcrumbs'][] = array(
				'href' => $this->url->link('seller/account-offer', '', 'SSL'),
				'text' => $this->language->get('ms_account_offers_breadcrumbs')
			);
			$data['breadcrumbs'][] = array(
				'href' => $this->url->link('multimerch/account_offer'),
				'text' => $this->language->get('ms_offer_title')
			);

			$data['text_recurring_item'] = $this->language->get('text_recurring_item');
			$data['text_next']           = $this->language->get('text_next');
			$data['text_next_choice']    = $this->language->get('text_next_choice');

			$data = array_merge($data, $this->load->language('multiseller/multiseller'));
			$data['heading_title'] = $this->language->get('ms_offer_title');

			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			$data['action'] = $this->url->link('checkout/cart/edit', '', true);

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$this->load->model('tool/image');
			$this->load->model('tool/upload');
			$this->load->model('catalog/product');

			$data['products'] = array();

			if(isset($this->request->get['cart']) && $this->request->get['cart'] == 'false' ){
				$products = array();
			} else {
				$products = $this->cart->getProducts();
			}

			$total_seller_netto  = 0;
			$total_client_netto  = 0;
			$total_seller_brutto = 0;
			$total_client_brutto  = 0;

			foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], $this->config->get($this->config->get('config_theme') . '_image_cart_width'), $this->config->get($this->config->get('config_theme') . '_image_cart_height'));
				} else {
					$image = '';
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				// Display prices
				$unit_price = 0;
				$customer_price = $this->MsLoader->MsOffer->priceForCustomer($product['product_id']);

				$product_tax = $this->tax->getRates($product['price'], $product['tax_class_id']);

				$tax = 0;
				foreach ($product_tax as $p){
					$tax = $p['rate'];
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $product['price'];

					$price = $this->currency->format($product['price'], $this->session->data['currency']);
					$total = $this->currency->format($customer_price * $product['quantity'] * (100 + $tax)/100, $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year'),
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				$data['products'][] = array(
					'product_id'=> $product['product_id'],
					'cart_id'   => $product['cart_id'],
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],
					'option'    => $option_data,
					'recurring' => $recurring,
					'quantity'  => $product['quantity'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'     => $price,
					'customer_price'=> $customer_price,
					'unit_price'=> $unit_price,
					'tax'       => $tax,
					'total'     => $total,
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);

				$total_seller_netto   += $unit_price * $product['quantity'];
				$total_seller_brutto  += $unit_price * $product['quantity'] * (100 + $tax)/100;
				$total_client_netto   += $customer_price * $product['quantity'];
				$total_client_brutto  += $customer_price * $product['quantity'] * (100 + $tax)/100;
			}

			$data['totals'] = array(
				'total_seller_netto'  => $this->currency->format($total_seller_netto, $this->session->data['currency']),
				'total_seller_brutto' => $this->currency->format($total_seller_brutto, $this->session->data['currency']),
				'total_client_netto'  => $this->currency->format($total_client_netto, $this->session->data['currency']),
				'total_client_brutto' => $this->currency->format($total_client_brutto, $this->session->data['currency'])
			);
			$data['profit'] =  $this->currency->format($total_client_brutto - $total_seller_brutto, $this->session->data['currency']);


			$data['total_services']['number'] = 0;
			$data['total_services']['text'] = $this->currency->format(0, $this->session->data['currency']);

			$data['continue'] = $this->url->link('common/home');
			$data['checkout'] = $this->url->link('checkout/checkout', '', true);
			$data['offer']      = $this->url->link('multimerch/account_offer', '', true);
			$data['offer_add'] = $this->url->link('multimerch/account_offer/submit', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('multimerch/account/offer', $data));
		}

	}


	public function uploadImage() {
		$json = array();
		$file = array();

		$json['errors'] = $this->MsLoader->MsFile->checkPostMax($_POST, $_FILES);

		if ($json['errors']) {
			return $this->response->setOutput(json_encode($json));
		}

		foreach ($_FILES as $file) {
			$errors = $this->MsLoader->MsFile->checkImage($file);

			if ($errors) {
				$json['errors'] = array_merge($json['errors'], $errors);
			} else {
				$fileName = $this->MsLoader->MsFile->uploadImage($file);
				$thumbUrl = $this->MsLoader->MsFile->resizeImage($this->config->get('msconf_temp_image_path') . $fileName, $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
				$json['files'][] = array(
					'name' => $fileName,
					'thumb' => $thumbUrl
				);
			}
		}

		return $this->response->setOutput(json_encode($json));
	}


	public function sendMail(){

		$this->load->language('multiseller/multiseller');

		$json = array();
		$data = $this->request->post;

		$data = array_merge($data, $this->load->language('multiseller/multiseller'));
		$data['submit'] = 'email';

		if( isset($data['offer_id']) && $data['offer_id'] != '' && $this->validate()){

			$this->MsLoader->MsOffer->saveOffer( $data );
			$offer_id = $data['offer_id'];

			$data['offer_info'] = $this->MsLoader->MsOffer->getOffer($offer_id, $this->customer->getId());

			$data['offer_info']['offer_by_image_thumb']  = $this->MsLoader->MsFile->resizeImage($data['offer_info']['offer_by_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
			$data['offer_info']['offer_for_image_thumb'] = $this->MsLoader->MsFile->resizeImage($data['offer_info']['offer_for_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));


			$data['services'] = json_decode($data['offer_info']['services'], true);

			$data['total_services']['number'] = 0;
			if( is_array($data['services']) ){
				foreach ($data['services'] as $key => $service){
					$data['total_services']['number'] += (float)$service['price'] * (100 +(float)$service['tax'])/100;
					$data['services'][$key]['price'] = $this->currency->format((float)$service['price'] * (100 + (float)$service['tax'])/100, $this->session->data['currency']);
				}
			} else {
				$data['services'] = array();
			}
			$data['total_services']['text'] = $this->currency->format($data['total_services']['number'], $this->session->data['currency']);



			$this->load->model('tool/image');
			$this->load->model('tool/upload');

			$products = $this->MsLoader->MsOffer->getProducts($offer_id);
			$total_client_netto  = 0;
			$total_client_brutto = 0;

			foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], $this->config->get($this->config->get('config_theme') . '_image_cart_width'), $this->config->get($this->config->get('config_theme') . '_image_cart_height'));
				} else {
					$image = '';
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				// Display prices

				$product_tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
				$tax = 0;
				foreach ($product_tax as $p){
					$tax = $p['rate'];
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $product['price'];

					$price = $this->currency->format($product['price'], $this->session->data['currency']);
					$total = $this->currency->format($product['price'] * $product['quantity'], $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year'),
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				if(isset($product['retail_price']) && $product['retail_price'] != ''){
					$price_ex_tax  = $product['retail_price']-$product['retail_price']*$product['discount']/100;
					$price_inc_tax = $price_ex_tax * ($tax + 100)/100;
				} else {
					$price_ex_tax  = 0;
					$price_inc_tax = 0;
				}

				$data['products'][] = array(
					'product_id'=> $product['product_id'],
					'offer_id'  => $product['offer_id'],
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],
					'option'    => $option_data,
					'recurring' => $recurring,
					'quantity'  => $product['quantity'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'     => $price,
					'discount'     => $product['discount'],
					'price_netto'  => $this->currency->format($price_ex_tax, $this->session->data['currency']),
					'price_brutto' => $this->currency->format($price_inc_tax, $this->session->data['currency']),
					'total'     =>  $this->currency->format(($price_inc_tax != 0 ? $price_inc_tax : $product['price']) * $product['quantity'], $this->session->data['currency']),
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);

				$total_client_netto  += $price_ex_tax * $product['quantity'];
				$total_client_brutto += $price_inc_tax * $product['quantity'];

			}

			$data['total_client_brutto'] = $this->currency->format($total_client_brutto, $this->session->data['currency']);
			$data['total_client_netto']  = $this->currency->format($total_client_netto, $this->session->data['currency']);

			$data['total_price'] = $data['total_services']['number'] + $total_client_brutto;
			$data['total_price'] = $this->currency->format($data['total_price'], $this->session->data['currency']);


			require_once( DIR_SYSTEM . 'library/dompdf/autoload.inc.php' );

			$dompdf = new Dompdf();
			$html   = '';
			ob_start();
			$this->pdf( $offer_id );
			$html .= ob_get_clean();

			$options = new Options();
			$options->setIsRemoteEnabled( true );
			$dompdf->setOptions( $options );

			$dompdf->loadHtml( $html );
			$dompdf->setPaper( 'A4', 'portrait' );
			$dompdf->render();
			$output = $dompdf->output();
			$path = DIR_IMAGE . 'offers/' . $data["name"] . '-' . $this->customer->getId() . '.pdf';
			file_put_contents($path, $output);


			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($data['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($data['by_name']);
			$mail->setSubject($data['name']);
			$mail->setHtml($this->load->view('mail/offer', $data));
			$mail->addAttachment($path);
			$mail->send();

			if ( file_exists($path) ) {
				unlink($path);
			}

			$json['success'] = $this->language->get('ms_email_send_success');


		} else {
			$json['error'] = $this->language->get('ms_send_mail_error');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	public function submit(){
		$data = $this->request->post;

		if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()){

			if(isset($data['offer_id']) && $data['offer_id'] != ''){

				$offer_id = $data['offer_id'];

				$images = $this->MsLoader->MsOffer->getOfferImages($offer_id);
				$delete_image  = $this->MsLoader->MsOffer->issetImage($data['by_image'], $offer_id);

				if($images[0]['offer_by_image'] != $this->logo && $images[0]['offer_by_image'] != $data['by_image'] && !$delete_image){

					$imgNameBy = explode('/', $images[0]['offer_by_image']);
					$imgNameBy = explode('.', $imgNameBy[2]);

					if ( file_exists(DIR_IMAGE . $images[0]['offer_by_image']) ) {
						unlink(DIR_IMAGE . 'tmp/' . $imgNameBy[0] . '-100x100.' . $imgNameBy[1]);
						unlink(DIR_IMAGE . $images[0]['offer_by_image']);
					}
				}

				$delete_image2  = $this->MsLoader->MsOffer->issetImage($data['for_image'], $offer_id);

				if($images[0]['offer_for_image'] != $this->logo && $images[0]['offer_for_image'] != $data['for_image'] && !$delete_image2){

					$imgNameFor = explode('/', $images[0]['offer_for_image']);
					$imgNameFor = explode('.', $imgNameFor[2]);

					if ( file_exists(DIR_IMAGE . $images[0]['offer_for_image']) ) {
						unlink(DIR_IMAGE . 'tmp/' . $imgNameFor[0] . '-100x100.' . $imgNameFor[1]);
						unlink(DIR_IMAGE . $images[0]['offer_for_image']);
					}
				}
			}

			if (isset($data['by_image']) && !empty($data['by_image'])) {

				if ($this->MsLoader->MsFile->checkFileAgainstSession($data['by_image'])) {
					$data['by_image'] = $this->MsLoader->MsFile->moveImage($data['by_image']);

					$type = end(explode('.', $this->request->post['by_image']));
					$length = strlen($type) + 1;

					if (file_exists(DIR_IMAGE . 'tmp/' . substr($this->request->post['by_image'], 0, -$length) . '-100x100.'. $type)) {
						unlink(DIR_IMAGE . 'tmp/' . substr($this->request->post['by_image'], 0, -$length) . '-100x100.'. $type);
					}
				}
			} else {
				$data['by_image'] = $this->logo;
			}

			if (isset($data['for_image']) && !empty($data['for_image'])) {
				if ($this->MsLoader->MsFile->checkFileAgainstSession($data['for_image'])) {
					$data['for_image'] = $this->MsLoader->MsFile->moveImage($data['for_image']);

					$type = end(explode('.', $this->request->post['by_image']));
					$length = strlen($type) + 1;

					if (file_exists(DIR_IMAGE . 'tmp/' . substr($this->request->post['for_image'], 0, -$length) . '-100x100.'. $type)) {
						unlink(DIR_IMAGE . 'tmp/' . substr($this->request->post['for_image'], 0, -$length) . '-100x100.'. $type);
					}
				}
			} else {
				$data['for_image'] = $this->logo;
			}

			foreach ($this->session->data['multiseller']['files'] as $key => $file){
				if ( file_exists(DIR_IMAGE . 'tmp/' . $file) ) {
					unlink(DIR_IMAGE . 'tmp/' . $file);
				}

				$arr = explode('.', $file);
				$fileName = $arr[0] . '.' . $arr[1];
				$fileType = $arr[2];

				if ( file_exists(DIR_IMAGE . 'tmp/' . $fileName . '-100x100.' . $fileType) ) {
					unlink(DIR_IMAGE . 'tmp/' . $fileName . '-100x100.' . $fileType);
				}

				unset ($this->session->data['multiseller']['files'][$key]);
			}

			if(isset($data['submit']) && $data['submit'] == 'save'){
				if( isset($offer_id) ){

					$this->MsLoader->MsOffer->saveOffer( $data );
					$this->session->data['success'] = 'Offer <a href="' . $this->url->link("seller/account-offer/viewOffer", "offer_id=" . $offer_id, "SSL") . '">&nbsp;' . $data['name'] . '</a>&nbsp;have been saved!';
					$this->response->redirect($this->url->link('seller/account-offer'));

				} else {
					$this->newOffer( $data, false);
				}


			} elseif(isset($data['submit']) && $data['submit'] == 'new'){

				$this->newOffer( $data, false );

			} elseif(isset($data['submit']) && $data['submit'] == 'pdf') {

				if ( isset( $offer_id ) ) {

					$this->MsLoader->MsOffer->saveOffer( $data );
					$offer_id = $data['offer_id'];

				} else {

					$offer_quantity = $this->MsLoader->MsOffer->getOfferQuantity( $this->customer->getId() );
					$seller         = $this->MsLoader->MsSeller->getSeller( $this->customer->getId() );
					$offer_limit    = $seller['ms.offer_limit'];

					if ( $offer_quantity >= $offer_limit ) {
						$data['offer_id'] = $this->MsLoader->MsOffer->getLastId();
						$this->MsLoader->MsOffer->saveOffer( $data );
						$offer_id = $data['offer_id'];

					} else {
						$offer_id = $this->newOffer( $data, true );
					}


				}

				require_once( DIR_SYSTEM . 'library/dompdf/autoload.inc.php' );

				$dompdf = new Dompdf();
				$html   = '';
				ob_start();
				$this->pdf( $offer_id );
				$html .= ob_get_clean();

				$options = new Options();
				$options->setIsRemoteEnabled( true );
				$dompdf->setOptions( $options );

				$dompdf->loadHtml( $html );
				$dompdf->setPaper( 'A4', 'portrait' );
				$dompdf->render();
				$dompdf->stream( $data['name'] . '.pdf' );

			}

		} else {

			$this->document->addStyle('catalog/view/javascript/bootstrap/css/bootstrap.min.css');

			$this->document->addScript('catalog/view/javascript/ms-common.js');
			$this->document->addScript('catalog/view/javascript/account-offer.js');
			$this->document->addScript('catalog/view/javascript/plupload/plupload.js');
			$this->document->addScript('catalog/view/javascript/plupload/plupload.html5.js');

			$this->load->model('catalog/product');
			$this->load->language('checkout/cart');
			$this->load->language('multiseller/multiseller');
			$this->document->setTitle($this->language->get('offer_title'));

			$data['breadcrumbs'] = array();
			$data['breadcrumbs'][] = array(
				'href' => $this->url->link('common/home'),
				'text' => $this->language->get('text_home')
			);
			$data['breadcrumbs'][] = array(
				'href' => $this->url->link('account/account', '', 'SSL'),
				'text' => $this->language->get('text_account')
			);
			$data['breadcrumbs'][] = array(
				'href' => $this->url->link('seller/account-offer', '', 'SSL'),
				'text' => $this->language->get('ms_account_offers_breadcrumbs')
			);
			$data['breadcrumbs'][] = array(
				'href' => $this->url->link('multimerch/account_offer'),
				'text' => $this->language->get('offer_title')
			);

			$data['heading_title'] = $this->language->get('offer_title');

			$data['text_recurring_item'] = $this->language->get('text_recurring_item');
			$data['text_next']           = $this->language->get('text_next');
			$data['text_next_choice']    = $this->language->get('text_next_choice');

			$data['column_image']    = $this->language->get('column_image');
			$data['column_name']     = $this->language->get('column_name');
			$data['column_model']    = $this->language->get('column_model');
			$data['column_quantity'] = $this->language->get('column_quantity');
			$data['column_price']    = $this->language->get('column_price');
			$data['column_total']    = $this->language->get('column_total');

			$data['button_update']   = $this->language->get('button_update');
			$data['button_remove']   = $this->language->get('button_remove');
			$data['button_shopping'] = $this->language->get('button_shopping');
			$data['button_checkout'] = $this->language->get('button_checkout');
			$data['button_offer']    = $this->language->get('button_offer');
			$data['ms_remove_selected'] = $this->language->get('ms_remove_selected');

			$data = array_merge($data, $this->load->language('multiseller/multiseller'));
			$data['heading_title'] = $this->language->get('offer_title');

			if (isset($this->error['name'])) {
				$data['error_name'] = $this->error['name'];
			}

			if (isset($this->error['date_start'])) {
				$data['error_date_start'] = $this->error['date_start'];
			}

			if (isset($this->error['date_end'])) {
				$data['error_date_end'] = $this->error['date_end'];
			}

			if (isset($this->error['by_name'])) {
				$data['error_by_name'] = $this->error['by_name'];
			}

			if (isset($this->error['for_name'])) {
				$data['error_for_name'] = $this->error['for_name'];
			}

			if (isset($this->error['by_email'])) {
				$data['error_by_email'] = $this->error['by_email'];
			}

			if (isset($this->error['for_email'])) {
				$data['error_for_email'] = $this->error['for_email'];
			}

			if (isset($this->error['for_address'])) {
				$data['error_for_address'] = $this->error['for_address'];
			}

			if (isset($this->error['by_address'])) {
				$data['error_by_address'] = $this->error['by_address'];
			}

			if (isset($this->error['by_phone'])) {
				$data['error_by_phone'] = $this->error['by_phone'];
			}

			if (isset($this->error['for_phone'])) {
				$data['error_for_phone'] = $this->error['for_phone'];
			}

			if (isset($this->error['limit'])) {
				$data['error_limit'] = $this->error['limit'];
			}

			if (isset($this->request->post['offer_id'])) {
				$data['offer_info']['offer_id'] = $this->request->post['offer_id'];
			}

			if (isset($this->request->post['name'])) {
				$data['offer_info']['offer_name'] = $this->request->post['name'];
			}

			if (isset($this->request->post['date_start'])) {
				$data['offer_info']['date_start'] = $this->request->post['date_start'];
			}

			if (isset($this->request->post['date_end'])) {
				$data['offer_info']['date_end'] = $this->request->post['date_end'];
			}

			if(isset($this->request->post['by_image'])){
				$data['offer_info']['offer_by_image'] = $this->request->post['by_image'];
				$data['offer_info']['offer_by_image_thumb']  = $this->MsLoader->MsFile->resizeImage($this->request->post['by_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
			}

			if (isset($this->request->post['by_name'])) {
				$data['offer_info']['offer_by_name'] = $this->request->post['by_name'];
			}

			if (isset($this->request->post['by_company'])) {
				$data['offer_info']['offer_by_company'] = $this->request->post['by_company'];
			}

			if (isset($this->request->post['by_nip'])) {
				$data['offer_info']['offer_by_nip'] = $this->request->post['by_nip'];
			}

			if (isset($this->request->post['by_email'])) {
				$data['offer_info']['offer_by_email'] = $this->request->post['by_email'];
			}

			if (isset($this->request->post['by_address'])) {
				$data['offer_info']['offer_by_address'] = $this->request->post['by_address'];
			}

			if (isset($this->request->post['by_phone'])) {
				$data['offer_info']['offer_by_phone'] = $this->request->post['by_phone'];
			}

			if(isset($this->request->post['for_image'])){
				$data['offer_info']['offer_for_image'] = $this->request->post['for_image'];
				$data['offer_info']['offer_for_image_thumb']  = $this->MsLoader->MsFile->resizeImage($this->request->post['for_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
			}

			if (isset($this->request->post['for_name'])) {
				$data['offer_info']['offer_for_name'] = $this->request->post['for_name'];
			}

			if (isset($this->request->post['for_company'])) {
				$data['offer_info']['offer_for_company'] = $this->request->post['for_company'];
			}

			if (isset($this->request->post['for_nip'])) {
				$data['offer_info']['offer_for_nip'] = $this->request->post['for_nip'];
			}

			if (isset($this->request->post['for_email'])) {
				$data['offer_info']['offer_for_email'] = $this->request->post['for_email'];
			}

			if (isset($this->request->post['for_address'])) {
				$data['offer_info']['offer_for_address'] = $this->request->post['for_address'];
			}

			if (isset($this->request->post['for_phone'])) {
				$data['offer_info']['offer_for_phone'] = $this->request->post['for_phone'];
			}

			$data['total_services']['number'] = (float)$this->request->post['total_serv'];
			$data['total_services']['text']   = $this->currency->format($data['total_services']['number'], $this->session->data['currency']);

			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			$data['action'] = $this->url->link('checkout/cart/edit', '', true);

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$this->load->model('tool/image');
			$this->load->model('tool/upload');

			$data['products'] = array();
			$this->load->model('catalog/product');

			$total_seller_netto  = 0;
			$total_client_netto  = 0;
			$total_seller_brutto = 0;
			$total_client_brutto = 0;

			$products = array();
			foreach (isset($_POST['product_id']) ? $_POST['product_id'] : $products as $product_id){
				$products[] = $this->model_catalog_product->getProduct($product_id);
			}

			foreach ($products as $product) {

				$product_total = 0;
				$stock = true;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], $this->config->get($this->config->get('config_theme') . '_image_cart_width'), $this->config->get($this->config->get('config_theme') . '_image_cart_height'));
				} else {
					$image = '';
				}

				// Display prices
				$unit_price = 0;

				$product_tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
				$tax = 0;
				foreach ($product_tax as $p){
					$tax = $p['rate'];
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $_POST['price'][$product['product_id']];
;
					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($_POST['final_price'][$product['product_id']] * $_POST['quantity'][$product['product_id']], $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}

				if ($product['subtract'] && (!$product['quantity'] || ($product['quantity'] < $_POST['quantity'][$product['product_id']]))) {
					$stock = false;
				}

				$data['products'][] = array(
					'product_id'  => $product['product_id'],
					'thumb'       => $image,
					'name'        => $product['name'],
					'model'       => $product['model'],
					'option'      => false,
					'quantity'    => $_POST['quantity'][$product['product_id']],
					'stock'       => $stock ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'      => false,
					'price'       => $price,
					'recurring'   => false,
					'unit_price'  => $unit_price,
					'tax'         => $tax,
					'retail_price'=> $_POST['retail_price'][$product['product_id']],
					'discount'    => $_POST['discount'][$product['product_id']],
					'price_ex_tax'=> $_POST['seller_price'][$product['product_id']],
					'price_inc_tax'=>$_POST['final_price'][$product['product_id']],
					'total'       => $total,
					'href'        => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);

				$total_seller_netto  += $unit_price * $_POST['quantity'][$product['product_id']];
				$total_seller_brutto += $unit_price * $_POST['quantity'][$product['product_id']] * (100 + $_POST['tax'][$product['product_id']]) / 100;
				$total_client_netto  += $_POST['seller_price'][$product['product_id']] * $_POST['quantity'][$product['product_id']];
				$total_client_brutto += $_POST['final_price'][$product['product_id']] * $_POST['quantity'][$product['product_id']];
			}

			$data['totals'] = array(
				'total_seller_netto'  => $this->currency->format($total_seller_netto, $this->session->data['currency']),
				'total_seller_brutto' => $this->currency->format($total_seller_brutto, $this->session->data['currency']),
				'total_client_netto'  => $this->currency->format($total_client_netto, $this->session->data['currency']),
				'total_client_brutto' => $this->currency->format($total_client_brutto, $this->session->data['currency'])
			);

			$data['profit'] = $this->currency->format($total_client_brutto - $total_seller_brutto, $this->session->data['currency']);

			$data['continue'] = $this->url->link('common/home');
			$data['checkout'] = $this->url->link('checkout/checkout', '', true);
			$data['offer']      = $this->url->link('multimerch/account_offer', '', true);
			$data['offer_add'] = $this->url->link('multimerch/account_offer/submit', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('multimerch/account/offer', $data));
		}

	}

	private function validate(){

		$this->load->language('multiseller/multiseller');

		if ((utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
			$this->error['name'] = $this->language->get('ms_validate_caption');
		}

		if ($this->request->post['date_start'] == '') {
			$this->error['date_start'] = $this->language->get('ms_validate_date');
		}

		if ($this->request->post['date_end'] == '') {
			$this->error['date_end'] = $this->language->get('ms_validate_date');
		}

		if ((utf8_strlen(trim($this->request->post['by_name'])) < 1) || (utf8_strlen(trim($this->request->post['by_name'])) > 32)) {
			$this->error['by_name'] = $this->language->get('ms_validate_name');
		}

		if ((utf8_strlen(trim($this->request->post['for_name'])) < 1) || (utf8_strlen(trim($this->request->post['for_name'])) > 32)) {
			$this->error['for_name'] = $this->language->get('ms_validate_name');
		}

		if( trim($this->request->post['by_address']) != ''){
			if ((utf8_strlen(trim($this->request->post['by_address'])) < 3) || (utf8_strlen(trim($this->request->post['by_address'])) > 128)) {
				$this->error['by_address'] = $this->language->get('ms_validate_address');
			}
		}

		if(trim($this->request->post['for_address']) != '') {
			if ( ( utf8_strlen( trim( $this->request->post['for_address'] ) ) < 3 ) || ( utf8_strlen( trim( $this->request->post['for_address'] ) ) > 128 ) ) {
				$this->error['for_address'] = $this->language->get( 'ms_validate_address' );
			}
		}

		if ((utf8_strlen($this->request->post['by_phone']) < 3) || (utf8_strlen($this->request->post['by_phone']) > 32)) {
			$this->error['by_phone'] = $this->language->get('ms_validate_phone');
		}

		if ((utf8_strlen($this->request->post['for_phone']) < 3) || (utf8_strlen($this->request->post['for_phone']) > 32)) {
			$this->error['for_phone'] = $this->language->get('ms_validate_phone');
		}

		if (!filter_var($this->request->post['by_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['by_email'] = $this->language->get('ms_validate_email');
		}

		if (!filter_var($this->request->post['for_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['for_email'] = $this->language->get('ms_validate_email');
		}

		if( (isset($this->request->post['submit']) && $this->request->post['submit'] == 'new') || ($this->request->post['submit'] == 'pdf' && !isset($this->request->post['offer_id'])) ){
			$offer_quantity = $this->MsLoader->MsOffer->getOfferQuantity($this->customer->getId());
			$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());
			$offer_limit = $seller['ms.offer_limit'];

			if($offer_quantity >= $offer_limit) {
				$this->error['limit'] = $this->language->get('ms_offer_limit_error');
			}
		}

		return !$this->error;
	}


	public function addToCart(){

		$this->load->language('multiseller/multiseller');

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];

			foreach ($quantity as $key => $value) {
				$this->cart->add($key, $value);
			}
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			$json['success'] = $this->language->get('ms_add_cart_success');

			$this->load->language('checkout/cart');

			// Totals
			$this->load->model('extension/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_extension_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);

						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

		} else {
			$json['error'] = $this->language->get('ms_add_cart_error');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	public function newOffer($data, $bool){

		$seller_id = $this->customer->getId();


		$offer_id = $this->MsLoader->MsOffer->addOffer( $seller_id, $data );
		$this->MsLoader->MsOffer->addOfferProducts( $offer_id, $data );

		$offer_quantity = $this->MsLoader->MsOffer->getOfferQuantity($seller_id);
		$seller = $this->MsLoader->MsSeller->getSeller($seller_id);
		$offer_limit = $seller['ms.offer_limit'];

		if($offer_quantity == $offer_limit){
			//send email to the administrator

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo('bihhi.iiyx@gmail.com');
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender('Admin');
			$mail->setSubject('Offer limit');
			$mail->setText('Seller - ' . $this->MsLoader->MsSeller->getSellerNickname($seller_id) . '(ID: ' . $seller_id .') have reached his limit for offers!');
			$mail->send();
		}

		if( $bool ){
			return $offer_id;
		} else {
			$this->session->data['success'] = 'Offer <a href="' . $this->url->link("seller/account-offer/viewOffer", "offer_id=" . $offer_id, "SSL") . '">&nbsp;' . $data['name'] . '</a>&nbsp;have been saved!';
			$this->response->redirect($this->url->link('seller/account-offer'));
		}
	}

	public function addProduct(){

		$json = array();

		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$arr = explode(",", $_POST['arr']);

		if( in_array($_POST['product_id'], $arr) ){
			$json['edit'] = array(
				'product_id' => $_POST['product_id'],
				'quantity'   => $_POST['quantity']
			);
		} else {

			$product = $this->model_catalog_product->getProduct($_POST['product_id']);

			$href = $this->url->link('product/product', 'product_id=' . $product['product_id']);

			$quantity = $_POST['quantity'];

			$customer_price = $this->MsLoader->MsOffer->priceForCustomer($product['product_id']);

			$product_tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
			$tax = 0;
			foreach ($product_tax as $p){
				$tax = $p['rate'];
			}

			$price = $this->currency->format($product['price'], $this->session->data['currency']);

			if ((float)$product['special']) {
				$total = $this->currency->format($customer_price * ($tax + 100)/100 * $quantity, $this->session->data['currency']);
				$special = $this->currency->format($product['special'], $this->session->data['currency']);
			} else {
				$special = false;
				$total = $this->currency->format($customer_price * ($tax + 100)/100 * $quantity, $this->session->data['currency']);
			}

			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get($this->config->get('config_theme') . '_image_cart_width'), $this->config->get($this->config->get('config_theme') . '_image_cart_height'));
			} else {
				$image = '';
			}

			$json['html'] = '';
			$json['html'] .= '<tr>';
			$json['html'] .= '<td class="text-center"><input name="remove-row" type="checkbox"/></td>';
			$json['html'] .= '<input class="product-id" type="hidden" name="product_id[]" value="'. $product["product_id"] .'" />';

			$json['html'] .= '<td class="text-center">';

			if ($image) {
				$json['html'] .= '<a href="' . $href . '"><img src="'. $image .'" alt="'. $product["name"] .'" title="'. $product["name"] .'" class="img-thumbnail" /></a>';
			}

			$json['html'] .= '</td>';
			$json['html'] .= '<td class="text-left"><a href="' . $href . '">' . $product["name"] . '</a></td>';
			$json['html'] .= '<td class="text-left">' . $product["model"] . '</td>';

			if($special){
				$json['html'] .= '<td class="text-right"><span>' . $special . '</span>';
				$json['html'] .= '<input id="price-' . $product["product_id"] . '" name="price[' . $product["product_id"] . ']" type="hidden" value="' . sprintf("%.2f", $product['special']) . '" />';
				$json['html'] .= '</td>';
			} else {
				$json['html'] .= '<td class="text-right"><span>' . $price .'</span>';
				$json['html'] .= '<input id="price-' . $product["product_id"] . '" name="price[' . $product["product_id"] . ']" type="hidden" value="' . sprintf("%.2f", $product['price']) . '" />';
				$json['html'] .= '</td>';
			}

			$json['html'] .= '<td class="text-center"><input id="retail-price-'.$product["product_id"].'" name="retail_price['.$product["product_id"].']" value="' . sprintf("%.2f", $customer_price) . '" type="number" min="0" step="any" class="form-control" onchange="newRetailPrice('. $product["product_id"] .');" /></td>';
			$json['html'] .= '<td class="text-center"><input id="discount-'.$product["product_id"].'" name="discount['.$product["product_id"].']" value="0" type="number" min="0" max="100" step="any" class="form-control" onchange="newRetailPrice('.$product["product_id"].');" /></td>';
			$json['html'] .= '<td class="text-center"><input id="seller-price-'.$product["product_id"].'" name="seller_price['.$product["product_id"].']" value="' . sprintf("%.2f", $customer_price) . '" type="number" min="0" step="any" class="form-control" onchange="newSellerPrice('.$product["product_id"].');" /></td>';
			$json['html'] .= '<td class="text-center"><input id="tax-'.$product["product_id"].'" name="tax[' . $product["product_id"] . ']" value="' . sprintf("%.2f", $tax) . '" type="number" min="0" step="any" required class="form-control" onchange="newTax(' . $product["product_id"]. ');" /></td>';
			$json['html'] .= '<td class="text-center"><input id="final-price-'.$product["product_id"].'" name="final_price['.$product["product_id"].']" type="number" step="any" class="form-control" value="'. sprintf("%.2f", $customer_price * ($tax + 100)/100) .'" onchange="newFinalPrice('.$product["product_id"].')" /></td>';
			$json['html'] .= '<td class="text-left"><input id="quantity-' . $product["product_id"] . '" name="quantity[' . $product["product_id"] . ']" value="'. $_POST["quantity"] . '" type="number" step="any" min="1" onchange="newQuantity(' . $product["product_id"] . ');" size="1" class="form-control" /></td>';
			$json['html'] .= '<td class="text-right"><span id="total-' . $product["product_id"] . '">' . $total . '</span></td>';
			$json['html'] .= '<td class="text-center"><a class="button" onclick="removeProduct(this);"><i class="fa fa-times-circle"></i></a></td>';
			$json['html'] .= '</tr>';

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function viewInvoice(){
		if(isset( $this->request->get['offer_id']) ){
			$this->pdf($this->request->get['offer_id'] );
		}
	}

	private function pdf($offer_id){
		$this->load->language('checkout/cart');
		$this->load->language('multiseller/multiseller');

		$this->document->setTitle($this->language->get('ms_view_invoice'));
		$title = $this->language->get('ms_view_invoice');

		$data['offer_date_start']   = $this->language->get('ms_offer_date_start');
		$data['offer_date_end']     = $this->language->get('ms_offer_date_end');
		$data['text_issued_by']     = $this->language->get('ms_issued_by');
		$data['text_offer_for']     = $this->language->get('ms_offer_for');
		$data['text_offer_nip']     = $this->language->get('ms_offer_nip');
		$data['ms_offer_company']   = $this->language->get('ms_offer_company');
		$data['ms_offer_phone']     = $this->language->get('ms_offer_phone');
		$data['ms_offer_address']   = $this->language->get('ms_offer_address');
		$data['ms_offer_email']     = $this->language->get('ms_offer_email');
		$data['ms_tab_products']    = $this->language->get('ms_tab_products');

		$data['column_image']    = $this->language->get('column_image');
		$data['column_name']     = $this->language->get('column_name');
		$data['column_model']    = $this->language->get('column_model');
		$data['column_quantity'] = $this->language->get('column_quantity');
		$data['column_total']    = $this->language->get('column_total');
		$data['ms_column_tax']   = $this->language->get('ms_column_tax');
		$data['ms_quantity_pdf'] = $this->language->get('ms_quantity_pdf');
		$data['ms_price_netto']  = $this->language->get('ms_price_netto');
		$data['ms_price_brutto'] = $this->language->get('ms_price_brutto');
		$data['ms_total_netto']  = $this->language->get('ms_price_netto');
		$data['ms_total_brutto'] = $this->language->get('ms_price_brutto');
		$data['ms_tab_services'] = $this->language->get('ms_tab_services');
		$data['ms_total_service']= $this->language->get('ms_total_service');
		$data['ms_total_price']  = $this->language->get('ms_total_price');

		$data['ms_service_text']   = $this->language->get('ms_service_text');
		$data['ms_service_price']  = $this->language->get('ms_service_price');
		$data['ms_column_tax']     = $this->language->get('ms_column_tax');
		$data['ms_service_final']  = $this->language->get('ms_service_final');

		$offer_info = $this->MsLoader->MsOffer->getOffer($offer_id, $this->customer->getId());

		$offer_info['offer_by_image_thumb']  = $this->MsLoader->MsFile->resizeImage($offer_info['offer_by_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
		$offer_info['offer_for_image_thumb'] = $this->MsLoader->MsFile->resizeImage($offer_info['offer_for_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));

		$offer_info['services'] = json_decode($offer_info['services'], true);
		$total_services['number'] = 0;
		if( is_array($offer_info['services']) ){
			foreach ($offer_info['services'] as $key => $service){
				$total_services['number'] += (float)$service['price'] * (100 + (float)$service['tax'])/100;
				$offer_info['services'][$key]['price'] = $this->currency->format((float)$service['price'], $this->session->data['currency']);
				$offer_info['services'][$key]['tax']   = (float)$service['tax'];
				$offer_info['services'][$key]['final_price'] = $this->currency->format((float)$service['price']  * (100 + (float)$service['tax'])/100, $this->session->data['currency']);
			}
		} else {
			$offer_info['services'] = array();
		}

		$total_services['text'] = $this->currency->format($total_services['number'], $this->session->data['currency']);

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$products = array();

		$products = $this->MsLoader->MsOffer->getProducts($offer_id);
		$result = array();
		$total_client_netto  = 0;
		$total_client_brutto = 0;

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			}

			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get($this->config->get('config_theme') . '_image_cart_width'), $this->config->get($this->config->get('config_theme') . '_image_cart_height'));
			} else {
				$image = '';
			}

			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}

			// Display prices

			$tax = $product['tax'];

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $product['price'];

				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$recurring = '';

			if ($product['recurring']) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($product['recurring']['trial']) {
					$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
				}

				if ($product['recurring']['duration']) {
					$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
				} else {
					$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
				}
			}

			if(isset($product['retail_price']) && $product['retail_price'] != ''){
				$price_ex_tax  = $product['retail_price']-$product['retail_price']*$product['discount']/100;
				$price_inc_tax = $price_ex_tax * ($product['tax'] + 100)/100;
			} else {
				$price_ex_tax  = 0;
				$price_inc_tax = 0;
			}

			$result[] = array(
				'product_id'=> $product['product_id'],
				'offer_id'  => $product['offer_id'],
				'thumb'     => $image,
				'name'      => $product['name'],
				'model'     => $product['model'],
				'option'    => $option_data,
				'recurring' => $recurring,
				'quantity'  => $product['quantity'],
				'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
				'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
				'price'     => $price,
				'tax'       => $tax,
				'discount'     => $product['discount'],
				'price_netto'  => $this->currency->format($price_ex_tax, $this->session->data['currency']),
				'price_brutto' => $this->currency->format($price_inc_tax, $this->session->data['currency']),
				'total'        => $this->currency->format(($price_inc_tax != 0 ? $price_inc_tax : $product['price']*(100+$tax)/100) * $product['quantity'], $this->session->data['currency']),
				'href'         => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);

			$total_client_netto  += $price_ex_tax * $product['quantity'];
			$total_client_brutto += $price_inc_tax * $product['quantity'];
		}

		$total_price = $this->currency->format($total_services['number'] + $total_client_brutto, $this->session->data['currency']);

		$total_client_brutto = $this->currency->format($total_client_brutto, $this->session->data['currency']);
		$total_client_netto  = $this->currency->format($total_client_netto, $this->session->data['currency']);

        include DIR_TEMPLATE . 'default/template/multimerch/account/offer_pdf.tpl' ;
	}

	public function newQuantity(){
		$json = array();

		$total_seller_netto  = 0;
		$total_seller_brutto = 0;
		$total_client_netto  = 0;
		$total_client_brutto  = 0;

		if( !empty($_POST['product_id']) ){
			foreach ($_POST['product_id'] as $key => $val) {

				$tax_class = $this->MsLoader->MsOffer->getProductTax($val);
				$product_tax = $this->tax->getRates($_POST['price'][$val], $tax_class);
				$tax_seller = 0;
				foreach ($product_tax as $p){
					$tax_seller = $p['rate'];
				}

				$total_seller_netto  += $_POST['price'][$val] * $_POST['quantity'][$val];
				$total_seller_brutto += $_POST['price'][$val] * $_POST['quantity'][$val] * (100 + $tax_seller)/100;

				$total_client_netto  += $_POST['seller_price'][$val] * $_POST['quantity'][$val];
				$total_client_brutto += $_POST['final_price'][$val] * $_POST['quantity'][$val];
			}
		}

		$json['client_netto']  = $this->currency->format($total_client_netto, $this->session->data['currency']);
		$json['client_brutto'] = $this->currency->format($total_client_brutto, $this->session->data['currency']);

		$json['seller_netto']  = $this->currency->format($total_seller_netto, $this->session->data['currency']);
		$json['seller_brutto'] = $this->currency->format($total_seller_brutto, $this->session->data['currency']);

		$json['profit'] = $this->currency->format($total_client_brutto - $total_seller_brutto, $this->session->data['currency']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function countTotal(){
		$json = array();

		$json['total'] = $this->currency->format($_POST['price'] * $_POST['quantity'], $this->session->data['currency']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function totalService(){
		$json = array();

		$json['total']['number'] = (float)$_POST['total'] - (float)$_POST['old'] * (100 + (float)$_POST['tax_old'])/100 + (float)$_POST['price'] * (100 + (float)$_POST['tax'])/100;
		$json['total']['text']   = $this->currency->format($json['total']['number'], $this->session->data['currency']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeService(){
		$json = array();

		$json['total']['number'] = (float)$_POST['total'] - (float)$_POST['price'];
		$json['total']['text']   = $this->currency->format($json['total']['number'], $this->session->data['currency']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}