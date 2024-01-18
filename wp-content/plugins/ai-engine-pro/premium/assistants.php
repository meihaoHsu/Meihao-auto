<?php

class MeowPro_MWAI_Assistants {
  private $core = null;
  private $namespace = 'mwai/v1/';

  function __construct( $core ) {
    $this->core = $core;
    add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
    add_action( 'mwai_ai_query_assistant', [ $this, 'query_assistant' ], 10, 2 );
  }

  #region REST API

  function rest_api_init() {
    register_rest_route( $this->namespace, '/openai/assistants/list', [
      'methods' => 'GET',
      'callback' => [ $this, 'rest_assistants_list' ],
    ] );
  }

  function rest_assistants_list( $request ) {
    try {
      $envId = $request->get_param( 'envId' );
      $openAI = new Meow_MWAI_Engines_OpenAI( $this->core, $envId );
      $res = $openAI->run( 'GET', '/assistants', null, null, true, [ 'OpenAI-Beta' => 'assistants=v1' ] );
      $data = $res['data'];
      // TODO: Should handle the "next" page.
      $assistants = array_map( function ( $assistant ) {
        $assistant['createdOn'] = date( 'Y-m-d H:i:s', $assistant['created_at'] );
        unset( $assistant['instructions'] );
        unset( $assistant['file_ids'] );
        unset( $assistant['metadata'] );
        return $assistant;
      }, $data);
      $this->core->update_ai_env( $openAI->get_env_id(), 'assistants', $assistants );
      return new WP_REST_Response([ 'success' => true, 'assistants' => $assistants ], 200 ); 
    }
    catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
  }

  #endregion

  #region Chatbot or Forms Takeover by Assistant
  function query_assistant( $reply, $query ) {
    $envId = $query->envId;
    $assistantId = $query->assistantId;
    // If it's a form, there is no chatId, a new one will be generated, and a new thread will be created.
    $chatId = !empty( $query->chatId ) ? $query->chatId : $this->core->generateRandomId( 10 );
    if ( empty( $envId ) || empty( $assistantId ) ) {
      throw new Exception( 'Assistant requires an envId and an assistantId.' );
    }
    $assistant = $this->core->getAssistant( $envId, $assistantId );
    if ( empty( $assistant ) ) {
      throw new Exception( 'Assistant not found.' );
    }
    $query->setModel( $assistant['model'] );
    $openAI = new Meow_MWAI_Engines_OpenAI( $this->core, $envId );

    // We will use the $chatId to see if there are any previous conversations. If not, we need to create a new thread.
    $chat = $this->core->discussions->get_discussion( $query->botId, $chatId );
    $threadId = $chat->threadId ?? null;
    
    // Create Thread
    if ( empty( $threadId ) ) {

      // TODO: This check for the first message, to see if it's the start sentence.
      // Maybe we should clean all the Query classes and make them more consistent and start sentence should be an attribute.
      // ANYWAY. Assistants don't support 'assistant' message when starting a new thread.
      // $firstMessage = $query->messages[0] ?? null;
      // $startSentence = null;
      // if ( $firstMessage && $firstMessage['role'] === 'assistant' ) {
      //   $startSentence = $firstMessage['content'];
      // }
      $messages = null;
      // if ( !empty( $startSentence ) ) {
      //   $messages = [[ 'role' => 'assistant', 'content' => $startSentence ]];
      // }
      $body = [ 'metadata' => [ 'chatId' => $chatId ] ];
      if ( !empty( $messages ) ) {
        $body['messages'] = $messages;
      }
      $res = $openAI->run( 'POST', '/threads', $body, null, true, [ 'OpenAI-Beta' => 'assistants=v1' ] );
      $threadId = $res['id'];
    }

    // Create Messages
    foreach ( $query->messages as $message ) {
      $body = [ 'role' => $message['role'], 'content' => $message['content'] ];
      if ( $body['role'] !== 'user' ) {
        error_log( "OpenAI does not support 'assistant' or 'system' messages yet." );
        continue;
      }
      if ( !empty( $message['functions'] ) ) {
        $body['functions'] = $message['functions'];
        $body['function_call'] = $message['function_call'];
      }
      $res = $openAI->run( 'POST', "/threads/{$threadId}/messages", $body, null, true, [ 'OpenAI-Beta' => 'assistants=v1' ] );
    }

    // Create Run
    $body = [ 'assistant_id' => $assistantId ];
    $res = $openAI->run( 'POST', "/threads/{$threadId}/runs", $body, null, true, [ 'OpenAI-Beta' => 'assistants=v1' ] );
    $runId = $res['id'];
    $runStatus = $res['status'];

    while ( $runStatus === 'running' || $runStatus === 'queued' || $runStatus === 'in_progress' ) {
      sleep( 0.65 );
      $res = $openAI->run( 'GET', "/threads/{$threadId}/runs/{$runId}", null, null, true, [ 'OpenAI-Beta' => 'assistants=v1' ] );
      $runStatus = $res['status'];
    }

    // Get Messages
    $res = $openAI->run( 'GET', "/threads/{$threadId}/messages", null, null, true, [ 'OpenAI-Beta' => 'assistants=v1' ] );
    $messages = $res['data'];
    $first = $messages[0];
    $content = $first['content'];
    $reply = null;
    foreach ( $content as $block ) {
      if ( $block['type'] === 'text' ) {
        $reply = $block['text']['value'];
        break;
      }
    }
    if ( !$reply) {
      throw new Exception( "No text reply from the assistant." );
    }

    // TODO: In fact, this threadId should probably be in the query.
    // The Discussions Module will also use that threadId. Currently, it's getting it from the $params.
    $query->setThreadId( $threadId );
    $reply = new Meow_MWAI_Reply( $query );
    $reply->setChoices( $content );
    $reply->setType( 'assistant' );
    $promptTokens = Meow_MWAI_Core::estimateTokens( $query->messages, $query->getModel() );
    $completionTokens = Meow_MWAI_Core::estimateTokens( $reply->result, $query->getModel() );
    $usage = $this->core->recordTokensUsage( $query->model, $promptTokens, $completionTokens );
    $reply->setUsage( $usage );
    return $reply;
  }
  #endregion
}