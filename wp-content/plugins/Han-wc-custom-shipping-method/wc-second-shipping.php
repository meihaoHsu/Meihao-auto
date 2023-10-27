<?php


/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function second_shipping_method_init() {
		if ( ! class_exists( 'WC_Second_Shipping_Method' ) ) {
			class WC_Second_Shipping_Method extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'second_shipping_method'; // Id for your shipping method. Should be uunique.
                    $this->method_title       = $this->get_option('title'); // Title shown in admin
					$this->method_description = __( 'Description of your shipping method' ); // Description shown in admin
                    $this->enabled            =  $this->get_option('enabled', $this->enabled); // This can be added as an setting but for this example its forced enabled
                    $this->title              = $this->get_option('title')?$this->get_option('title'):"Second Shipping Method"; // This can be added as an setting but for this example its forced.
                    $this->cost = $this->get_option('cost');
					$this->availability       = $this->get_option('availability');
                    $this->countries= $this->get_option('countries');
				    $this->free_cost = $this->get_option('free_cost');
					$this->init();
				}

				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}
				 protected function name($id)
				{
					$default_name = array(
						'second_shipping_method' => 'second_shipping_method',
						
					);
					return $default_name[$id];
				}
				 public function init_form_fields()
				{
					$form_fields = array(
						'enabled'                => array(
							'title'   => __('Enable', 'woocommerce'),
							'type'    => 'checkbox',
							'label'   => __('Enable', 'woocommerce'),
							'default' => 'no',
						),
						'title'                  => array(
							'title'    => __('Title', 'woocommerce'),
							'type'     => 'text',
							'default'  => __($this->name($this->id), 'woocommerce'),
							'desc_tip' => true,
						),
						 'availability'           => array(
								'title'   => __('Method availability', 'woocommerce'),
								'type'    => 'select',
								'default' => 'all',
								'class'   => 'availability wc-enhanced-select',
								'options' => array(
									'all'      => __('All allowed countries', 'woocommerce'),
									'specific' => __('Specific Countries', 'woocommerce'),
								),
							),
							'countries'              => array(
								'title'             => __('Specific Countries', 'woocommerce'),
								'type'              => 'multiselect',
								'class'             => 'wc-enhanced-select',
								'css'               => 'width: 450px;',
								'default'           => '',
								'options'           => WC()->countries->get_shipping_countries(),
								'custom_attributes' => array(
									'data-placeholder' => __('Select some countries', 'woocommerce'),
								),
							),
						'cost'                   => array(
							'title'   => __('費用', 'woocommerce'),
							'type'    => 'text',
							'default' => '120',
						),
						'free_cost'              => array(
							'title'   => __('訂單金額多少以上免運費'),
							'type'    => 'text',
							'default' => '1000',
						),
				 
					);

				   
						$this->form_fields = $form_fields;
        
    }
				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package=array() ) {
					 global $woocommerce;

                    $cart_contents_total = $woocommerce->cart->cart_contents_total;
                    $cart_total = apply_filters('custome_shipping_cart_total' , $cart_contents_total);

                    $cost = ((int) $cart_total  >= (int) $this->get_option('free_cost'))
					? 0
					: (int) $this->get_option('cost');
					$rate = array(
						'id' => $this->id,
						'label' => $this->title,
						'cost' => $cost,
						'calc_tax' => 'per_item'
					);

					// Register the rate
					$this->add_rate( $rate );
				}
			}
		}
	}

	add_action( 'woocommerce_shipping_init', 'second_shipping_method_init' );

	function add_second_shipping_method( $methods ) {
		$methods['second_shipping_method'] = 'WC_Second_Shipping_Method';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'add_second_shipping_method' );
}