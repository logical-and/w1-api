<?php

namespace W1;

use W1\Form\PaymentForm;

class PaymentAPI {

	protected $merchantId;
	protected $secretKey;
	protected $paymentUrl = 'https://www.walletone.com/checkout/default.aspx';

	/**
	 * @return static
	 */
	public static function buildInstance()
	{
		return (new \ReflectionClass(get_called_class()))->newInstanceArgs(func_get_args());
	}

	public function __construct($merchantId, $secretKey)
	{
		$this->merchantId = $merchantId;
		$this->secretKey  = $secretKey;
	}

	public function createPaymentForm()
	{
		return new PaymentForm($this->merchantId, $this->secretKey, $this);
	}

	public function getMerchantId()
	{
		return $this->merchantId;
	}

	public function getSecretKey()
	{
		return $this->secretKey;
	}

	public function getPaymentUrl()
	{
		return $this->paymentUrl;
	}

	public function getFormHTML(PaymentForm $form, $id = 'w1form')
	{
		// http://www.walletone.com/ru/merchant/documentation/

		$html = '<form id="' . $id . '" action="' . $this->paymentUrl . '" method="POST">';
		foreach($form->toArray() as $key => $val)
		{
			if (is_array($val))
				foreach($val as $value)
				{
					$html .= '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';
                 }
			else
				$html .= '<input type="hidden" name="' . $key . '" value="' . $val . '"/>';
          }

		$html .= '</form>';

		return $html;
	}

	public function getFormHTMLDocument(PaymentForm $form, $textProcessing = 'Processing...', $timeout = 1)
	{
		return '<html>' .
			'<head><title>' . $textProcessing .'</title></head>' .
			'<body>' .
			'<h1><center>' . $textProcessing .'</center></h1>' .
			$this->getFormHTML($form, 'w1form') .
			'<script>setTimeout(function() {' .
				'document.getElementById("w1form").submit();' .
			'}, ' . $timeout . ' * 1000);</script>' .
			'</body>' .
			'</html>';
	}

	public function getSuccessResponse()
	{
		return 'WMI_RESULT=OK';
	}

	public function getRetryResponse($description)
	{
		return 'WMI_RESULT=RETRY&WMI_DESCRIPTION=' . $description;
	}
}
 