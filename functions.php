<?php

require_once 'lib/styles.php';
require_once 'lib/ads.php';
require_once 'lib/widgets.php';
require_once 'lib/admin.php';
require_once 'lib/Broadstreet.php';

function blargo_version() {
    return '0.0.1';
}

function blargo_setup() {
    
    $network_id = blargo_broadstreet_network_id();
    $is_setup   = blargo_is_setup();
    $errors     = array();
    
    if($network_id && !$is_setup) {
        $api   = blargo_get_api_client();
        $zones = blargo_ad_spots();
        
        foreach($zones as $alias => $info)
        {
            try
            {
                $api->createZone($network_id, $info['name'], array('alias' => $alias));
            }
            catch(Exception $ex)
            {
                $errors[] = "Issue creating zone: {$info['name']}. Stack trace: " . $ex->__toString();
            }
        }
        
        if(count($errors))
        {
            blargo_send_report('Blargo Setup Failure', implode("\n\n", $errors));
        }
        
        blargo_is_setup('true');
    }
    
    return $errors;    
}

/**
 * Get or set the status for this blog being set up with blargo
 * @param type $is_setup
 * @return type
 */
function blargo_is_setup($is_setup = null) {
    $network_id = blargo_broadstreet_network_id();
    $key        = 'Broadstreet_Network_Setup_' + $network_id;
    
    if($is_setup === null) {
        return blargo_get_option($key, false);
    }
    
    blargo_set_option($key, $is_setup);
}

/**
 * Get this blog's API key
 * @param string $key The API key
 * @return type
 */
function blargo_broadstreet_key($key = null) {
    if($key === null)
        return blargo_get_option(blargo_api_key_key());
    else
        return blargo_set_option (blargo_api_key_key(), $key);
}

/**
 * Get this Blog's network id
 * @return type
 */
function blargo_broadstreet_network_id($network_id = null) {
    if($network_id === null)
        return blargo_get_option(blargo_network_key_key());
    else
        return blargo_set_option(blargo_network_key_key (), $network_id);
}

/**
 * Get a Wordpress option
 * @param string $name
 * @param mixed $default What to return if the option isn't found
 * @return mixed
 */
function blargo_get_option($name, $default = FALSE)
{
    $value = get_option($name);
    if( $value !== FALSE ) return $value;
    return $default;
}

/**
 * Set a Wordpress option
 * @param string $name
 * @param mixed $value
 */
function blargo_set_option($name, $value)
{
    if (get_option($name) !== FALSE)
    {
        update_option($name, $value);
    }
    else
    {
        $deprecated = ' ';
        $autoload   = 'yes';
        add_option($name, $value, $deprecated, $autoload);
    }
}

/**
 * Get the option constant for the api key
 * @return string
 */
function blargo_api_key_key() {
    return 'Broadstreet_API_Key';    
}

/**
 * Get the option constant for the network key
 * @return string
 */
function blargo_network_key_key() {
    return 'Broadstreet_Network_Key';
}

/**
 * Get the option constant for the network key
 * @return string
 */
$blargo_api_client = null;
function blargo_get_api_client() {
    global $blargo_api_client;
    
    if($blargo_api_client) return $blargo_api_client;
    
    $key = blargo_broadstreet_key();
    $api = new Broadstreet($key);
    
    return $api;
}

/**
 * Makes a call to the Broadstreet service to collect information information
 *  on the blog in case of errors and other needs.
 */
function blargo_send_report($subject, $message = 'General')
{
    $report .= get_bloginfo('name'). "\n";
    $report .= get_bloginfo('url'). "\n";
    $report .= get_bloginfo('admin_email'). "\n";
    $report .= 'WP Version: ' . get_bloginfo('version'). "\n";
    $report .= 'Theme Version: ' . blargo_version() . "\n";
    $report .= "$message\n";

    @wp_mail('theme@broadstreetads.com', "Report: $subject", $report);
}

/**
 * If this is a new installation and we've never sent a report to the
 * Broadstreet server, send a packet of basic info about this blog in case
 * issues should arise in the future.
 */
function blargo_report_if_new()
{
    $install_key = 'Blargo_Installed';
    $upgrade_key = $install_key;

    $installed = blargo_get($install_key);
    $upgraded  = self::getOption($upgrade_key);

    $sent = ($installed && $upgraded);

    if($sent === FALSE)
    {   
        if(!$installed)
        {
            blargo_send_report("Blargo Installation");
            blargo_set_option($install_key, 'true');
            #blargo_set_option($upgrade_key, 'true');
        }
        else
        {
            blargo_send_report("Blargo Upgrade");
            blargo_set_option($upgrade_key, 'true');
        }
    }
}

/**
 * Callback for the required Broadstreet javascript on the CDN
 */
function blargo_broadstreet_cdn()
{
    # Add Broadstreet ad zone CDN
    if(!is_admin()) 
    {
        wp_enqueue_script('Broadstreet-cdn', 'http://cdn.broadstreetads.com/init.js');
    }
}

/**
 * Override the largo header
 */
if ( ! function_exists( 'largo_header' ) ) {
	function largo_header() {
        
        if (of_get_option('monster_ad')) {
            echo '<span class="blargo-monster-ad">';
            dynamic_sidebar('monster-ad-spot');
            echo '</span>';
        }
        
        echo '<div style="position: relative;">';
		
        $header_tag = is_home() ? 'h1' : 'h2'; // use h1 for the homepage, h2 for internal pages

		// if we're using the text only header, display the output, otherwise this is just replacement text for the banner image
		$header_class = of_get_option( 'no_header_image' ) ? 'branding' : 'visuallyhidden';
		$divider = $header_class == 'branding' ? '' : ' - ';

    	// print the text-only version of the site title
    	printf('<%1$s class="%2$s"><a itemprop="url" href="%3$s"><span itemprop="name">%4$s</span>%5$s<span class="tagline" itemprop="description">%6$s</span></a></%1$s>',
	    	$header_tag,
	    	$header_class,
	    	esc_url( home_url( '/' ) ),
	    	esc_attr( get_bloginfo('name') ),
	    	$divider,
	    	esc_attr( get_bloginfo('description') )
	    );

	    // add an image placeholder, the src is added by largo_header_js() in inc/enqueue.php
	    if ($header_class != 'branding')
	    	echo '<a itemprop="url" href="' . esc_url( home_url( '/' ) ) . '"><img class="header_img" src="" alt="" /></a>';

        echo '<span id="blargo-header-ad">';
        dynamic_sidebar('right-logo');
        echo '</span>';
        
	    if ( of_get_option( 'logo_thumbnail_sq' ) )
			echo '<meta itemprop="logo" content="' . of_get_option( 'logo_thumbnail_sq' ) . '"/>';
        
        echo '</div>';
	}
}

register_sidebar(array(
  'name' => __( 'Right of Logo Ad' ),
  'id' => 'right-logo',
  'description' => __('The widget area for an ad on the right of the logo'),
  'before_title' => '',
  'after_title' => '',
  'before_widget' => '',
  'after_widget'  => ''
));

register_sidebar(array(
  'name' => __( 'Monster Ad Spot' ),
  'id' => 'monster-ad-spot',
  'description' => __( 'The top of page, monster ad spot (960px wide). You must enable this ad spot on Appearance->Theme Options->Broadstreet Add-Ons->Monster Ad Spot before using it.' ),
  'before_title' => '',
  'after_title' => '',
  'before_widget' => '',
  'after_widget'  => ''
));

function blargo_like_button($content) {
    if(trim(of_get_option('post_like_button')) && is_single())
    {
        $content .= '<div class="fb-like-box" data-href="'.of_get_option('post_like_button').'" data-width="500" data-show-faces="false" data-border-color="#ccc" data-stream="false" data-header="false"></div>';
    }

    return $content;
}

function blargo_in_story_code($attrs)
{
    $network_id = blargo_broadstreet_network_id();

    if(blargo_is_setup())
        return "<span class=\"blargo-zone\"><script>broadstreet.zone_alias($network_id, 'in-story', { uriKeywords: true, softKeywords: true })</script></span>";
    else
        return "Your Broadstreet account isn't set up yet! Click 'Ad Management' in the admin panel menu to get started.";   
} 

/**
 * Get category slugs for use in keyword-dropping
 * @return type
 */
function blargo_get_category_slugs() {
    global $post;
    
    $slugs = array();
    
    if(is_single()) {
        
        $id = get_the_ID();
        $cats = wp_get_post_categories($id);      

        foreach($cats as $cat){
            $c = get_category($cat);
            $slugs[] = $c->slug;
        }
        
        $slugs[] = $post->post_name;
    }   
    
    if(is_page()) {
        $slugs[] = $post->post_name;
    }   
    
    if(is_category() || is_archive()) {
        $cat = get_query_var('cat');
        $cat = get_category ($cat);
        $slugs[] = $cat->slug;
    }
    
    if(is_home()) {
        // No categories
        $slugs = array();
    }

    return $slugs;
}

#add_filter('the_content', 'blargo_like_button', 1);
add_shortcode('ad', 'blargo_in_story_code');
add_action('wp_head', 'blargo_generate_styles');
add_action('init', 'blargo_broadstreet_cdn');

add_action('wp_head', 'blargo_get_category_slugs');
