<?php
    $corePath = preg_replace('/https?:\/\/[^\/]*/is', '', get_option("siteurl"));
    $tmp = str_replace('/view/license', '', dirname(__FILE__));
    $corePath .= '/wp-content/'.preg_replace('/^.*?\/(plugins\/.*?)$/is', '$1', str_replace("\\","/", $tmp));

    $plugin_version = plugin_get_version_WooCommerce_Magic360();
    $isKey = magictoolbox_WooCommerce_Magic360_get_data_from_db();
    $isScrollKey = false;
?>
<div class="license-container">
    <h1>Your Magic 360 license</h1>

    <?php if (!$isKey) { ?>
    <div class="magictoolbox-trial-box update-nag" style="width: 300px;    float: right;    display: inline-block;    margin-right: 45px; padding: 20px;">
        <a href="<?php echo WooCommerceMagic360_url('https://www.magictoolbox.com/buy/magic360/','license page buy your license link'); ?>" target="_blank" style="display: inline-block; lineheight: 0;"><img src="<?php echo $corePath; ?>/admin_graphics/magic360-trial-block.png" alt="" style="max-width: 100%;" /></a>
    </div>
    <?php } ?>
    <?php if (!$isKey) { ?>
    <div class="magictoolbox-trial-text">
        <p>You're using the <b>Trial</b> version (or license key isn't entered) of Magic 360.</p>
        <p>All features are included and there's no time limit.</p>

        </br>
        <h2><b>Upgrade now</b></h2>
        <p>To remove the red message "Magic 360&trade; trial version", buy a license:</p>
        <p><a href="<?php echo WooCommerceMagic360_url('https://www.magictoolbox.com/buy/magic360/','license page buy your license link'); ?>" target="_blank" class="button button-primary orange-button">Buy my Magic 360 license &gt;</a></p>
        <p>
            <span>As well as no more nagging text, you'll enjoy:</span>
            <ul style="list-style-type: circle; margin-left: 35px;">
                <li>Free tech support</li>
                <li>12 months free updates</li>
                <li>30 day moneyback guarantee</li>
                <li>Choice of 40+ other modules</li>
            </ul>
        </p>

        </br>
        <h2><b>Register your Magic 360 license</b></h2>
        <p>After buying your license, register it below:</p>
    </div>
    <?php } ?>
    <div>
        <?php if (!$isKey) { ?>
        <div class="CheckLicense" style="display:inline-block;">
            <div style="position: relative; display: inline-block; vertical-alight: middle;">
                <span>License key </span>
                <input class="license-key" type="text" placeholder="License key">
                <button class="button-primary register-btn main-b">Register</button>
                <div class="msg-wrapper">
                    <div class="scanner authentication">
                        <span>authentication</span>
                    </div>
                    <div class="key-is-not-correct sad-message">
                        The key is not correct.
                    </div>
                    <div class="license-failed sad-message">
                        License failed.
                    </div>
                    <div class="wordpress-error sad-message">
                        Wordpress error.
                    </div>
                    <div class="problem sad-message">
                        There was a problem with checking your license key. Please <a target="_blank" href="<?php echo WooCommerceMagic360_url('https://www.magictoolbox.com/contact/','license page problem contact link'); ?>">contact us</a>
                    </div>
                </div>
            </div>
        </div>
        <?php } else { ?>
        <p><span>License key: <?php echo $isKey->license; ?></span></p>
        <?php } ?>
    </div>

    <div>
    </div>
    <br/>
    <hr style="max-width: 50%; margin-left: 0;">
    <p>Thanks for using Magic 360!</p>
</div>
