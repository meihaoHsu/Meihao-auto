// jQuery(document).ready(function($) {

jQuery( function( $ ) {
    // $('#translate-log-open').click(function () {
    //     $('#translate-log-wrapper').toggle();
    // })

    $('#translate-button').click(function() {
        translateFunction();
    });
    $('#changeLanguage').click(function (){
        var inputLanguage = $('#inputLanguage').val();
        var outputLanguage = $('#outputLanguage').val();
        $('#inputLanguage').val(outputLanguage);
        $('#outputLanguage').val(inputLanguage);
    });

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        $("#outputText").text("Speech recognition not supported!");
        $("#voice-input").prop("disabled", true);
    } else {
        let Default_button_image = $('#voice-input').html();
        // 初始化語音辨識物件
        const recognition = new webkitSpeechRecognition();
        recognition.interimResults = false;

        //語音辨識結果顯示
        recognition.onresult = function(event) {
            const results = event.results[0];
            const transcript = results[0].transcript;
            $("#inputText").text(transcript);
            SeriouslyStopListening(recognition);
        };

        recognition.onspeechend = function() {
            // 停止收音
            SeriouslyStopListening(recognition);
        };

        // 判斷用戶是否已講完話
        recognition.onend = function(event) {
            // 語音輸入結束
            $("#voice-input").removeClass("btn-danger", true);
            $("#voice-input").addClass("btn-info", true);
            $("#voice-input").removeClass('recoding-button');
            $("#voice-input").addClass('voice-button');
            translateFunction();
            // SeriouslyStopListening(recognition);
            recognition.stop();
        };

        //點擊後觸發開始辨識
        $("#voice-input").on("click", function() {
            if($("#voice-input").hasClass('voice-button')){
                $('#inputText').text('');
                $("#voice-input").removeClass("btn-info", true);
                $("#voice-input").addClass("btn-danger", true);
                $("#voice-input").removeClass('voice-button');
                $("#voice-input").addClass('recoding-button');
                recognition.lang = $("#inputLanguage").val();
                recognition.start();
            }else if($("#voice-input").hasClass('recoding-button')){
                // SeriouslyStopListening(recognition);
                recognition.stop();
            }
        });

    }

    function translateFunction(){

        var inputLanguage = $('#inputLanguage').val();
        var outputLanguage = $('#outputLanguage').val();
        var inputText = $('#inputText').val();
        $.post('../../../wp-admin/admin-ajax.php', {
            action: 'ajax_translate_text', // 自取一個action的名稱
            inputLanguage: inputLanguage,
            outputLanguage: outputLanguage,
            inputText: inputText,
        }, function (result) {
            console.log(result);
            var data = JSON.parse( result );
            if (data.result === '1'){
                $('#outputText').text('');
                $('#outputText').text(data.text);
                $('#translate-log-detail').append(data.log_html);
            }else if(data.result === '0'){
                alert(data.text);
            }
        });
    }
    function SeriouslyStopListening(recognition)
    {
        if (navigator.vendor.indexOf('Apple') > -1)
        {
            try{ recognition.start(); }
            catch(err) { }
        }
        recognition.stop();
    }

});
const openButton = document.getElementById('translate-log-open');
const lightbox = document.getElementById('lightbox');
const logWrapper = document.getElementById('translate-log-wrapper');
const chatai = document.getElementsByClassName('mwai-open-button');
console.log(chatai);

openButton.addEventListener('click', () => {
    lightbox.style.display = 'block';
    logWrapper.style.display = 'block';
    for (let i = 0; i < chatai.length; i++) {
chatai[i].style.display = 'none';
}
    setTimeout(() => {
        lightbox.style.opacity = '1';
        logWrapper.style.opacity = '1';
    }, 10); 
});


lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
        lightbox.style.opacity = '0';
        logWrapper.style.opacity = '0';
         
        setTimeout(() => {
            lightbox.style.display = 'none';
            logWrapper.style.display = 'none';
            for (let i = 0; i < chatai.length; i++) {
    chatai[i].style.display = 'block';
}
        }, 300); 
    }
});