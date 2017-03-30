<?php

class Magestore_Affiliatepluscoupon_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
		if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled() || Mage::helper('affiliateplus/account')->accountNotLogin())
			return $this->_redirect('affiliateplus/index/index');
		
		$account = Mage::helper('affiliateplus/account')->getAccount();
		$accountId = $account->getId();
		$coupon = Mage::getModel('affiliatepluscoupon/coupon')->setCurrentAccountId($accountId);
		$helper = Mage::helper('affiliatepluscoupon');
		
		$coupon->loadByProgram();
		if (!$coupon->getId()){
			try {
				$coupon->setCouponCode($helper->generateNewCoupon())
					->setAccountName($account->getName())
					->setProgramName('Affiliate Program')
					->save();
			} catch (Exception $e){}
		}
		$account->setCouponCode($coupon->getCouponCode());
		Mage::register('account_model',$account);
		
		if (Mage::helper('affiliatepluscoupon')->isMultiProgram()){
			$programs = Mage::getResourceModel('affiliateplusprogram/account_collection')
    			->addFieldToFilter('account_id',$accountId);
			$pCouponCodes = array();
			foreach ($programs as $accProgram){
				$program = Mage::getModel('affiliateplusprogram/program')
					->setStoreId(Mage::app()->getStore()->getId())
					->load($accProgram->getProgramId());
				if (!$program->getUseCoupon() || !floatval($program->getDiscount())) continue;
				$coupon->setId(null)->loadByProgram($accProgram->getProgramId());
				if (!$coupon->getId()){
					try {
						$coupon->setCouponCode($helper->generateNewCoupon($program->getCouponPattern()))
							->setAccountName($account->getName())
							->setProgramName($program->getName())
							->save();
					} catch (Exception $e){}
				}
				if ($coupon->getCouponCode()) $pCouponCodes[$program->getId()] = $coupon->getCouponCode();
			}
			Mage::register('program_coupon_codes',$pCouponCodes);
		}
		
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Coupon Code'));
		$this->renderLayout();
	}

	/**
	 *	Added By Adam
	 *	Save new coupon to database
	 */
	public function editCouponPostAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
		if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled() || Mage::helper('affiliateplus/account')->accountNotLogin())
			return $this->_redirect('affiliateplus/index/index');
		$couponCode = $this->getRequest()->getParam('coupon_code');
		$program_id = $this->getRequest()->getParam('program_id', 0);
		$result = $this->_checkCouponCode($couponCode, $program_id);

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	/**
	 * @param string $couponCode
	 * @param int $program_id
	 * @return array
	 */
	protected function _checkCouponCode($couponCode='', $program_id=0){
		$account 	= Mage::helper('affiliateplus/account')->getAccount();
		$accountId 	= $account->getId();
		$coupon 	= Mage::getModel('affiliatepluscoupon/coupon')->setCurrentAccountId($accountId);
		$collection = Mage::getModel('affiliatepluscoupon/coupon')->getCollection()
			->addFieldToFilter('coupon_code', $couponCode)
			->getFirstItem()
		;
		$salesruleCoupon = Mage::getModel('salesrule/coupon')->getCollection()
			->addFieldToFilter('code', $couponCode)
			->getFirstItem()
		;

		$error 	= true;
		$html 	= '';
		$result = array();
		$coupon->setId(null)->loadByProgram($program_id);

//		if (!$coupon->getId()) {
//			$error = true;
//			$html = $this->__('The program has not available. Please check it again.');
//			$result['coupon_code'] = $coupon->getCouponCode();
//		}
		if($collection && $collection->getId() && (($collection->getAccountId() != $accountId) || ($program_id > 0 && $collection->getAccountId() == $accountId && $collection->getProgramId() != $program_id))) {
			$error = true;
			$html = $this->__('The coupon code %s has existed on database. Please  try with another code', $couponCode);
			$result['coupon_code'] = $coupon->getCouponCode();
		} else if($salesruleCoupon && $salesruleCoupon->getId()){
			$error = true;
			$html = $this->__('The coupon code %s has existed on coupon of Salesrule. Please  try with another code', $couponCode);
			$result['coupon_code'] = $coupon->getCouponCode();
		}else {
			try {
				$coupon->setCouponCode($couponCode)->save();
				$error = false;
				$html = $this->__('The coupon code %s has been updated', $couponCode);
				$result['coupon_code'] = $couponCode;

			} catch (Exception $e) {
				$error = true;
				Mage::getSingleton('core/session')->addWarning($e->getMessage());
				$result['coupon_code'] = $coupon->getCouponCode();
			}
		}
		$result['error'] = $error;
		$result['html'] = $html;
		return $result;
	}
}