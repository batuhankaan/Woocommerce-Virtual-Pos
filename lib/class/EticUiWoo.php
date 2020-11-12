<?php

class EticUiWoo extends EticUi
{

	function __construct()
	{
		$this->store_uri = get_site_url();
		$this->store_url = get_permalink(wc_get_page_id('shop'));
		$this->uri = plugins_url() . '/sanalpospro/';
		$this->url = plugins_url() . '/sanalpospro/';
	}

	public function l($txt)
	{ // translate
		return __($txt, 'sanalpospro');
	}

	public function displayPrice($price, $currency = null)
	{
		return $price . ' ' . $currency;
	}

	public function addCSS($file, $external = false)
	{
		return '<link rel="stylesheet" href="' . $this->uri . $file . '" type="text/css" media="all">';
	}

	public function displayProductInstallments($price)
	{
//		$product = New Product(Etictools::getValue('id_product'));
//		if (EticInstallment::getProductRestriction($product->id_category_default))
//			return '<section><br/><div class="alert alert-info">Bu ürün kanun gereği taksitli olarak satılamamaktadır.'
//				. ' Kredi kartınızdan taksitsiz olarak ödeyebilirsiniz.</div></section>';
		$prices = EticInstallment::getRates($price);
		if (count($prices) < 1)
			return;
		$return = '<div class="row">';
		$block_count = 0;
		foreach ($prices as $f => $v) {
			$block_count++;
			if ($block_count == 4) {
				$return .= '</div><div class="row">';
			}
			$return .= '<div class="col-lg-4 col-sm-4 col-xs-6 eticsoft_spr_bank">
				<div class="eticsoft_inst_container ' . $f . '">
					<div class="block_title" align="center"><img src="' . $this->uri . 'img/cards/' . $f . '.png"></div>';
			$return .= '<table class="table">
						<tr>
							<th>' . $this->l('Taksit Sayısı') . '</th>
							<th>' . $this->l('Aylık Ödeme') . '</th>
							<th>' . $this->l('Toplam Tutar') . '</th>
						</tr>';
			foreach ($v as $k => $ins) {
				$return .= '<tr class="' . ($k % 2 ? $f . '-odd' : '' ) . '">
				<td>' . $k . '</td>
				<td>' . EticUiWoo::displayPrice($ins['month']) . '</td>
				<td>' . EticUiWoo::displayPrice($ins['total']) . '</td>
			</tr>';
			}
			$return .= '</table></div></div>';
		}
		$return .= '<div class="col-lg-4 col-sm-4 col-xs-6 eticsoft_spr_bank">
				<div class="eticsoft_inst_container">
					<div class="block_title"><h3>' . $this->l('Diğer Kartlar') . '</h3></div>
					' . $this->l('Tüm kredi ve bankamatik kartları ile tek çekim olarak ödeme yapabilirsiniz.') . '
					<hr/>
					<img class="col-sm-12 img-responsive" src="' . $this->uri . 'img/master_visa_aexpress.png"/>
					</div>
					</div>';
		$return .= '</div></section>';
		return $return;
	}

	public function displayAdminOrder($tr)
	{
		
		$order = new WC_Order($tr->id_order);
		$order->get_currency();
		$cur = Etictools::getCurrency($tr->currency_number, 'iso_number');
		$currency = $cur->iso_code;

		$t = '
		<!-- SPR EticSoft Order Details -->
			<li class="wide">
			<hr/>
		<div class="eticsoft">
			<div align="center">
				<h2 align="center" class="spp_head">
					' . $this->l('Credit Card Process Details') . '
				</h2>
				<br/>
				<h4 style="color: #f52a2a;"> UYARI! SİPARİŞİ BANKA YÖNETİCİ EKRANINDAN KONTROL ETMEDEN KARGOLAMAYINIZ!</h4>
					<a href="https://sanalpospro.com" target="_blank">
						<img src="' . $this->url . '/logo.png" width="180px"/>
					</a> <br/>
				<hr/>
				<table class="wp-list-table widefat fixed striped posts">
					<tr>
						<td>' . $this->l('POS answer:') . ' ' . $tr->result_code . '<br/>
						<span class="badge">' . $tr->date_update . '</span>'
					. '</td>'
					. '<td>#' . $tr->boid . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Total Amount') . '</td>
						<td><span style="font-size:2em;">' . $this->displayPrice($tr->total_pay) . '</span>
						' . $currency . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Customer Fee Commission') . '</td>
						<td><span class="badge badge-warning">' . $this->displayPrice($tr->total_pay - $tr->total_cart, $currency) . '</span></td>
					</tr>
					<tr>
						<td>' . $this->l('POS System Fee') . '</td>
						<td><span class="badge badge-danger">' . $this->displayPrice($tr->gateway_fee, $currency) . '</span></td>
					</tr>
					<tr>
						<td>' . $this->l('E-Shop Remaining Amount') . '</td>
						<td><span class="badge badge-success" style="font-size:2em;">' . $this->displayPrice($tr->total_pay - $tr->gateway_fee) . '</span>
							' . $currency . '</td>
					</tr>
					<tr>
						<td colspan="2">
							' . $this->l('IP Address') . ' <span class="badge">' . $tr->cip . '</span> <br/>
							' . $this->l('Transaction number') . ' <span class="badge">' . $tr->boid . '</span>
							<br/>	' . $tr->date_update . '
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center" >
							<img src="' . $this->url . '/img/gateways/' . $tr->gateway . '.png" width="150px" />
						</td>
					</tr>
				</table>
				<div class="row align-center"><br/>
					<small>
						Bu bilgilerde bir hata olduğu düşünüyorsanız <a href="https://sanalpospro.com/contact">Hata Bildirimi</a>
					</small>
				</div>
			</div>
				<hr/>
			<div align="center">
				<h2 align="center" class="spp_head">
					' . $this->l('Credit Card Info') . '
				</h2>
				<table class="wp-list-table widefat fixed striped posts">
					<tr>
						<td>' . $this->l('Card Type') . '</td>
						<td><img src="' . $this->uri . 'img/cards/' . ($tr->family != '' ? $tr->family : 'default') . '.png" /></td>
					</tr>
					<tr>
						<td>' . $this->l('Installment') . '</td>
						<td>' . $tr->installment . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Card Name') . '</td>
						<td>' . $tr->cc_name . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Card No') . '</td>
						<td>' . $tr->cc_number . '</td>
					</tr>
				</table>
			</div>
			<hr/>
			<div align="center">
				<h2 align="center" class="spp_head">
						' . $this->l('Fraud - Risk Score') . '
				</h2>
				<div class="">
					<div style="margin: auto">
							<img style="margin:auto" src="' . $this->url . '/img/icons/icon_clock.png"/>
						</div>
					</div>
				</div>
		
					FP007 Powered By EticSoft R&D Lab
				<p>Dolandırıcılık Koruma Servisi\'nin Entegrasyonu Henüz Test Aşamasında</p>
				</div>
			</li>
					<!-- SPR EticSoft Order Details -->';
		return $t;
	}
}