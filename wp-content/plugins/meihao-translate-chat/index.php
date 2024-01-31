<?php
/*
 * Plugin Name: Meihao Translate Chat
 * Description: Meihao Translate Chat
 * Version: 1.0.0
 * Author: Han Hsu
 * Text Domain: Meihao-Translate-Chat
 * Domain Path: /languages
 */
! defined( 'Meihao_TRANSLATE_CHAT_DIR' ) && define( 'Meihao_TRANSLATE_CHAT_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'Meihao_TRANSLATE_CHAT_URL' ) && define( 'Meihao_TRANSLATE_CHAT_URL', plugin_dir_url( __FILE__ ) );

class Meihao_translate_chat{
    private static $instance;
    protected $_panel;
    public function __construct()
    {
        $this->hooks();
    }
    private function hooks()
    {
        add_action('admin_menu', [$this, 'menuBuild']);
        add_shortcode( 'meihao-translate-chat-frontend', [ $this, 'meihao_translate_chat_frontend' ] );

        add_action('wp_enqueue_scripts', [$this, 'load_meihao_css_js_files'], 110);
        add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts_callback'] );

        add_action( 'wp_ajax_ajax_translate_text', [$this,'ajax_translate_text'] ); // 210922 抽獎動作
        add_action( 'wp_ajax_nopriv_ajax_translate_text', [$this,'ajax_translate_text'] ); // 210922 抽獎動作
        add_action( 'init', array( $this, 'init' ), 20 );
    }
    public function init(){
        session_start([
            'cookie_lifetime' => 86400,
        ]);
    }
    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function menuBuild(){
        add_submenu_page('tools.php', '即時文字翻譯設定', '即時文字翻譯設定', 'manage_options', 'meihao-translate-chat', [$this,'meihao_translate_chat']);

    }

    public function load_meihao_css_js_files(){
        global $post;
        if(isset($post->post_content)){
            if( has_shortcode($post->post_content,'meihao-translate-chat-frontend')){
                wp_enqueue_script("meihao-translate-js", Meihao_TRANSLATE_CHAT_URL. 'assets/js/frontend.js', ['jquery'], false, true);
                wp_enqueue_style("meihao-translate-css", Meihao_TRANSLATE_CHAT_URL. 'assets/css/frontend.css', array(), '1.0');
            }
        }
    }
    public function admin_enqueue_scripts_callback(){
        //Add the Select2 CSS file
        wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');
        //Add the Select2 JavaScript file
        wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', 'jquery', '4.1.0-rc.0');

    }

    public function meihao_translate_chat(){
        include_once (Meihao_TRANSLATE_CHAT_DIR.'admin-setting.php');
    }

    public function meihao_translate_chat_frontend(){
        include_once (Meihao_TRANSLATE_CHAT_DIR.'frontend.php');
    }

    public function ajax_translate_text(){
        $options = get_option( 'mtc-option' );
        $prompt = $options['prompt']; $openAIKey = $options['openAIKey']; $Language = $options['translate_languages'];
        $inputLanguage = $_POST['inputLanguage']; $outputLanguage = $_POST['outputLanguage']; $input_text = $_POST['inputText']; // 輸入的文字

        $url = 'https://api.openai.com/v1/chat/completions'; //聊天接口

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openAIKey,
        );

        $prompt = sprintf($prompt,$input_text,$Language[$inputLanguage],$Language[$outputLanguage]);

        $data = array(
            'model' => 'gpt-3.5-turbo', //聊天模型
            'temperature' => 0.5,
            'max_tokens' => 3000,
            'messages' => [
                ["role" => "user", "content" => $prompt],
            ]

        );


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);

        if  ($response !== false) {
            $result = json_decode($response, true);
            error_log(print_r('$result',1));
            error_log(print_r($result,1));
            if (isset($result['choices'][0]['message'])) {
                error_log(print_r('123',1));
                $output_text = $result['choices'][0]['message']['content'];
                error_log(print_r('$output_text',1));
                error_log(print_r($output_text,1));
            }
        }




        if(isset($_SESSION['trans_logs']) && $_SESSION['trans_logs'] !=''){
            $trans_logs = unserialize($_SESSION['trans_logs']);
        }
        $log_html = "<span>$input_text</span><i class='dashicons dashicons-arrow-down-alt'></i><span>".trim($output_text)."</span>";
        $trans_logs[]=$log_html;
        $_SESSION['trans_logs']= serialize($trans_logs);



        $output = ['result'=>'1','text'=>trim($output_text),'log_html'=>"<p class='translate-log'>$log_html</p>"];

        echo json_encode($output);
        die();
    }
}

add_action('plugins_loaded', array('Meihao_translate_chat', 'getInstance'));
?>