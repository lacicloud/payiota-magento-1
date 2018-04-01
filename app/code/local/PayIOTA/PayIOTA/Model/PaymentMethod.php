<?php
class PayIOTA_PayIOTA_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract {
	protected $_code = 'payiota';
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('payiota/payment/redirect', array('_secure' => true));
	}
}
?>