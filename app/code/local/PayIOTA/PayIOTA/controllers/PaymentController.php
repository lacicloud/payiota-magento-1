<?php
class PayIOTA_PayIOTA_PaymentController extends Mage_Core_Controller_Front_Action {
	// The redirect action is triggered when someone places an order
	public function redirectAction() {
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('Mage_Core_Block_Template','payiota',array('template' => 'payiota/redirect.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	}
	
	private function _errorAndDie($error_msg) {		
		die('IPN Error: '.$error_msg);
	}
	
	//verify verification string as shown on PayIOTA.me dashboard
	function _is_ipn_valid($ipn, $order = null) {
	

			if (!isset($ipn["verification"])) {
				return FALSE;
			}

			$verification = $ipn["verification"];
			
			if ($verification != trim(Mage::getStoreConfig('payment/payiota/verification'))) {
				return FALSE;
			}
			
			return TRUE;
			
			
	}
	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	public function responseAction() {
		if ($this->getRequest()->isPost()) {
			if ($this->_is_ipn_valid($_POST)) {		
				// Payment was successful, so update the order's state, send order email and move to the success page
				$order_id = intval($_POST['custom']);
				$paid_iota = $_POST["paid_iota"];
				$price_iota = $_POST["price_iota"];
				$transaction_id = $_POST["address"];
				$order = Mage::getModel('sales/order');
				if ($order->loadByIncrementId($order_id)) {
					if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW and $paid_iota >= $price_iota) {
										//order complete or queued for nightly payout
										$str = "Successfully completed order ".$order_id." with IOTA payment of ".$paid_iota." for price IOTA of ".$price_iota." with transaction ID ".$transaction_id;
										  $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $str)->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING)->save();
										$order->sendNewOrderEmail();
										$order->setEmailSent(true);						
					}
				}

			} else {
				$this->_errorAndDie('Verification mismatch.');
			}
		} else {
			$this->_errorAndDie('Request is not POST.');
		}
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
		if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
			$order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
			if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Order canceled')->save();
			}
		}
	}
}