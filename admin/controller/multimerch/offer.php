<?php

class ControllerMultimerchOffer extends ControllerMultimerchBase {
	private $error = array();

	public function __construct($registry) {
		parent::__construct($registry);
	}

	public function index(){
		$this->document->addScript('//code.jquery.com/ui/1.11.2/jquery-ui.min.js');
		$this->validate(__FUNCTION__);

		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['token'] = $this->session->data['token'];
		$this->data['heading'] = $this->language->get('ms_account_offers');
		$this->document->setTitle($this->language->get('ms_account_offers'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('ms_account_offers'),
			'href' => $this->url->link('multimerch/offer', 'token=' . $this->session->data['token'], 'SSL'),
		);

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('multiseller/offer-list.tpl', $this->data));
	}

	public function getTableData() {
		$colMap = array(
		);

		$sorts = array('offer_id', 'date_created', 'offer_name', 'total_amount');
		$filters = $sorts;

		list($sortCol, $sortDir) = $this->MsLoader->MsHelper->getSortParams($sorts, $colMap);
		$filterParams = $this->MsLoader->MsHelper->getFilterParams($filters, $colMap);

		$arr = array();
		if(isset($this->request->get['seller_id'])){
			$arr = array(
				'seller_id' => $this->request->get['seller_id']
			);
		}

		$offers = $this->MsLoader->MsOffer->getOffers(
			$arr,
			array(
				'order_by'  => $sortCol,
				'order_way' => $sortDir,
				'offset' => $this->request->get['iDisplayStart'],
				'limit' => $this->request->get['iDisplayLength'],
				'filters' => $filterParams
			),
			array(
				'total_amount' => 1,
				'products' => 1,
			)
		);

		$total_offers = isset($offers[0]) ? $offers[0]['total_rows'] : 0;
		$this->load->model('tool/upload');
		$columns = array();

		foreach ($offers as $offer) {
			$offer_products = $this->MsLoader->MsOffer->getOfferProducts(array('offer_id' => $offer['offer_id']));

			$offer_total = 0;

			foreach ($offer_products as $p) {
				$offer_total += $p['retail_price'] * (100 - $p['discount'])/100 * $p['quantity'] * (100 + $p['tax'])/100;
			}

			$columns[] = array_merge(
				$offer,
				array(
					'offer_id' => $offer['offer_id'],
					'offer_name' => '<a href="' . $this->url->link('multimerch/offer/viewOffer', 'token=' . $this->session->data['token'] . '&offer_id=' . $offer['offer_id'] . '&seller_id=' . $offer['seller_id'], 'SSL') . '">' .$offer['offer_name'] . '</a>',
					'date_created' => date($this->language->get('date_format_short'), strtotime($offer['date_created'])),
					'total_amount' => $this->currency->format($offer_total)
				)
			);
		}


		$this->response->setOutput(json_encode(array(
			'iTotalRecords' => $total_offers,
			'iTotalDisplayRecords' => $total_offers,
			'aaData' => $columns
		)));
	}

	public function viewOffer(){

		$this->document->setTitle('Offer Information');

		if( isset($this->request->get['offer_id']) ){
			$offer_id = $this->request->get['offer_id'];
		} else {
			$offer_id = 0;
		}

		$data = array();
		$data['offer_id'] = $offer_id;

		$data = array_merge($data, $this->load->language('multiseller/multiseller'));

		$this->document->setTitle($this->language->get('ms_heading_title_offer'));

		$data['heading_title'] = $this->language->get('heading_title');

		$data['tab_general'] = $this->language->get('tab_general');

		$data['token'] = $this->session->data['token'];

		$seller = $this->MsLoader->MsSeller->getSeller($this->request->get['seller_id']);

		if (isset($this->request->get['seller_id'])) {
			$data['seller_id'] = $this->request->get['seller_id'];
		} else {
			$data['seller_id'] = 0;
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('ms_catalog_sellers_breadcrumbs'),
			'href' => $this->url->link('multimerch/seller', 'token=' . $this->session->data['token'], 'SSL'),
		);

		$data['breadcrumbs'][] = array(
			'text' => $seller['ms.nickname'],
			'href' => $this->url->link('multimerch/seller/update', 'token=' . $this->session->data['token'] . '&seller_id=' . $this->request->get['seller_id'], 'SSL'),
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Offers',
			'href' => $this->url->link('multimerch/seller/update', 'token=' . $this->session->data['token'] . '&tab=offers&seller_id=' . $this->request->get['seller_id'], 'SSL'),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->MsLoader->MsOffer->getOfferName($offer_id),
			'href' => $this->url->link('multimerch/offer/viewOffer', 'token=' . $this->session->data['token'] . '&seller_id=' . $this->request->get['seller_id'] . '&offer_id=' . $offer_id, 'SSL'),
		);

		$data['offer_info'] = $this->MsLoader->MsOffer->getOffer($offer_id, $data['seller_id']);


		$data['offer_info']['services'] = json_decode($data['offer_info']['services'], true);
		$total_services['number'] = 0;
		if(is_array($data['offer_info']['services'])){
			foreach ($data['offer_info']['services'] as $key => $service){
				$total_services['number'] += (float)$service['price'] * (100 + (float)$service['tax'])/100;
				$data['offer_info']['services'][$key]['price'] = $this->currency->format((float)$service['price']* (100 + (float)$service['tax'])/100, $this->session->data['currency']);
			}
		} else {
			$data['offer_info']['services'] = array();
		}
		$data['total_services']['text'] = $this->currency->format($total_services['number'], $this->session->data['currency']);


		$data['offer_info']['offer_by_image_thumb']  = $this->MsLoader->MsFile->resizeImage($data['offer_info']['offer_by_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
		$data['offer_info']['offer_for_image_thumb'] = $this->MsLoader->MsFile->resizeImage($data['offer_info']['offer_for_image'], $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));


		$this->load->model('tool/image');
		$this->load->model('tool/upload');

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
				'tax'       => $product['tax'],
				'unit_price'=> $this->currency->format($unit_price, $this->session->data['currency']),

				'retail_price'  => $this->currency->format($product['retail_price'], $this->session->data['currency']),
				'discount'      => $product['discount'],
				'price_ex_tax'  => $this->currency->format($price_ex_tax, $this->session->data['currency']),
				'price_inc_tax' => $this->currency->format($price_inc_tax, $this->session->data['currency']),

				'total'         => $this->currency->format(($price_inc_tax != 0 ? $price_inc_tax : $unit_price * (100 + $product['tax'])/100) * $product['quantity'], $this->session->data['currency']),
				'total_seller'  => $unit_price * (100 + $tax_seller)/100 * $product['quantity'],
				'href'          => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);

			$total_seller_netto  += $unit_price * $product['quantity'];
			$total_client_netto  += $price_ex_tax * $product['quantity'];
			$total_seller_brutto += $unit_price * $product['quantity'] * (100 + $tax_seller)/100;
			$total_client_brutto += $price_ex_tax * $product['quantity'] * (100 + $product['tax'])/100;
		}

		$data['totals'] = array(
			'total_seller_netto'  => $this->currency->format($total_seller_netto, $this->session->data['currency']),
			'total_seller_brutto' => $this->currency->format($total_seller_brutto, $this->session->data['currency']),
			'total_client_netto'  => $this->currency->format($total_client_netto, $this->session->data['currency']),
			'total_client_brutto' => $this->currency->format($total_client_brutto, $this->session->data['currency']),
		);
		$data['profit'] = $this->currency->format($total_client_brutto - $total_seller_brutto, $this->session->data['currency']);


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('multiseller/offer', $data));
	}

}
?>
