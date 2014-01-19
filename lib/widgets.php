<?php

/**
 * This is an optional widget to display a broadstreet zone
 */
class Blargo_Zone_Widget extends WP_Widget
{
    /**
     * Set the widget options
     */
     function __construct()
     {
        $widget_ops = array('classname' => 'bs_zones', 'description' => 'Easily place an ad zone with this widget');
        $this->WP_Widget('bs_zones', 'Ad Zone', $widget_ops);
     }

     /**
      * Display the widget on the sidebar
      * @param array $args
      * @param array $instance
      */
     function widget($args, $instance)
     {
         extract($args);
         
         $title      = apply_filters('widget_title', $instance['w_title']);
         $zone_id    = $instance['w_zone'];
         $network_id = blargo_broadstreet_network_id();
         
        echo $before_widget;

        if(trim($title))
            echo $before_title . $title. $after_title;

        if(blargo_is_setup())
            echo "<span class=\"blargo-zone\"><script>broadstreet.zone_alias($network_id, '$zone_id', { uriKeywords: true, softKeywords: true })</script></span>";
        else
            echo "Your Broadstreet account isn't set up yet! Click 'Ad Management' in the admin panel menu to get started.";
        
        echo $after_widget;
     }

     /**
      * Update the widget info from the admin panel
      * @param array $new_instance
      * @param array $old_instance
      * @return array
      */
     function update($new_instance, $old_instance)
     {
        $instance = $old_instance;
        
        $instance['w_zone'] = $new_instance['w_zone'];
        $instance['w_title'] = $new_instance['w_title'];

        return $instance;
     }

     /**
      * Display the widget update form
      * @param array $instance
      */
     function form($instance) 
     {
        $defaults = array('w_title' => '', 'w_info_string' => '', 'w_opener' => '', 'w_closer' => '', 'w_zone' => '');
		$instance = wp_parse_args((array) $instance, $defaults);
        $network_id = blargo_broadstreet_network_id();
        $zones = blargo_ad_spots();
        
        
       ?>
        <div class="widget-content">
       <?php if(!$network_id): ?>
            <p style="color: green; font-weight: bold;">Your new website
                isn't set up with Broadstreet yet. Visit the 
                <a href="#">Ad Management</a> page in the left menu to get started.
                When you're done with that, come back here!
            </p>
        <?php else: ?>
        <p>
            <label for="<?php echo $this->get_field_id('w_title'); ?>">Title (optional):</label>
            <input class="widefat" type="input" id="<?php echo $this->get_field_id('w_title'); ?>" name="<?php echo $this->get_field_name('w_title'); ?>" value="<?php echo $instance['w_title'] ?>" />
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('w_info_string'); ?>">Zone</label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'w_zone' ); ?>" name="<?php echo $this->get_field_name('w_zone'); ?>" >
                <?php foreach($zones as $key => $zone): ?>
                <option <?php if(isset($instance['w_zone']) && $instance['w_zone'] == $key) echo "selected" ?> value="<?php echo $key ?>"><?php echo $zone['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php endif; ?>
        </div>
       <?php
     }
}

register_widget('Blargo_Zone_Widget');