<?php
class Ves_Widgets_Helper_Data extends Mage_Core_Helper_Abstract{
	
	const PRODUCT_CHOOSER_BLOCK_ALIAS    = 'adminhtml/catalog_product_widget_chooser';
	const CATEGORY_CHOOSER_BLOCK_ALIAS   = 'adminhtml/catalog_category_widget_chooser';
	const CMS_PAGE_CHOOSER_BLOCK_ALIAS   = 'adminhtml/cms_page_widget_chooser';
	const CMS_BLOCK_CHOOSER_BLOCK_ALIAS  = 'adminhtml/cms_block_widget_chooser';

	const XML_PATH_DEFAULT_CHOOSER_CONFIG = 'jarlssen_chooser_widget/chooser_defaults';

	const REQUIRED_HANDLE = 'editor';

	protected $_hasRequiredHandle;

	protected $_requiredConfigValues = array('input_name');

    /**
     * Wrapper function, that creates product chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createProductChooser($dataModel, $fieldset, $config)
    {
    	$blockAlias = self::PRODUCT_CHOOSER_BLOCK_ALIAS;
    	$this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
    	return $this;
    }

    /**
     * Wrapper function, that creates category chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createCategoryChooser($dataModel, $fieldset, $config)
    {
    	$blockAlias = self::CATEGORY_CHOOSER_BLOCK_ALIAS;
    	$this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
    	return $this;
    }

    /**
     * Wrapper function, that creates cms page chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createCmsPageChooser($dataModel, $fieldset, $config)
    {
    	$blockAlias = self::CMS_PAGE_CHOOSER_BLOCK_ALIAS;
    	$this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
    	return $this;
    }

    /**
     * Wrapper function, that creates cms block chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createCmsBlockChooser($dataModel, $fieldset, $config)
    {
    	$blockAlias = self::CMS_BLOCK_CHOOSER_BLOCK_ALIAS;
    	$this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
    	return $this;
    }

    /**
     * Wrapper function, that creates custom chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     * @param string $blockAlias
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createChooser($dataModel, $fieldset, $config, $blockAlias)
    {
    	$this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
    	return $this;
    }

    /**
     * This function is actually some kind of workaround how to create
     * a chooser and to reuse the product chooser widget.
     *
     * Most of the code was created after some reverse engineering of these 2 classes:
     *  - Mage_Widget_Block_Adminhtml_Widget_Options
     *  - Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser
     *
     * So there are interesting ideas of the Magento Core Team in these 2 classes:
     *  - Mage_Widget_Block_Adminhtml_Widget_Options
     *  -- Here they extend Mage_Adminhtml_Block_Widget_Form and do some tricks in:
     *  --- _prepareForm
     *  --- addFields and _addField
     *
     *  - Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser
     *  -- Here they attach the chooser html in the property after_element_html
     *  -- also they add some js methods, that control the behaviour of the chooser button
     *     and the the behaviour of the products grid that appear after the the button is pressed.
     *
     * The ideas in the both classes are interesting and this is a good example how we
     * can reuse core components.
     *
     * !!! The best solution would be to create our class that extends
     * Mage_Adminhtml_Block_Widget_Form and to do similar tricks that they do in Mage_Widget_Block_Adminhtml_Widget_Options
     * So we can reuse this class for the forms, that we need different kind of choosers.
     * Right now we can't reuse their Mage_Widget_Block_Adminhtml_Widget_Options, because there
     * are too many dependencies of the widget config and this class can't be reused easy out of the widget context.
     *
     * Also it was needed to include some extra JS files by layout update: <update handle="editor"/>
     * In favour to fire the choose grid after the choose button is pressed.
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     * @param string $blockAlias
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    protected function _prepareChooser($dataModel, $fieldset, $config, $blockAlias)
    {
    	$this->_checkRequiredHandle()
    	->_checkRequiredConfigs($config)
    	->_populateMissingConfigValues($config, $blockAlias);

    	$chooserConfigData = $this->_prepareChooserConfig($config, $blockAlias);
    	$chooserBlock = Mage::app()->getLayout()->createBlock($blockAlias, '', $chooserConfigData);

    	$element = $this->_createFormElement($dataModel, $fieldset, $config);

    	$chooserBlock
    	->setConfig($chooserConfigData)
    	->setFieldsetId($fieldset->getId())
    	->prepareElementHtml($element);

    	$this->_fixChooserAjaxUrl($element);

    	return $this;
    }

    /**
     * Checks if required handle "editor" is added in the layout update
     * If the handle is not in the layout, then we throw exception and
     * warn the developer, that the handle is required for the chooser functionality
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     * @throws Exception
     */
    protected function _checkRequiredHandle()
    {
    	if(null === $this->_hasRequiredHandle) {
    		$handles = Mage::app()->getLayout()->getUpdate()->getHandles();

    		if(!in_array(self::REQUIRED_HANDLE, $handles)) {
    			throw new Exception("Required handle \"" . self::REQUIRED_HANDLE . "\" is missing. You have to add the handle in the layout in favor to have working chooser.");
    		}
    		$this->_hasRequiredHandle = true;
    	}

    	return $this;
    }

    /**
     * Checks if all required config values are in the config array
     * Basically there values are critical for the normal work of the extension
     * If we don't have them, then for e.g. we can't pass the data, that we need to save
     * after form submit.
     *
     * We throw exception if at least on required config values is missing
     *
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     * @throws Exception
     */
    protected function _checkRequiredConfigs($config)
    {
    	foreach($this->_requiredConfigValues as $value) {
    		if(!isset($config[$value])) {
    			throw new Exception("Required input config value \"" . $value . "\" is missing.");
    		}
    	}

    	return $this;
    }

    /**
     * Inspects the config array and populate missing not mandatory values
     * with the predefined default values
     *
     * @param array $config
     * @param string $blockAlias
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    protected function _populateMissingConfigValues(&$config, $blockAlias)
    {
    	$currentWidgetKey = str_replace('adminhtml/', '',$blockAlias);

    	$chooserDefaults = Mage::getStoreConfig(self::XML_PATH_DEFAULT_CHOOSER_CONFIG);

    	if(!isset($chooserDefaults[$currentWidgetKey])) {
    		$currentWidgetKey = 'default';
    	}

    	foreach($chooserDefaults[$currentWidgetKey] as $configKey => $value) {
    		if(!isset($config[$configKey])) {
    			$config[$configKey] = $value;
    		}
    	}

    	return $this;
    }

    /**
     * Creates label form element and sets empty value of
     * the hidden input, that is created, when we have form element
     * from type label
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function _createFormElement($dataModel, $fieldset, $config)
    {
    	$isRequired = (isset($config['required']) && true === $config['required']) ? true : false;

    	$inputConfig = array(
    		'name'  => $config['input_name'],
    		'label' => $config['input_label'],
    		'required' => $isRequired
    		);

    	if (!isset($config['input_id'])) {
    		$config['input_id'] = $config['input_name'];
    	}

    	$element = $fieldset->addField($config['input_id'], 'label', $inputConfig);
    	$element->setValue($dataModel->getData($element->getId()));
    	$dataModel->setData($element->getId(),'');

    	return $element;
    }

    /**
     * Prepare config in format, that is needed for the chooser "factory"
     *
     * @param array $config
     * @param string $blockAlias
     *
     * @return array
     */
    protected function _prepareChooserConfig($config, $blockAlias)
    {
    	return array(
    		'button' =>
    		array(
    			'open' => $config['button_text'],
    			'type' => $blockAlias
    			)
    		);
    }

    /**
     * Replaces part of the chooser ajax fetch url,
     * because we hit 404 page when we have routers defined in the following way:
     *
     * 	<admin>
     *       <routers>
     *           <brands>
     *               <use>admin</use>
     *               <args>
     *                   <module>MyCompany_MyModule</module>
     *                   <frontName>myfrontname</frontName>
     *               </args>
     *           </brands>
     *       </routers>
     *   </admin>
     *
     * Basically we just replace "myfrontname" with the admin front name
     *
     * @param Varien_Data_Form_Element_Abstract $element
     */
    protected function _fixChooserAjaxUrl($element)
    {
    	$adminPath = (string)Mage::getConfig()
    	->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_ADMINHTML_ROUTER_FRONTNAME);

    	$currentRouterName = Mage::app()->getRequest()->getRouteName();

    	if($adminPath != $currentRouterName) {
    		$afterElementHtml = $element->getAfterElementHtml();
    		$afterElementHtml = str_replace('/' . $currentRouterName . '/','/' . $adminPath . '/', $afterElementHtml);
    		$element->setAfterElementHtml($afterElementHtml);
    	}
    }

    public function renderMediaChooser(Varien_Data_Form_Element_Abstract $element) {
        if (Mage::getSingleton('admin/session')->isAllowed('cms/media_gallery')) {

            $layout = $element->getForm()->getParent()->getLayout();
            $id = $element->getHtmlId();

            if ($url = $element->getValue()) {
                $linkStyle = "display:inline;";

                if(!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                    $url = Mage::getBaseUrl('media') . $url;
                }
            }else{
                $linkStyle = "display:none;";
                $url = "#";
            }

            $hiddenField = '<input type="hidden" name="hidden_file" id="hidden_file_'.$id.'" class="hidden-file-path" value=""/>';
            $imagePreview = '<a id="' . $id . '_link" href="' . $url . '" style="text-decoration: none; ' . $linkStyle . '"'
                . ' onclick="imagePreview(\'' . $id . '_image\'); return false;">'
                . ' <img src="' . $url . '" id="' . $id . '_image" title="' . $element->getValue() . '"'
                . ' alt="' . $element->getValue() . '" height="30" class="small-image-preview v-middle"/>'
                . ' </a>';

            $selectButtonId = 'add-image-' . mt_rand();
            $chooserUrl = Mage::getUrl('adminhtml/cms_wysiwyg_images_chooser/index', array('target_element_id' => $id));
            $label = ($element->getValue()) ? $this->__('Change Image') : $this->__('Select Image');


            // Select/Change Image Button
            $chooseButton = $layout->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('add-image')
                ->setId($selectButtonId)
                ->setLabel($label)
                ->setOnclick('openEfinder(this, \'hidden_file_'.$id.'\', \'#'.$id.'\', changeElFieldImage)')
                ->setDisabled($element->getReadonly())
                ->setStyle('display:inline;margin-top:7px');

            // Remove Image Button
            $onclickJs = '
                document.getElementById(\''. $id .'\').value=\'\';
                document.getElementById(\'hidden_file_'. $id .'\').value=\'\';
                if(document.getElementById(\''. $id .'_image\')){
                    document.getElementById(\''. $id .'_image\').parentNode.style.display = \'none\';
                }
                document.getElementById(\''. $selectButtonId .'\').innerHTML=\'<span><span><span>' . addslashes($this->__('Select Image')) . '</span></span></span>\';
            ';

            $removeButton = $layout->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('delete')
                ->setLabel($this->__('Remove Image'))
                ->setOnclick($onclickJs)
                ->setDisabled($element->getReadonly())
                ->setStyle('margin-top:7px');


            $wrapperStart = '<div id="buttons_' . $id . '" class="buttons-set" style=" width: 325px;">';
            $wrapperEnd = '</div>';

            // Add our custom HTML after the form element
            $element->setAfterElementHtml($wrapperStart . $hiddenField. $imagePreview . $chooseButton->toHtml() . $removeButton->toHtml() . $wrapperEnd);
        }

        return $element;
    }
}