<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsReferFriends Refer Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_Rewardpointsreferfriends_ReferController extends Mage_Core_Controller_Front_Action {

    function indexAction() {
        $helper = Mage::helper('rewardpointsreferfriends');
        if (!$helper->isEnable(Mage::app()->getStore()->getId()) || (!$helper->getSpecialOffer(Mage::app()->getStore()->getId())->getSize() && !Mage::getBlockSingleton('rewardpointsreferfriends/defaultpage_refer')->isEnableDefault()))
            return $this->_redirect("");
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Action show template send mail
     */
    function sendmailAction() {
        if (!Mage::helper('rewardpointsreferfriends')->isEnable(Mage::app()->getStore()->getId()) || !Mage::helper('customer')->isLoggedIn())
            return $this->_redirect("");
        if (!Mage::helper('rewardpointsreferfriends')->getReferConfig('use_email'))
            $this->_redirect('');
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Send action
     * @return type
     */
    public function sendAction() {
        if (!Mage::helper('rewardpointsreferfriends')->isEnable(Mage::app()->getStore()->getId()) || !Mage::helper('customer')->isLoggedIn())
            return $this->_redirect("");
        $productId = $this->getRequest()->getParam('id');
        if (!$this->_validateFormKey())
            return $this->_redirect('*/*/sendmail', array('_current' => true));
        try {
            if (!$this->_sendEmail()){
                if($productId) $this->_redirect('rewardpointsreferfriends/refer/sendmail', array('id' => $productId));
                else $this->_redirect('rewardpointsreferfriends/refer/sendmail');
            }else {
                if($productId){
                    Mage::getSingleton('core/session')->addSuccess($this->__('The product had been sent to your friends.'));
                    $this->_redirectSuccess(Mage::getUrl('catalog/product/view', array('id' => $productId)));
                }else{
                    Mage::getSingleton('core/session')->addSuccess($this->__('Email had been sent to your friends.'));
                    $this->_redirectSuccess(Mage::getUrl('*/*/sendmail', array('_current' => true)));
                }
            }
            return;
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirectError(Mage::getUrl('*/*/sendmail', array('_current' => true)));
        }
    }

    /**
     * Function send mail
     * @return \Magestore_Rewardpointsreferfriends_ReferController
     */
    protected function _sendEmail() {
        $data = $this->getRequest()->getParams();

        $senderInfo = $data['sender'];
        $recipients = $data['recipients'];
        $recipientEmail = $recipients['email'];
        $recipientName = $recipients['name'];

        $maxemailsentperday = Mage::helper('rewardpointsreferfriends')->getReferConfig('max_email');
        if (is_numeric($maxemailsentperday) && $maxemailsentperday > 0) {
            $customerOffer = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->getCollection()
                    ->addFieldToFilter('customer_id', Mage::helper('customer')->getCustomer()->getId())
                    ->getFirstItem();
            if (!$customerOffer->getId()) {
                echo 'fail';
                return;
            }
            $dateSent = strtotime($customerOffer->getData('date_sent'));
            $emailSent = $customerOffer->getData('email_sent');
            $dateNow = strtotime(date('y-m-d'));
            if (($emailSent + count($recipientEmail)) > $maxemailsentperday) {
                Mage::getSingleton('core/session')->addError('You can send a maximum of ' . $maxemailsentperday . ' emails per day.');
                if (($maxemailsentperday - $emailSent) <= 0)
                    Mage::getSingleton('core/session')->addError('You can not send more email today.');
                else
                    Mage::getSingleton('core/session')->addError('You have only ' . ($maxemailsentperday - $emailSent) . ' times left to send emails today.');
                return false;
            }
            if ($dateSent != $dateNow) {
                $emailSent = 0;
                $customerOffer->setData('date_sent', date('Y-m-d'));
            }
        }
        //send email
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
//        $message = $senderInfo['message'];//nl2br(htmlspecialchars($senderInfo['message']));
        $message = $this->makeClickableLinks($senderInfo['message']);
        $message = nl2br($message);

        $sender = array(
            'name' => Mage::helper('rewardpointsreferfriends')->htmlEscape($senderInfo['name']),
            'email' => Mage::helper('rewardpointsreferfriends')->htmlEscape($senderInfo['email']),
        );
        $mailTemplate->setDesignConfig(array(
            'area' => 'frontend',
            'store' => Mage::app()->getStore()->getId(),
        ));
        $mailTemplate->setTemplateSubject(Mage::helper('rewardpointsreferfriends')->getReferConfig('sharing_subject'));
        $templateId = 'rewardpointsreferfriends_email_template';
        foreach ($recipientEmail as $k => $email) {
            $name = $recipientName[$k];
            $mailTemplate->sendTransactional(
                    $templateId, $sender, $email, $name, array(
                'store' => Mage::app()->getStore(),
                'name' => $name,
                'email' => $email,
                'message' => $message,
                'sender_name' => $sender['name'],
                'sender_email' => $sender['email'],
                    )
            );
        }

        $translate->setTranslateInline(true);
        if (is_numeric($maxemailsentperday) && $maxemailsentperday > 0) {
            $customerOffer->setEmailSent($emailSent + count($recipientEmail));
            try {
                $customerOffer->save();
            } catch (Exception $e) {
                
            }
        }
        return $this;
    }

    public static function makeClickableLinks($s) {
        return preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $s);
    }

    /**
     * Get Ajax mail action
     */
    function ajaxmailAction() {
        if (!Mage::helper('rewardpointsreferfriends')->isEnable(Mage::app()->getStore()->getId()) || !Mage::helper('customer')->isLoggedIn())
            return $this->_redirect("");

        $data = $this->getRequest()->getPost();

        //Captcha
        $captcha = $data['captcha'];
        $_captcha = Mage::getSingleton('core/session')->getData('send_mail_captcha_code');
        if ($_captcha != $captcha && $captcha) {
            echo 'wrong_captcha';
            return;
        }
        //
        $emaillist = explode(',', $data['emaillist']);
        $emailName = array();
        $numOfemail = 0;
        foreach ($emaillist as $key => $email) {
            $numOfemail +=1;
            $name = '';
            if (strpos($email, '<') !== false) {
                $name = substr($email, 0, strpos($email, '<'));
                $email = substr($email, strpos($email, '<') + 1);
            }
            $emaillist[$key] = trim(rtrim(trim($email), '>'));
            if (!filter_var($emaillist[$key], FILTER_VALIDATE_EMAIL)) {
                echo 'email_wrong';
                return;
            }
            if ($name != '') {
                $emailName[$key] = trim($name);
            }
            else
                $emailName[$key] = $this->nameEmail($emaillist[$key]);
        }
        //check max sent email
        $maxemailsentperday = Mage::helper('rewardpointsreferfriends')->getReferConfig('max_email');
        if (is_numeric($maxemailsentperday) && $maxemailsentperday > 0) {
            $customerOffer = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->getCollection()
                    ->addFieldToFilter('customer_id', Mage::helper('customer')->getCustomer()->getId())
                    ->getFirstItem();
            if (!$customerOffer->getId()) {
                echo 'fail';
                return;
            }
            $dateSent = strtotime($customerOffer->getData('date_sent'));
            $emailSent = $customerOffer->getData('email_sent');
            $dateNow = strtotime(date('y-m-d'));
            if (($emailSent + $numOfemail) > $maxemailsentperday) {
                echo ($maxemailsentperday - $emailSent);
                return;
            }
            if ($dateSent != $dateNow) {
                $emailSent = 0;
                $customerOffer->setData('date_sent', date('Y-m-d'));
            }
        }
        $emailsubject = $data['subject'];
        $emailcontent = $data['content'];
        try {
            $this->sendAjaxMail($emailName, $emaillist, $emailsubject, $emailcontent);
            if (is_numeric($maxemailsentperday) && $maxemailsentperday > 0) {
                $customerOffer->setEmailSent($emailSent + $numOfemail);
                try {
                    $customerOffer->save();
                } catch (Exception $e) {
                    
                }
            }
            echo 'success';
            return;
        } catch (Exception $e) {
            echo 'fail';
            return;
        }
    }

    /**
     * Send mail
     * @param type $emaillist
     * @param type $emailsubject
     * @param type $emailcontent
     * @return \Magestore_Rewardpointsreferfriends_ReferController
     */
    function sendAjaxMail($emailName, $emaillist, $emailsubject, $emailcontent) {
        $customer = Mage::helper('customer')->getCustomer();
        //send email
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
//        $message = $emailcontent; //nl2br(htmlspecialchars($emailcontent));

        $message = $this->makeClickableLinks($emailcontent);
        $message = nl2br($message);
        $sender = array(
            'name' => Mage::helper('rewardpointsreferfriends')->htmlEscape($customer->getName()),
            'email' => Mage::helper('rewardpointsreferfriends')->htmlEscape($customer->getEmail()),
        );
        $mailTemplate->setDesignConfig(array(
            'area' => 'frontend',
            'store' => Mage::app()->getStore()->getId(),
        ));
        $mailTemplate->setTemplateSubject($emailsubject);
        $templateId = 'rewardpointsreferfriends_email_template';
        foreach ($emaillist as $k => $email) {
            $name = $emailName[$k];
            $mailTemplate->sendTransactional(
                    $templateId, $sender, $email, $name, array(
                'store' => Mage::app()->getStore(),
                'name' => $name,
                'email' => $email,
                'message' => $message,
                'sender_name' => $sender['name'],
                'sender_email' => $sender['email'],
                    )
            );
        }

        $translate->setTranslateInline(true);
        return $this;
    }

    function nameEmail($email) {
        $mail = explode('@', $email);
        return $mail['0'];
    }

    function refreshAction() {
        $formId = $this->getRequest()->getPost('formId', false);
        if ($formId) {
            $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
            $this->getLayout()->createBlock('rewardpointsreferfriends/captcha_zend')->setFormId($formId)->setIsAjax(true)->toHtml();
            $this->getResponse()->setBody(json_encode(array('imgSrc' => $captchaModel->getImgSrc())));
        }
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
    }

    public function imagecaptchaAction() {
        require_once(Mage::getBaseDir('lib') . DS . 'captcha' . DS . 'class.simplecaptcha.php');
        $config['BackgroundImage'] = Mage::getBaseDir('lib') . DS . 'captcha' . DS . "white.png";
        $config['BackgroundColor'] = "FF0000";
        $config['Height'] = 30;
        $config['Width'] = 100;
        $config['Font_Size'] = 23;
        $config['Font'] = Mage::getBaseDir('lib') . DS . 'captcha' . DS . "ARLRDBD.TTF";
        $config['TextMinimumAngle'] = 15;
        $config['TextMaximumAngle'] = 30;
        $config['TextColor'] = '2B519A';
        $config['TextLength'] = 4;
        $config['Transparency'] = 80;
        $captcha = new SimpleCaptcha($config);
        Mage::getSingleton('core/session')->setData('send_mail_captcha_code', $captcha->Code);
    }

    public function refreshcaptchaAction() {
        $result = Mage::getModel('core/url')->getUrl('*/*/imageCaptcha/') . now();
        echo $result;
    }

}