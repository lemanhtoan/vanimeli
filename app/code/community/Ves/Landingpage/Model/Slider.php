<?php
 /*------------------------------------------------------------------------
  # Ves Blog Module 
  # ------------------------------------------------------------------------
  # author:    Ves.Com
  # copyright: Copyright (C) 2012 http://www.ves.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.ves.com
  # Technical Support:  http://www.ves.com/
-------------------------------------------------------------------------*/
class Ves_Landingpage_Model_Slider extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {	
	    $this->_init('ves_landingpage/slider');
	
    }
    public function add($caption_1 ='',$class_1 ='',$effect_1 ='',$caption_2 ='', $class_2 ='',$effect_2 ='',$caption_3 ='', $class_3 ='',$effect_3 ='',$status ='')
    {

      $data = array(
                    'caption_1'          => $caption_1,
                    'class1'             => $class_1,
                    'effect_1'           => $effect_1,
                    'caption_2'          => $caption_2,
                    'class_2'            => $class_2,
                    'effect_2'           => $effect_2,
                    'caption_3'          => $caption_3,
                    'class_3'            => $class_3,
                    'effect_3'           => $effect_3,
                    'status'             => $status,
                    'store_id'           => 0,
                    );
          $model = $this->setData($data);
          try{
            $slider_id = $model->save()->getId();
            Mage::getSingleton('core/session')->addSuccess( 'Created slider successfully!' );  
            //echo 'Inserted abc',$slider_id;
            return $slider_id;
        }catch(exception $e){
            Mage::getSingleton( 'core/session') ->addError( $e->getMessage() );
        }
    }

    // save  
    public function update($slider_id ='',$caption_1 ='',$class_1 ='',$effect_1 ='',$caption_2 ='', $class_2 ='',$effect_2 ='',$caption_3 ='', $class_3 ='',$effect_3 ='',$status ='')
    {
      //echo $static_block_id;die;
      $data = array(
                    'caption_1'          => $caption_1,
                    'class1'             => $class_1,
                    'effect_1'           => $effect_1,
                    'caption_2'          => $caption_2,
                    'class_2'            => $class_2,
                    'effect_2'           => $effect_2,
                    'caption_3'          => $caption_3,
                    'class_3'            => $class_3,
                    'effect_3'           => $effect_3,
                    'status'             => $status,
                    'store_id'           => 0,
                    );
                  
          $model = $this->setData($data);
          $model = $this->load($slider_id)->setData($data);
          try{
            $model->setId($slider_id)->save();
            Mage::getSingleton('core/session')->addSuccess( 'Saved Slider successfully!' );  
            return $slider_id;
        }catch(exception $e){
            Mage::getSingleton( 'core/session') ->addError( $e->getMessage() );
        }
    }

    public function deletes($slider_id)
    {
      $res = true;
      $model = $this->setId($slider_id);
          try{
            $model->delete();
            //Mage::getSingleton('core/session')->addSuccess( 'Deleted profile successfully!' );  
            //echo 'Inserted abc',$slider_id;
        }catch(exception $e){
            //Mage::getSingleton( 'core/session') ->addError( $e->getMessage() );
        }
      return $res;
    }
}