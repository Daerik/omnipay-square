<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dylan
 * Date: 16/04/2019
 * Time: 3:51 PM
 */

namespace Omnipay\Square\Message;


use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;
use Square;

class UpdateCustomerRequest extends AbstractRequest
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

    public function getFirstName()
    {
        return $this->getParameter('firstName');
    }

    public function setFirstName($value)
    {
        return $this->setParameter('firstName', $value);
    }

    public function getLastName()
    {
        return $this->getParameter('lastName');
    }

    public function setLastName($value)
    {
        return $this->setParameter('lastName', $value);
    }

    public function getCompanyName()
    {
        return $this->getParameter('companyName');
    }

    public function setCompanyName($value)
    {
        return $this->setParameter('companyName', $value);
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function setAddress(Square\Models\Address $value)
    {
        return $this->setParameter('address', $value);
    }

    public function getAddress()
    {
        return $this->getParameter('address');
    }

    public function setNickName($value)
    {
        return $this->setParameter('nickName', $value);
    }

    public function getNickName()
    {
        return $this->getParameter('nickName');
    }

    public function getPhoneNumber()
    {
        return $this->getParameter('phoneNumber');
    }

    public function setPhoneNumber($value)
    {
        return $this->setParameter('phoneNumber', $value);
    }



    public function getNote(){
        return $this->getParameter('note');
    }

    public function setNote($value)
    {
        return $this->setParameter('note', $value);
    }

    public function getReferenceId(){
        return $this->getParameter('referenceId');
    }

    public function setReferenceId($value)
    {
        return $this->setParameter('referenceId', $value);
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

        $data['given_name'] = $this->getFirstName();
        $data['family_name'] = $this->getLastName();
        $data['company_name'] = $this->getCompanyName();
        $data['email_address'] = $this->getEmail();

        $data['address'] = $this->getAddress();
        $data['nickname'] = $this->getEmail();
        $data['phone_number'] = $this->getPhoneNumber();
        $data['reference_id'] = $this->getReferenceId();
        $data['note'] = $this->getNote();

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
	        $api_response = $api_instance->updateCustomer($this->getCustomerReference(), $data);
	        $result = $api_response->getResult();
	        
	        if ($error = $api_response->getErrors()) {
                $response = [
                    'status' => 'error',
                    'code' => $error['code'],
                    'detail' => $error['detail']
                ];
            } else {
                $response = [
                    'status' => 'success',
                    'customer' => $result->getCustomer()
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
        return $this->response = new CustomerResponse($this, $response);
    }
}