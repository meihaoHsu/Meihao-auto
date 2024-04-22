<?php
if(!class_exists('MTT_Plugin')){

    class MTT_Plugin{

        public static $_instance=NULL;

        function __construct(){
            add_shortcode('MeihaoTaiwanTrainsFrontend', array($this, 'meihao_taiwan_trains_frontend'));
            add_shortcode('MeihaoTaiwanFlightsFrontend', array($this, 'meihao_taiwan_flights_frontend'));
            add_action('wp_enqueue_scripts', [$this, 'load_css_js_to_shortcode_page'], 110);

            add_action('wp_ajax_search_TDX_dailyTrain_API',[$this, 'ajax_search_TDX_dailyTrain_API']);
            add_action('wp_ajax_nopriv_search_TDX_dailyTrain_API',[$this, 'ajax_search_TDX_dailyTrain_API']);
            add_action('wp_ajax_search_TDX_trainsTimeTable_API',[$this, 'ajax_search_TDX_trainsTimeTable_API']);
            add_action('wp_ajax_nopriv_search_TDX_trainsTimeTable_API',[$this, 'ajax_search_TDX_trainsTimeTable_API']);
        }

        /** Admin Hook */


        /** Frontend Hook */
        //ShortCode 載入模板
        function meihao_taiwan_trains_frontend(){
            ob_start();
            include MTT_DIR.'/templates/Taiwan_Trains_page.php';

            $shortcodeTemplate = ob_get_clean();
            return $shortcodeTemplate;
        }
        function meihao_taiwan_flights_frontend(){
            ob_start();
            include MTT_DIR.'/templates/Taiwan_Flights_page.php';

            $shortcodeTemplate = ob_get_clean();
            return $shortcodeTemplate;
        }
        //使用ShortCode 頁面載入 css & js
        function load_css_js_to_shortcode_page() {
            global $post;
            if ( is_a( $post, 'WP_Post' ) &&( has_shortcode( $post->post_content, 'MeihaoTaiwanTrainsFrontend') || has_shortcode( $post->post_content, 'MeihaoTaiwanFlightsFrontend') ) ) {
                wp_enqueue_style( 'dashicons' );
                wp_enqueue_style("meihao-taiwan-trains-css", MTT_URL.'assets/css/meihao-taiwan-trains.css');
                wp_enqueue_style("jquery-ui-css", '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
                wp_enqueue_script("jquery-ui-js", '//code.jquery.com/ui/1.13.2/jquery-ui.js', ['jquery'], false, true);
                if(has_shortcode( $post->post_content, 'MeihaoTaiwanTrainsFrontend')){
                    wp_register_script("meihao-taiwan-trains-js", MTT_URL.'assets/js/meihao-taiwan-trains.js', ['jquery','wp-i18n'], false, true);
                    wp_enqueue_script('meihao-taiwan-trains-js');
                    wp_set_script_translations( 'meihao-taiwan-trains-js', 'Meihao-Taiwan-Trains',MTT_DIR. '/languages/' );
                }
                if(has_shortcode( $post->post_content, 'MeihaoTaiwanFlightsFrontend')){
                    wp_register_script("meihao-taiwan-flights-js", MTT_URL.'assets/js/meihao-taiwan-flights.js', ['jquery','wp-i18n'], false, true);
                    wp_enqueue_script('meihao-taiwan-flights-js');
                    wp_set_script_translations( 'meihao-taiwan-flights-js', 'Meihao-Taiwan-Trains',MTT_DIR. '/languages/' );
                }
                load_plugin_textdomain( 'Meihao-Taiwan-Trains',false, MTT_DIR . '/languages/' );
            }
        }


        //Ajax搜尋結果
        function ajax_search_TDX_dailyTrain_API(){
            $ajaxResult = ['result'=>0];
            if ( !isset($_REQUEST['start']) && !isset($_REQUEST['end']) && !isset($_REQUEST['date']) ) {
                echo json_encode($ajaxResult);
            }else{
                $ajaxResult['result']=1;
                $client_id = 'service-54f9501a-29a8-4e60';
                $client_secret = 'd731de9d-0a5d-4d74-bde3-bb4a4a27e133';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://tdx.transportdata.tw/auth/realms/TDXConnect/protocol/openid-connect/token');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials&client_id='.$client_id.'&client_secret='.$client_secret);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $tokenResult = curl_exec($ch);
                curl_close($ch);

                $access_token = json_decode($tokenResult,1)['access_token'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://tdx.transportdata.tw/api/basic/v3/Rail/TRA/DailyTrainTimetable/OD/'.$_REQUEST['start'].'/to/'.$_REQUEST['end'].'/'.$_REQUEST['date']);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: Bearer '.$access_token));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $trainEstimatedTime = curl_exec($ch);
                curl_close($ch);
                $trainsInformation = json_decode($trainEstimatedTime);


                $ajaxResult['TrainTimetables']=$trainsInformation->TrainTimetables;

                echo json_encode($ajaxResult);
            }
            die();
        }

        function ajax_search_TDX_trainsTimeTable_API(){
            $ajaxResult = ['result'=>0];
            if ( !isset($_REQUEST['code']) ) {
                echo json_encode($ajaxResult);
            }else{
                $ajaxResult['result']=1;
                $client_id = 'service-54f9501a-29a8-4e60';
                $client_secret = 'd731de9d-0a5d-4d74-bde3-bb4a4a27e133';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://tdx.transportdata.tw/auth/realms/TDXConnect/protocol/openid-connect/token');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials&client_id='.$client_id.'&client_secret='.$client_secret);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $tokenResult = curl_exec($ch);
                curl_close($ch);

                $access_token = json_decode($tokenResult,1)['access_token'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://tdx.transportdata.tw/api/basic/v3/Rail/TRA/GeneralTrainTimetable/TrainNo/'.$_REQUEST['code']);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: Bearer '.$access_token));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $trainTimeTable = curl_exec($ch);
                curl_close($ch);
                $trainsInformation = json_decode($trainTimeTable);


                $ajaxResult['TrainTimetables']=$trainsInformation->TrainTimetables;

                echo json_encode($ajaxResult);
            }
            die();

        }


        public static function instance(){
            if(is_null(self::$_instance))self::$_instance=new self();
            return self::$_instance;
        }
    }
}