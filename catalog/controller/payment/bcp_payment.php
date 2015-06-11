<?php
class ControllerPaymentBCPPayment extends Controller {
	protected function index() {
		$this->language->load('payment/bcp_payment');

		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/bcp_payment.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/bcp_payment.tpl';
			} else {
				$this->template = 'default/template/payment/bcp_payment.tpl';
			}

      $this->render();
		}

	}

	public function callback() {
    $this->log->write('Callback function was called');
    $inputData = file_get_contents('php://input');
    $this->log->write("input data: " . $inputData);
    $payResponse = json_decode($inputData);
    $this->log->write("payResponse: " . $payResponse);

    if (!function_exists('getallheaders'))
    {
        function getallheaders()
        {
           $headers = '';
           foreach ($_SERVER as $name => $value)
           {
               if (substr($name, 0, 5) == 'HTTP_')
               {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
               }
           }
       return $headers;
       }
    }

    function getSignature($headers) {
        array_change_key_case($headers, CASE_LOWER);
        $this->log->write("changedArray: " . print_r($headers));
        return $headers['bpsignature'];
    }

    //callback password
    if(($callbackPass = $this->config->get('bcp_payment_password'))!= NULL){
      $paymentHeaders = getallheaders();
      $this->log->write("paymentHeaders: " . print_r($paymentHeaders));
      $digest = getSignature($paymentHeaders);

      $this->log->write("digest: " . $digest);

      $hashMsg = $inputData . $callbackPass;
      $checkDigest = hash('sha256', $hashMsg);

      if (strcmp($digest, $checkDigest) == 0){
        $security = 1;
      }
      else{
        $security = 0;
      }
    }
    else{
      $security = 1;
    }

    $this->log->write("security: " . $security);

    //payment status
    $paymentStatus = $payResponse -> status;
    $this->log->write('paymentStatus: ' . $paymentStatus);

    //order id
    $preOrderId = json_decode($payResponse -> reference);
    $orderId =  $preOrderId -> order_number;

    //confirmation process
    $this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($orderId);

    if ($order_info && $security) {


    if ($paymentStatus != NULL) {

      $this->log->write("paymentStatus: " . $paymentStatus);
				$order_status_id = $this->config->get('config_order_status_id');
                $this->log->write("order_status_id: " . $order_status_id);

				switch($paymentStatus) {
					case 'confirmed':
							$order_status_id = 5; //complete
						break;
					case 'pending':
						$order_status_id = 1; //pending
						break;
          case 'received':
						$order_status_id = 1; //pending
						break;
          case 'insufficient_amount':
						$order_status_id = 7; //cancel
						break;
          case 'invalid':
						$order_status_id = 7; //cancel
						break;
          case 'timeout':
						$order_status_id = 14; //expired
						break;

				}

				if (!$order_info['order_status_id']) {
					$this->model_checkout_order->confirm($orderId, $order_status_id);
				} else {
					$this->model_checkout_order->update($orderId, $order_status_id);
				}
			} else {
				$this->model_checkout_order->confirm($orderId, $this->config->get('config_order_status_id'));
			}

    }
	}
  public function paysend() {

    //Getting API-ID from config
    $apiID = $this->config->get('bcp_payment_api');

    //test mode check
    $testMode = 0; //if set to 1, test mode will be set
    if (!$testMode) {
			$payurl = 'https://www.bitcoinpay.com/api/v1/payment/btc';
		} else {
			$payurl = 'https://bitcoinpaycom.apiary-mock.com/api/v1/payment/btc';
		}

    //data preparation
    $this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

    $price = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
    $idoforder = $order_info['order_id'];
    $cname = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
    $csurname = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
    $cnamecomplete = "{$cname} {$csurname}";
    $cemail = html_entity_decode($order_info['email'], ENT_QUOTES, 'UTF-8');

    //additional customer data
    $customData = array(
        'customer_name' => $cnamecomplete,
        'order_number' => intval($idoforder),
        'customer_email' => $cemail
    );
    $jCustomData = json_encode($customData);

    //data packing
    //additional checks
    $notiEmail = $this->config->get('bcp_payment_email');
    $lang = $this->session->data['language'];
    $settCurr = $this->config->get('bcp_payment_currency');

    if(strlen($settCurr)!=3){
      $settCurr = "BTC";
    }

    $postData = array(
        'settled_currency' => $settCurr,
        'return_url' => $this->url->link('payment/bcp_payment/return_url'),
        'notify_url' => $this->url->link('payment/bcp_payment/callback', '', 'SSL'),
        'price' => floatval($price),
        'currency' => $order_info['currency_code'],
        'reference' => json_decode($jCustomData)
    );

    if (($notiEmail !== NULL) && (strlen($notiEmail) > 5)){
        $postData['notify_email'] = $notiEmail;
        }
    if ((strcmp($lang, "cs") !== 0)||(strcmp($lang, "en") !== 0)||(strcmp($lang, "de") !== 0)){
        $postData['lang'] = "en";
    }
    else{
        $postData['lang'] = $lang;
    }

    $content = json_encode($postData);

    //sending data via cURL
    $curlheaders = array(
    "Content-type: application/json",
    "Authorization: Token {$apiID}",
    );
    $curl = curl_init($payurl);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,$curlheaders);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //bypassing ssl verification, because of bad compatibility
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);


    //sending to server, and waiting for response
    $response = curl_exec($curl);

    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $jHeader = substr($response, 0, $header_size);
    $jBody = substr($response, $header_size);

    $jHeaderArr = $this -> get_headers_from_curl_response($jHeader);

    //http response code
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    //callback password check
    if(($callbackPass = $this->config->get('bcp_payment_password'))!= NULL){
      $digest = getSignature($jHeaderArr[0]);


      $hashMsg = $jBody . $callbackPass;
      $checkDigest = hash('sha256', $hashMsg);

      if (strcmp($digest, $checkDigest) == 0){
        $security = 1;
      }
      else{
        $security = 0;
      }
    }
    else{
      $security = 1;
    }

    if ( $status != 200 ) {
        die("Error: call to URL {$payurl} failed with status {$status}, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl) . "<br /> Please contact shop administrator...");
        curl_close($curl);
    }
    elseif(!$security){
      die("Error: Callback password does not match! <br />Please contact shop administrator...");
      curl_close($curl);
    }
    else{
      curl_close($curl);

      $response = json_decode($jBody);

      //adding paymentID to payment method
      $prePaymentMethod = html_entity_decode($order_info['payment_method'], ENT_QUOTES, 'UTF-8');
      $finPaymentMethod = $prePaymentMethod . "<br /><strong>PaymentID: </strong>" . $response -> data -> payment_id;

      $paymentQuery = $this->db->query("UPDATE `" . DB_PREFIX . "order` SET `payment_method` = '" . $finPaymentMethod . "' WHERE `order_id` = " . $order_info['order_id']);

      //redirect to pay gate
      $paymentUrl = $response -> data -> payment_url;
      header("Location: {$paymentUrl}");
      die();
    }
  }

  private function get_headers_from_curl_response($headerContent){
        $headers = array();

        // Split the string on every "double" new line.
        $arrRequests = explode("\r\n\r\n", $headerContent);

        // Loop of response headers. The "count() -1" is to
        //avoid an empty row for the extra line break before the body of the response.
        for ($index = 0; $index < count($arrRequests) -1; $index++) {

            foreach (explode("\r\n", $arrRequests[$index]) as $i => $line)
            {
                if ($i === 0)
                    $headers[$index]['http_code'] = $line;
                else
                {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$index][$key] = $value;
                }
            }
        }

        return $headers;
    }
    public function return_url(){
      $returnStatus = $this->request->get['bitcoinpay-status'];

      $this->log->write("Status: " . $returnStatus);

      if(strcmp($returnStatus,"true") == 0){
        $this->log->write("IF - TRUE");
        $statusUrl = $this->url->link('checkout/success');
      }
      else{
        $this->log->write("IF - ELSE");
        $statusUrl = $this->url->link('payment/bcp_payment/fail_url');
      }

      $this->log->write("before header");
      //redirect to status url
      header("Location: {$statusUrl}");
      die();
    }
    public function fail_url() {

		if (isset($this->session->data['order_id'])) {

			$this->cart->clear();



			unset($this->session->data['shipping_method']);

			unset($this->session->data['shipping_methods']);

			unset($this->session->data['payment_method']);

			unset($this->session->data['payment_methods']);

			unset($this->session->data['guest']);

			unset($this->session->data['comment']);

			unset($this->session->data['order_id']);

			unset($this->session->data['coupon']);

			unset($this->session->data['reward']);

			unset($this->session->data['voucher']);

			unset($this->session->data['vouchers']);

			unset($this->session->data['totals']);

		}



		$this->language->load('payment/bcp_payment_fail');



		$this->document->setTitle($this->language->get('heading_title'));



		$this->data['breadcrumbs'] = array();



		$this->data['breadcrumbs'][] = array(

			'href'      => $this->url->link('common/home'),

			'text'      => $this->language->get('text_home'),

			'separator' => false

		);



		$this->data['breadcrumbs'][] = array(

			'href'      => $this->url->link('checkout/cart'),

			'text'      => $this->language->get('text_basket'),

			'separator' => $this->language->get('text_separator')

		);



		$this->data['breadcrumbs'][] = array(

			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),

			'text'      => $this->language->get('text_checkout'),

			'separator' => $this->language->get('text_separator')

		);



		$this->data['breadcrumbs'][] = array(

			'href'      => $this->url->link('checkout/success'),

			'text'      => $this->language->get('text_success'),

			'separator' => $this->language->get('text_separator')

		);



		$this->data['heading_title'] = $this->language->get('heading_title');



		if ($this->customer->isLogged()) {

			$this->data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));

		} else {

			$this->data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));

		}



		$this->data['button_continue'] = $this->language->get('button_continue');



		$this->data['continue'] = $this->url->link('common/home');



		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {

			$this->template = $this->config->get('config_template') . '/template/common/success.tpl';

		} else {

			$this->template = 'default/template/common/success.tpl';

		}



		$this->children = array(

			'common/column_left',

			'common/column_right',

			'common/content_top',

			'common/content_bottom',

			'common/footer',

			'common/header'

		);



		$this->response->setOutput($this->render());

	}
}
?>
