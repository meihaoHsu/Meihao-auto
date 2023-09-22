<?php

const MWAI_DEFAULT_NAMESPACE = 'mwai';

class MeowPro_MWAI_Embeddings {
  private $core = null;
  private $wpdb = null;
  private $db_check = false;
  private $table_vectors = null;
  private $namespace = 'mwai/v1/';
  private $defaultIndex = null;
  private $defaultNamespace = MWAI_DEFAULT_NAMESPACE;
  private $options = [];
  private $settings = [];
  private $syncPosts = false;
  private $syncPostTypes = [];

  function __construct() {
    global $wpdb, $mwai_core;
    $this->core = $mwai_core;
    $this->wpdb = $wpdb;
    $this->options = $this->core->get_option( 'pinecone' );
    $this->defaultIndex = $this->options['index'];
    if ( $this->options['namespaces'] ) {
      $this->defaultNamespace = $this->options['namespaces'][0];
    }
    $this->table_vectors = $wpdb->prefix . 'mwai_vectors';
    $this->settings = $this->core->get_option( 'embeddings' );
    $this->syncPosts = isset( $this->settings['syncPosts'] ) ? $this->settings['syncPosts'] : false;
    $this->syncPostTypes = isset( $this->settings['syncPostTypes'] ) ? $this->settings['syncPostTypes'] : [];

    // AI Engine Filters
    add_filter( 'mwai_context_search', [ $this, 'context_search' ], 10, 3 );
    new MeowPro_MWAI_Addons_Pinecone();
    
    // WordPress Filters
    add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
    add_action( 'save_post', array( $this, 'onSavePost' ), 10, 3 );
    if ( $this->syncPosts ) {
      add_action( 'wp_trash_post', array( $this, 'onDeletePost' ) );
    }
  }

  #region REST API

  function rest_api_init() {
		try {
      register_rest_route( $this->namespace, '/indexes/add', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_indexes_add' )
			));
      register_rest_route( $this->namespace, '/indexes/delete', array(
        'methods' => 'POST',
        'permission_callback' => array( $this->core, 'can_access_settings' ),
        'callback' => array( $this, 'rest_indexes_delete' )
      ));
			register_rest_route( $this->namespace, '/indexes/list', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_indexes_list' )
			));

      // Vectors
      register_rest_route( $this->namespace, '/vectors/list', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_vectors_list' ),
			) );
			register_rest_route( $this->namespace, '/vectors/add', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_vectors_add' ),
			) );
			register_rest_route( $this->namespace, '/vectors/ref', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_vectors_by_ref' ),
			) );
			register_rest_route( $this->namespace, '/vectors/update', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_vectors_update' ),
			) );
			register_rest_route( $this->namespace, '/vectors/delete', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_vectors_delete' ),
			) );
      
    }
    catch ( Exception $e ) {
      var_dump( $e );
    }
  }

  function rest_vectors_list( $request ) {
		try {
			$params = $request->get_json_params();
			$page = isset( $params['page'] ) ? $params['page'] : null;
			$limit = isset( $params['limit'] ) ? $params['limit'] : null;
      $offset = (!!$page && !!$limit) ? ( $page - 1 ) * $limit : 0;
			$filters = isset( $params['filters'] ) ? $params['filters'] : null;
			$sort = isset( $params['sort'] ) ? $params['sort'] : null;
      $vectors = $this->searchVectors( $offset, $limit, $filters, $sort );
			return new WP_REST_Response([ 
        'success' => true,
        'total' => $vectors['total'],
        'vectors' => $vectors['rows']
      ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function rest_vectors_add( $request ) {
		try {
			$params = $request->get_json_params();
			$vector = $params['vector'];
      $success = $this->vectors_add( $vector );
			return new WP_REST_Response([ 'success' => $success, 'vector' => $vector ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function rest_vectors_by_ref( $request ) {
		try {
			$params = $request->get_json_params();
			$refId = $params['refId'];
      $dbIndex = !empty( $params['dbIndex'] ) ? $params['dbIndex'] : $this->defaultIndex;
      $dbNS = !empty( $params['dbNS'] ) ? $params['dbNS'] : $this->defaultNamespace;
      $vectors = $this->getByRef( $refId, $dbIndex, $dbNS );
			return new WP_REST_Response([ 'success' => true, 'vectors' => $vectors ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function rest_vectors_update( $request ) {
		try {
			$params = $request->get_json_params();
			$vector = $params['vector'];
      $success = $this->updateVector( $vector );
			return new WP_REST_Response([ 'success' => $success, 'vector' => $vector ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function rest_vectors_delete( $request ) {
		try {
			$params = $request->get_json_params();
			$localIds = $params['ids'];
      $success = $this->vectors_delete( $localIds );
			return new WP_REST_Response([ 'success' => $success ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

  function rest_indexes_add( $request ) {
    try {
      $params = $request->get_json_params();
      $name = $params['name'];
      $index = apply_filters( 'mwai_embeddings_add_index', [], $name, $params );
      if ( !empty( $index ) ) {
        return $this->rest_indexes_list();
      }
      return new WP_REST_Response([ 'success' => false, 'message' => "Could not create index." ], 200 );
    }
    catch ( Exception $e ) {
      return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 200 );
    }
  }

  function rest_indexes_delete( $request ) {
    try {
      $params = $request->get_json_params();
      $name = $params['name'];
      $success = apply_filters( 'mwai_embeddings_delete_index', [], $name );
      if ( $success ) {
        return $this->rest_indexes_list();
      }
      return new WP_REST_Response([ 'success' => false, 'message' => "Could not delete index." ], 200 );
    }
    catch ( Exception $e ) {
      return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 200 );
    }
  }

  function rest_indexes_list() {
    try {
      $indexes = apply_filters( 'mwai_embeddings_list_indexes', [] );
      return new WP_REST_Response([ 'success' => true, 'indexes' => $indexes ], 200 );
    }
    catch ( Exception $e ) {
      return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 200 );
    }
  }
  #endregion
  
  #region Events (WP & AI Engine)

  function onSavePost( $postId, $post, $update ) {
    if ( !in_array( $post->post_type, $this->syncPostTypes ) ) {
      return;
    }
    if ( $post->post_status !== 'publish' ) {
      return;
    }
    if ( !$this->checkDB() ) { return false; }
    $vector = $this->getVectorByRefId( $postId );
    if ( !$vector ) { 
      if ( $this->syncPosts ) {
        $cleanPost = $this->core->getCleanPost( $post );
        $vector = [
          'type' => 'postId',
          'title' => $cleanPost['title'],
          'refId' => $postId,
          'dbIndex' => $this->defaultIndex,
        ];
        $this->vectors_add( $vector, 'pending' );
      }
      return;
    }
    if ( empty( $vector->content ) ) {
      // This is a bit stupid; after a post is created, the hook onSavePost is called another time.
      // This would change the status of pending into outdated, which is not what we want.
      return;
    }
    $cleanPost = $this->core->getCleanPost( $post );
    if ( $cleanPost['checksum'] === $vector['refChecksum'] ) { return; }
    $this->wpdb->update( $this->table_vectors, [ 'status' => 'outdated' ],
      [ 'refId' => $postId, 'type' => 'postId' ], [ '%s' ], [ '%d', '%s' ]
    );
  }

  function onDeletePost( $postId ) {
    if ( !$this->checkDB() ) { return false; }
    $vectorIds = $this->wpdb->get_col( $this->wpdb->prepare(
      "SELECT id FROM $this->table_vectors WHERE refId = %d AND type = 'postId'", $postId
    ) );
    if ( !$vectorIds ) { return; }
    $this->vectors_delete( $vectorIds );
  }

  function context_search( $context, $query, $options = [] ) {
    $index = !empty( $options['embeddingsIndex'] ) ? $options['embeddingsIndex'] : null;
    $namespace = !empty( $options['embeddingsNamespace'] ) ? $options['embeddingsNamespace'] : $this->defaultNamespace;

    // Context already provided? Or no embeddings index? Let's return.
    if ( !$index || !empty( $context ) ) {
      return $context;
    }

    $queryEmbed = new Meow_MWAI_Query_Embed( $query );
    $reply = $this->core->ai->run( $queryEmbed );
    if ( empty( $reply->result ) ) {
      return null;
    }
    $embeds = $this->queryVectorDB( $reply->result, $index, $namespace );
    if ( empty( $embeds ) ) {
      return null;
    }
    $minScore = empty( $this->settings['minScore'] ) ? 75 : (float)$this->settings['minScore'];
    $maxSelect = empty( $this->settings['maxSelect'] ) ? 1 : (int)$this->settings['maxSelect'];
    $embeds = array_slice( $embeds, 0, $maxSelect );

    // Prepare the context
    $context = [];
    $context["content"] = "";
    $context["type"] = "embeddings";
    $context["embeddingIds"] = []; 
    foreach ( $embeds as $embed ) {
      if ( ( $embed['score'] * 100 ) < $minScore ) {
        break;
      }
      $embedId = $embed['id'];
      $data = $this->getVectorByRemoteId( $embedId, $index, $namespace );

      // If the vector is not available locally, we try to get it from the Vector DB.
      if ( empty( $data ) ) {
        $remoteVector = $this->getRemoteVectorMetadata( $embedId, $index, $namespace );
        if ( empty( $remoteVector ) ) {
          error_log("A vector was returned by the Vector DB, but it is not available in the local DB and we could not retrieve it more information about it from the Vector DB (ID {$embedId}).");
        }
        $type = isset( $remoteVector['type'] ) ? $remoteVector['type'] : 'manual';
        $title = isset( $remoteVector['title'] ) ? $remoteVector['title'] : 'N/A';
        $content = isset( $remoteVector['content'] ) ? $remoteVector['content'] : '';
        $isOk = !empty( $content );
        $this->vectors_add( [
          'type' => $type,
          'title' => $title,
          'content' => $content,
          'dbId' => $embedId,
          'dbIndex' => $index,
          'dbNS' => $namespace,
        ], $isOk ? 'ok' : 'orphan' );
        // If there is no content, it is marked as 'orphan' locally, and we don't add it in the context for this query.
        if ( !$isOk ) {
          continue;
        }
      }

      $context["content"] .= $data['content'] . "\n";
      $context["embeddings"][] = [ 
        'id' => $embedId,
        'type' => $data['type'],
        'title' => $data['title'],
        'ref' => $data['refId'],
        'score' => (float)$embed['score'],
      ];
    }
    
    return empty( $context["content"] ) ? null : $context;
  }
  #endregion

  #region DB Queries

  function queryVectorDB( $searchVectors, $index = null, $namespace = null ) {
    $index = $index ? $index : $this->defaultIndex;
    $namespace = $namespace ? $namespace : $this->defaultNamespace;
    $vectors = apply_filters( 'mwai_embeddings_query_vectors', [], $searchVectors, $index, $namespace );
    return $vectors;
  }

  function vectors_delete( $localIds ) {
    if ( !$this->checkDB() ) { return false; }

    $dbNamespaces = [];
    foreach ( $localIds as $id ) {
      $vector = $this->getVector( $id );
      if ( empty( $vector ) ) { continue; }
      // Delete local vectors
      $this->wpdb->delete( $this->table_vectors, [ 'id' => $id ], [ '%d' ] );
      // Gather the vectors per namespaces
      $ns = $vector['dbNS'];
      if ( !isset( $dbNamespaces[ $ns ] ) ) {
        $dbNamespaces[ $ns ] = [];
      }
      $dbNamespaces[ $ns ][] = $vector['dbId'];
    }
    // Delete remote vectors
    foreach ( $dbNamespaces as $ns => $dbIds ) {
      apply_filters( 'mwai_embeddings_delete_vectors', [], $dbIds, false, $ns );
    }
    return true;
  }

  // function vectors_delete_all( $success, $index, $syncPineCone = true ) {
  //   if ( $success ) { return $success; }
  //   if ( !$this->checkDB() ) { return false; }
  //   if ( $syncPineCone ) { $this->pinecode_delete( null, true ); }
  //   $this->wpdb->delete( $this->table_vectors, [ 'dbIndex' => $index ], array( '%s' ) );
  //   return true;
  // }

  function vectors_add( $vector = [], $status = 'processing' ) {
    if ( !$this->checkDB() ) { return false; }

    // If it doesn't have content, it's basically an empty vector
    // that needs to be processed later, through the UI.
    $hasContent = isset( $vector['content'] );

    if ( $hasContent && strlen( $vector['content'] ) > 65535 ) {
      throw new Exception( 'The content of the embedding is too long (max 65535 characters).' );
    }

    $success = $this->wpdb->insert( $this->table_vectors, 
      [
        'id' => null,
        'type' => $vector['type'],
        'title' => $vector['title'],
        'content' => $hasContent ? $vector['content'] : '',
        'refId' => !empty( $vector['refId'] ) ? $vector['refId'] : null,
        'refChecksum' => !empty( $vector['refChecksum'] ) ? $vector['refChecksum'] : null,
        'dbId' => isset( $vector['dbId'] ) ? $vector['dbId'] : null,
        'dbIndex' => !empty( $vector['dbIndex'] ) ? $vector['dbIndex'] : $this->defaultIndex,
        'dbNS' => !empty( $vector['dbNS'] ) ? $vector['dbNS'] : $this->defaultNamespace,
        'status' => $status,
        'updated' => date( 'Y-m-d H:i:s' ),
        'created' => date( 'Y-m-d H:i:s' )
      ],
      array( '%s', '%s', '%s', '%s', '%s', '%s' )
    );

    if ( !$success ) {
      $error = $this->wpdb->last_error;
      throw new Exception( $error );
    }

    // Check for content, if there is, create the embedding.
    if ( !$hasContent ) { return true; }
    $vector['id'] = $this->wpdb->insert_id;
    $queryEmbed = new Meow_MWAI_Query_Embed( $vector['content'] );
    $queryEmbed->setEnv('admin-tools');
    $reply = $this->core->ai->run( $queryEmbed );
    $vector['embedding'] = $reply->result;
    $dbId = apply_filters( 'mwai_embeddings_add_vector', false, $vector );
    if ( $dbId ) {
      $this->wpdb->update( $this->table_vectors, [ 'dbId' => $dbId, 'status' => "ok" ],
        [ 'id' => $vector['id'] ], array( '%s' ), [ '%d' ]
      );
    }
    else {
      $this->wpdb->update( $this->table_vectors, [ 'dbId' => null, 'status' => "error" ],
        [ 'id' => $vector['id'] ], array( '%s' ), [ '%d' ]
      );
    }
    return true;
  }

  function getByRef( $refId, $dbIndex = null, $dbNS = null ) {
    if ( !$this->checkDB() ) { return false; }

    $query = "SELECT * FROM {$this->table_vectors}";
    $where = array();
    $where[] = "refId = '" . esc_sql( $refId ) . "'";

    if ( $dbIndex ) {
      $where[] = "dbIndex = '" . esc_sql( $dbIndex ) . "'";
    }
    if ( $dbNS ) {
      $where[] = "dbNS = '" . $dbNS . "'";
    }
    $query .= " WHERE " . implode( " AND ", $where );
    $vectors = $this->wpdb->get_results( $query, ARRAY_A );
    return $vectors;
  }

  function updateVector( $vector = [] ) {
    if ( !$this->checkDB() ) { return false; }
    if ( empty( $vector['id'] ) ) { throw new Exception( "Missing ID" ); }
    $originalVector = $this->getVector( $vector['id'] );
    if ( !$originalVector ) { throw new Exception( "Vector not found" ); }
    $newContent = $originalVector['content'] !== $vector['content'];
    $dbNS = !empty( $vector['dbNS'] ) ? $vector['dbNS'] : $this->defaultNamespace;

    // Update the vector
    $this->wpdb->update( $this->table_vectors, [
        'type' => $vector['type'],
        'title' => $vector['title'],
        'content' => $vector['content'],
        'refId' => !empty( $vector['refId'] ) ? $vector['refId'] : null,
        'refChecksum' => !empty( $vector['refChecksum'] ) ? $vector['refChecksum'] : null,
        'dbIndex' => $vector['dbIndex'],
        'status' => $newContent ? "processing" : "ok",
        'updated' => date( 'Y-m-d H:i:s' )
      ],
      [ 'id' => $vector['id'] ],
      [ '%s', '%s', '%s', '%s', '%s' ],
      [ '%d' ]
    );

    if ( $originalVector['content'] !== $vector['content'] ) {
      // Delete the original vector
      apply_filters( 'mwai_embeddings_delete_vectors', [], $originalVector['dbId'], false, $dbNS );
      // Create embedding
      $queryEmbed = new Meow_MWAI_Query_Embed( $vector['content'] );
      $queryEmbed->setEnv('admin-tools');
      $reply = $this->core->ai->run( $queryEmbed );
      $vector['embedding'] = $reply->result;
      $dbId = apply_filters( 'mwai_embeddings_add_vector', false, $vector, $dbNS );
      if ( $dbId ) {
        $this->wpdb->update( $this->table_vectors,
          [ 'dbId' => $dbId, 'status' => "ok", 'updated' => date( 'Y-m-d H:i:s' ) ],
          [ 'id' => $vector['id'] ], [ '%s', '%s' ], [ '%d' ]
        );
      }
      else {
        $this->wpdb->update( $this->table_vectors,
          [ 'dbId' => null, 'status' => "error", 'updated' => date( 'Y-m-d H:i:s' ) ],
          [ 'id' => $vector['id'] ], [ '%s', '%s' ], [ '%d' ]
        );
      }
    }

    return true;
  }

  function getVector( $id ) {
    if ( !$this->checkDB() ) {
      return null;
    }
    $vector = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM $this->table_vectors WHERE id = %d", $id ), ARRAY_A );
    return $vector;
  }

  function getVectorByRemoteId( $remoteId, $indexName = null, $namespace = null ) {
    if ( !$this->checkDB() ) {
      return null;
    }
    $indexName = $indexName ? $indexName : $this->defaultIndex;
    $namespace = $namespace ? $namespace : $this->defaultNamespace;
    $vector = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM $this->table_vectors WHERE dbId = %s AND dbIndex = %s AND dbNS = %s", $remoteId, $indexName, $namespace ), ARRAY_A );
    return $vector;
  }

  function getRemoteVectorMetadata( $vectorId, $indexName, $namespace ) {
    $vector = apply_filters( 'mwai_embeddings_get_vector', null, $vectorId, $indexName, $namespace );
    return $vector;
  }

  function getVectorByRefId( $refId ) {
    if ( !$this->checkDB() ) {
      return null;
    }
    $vector = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM $this->table_vectors WHERE refId = %d", $refId ), ARRAY_A );
    return $vector;
  }

  function searchVectors( $offset = 0, $limit = null, $filters = null, $sort = null ) {
    if ( !$this->checkDB() ) { return []; }

    $filters = !empty( $filters ) ? $filters : [];
    $dbIndex = !empty( $filters['dbIndex'] ) ? $filters['dbIndex'] : $this->defaultIndex;
    $dbNS = !empty( $filters['dbNS'] ) ? $filters['dbNS'] : $this->defaultNamespace;

    // Is AI Search
    $isAiSearch = !empty( $filters['search'] );
    $matchedVectors = [];
    if ( $isAiSearch ) {
      $query = $filters['search'];
      $queryEmbed = new Meow_MWAI_Query_Embed( $query );
      $queryEmbed->setEnv('admin-tools');
      //$queryEmbed->injectParams( $params );
			$reply = $this->core->ai->run( $queryEmbed );
      $matchedVectors = $this->queryVectorDB( $reply->result, $dbIndex, $dbNS );
      if ( empty( $matchedVectors ) ) {
        return [];
      }
    }

    $offset = !empty( $offset ) ? intval( $offset ) : 0;
    $limit = !empty( $limit ) ? intval( $limit ) : 100;
    $sort = !empty( $sort ) ? $sort : [ "accessor" => "created", "by" => "desc" ];
    $query = "SELECT * FROM $this->table_vectors";

    // Filters
    $where = array();
    if ( isset( $filters['type'] ) ) {
      $where[] = "type = '" . esc_sql( $filters['type'] ) . "'";
    }
    if ( isset( $filters['dbIndex'] ) ) {
      $where[] = "dbIndex = '" . esc_sql( $filters['dbIndex'] ) . "'";
    }
    if ( isset( $filters['dbNS'] ) ) {
      $where[] = "dbNS = '" . esc_sql( $filters['dbNS'] ) . "'";
    }
    // $dbIds is an array of strings
    $dbIds = [];
    $rawDbIds = [];
    if ( $isAiSearch ) {
      foreach ( $matchedVectors as $vector ) {
        $dbIds[] = "'" . $vector['id'] . "'";
        $rawDbIds[] = $vector['id'];
      }
      if (!empty($dbIds)) {
          $where[] = "dbId IN (" . implode( ",", $dbIds ) . ")";
      }
    }
    if ( count( $where ) > 0 ) {
      $query .= " WHERE " . implode( " AND ", $where );
    }

    // Count based on this query
    $vectors['total'] = $this->wpdb->get_var( "SELECT COUNT(*) FROM ($query) AS t" );

    // Order by
    if ( !$isAiSearch ) {
      $query .= " ORDER BY " . esc_sql( $sort['accessor'] ) . " " . esc_sql( $sort['by'] );
    }

    // Limits
    if ( !$isAiSearch && $limit > 0 ) {
      $query .= " LIMIT $offset, $limit";
    }

    $vectors['rows'] = $this->wpdb->get_results( $query, ARRAY_A );

    // Consolidate results
    foreach ( $vectors['rows'] as $key => &$vectorRow ) {
      if ( $vectorRow['type'] === 'postId' ) {
        // Get the Post Type
        $vectorRow['subType'] = get_post_type( $vectorRow['refId'] );
      }
    }

    if ( $isAiSearch ) {
      foreach ( $vectors['rows'] as &$vectorRow ) {
        $dbId = $vectorRow['dbId'];
        $queryVector = null;
        foreach ( $matchedVectors as $vector ) {
          if ( (string)$vector['id'] === (string)$dbId ) {
            $queryVector = $vector;
            break;
          }
        }
        if ( !empty( $queryVector ) ) {
          $vectorRow['score'] = $queryVector['score'];
        }
      }
      unset( $vectorRow );

      // If the count of the result vectors is less than the $ids, then we need to add the missing ones
      if ( count( $vectors['rows'] ) < count( $rawDbIds ) ) {
        $missingIds = array_diff( $rawDbIds, array_column( $vectors['rows'], 'dbId' ) );
        foreach ( $missingIds as $missingId ) {
          $vector = [
            'type' => 'manual',
            'title' => "Vector #$missingId",
            'dbId' => $missingId,
            'dbIndex' => $this->defaultIndex,
          ];
          $this->vectors_add( $vector, 'orphan' );
        }
      }

    }

    return $vectors;
  }

  #endregion

  #region DB Setup

  function create_db() {
    $charset_collate = $this->wpdb->get_charset_collate();
    $sqlVectors = "CREATE TABLE $this->table_vectors (
      id BIGINT(20) NOT NULL AUTO_INCREMENT,
      type VARCHAR(32) NULL,
      title VARCHAR(255) NULL,
      content TEXT NULL,
      behavior VARCHAR(32) DEFAULT 'context' NOT NULL,
      status VARCHAR(32) NULL,
      dbId VARCHAR(64) NULL,
      dbIndex VARCHAR(64) NOT NULL,
      dbNS VARCHAR(64) NOT NULL,
      refId BIGINT(20) NULL,
      refChecksum VARCHAR(64) NULL,
      created DATETIME NOT NULL,
      updated DATETIME NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sqlVectors );
  }

  function checkDB() {
    if ( $this->db_check ) {
      return true;
    }
    $this->db_check = !( strtolower( 
      $this->wpdb->get_var( "SHOW TABLES LIKE '$this->table_vectors'" ) ) != strtolower( $this->table_vectors )
    );
    if ( !$this->db_check ) {
      $this->create_db();
      $this->db_check = !( strtolower( 
        $this->wpdb->get_var( "SHOW TABLES LIKE '$this->table_vectors'" ) ) != strtolower( $this->table_vectors )
      );
    }

    // LATER: REMOVE THIS AFTER OCTOBER 2023
    // Make sure the column "dbId" exists in the $this->table_vectors table
    $this->db_check = $this->db_check && $this->wpdb->get_var( "SHOW COLUMNS FROM $this->table_vectors LIKE 'dbId'" );
    if ( !$this->db_check ) {
      $this->wpdb->query( "ALTER TABLE $this->table_vectors ADD COLUMN dbId VARCHAR(64) NULL" );
      $this->wpdb->query( "UPDATE $this->table_vectors SET dbId = id" );
    }

    return $this->db_check;
  }

  #endregion
}
