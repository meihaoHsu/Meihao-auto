<?php
include MTT_DIR.'/station.php';
?>
<div class="meihao_wrapper">
    <div class="taiwan_trains_header">
        <h2><?=__('Trains TimeTable Search','Meihao-Taiwan-Trains');?></h2>
    </div>
    <div class="taiwan_trains_body">
        <form id="taiwan_trainsForm" method="post" name="taiwan_trainsForm">
            <div class="box-wrapper">
                <button type="button" class="select-box select-station" value="#start-station-wrapper">
                    <?=__('Departure Station','Meihao-Taiwan-Trains');?>
                    <small id="start-station">Taipei</small>
                    <input type="hidden" id="start" name="start" value="1000">
                </button>
                <a class="change-station">
                    <span class="dashicons dashicons-leftright change-font"></span>
                </a>
                <button type="button" class="select-box select-station" value="#end-station-wrapper">
                    <?=__('Arrival Station','Meihao-Taiwan-Trains');?>
                    <small id="end-station">Xinzuoying</small>
                    <input type="hidden" id="end" name="end" value="4340">
                </button>
                <button type="button" class="select-box select-date">
                    <?=__('Departure Date','Meihao-Taiwan-Trains');?>
                    <input class="select-input" type="text" id="date" name="date" value="<?=date('Y-m-d')?>">
                </button>
            </div>
            <div class="station-wrapper" id="start-station-wrapper">
                <input type="text" class="keyword" name="keyword" data-type="start" placeholder="Start Station ,Please type the station name, ex:'Taipei'">
                <div class="trains-station-detail" >
                    <?php foreach ($stations as $city => $parentDetail):?>
                        <button type="button" class="station-btn station-parent <?=$city?>" data-type="start" value="<?=$city?>">
                            <div class="station-btn-div">
                                <span class="station-en"><?=$parentDetail['EN']?></span>
                                <span class="station-tw"><?=$parentDetail['TW']?></span>
                            </div>
                        </button>
                        <?php foreach ($parentDetail['stations'] as $childrenStations):?>
                            <button type="button" class="station-btn station-children <?=$city?>" data-type="start" value="<?=$childrenStations['code']?>">
                                <div class="station-btn-div">
                                    <span class="station-en"><?=$childrenStations['EN']?></span>
                                    <span class="station-tw"><?=$childrenStations['TW']?></span>
                                </div>
                            </button>
                        <?php endforeach;?>
                    <?php endforeach;?>
                </div>
            </div>
            <div class="station-wrapper" id="end-station-wrapper">
                <input type="text" class="keyword" name="keyword" data-type="end" placeholder="End Station ,Please type the station name, ex:'Taipei'">
                <div class="trains-station-detail" >
                    <?php foreach ($stations as $city => $parentDetail):?>
                        <button type="button" class="station-btn station-parent <?=$city?>" data-type="end" value="<?=$city?>">
                            <div class="station-btn-div">
                                <span class="station-en"><?=$parentDetail['EN']?></span>
                                <span class="station-tw"><?=$parentDetail['TW']?></span>
                            </div>
                        </button>
                        <?php foreach ($parentDetail['stations'] as $childrenStations):?>
                            <button type="button" class="station-btn station-children <?=$city?>" data-type="end" value="<?=$childrenStations['code']?>">
                                <div class="station-btn-div">
                                    <span class="station-en"><?=$childrenStations['EN']?></span>
                                    <span class="station-tw"><?=$childrenStations['TW']?></span>
                                </div>
                            </button>
                        <?php endforeach;?>
                    <?php endforeach;?>
                </div>
            </div>
            <div class="trains-search">
                <button class="search-btn" id="search-btn" type="button"><?=__('Search','Meihao-Taiwan-Trains');?></button>
            </div>
        </form>
    </div>
    <div class="taiwan_trains_footer">
        <div class="result-wrapper" id="trains-result">

        </div>
    </div>
</div>
