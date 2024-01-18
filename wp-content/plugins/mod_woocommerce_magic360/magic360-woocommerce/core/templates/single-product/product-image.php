<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $woocommerce, $product, $main_id;

$attachment_ids = $product->get_gallery_image_ids();

if (!isset($GLOBALS['magictoolbox']['WooCommerce_Magic360_product_loaded'])) {

?>
<div class="images woocommerce-product-gallery__wrapper">

	<?php
		$flag = (isset($main_id) or has_post_thumbnail()) ? true : false; 

        if ( $flag ) {
            //WoodMart theme sale barge fix
            do_action( 'woodmart_before_single_product_main_gallery' );

            //$pid = isset($main_id) ? $main_id : $product->get_id();
            $pid = $product->get_id();

            if(function_exists('wpml_get_default_language')){
                $pid_ = apply_filters( 'wpml_object_id', $pid, 'post' );
                if (!empty($pid_)) {
                    $pid = $pid_;
                }
            }


            $plugin = $GLOBALS['magictoolbox']['WooCommerceMagic360'];

            $GLOBALS['custom_template_headers'] = true;

            $plugin->params->setProfile('product');
            $useWpImages = $plugin->params->checkValue('use-wordpress-images','yes');
            $plugin->params->setProfile('product');

            if (!$useWpImages) { //no need in watermark with wp images

                /*set watermark options for all profiles START */
                $defaultParams = $plugin->params->getParams('default');
                $wm = array();
                $profiles = $plugin->params->getProfiles();
                foreach ($defaultParams as $id => $values) {
                    if (($values['group']) == 'Watermark') {
                        $wm[$id] = $values;
                    }
                }
                foreach ($profiles as $profile) {
                    $plugin->params->appendParams($wm,$profile);
                }
                /*set watermark options for all profiles END */
            }
            if(magictoolbox_WooCommerce_Magic360_check_plugin_active('others')){

                $magictoolbox_plugin_name = array('WooCommerceMagicZoom','WooCommerceMagicZoomPlus','WooCommerceMagicThumb');
                foreach ($magictoolbox_plugin_name as $plugin_name) {
                    if(isset($GLOBALS['magictoolbox'][$plugin_name]) && !empty($GLOBALS['magictoolbox'][$plugin_name]) ){
                        $others_plugin = $GLOBALS['magictoolbox'][$plugin_name];
                        $others_plugin->params->setProfile('product');
                        break;
                    }
                }
                if ($others_plugin->params->checkValue('page-status','Yes')) {
                    return;
                }
            }

            if ($plugin->params->checkValue('page-status','Yes') && metadata_exists( 'post', $pid, '_magic360_data' )) {

                $magic360_data = json_decode((get_post_meta( $pid, '_magic360_data', true )), true);
                $magic360_image_gallery = $magic360_data['images_ids'];

                if(!empty($magic360_image_gallery)){
                    foreach($magic360_image_gallery as $i => $image_id) {
                        $image_src = wp_get_attachment_image_src($image_id, 'original');
                        $image_src = preg_replace('/.*(\/wp-content.*)/','$1', $image_src[0]);
                        $GLOBALS['magic360images'][$i] = array(
                            'medium' => WooCommerce_Magic360_get_product_image($image_src,'thumb',$image_id),
                            'img' => WooCommerce_Magic360_get_product_image($image_src,'original',$image_id),
                            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
                        );
                    }
                    $plugin->params->setValue('columns', $magic360_data['options']['columns']);


                    //alphanumeric sort
                    usort($GLOBALS['magic360images'], 'magictoolbox_WooCommerce_Magic360_key_sort');

                    $html = $plugin->getMainTemplate($GLOBALS['magic360images']);

                    $html = str_replace('<img','<img alt="'.$GLOBALS['magic360images'][0]['alt'].'"',$html);
                    
                    echo '<div class="spinContainer">'.$html.'</div>';
                    
                }
            }
            $GLOBALS['magictoolbox']['WooCommerce_Magic360_product_loaded'] = true;
		}


    if ( $attachment_ids && $product->get_image_id() ) {
        foreach ( $attachment_ids as $attachment_id ) {
            echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $attachment_id ), $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
        }
    }
?>

</div>

<?php } ?>