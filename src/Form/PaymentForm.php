<?php

namespace W1\Form;

class PaymentForm extends AbstractForm {

	const CURRENCY_RUB = 643;
	const CURRENCY_USD = 840;
	const CURRENCY_EUR = 978;
	const CURRENCY_KZT = 398;
	const CURRENCY_BYR = 974;
	const LANG_RU      = 'ru-RU';
	const LANG_EN      = 'en-US';
	protected $WMI_MERCHANT_ID;
	protected $secretKey;
	protected $WMI_PAYMENT_AMOUNT;
	protected $WMI_CURRENCY_ID;
	protected $WMI_PAYMENT_NO;
	protected $WMI_DESCRIPTION;
	protected $WMI_SUCCESS_URL;
	protected $WMI_FAIL_URL;
	protected $WMI_EXPIRED_DATE;
	protected $WMI_PTENABLED = [];
	protected $WMI_PTDISABLED = [];
	protected $WMI_RECIPIENT_LOGIN;
	protected $WMI_CUSTOMER_FIRSTNAME;
	protected $WMI_CUSTOMER_EMAIL;
	protected $WMI_CULTURE_ID = self::LANG_EN;

	public function __construct($merchantId, $key)
	{
		$this->setMerchantId($merchantId)->setSecretKey($key);
	}

	// --- Control

	public function toArray()
	{
		$fields = array_filter(parent::toArray(), function($value) {
			return !!$value;
		});
		if ($this->WMI_DESCRIPTION) $fields[ 'WMI_DESCRIPTION' ] = "BASE64:" . base64_encode($this->WMI_DESCRIPTION);

		// From http://www.walletone.com/ru/merchant/documentation/

		foreach($fields as $name => $val)
		{
			if (is_array($val))
			{
				usort($val, "strcasecmp");
				$fields[$name] = $val;
			}
		}

		uksort($fields, "strcasecmp");

		$fields["WMI_SIGNATURE"] = $this->getSignature($fields);

		return $fields;
	}

	public function getSignature(array $fields = NULL)
	{
		if (!$fields) $fields = $this->toArray();

		uksort($fields, "strcasecmp");
		$fieldValues = "";

		foreach($fields as $value)
		{
			if (is_array($value))
				foreach($value as $v)
				{
					//Конвертация из текущей кодировки (UTF-8)
					//необходима только если кодировка магазина отлична от Windows-1251
					$v = iconv("utf-8", "windows-1251", $v);
					$fieldValues .= $v;
				}
			else
			{
				//Конвертация из текущей кодировки (UTF-8)
				//необходима только если кодировка магазина отлична от Windows-1251
				$value = iconv("utf-8", "windows-1251", $value);
				$fieldValues .= $value;
			}
		}

		return base64_encode(pack("H*", md5($fieldValues . $this->secretKey)));
	}

	/**
	 * Or from _POST :)
	 *
	 * @param array $input
	 * @return self
	 */
	public function fromArray(array $input)
	{
		foreach ($input as $key => $value)
		{
			if (0 === strpos($key, 'WMI') AND property_exists($this, $key))
			{
				$this->$key = $this;
			}
		}

		return $this;
	}

	public function isSignatureValid($signature)
	{
		return $signature == $this->getSignature();
	}

	public function isSignatureFromArrayValid(array $input)
	{
		return !empty($input['WMI_SIGNATURE']) AND $this->fromArray($input)->isSignatureFromArrayValid($input);
	}

	public function isPaymentAccepted(array $input)
	{
		return
			!empty($input["WMI_ORDER_STATE"]) AND
			"ACCEPTED" == $input["WMI_ORDER_STATE"] AND
			$this->isSignatureFromArrayValid($input);
	}

	// --- Accessors

	/**
	 * Set WMI_CULTURE_ID
	 *
	 * @param mixed $WMI_CULTURE_ID
	 * @return $this
	 */
	public function setCultureId($WMI_CULTURE_ID)
	{
		$this->WMI_CULTURE_ID = $WMI_CULTURE_ID;

		return $this;
	}

	/**
	 * Set WMI_CURRENCY_ID
	 *
	 * @param mixed $WMI_CURRENCY_ID
	 * @return $this
	 */
	public function setCurrencyId($WMI_CURRENCY_ID)
	{
		$this->WMI_CURRENCY_ID = $WMI_CURRENCY_ID;

		return $this;
	}

	/**
	 * Set WMI_CUSTOMER_EMAIL
	 *
	 * @param mixed $WMI_CUSTOMER_EMAIL
	 * @return $this
	 */
	public function setCustomerEmail($WMI_CUSTOMER_EMAIL)
	{
		$this->WMI_CUSTOMER_EMAIL = $WMI_CUSTOMER_EMAIL;

		return $this;
	}

	/**
	 * Set WMI_CUSTOMER_FIRSTNAME
	 *
	 * @param mixed $WMI_CUSTOMER_FIRSTNAME
	 * @return $this
	 */
	public function setCustomerFirstName($WMI_CUSTOMER_FIRSTNAME)
	{
		$this->WMI_CUSTOMER_FIRSTNAME = $WMI_CUSTOMER_FIRSTNAME;

		return $this;
	}

	/**
	 * Set WMI_DESCRIPTION
	 *
	 * @param mixed $WMI_DESCRIPTION
	 * @return $this
	 */
	public function setDescription($WMI_DESCRIPTION)
	{
		$this->WMI_DESCRIPTION = $WMI_DESCRIPTION;

		return $this;
	}

	/**
	 * Set WMI_EXPIRED_DATE
	 *
	 * @param mixed $WMI_EXPIRED_DATE
	 * @return $this
	 */
	public function setExpireDate(\DateTime $date)
	{
		$this->WMI_EXPIRED_DATE = $date->format('c');

		return $this;
	}

	/**
	 * Set WMI_FAIL_URL
	 *
	 * @param mixed $WMI_FAIL_URL
	 * @return $this
	 */
	public function setFailUrl($WMI_FAIL_URL)
	{
		$this->WMI_FAIL_URL = $WMI_FAIL_URL;

		return $this;
	}

	/**
	 * Set WMI_PAYMENT_AMOUNT
	 *
	 * @param mixed $WMI_PAYMENT_AMOUNT
	 * @return $this
	 */
	public function setAmount($WMI_PAYMENT_AMOUNT)
	{
		$this->WMI_PAYMENT_AMOUNT = $WMI_PAYMENT_AMOUNT;

		return $this;
	}

	/**
	 * Set WMI_PAYMENT_NO
	 *
	 * @param mixed $WMI_PAYMENT_NO
	 * @return $this
	 */
	public function setPaymentId($WMI_PAYMENT_NO)
	{
		$this->WMI_PAYMENT_NO = $WMI_PAYMENT_NO;

		return $this;
	}

	/**
	 * Set WMI_PTDISABLED
	 *
	 * @param array $WMI_PTDISABLED
	 * @return $this
	 */
	public function addDisabledPaymentMethod($WMI_PTDISABLED)
	{
		$this->WMI_PTDISABLED[ ] = $WMI_PTDISABLED;

		return $this;
	}

	/**
	 * Set WMI_PTENABLED
	 *
	 * @param array $WMI_PTENABLED
	 * @return $this
	 */
	public function addEnabledPaymentMethod($WMI_PTENABLED)
	{
		$this->WMI_PTENABLED[ ] = $WMI_PTENABLED;

		return $this;
	}

	/**
	 * Set WMI_RECIPIENT_LOGIN
	 *
	 * @param mixed $WMI_RECIPIENT_LOGIN
	 * @return $this
	 */
	public function setCustomerLogin($WMI_RECIPIENT_LOGIN)
	{
		$this->WMI_RECIPIENT_LOGIN = $WMI_RECIPIENT_LOGIN;

		return $this;
	}

	/**
	 * Set WMI_SUCCESS_URL
	 *
	 * @param mixed $WMI_SUCCESS_URL
	 * @return $this
	 */
	public function setSuccessUrl($WMI_SUCCESS_URL)
	{
		$this->WMI_SUCCESS_URL = $WMI_SUCCESS_URL;

		return $this;
	}

	/**
	 * Set WMI_MERCHANT_ID
	 *
	 * @param mixed $WMI_MERCHANT_ID
	 * @return $this
	 */
	protected function setMerchantId($WMI_MERCHANT_ID)
	{
		$this->WMI_MERCHANT_ID = $WMI_MERCHANT_ID;

		return $this;
	}

	/**
	 * Set secretKey
	 *
	 * @param mixed $secretKey
	 * @return $this
	 */
	protected function setSecretKey($secretKey)
	{
		$this->secretKey = $secretKey;

		return $this;
	}
}