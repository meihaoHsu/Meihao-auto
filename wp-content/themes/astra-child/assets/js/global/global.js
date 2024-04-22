jQuery(function ($) {
    $(document.body).on('click','.repair-detail-url',function (e){
        var ua = navigator.userAgent;
        var iOS = !!ua.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); // ios
        if(iOS === true){
            let URL = $(this).attr('data-iosURL');
            $(this).attr('href',URL);

        }
    });
});