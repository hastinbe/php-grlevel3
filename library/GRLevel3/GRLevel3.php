<?php
/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to beausy@gmail.com so I can send you a copy immediately.
 *
 * GRLevel3
 *
 * @category  GRLevel3
 * @package   GRLevel3
 * @author    Beau Hastings <beausy@gmail.com>
 * @copyright Copyright (c) 2009-2012, Beau Hastings
 * @license   New BSD License
 */
class GRLevel3
{
  const IMAGE_NOT_AVAILABLE = 'Radar images are not available at this time.';
  const IMAGE_NOT_CURRENT = 'Radar image is not current - more than %s seconds old.';

  /**
   * Full name of NEXRAD radar site (ICAO) -- MUST BE LOWERCASE
   * @var string
   */
  protected $_radar_name;

  /**
   * Default radar product 'cr','br','cr248','br1' etc.
   * @var string
   */
  protected $_radar_product;

  /**
   * Available radar products
   * @var array
   */
  protected $_radar_products = array(
    'br1'    => 'Base Reflectivity 1',
    'br2'    => 'Base Reflectivity 2',
    'br3'    => 'Base Reflectivity 3',
    'br4'    => 'Base Reflectivity 4',
    'br248'  => 'Base Reflectivity 248nm',
    'bv1'    => 'Base Velocity 1',
    'bv2'    => 'Base Velocity 2',
    'bv3'    => 'Base Velocity 3',
    'bv4'    => 'Base Velocity 4',
    'bv32'   => 'Base Velocity 32nm',
    'srv1'   => 'Storm Relative Velocity 1',
    'srv2'   => 'Storm Relative Velocity 2',
    'srv3'   => 'Storm Relative Velocity 3',
    'srv4'   => 'Storm Relative Velocity 4',
    'sw'     => 'Spectrum Width',
    'sw32'   => 'Spectrum Width 32nm',
    'cr'     => 'Composite Reflectivity',
    'cr248'  => 'Composite Reflectivity 248nm',
    'et'     => 'Echo Tops',
    'vil'    => 'Vertically Integrated Liquid',
    'ohr'    => 'One Hour Rain',
    'thr'    => 'Three Hour Rain',
    'str'    => 'Storm Rain',
    'dsp'    => 'Digital Total Rainfall',
  );

  /**
   * A collection of radar error messages
   * @var array
   */
  protected $_radar_messages = array();

  /**
   * Instance of GRLevel3_Image
   * @var GRLevel3_Image
   */
  protected $_image;

  /**
   * Directory containing collection of radar images
   * @var string
   */
  protected $_image_directory;

  /**
   * Image type (ie: png, jpg)
   * @var string
   */
  protected $_image_type = 'png';

  /**
   * Number of images
   * @var integer
   */
  protected $_image_count;

  /**
   * Image width
   * @var integer
   */
  protected $_image_width = 512;

  /**
   * Image height
   * @var integer
   */
  protected $_image_height = 512;
  /**
   * Image expiration in seconds
   * @var integer
   */
  protected $_image_expiration = 1200;

  /**
   * Image collection
   * @var array
   */
  protected $_images = array();

  /**
   * Constructor
   *
   * @param  array  $options
   * @return void
   */
  public function __construct(array $options = null)
  {
    if (is_array($options))
      $this->setOptions($options);
  }

  /**
     * Set property value
     *
     * @param  string  $name   Property name
     * @param  mixed   $value  Property value
     * @return void
     */
  public function __set($name, $value)
  {
    $method = 'set' . $name;
    if (!method_exists($this, $method))
    {
      /**
       * @see GRLevel3_Exception
       */
      require_once 'Exception.php';
      throw new GRLevel3_Exception('Invalid GRLevel3 property');
    }
    $this->$method($value);
  }

  /**
   * Retrieve property value
   *
   * @param  string  $name  Property name
   * @return mixed
   */
  public function __get($name)
  {
    $method = 'get' . $name;
    if (!method_exists($this, $method))
    {
      /**
       * @see GRLevel3_Exception
       */
      require_once 'Exception.php';
      throw new GRLevel3_Exception('Invalid GRLevel3 property');
    }
    return $this->$method();
  }

  /**
   * Set property values
   *
   * @param  array  $options  An associative array of property name and value pairs
   * @return GRLevel3
   */
  public function setOptions(array $options)
  {
    $methods = get_class_methods($this);

    foreach ($options as $key => $value)
    {
      $method = 'set' . ucfirst($key);
      if (in_array($method, $methods))
        $this->$method($value);
    }
    return $this;
  }
  /**
   *
   *
   * @param   string  $name  Full name of NEXRAD radar site
   * @return   GRLevel3
   */
  public function setRadarName($name)
  {
    $this->_radar_name = strtolower($name);
    return $this;
  }

  /**
   * Retrieve the full name of NEXRAD radar site
   *
   * @return string
   */
  public function getRadarName()
  {
    return $this->_radar_name;
  }

  /**
   * Set the default radar product
   *
   * @param  string  $product  A radar product (cr, br1, etc)
   * @return GRLevel3
   */
  public function setRadarProduct($product)
  {
    $this->_radar_product = $product;
    return $this;
  }
  /**
   * Retrieve the default radar product
   *
   * @return string
   */
  public function getRadarProduct()
  {
    return $this->_radar_product;
  }

  /**
   * Set the radar products
   *
   * @param  array  $products  An array of radar products
   * @return GRLevel3
   */
  public function setRadarProducts(array $products = array())
  {
    $this->_radar_products = $products;
    return $this;
  }
  /**
   * Retrieve the radar products
   *
   * @return array
   */
  public function getRadarProducts()
  {
    return $this->_radar_products;
  }
  /**
   * Set the radar image types
   *
   * @param  array  $messages  An array of radar error messages
   * @return GRLevel3
   */
  public function setRadarMessages($messages)
  {
    $this->_radar_messages = $messages;
    return $this;
  }
  /**
   * Retrieve the radar error messages
   *
   * @return array
   */
  public function getRadarMessages()
  {
    return $this->_radar_messages;
  }

  /**
   * Set the image directory
   *
   * @param  string  $value  Path to GRLevel3 images
   * @return GRLevel3
   */
  public function setImageDirectory($value)
  {
    $this->_image_directory = $value;
    return $this;
  }
  /**
   * Retrieve the image directory
   *
   * @return string
   */
  public function getImageDirectory()
  {
    return $this->_image_directory;
  }

  /**
   * Set the image type
   *
   * @param  string  $value  File type of radar images
   * @return GRLevel3
   */
  public function setImageType($value)
  {
    $this->_image_type = $value;
    return $this;
  }

  /**
   * Retrieve the image type
   *
   * @return string
   */
  public function getImageType()
  {
    return $this->_image_type;
  }
  /**
   * Set the image count
   *
   * @param  integer  $value
   * @return GRLevel3
   */
  public function setImageCount($value)
  {
    $this->_image_count = $value;
    return $this;
  }

  /**
   * Retrieve the image count
   *
   * @return integer
   */
  public function getImageCount()
  {
    return $this->_image_count;
  }

  /**
   * Set the image width
   *
   * @param  integer  $value  Image width
   * @return GRLevel3
   */
  public function setImageWidth($value)
  {
    $this->_image_width = $value;
    return $this;
  }

  /**
   * Retrieve the image width
   *
   * @return integer
   */
  public function getImageWidth()
  {
    return $this->_image_width;
  }

  /**
   * Set the image height
   *
   * @param  integer  $value  Image height
   * @return GRLevel3
   */
  public function setImageHeight($value)
  {
    $this->_image_height = $value;
    return $this;
  }

  /**
   * Retrieve the image height
   *
   * @return integer
   */
  public function getImageHeight()
  {
    return $this->_image_height;
  }

  /**
   * Set the image expiration time
   *
   * @param  integer  $value  Time in seconds
   * @return GRLevel3
   */
  public function setImageExpiration($value)
  {
    $this->_image_expiration = $value;
    return $this;
  }

  /**
   * Retrieve the image expiration time
   *
   * @return integer
   */
  public function getImageExpiration()
  {
    return $this->_image_expiration;
  }

  /**
   * Set the image collection
   *
   * @param  array  $images  An array of images
   * @return GRLevel3
   */
  public function setImageCollection(array $images = array())
  {
    if (is_array($images))
      $this->_images = $images;
  }

  /**
   * Retrieve the image collection
   *
   * @return array
   */
  public function getImageCollection()
  {
    return $this->_images;
  }

  /**
   * Add an image to the collection
   *
   * @param  string  $value  The image name
   * @return array
   */
  public function addImage($image)
  {
    return array_push($this->_images, $image);
  }

  /**
   * Remove an image from the collection
   *
   * @param  string  $value  The image name
   * @return void
   */
  public function removeImage($image)
  {
    if (in_array($image, $this->_images))
      $this->setImages(array_diff($this->_images, array($image)));
  }

  /**
   * Retrieve available radar products
   *
   * @return  array  An associative array of radar product and legend value pairs
   */
  public function getAvailableProducts()
  {
    $avail_products = array();
    $radar_messages = array();
    $products = $this->getRadarProducts();
    $webroot = $this->_findWebRoot();

    foreach ($products as $product => $legend)
    {
      $tname = realpath($webroot . $this->getImageDirectory() . '/' ) . '/' . strtolower($this->getRadarName()) . '_' . $product . '_';
      $ttype = '.' . $this->getImageType();
      $tfile = $tname . '0' . $ttype;
      if (file_exists($tfile))
      {
        $secsOld = filemtime($tfile);

        /* COMMENTED OUT FOR TESTING, REMOVE ME
        if ($secsOld >= time() - $this->getImageExpiration())
        {
          $avail_products[$product] = $legend;
          $radar_messages[$product] = '&nbsp;';
        }
        else {
          $radar_messages[$product] = sprintf(GRLevel3::IMAGE_NOT_CURRENT, $this->getImageExpiration());
        }*/
        
        //
        $avail_products[$product] = $legend;
        $radar_messages[$product] = '&nbsp;';
      }
      else {
        $radar_messages[$product] = GRLevel3::IMAGE_NOT_AVAILABLE;
      }
    }

    $this->setRadarMessages($radar_messages);
    return $avail_products;
  }

  /**
   * Generates a thumbnail for the given image
   *
   * @return boolean  Indicates whether or not the thumbnail was generated successfully
   */
  public function generateThumbnail($image, $width, $height)
  {
    $webroot = $this->_findWebRoot();
    
    if (file_exists($webroot . $this->getImageDirectory() . '/' . basename($image)))
    {
      $parts = explode('.', $image);
      $image = $webroot . $this->getImageDirectory() . '/' . basename($image);

      if (preg_match('/jpg|jpeg/', $parts[1]))
        $srcimg = imagecreatefromjpeg($image);

      if (preg_match('/png/', $parts[1]))
        $srcimg = imagecreatefrompng($image);

      $thumbnail = $parts[0] . 't.' . $parts[1];

      $old_x = imageSX($srcimg);
      $old_y = imageSY($srcimg);

      if ($old_x > $old_y)
      {
        $thumb_w = $width;
        $thumb_h = $old_y * ($height / $old_x);
      }

      if ($old_x < $old_y)
      {
        $thumb_w = $old_x * ($width / $old_y);
        $thumb_h = $height;
      }

      if ($old_x == $old_y)
      {
        $thumb_w = $width;
        $thumb_h = $height;
      }

      $destimg = ImageCreateTrueColor($thumb_w, $thumb_h);
      if (!imagecopyresampled($destimg, $srcimg, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y))
        return false;

      if (preg_match('/png/', $parts[1]))
      {
        if (!imagepng($destimg, $webroot . $this->getImageDirectory() . '/' . basename($thumbnail)))
        {
          imagedestroy($destimg);
          imagedestroy($srcimg);
          return false;
        }
      }
      else {
        if (!imagejpeg($destimg, $webroot . $this->getImageDirectory() . '/' . basename($thumbnail)))
        {
          imagedestroy($destimg);
          imagedestroy($srcimg);
          return false;
        }
      }

      imagedestroy($destimg);
      imagedestroy($srcimg);

      return true;
    }

    return false;
  }
  
  /**
   * Attempt to find the web root path
   *
   * @return string
   */
  private function _findWebRoot()
  {
    if (!isset($_SERVER['DOCUMENT_ROOT']))
    {
      $path_trans = str_replace( '\\\\', '/', $_SERVER['PATH_TRANSLATED']);
      $webroot = substr($path_trans, 0, strlen($path_trans) - strlen($_SERVER['PHP_SELF']));
    }
    else {
      $webroot = $_SERVER['DOCUMENT_ROOT'];
    }
    
    return $webroot;
  }
}