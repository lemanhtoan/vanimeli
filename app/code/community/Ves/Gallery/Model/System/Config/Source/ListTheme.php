<?php
class Ves_Gallery_Model_System_Config_Source_ListTheme
{
	private function _listDirectories($path, $fullPath = false)
    {
        $result = array();
        $dir = opendir($path);
        if ($dir) {
            while ($entry = readdir($dir)) {
                if (substr($entry, 0, 1) == '.' || !is_dir($path . DS . $entry)){
                    continue;
                }
                if ($fullPath) {
                    $entry = $path . DS . $entry;
                }
                $result[] = $entry;
            }
            unset($entry);
            closedir($dir);
        }

        return $result;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		//$directory = Mage::getBaseDir('design') . DS . 'frontend' . DS . $package;
		$directory = Mage::getBaseDir('skin') . DS . 'frontend' . DS . 'base' . DS . 'default' .  DS .  'ves_gallery';
        $directories = $this->_listDirectories($directory);
        $templates =  array();
        $templates[] = array('value' => '', 'label'=>Mage::helper("ves_gallery")->__("-- Choose a popup skin --"));
		foreach($directories as $key => $template){
			$templates[] = array('value' => $template, 'label'=>$template);
		}
		
		return $templates;
    }
}
