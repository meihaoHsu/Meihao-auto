<?php

class MeowPro_MWAI_Addons_Qdrant {
  private $core = null;
  private $envs = [];

  // Current Vector DB
  private $default_env = null;
  private $apiKey = null;
  private $server = null;

  function __construct() {
    global $mwai_core;
    $this->core = $mwai_core;
    $this->envs = $this->core->get_option( 'embeddings_envs' );
    $this->init_settings();

    add_filter( 'mwai_embeddings_add_index', array( $this, 'add_index' ), 10, 2 );
    add_filter( 'mwai_embeddings_list_indexes', array( $this, 'list_indexes' ), 10, 2 );
    add_filter( 'mwai_embeddings_list_vectors', array( $this, 'list_vectors' ), 10, 2 );
    add_filter( 'mwai_embeddings_delete_index', array( $this, 'delete_index' ), 10, 2 );
    add_filter( 'mwai_embeddings_add_vector', [ $this, 'add_vector' ], 10, 3 );
    add_filter( 'mwai_embeddings_get_vector', [ $this, 'get_vector' ], 10, 4 );
    add_filter( 'mwai_embeddings_query_vectors', [ $this, 'query_vectors' ], 10, 4 );
    add_filter( 'mwai_embeddings_delete_vectors', [ $this, 'delete_vectors' ], 10, 2 );

    // We don't have a way to delete everything related to a namespace yet, but it works like that:
    //$this->delete_vectors( null, null, true, 'nekod' );
  }

  function init_settings( $envId = null, $index = null ) {
    $default_id = $envId ?? $this->core->get_option( 'embeddings_default_env' );
    foreach ( $this->envs as $env ) {
      if ( $env['id'] === $default_id ) {
        $this->default_env = $env;
        break;
      }
    }
    // This class has only Qdrant support.
    if ( empty( $this->default_env ) || $this->default_env['type'] !== 'qdrant' ) {
      return false;
    }

    $this->apiKey = $env['apikey'];
    $this->server = $env['server'];
    return true;
  }

  function run( $method, $url, $query = null, $json = true, $isAbsoluteUrl = false ) {
    $headers = "accept: application/json, charset=utf-8\r\ncontent-type: application/json\r\n" .
      "api-key: " . $this->apiKey . "\r\n";
    $body = $query ? json_encode( $query ) : null;
    $url = $isAbsoluteUrl ? $url : $this->server . $url;
    $options = [
      "headers" => $headers,
      "method" => $method,
      "timeout" => MWAI_TIMEOUT,
      "body" => $body,
      "sslverify" => false
    ];

    try {
      $response = wp_remote_request( $url, $options );
      if ( is_wp_error( $response ) ) {
        throw new Exception( $response->get_error_message() );
      }
      $response = wp_remote_retrieve_body( $response );
      $data = $response === "" ? true : ( $json ? json_decode( $response, true ) : $response );
      if ( !is_array( $data ) && empty( $data ) && is_string( $response ) ) {
        throw new Exception( $response );
      }
      return $data;
    }
    catch ( Exception $e ) {
      error_log( $e->getMessage() );
      throw new Exception( $e->getMessage() . " (Qdrant)" );
    }
    return [];
  }

  function add_index( $index, $options ) {
    // Already handled.
    if ( !empty( $index ) ) { return $index; }
    $envId = $options['envId'];
    $name = $options['name'];
    if ( !$this->init_settings( $envId ) ) {
      return false;
    }
    $dimension = 1536;
    $metric = 'Cosine';
    $result = $this->run( 'PUT', '/collections/' . $name, [
      'vectors' => [
        'distance' => $metric,
        'size' => $dimension,
      ]
    ], true );
    return $name;
  }

  function delete_index( $success, $options ) {
    // Already handled.
    if ( $success ) { return $success; }
    $envId = $options['envId'];
    $name = $options['name'];
    if ( !$this->init_settings( $envId ) ) { return false; }
    $index = $this->run( 'DELETE', "/collections/{$name}" );
    $success = !empty( $index );
    return $success;
  }

  function list_indexes( $indexes, $options ) {
    // Already handled.
    if ( !empty( $indexes ) ) { return $indexes; }
    $envId = $options['envId'];
    if ( !$this->init_settings( $envId ) ) { return false; }

    $indexesIds = $this->run( 'GET', '/collections', null, true );

    $indexes = [];
    foreach ( $indexesIds['result']['collections'] as $row ) {
      $index = $this->run( 'GET', "/collections/" . $row['name'], null, true )['result'];
      $indexes[] = [
        'name' => $row['name'],
        'metric' => $index['config']['params']['vectors']['distance'],
        'dimension' => $index['config']['params']['vectors']['size'],
        'host' => $this->server,
        'ready' => $index['status'] === "green"
      ];
    }   
    return $indexes;
  }

  function list_vectors( $vectors, $options ) {
    // Already handled.
    if ( !empty( $vectors ) ) { return $vectors; }
    $envId = $options['envId'];
    $index = $options['index'];
    $limit = $options['limit'];
    $offset = $options['offset'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }

    $vectors = $this->run( 'POST', "/collections/{$index}/points/scroll", [
      'limit' => $limit,
      'offset' => $offset,
      'with_payload' => false,
      'with_vector' => false,
    ], true );
    $vectors = isset( $vectors['result']['points'] ) ? $vectors['result']['points'] : [];
    // $vectors = array_map( function( $vector ) {
    //   return [
    //     'id' => $vector['id'],
    //     'type' => isset( $vector['payload']['type'] ) ? $vector['payload']['type'] : 'manual',
    //     'title' => isset( $vector['payload']['title'] ) ? $vector['payload']['title'] : '',
    //     'values' => isset( $vector['vector'] ) ? $vector['vector'] : []
    //   ];
    // }, $vectors );
    $vectors = array_map( function( $vector ) { return $vector['id']; }, $vectors );
    return $vectors;
  }

  function delete_vectors( $success, $options ) {
    // Already handled.
    if ( $success ) { return $success; }
    $envId = $options['envId'];
    $index = $options['index'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }

    $ids = $options['ids'];
    $deleteAll = $options['deleteAll'];

    if ( $deleteAll ) {
      $body = [
        'filter' => [
          'must' => [[
            "is_empty" => [
              "key" => "any"
            ]
          ]]
        ]
      ];
    } else {
      $body = ['points' => $ids];
    }

    $success = $this->run( 'POST', "/collections/{$index}/points/delete", $body );
    $success = true;
    return $success;
  }

  function add_vector( $success, $vector, $options ) {
    if ( $success ) { return $success; }
    $envId = $options['envId'];
    $index = $options['index'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }
    $randomId = $this->get_uuid();
    $body = [
      'points' => [
        [
          'id' => $randomId,
          'vector' => $vector['embedding'],
          'payload' => [
            'type' => $vector['type'],
            'title' => $vector['title']
          ]
        ]
      ]
    ];

    $res = $this->run( 'PUT', "/collections/{$index}/points", $body );

    $success = isset( $res['status'] ) && $res['status'] === "ok";
    if ( $success ) {
      return $randomId;
    }
    $error = isset( $res['status']['error'] ) ? $res['status']['error'] : 'Unknown error from Qdrant.';
    throw new Exception( $error );
  }

  function query_vectors( $vectors, $vector, $options ) {
    // Output the content of the $vector array to see what's inside in the error_log
    if ( !empty( $vectors ) ) { return $vectors; }
    $envId = $options['envId'];
    $index = $options['index'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }
    $maxSelect = isset( $options['maxSelect'] ) ? $options['maxSelect'] : 10;

    $body = [
      'limit' => $maxSelect,
      'vector' => $vector,
      'with_payload' => true,
      //'with_vector' => true,
    ];

    $res = $this->run( 'POST', "/collections/{$index}/points/search", $body );
    $vectors = isset( $res['result'] ) ? $res['result'] : [];

    foreach ( $vectors as &$vector ) {
      $vector['metadata'] = $vector['payload'];
    }
    return $vectors;
  }

  function get_vector( $vector, $vectorId, $options ) {
    // Check if the filter has been already handled.
    if ( !empty( $vector ) ) { return $vector; }
    $vectorId = $vectorId;
    $envId = $options['envId'];
    $index = $options['index'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }

    $res = $this->run( 'GET', "/collections/{$index}/points/{$vectorId}" );
    
    $removeVector = isset( $res['result']['id'] ) ? $res['result'] : null;
    
    if ( !empty( $removeVector ) ) {
      return [
        'id' => $vectorId,
        'type' => isset( $removeVector['payload']['type'] ) ? $removeVector['payload']['type'] : 'manual',
        'title' => isset( $removeVector['payload']['title'] ) ? $removeVector['payload']['title'] : '',
        'values' => isset( $removeVector['vector'] ) ? $removeVector['vector'] : []
      ];
    }
    return null;
  }

  function get_uuid($len = 32, $strong = true) {
    $data = openssl_random_pseudo_bytes($len, $strong);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
      
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }
}