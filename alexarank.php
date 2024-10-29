<?php
/*
Plugin Name: AlexaRank
Plugin URI: http://www.fliptel.de/wordpress-plugins
Description: Displays the Alexa traffic ranking in the sidebar of your blog via widget or anywhere else. <a href="options-general.php?page=alexarank/alexarank.php">Configure here</a>. Check out more <a href="http://www.fliptel.de/wordpress-plugins">Wordpress Plugins</a> by <a href="http://www.fliptel.de">Fliptel</a>.
Version: 0.2
Author: fliptel
Author URI: http://www.fliptel.de
*/

/**
 * v0.2 13.07.2009 fixed css, some templates got brocken thx to sullivan
 * v0.1 09.07.2009 initial release
 */

if(!class_exists('AlexaRank')):
class AlexaRank {
  var $id;
  var $version;
  var $title;
  var $name;
  var $options;
  var $path;
  var $cache_file;
  var $locale;
  var $url;
  var $layouts;
  
  function AlexaRank() {
    $this->id         = 'alexarank';
    $this->title      = 'AlexaRank';
    $this->version    = '0.2';
    $this->name       = $this->title. ' v'. $this->version;
    $this->path       = dirname(__FILE__);
    $this->url        = get_bloginfo('wpurl'). '/wp-content/plugins/' . $this->id; 

    $this->layouts = array(
      array(
        'icon'    => '0.gif',
        'width'   => 80,
        'height'  => 30,
        'text'  => array(
          'width' => 20,
          'x'     => 30,
          'y'     => 20,
          'font'  => 1,
          'color' => '808080'
        ),
        'image' => array(
          'x'     => 31,
          'y'     => 15,
          'w'     => 40,
          'h'     => 3,
          'color' => '1b13b7'
        )
      ),
      array(
        'icon'    => '1.gif',
        'width'   => 80,
        'height'  => 15,
        'text'  => null,
        'image' => array(
          'x'     => 35,
          'y'     => 6,
          'w'     => 40,
          'h'     => 3,
          'color' => '1b13b7'
        )
      ),
      array(
        'icon'    => '2.gif',
        'width'   => 80,
        'height'  => 15,
        'text'  => array(
          'width' => 32,
          'x'     => 15,
          'y'     => 1,
          'font'  => 2,
          'color' => '808080'
        ),
        'image' => null
      ),
      array(
        'icon'    => '3.gif',
        'width'   => 80,
        'height'  => 15,
        'text'  => array(
          'width' => 32,
          'x'     => 18,
          'y'     => 1,
          'font'  => 2,
          'color' => '808080'
        ),
        'image' => null
      ),
      array(
        'icon'    => '4.gif',
        'width'   => 80,
        'height'  => 15,
        'text'  => null,
        'image' => array(
          'x'     => 28,
          'y'     => 11,
          'w'     => 40,
          'h'     => 1,
          'color' => '1b13b7'
        )
      )
    );

	  $this->locale = get_locale();

	  if(empty($this->locale)) {
		  $this->locale = 'en_US';
    }

    load_textdomain($this->id, sprintf('%s/%s.mo', $this->path, $this->locale));

    $this->loadOptions();
    
    $this->cache_file = $this->path. '/cache/layout'. $this->options['layout']. '.gif';

    if(!@isset($_GET['image'])) {
      if(is_admin()) {
        add_action('admin_menu', array(&$this, 'optionMenu'));
      }
      else {
        add_action('wp_head', array(&$this, 'blogHead'));
      }

      add_action('widgets_init', array(&$this, 'initWidgets'));
    }
  }
  
  function optionMenu() {
    add_options_page($this->title, $this->title, 8, __FILE__, array(&$this, 'optionMenuPage'));
  }

  function optionMenuPage() {

    if(@$_REQUEST[$this->id]) {
      @unlink($this->cache_file);
    
      $this->updateOptions($_REQUEST[$this->id]);
    
      echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved!', $this->id) . '</strong></p></div>'; 
    }
?>
<div class="wrap">

<h2><?php _e('Settings', $this->id); ?></h2>
<form method="post" action="">
<table class="form-table">
<?php if(!file_exists($this->path. '/cache/') || !is_writeable($this->path. '/cache/')): ?>
<tr valign="top"><th scope="row" colspan="3"><span style="color:red;"><?php _e('Warning! The cachedirectory is missing or not writeable!', $this->id); ?></span><br /><em><?php echo $this->path; ?>/cache</em></th></tr>
<?php endif; ?>
</tr>
<tr valign="top">
  <th scope="row"><?php _e('Title', $this->id); ?></th>
  <td><input name="<?=$this->id?>[title]" type="text" class="code" value="<?=$this->options['title']?>" /><br /><?php _e('The title is displayed above the badge in widget mode only!', $this->id); ?></td></tr>
  <th scope="row"><?php _e('Layout', $this->id); ?></th>
  <td colspan="3">
  <input name="<?=$this->id?>[layout]" type="radio" class="code" value="0"<?php echo intval($this->options['layout']) == 0 ? ' checked="checked"' : ''; ?> />
  <img src="<?=$this->url?>/screenshot-1.gif" style="vertical-align:middle;" /><br /><br />
  <input name="<?=$this->id?>[layout]" type="radio" class="code" value="1"<?php echo intval($this->options['layout']) == 1 ? ' checked="checked"' : ''; ?> />
  <img src="<?=$this->url?>/screenshot-2.gif" style="vertical-align:middle;" /><br /><br />
  <input name="<?=$this->id?>[layout]" type="radio" class="code" value="2"<?php echo intval($this->options['layout']) == 2 ? ' checked="checked"' : ''; ?> />
  <img src="<?=$this->url?>/screenshot-3.gif" style="vertical-align:middle;" /><br /><br />
  <input name="<?=$this->id?>[layout]" type="radio" class="code" value="3"<?php echo intval($this->options['layout']) == 3 ? ' checked="checked"' : ''; ?> />
  <img src="<?=$this->url?>/screenshot-4.gif" style="vertical-align:middle;" /><br /><br />
  <input name="<?=$this->id?>[layout]" type="radio" class="code" value="4"<?php echo intval($this->options['layout']) == 4 ? ' checked="checked"' : ''; ?> />
  <img src="<?=$this->url?>/screenshot-5.gif" style="vertical-align:middle;" />
  </td>
</tr>

</table>

<p class="submit">
  <input type="submit" value="<?php _e('save', $this->id); ?>" name="submit" />
</p>

</form>

</div>
<?php
}
  function loadOptions() {
    if(!($this->options = get_option($this->id))) {
      $this->options = array(
        'title' => 'AlexaRank',
        'layout' => 0
			);

      add_option($this->id, $this->options, $this->name, 'yes');
    }
  }
  
  function updateOption($name, $value) {
    $this->updateOptions(array($name => $value));
  }

  function updateOptions($options) {
    foreach($this->options as $k => $v) {
      if(array_key_exists($k, $options)) {
        $this->options[$k] = $options[ $k ];
      }
    }

		update_option($this->id, $this->options);
	}
  
  function blogHead() {
    printf('<meta name="%s" content="%s/%s" />' . "\n", $this->id, $this->id, $this->version);
    print( '<style type="text/css">
#alexarank, #alexarank small {padding: 0;margin: 0;color: #aaa;font-family: Arial, sans-serif;font-size: 10px;font-style: normal;font-weight: normal;letter-spacing: 0px;text-transform: none; width: 80px;text-align:center;border:0;}
#alexarank small a:hover, #alexarank small a:link, #alexarank small a:visited, #alexarank small a:active {color: #aaa;text-decoration:none;cursor: pointer;text-transform: none;font-size:10px;border:0;display:inline;}
</style>');
  }

  function httpGet($url) {

    if(!class_exists('Snoopy')) {
      include_once(ABSPATH. WPINC. '/class-snoopy.php');
    }

	  $Snoopy = new Snoopy();

    if(@$Snoopy->fetch($url)) {

      if(!empty( $Snoopy->results)) {
        return $Snoopy->results;
      }
    }

    return false;
  }
  
  function normalizeValue($value) {
    if($value < 0) {
  		return 0;
  	}

    $rank = intval(floor(log($value) / log(5)));

  	return $rank > 10 ? 10 : 10 - $rank; 
  }

  function getAlexaRank() {
    $url = get_bloginfo('wpurl');

    $url = sprintf('http://data.alexa.com/data?cli=10&dat=snbamz&url=%s', urlencode($url));

    if(($data = $this->httpGet($url)) !== false) {
      
      preg_match('|POPULARITY URL="(.*?)" TEXT="([0-9]+)"|', $data, $matches);

      if(count($matches) == 3 && !empty($matches[ 2 ])) {
        return intval($matches[ 2 ]);
      }
    }

    return 0;
  }

  function rgbColor(&$img, $rgb) {
    if( $rgb[ 0 ] == '#' ) {
      $rgb = substr( $rgb, 1 );
    }
    
    $a = substr($rgb, 0, 2);
    $b = substr($rgb, 2, 2);
    $c = substr($rgb, 4, 2);

    return imagecolorallocate($img, hexdec($a), hexdec($b), hexdec($c));
  }

  
  function getCode() {
    return sprintf( '<div id="%s"><a class="snap_noshots" href="http://www.fliptel.de/wordpress-plugins#%s" target="_blank"><img src="%s/%s/%s/%s.php?image=1" border="0" alt="%s" title="%s" /></a><br /><small><a href="http://www.fliptel.de/wordpress-plugins" class="snap_noshots" target="_blank">Plugin</a> by <a href="http://www.fliptel.de" class="snap_noshots" target="_blank">Fliptel</a></small></div>', $this->id, $this->id, get_bloginfo('wpurl'), PLUGINDIR, $this->id, $this->id, $this->title, $this->title);
  }

  function draw() {
    clearstatcache();
    
    $create = false;
    
    if(!file_exists($this->cache_file)) {
      $create = true;
    }
    elseif(time() - filemtime($this->cache_file) > (3600 * 3)) {
      $create = true;
    }
    
    if($create) {

      $numeric = $this->getAlexaRank();

      $layout = $this->layouts[intval($this->options['layout'])];

      $img = @imagecreatefromgif($this->path. '/img/'. $this->options['layout']. '.gif');
      
      if(!is_null($layout['text'])) {

        $x = intval(round($layout['text']['width'] - ((imagefontwidth($layout['text']['font'])*strlen($numeric==0?'none':number_format($numeric,0,'.','.')))/2) + $layout['text']['x']));
        $color1 = $this->rgbColor($img, $layout['text']['color']);
        imagestring($img, $layout['text']['font'], $x, $layout['text']['y'], $numeric == 0 ? 'none' : number_format($numeric,0,'.','.'), $color1);
      }
            
      if(!is_null($layout['image']) && $numeric > 0) {
        $color2 = $this->rgbColor($img, $layout['image']['color']);
        imagefilledrectangle($img, $layout['image']['x'], $layout['image']['y'], $layout['image']['x'] + ($this->normalizeValue($numeric)*4)-1, $layout['image']['y'] + $layout['image']['h']-1, $color2);
      }

      if(is_writeable($this->path. '/cache')) {
        imagegif($img, $this->cache_file);
      }
    }
    else {
      $img = @imagecreatefromgif($this->cache_file);
    }
    
    header( 'Content-Type: image/gif' );

    @imagegif($img);
  }

  function initWidgets() {
    if(function_exists('register_sidebar_widget')) {
      register_sidebar_widget($this->title. ' Widget', array(&$this, 'Widget'), null, 'widget_'. $this->id);
    }
  }
  
  function widget($args) {
    extract($args);

    printf('%s%s%s%s%s%s', $before_widget, $before_title, $this->options['title'], $after_title, $this->getCode(), $after_widget);
  }
}

function alexarank_display() {
  global $AlexaRank;

  if(!isset($AlexaRank)) {
    $AlexaRank = new AlexaRank();
  }

  if($AlexaRank) {
    echo $AlexaRank->getCode();
  }
}
endif;

if(@isset($_GET['image'])) {
  include_once(dirname(__FILE__). '/../../../wp-config.php');

  if(!isset($AlexaRank)) {
    $AlexaRank = new AlexaRank();
  }

  $AlexaRank->draw();
}
else {
  add_action('plugins_loaded', create_function('$AlexaRank_s92231c', 'global $AlexaRank; $AlexaRank = new AlexaRank();')); 
}

?>
