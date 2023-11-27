<?php

namespace Omnipay\Square\Message;

use Omnipay\Common\Message\AbstractRequest;
use Square;

/**
 * Square Purchase Request
 */
class WebPaymentRequest extends AbstractRequest
{
    public function getAccessToken()
    {
        return $this->getParameter('accessToken');
    }

    public function setAccessToken($value)
    {
        return $this->setParameter('accessToken', $value);
    }

    public function getLocationId()
    {
        return $this->getParameter('locationId');
    }

    public function setLocationId($value)
    {
        return $this->setParameter('locationId', $value);
    }

    public function getData()
    {
        $items = $this->getItems();

        $items_list = [];

        if (!empty($items) && count($items) > 0) {
            foreach ($items as $index => $item) {
				$money = new Square\Models\Money();
				$money->setAmount($item->getPrice() * 100);
				$money->setCurrency($this->getCurrency());
				
				$order_line_item = new Square\Models\OrderLineItem((string) $item->getQuantity());
				$order_line_item->setName($item->getName());
				$order_line_item->setBasePriceMoney(call_user_func(function() use ($item) {
					$money = new Square\Models\Money();
					$money->setAmount($item->getPrice() * 100);
					$money->setCurrency($this->getCurrency());
				}));
				
                $items_list[$index] = $order_line_item;
            }
        }
		
		$order = new Square\Models\Order($this->getLocationId());
		$order->setReferenceId($this->getTransactionReference());
		$order->setLineItems($items_list);
		
		$order_request = new Square\Models\CreateOrderRequest();
	    $order_request->setIdempotencyKey(uniqid());
	    $order_request->setOrder($order);

        $data = new Square\Models\CreateCheckoutRequest(uniqid(), $order_request);
		$data->setAskForShippingAddress(true);
		$data->setRedirectUrl($this->getReturnUrl());

        return $data;
    }

    public function sendData($data)
    {
	    $environment = Square\Environment::PRODUCTION;
	    
	    if($this->getParameter('testMode')) {
		    $environment = Square\Environment::SANDBOX;
	    }
	    
	    $api_client = new Square\SquareClient([
		    'accessToken' => $this->getAccessToken(),
		    'environment' => $environment
	    ]);
	    
	    $api_instance = $api_client->getCheckoutApi();

        try {
	        $api_response = $api_instance->createCheckout($this->getLocationId(), $data);
	        $result = $api_response->getResult();
            $checkout = $result->getCheckout();
            $response = [
                'id' => $checkout->getId(),
                'checkout_url' => $checkout->getCheckoutPageUrl()
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'detail' => 'Exception when creating web payment request: ' . $e->getMessage()
            ];
        }

        return $this->createResponse($response);
    }

    public function createResponse($response)
    {
        return $this->response = new WebPaymentResponse($this, $response);
    }
}
