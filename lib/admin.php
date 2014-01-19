<?php

add_action('admin_menu', 	'blargo_admin_setup');
#add_action('admin_init', 	'blargo_admin_scripts');
#add_action('admin_notices', 'blargo_notices');

/**
 * A callback executed whenever the user tried to access the Broadstreet admin page
 */
function blargo_admin_setup()
{
    $icon_url = 'http://broadstreet-common.s3.amazonaws.com/broadstreet-blargo/broadstreet-icon.png';

    add_menu_page('Ad Manager', 'Ad Manager', 'edit_pages', 'Ad Manager', 'blargo_admin_settings', $icon_url);
    add_submenu_page('Ad Manager', 'Settings', 'Settings', 'edit_pages', 'Ad Manager', 'blargo_admin_settings');
    //add_submenu_page('Ad Manager', 'Help', 'How To Get Started', 'edit_pages', 'Broadstreet-Help', 'blargo_admin_help');
}

/**
 * The callback that is executed when the user is loading the admin page.
 *  Basically, output the page content for the admin page. The function
 *  acts just like a controller method for and MVC app. That is, it loads
 *  a view.
 */
function blargo_admin_settings()
{
    if($_POST['access_token']) {
        blargo_broadstreet_key($_POST['access_token']);
    }
    
    if($_POST['network_id']) {
        blargo_broadstreet_network_id($_POST['network_id']);
    }
    
    $setup_errors = array();
    if($_POST['setup']) {
        $setup_errors = blargo_setup();
    }

    $data = array();

    $data['api_key']            = blargo_broadstreet_key();
    $data['network_id']         = blargo_broadstreet_network_id();
    $data['errors']             = array();
    $data['networks']           = array();
    $data['key_valid']          = false;

    if(!$data['api_key']) 
    {
        $data['errors'][] = '<strong>You dont have an API key set yet!</strong><ol><li>If you already have a Broadstreet account, <a href="http://my.broadstreetads.com/access-token">get your key here</a>.</li><li>If you don\'t have an account with us, <a target="blank" id="one-click-signup" href="#">then use our one-click signup</a>.</li></ol>';
    } 
    else 
    {
        $api = blargo_get_api_client();

        try
        {
            $data['networks']  = $api->getNetworks();
            $data['key_valid'] = true;
        }
        catch(Exception $ex)
        {
            $data['networks'] = array();
            $data['key_valid'] = false;
        }
    }

    ?>
    <style>
        #blargo-settings-pane {
            background-color: white;
            width: 400px;
            border: 1px solid #ccc;
            padding: 10px;
            padding-top: 0px;
            margin-top: 10px;
        }
        
        #blargo-settings-pane h2 {
            text-align: center;
        }
        
        #blargo-settings-pane select {
            width: 200px;
        }
        
        .blargo-important {
            color: red;
            font-weight: bold;
        }
        
        .blargo-divider {
            border-bottom: 1px dotted #eee;
            margin: 7px 0 7px 0;
        }
        
    </style>
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=313569238757581";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <script>window.admin_email = '<?php bloginfo('admin_email'); ?>';</script>
    
    <a target="_blank" href="http://broadstreetads.com"><img style="width:380px;" src="http://broadstreet-common.s3.amazonaws.com/broadstreet-blargo/broadstreet-logo.png" alt="Broadstreet" /></a>
    
    <div id="blargo-settings-pane">
        <h2>Ad Management Account Setup</h2>
        <div class="blargo-divider"></div>
        <?php if($data['api_key'] && !$data['key_valid']): ?>
        <p>
            <span class="blargo-important">The access token you entered isn't valid.
            Please re-enter it.</span>
        </p>
        <?php endif; ?>
        <?php if(!$data['api_key']): ?>
        <div>
            <p>
                <span class="blargo-important">Important!</span> Your Broadstreet settings have not been entered yet.
                Broadstreet is the service that helps you manage your ads.
                If you do not already have a Broadstreet account, 
                <a target="_blank" href="https://my.broadstreetads.com/register">sign up here</a>.
            </p>

            <p>
                Once that is complete, copy and paste the access token (a long
                string of random numbers and letters) on 
                <a target="_blank" target="_blank"  href="https://my.broadstreetads.com/access-token">this page</a>
                below.
            </p>            
        </div>        
        <div class="blargo-divider"></div>
        <?php endif; ?>
        <div>
            <form method="post">
                <input type="text" name="access_token" placeholder="Paste Your Token Here" value="<?php echo htmlentities($data['api_key']) ?>">
                <input type="submit" value="Save" name="save" />
                <a target="_blank" href="https://my.broadstreetads.com/access-token">Get your token here</a>
            </form>
        </div>
        <?php if($data['key_valid']): ?>
        <div class="blargo-divider"></div>   
        <?php if(!$data['network_id']): ?>
            <p>
                <span class="blargo-important">Important!</span> You need
                to select which publication this Wordpress site is, then press
                save.
            </p>
        <?php endif; ?>
        <div>
            <form method="post">
                <select name="network_id" type="text">
                    <?php foreach($data['networks'] as $network): ?>
                    <option <?php if($data['network_id'] == $network->id) echo "selected"; ?> value="<?php echo $network->id ?>"><?php echo htmlentities($network->name) ?></option>
                    <?php endforeach; ?>
                    <?php if(count($data['networks']) == 0): ?>
                    <option value="-1">Enter a valid token above</option>
                    <?php endif; ?>
                </select>
                <input type="submit" value="Save" name="save" />
            </form>
        </div>
        <?php endif; ?>
        <?php if($data['key_valid'] && $data['network_id'] && !blargo_is_setup()): ?>
        <div class="blargo-divider"></div>
            <p>
                <span class="blargo-important">Important!</span> You're almost
                ready! Now we'll need to automatically create your ad zones
                so you can get your site ad-ready.
            </p>
            <form method="post">
                <input type="submit" value="Get Set Up for Ads" name="setup" />
            </form>
        <?php endif; ?>
        <?php if($data['key_valid'] && $data['network_id'] && blargo_is_setup()): ?>
            <div class="blargo-divider"></div> 

                <strong style="color: green;">You are all set up!</strong> You can now:
            <ol>
                <li>Visit the <a href="widgets.php">Widgets page</a> and place
                    <strong>Ad Zone</strong> widgets in your sidebar.</li>
                <li>Visit the <a target="_blank" href="https://my.broadstreetads.com/networks/<?php echo $data['network_id'] ?>?access_token=<?php echo $data['api_key'] ?>">Broadstreet dashboard</a> and begin placing ads
                    on your website.
                </li>
                <li>Join our <a target="_blank" href="https://www.facebook.com/groups/broadstreetads/">community group on Facebook</a></li>
                <li>Like us, below!</li>
            </ol>
                
            <div class="blargo-divider"></div>
            
            <div class="fb-like" data-href="http://www.facebook.com/broadstreetads" data-send="false" data-layout="box_count" data-width="450" data-show-faces="true"></div>
            <a href="https://twitter.com/broadstreetads" class="twitter-follow-button" data-show-count="false">Follow @broadstreetads</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    
            <?php if(count($setup_errors)): ?>
                <p>
                Although you were set up, it looks like there were some errors
                getting your account created. These have been reported to
                Broadstreet's team. Please copy and paste these errors in an
                email to kenny@broadstreetads.com:
                </p>
                <textarea><?php htmlentities(implode(',', $setup_errors)) ?></textarea>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
    <h4>More from Broadstreet: Our Blog</h4>
    <div id="bs-blog"></div>
    <script src="https://broadstreet-common.s3.amazonaws.com/broadstreet-net/init.js"></script>
    <script>
        jQuery(function() {
            var bs = new Broadstreet.Network();
            bs.postList('#bs-blog');
        });
    </script>
    
                

    <?php
}