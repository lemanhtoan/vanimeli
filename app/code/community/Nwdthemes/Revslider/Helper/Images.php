<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Helper_Images extends Mage_Cms_Helper_Wysiwyg_Images {

	const IMAGE_DIR = 'revslider';
	const IMAGE_THUMB_DIR = 'revslider/thumbs';
	const RS_IMAGE_PATH = 'revslider';

    public static $imageSizes = array(
        'gallery' => array('width' => 195, 'height' => 130),
        'thumbnail' => array('width' => 150, 'height' => 150),
        'medium' => array('width' => 300, 'height' => 200),
        'large' => array('width' => 1024, 'height' => 682),
        'post-thumbnail' => array('width' => 825, 'height' => 510)
    );

	/**
	 * Get images directory
	 *
	 * @return string
	 */
	
	public function getImageDir() {
		return self::IMAGE_DIR;
	}

	/**
	 * Get image thumbs directory
	 *
	 * @return string
	 */
	
	public function getImageThumbDir() {
		return self::IMAGE_THUMB_DIR;
	}
	
    /**
     * Images Storage root directory
     *
     * @return string
     */
    public function getStorageRoot() {
        return $this->imageBaseDir();
    }

    /**
     * Check whether using static URLs is allowed
     * always allowed for Revslider
     *
     * @return boolean
     */
    public function isUsingStaticUrlsAllowed() {
		return true;
    }

	/**
	 * Resize image
	 *
	 * @param string $fileName
	 * @param int $width
	 * @param int $height
	 * @return string Resized image url
	 */

	public function resizeImg($fileName, $width, $height = '') {

        $fileName = $this->imageClean($fileName);
		if (strpos($fileName, '//') !== false && strpos($fileName, $this->imageBaseUrl()) === false) {
			return $fileName;
		}

		if ( ! $height) {
			$height = $width;
		}

		$thumbDir = self::IMAGE_THUMB_DIR;
		$resizeDir = $thumbDir . "/resized_{$width}x{$height}";

		$ioFile = new Varien_Io_File();
		$ioFile->checkandcreatefolder(realpath(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)) . DS . $resizeDir);

		$baseURL = str_replace(array('https://', 'http://'), '//', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA));
		$fileName = str_replace(array('https://', 'http://'), '//', $fileName);
		$fileName = str_replace($baseURL, '', $fileName);

		$imageFile = str_replace(array('/', '\\'), '_', str_replace('revslider/', '', $fileName));

		$folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
		$imageURL = $folderURL . $fileName;

		$basePath = realpath(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)) . DS . $fileName;
		$newPath = realpath(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)) . DS . $resizeDir . DS . $imageFile;

		if ($width != '') {
			if (file_exists($basePath) && is_file($basePath) && ! file_exists($newPath)) {
				$imageObj = new Varien_Image($basePath);
				$imageObj->constrainOnly(TRUE);
				$imageObj->keepAspectRatio(TRUE);
				$imageObj->keepFrame(FALSE);
				$imageObj->keepTransparency(TRUE);
				//$imageObj->backgroundColor(array(255,255,255));
				$imageObj->resize($width, $height);
				$res = $imageObj->save($newPath);
			}
			$resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $resizeDir . '/' . $imageFile;
		} else {
			$resizedURL = $imageURL;
		}
		return $resizedURL;
	}

	/**
	 *	Compatibility wrappers
	 */

	public function get_intermediate_image_sizes() {
		return array();
	}

	public function wp_generate_attachment_metadata() {
		return FALSE;
	}

	public function wp_update_attachment_metadata() {
		return FALSE;
	}

	public function wp_get_attachment_metadata() {
		return FALSE;
	}

	/**
	 *	Get image id by url
	 *
	 *	@param	string	$url
	 *	@return	int
	 */
	
	public function attachment_url_to_postid($url) {
		return $this->get_image_id_by_url($url);
	}

	/**
	 *	Get image id by url
	 *
	 *	@param	string	$url
	 *	@return	int
	 */

	public function get_image_id_by_url($url) {
		$id = false;
		$imagePath = $this->imageFile($url);
		if ($imagePath && file_exists($this->imageBaseDir() . $imagePath)) {
			$id = $this->idEncode($imagePath);
		}
		return $id;
	}

	/**
	 *	Get image url by id and size
	 *
	 *	@param	int		Image id
	 *	@param	string	Size type
	 *	@return string
	 */

	public function wp_get_attachment_image_src($attachment_id, $size='thumbnail') {
		return $this->image_downsize($attachment_id, $size);
	}
	
	/**
	 *	Get attached file
	 *
	 *	@param	string
	 *	@return string
	 */

	public function get_attached_file($attachment_id) {
		if ($attachment_id) {
			$image = $this->imageBaseDir() . $this->imageFile($this->idDecode($attachment_id));
			if (file_exists($image)) {
				return $image;
			}
		}
	}
	
	/**
	 *	Resize image by id and preset size
	 *
	 *	@param	int		Image id
	 *	@param	string	Size type
	 *	@return string
	 */

	public function image_downsize($id, $size = 'medium') {
        $downsizedImage = false;
		if ((string)(int)$id === (string)$id && $product = Mage::helper('nwdrevslider/products')->getProduct($id, false)) {
			switch ($size) {
				case 'thumbnail' :
					$image = $product['image_thumbnail'];
				break;
				case 'small' :
				case 'medium' :
					$image = $product['image_medium'];
				break;
				case 'base' :
				case 'large' :
				case 'full' :
				default :
					$image = $product['image'];
				break;
			}
			if ($imageSzie = getimagesize( $this->imagePath($image) )) {
				$width = $imageSzie[0];
				$height = $imageSzie[1];
				$downsizedImage = array($image, $width, $height);
			}
		} elseif ($id) {
            $image = $this->imageFile($this->get_attached_file($id));
			if (isset(self::$imageSizes[$size])) {
				$width = self::$imageSizes[$size]['width'];
				$height = self::$imageSizes[$size]['height'];
				$imageUrl = $this->image_resize($this->imageUrl($image), $width, $height);
				$downsizedImage = array($imageUrl, $width, $height);
			} elseif ($imageSzie = getimagesize($this->imagePath($image))) {
				$width = $imageSzie[0];
				$height = $imageSzie[1];
				$imageUrl = $this->imageUrl($image);
				$downsizedImage = array($imageUrl, $width, $height);
			}
		}
		return $downsizedImage;
	}

	/**
	 *	Resize image
	 *
	 *	@param	string	Image url
	 *	@param	int		Width
	 *	@param	int		Height
	 *	@param	boolean	Is crop
	 *	@param	boolean	Is single
	 *	@param	boolean	Is upscale
	 *	@return string
	 */

	public function image_resize($url, $width = null, $height = null, $crop = null, $single = true, $upscale = false) {
		return $this->resizeImg($url, $width, $height);
	}

	/**
	 *	Insert new image
	 *
	 *	@param	array	Data
	 *	@param	string	Image
	 *	@return	int		Id
	 */

	public function wp_insert_attachment($data, $image) {
		return false;
	}

	/**
	 *	Alias for Resize Image
	 */

	public function rev_aq_resize($url, $width = null, $height = null, $crop = null, $single = true, $upscale = false) {
		return $this->image_resize($url, $width, $height, $crop, $single, $upscale);
	}

	/**
	 *	Convert image name to url
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function image_to_url($image) {
		$image = $this->imageFile($image);
		if (empty($image) || strpos($image, '//') !== false) {
			$url = $image;
		} else {
			$url = $this->imageBaseUrl() . $image;
		}
        $urlImageData = explode('media/', $url);
        if (isset($urlImageData['1'])) {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . ltrim($urlImageData['1'], '/');
        }
		return $url;
	}

	/**
	 *	Convert image url to path
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function image_url_to_path($url) {
		if (strpos($url, $this->imageBaseUrl()) === false && strpos($url, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)) !== false) {
			$image = str_replace(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), '', $url);
			$path = realpath(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)) . DS . $image;
		} else {
			$image = str_replace($this->imageBaseUrl(), '', $url);
			$path = $this->imageBaseDir() . $image;
		}

		return $path;
	}

	/**
	 *	Get image url
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function imageUrl($image) {
		if ($image && strpos($image, '//') === false) {
            $url = $this->imageBaseUrl() . $this->imageFile($image);
        } else {
            $url = $this->imageClean($image);
        }
        if (Mage::helper('nwdrevslider/framework')->is_ssl()) {
            $url = str_replace('http://', 'https://', $url);
        }
		return $url;
	}

	/**
	 *	Get image path
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function imagePath($image) {
		if (strpos($image, $this->imageBaseUrl()) === false && strpos($image, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)) !== false) {
			$image = str_replace(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), '', $image);
            if ($image) {
                $path = realpath(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)) . DS . str_replace(array('\\', '/'), DS, $image);
            }
		} else {
            $image = str_replace($this->imageBaseUrl(), '', $image);
            if ($image) {
                $path = $this->imageBaseDir() . str_replace(array('\\', '/'), DS, $image);
            }
		}
		return $path;
	}

	/**
	 *	Get image file from url or path
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function imageFile($image) {
        $replace = array(
            $this->imageBaseDir(),
            $this->imageBaseUrl(),
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA),
            realpath(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA))
        );
		$file = str_replace($replace, '', $this->imageClean($image));
		$file = ltrim($file, DS . '/');
		return $file;
	}

	/**
	 *	Clean image from artifacts
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function imageClean($image) {
		$image = str_replace(array('//', ':/'), array('/', '://'), $image);
		return $image;
	}

	/**
	 *	Get images base path
	 *
	 *	@return	string
	 */

	public function imageBaseDir() {
		return realpath(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)) . DS . self::IMAGE_DIR . DS;
	}

	/**
	 *	Get images base url
	 *
	 *	@return	string
	 */

	public function imageBaseUrl() {
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . self::IMAGE_DIR . '/';
	}

    /**
     *  Remove admin url from images
     *
     *  @param  mixed
     *  @return mixed
     */

    public function relativeImagesUrl($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->relativeImagesUrl($value);
            }
        } elseif (is_object($data)) {
            $arr = (array) $data;
            foreach ($arr as $key => $value) {
                $arr[$key] = $this->relativeImagesUrl($value);
            }
            $data = (object) $arr;
        } elseif (is_string($data)) {
            $arr = json_decode($data);
            if (is_array($arr) || is_object($arr)) {
                $arr = $this->relativeImagesUrl((array) $arr);
                $data = json_encode($arr);
            } elseif (strpos($data, $this->imageBaseUrl()) !== false) {
                $data = $this->imageFile($data);
            }
        }
        return $data;
    }

}
