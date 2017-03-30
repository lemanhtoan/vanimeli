<?php
class NextBits_HidePrice_Helper_Redirects extends NextBits_HidePrice_Helper_Core
{
	const REDIRECT_TYPE_LOGIN = 'hideprice/requirelogin/requireloginredirect';
    public function getRedirect($sConfigPath)
    {
        $sRedirectPath = '/';
        $sConfigVar    = Mage::getStoreConfig($sConfigPath);
        if (isset($sConfigVar)) {
            $sRedirectPath = $sConfigVar;
        }
        return Mage::getUrl($sRedirectPath);
    }

    protected function isRedirectRequired($oControllerAction)
    {
        $bIsCmsController        = $this->isCmsController($oControllerAction);
        $bIsFrontController      = $this->isFrontController($oControllerAction);
        $bIsApiController        = $this->isApiController($oControllerAction);
        $bIsCustomerController   = $this->isCustomerController($oControllerAction);
        $bIsXmlConnectController = $this->isXmlConnectController($oControllerAction);

        return !$bIsCmsController && $bIsFrontController && !$bIsCustomerController
            && !$bIsApiController && !$bIsXmlConnectController;
    }

    protected function isCustomerController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_Customer_AccountController;
    }

    protected function isApiController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_Api_Controller_Action;
    }

    protected function isFrontController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_Core_Controller_Front_Action;
    }

    protected function isCmsController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_Cms_IndexController
        || $oControllerAction instanceof Mage_Cms_PageController;
    }

    protected function isXmlConnectController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_XmlConnect_ConfigurationController;
    }

    public function performRedirect($oControllerAction)
    {
        if ($this->isRedirectRequired($oControllerAction)) {
            $oResponse = $oControllerAction->getResponse();
            $oResponse->setRedirect(
                $this->getRedirect(
                    self::REDIRECT_TYPE_LOGIN
                )
            );
            $oSession = Mage::getSingleton('core/session');
            $oSession->addUniqueMessages(
                Mage::getSingleton('core/message')->notice(
                    Mage::helper('hideprice')->getLoginMessage()
                )
            );
            session_write_close();
        }
    }
}