<?php

namespace Omnipay\Square\Message;

use Omnipay\Common\Message\AbstractRequest;
use Square;

/**
 * Square List Refunds Request
 */
class ListRefundsRequest extends AbstractRequest
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

    public function getBeginTime()
    {
        return $this->getParameter('begin_time');
    }

    public function setBeginTime($value)
    {
        return $this->setParameter('begin_time', $value);
    }

    public function getEndTime()
    {
        return $this->getParameter('end_time');
    }

    public function setEndTime($value)
    {
        return $this->setParameter('end_time', $value);
    }

    public function getSortOrder()
    {
        return $this->getParameter('sort_order');
    }

    public function setSortOrder($value)
    {
        return $this->setParameter('sort_order', $value);
    }

    public function getCursor()
    {
        return $this->getParameter('cursor');
    }

    public function setCursor($value)
    {
        return $this->setParameter('cursor', $value);
    }

    /*
    public function getCheckoutId()
    {
    return $this->getParameter('checkOutId');
    }

    public function setCheckoutId($value)
    {
    return $this->setParameter('checkOutId', $value);
    }
    */

    public function getData()
    {
        return [];
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
	    
	    $api_instance = $api_client->getRefundsApi();

        try {
	        $api_response = $api_instance->listPaymentRefunds(
		        $this->getBeginTime(),
		        $this->getEndTime(),
		        $this->getSortOrder(),
		        $this->getCursor(),
		        $this->getLocationId()
	        );
	        $result = $api_response->getResult();

            if ($error = $api_response->getErrors()) {
                $response = [
                    'status' => 'error',
                    'code' => $error['code'],
                    'detail' => $error['detail']
                ];
            } else {
                $refunds = [];
                $refundItems = $result->getRefunds();
                if ($refundItems === null) {
                    $refundItems = [];
                }
                foreach ($refundItems as $refund) {
                    $item = new \stdClass();
                    $item->id = $refund->getId();
                    $item->locationId = $refund->getLocationId();
                    $item->transactionId = $refund->getPaymentId();
                    $item->createdAt = $refund->getCreatedAt();
                    $item->reason = $refund->getReason();
                    $item->status = $refund->getStatus();
                    $item->amount = $refund->getAmountMoney()->getAmount();
                    $item->processingFee = $refund->getProcessingFeeMoney();
                    $item->currency = $refund->getAmountMoney()->getCurrency();
                    $refunds[] = $item;
                }
                $response = [
                    'status' => 'success',
                    'refunds' => $refunds,
                    'cursor' => $result->getCursor()
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'detail' => 'Exception when calling TransactionsApi->listRefunds: ', $e->getMessage()
            ];
        }

        return $this->createResponse($response);
    }

    public function createResponse($response)
    {
        return $this->response = new ListRefundsResponse($this, $response);
    }
}
