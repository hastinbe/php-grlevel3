<?php
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

/* @see GRLevel3 */
require_once 'library/GRLevel3/GRLevel3.php';

$gr3 = new GRLevel3(array(
  'radarName'      => 'kbis',
  'radarProduct'   => 'br1',
  'imageDirectory' => '/projects/grlevel3/radar_images',
  'imageType'      => 'png',
  'imageCount'     => 10,
  'imageWidth'     => 800,
  'imageHeight'    => 600
));

$products = $gr3->getAvailableProducts();
$cur_product = $gr3->getRadarProduct();

if (isset($_GET['p']))
{
  $cur_product = preg_replace('/[^a-zA-Z0-9 ]/u', '', (string) $_GET['p']); // English Alphabet

  if (!isset($products[$cur_product]))
    $cur_product = $gr3->getRadarProduct();
}

$image_name = strtolower($gr3->getRadarName() . '_' . $cur_product . '_');

// build an array of images
for ($i = 0; $i < $gr3->getImageCount(); $i++)
  $gr3->addImage($image_name . $i . '.' . $gr3->getImageType());

foreach ($gr3->getImageCollection() as $image)
{
  $image_path = $gr3->getImageDirectory() . '/' . basename($image);
  $images_assoc[] = array($image_path, $image_path, '', $image);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <title>php-grlevel3</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
      padding-top: 60px;
      padding-bottom: 40px;
    }
    .sidebar-nav {
      padding: 9px 0;
    }
    </style>
    <link href="css/bootstrap-responsive.min.css" rel="stylesheet">
    <!-- IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">php-grlevel3</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="active"><a href="#">Home</a></li>
            </ul>
            <p class="navbar-text pull-right" id="nav_time"></p>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    
    <div class="container-fluid">
      
      <div class="row-fluid">
<?php if (isset($products[$cur_product])): ?>
        <div class="well span3" style="padding: 8px 0;">
          <ul class="nav nav-list">
            <li class="nav-header">Radar Product</li>
  <?php foreach ($products as $product => $legend): ?>
            <li <?php echo ($product == $cur_product ? 'class="active"' : '') ?>><a href="?p=<?php echo $product ?>"><?php echo $legend ?></a></li>
  <?php endforeach ?>
          </ul>
        </div>
<?php endif ?>

<?php if (isset($products[$cur_product])): ?>
<div class="span8">
<div class="span6">
<h2>Radar | <small><?php echo $products[$cur_product] ?></small></h2>
</div>

<div class="pull-right" style="margin-bottom:8px">
  <h3>Delay</h3>
  <div class="btn-group">
    <a class="btn" href="#" id="increase-slide-delay"><i class="icon-plus"></i></a>
    <a class="btn" href="#" id="decrease-slide-delay"><i class="icon-minus"></i></a>
  </div>
</div>
</div>
<?php endif ?>

        <div class="span8">
<?php if (isset($products[$cur_product])): ?>
          <div id="radar"></div>
<?php else: ?>
          <div class="alert alert-info">
            <a class="close" data-dismiss="alert">&times;</a>
            <strong>We&#39;re sorry!</strong> Radar images are not available at this time.
          </div>
<?php endif ?>
        </div>

      </div><!--/.row-fluid-->
      
      <hr>
      <footer>
        <p>&copy; php-grlevel3 2012</p>
      </footer>
    </div><!--/.fluid-container-->
    
<?php if (isset($products[$cur_product])): ?>
    <script type="text/javascript" src="js/jquery-1.6.4.min.js"></script>
    <script type="text/javascript" src="js/simplegallery.js"></script>
    <script type="text/javascript">
    var mygallery = new simpleGallery({
      wrapperid:    "radar", // ID of gallery container
      dimensions:   [<?php echo $gr3->getImageWidth() ?>, <?php echo $gr3->getImageHeight() ?>], //width/height of gallery in pixels. Should reflect dimensions of the images exactly
      imagearray:   <?php echo json_encode($images_assoc) ?>,
      autoplay:     [true, 250, 100000], // [auto_play_boolean, delay_btw_slide_millisec, cycles_before_stopping_int]
      persist:      false, // remember last viewed slide and recall within same session?
      fadeduration: 250 // transition duration (milliseconds)
    });
    
    $('#increase-slide-delay').click(function(e) {
      mygallery.setting.autoplay[1] += 100;
      e.preventDefault();
    });
    
    $('#decrease-slide-delay').click(function(e) {
      var new_speed = mygallery.setting.autoplay[1] - 100;
      
      if (new_speed < 100)
        new_speed = 100;
      
      mygallery.setting.autoplay[1] = new_speed;

      e.preventDefault();
    });
    
    setInterval(function() {
      $('#nav_time').text(new Date().toUTCString());
    }, 1000);
    </script>
<?php endif ?>
  </body>
</html>