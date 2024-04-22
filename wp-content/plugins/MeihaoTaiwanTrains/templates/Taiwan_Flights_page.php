<?php
include MTT_DIR.'/flights.php';
?>
<div class="meihao_wrapper">
    <div class="taiwan_trains_header">
        <h2><?=__('Flights TimeTable Search','Meihao-Taiwan-Trains');?></h2>
    </div>
    <div class="taiwan_trains_body">
        <form id="taiwan_trainsForm" method="post" name="taiwan_trainsForm">
            <div class="box-wrapper">
                <button type="button" class="select-box select-station" value="#start-station-wrapper">
                    <?=__('Departure Airport','Meihao-Taiwan-Trains');?>
                    <small id="start-station">TPE</small>
                    <input type="hidden" id="start" name="start" value="TPE">
                </button>
                <a class="change-station">
                    <span class="dashicons dashicons-leftright change-font"></span>
                </a>
                <button type="button" class="select-box select-station" value="#end-station-wrapper">
                    <?=__('Arrival Airport','Meihao-Taiwan-Trains');?>
                    <small id="end-station">SGN</small>
                    <input type="hidden" id="end" name="end" value="SGN">
                </button>
                <button type="button" class="select-box select-date" >
                    <?=__('Departure Date','Meihao-Taiwan-Trains');?>
                    <input class="select-input" type="text" id="date" name="date" value="<?=date('Y-m-d')?>">
                </button>

                <button type="button" class="select-box select-current" >
                    <?=__('Currency','Meihao-Taiwan-Trains');?>
                    <select id="curr" name="curr" class="select-input">
                        <option value="TWD">TWD</option>
                        <option value="VND">VND</option>
                        <option value="IDR">IDR</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>
            <div class="station-wrapper" id="start-station-wrapper">
                <input type="text" class="keyword" name="keyword" data-type="start" placeholder="Start Airport ,Please type the Airport name or Code, ex:'TPE'">
                <div class="trains-station-detail" >
                    <?php foreach ($stations as $city => $parentDetail):?>
                        <button type="button" class="station-btn station-parent <?=$city?>" data-type="start" value="<?=$city?>">
                            <div class="station-btn-div">
                                <span class="station-en"><?=$parentDetail['EN']?></span>
                                <span class="station-tw"><?=$parentDetail['TW']?></span>
                            </div>
                        </button>
                        <?php foreach ($parentDetail['airport'] as $childrenStations):?>
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
                <input type="text" class="keyword" name="keyword" data-type="end" placeholder="End Airport ,Please type the Airport name or Code, ex:'TPE'">
                <div class="trains-station-detail" >
                    <?php foreach ($stations as $city => $parentDetail):?>
                        <button type="button" class="station-btn station-parent <?=$city?>" data-type="end" value="<?=$city?>">
                            <div class="station-btn-div">
                                <span class="station-en"><?=$parentDetail['EN']?></span>
                                <span class="station-tw"><?=$parentDetail['TW']?></span>
                            </div>
                        </button>
                        <?php foreach ($parentDetail['airport'] as $childrenStations):?>
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
                <a id="result-link" href="#" target="_blank"></a>
            </div>
        </form>
    </div>
</div>
