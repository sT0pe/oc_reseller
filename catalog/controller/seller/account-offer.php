<?php
class ControllerSellerAccountOffer extends ControllerSellerAccount {
	private $logo = 'ms_no_image.jpg';


	public function index() {

		$this->document->addStyle('catalog/view/javascript/multimerch/datatables/css/jquery.dataTables.css');
		$this->document->addScript('catalog/view/javascript/multimerch/datatables/js/jquery.dataTables.min.js');

		$this->data['link_back'] = $this->url->link('account/account', '', 'SSL');

		$this->document->setTitle($this->language->get('ms_account_offer_information'));

		$this->data['offer'] = $this->url->link('multimerch/account_offer', 'cart=false', true);

		if( isset($this->session->data['error'])){
			$this->data['error'] = $this->session->data['error'];
			unset($this->session->data['error']);
		}

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_offers_breadcrumbs'),
				'href' => $this->url->link('seller/account-offer', '', 'SSL'),
			)
		));

		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('account-offer');
		$this->response->setOutput($this->load->view($template, array_merge($this->data, $children)));
	}


	public function getTableData() {

		$colMap = array(
		);

		$sorts = array('offer_id', 'date_created', 'offer_name', 'total_amount');
		$filters = array_merge($sorts, array('products'));

		list($sortCol, $sortDir) = $this->MsLoader->MsHelper->getSortParams($sorts, $colMap);
		$filterParams = $this->MsLoader->MsHelper->getFilterParams($filters, $colMap);

		$seller_id = $this->customer->getId();

		$offers = $this->MsLoader->MsOffer->getOffers(
			array(
				'seller_id' => $seller_id
			),
			array(
				'order_by'  => $sortCol,
				'order_way' => $sortDir,
				'offset' => $this->request->get['iDisplayStart'],
				'limit' => $this->request->get['iDisplayLength'],
				'filters' => $filterParams
			),
			array(
				'total_amount' => 1
			)
		);


		$total_offers = isset($offers[0]) ? $offers[0]['total_rows'] : 0;
		$this->load->model('tool/upload');
		$columns = array();

		foreach ($offers as $offer) {
			$offer_products = $this->MsLoader->MsOffer->getOfferProducts(array('offer_id' => $offer['offer_id'], 'seller_id' => $seller_id));

			$products = "";
			$offer_total = 0;

			foreach ($offer_products as $p) {
				$products .= "<p style='text-align:left'>";
				$products .= "<span class='name'>" . ($p['quantity'] > 1 ? "{$p['quantity']} x " : "") . "<a href='" . $this->url->link('product/product', 'product_id=' . $p['product_id'], 'SSL') . "'>{$p['name']}</a></span>";

				$products .= "<span class='total'>" . $this->currency->format($p['retail_price'] * $p['quantity'] * (100 + $p['tax'])/100 ) . "</span>";
				$products .= "</p>";

				$offer_total += $p['retail_price'] * $p['quantity'] * (100 + $p['tax'])/100;
			}

			$actions  = '<a class="icon-view" href="' . $this->url->link('seller/account-offer/viewOffer', 'offer_id=' . $offer['offer_id'], 'SSL') . '" title="' . $this->language->get('ms_view_modify') . '"><i class="fa fa-file-text-o "></i></a>';
			$actions .= '<a class="icon-invoice" target="_blank" href="' . $this->url->link('multimerch/account_offer/viewInvoice', 'offer_id=' . $offer['offer_id'], 'SSL') . '" title="' . $this->language->get('ms_view_invoice') . '"><i class="fa fa-search"></i></a>';
			$actions .= '<a class="icon-message" href="' . $this->url->link('account/msmessage', 'offer_id=' . $offer['offer_id'], 'SSL') . '" title="' . $this->language->get('ms_view_conversation') . '"><i class="fa fa-envelope-o"></i></a>';
			$actions .= '<a class="icon-remove btn-delete-offer" href="' . $this->url->link('seller/account-offer/removeOffer', 'offer_id=' . $offer['offer_id'], 'SSL') . '" title="' . $this->language->get('ms_remove_offer') . '"><i class="fa fa-trash-o"></i></a>';


			$columns[] = array_merge(
				$offer,
				array(
					'offer_id' => $offer['offer_id'],
					'offer_name' => '<a href="' . $this->url->link('seller/account-offer/viewOffer', 'offer_id=' . $offer['offer_id'], 'SSL') . '">' .$offer['offer_name'] . '</a>',
					'products' => $products,
					'date_created' => date($this->language->get('date_format_short'), strtotime($offer['date_created'])),
					'total_amount' => $this->currency->format($offer_total),
					'action_offer' => $actions
				)
			);
		}

		$this->response->setOutput(json_encode(array(
			'iTotalRecords' => $total_offers,
			'iTotalDisplayRecords' => $total_offers,
			'aaData' => $columns
		)));
	}


	public function viewOffer() {

		$this->document->addStyle('catalog/view/javascript/bootstrap/css/bootstrap.min.css');

		$this->document->addScript('catalog/view/javascript/ms-common.js');
		$this->document->addScript('catalog/view/javascript/account-offer.js');
		$this->document->addScript('catalog/view/javascript/plupload/plupload.js');
		$this->document->addScript('catalog/view/javascript/plupload/plupload.html5.js');

		$offer_id = isset($this->request->get['offer_id']) ? (int)$this->request->get['offer_id'] : 0;

		$data['offer_info'] = $this->MsLoader->MsOffer->getOffer($offer_id, $this->customer->getId());
		if( !$data['offer_info'] ){
			$this->session->data['error'] = 'Non existing offer';
			$this->response->redirect($this->url->link('seller/account-offer'));
		}

		$data['services'] = json_decode($data['offer_info']['services'], true);
		$data['total_services']['number'] = 0;
		if( is_array($data['services']) ){
			foreach ($data['services'] as $service){
				$data['total_services']['number'] += (float)$service['price'] * (100 + (float)$service['tax'])/100;
			}
		} else {
			$data['services'] = array();
		}

		$data['total_services']['text'] = $this->currency->format($data['total_services']['number'], $this->session->data['currency']);

		$data['offer_info']['offer_by_image_thumb']  = $this->MsLoader->MsFile->resizeImage($data['offer_info']['offer_by_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
		$data['offer_info']['offer_for_image_thumb'] = $this->MsLoader->MsFile->resizeImage($data['offer_info']['offer_for_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));

		$this->load->language('checkout/cart');
		$this->load->language('multiseller/multiseller');
		$this->document->setTitle($this->language->get('ms_offer_title_view'));

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
			'href' => $this->url->link('seller/account-offer/viewOffer', 'offer_id=' . $offer_id, 'SSL'),
			'text' => $data['offer_info']['offer_name']
		);

		$data['continue'] = $this->url->link('common/home');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['offer']     = $this->url->link('multimerch/account_offer', '', true);
		$data['offer_add'] = $this->url->link('multimerch/account_offer/submit', '', true);

			$data['text_recurring_item'] = $this->language->get('text_recurring_item');
			$data['text_next'] = $this->language->get('text_next');
			$data['text_next_choice'] = $this->language->get('text_next_choice');

			$data = array_merge($data, $this->load->language('multiseller/multiseller'));

			$data['heading_title'] = $this->language->get('ms_offer_title_view');

			if (!$this->MsLoader->MsOffer->hasStock($offer_id) && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
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

			$this->load->model('tool/image');
			$this->load->model('tool/upload');
			$this->load->model('catalog/product');



			$data['products'] = array();

			$total_seller_netto = 0;
		    $total_client_netto = 0;
			$total_seller_brutto = 0;
			$total_client_brutto = 0;
			$products = $this->MsLoader->MsOffer->getProducts($offer_id);

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

				$product_tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
				$tax_seller = 0;
				foreach ($product_tax as $p){
					$tax_seller = $p['rate'];
				}

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
					$price_inc_tax = $price_ex_tax*(100 + $product['tax'])/100;
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
					'unit_price'=> $unit_price,

					'retail_price'  => $product['retail_price'],
					'discount'      => $product['discount'],
					'price_ex_tax'  => $price_ex_tax,
					'tax'           => $product['tax'],
					'price_inc_tax' => $price_inc_tax,

					'total'         => $this->currency->format(($price_inc_tax != 0 ? $price_inc_tax : $unit_price * (100 + $product['tax'])/100) * $product['quantity'], $this->session->data['currency']),
					'total_seller'  => $unit_price * (100 + $product['tax'])/100 * $product['quantity'],
					'href'          => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);

				$total_seller_netto  += $unit_price * $product['quantity'];
				$total_seller_brutto += $unit_price * $product['quantity'] * (100 + $tax_seller)/100;
				$total_client_netto  += $price_ex_tax * $product['quantity'];
				$total_client_brutto += $price_inc_tax * $product['quantity'];
			}

			$data['totals'] = array(
				'total_seller_netto'  => $this->currency->format($total_seller_netto, $this->session->data['currency']),
				'total_seller_brutto' => $this->currency->format($total_seller_brutto, $this->session->data['currency']),
				'total_client_netto'  => $this->currency->format($total_client_netto, $this->session->data['currency']),
				'total_client_brutto' => $this->currency->format($total_client_brutto, $this->session->data['currency']),
			);
			$data['profit'] = $this->currency->format($total_client_brutto - $total_seller_brutto, $this->session->data['currency']);


			$this->load->model('extension/extension');
			$data['modules'] = array();
			$files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');

			if ($files) {
				foreach ($files as $file) {
					$result = $this->load->controller('extension/total/' . basename($file, '.php'));

					if ($result) {
						$data['modules'][] = $result;
					}
				}
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('multimerch/account/offer', $data));
	}


	public function removeOffer() {

		$offer_id = isset($this->request->get['offer_id']) ? (int)$this->request->get['offer_id'] : 0;


		$images = $this->MsLoader->MsOffer->getOfferImages($offer_id);

		if( $images[0]['offer_by_image'] != $this->logo ){
			$imgNameBy = explode('/', $images[0]['offer_by_image']);
			$imgNameBy = explode('.', $imgNameBy[2]);

			if( !$this->MsLoader->MsOffer->issetImage($images[0]['offer_by_image'], $offer_id) ) {

				if ( file_exists(DIR_IMAGE . $images[0]['offer_by_image']) ) {

					unlink(DIR_IMAGE . 'tmp/' . $imgNameBy[0] . '-100x100.' . $imgNameBy[1]);
					unlink(DIR_IMAGE . $images[0]['offer_by_image']);
				}

			}
		}

		if( $images[0]['offer_for_image'] != $this->logo ){
			$imgNameFor = explode('/', $images[0]['offer_for_image']);
			$imgNameFor = explode('.', $imgNameFor[2]);

			if( !$this->MsLoader->MsOffer->issetImage($images[0]['offer_for_image'], $offer_id) ){
				if ( file_exists(DIR_IMAGE . $images[0]['offer_for_image']) ) {
					unlink(DIR_IMAGE . 'tmp/' . $imgNameFor[0] . '-100x100.' . $imgNameFor[1]);
					unlink(DIR_IMAGE . $images[0]['offer_for_image']);
				}
			}
		}

		$this->MsLoader->MsOffer->removeOffer($offer_id);

		$this->session->data['success'] = $this->language->get('ms_remove_offer_success');
		$this->response->redirect($this->url->link('seller/account-offer'));
	}


}