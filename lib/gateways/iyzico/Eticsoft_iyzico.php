<?php
@ini_set('display_errors', 'on');
require_once(dirname(__FILE__) . '/php/IyzipayBootstrap.php');
IyzipayBootstrap::init();

class EticSoft_iyzico
{

    public function pay($tr)
    {
		//
		# taksit sorgusu
		$options = EticSoft_iyzico::options($tr);

		if ($tr->gateway_params->tdmode == 'off') {
			$is3d = false;
		}
		else if($tr->gateway_params->tdmode == 'on') {
			$is3d = true;
		}
		else {
			$request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
			$request->setLocale(\Iyzipay\Model\Locale::TR);
			$request->setConversationId($orderid);
			$request->setBinNumber(substr($tr->cc_number, 0 ,6));
			$request->setPrice(number_format($tr->total_pay, 2, '.', ''));

			# make request
			$installmentInfo = \Iyzipay\Model\InstallmentInfo::retrieve($request, $options);
			if($installmentInfo->getStatus() == 'success'){
				$inst_result = json_decode($installmentInfo->getRawResult());
				$card_type = isset($inst_result->installmentDetails[0]->cardType) ? $inst_result->installmentDetails[0]->cardType : 'UNKNOWN';
				if($inst_result->installmentDetails[0]->force3ds == 1 OR $card_type == 'DEBIT_CARD')
					$is3d = true;
				else
					$is3d = false;
			}
		}
		//

# create request class
        $request = new \Iyzipay\Request\CreatePaymentRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($tr->id_cart);
        $request->setPrice(number_format($tr->total_cart,2, '.', ''));
        $request->setPaidPrice(number_format($tr->total_pay, 2, '.', ''));
        $request->setCurrency(\Iyzipay\Model\Currency::TL);
        $request->setInstallment($tr->installment);
        $request->setBasketId($tr->id_cart);
        $request->setPaymentChannel(\Iyzipay\Model\PaymentChannel::WEB);
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);

        $paymentCard = new \Iyzipay\Model\PaymentCard();
        $paymentCard->setCardHolderName($tr->cc_name);
        $paymentCard->setCardNumber($tr->cc_number);
        $paymentCard->setExpireMonth($tr->cc_expire_month);
        $paymentCard->setExpireYear('20'.substr($tr->cc_expire_year, -2));
        $paymentCard->setCvc($tr->cc_cvv);
        $paymentCard->setRegisterCard(0);
        $request->setPaymentCard($paymentCard);

        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId($tr->id_customer);
        $buyer->setName($tr->customer_firstname);
        $buyer->setSurname($tr->customer_lastname);
        $buyer->setGsmNumber($tr->customer_mobile);
        $buyer->setEmail($tr->customer_email);
        $buyer->setIdentityNumber("11111111111");
        //$buyer->setLastLoginDate("2015-10-05 12:43:35");
        //$buyer->setRegistrationDate("2013-04-21 15:12:09");
        $buyer->setRegistrationAddress($tr->customer_address);
        $buyer->setIp($tr->cip);
        $buyer->setCity($tr->customer_city);
        $buyer->setCountry("Turkey");
     //   $buyer->setZipCode("34732");
        $request->setBuyer($buyer);

        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($tr->customer_firstname.' '.$tr->customer_lastname);
        $shippingAddress->setCity($tr->customer_city);
        $shippingAddress->setCountry("Turkey");
        $shippingAddress->setAddress($tr->customer_address);
    //    $shippingAddress->setZipCode("34742");
        $request->setShippingAddress($shippingAddress);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($tr->customer_firstname.' '.$tr->customer_lastname);
        $billingAddress->setCity($tr->customer_city);
        $billingAddress->setCountry("Turkey");
        $billingAddress->setAddress($tr->customer_address);
     // $billingAddress->setZipCode("34742");
        $request->setBillingAddress($billingAddress);

        $basketItems = array();
		
		$dr = ($tr->total_cart)/($tr->total_cart + $tr->total_discount); //discount hesaplama
		
		foreach ($tr->product_list as $p) {
 			$basket_item = new \Iyzipay\Model\BasketItem();
			$basket_item->setId($p['id_product']);
			$basket_item->setName($p['name']);
			$basket_item->setCategory1("Product");
			$basket_item->setCategory2("Product");
			$basket_item->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
			$price = (float)number_format(($p['price'] * $p['quantity'] * $dr) , 2, '.', '');
			$basket_item->setPrice($price);
			$basketItems []= $basket_item;
    }

		if ($tr->total_shipping > 0) {
            $basket_item = new \Iyzipay\Model\BasketItem();
            $basket_item->setId(1);
			$priceca = (float)number_format(($tr->total_shipping * $dr) , 2, '.', '');
            $basket_item->setPrice ($priceca);
            $basket_item->setName("Cargo");
            $basket_item->setCategory1("Cargo");
            $basket_item->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
			$basketItems []= $basket_item;
		}

		$request->setBasketItems($basketItems);

		 //print_r($request);
		 //exit;

		if($is3d){
			$request->setCallbackUrl($tr->ok_url);
			$response = \Iyzipay\Model\ThreedsInitialize::create($request, $options);

			$status = $response->getStatus() == 'success' ? 'success' : 'fail';
			$result = json_decode($response->getRawResult());

			if($status == 'success'){
				die(base64_decode($result->threeDSHtmlContent));
			}
			$tr->result_code = $result->errorCode;
			$tr->result_message = $result->errorMessage;
			return $tr;
		}

		$response = \Iyzipay\Model\Payment::create($request,  $options);
        $tr->result = $response->getStatus() == 'success' ? true : false;
		$result = json_decode($response->getRawResult());
		$tr->result_code = $result->errorCode;
		$tr->result_message = $result->errorMessage;
		return $tr;
    }

    public static function options($tr)
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey($tr->gateway_params->apikey);
        $options->setSecretKey($tr->gateway_params->secretkey);
        if ($tr->gateway_params->test_mode == 'on')
            $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        else
            $options->setBaseUrl("https://api.iyzipay.com");
        return $options;
    }

    public static function test()
    {
        # make request
        $iyzipayResource = \Iyzipay\Model\ApiTest::retrieve(EticSoft_iyzico::options());
        # print result
        print_r($iyzipayResource);
    }


	public function tdValidate($tr){
		$tr->result_code	= $_POST['mdStatus'];
		$tr->result_message 	= '3D Secure Doğrulaması Başarısız';

		if($_POST['status'] == 'success' AND (int)$_POST['mdStatus'] == 1){
			$options = EticSoft_iyzico::options($tr);
			$request = new \Iyzipay\Request\CreateThreedsPaymentRequest();
			$request->setLocale(\Iyzipay\Model\Locale::TR);
			$request->setConversationId($_POST['conversationId']);
			$request->setPaymentId($_POST['paymentId']);
			$request->setConversationData($_POST['conversationData']);
			$threedsPayment = \Iyzipay\Model\ThreedsPayment::create($request, $options);

			$tr->result = $threedsPayment->getStatus() == 'success' ? true : false;
			$result = json_decode($threedsPayment->getRawResult());
			$tr->result_code	= '3D-'.$result->errorCode;
			$tr->result_message	= $result->errorMessage;
		}
		return $tr;

	}

}

// $ico = new EticSoft_iyzico();
// $ico->run($tr);
// EticSoft_iyzico::test();
