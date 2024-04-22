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
        let $start = $('#start').val(), $end = $('#end').val(), $date = $('#date').val();
        let $trainHTML = '', $trainType={1:'Tarko',2:'Puyuma',3:'Tze-Chiang',4:'Chu-Kuang',5:'Fu-Hsing',6:'Local',7:'Ordinary',10:'Fast Local',11:'Tze-Chiang'};

        $('.station-wrapper, .station-parent, .station-children').hide();

        if($start !== '' && $end !== '' && $date !== ''){
            $.post('../../wp-admin/admin-ajax.php', {
                action: 'search_TDX_dailyTrain_API', // 自取一個action的名稱
                start: $start,
                end: $end,
                date: $date,
            }, function (data) {
                var $result = $.parseJSON(data);
                if($result.result !== 1){
                    alert(__('Please enter the full data!','Meihao-Taiwan-Trains'));
                }else{
                    $result.TrainTimetables.forEach(function (train) {
                        if(train.TrainInfo.EndingStationName.En !== 'Taipei Surround Island'){
                            var start_time  =   new Date('2024-01-01 '+train.StopTimes[0].DepartureTime);
                            var end_time    =   new Date('2024-01-01 '+train.StopTimes[1].DepartureTime);

                            var diff = (end_time - start_time)/1000;
                            var HH = Math.floor(diff/3600);
                            var MM = Math.floor(diff%3600)/60;

                            $trainHTML += '<div class="result-train">' +
                                '<div class="result-train-info">' +
                                    '<div class="train-info train-data">' +
                                        '<span class="line-code">'+train.TrainInfo.TrainNo+'</span>' +
                                        '<small class="train-type train-'+train.TrainInfo.TrainTypeCode+'">'+$trainType[train.TrainInfo.TrainTypeCode]+'</small>' +
                                        '<span class="station-info">'+train.TrainInfo.StartingStationName.En+' - '+train.TrainInfo.EndingStationName.En+'</span>' +
                                    '</div>' +
                                    '<div class="train-info time-data">' +
                                        '<span class="line-time">'+train.StopTimes[0].DepartureTime+' - '+train.StopTimes[1].DepartureTime+'</span>' +
                                        '<small class="total-time">'+HH+ __("Hours","Meihao-Taiwan-Trains") +MM+ __("Minutes","Meihao-Taiwan-Trains") +'</small>' +
                                    '</div>' +
                                    '<div class="train-info open-detail">' +
                                    '<span class="dashicons dashicons-insert train-detail" data-code="'+train.TrainInfo.TrainNo+'"></span>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="result-train-detail"></div>' +
                            '</div>';
                        }
                    });

                    $('#trains-result').html($trainHTML);
                }
            });
        }else{
            $trainHTML +='<div class="no-result">' +
                '<span>'+__('No Taiwan Railway train number found! There are two possibilities','Meihao-Taiwan-Trains')+'</span>' +
                '<ul>' +
                '<li>'+__('The departure time is set too late and there are no trains available.','Meihao-Taiwan-Trains')+'</li>' +
                '<li>'+__('The Departure and Arrival station settings are incorrect.','Meihao-Taiwan-Trains')+'</li>' +
                '</ul></div>';
            $resultTrainDetail.html($trainHTML);
        }
    });


    $(document.body).on('click','.train-detail',function (e){
        let $code = $(this).data('code'), $trainHTML = '', $trainType={1:'Tarko',2:'Puyuma',3:'Tze-Chiang',4:'Chu-Kuang',5:'Fu-Hsing',6:'Local',7:'Ordinary',10:'Fast Local',11:'Tze-Chiang'};

        let $resultTrainDetail = $(this).closest('.result-train-info').siblings('.result-train-detail');

        if($code !== ''){
            $.post('../../wp-admin/admin-ajax.php', {
                action: 'search_TDX_trainsTimeTable_API', // 自取一個action的名稱
                code: $code,
            }, function (data) {

                var $result = $.parseJSON(data);
                console.log($result);
                if($result.result !== 1){
                    $trainHTML +='<div class="no-result">' +
                            '<span>'+__('No Taiwan Railway train number found! There are two possibilities','Meihao-Taiwan-Trains')+'</span>' +
                            '<ul>' +
                                '<li>'+__('The departure time is set too late and there are no trains available.','Meihao-Taiwan-Trains')+'</li>' +
                                '<li>'+__('The Departure and Arrival station settings are incorrect.','Meihao-Taiwan-Trains')+'</li>' +
                            '</ul></div>';
                    $resultTrainDetail.html($trainHTML);
                }else{
                    $trainHTML += '<span class="dashicons dashicons-no-alt close-detail"></span>' +
                            '<h4>'+$result.TrainTimetables[0].TrainInfo.TrainNo+' '+$trainType[$result.TrainTimetables[0].TrainInfo.TrainTypeCode]+ '</h4>' +
                            '<div class="time-table">' +
                                '<div class="time-info">' +
                                    '<span><label>'+__('Station Name','Meihao-Taiwan-Trains')+'</label></span>' +
                                    '<span><label>'+__('Arrival Time','Meihao-Taiwan-Trains')+'</label></span>' +
                                    '<span><label>'+__('Departure Time','Meihao-Taiwan-Trains')+'</label></span>' +
                                '</div>';
                    $result.TrainTimetables[0].StopTimes.forEach(function (train) {
                            $trainHTML += '<div class="time-info">' +
                                    '<span>'+train.StationName.En+' '+train.StationName.Zh_tw+'</span>' +
                                    '<span>'+train.ArrivalTime+'</span>' +
                                    '<span>'+train.DepartureTime+'</span>' +
                                '</div>';
                    });

                    $trainHTML += '</div>';
                    $resultTrainDetail.html($trainHTML);

                }
            });
        }else{
            $trainHTML +='<div class="no-result">' +
                    '<span>'+__('No Taiwan Railway train number found! There are two possibilities','Meihao-Taiwan-Trains')+'</span>' +
                    '<ul>' +
                        '<li>'+__('The departure time is set too late and there are no trains available.','Meihao-Taiwan-Trains')+'</li>' +
                        '<li>'+__('The Departure and Arrival station settings are incorrect.','Meihao-Taiwan-Trains')+'</li>' +
                    '</ul></div>';
            $resultTrainDetail.html($trainHTML);
        }
    });
    $(document.body).on('click','.close-detail',function (e) {

        let $resultTrainDetail = $(this).closest('.result-train-detail');
        $resultTrainDetail.html('');

    });



});

