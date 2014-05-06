<?php

if(!function_exists('blargo_generate_styles')) {

    function blargo_generate_styles() {
        $layout       = of_get_option('fixed_layout');
        $no_margins   = of_get_option('no_margins');
        $colors       = of_get_option('color_scheme');
        $background   = of_get_option('page_background');
        $transparency = of_get_option('bg_transparency');
        $font_set     = of_get_option('font_set');
        
        $styles   = "/* Generated by Blargo */\n";
        
        # Layout Style (fixed/fluid)
        if($layout) // 'Fixed' will have a value of 1
            $styles .= '
                #page, .global-nav, #site-footer { max-width: 960px; margin: 0 auto; }
            ';
        
        # Space-efficient layout
        if($no_margins)
            $styles .= '
                #page { padding: 0 0 18px 0; }
                #content { padding: 10px 20px 0 40px; }
            ';
        
        # Color Scheme
        $styles .= blargo_get_color_scheme($colors);
        
        # Background Image
        if($background)
            $styles .= "
                body { background: url($background) top center no-repeat; background-attachment:fixed; background-size: 100%;}
            ";
        
        if($transparency !== '')
            $styles .= "
                #page { background-color: rgba(256, 256, 256, 0.$transparency); }
            ";
        
        
        echo "\n<style type='text/css'>\n$styles\n</style>";
    }
    
    function blargo_get_color_scheme($scheme) {
        $colors = array();
        
        $colors['base'] = array (
            'nav_bg' => '#2275bb',
            'nav_font_color' => '#ffffff',
            'nav_font_size_px' => '15',
            'nav_active_bg' => '#1e67a5',
            'meta_font_color' => '#333333',
            'nav_sep' => '#1e67a5',
            'widget_header_bg' => '#2275bb',
            'widget_header_font_color' => '#ffffff',
            'content_font_color' => '#333333',
            'link_color' => '#2275bb',
            'link_color_hover' => '#368fda',
            'link_font_color_hover' => '#fff',
            'font_size_px' => '19.5',
            'custom' => '',            
        );
        
        $colors['blue'] = array (
            /* Base is actually blue */
        );
        
        $colors['blargo'] = array (
            'widget_header_bg' => 'transparent',
            'widget_header_font_color' => '#333333',
            'nav_sep' => '#111',
            'nav_bg' => '#333333',
            'font_size_px' => '16',
            'link_color' => '#333333',
            'link_color_hover' => '#368fda',
            'nav_font_size_px' => '17',
            'custom' => "
                .widgettitle, .stories h3.widgettitle { border: none; }
            ");

        $colors['gray'] = array (
            'widget_header_bg' => 'transparent',
            'widget_header_font_color' => '#333333',
            'nav_sep' => '#111',
            'nav_bg' => '#333333',
            'font_size_px' => '16',
            'link_color' => '#333333',
            'nav_active_bg' => '#555555',
            'link_color_hover' => '#555',
            'nav_font_size_px' => '17',
            'custom' => "
                .widgettitle, .stories h3.widgettitle { border: none; }
            ",
        );
        
        $colors['red'] = array (
            'widget_header_bg' => 'transparent',
            'widget_header_font_color' => '#333333',
            'nav_sep' => '#111',
            'nav_bg' => '#333333',
            'nav_active_bg' => '#ff0000',
            'font_size_px' => '18',
            'nav_font_size_px' => '17',
            'link_color' => '#333333',
            'link_color_hover' => '#555',
            'custom' => "
                .widgettitle, .stories h3.widgettitle { border: none; }
            ",
        );

        $colors['green'] = array (
            'widget_header_bg' => 'rgb(138,199,73)',
            //'widget_header_font_color' => '#333333',
            'nav_sep' => 'rgb(138,199,73)',
            'nav_bg' => 'rgb(138,199,73)',
            'nav_active_bg' => 'rgb(187,216,64)',
            'font_size_px' => '18',
            'nav_font_size_px' => '17',
            'link_color' => 'rgb(138,199,73)',
            'link_color_hover' => 'rgb(187,216,64)',
            'custom' => "                
            ",
        );
        
        $colors['full_red'] = array (
            'widget_header_bg' => '#c52626',
            //'widget_header_font_color' => '#333333',
            'nav_sep' => 'red',
            'nav_bg' => '#c52626',
            'nav_active_bg' => '#ff0000',
            'font_size_px' => '18',
            'nav_font_size_px' => '17',
            'link_color' => '#c52626',
            'link_color_hover' => '#555',
            'custom' => "                
            ",
        );
        
        $colors['yellow'] = array (
            'widget_header_bg' => 'transparent',
            'widget_header_font_color' => '#333333',
            'nav_sep' => '#111',
            'nav_bg' => '#333333',
            'nav_active_bg' => '#fcc71f',
            'font_size_px' => '19.5',
            'nav_font_size_px' => '17',
            'link_color' => '#333333',
            'link_color_hover' => '#555',
            'custom' => "
                .widgettitle, .stories h3.widgettitle { border: none; }
            ",
        );
                
        $set = isset($colors[$scheme]) ? $colors[$scheme] : $colors['base'];
        
        # Inherit from the base color set
        foreach($colors['base'] as $key => $val)
            if(!isset($set[$key])) $set[$key] = $val;
            
        return "
            .navbar ul { font-size: {$set['nav_font_size_px']}px; }
            a { color: {$set['link_color']}; }
            a:hover { color: {$set['link_color_hover']}; }
            .navbar-inner { background-color: {$set['nav_bg']}; }
            .navbar > li > a { color: {$set['nav_font_color']}; }    
            .navbar .divider-vertical {background-color: {$set['nav_sep']}; border-left: {$set['nav_sep']}; }    
            .navbar li>a:hover, .navbar .active > a, .navbar .active > a:hover { color: {$set['link_font_color_hover']}; background-color: {$set['nav_active_bg']}; }
            .byline .time-ago, .byline .edit-link a { color: {$set['meta_font_color']}; }
            a { color: {$set['link_color']}; }    
            p { font-size: {$set['font_size_px']}px; color: {$set['content_font_color']} }
            .widgettitle, .stories h3.widgettitle { color: {$set['widget_header_font_color']}; background-color: {$set['widget_header_bg']}; }
            .blargo-zone { display: inline-block; max-width: 100%; text-align: center; }
            .blargo-zone > span, .blargo-zone > span > a, .blargo-zone > span > img { display: inline-block; max-width: 100%; height: auto !important; }
            #blargo-header-ad { position: absolute; right: 10px; top: 30px; }
            @media (max-width: 900px) { #blargo-header-ad { display:none; position: static; right: 0; top: 0; display: block; padding: 10px; text-align: left; } }
            .blargo-monster-ad > .blargo-zone { display: block; background-color: #222; }
            .footer-credit { display: none; }
            {$set['custom']}
            
        ";
    }   
}
