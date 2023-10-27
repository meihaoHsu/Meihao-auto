<?php

class MeowPro_MWAI_Addons_Pinecone {
  private $core = null;
  private $apiKey = null;
  private $server = null;
  private $host = null;
  private $defaultIndex = null;
  private $supportNS = true;
  private $options = [];

  function __construct() {
    global $mwai_core;
    $this->core = $mwai_core;
    $this->options = $this->core->get_option( 'pinecone' );
    $this->server = $this->options['server'];
    $this->apiKey = $this->options['apikey'];
    $this->defaultIndex = $this->options['index'];
    $this->supportNS = $this->server !== 'gcp-starter';
    $this->host = $this->pinecone_get_host( $this->defaultIndex );

    add_filter( 'mwai_embeddings_add_index', array( $this, 'add_index' ), 10, 3 );
    add_filter( 'mwai_embeddings_list_indexes', array( $this, 'list_indexes' ), 10, 1 );
    add_filter( 'mwai_embeddings_delete_index', array( $this, 'delete_index' ), 10, 2 );
    add_filter( 'mwai_embeddings_add_vector', [ $this, 'add_vector' ], 10, 3 );
    add_filter( 'mwai_embeddings_get_vector', [ $this, 'get_vector' ], 10, 4 );
    add_filter( 'mwai_embeddings_query_vectors', [ $this, 'query_vectors' ], 10, 4 );
    add_filter( 'mwai_embeddings_delete_vectors', [ $this, 'delete_vectors' ], 10, 4 );

    // We don't have a way to delete everything related to a namespace yet, but it works like that:
    //$this->delete_vectors( null, null, true, 'nekod' );
  }

  function pinecone_get_host( $indexName ) {
    $host = null;
    if ( !empty( $this->options['indexes'] ) ) {
      foreach ( $this->options['indexes'] as $i ) {
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
  function add_index( $index, $name, $params ) {
    $podType = $params['podType'];
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
  function delete_index( $success, $name ) {
    $index = $this->run( 'DELETE', "/databases/{$name}", null, true );
    $success = !empty( $index );
    return $success;
  }

  // List all indexes from Pinecone.
  function list_indexes( $indexes ) {
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

  // Delete vectors from Pinecone.
  function delete_vectors( $success, $ids, $deleteAll = false, $namespace = null ) {
    $body = [
      'ids' => $deleteAll ? null : $ids,
      'deleteAll' => $deleteAll
    ];
    if ( $this->supportNS ) {
      $body['namespace'] = $namespace;
    }
    // If delete fails, an exception will be thrown. Otherwise, it's successful.
    $success = $this->run( 'POST', "https://{$this->host}/vectors/delete", $body, true, true );
    $success = true;
    return $success;
  }

  // Add a vector to Pinecone.
  function add_vector( $success, $vector ) {
    $dbNS = isset( $vector['dbNS'] ) ? $vector['dbNS'] : null;
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
    if ( $this->supportNS && !empty( $dbNS ) ) {
      $body['namespace'] = $dbNS;
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
  function query_vectors( $vectors, $vector, $indexName = null, $namespace = null ) {
    $indexName = !empty( $indexName ) ? $indexName : $this->defaultIndex;
    $host = $this->pinecone_get_host( $indexName );
    $body = [
      'topK' => 10,
      'vector' => $vector,
    ];
    if ( $this->supportNS ) {
      $body['namespace'] = $namespace;
    }
    $res = $this->run( 'POST', "https://{$host}/query", $body, true, true );
    $vectors = isset( $res['matches'] ) ? $res['matches'] : [];
    return $vectors;
  }

  // Get a vector from Pinecone.
  function get_vector( $vector, $vectorId, $indexName = null, $namespace = null ) {
    // Check if the filter has been already handled.
    if ( !empty( $vector ) ) { return $vector; }
    $vectorId = (int)$vectorId;
    $indexName = !empty( $indexName ) ? $indexName : $this->defaultIndex;
    $host = $this->pinecone_get_host( $indexName );
    $url = "https://{$host}/vectors/fetch?ids={$vectorId}&namespace={$namespace}";
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
