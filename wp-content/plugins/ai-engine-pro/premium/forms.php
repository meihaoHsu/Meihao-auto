<?php

define( 'MWAI_FORMS_FRONT_PARAMS', [ 'id', 'label', 'type', 'name', 'options', 'copyButton',
  'required', 'placeholder', 'default', 'maxlength', 'rows', 'outputElement' ] );
define( 'MWAI_FORMS_SERVER_PARAMS', [ 'model', 'temperature', 'maxTokens', 'prompt', 'envId', 'env',
  'assistantId', 'embeddingsIndex', 'embeddingsEnv', 'embeddingsEnvId', 'embeddingsNamespace'
] );

class MeowPro_MWAI_Forms {
  private $core = null;
  private $namespace = 'mwai-ui/v1';

  function __construct() {
    global $mwai_core;
    $this->core = $mwai_core;
    if ( is_admin() ) { return; }

    add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
    add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
    add_shortcode( 'mwai-form-field', array( $this, 'shortcode_mwai_form_field' ) );
    add_shortcode( 'mwai-form-submit', array( $this, 'shortcode_mwai_form_submit' ) );
    add_shortcode( 'mwai-form-output', array( $this, 'shortcode_mwai_form_output' ) );
    add_shortcode( 'mwai-form-container', array( $this, 'shortcode_mwai_form_container' ) );
  }

  public function register_scripts() {
		$physical_file = trailingslashit( MWAI_PATH ) . 'premium/forms.js';	
		$cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MWAI_VERSION;
		wp_register_script( 'mwai_forms', trailingslashit( MWAI_URL ) . 'premium/forms.js',
			[ 'wp-element' ], $cache_buster, false );
	}

  public function enqueue_scripts() {
		wp_enqueue_script( "mwai_forms" );
	}

  function cleanParams( &$params ) {
		foreach ( $params as $param => $value ) {
			if ( empty( $value ) || is_array( $value ) ) {
				continue;
			}
			$lowerCaseValue = strtolower( $value );
			if ( $lowerCaseValue === 'true' || $lowerCaseValue === 'false' || is_bool( $value ) ) {
				$params[$param] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			}
			else if ( is_numeric( $value ) ) {
				$params[$param] = filter_var( $value, FILTER_VALIDATE_FLOAT );
			}
		}
		return $params;
	}

  public function fetchSystemParams( $id, $formId ) {
		$frontSystem = [
			'id' => $id,
			'formId' => $formId,
			'userData' => $this->core->getUserData(),
			'sessionId' => $this->core->get_session_id(),
			'restNonce' => $this->core->get_nonce(),
			'contextId' => get_the_ID(),
			'pluginUrl' => MWAI_URL,
			'restUrl' => untrailingslashit( rest_url() ),
			'debugMode' => $this->core->get_option( 'debug_mode' ),
			'stream' => $this->core->get_option( 'shortcode_chat_stream' ),
		];
		return $frontSystem;
	}

  function rest_api_init() {
		try {
			register_rest_route( $this->namespace, '/forms/submit', array(
				'methods' => 'POST',
				'callback' => array( $this, 'rest_submit' ),
        'permission_callback' => '__return_true'
			) );
		}
		catch ( Exception $e ) {
			var_dump( $e );
		}
	}

  public function stream_push( $data ) {
		$out = "data: " . json_encode( $data );
		echo $out;
		echo "\n\n";
		ob_end_flush();
		flush();
	}

  function rest_submit( $request ) {
    try {
			$params = $request->get_json_params();
      $context = null;
			$id = $params['id'] ?? null ;
			$formId = $params['formId'] ?? null;
			$stream = $params['stream'] ?? false;
			$fields = $params['fields'] ?? [];
      $systemParams = get_transient( 'mwai_custom_form_' . $id ) ?? [];
      $systemParams['prompt'] = $systemParams['prompt'] ?? "";
      $model = isset( $params['model'] ) ? $params['model'] : $systemParams['model'] ?? "";
      if ( empty( $model ) ) {
        $model = MWAI_FALLBACK_MODEL;
      }

      // Prepare the prompt (based on the fields).
      $prompt = isset( $params['prompt'] ) ? $params['prompt'] : $systemParams['prompt'] ?? "";
      foreach ( $fields as $name => $value ) {
        if ( $value === null ) { continue; }
        if ( is_array( $value ) ) {
          $value = implode( ",", $value );
        }
        $name = "{" . $name . "}";
        // Let's try to also make sure we replace the case where it is ${SELECTOR} as well.
        $prompt = str_replace( '$' . $name, $value, $prompt );
        $prompt = str_replace( $name, $value, $prompt );
      }
      $systemParams['prompt'] = $prompt;
      $systemParams['env'] = empty( $systemParams['env'] ) ? 'form' : $systemParams['env'];

      // Take care of the parameters
      $query = null;
      if ( substr( $model, 0, 6 ) === 'dall-e' ) {
        $query = new Meow_MWAI_Query_Image( $prompt, $model );
        $query->injectParams( $systemParams );
      }
      else if ( $model === 'whisper-1' ) {
        $query = new Meow_MWAI_Query_Transcribe( $prompt );
        $query->injectParams( $systemParams );
        $query->setPrompt( "" );
        $query->setURL( $prompt );
      }
      else {
        $query = !empty( $systemParams['assistantId'] ) ?
          new Meow_MWAI_Query_Assistant( $prompt ) : 
					new Meow_MWAI_Query_Text( $prompt, 4096 );
        $query->injectParams( $systemParams );

        // Awareness & Embeddings
				// TODO: This is same in Chatbot Legacy and Forms, maybe we should move it to the core?
        $embeddingsEnvId = $systemParams['embeddingsEnvId'] ?? $systemParams['embeddingsEnv'] ?? null;
				$embeddingsIndex = $systemParams['embeddingsIndex'] ?? null;
        $embeddingsNamespace = $systemParams['embeddingsNamespace'] ?? null;
				if ( $query->mode === 'chat' ) {
					$context = apply_filters( 'mwai_context_search', $context, $query, [ 
            'embeddingsEnvId' => $embeddingsEnvId,
            'embeddingsIndex' => $embeddingsIndex,
            'embeddingsNamespace' => $embeddingsNamespace
          ] );
					if ( !empty( $context ) ) {
						if ( isset( $context['content'] ) ) {
							$content = $this->core->cleanSentences( $context['content'] );
							$query->injectContext( $content );
						}
						else {
							error_log("AI Engine: A context without content was returned.");
						}
					}
				}
      }

      $query->setExtraParam( 'fields', $fields );

      // Process Query
			if ( $stream ) { 
				$streamCallback = function( $reply ) {
					//$raw = _wp_specialchars( $reply, ENT_NOQUOTES, 'UTF-8', true );
					$raw = $reply;
					$this->stream_push( [ 'type' => 'live', 'data' => $raw ] );
					if (  ob_get_level() > 0 ) {
						ob_flush();
					}
					flush();
				};
				header( 'Cache-Control: no-cache' );
				header( 'Content-Type: text/event-stream' );
				header( 'X-Accel-Buffering: no' ); // This is useful to disable buffering in nginx through headers.
				ob_implicit_flush( true );
				ob_end_flush();
			}

      // Query the AI
			$reply = $this->core->ai->run( $query, $streamCallback );
      $rawText = $reply->result;
      $extra = [];
			if ( $context ) {
				$extra = [ 'embeddings' => $context['embeddings'] ];
			}
      $rawText = apply_filters( 'mwai_form_reply', $rawText, $query, $params, $extra );

      $restRes = [
				'success' => true,
				'reply' => $rawText,
				'images' => $reply->getType() === 'images' ? $reply->results : null,
				'usage' => $reply->usage
			];

      // Process Reply
			if ( $stream ) {
				$this->stream_push( [ 'type' => 'end', 'data' => json_encode( $restRes ) ] );
				die();
			}
			else {
				return new WP_REST_Response( $restRes, 200 );
			}
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			if ( $stream ) { 
				$this->stream_push( [ 'type' => 'error', 'data' => $message ] );
			}
			else {
				return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
			}
		}
  }

   // Rename the keys of the atts into camelCase to match the internal params system.
  function keysToCamelCase( $atts ) {
    $atts = array_map( function( $key, $value ) {
			$key = str_replace( '_', ' ', $key );
			$key = ucwords( $key );
			$key = str_replace( ' ', '', $key );
			$key = lcfirst( $key );
			return [ $key => $value ];
		}, array_keys( $atts ), $atts );
		$atts = array_merge( ...$atts );
    return $atts;
  }

  function fetchFrontParams( $atts ) {
    $frontParams = [];
    foreach ( MWAI_FORMS_FRONT_PARAMS as $param ) {
			if ( isset( $atts[$param] ) ) {
				$frontParams[$param] = $atts[$param];
			}
		}
    $frontParams = $this->cleanParams( $frontParams );
    return $frontParams;
  }
  
  function fetchServerParams( $atts ) {
    $serverParams = [];
    foreach ( MWAI_FORMS_SERVER_PARAMS as $param ) {
      if ( isset( $atts[$param] ) ) {
        $serverParams[$param] = $atts[$param];
        if ( $param === 'prompt' ) {
          $serverParams[$param] = urldecode( $serverParams[$param] );
        }
      }
    }
    $serverParams = $this->cleanParams( $serverParams );
    return $serverParams;
  }

  function encodeParamsForHTML( $params ) {
    $params = htmlspecialchars( json_encode( $params ), ENT_QUOTES, 'UTF-8' );
    return $params;
  }

  // Based on the id, label, type, name and options, it will return the HTML code for the field.
  function shortcode_mwai_form_field( $atts ) {
    $atts = apply_filters( 'mwai_forms_field_params', $atts );
    $atts = $this->keysToCamelCase( $atts );
    $frontParams = $this->fetchFrontParams( $atts );

    // Client-side: Prepare JSON for Front Params and System Params
		$theme = isset( $frontParams['themeId'] ) ? $this->core->getTheme( $frontParams['themeId'] ) : null;
		$jsonFrontParams = $this->encodeParamsForHTML( $frontParams );
		$jsonFrontTheme = $this->encodeParamsForHTML( $theme );

    $this->enqueue_scripts();
		return "<div class='mwai-form-field-container' data-params='{$jsonFrontParams}' 
      data-theme='{$jsonFrontTheme}'></div>";
  }

  function shortcode_mwai_form_submit( $atts ) {
    $formId = 'mwai-' . uniqid();
    $atts = apply_filters( 'mwai_form_params', $atts );

    // Check if the old filter is used
    $originalAtts = $atts;
    $atts = apply_filters( 'mwai_forms_params', $atts );
    if ( $originalAtts !== $atts ) {
      trigger_error( 
        'The mwai_forms_params filter is deprecated. Please use mwai_form_params instead.', 
        E_USER_DEPRECATE
      );
    }
    
    $atts = $this->keysToCamelCase( $atts );
    $frontParams = $this->fetchFrontParams( $atts );
    $systemParams = $this->fetchSystemParams( $formId, $formId ); // Overridable by $atts later
    $serverParams = $this->fetchServerParams( $atts );

    // Extract the fields and selectors from the prompt, and build the inputs object.
    $prompt = $serverParams['prompt'];
    $inputs = [ 'fields' => [], 'selectors' => [] ];
    $matches = [];
    preg_match_all( '/{([A-Za-z0-9_-]+)}/', $prompt, $matches );
    foreach ( $matches[1] as $match ) {
      $inputs['fields'][] = $match;
    }
    $matches = [];
    preg_match_all( '/\$\{([^}]+)\}/', $prompt, $matches );
    foreach ( $matches[1] as $match ) {
      $inputs['selectors'][] = $match;
    }
    $frontParams['inputs'] = $inputs;

    // Server-side: Keep the System Params
		if ( count( $serverParams ) > 0 ) {
      $id = md5( json_encode( $serverParams ) );
      $systemParams['id'] = $id;
      $systemParams['formId'] = $formId;
      $systemParams['inputs'] = $inputs;
			set_transient( 'mwai_custom_form_' . $id, $serverParams, 60 * 60 * 24 );
		}

    // Client-side: Prepare JSON for Front Params and System Params
		$theme = isset( $frontParams['themeId'] ) ? $this->core->getTheme( $frontParams['themeId'] ) : null;
    $jsonFrontParams = $this->encodeParamsForHTML( $frontParams );
    $jsonFrontSystem = $this->encodeParamsForHTML( $systemParams );
    $jsonFrontTheme = $this->encodeParamsForHTML( $theme );

    $this->enqueue_scripts();
		return "<div class='mwai-form-submit-container' 
      data-params='{$jsonFrontParams}' data-system='{$jsonFrontSystem}' 
      data-theme='{$jsonFrontTheme}'>
    </div>";
  }

  function shortcode_mwai_form_output( $atts ) {
    //$atts = apply_filters( 'mwai_forms_output_params', $atts );
    $atts = $this->keysToCamelCase( $atts );
    $frontParams = $this->fetchFrontParams( $atts );

    // Client-side: Prepare JSON for Front Params and System Params
		$theme = isset( $frontParams['themeId'] ) ? $this->core->getTheme( $frontParams['themeId'] ) : null;
		$jsonFrontParams = $this->encodeParamsForHTML( $frontParams );
		$jsonFrontTheme = $this->encodeParamsForHTML( $theme );

    $this->enqueue_scripts();
		return "<div class='mwai-form-output-container' data-params='{$jsonFrontParams}' 
      data-theme='{$jsonFrontTheme}'></div>";
  }

  function chatgpt_style( $id ) {
    $css = file_get_contents( MWAI_PATH . '/premium/styles/forms_ChatGPT.css' );
    $css = str_replace( '#mwai-form-id', "#mwai-form-container-{$id}", $css );
    return "<style>" . $css . "</style>";
  }

  function shortcode_mwai_form_container( $atts ) {
    $html = '<div class="mwai-form-settings"></div>';
    $id = empty( $atts['id'] ) ? uniqid() : $atts['id'];
    $theme = strtolower( $atts['theme'] );
    $style_content = "";
    if ( $theme === 'chatgpt' ) {
      $style_content = $this->chatgpt_style( $id, $style_content );
    }
    $style_content = apply_filters( 'mwai_forms_style', $style_content, $id );
    return $style_content;
  }
}
