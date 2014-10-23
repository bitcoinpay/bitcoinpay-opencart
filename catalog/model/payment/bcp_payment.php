<?php 
class ModelPaymentBCPPayment extends Model {
 public function getMethod($address, $total) {
		$this->load->language('payment/bcp_payment');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('bcp_payment_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (0 > $total) {
			$status = false;
		} elseif (!$this->config->get('bcp_payment_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$currencies = array(
			'AUD',
			'CAD',
			'EUR',
			'GBP',
			'JPY',
			'USD',
			'NZD',
			'CHF',
			'HKD',
			'SGD',
			'SEK',
			'DKK',
			'PLN',
			'NOK',
			'HUF',
			'CZK',
			'ILS',
			'MXN',
			'MYR',
			'BRL',
			'PHP',
			'TWD',
			'THB',
			'TRY'
		);

		if (!in_array(strtoupper($this->currency->getCode()), $currencies)) {
			$status = false;
		}
    //Getting button variant
    $btnVar = $this->config->get('bcp_payment_buttons');

		$method_data = array();
		if ($status) {
      if($btnVar == 4){
        $method_data = array(
  				'code'       => 'bcp_payment',
  				'title'      => $this->language->get('text_title'),
  				'sort_order' => $this->config->get('bcp_payment_sort_order')
  			);
      }
      else{
      $titleImg = "<img src=\"/bcp/img/0{$btnVar}.png\" alt=\"Bitcoin pay\">";
        $method_data = array(
  				'code'       => 'bcp_payment',
  				'title'      => $titleImg,
  				'sort_order' => $this->config->get('bcp_payment_sort_order')
  			);
      }

		}

		return $method_data;
	}
}
?>