<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dylan
 * Date: 17/04/2019
 * Time: 3:28 PM
 */

namespace Omnipay\Square\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;
use Square;

class FetchCardRequest extends AbstractRequest
{
    public function getAccessToken()
    {
        return $this->getParameter('accessToken');
    }

    public function setAccessToken($value)
    {
        return $this->setParameter('accessToken', $value);
    }

    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }

    public function getCustomerReference()
    {
        return $this->getParameter('customerReference');
    }

    public function getCard()
    {
        return $this->getParameter('card');
    }

    public function setCard($value)
    {
        return $this->setParameter('card', $value);
    }
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $data = [];

        $data['customer_id'] = $this->getCustomerReference();
        $data['card_id'] = $this->getCard();

        return $data;
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     * @return ResponseInterface
     */
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
	    
	    $api_instance = $api_client->getCustomersApi();

        try {
	        $api_response = $api_instance->retrieveCustomer($data['customer_id']);
	        $result = $api_response->getResult();

            if ($error = $api_response->getErrors()) {
                $response = [
                    'status' => 'error',
                    'code' => $error['code'],
                    'detail' => $error['detail']
                ];
            } else {
                $cardId = $this->getCard();
                $cards = array_filter($result->getCustomer()->getCards(), function ($cur) use ($cardId){
                    return $cur->getId() == $cardId;
                });

                if($cards === null || count($cards) == 0){
                    throw new \Exception('Card not found!');
                }
                $response = [
                    'status' => 'success',
                    'card' => $cards[0]
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'detail' => 'Exception when creating customer: ', $e->getMessage()
            ];
        }

        return $this->createResponse($response);
    }

    public function createResponse($response)
    {
        return $this->response = new CardResponse($this, $response);
    }
}