<?php
class ControllerPaymentBCPPayment extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/bcp_payment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('bcp_payment', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_authorization'] = $this->language->get('text_authorization');
		$this->data['text_sale'] = $this->language->get('text_sale');

		$this->data['entry_email'] = $this->language->get('entry_email');
    $this->data['entry_api'] = $this->language->get('entry_api');
    $this->data['entry_password'] = $this->language->get('entry_password');
    $this->data['entry_currency'] = $this->language->get('entry_currency');

    $this->data['entry_buttons'] = $this->language->get('entry_buttons');
    $this->data['entry_buttons_text'] = $this->language->get('entry_buttons_text');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

    if (isset($this->error['api'])) {
			$this->data['error_api'] = $this->error['api'];
		} else {
			$this->data['error_api'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),      		
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/bcp_payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/bcp_payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['bcp_payment_email'])) {
			$this->data['bcp_payment_email'] = $this->request->post['bcp_payment_email'];
		} else {
			$this->data['bcp_payment_email'] = $this->config->get('bcp_payment_email');
		}

    if (isset($this->request->post['bcp_payment_api'])) {
			$this->data['bcp_payment_api'] = $this->request->post['bcp_payment_api'];
		} else {
			$this->data['bcp_payment_api'] = $this->config->get('bcp_payment_api');
		}

    if (isset($this->request->post['bcp_payment_password'])) {
			$this->data['bcp_payment_password'] = $this->request->post['bcp_payment_password'];
		} else {
			$this->data['bcp_payment_password'] = $this->config->get('bcp_payment_password');
		}

    if (isset($this->request->post['bcp_payment_currency'])) {
			$this->data['bcp_payment_currency'] = $this->request->post['bcp_payment_currency'];
		} else {
			$this->data['bcp_payment_currency'] = $this->config->get('bcp_payment_currency');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['bcp_payment_geo_zone_id'])) {
			$this->data['bcp_payment_geo_zone_id'] = $this->request->post['bcp_payment_geo_zone_id'];
		} else {
			$this->data['bcp_payment_geo_zone_id'] = $this->config->get('bcp_payment_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['bcp_payment_status'])) {
			$this->data['bcp_payment_status'] = $this->request->post['bcp_payment_status'];
		} else {
			$this->data['bcp_payment_status'] = $this->config->get('bcp_payment_status');
		}

    if (isset($this->request->post['bcp_payment_buttons'])) {
			$this->data['bcp_payment_buttons'] = $this->request->post['bcp_payment_buttons'];
		} else {
			$this->data['bcp_payment_buttons'] = $this->config->get('bcp_payment_buttons');
		}

		if (isset($this->request->post['bcp_payment_sort_order'])) {
			$this->data['bcp_payment_sort_order'] = $this->request->post['bcp_payment_sort_order'];
		} else {
			$this->data['bcp_payment_sort_order'] = $this->config->get('bcp_payment_sort_order');
		}

		$this->template = 'payment/bcp_payment.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/bcp_payment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

    if (!$this->request->post['bcp_payment_api']) {
			$this->error['api'] = $this->language->get('error_api');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>