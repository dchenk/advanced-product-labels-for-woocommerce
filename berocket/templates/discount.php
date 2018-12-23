<?php 
$start_time = 1475532000;//1475272800
$end_time   = 1475791200;//1475445600
$time       = time();
$discount   = 30;
$d_type     = 'p';
if( ! isset($dplugin_price) || ! is_numeric($dplugin_price) ) {
    $dplugin_price = 0;
}
if ( $d_type == 'p' ) {
    $discount_text   = $discount . '%';
    $discount_amount = ( $dplugin_price / 100 * $discount );
} else {
    $discount_text   = '$' . $discount;
    $discount_amount = $discount;
}

$price_new       = $dplugin_price - $discount_amount;
$price_new       = number_format( $price_new, 2, ".", " " );
$price_new       = '$' . $price_new;
$discount_amount = number_format( $discount_amount, 2, ".", " " );
$discount_amount = '$' . $discount_amount;

if ( $time > $start_time && $time < $end_time ) { ?>
    <div class="discount-block-check"></div>
    <div class="wrap discount-block">
        <div>
            <?php
            $text = 'Only today <strong>PRO</strong> version of %name% plugin for just %price%!<br>
            Get your <strong class="scale red pulse">%disc% discount</strong> and save %amount% today
            <a class="buy_button" href="%link%" target="_blank">Buy Now</a>';
            $text = str_replace( '%name%',   $dplugin_name,    $text );
            $text = str_replace( '%link%',   $dplugin_link,    $text );
            $text = str_replace( '%disc%',   $discount_text,   $text );
            $text = str_replace( '%amount%', $discount_amount, $text );
            $text = str_replace( '%price%',  $price_new,       $text );
            echo $text;
            ?>
        </div>
    </div>
    <script>
        jQuery(window).scroll(function() {
            var top = jQuery('.discount-block-check').offset().top - 32;

            if( jQuery(window).width() <= 782 ) {
                jQuery('.discount-block').removeClass('fixed');
                jQuery('.discount-block-check').height(0);
            } else {
                if( jQuery(window).scrollTop() > top ) {
                    if( ! jQuery('.discount-block').is('.fixed') ) {
                        jQuery('.discount-block-check').height(jQuery('.discount-block').outerHeight(true));
                        jQuery('.discount-block').addClass('fixed');
                    }
                } else {
                    if( jQuery('.discount-block').is('.fixed') ) {
                        jQuery('.discount-block-check').height(0);
                        jQuery('.discount-block').removeClass('fixed');
                    }
                }
            }
        });
    </script>
    <style>
    .discount-block-check {
        margin: 0!important;
        padding: 0!important;
    }
    .discount-block{
        text-align: center;
        background: #ffffff;
        padding: 20px;
        font-size: 24px;
        border: 4px solid #ff8800;
        line-height: 1.8em;
        transition: 0.4s all;
        animation-name: border_pulse;
        animation-duration: 4s;
        animation-iteration-count: infinite;
    }
    .discount-block.fixed{
        position: fixed;
        top: 32px;
        left: 180px;
        right: 0px;
    }
    @media only screen and (max-device-width: 960px) {
        .discount-block.fixed{
            left: 56px;
        }
    }
    @media only screen and (max-device-width: 782px) {
        .discount-block{
            font-size: 16px;
            padding: 5px;
        }
    }
    @keyframes border_pulse {
        0% {border-color: #ff8800;}
        50% {border-color: #0088ff;}
        100% {border-color: #ff8800;}
    }
    .discount-block .buy_button {
        font-size: 20px;
        padding: 8px 30px;
        color: #fff;
        line-height: 28px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
        text-align: center;
        text-decoration: none;
        background-color: #f16543;
        cursor: pointer;
    }
    .discount-block .buy_button:hover {
        background-color: #d94825;
    }
    .pulse {
        animation-duration: 1.5s;
        animation-iteration-count: infinite;
        animation-timing-function: linear;
        animation-timing-function: cubic-bezier(0.2,0.2,0.2,0.2);
    }
    .time-5 {
        animation-duration: 0.5s;
    }
    .time-10 {
        animation-duration: 1s;
    }
    .time-15 {
        animation-duration: 1.5s;
    }
    .time-20 {
        animation-duration: 2s;
    }
    .time-25 {
        animation-duration: 2.5s;
    }
    .time-30 {
        animation-duration: 3s;
    }
    .time-35 {
        animation-duration: 3.5s;
    }
    .time-40 {
        animation-duration: 4s;
    }
    .red {
        color: #ff0000;
    }
    .red.pulse {
        animation-name: red_pulse;
    }
    @keyframes red_pulse {
        0% {color: #ff0000;}
        50% {color: #000000;}
        100% {color: #ff0000;}
    }
    .blue {
        color: #0000ff;
    }
    .blue.pulse {
        animation-name: blue_pulse;
    }
    @keyframes blue_pulse {
        0% {color: #0000ff;}
        50% {color: #000000;}
        100% {color: #0000ff;}
    }
    .pulse.scale {
        display: inline-block;
        animation-name: scale_pulse;
    }
    @keyframes scale_pulse {
        0% {transform: scale(1);}
        25%{transform: scale(1.05);}
        50% {transform: scale(1);}
        75% {transform: scale(0.9);}
        100% {transform: scale(1);}
    }
    .pulse.scale.red {
        animation-name: scale_red_pulse;
    }
    @keyframes scale_red_pulse {
        0% {transform: scale(1);color: #ff0000;}
        25%{transform: scale(1.05);color: #ff0000;}
        50% {transform: scale(1);color: #ff0000;}
        75% {transform: scale(0.9);color: #000000;}
        100% {transform: scale(1);color: #ff0000;}
    }
    .pulse.scale.blue {
        animation-name: scale_blue_pulse;
    }
    @keyframes scale_blue_pulse {
        0% {transform: scale(1);color: #0000ff;}
        25%{transform: scale(1.05);color: #0000ff;}
        50% {transform: scale(1);color: #0000ff;}
        75% {transform: scale(0.9);color: #000000;}
        100% {transform: scale(1);color: #0000ff;}
    }
    </style>
<?php } ?>
