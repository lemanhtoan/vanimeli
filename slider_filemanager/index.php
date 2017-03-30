<?php
/**
 * Zo2 (http://www.zo2framework.org)
 * A powerful Joomla template framework
 *
 * @link        http://www.zo2framework.org
 * @link        http://github.com/aploss/zo2
 * @author      Duc Nguyen <ducntv@gmail.com>
 * @author      Hiepvu <vqhiep2010@gmail.com>
 * @copyright   Copyright (c) 2013 APL Solutions (http://apl.vn)
 * @license     GPL v2
 */
defined('_JEXEC') or die('Restricted Access');
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * @todo Opengraph support
 * @todo Facebook & Twitter ... data attributes support
 */
/* Get Zo2Framework */
$framework = Zo2Factory::getFramework();
?>
<!DOCTYPE html>
<html lang="<?php echo $this->zo2->template->getLanguage(); ?>" dir="<?php echo $this->zo2->template->getDirection(); ?>">
    <head>
        <?php unset($this->_scripts[JURI::root(true) . '/media/jui/js/bootstrap.min.js']); ?>
        <?php echo $this->zo2->template->fetch('html://layouts/head.response.php'); ?>
        <?php // echo $this->zo2->template->fetch('html://layouts/head.favicon.php'); ?>
    <jdoc:include type="head" />
        <!--[if !IE 8]> -->
    <link href='http://fonts.googleapis.com/css?family=Hind:400,300,500,600,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Lato:400,300,700,900' rel='stylesheet' type='text/css'>
	 <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/fonts.css" type="text/css" />
        <!-- <![endif]-->
</head>
<body class="<?php echo $this->zo2->layout->getBodyClass(); ?> <?php echo $this->zo2->template->getDirection(); ?> <?php echo $this->zo2->framework->isBoxed() ? 'boxed' : ''; ?>">
    <?php echo $this->zo2->template->fetch('html://layouts/css.condition.php'); ?>        
    <!-- Main wrapper -->
    <section class="zo2 wrapper<?php echo $this->zo2->framework->isBoxed() ? ' boxed container' : ''; ?>">
        <?php //echo $this->zo2->utilities->socialshares->render('floatbar');  ?>
        <?php echo $this->zo2->utilities->styleswitcher->render(); ?>
        <?php echo $this->zo2->layout->render(); ?>               
    </section>
    <?php echo $this->zo2->layout->renderOut(); ?>               
    <?php echo $this->zo2->template->fetch('html://layouts/joomla.debug.php'); ?>
    <script type="text/javascript">
        <?php echo $this->zo2->utilities->bottomscript->render(); ?>
    </script>

    
<!--Start of Zopim Live Chat Script-->
<script type="text/javascript">
window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute('charset','utf-8');
$.src='//v2.zopim.com/?ayL65WgghWHo22NNhYMryAiJdY4Vyb4n';z.t=+new Date;$.
type='text/javascript';e.parentNode.insertBefore($,e)})(document,'script');
</script>
<!--End of Zopim Live Chat Script-->

 
<script type="text/javascript">
 
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-39681578-1', 'auto');
  ga('send', 'pageview');

 
 
 
SqueezeBox.assign($$('.guide-theme a, .guide-theme a, .guide-module a'), {
 
    'handler':'iframe',
     'size': {x: 1100, y: 555}
}); 

</script>
 


</body>
</html>
