# W1 API helper

The workflow constists from 2 steps:

1. Redirect user to W1 payment page
2. Check the payment

Here is code samples for each step:

1-st step:
```php
$api = new W1\W1Api('merchantId', 'secretKey');
echo $api->getFormHTMLDocument($api->createPaymentForm()
	->setPaymentId(uniqid())
	->setCultureId('ru' == $this->getLastLocale() ? 
  	  W1\Form\PaymentForm::LANG_RU : 
  	  W1\Form\PaymentForm::LANG_EN
	 )
	->setAmount(2.99)
	->setCurrencyId(W1\Form\PaymentForm::CURRENCY_USD)
	->setDescription('Оплата за что-то')
	->setSuccessUrl($this->get_page_url('buy_method_w1_callback')) // to redirect user
	->setFailUrl($this->get_page_url('')) // to redirect user
```

2-nd step:
```php
$api = new W1\W1Api('merchantId', 'secretKey');
if (!empty($_POST))
{
	if (!$api->createPaymentForm()->isPaymentAccepted($_POST))
	{
		$this->logger()->log_exception('Error:W1', 'Wrong confirmation: ' . json_encode($_POST), __FILE__, __LINE__);
		echo $api->getRetryResponse('Wrong confirmation');
	}
	else
	{
		$this->buy_dlc_finalize();
		echo $api->getSuccessResponse();
	}
}
else $this->redirect_to_relative('buy_method_w1');
```

And that's all! You're wonderful! :)
