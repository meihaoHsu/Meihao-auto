const { __ } = wp.i18n;

jQuery(document).ready(function ($) {

    $('#date').datepicker({
        dateFormat:'yy-mm-dd',
    });

    $('.select-date' ).on( 'click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('input#date').focus();
    });

    // station btn click action
    $(document.body).on('click','.select-station',function (e){
       let $stationWrapper = $(this).val();
       $('.station-wrapper, .station-parent, .station-children').hide();
       $($stationWrapper).show();
       $($stationWrapper+' .station-parent').show();
    });
    $(document.body).on('click','.station-parent',function (e){
        let $parentValue = $(this).val();
        $('.station-btn').hide();
        $('.station-children'+'.'+$parentValue).show();
    });
    $(document.body).on('click','.station-children',function (e){
        let $stationCode = $(this).val(), $stationType=$(this).data('type');
        $('.station-wrapper, .station-parent, .station-children').hide();
        $('#'+$stationType+'-station').html($(this).find('.station-en').html());
        $('#'+$stationType).val($stationCode);
    });
    $(document.body).on('change','.keyword',function (e){
        let $keyword = $(this).val(), $stationType=$(this).data('type');
        $('.station-parent, .station-children').hide();
        $('span:contains('+$keyword+')').each(function (i) {
            if($(this).closest('.station-children').data('type') === $stationType){
                $(this).closest('.station-children').show();
            }
        });
    });
    $(document.body).on('click','.change-station',function (e){
        let $startStation = $('#start-station').html(), $startCode=$('#start').val();
        let $endStation = $('#end-station').html(), $endCode=$('#end').val();
        $('#start-station').html($endStation);
        $('#start').val($endCode);
        $('#end-station').html($startStation);
        $('#end').val($startCode);
    });

    $(document.body).on('click','#search-btn',function (e){
        let $start = $('#start').val(), $end = $('#end').val(), $date = $('#date').val(),$curr = $('#curr').val();
        $('.station-wrapper, .station-parent, .station-children').hide();

        if($start !== '' && $end !== '' && $date !== ''){

            let $url='https://www.google.com/travel/flights?';
            var ua = navigator.userAgent;
            var iOS = !!ua.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); // ios
            if(iOS === true){
                $url='googlechrome://www.google.com/travel/flights?';
            }

            $url += 'q=Flights from '+$start+' to '+$end+' on '+$date+' oneway&curr='+$curr;

            $('#result-link').attr('href',$url);
            document.getElementById('result-link').click();
        }
    });

});

