<?php
/*
Plugin Name: AI Engine: ChatGPT Chatbot, Content Generator, GPT 4, Ultra-Customizable (Pro)
Plugin URI: https://wordpress.org/plugins/ai-engine/
Description: GPT for WordPress! Chatbot (ChatGPT), content and images generator, copilot, model training and much more! Highly customizable, sleek UI. You will love it!
Version: 1.9.81
Author: Jordy Meow
Author URI: https://jordymeow.com
Text Domain: ai-engine

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/

define( 'MWAI_VERSION', '1.9.81' );
define( 'MWAI_PREFIX', 'mwai' );
define( 'MWAI_DOMAIN', 'ai-engine' );
define( 'MWAI_ENTRY', __FILE__ );
define( 'MWAI_PATH', dirname( __FILE__ ) );
define( 'MWAI_URL', plugin_dir_url( __FILE__ ) );
define( 'MWAI_TIMEOUT', 60 * 5 );
define( 'MWAI_DEFAULT_MODEL', 'gpt-3.5-turbo' );

require_once( MWAI_PATH . '/classes/init.php' );

// NOTE: This should be removed when GPT-4 is released to everyone.
add_filter( 'mwai_ai_exception', function ( $exception ) {
  try {
    if ( substr( $exception, 0, 56 ) === "Error model_not_found: The model: `gpt-4` does not exist" ) {
      return "The GPT-4 model is currently not available for your OpenAI account. Luckily, you can join the <a target='_blank' href='https://openai.com/waitlist/gpt-4-api'>waitlist</a> to get access to it! ✌️";
    }
    else if ( substr( $exception, 0, 60 ) === "Error model_not_found: The model: `gpt-4-32k` does not exist" ) {
      return "The GPT-4 32k model is currently not available for your OpenAI account. Luckily, you can join the <a target='_blank' href='https://openai.com/waitlist/gpt-4-api'>waitlist</a> to get access to it! ✌️";
    }
    return $exception;
  }
  catch ( Exception $e ) {
    error_log( $e->getMessage() );
  }
  return $exception;
} );

?>