<?php

class MeowPro_MWAI_Addons_Pinecone {
  private $core = null;
  private $envs = [];

  // Current Vector DB
  private $default_env = null;
  private $apiKey = null;
  private $server = null;
  private $host = null;
  private $index = null;
  private $namespace_support = true;
  private $indexes = [];

  function __construct() {
    global $mwai_core;
    $this->core = $mwai_core;
    $this->envs = $this->core->get_option( 'embeddings_envs' );
    $this->init_settings();

    // Add filters...
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
    // This class has only Pinecone support.
    if ( empty( $this->default_env ) || $this->default_env['type'] !== 'pinecone' ) {
      return false;
    }

    $this->apiKey = $env['apikey'];
    $this->server = $env['server'];
    $this->indexes = $env['indexes'];
    $this->index = !empty( $index ) ? $index : $env['index'];
    $this->host = $this->resolve_host( $this->index );
    $this->namespace_support = $this->server !== 'gcp-starter';
    return true;
  }

  function resolve_host( $indexName ) {
    $host = null;
    if ( !empty( $this->indexes ) ) {
      foreach ( $this->indexes as $i ) {
        if ( $i['name'] === $indexName ) {
          $host = $i['host'];
          break;
        }
      }
    }
    return $host;
  }

  // Generic function to run a request to Pinecone.
  function run( $method, $url, $query = null, $json = true, $isAbsoluteUrl = false )
  {
    $headers = "accept: application/json, charset=utf-8\r\ncontent-type: application/json\r\n" . 
      "Api-Key: " . $this->apiKey . "\r\n";
    $body = $query ? json_encode( $query ) : null;
    $url = $isAbsoluteUrl ? $url : "https://controller." . $this->server . ".pinecone.io" . $url;
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
      throw new Exception( $e->getMessage() . " (Pinecone)" );
    }
    return [];
  }

  // Add a new index to Pinecone.
  function add_index( $index, $options ) {
    // Already handled.
    if ( !empty( $index ) ) { return $index; }
    $envId = $options['envId'];
    $name = $options['name'];
    $podType = $options['podType'];
    if ( !$this->init_settings( $envId ) ) {
      return false;
    }
    $dimension = 1536;
    $metric = 'cosine';
    $index = $this->run( 'POST', '/databases', [
      'name' => $name,
      'metric' => $metric,
      'dimension' => $dimension,
      'pod_type' => "{$podType}.x1"
    ], true );
    return $index;
  }

  // Delete an index from Pinecone.
  function delete_index( $success, $options ) {
    // Already handled.
    if ( $success ) { return $success; }
    $envId = $options['envId'];
    $name = $options['name'];
    if ( !$this->init_settings( $envId ) ) { return false; }
    $index = $this->run( 'DELETE', "/databases/{$name}", null, true );
    $success = !empty( $index );
    return $success;
  }

  // List all indexes from Pinecone.
  function list_indexes( $indexes, $options ) {
    // Already handled.
    if ( !empty( $indexes ) ) { return $indexes; }
    $envId = $options['envId'];
    if ( !$this->init_settings( $envId ) ) { return false; }
    $indexesIds = $this->run( 'GET', '/databases', null, true );
    $indexes = [];
    foreach ( $indexesIds as $indexId ) {
      $index = $this->run( 'GET', "/databases/{$indexId}", null, true );
      $indexes[] = [
        'name' => $index['database']['name'],
        'metric' => $index['database']['metric'],
        'dimension' => $index['database']['dimension'],
        'host' => $index['status']['host'],
        'ready' => $index['status']['ready']
      ];
    }   
    return $indexes;
  }

  // List all vectors from Pinecone.
  function list_vectors( $vectors, $options ) {
    if ( !empty( $vectors ) ) { return $vectors; }
    $envId = $options['envId'];
    $index = $options['index'];
    $limit = $options['limit'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }
    $namespace = isset( $options['namespace'] ) ? $options['namespace'] : null;
    // We are using a trick here to get all the vectors. We are querying for a vector that doesn't exist.
    include_once( 'empty_vector.php' );
    $body = [ 'topK' => $limit, 'vector' => VECTOR_SPACE ];
    if ( $this->namespace_support && isset( $namespace ) ) {
      $body['namespace'] = $namespace;
    }
    $res = $this->run( 'POST', "https://{$this->host}/query", $body, true, true );
    $vectors = isset( $res['matches'] ) ? $res['matches'] : [];
    $vectors = array_map( function( $v ) { return $v['id']; }, $vectors );
    return $vectors;
  }

  // Delete vectors from Pinecone.
  function delete_vectors( $success, $options ) {
    // Already handled.
    if ( $success ) { return $success; }
    $envId = $options['envId'];
    $index = $options['index'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }
    $ids = $options['ids'];
    $namespace = $options['namespace'];
    $deleteAll = $options['deleteAll'];
    $body = [
      'ids' => $deleteAll ? null : $ids,
      'deleteAll' => $deleteAll
    ];
    if ( $this->namespace_support ) {
      $body['namespace'] = $namespace;
    }
    // If delete fails, an exception will be thrown. Otherwise, it's successful.
    $success = $this->run( 'POST', "https://{$this->host}/vectors/delete", $body, true, true );
    $success = true;
    return $success;
  }

  // Add a vector to Pinecone.
  function add_vector( $success, $vector, $options ) {
    if ( $success ) { return $success; }
    $envId = $options['envId'];
    $index = $options['index'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }
    $namespace = isset( $options['namespace'] ) ? $options['namespace'] : null;
    $randomId = bin2hex( random_bytes( 32 ) );
    $body = [
      'vectors' => [
        'id' => $randomId,
        'values' => $vector['embedding'],
        'metadata' => [
          'type' => $vector['type'],
          'title' => $vector['title']
        ]
      ]
    ];
    if ( $this->namespace_support && !empty( $namespace ) ) {
      $body['namespace'] = $namespace;
    }
    $res = $this->run( 'POST', "https://{$this->host}/vectors/upsert", $body, true, true );
    $success = isset( $res['upsertedCount'] ) && $res['upsertedCount'] > 0;
    if ( $success ) {
      return $randomId;
    }
    $error = isset( $res['message'] ) ? $res['message'] : 'Unknown error from Pinecone.';
    throw new Exception( $error );
  }

  // Query vectors from Pinecone.
  function query_vectors( $vectors, $vector, $options ) {
    // Output the content of the $vector array to see what's inside in the error_log
    if ( !empty( $vectors ) ) { return $vectors; }
    $envId = $options['envId'];
    $index = $options['index'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }
    $namespace = isset( $options['namespace'] ) ? $options['namespace'] : null;
    $maxSelect = isset( $options['maxSelect'] ) ? $options['maxSelect'] : 10;
    $body = [ 'topK' => $maxSelect, 'vector' => $vector ];
    if ( $this->namespace_support && isset( $namespace ) ) {
      $body['namespace'] = $namespace;
    }
    $res = $this->run( 'POST', "https://{$this->host}/query", $body, true, true );
    $vectors = isset( $res['matches'] ) ? $res['matches'] : [];
    return $vectors;
  }

  // Get a vector from Pinecone.
  function get_vector( $vector, $vectorId, $options ) {
    // Check if the filter has been already handled.
    if ( !empty( $vector ) ) { return $vector; }
    $vectorId = $vectorId;
    $envId = $options['envId'];
    $index = $options['index'];
    if ( !$this->init_settings( $envId, $index ) ) {
      return false;
    }
    $namespace = isset( $options['namespace'] ) ? $options['namespace'] : null;
    $url = "https://{$this->host}/vectors/fetch?ids={$vectorId}";
    if ( !empty( $namespace ) && $this->namespace_support ) {
      $url .= "&namespace={$namespace}";
    } 
    $res = $this->run( 'GET', $url, null, true, true );
    $removeVector = isset( $res['vectors'] ) ? $res['vectors'][$vectorId] : null;
    if ( !empty( $removeVector ) ) {
      return [
        'id' => $vectorId,
        'type' => isset( $removeVector['metadata']['type'] ) ? $removeVector['metadata']['type'] : 'manual',
        'title' => isset( $removeVector['metadata']['title'] ) ? $removeVector['metadata']['title'] : '',
        'values' => isset( $removeVector['values'] ) ? $removeVector['values'] : []
      ];
    }
    return null;
  }
}
