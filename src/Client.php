<?php
/**
 * This is the client REST Api sdk for Affitto Certificato services
 * All service operations are done through this class.
 * 
 * 
 * @package    AffittoCertificatoAPI
 * @license	   https://www.gnu.org/licenses/gpl-3.0.html  GPL 3.0
 * @version	   Tue Aug 6 15:46:02 2019 +0200
 * @author 	Paolo Rosi <paolo.rosi@affittocertificato.it>
 * @link	   https://github.com/bradiporitmico/affittocertificato_api
 */

namespace AffittoCertificato\Api;


class NullResponseException extends \Exception {};
class FailedCallException extends \Exception {};

class Client{

	private $baseUrl = 'https://api.affittocertificato-services.cloud/';
	private $token = null;
	private $response = null;

	private function apiCall (string $method, $post=null){
		$ch = curl_init();
		$url = "{$this->baseUrl}{$method}";

		curl_setopt($ch, CURLOPT_URL, $url);
		if ($this->token){
			curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: bearer {$this->token}"]);
		}

		/*
		// workaround for servers with self signed certificates
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, false);
		*/

		if ($post){
			curl_setopt($ch, CURLOPT_POST, true); 
			if (is_array($post)){
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
			}	else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$return = curl_exec($ch);
		curl_close($ch);

		if (!$return)
			throw new NullResponseException('Null response received');
		
		$json = json_decode($return);
		if (!$json)
			throw new FailedCallException("Invalid response '$return'");

		return $json;
	}

	public function getResponse() : \stdClass{
		return $this->response;
	}

	public function getToken () : string{
		return $this->token;
	}

	public function setToken (string $token) : Client{
		$this->token = $token;
		return $this;
	}

	public function getBaseUrl () :string{
		return $this->baseUrl;
	}

	public function setBaseUrl (string $value) : Client{
		$this->baseUrl = $value;
		return $this;
	}

	public function login (string $username, string $password) : bool{
		$this->response = $this->apiCall('login', ['username'=>$username, 'password'=>$password]);
		if ($res = (isset($this->response->success) && $this->response->success)){
			$this->setBaseUrl ($this->response->response->endpoint);
			$this->setToken ($this->response->response->token);
		}
		return $res;

	}

	public function userRatingByEmail (string $email) : bool{
		$this->response = $this->apiCall("userRatingByEmail/{$email}");
		return $this->response->success;
	}
	
	public function userRatingByCf (string $cf) : bool{
		$this->response = $this->apiCall("userRatingByCF/{$cf}");
		return $this->response->success;
	}
	
	public function ping () {
		$this->response = $this->apiCall("ping");
		return $this->response->success;
	}
	
	public function getPrivacyTexts () : bool{
		$this->response = $this->apiCall("getPrivacyTexts");
		return $this->response->success;
	}
	
	public function userCreate (string $givenName, string $familyName, string $email, string $italianFiscalCode) : bool{
		$this->response = $this->apiCall("userCreate",[
			'givenName' => $givenName,
			'familyName' => $familyName,
			'email' => $email,
			'italianFiscalCode' => $italianFiscalCode
		]);
		return $this->response->success;
	}
	
	public function userCreateWithPhone (string $givenName, string $familyName, string $email, string $italianFiscalCode, string $phone) : bool{
		$this->response = $this->apiCall("userCreateWithPhone",[
			'givenName' => $givenName,
			'familyName' => $familyName,
			'email' => $email,
			'italianFiscalCode' => $italianFiscalCode,
			'phone' => $phone
		]);
		return $this->response->success;
	}

	public function userCreateSimple (string $givenName, string $familyName, string $email, string $phone, string $type = 'tenant') : bool{
		$this->response = $this->apiCall("user/create/simple",[
			'givenName' => $givenName,
			'familyName' => $familyName,
			'email' => $email,
			'phone' => $phone,
			'type' => $type,
		]);
		return $this->response->success;
	}

	public function userCreateJson ($data) : bool{
		$this->response = $this->apiCall("user/create", json_encode($data));
		if (isset($this->response->success))
			return $this->response->success;
		else	
			return false;
	}
	

	public function usersList () : bool{
		$this->response = $this->apiCall("users/list");
		return $this->response->success ?? false;
	}
	

	
}