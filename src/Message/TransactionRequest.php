<?php

namespace Omnipay\Square\Message;

use Omnipay\Common\Message\AbstractRequest;
use Square;

/**
 * Square Purchase Request
 */
class TransactionRequest extends AbstractRequest
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

    public function getCheckoutId()
    {
        return $this->getParameter('checkoutId');
    }

    public function setCheckoutId($value)
    {
        return $this->setParameter('ReceiptId', $value);
    }

    public function getTransactionId()
    {
        return $this->getParameter('transactionId');
    }

    public function setTransactionId($value)
    {
        return $this->setParameter('transactionId', $value);
    }

    public function getData()
    {
        $data = [];

        $data['checkoutId'] = $this->getCheckoutId();
        $data['transactionId'] = $this->getTransactionId();

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
	    
	    $api_instance = $api_client->getTransactionsApi();

        try {
	        $api_response = $api_instance->retrieveTransaction($this->getLocationId(), $data['transactionId']);
	        $result = $api_response->getResult();

            $orders = [];

            $lineItems = $result->getTransaction()->getTenders();
            if (count($lineItems) > 0) {
                foreach ($lineItems as $key => $value) {
                    $data = [];
                    $data['quantity'] = 1;
                    $data['amount'] = $value->getAmountMoney()->getAmount() / 100;
                    $data['currency'] = $value->getAmountMoney()->getCurrency();
                    $orders[] = $data;
                }
            }
	        
	        if ($error = $api_response->getErrors()) {
                $response = [
                    'status' => 'error',
                    'code' => $error['code'],
                    'detail' => $error['detail']
                ];
            } else {
                $response = [
                    'status' => 'success',
                    'transactionId' => $result->getTransaction()->getId(),
                    'referenceId' => $result->getTransaction()->getReferenceId(),
                    'orders' => $orders
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'detail' => 'Exception when calling LocationsApi->listLocations: ', $e->getMessage()
            ];
        }

        return $this->createResponse($response);
    }

    public function createResponse($response)
    {
        return $this->response = new TransactionResponse($this, $response);
    }
}
