<?php
 /*------------------------------------------------------------------------
  # Ves Map Module 
  # ------------------------------------------------------------------------
  # author:    Ves.Com
  # copyright: Copyright (C) 2012 http://www.ves.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.ves.com
  # Technical Support:  http://www.ves.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Model_Group extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {	
	    $this->_init('ves_testimonial/group');
	
    }
    public function add($name ='',$status='')
    {
      $data = array(
                    'name'             => $name,
                    'status'            => $status,
                    'store_id'          => 0,
                    );
          $model = $this->setData($data);
          try{
            $group_id = $model->save()->getId();
            Mage::getSingleton('core/session')->addSuccess( 'Created Group Tabs successfully!' );  
            //echo 'Inserted abc',$group_id;
            return $group_id;
        }catch(exception $e){
            Mage::getSingleton( 'core/session') ->addError( $e->getMessage() );
        }
    }

    // save  
    public function update($group_id ='',$name ='',$status='')
    {
      $data = array(
                    'name'             => $name,
                    'status'            => $status,
                    'store_id'          => 0,
                    );
          $model = $this->setData($data);
          $model = $this->load($group_id)->setData($data);
          try{
            $model->setId($group_id)->save();
            Mage::getSingleton('core/session')->addSuccess( 'Saved Group Tabs successfully!' );  
            return $group_id;
        }catch(exception $e){
            Mage::getSingleton( 'core/session') ->addError( $e->getMessage() );
        }
    }

    public function deletes($group_id)
    {
      $res = true;
      $images = $this->image;
      foreach ($images as $image)
      {
        if (preg_match('/sample/', $image) === 0)
          if ($image && file_exists(dirname(__FILE__).'/images/'.$image))
            $res &= @unlink(dirname(__FILE__).'/images/'.$image);
      }
      $model = $this->setId($group_id);
          try{
            $model->delete();
            //Mage::getSingleton('core/session')->addSuccess( 'Deleted profile successfully!' );  
            //echo 'Inserted abc',$group_id;
        }catch(exception $e){
            //Mage::getSingleton( 'core/session') ->addError( $e->getMessage() );
        }
      return $res;
    }
}