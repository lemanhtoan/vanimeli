<?php

class Ves_TabsHome_Model_System_Config_Source_ListTheme {

    private function _listDirectories($path, $fullPath = false) {
        $result = array();
        $dir = opendir($path);
        if ($dir) {
            while ($entry = readdir($dir)) {
                if (substr($entry, 0, 1) == '.' || !is_dir($path . DS . $entry)) {
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
    public function toOptionArray() {

        //$directory = Mage::getBaseDir('design') . DS . 'frontend' . DS . $package;
        $directory = Mage::getBaseDir('design') . DS . 'frontend' . DS . 'default' . DS . 'default' . DS . 'template' . DS . 'ves' . DS . 'tabshome';


        $directories = $this->_listDirectories($directory);
        $templates = array();
        foreach ($directories as $key => $template) {
            $templates[] = array('value' => $template, 'label' => $template);
        }

        return $templates;
    }

}
