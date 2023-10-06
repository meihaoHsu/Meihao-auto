<?php
$options = get_option( 'mtc-option' );
if(isset($_SESSION['trans_logs']) && $_SESSION['trans_logs'] !=''){
    $translate_logs = array_reverse(unserialize($_SESSION['trans_logs']));
}else{
    $translate_logs = array();
}
$current_lang = apply_filters( 'wpml_current_language', NULL );
$default_language = ['vi'=>'vi','id'=>'id_ID','zh-hant'=>'zh_TW','en'=>'en_US']; // 當前語系預設翻譯用語言

?>
<div class="pick_lang">
    <select id="inputLanguage" name="inputLanguage">
        <?php foreach ($options['translate_languages'] as $langID => $title):?>
            <option value="<?=$langID?>" <?=($default_language[$current_lang] == $langID)?'selected':''?> ><?=$title?></option>
        <?php endforeach;?>
    </select>
    <button type="button" id="changeLanguage">
        <img src="<?= plugin_dir_url( __FILE__ ) . 'image/Frame-18630.png'; ?>">
    </button>
    <select id="outputLanguage" name="outputLanguage">
        <?php foreach ($options['translate_languages'] as $langID => $title):?>
            <option value="<?=$langID?>" <?=('zh_TW' == $langID)?'selected':'' ?> ><?=$title?></option>
        <?php endforeach;?>
    </select>
</div>
<div class="enter_btn">
    <button id="voice-input" class="btn-info voice-button"></button>

    <div class="translate_log">
        <div id="translate-log-open-wrapper">
            <!--            <button id="translate-log-open">--><?php //echo esc_html__('Recent Conversation', 'Meihao-Translate-Chat'); ?><!--</button>-->
            <button id="translate-log-open"><img class="recent-img" src="<?= plugin_dir_url( __FILE__ ) . 'image/recent-icon.png'; ?>"></button>
        </div>
        <div id="lightbox">
            <div id="translate-log-wrapper" style="display: none;">
                <div id="translate-log-detail">
                    <?php foreach($translate_logs as $key => $translate_log):?>
                        <p class="translate-log" >
                            <?=$translate_log?>
                        </p>
                    <?php endforeach;?>
                </div>
            </div>
        </div>

    </div>

    <button id="translate-button"><?php echo esc_html__('translation', 'Meihao-Translate-Chat'); ?></button>
</div>
<div>
    <div>
        <label class="result"><?php echo esc_html__('Input Text', 'Meihao-Translate-Chat'); ?></label>
        <textarea id="inputText" name="inputText" placeholder="<?php echo esc_html__('Text or send voice message...', 'Meihao-Translate-Chat'); ?>"></textarea>
    </div>
    <div>
        <label class="result"><?php echo esc_html__('Translate Result', 'Meihao-Translate-Chat'); ?></label>
        <textarea id="outputText" name="outputText" placeholder="<?php echo esc_html__('Waiting for translate...', 'Meihao-Translate-Chat'); ?>"></textarea>
    </div>
</div>


