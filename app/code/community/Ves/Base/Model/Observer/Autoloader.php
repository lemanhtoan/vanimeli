<?php
/**
 * Base for Venus Extensions
 *
 * @category   Ves
 * @package    Ves_Base
 * @author     venustheme <venustheme@gmail.com>
 * @copyright  Copyright (c) 2009 Venustheme.com <venustheme@gmail.com>
 */
class Ves_Base_Model_Observer_Autoloader extends Varien_Event_Observer {

    /**
     * This an observer function for the event 'controller_front_init_before'.
     * It prepends our autoloader, so we can load the extra libraries.
     *
     * @param Varien_Event_Observer $event
     */
    protected static $registered = false;

    public function controllerFrontInitBefore( $event ) {
    	// this should not be necessary.  Just being done as a check
		if (!self::$registered) {
			spl_autoload_register( array($this, 'load'), true, true );
        	self::$registered = true;
		}
        
    }

    /**
     * This function can autoloads classes starting with:
     * - Solarium
     * - Symfony\Component\EventDispatcher
     *
     * @param string $class
     */
    public static function load( $class )
    {

    	if($class == "PtsWidgetPageBuilder" ) {
    		$phpFile = Mage::getBaseDir('lib') . '/VesBase/widgetbase.php';
    		if(file_exists($phpFile)) {
    			require_once( $phpFile );
    		}
    		
    	} elseif($class == "HelperForm") {
            $phpFile = Mage::getBaseDir('lib') . '/VesBase/form.php';
            if(file_exists($phpFile)) {
                require_once( $phpFile );
            }
        } elseif ( strpos($class, "PtsWidget") === 0 ) {
        	$class = str_replace("PtsWidget", "", $class);
        	$class = strtolower($class);
        	$phpFile = Mage::getBaseDir('lib') . '/VesBase/widget/' . str_replace( '\\', '/', $class ) . '.php';
        	if(file_exists($phpFile)) {
        		require_once( $phpFile );
        	}
        }
    }

}