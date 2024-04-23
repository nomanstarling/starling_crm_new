<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <style type="text/css">
            /* @font-face {
                font-family: 'Roboto';
                src: url('public/Roboto-Regular.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Roboto';
                src: url('public/Roboto-Thin.ttf') format('truetype');
                font-weight: 300;
                font-style: normal;
            }

            @font-face {
                font-family: 'Roboto';
                src: url('public/Roboto-Bold.ttf') format('truetype');
                font-weight: 700;
                font-style: normal;
            } */
            /* @import url('https://fonts.googleapis.com/css2?family=Protest+Revolution&display=swap'); */

            * {
                font-family: 'Roboto', Arial, sans-serif;
                color: #000;
                font-size: 14px;
                line-height: 1.2;
            }
            @page {
                margin: 0;
                padding: 0;
            }
            html, body {
                margin: 0;
                min-height: 100%;
                padding: 0;
            }

            .logo
            {
                position: absolute; 
                top: 30px; 
                left: 30px; 
                z-index: 100; 
                font-weight: bold; 
                color: red; 
                text-transform: uppercase;
                padding: 10px;
                width: 40%;
            }

            .logo img
            {
                width: 100%;
            }

            .feature
            {
                width: 100%;
            }


            .images
            {
                width: 100%;
            }

            .images table
            {
                width: 100%;
            }

            .images td
            {
                width: 33.333%;
                padding: 3px;
            }

            .images td img
            {
                width: 100%;
                border-radius:5px;
            }

            h1
            {
                font-size: 18px;
                font-weight: light;
                margin: 0;
                padding: 0;
                letter-spacing: 0;
                text-transform: uppercase;
                padding-bottom: 0px;
                border-bottom: 1px solid #ccc;
                position: relative;
                display: block;
                font-family: 'Roboto', Arial, sans-serif;
            }

            .footer
            {
                position: absolute;
                bottom: 0;
                left: 0px;
                right: 0px;
                bottom: 0px;
                height: 100px;
                background:#1B2742;
                padding-top:20px;
            }
            
            .footer h1
            {
                border-bottom: 0;
                font-size: 22px;
                margin-left: 10px;
                font-weight: bold;
                
            }

            .footer table
            {
                width: 100%;
            }

            .footer td
            {
                
                width: 33.333%;
                padding: 0 10px;
                position: relative;
                vertical-align: middle;
            }
            .footer td, .footer td a{
                color:white;
                text-decoration: none;
                font-weight: bold;
            }
            

            .footer ul
            {
                list-style-type: disc;
                padding: 0;
                margin: 0;
            }

            .footer td.border:after
            {
                content: "";
                display: block;
                width: 1px;
                height: 50px;
                background: white;
                position: absolute;
                top: 18px;
                right: 10px;
                z-index: 100;
            }

            .footer span
            {
                font-weight: bold;
            }
            

        </style>
    </head>
    <body>

        <style>
            .featuredImg{
                height:40% !important;
                width:100% !important;
                object-fit: cover !important;
            }
            .images img{
                height:160px;
                width:100%;
            }
            .location{
                background: #1B2742;
                color:white;
                font-size:28px;
                font-weight: bold;
                display:inline-block;
                margin-top:-60px;
                padding:10px 70px 16px 50px;
                text-align:center;
                border-bottom-right-radius:300px;
                border-top-right-radius:100px;
                
            }
            .keyfactor{
                background: rgb(69,88,102);
                background: linear-gradient(324deg, rgba(69,88,102,1) 0%, rgba(77,106,129,1) 35%);
            }
            
        </style>

        <style>
        
            .body{
                padding-right:40px;
                padding-left:40px;
            }
        </style>
        <?php

            function encode_img_base64($img_path = false): string
            {
                if($img_path){
                    $path = $img_path;
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
                return '';
            }
            $imgLogo = encode_img_base64(asset('assets/media/logos/logo.png'));
            
            $imgOne = null;
            $imgTwo = null;
            $imgThree = null;
            $imgFour = null;

            foreach($images as $key => $image){
                if($key == 0){
                    $imgOne = encode_img_base64(asset('public/storage/'.$image->path));
                }
                if($key == 1){
                    $imgTwo = encode_img_base64(asset('public/storage/'.$image->path));
                }
                if($key == 2){
                    $imgThree = encode_img_base64(asset('public/storage/'.$image->path));
                }
                if($key == 3){
                    $imgFour = encode_img_base64(asset('public/storage/'.$image->path));
                }
            }
            
            $agentImg = $agent_image;

            $footerLogo = encode_img_base64(asset('assets/media/logos/logo.png'));
            $iconMap = encode_img_base64(asset('assets/media/map_pin.png'));
            $iconBed = encode_img_base64(asset('assets/media/bed.png'));
            $iconBath = encode_img_base64(asset('assets/media/bath.png'));
            $iconBua = encode_img_base64(asset('assets/media/bua.png'));

            $limitWords = 20; // Set the desired word limit

            // Explode the description into an array of words, slice, and join
            $desc = implode(' ', array_slice(explode(' ', $listing->desc), 0, $limitWords));

            
        ?>
        <div class="page">
            <!-- <div class="logo"><img src="<?= $imgLogo; ?>" /></div> -->
            <img src="<?= $imgOne?>" alt="Image" class="featuredImg">
            <div class="location">
                <?= $listing->location; ?>
            </div>

            <div class="">
                <div class="body">
                    <?php if($imgTwo != null){ ?>
                    <div class="images">
                        <table>
                            <tr>
                                <td><img src="<?= $imgTwo?>" /></td>
                                <td><img src="<?= $imgThree?>" /></td>
                                <td><img src="<?= $imgFour?>" /></td>
                            </tr>
                        </table>
                    </div>
                    <?php } ?>

                    <style>
                        .row {
                            width: 100% !important;
                            display: block;
                        }

                        .column {
                            width: 50% !important;
                            float: left !important;
                        }
                        .column-right {
                            float: right !important;
                        }

                        .clear {
                            clear: both;
                        }

                        .amenities {
                            /* list-style: none; */
                            padding: 0;
                            padding-left: 12px;
                        }

                        .amenities li {
                            margin-bottom: 5px;
                        }
                    </style>

                    <table style="margin-top:20px;">
                        <tr>
                            <td style="width:60%; padding-top:0px !important;">
                                <div style="padding-right:30px !important; padding-top:0px !important;">
                                    <h1><?=$listing->title?></h1>
                                    <?= $listing->borchure_cont != null ? $listing->borchure_cont : $desc ?>
                                    <?php if (!empty($amenities) && count($amenities) > 0) { ?>
                                    <h3 style="letter-spacing:3px; margin:0px !important; color:#D3B879; font-weight: bold !important; font-size:19px;">AMENITIES</h3>
                                    <div class="row">
                                        <div class="column">
                                            <ul class="amenities">
                                                <?php
                                                
                                                    $count = 0;
                                                    foreach ($amenities as $key => $amen) {
                                                        if ($count < 4) {
                                                            ?>
                                                            <li><?= $amen->name; ?></li>
                                                            <?php
                                                        }
                                                        $count++;
                                                    }
                                                ?>
                                            </ul>
                                        </div>
                                        <?php if (count($amenities) > 4) { ?>
                                            <div class="column column-right">
                                                <ul class="amenities">
                                                    <?php
                                                    $count = 0;
                                                    foreach ($amenities as $key => $amen) {
                                                        if ($count >= 4) {
                                                            ?>
                                                            <li><?= $amen->name; ?></li>
                                                            <?php
                                                        }
                                                        $count++;
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        <?php } ?>
                                        <div class="clear"></div>
                                    </div>
                                    <?php } ?>
                                </div>
                                
                            </td>
                            <style>
                                .keyfactor h3, .keyfactor p{
                                    color:white !important;
                                    font-weight: bold !important;
                                    margin:0px !important;
                                    padding:0px !important;
                                }
                                .keyfactor h3{
                                    font-size:20px !important;
                                    margin-bottom:7px !important;
                                }
                                .keyfactor p{
                                    font-size:14px !important;
                                    margin-bottom:7px !important;
                                }
                                .hrhite{
                                    display: block;
                                    height: 1px;
                                    border: 0;
                                    border-top: 1px solid white;
                                    padding: 0;
                                }
                                .text-center{
                                    text-align:center;
                                }
                                .price{
                                    font-size: 30px !important;
                                    margin:0px !important;
                                    font-weight:600px !important;
                                    margin-top:25px !important;
                                }
                                .price .amount{
                                    font-weight:bolder !important;
                                    font-size: 30px !important;
                                    
                                }
                                .profImg{
                                    width:130px;
                                    height:130px;
                                    border-radius:100px;
                                    margin-top:-50px;
                                    border:3px solid #D3B879;
                                }
                            </style>
                            <td style="width:40%; vertical-align: top !important;">
                                <div class="keyfactor" style="padding-top:15px; padding-bottom:15px; padding-right:20px; padding-left:20px; border-radius:8px;">
                                    <h3>KEY FACTORS</h3>
                                    <p><span><img src="<?= $iconMap; ?>" style="width:15px;"></span> <?= $location; ?> </p>
                                    <hr class="hrhite">
                                    <p><span><img src="<?= $iconBed; ?>" style="width:15px;"></span> <?= $listing->bedrooms; ?> Bedroom <?= $category->title; ?> </p>
                                    <hr class="hrhite">
                                    <p><span><img src="<?= $iconBath; ?>" style="width:15px;"></span> <?= $listing->bathrooms; ?> Bathroom </p>
                                    <hr class="hrhite">
                                    <p><span><img src="<?= $iconBua; ?>" style="width:15px;"></span> BUA <?= $listing->bua;?> sq.ft. </p>
                                </div>
                                <h3 class="text-center price">AED <span class="amount"><?= $listing->price; ?></span></h3>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="footer">
                    <table>
                        <tr>
                            <td style="width:10%;">
                                <img src="<?= $agentImg; ?>" alt="" class="profImg">
                            </td>
                            <td class="border" style="width:40%;">
                                Contact <?= $agent->first_name; ?> <?= $agent->last_name; ?> today <br/>
                                <a href="tel:<?= $agent->mobile; ?>"><?= $agent->mobile; ?></a><br />
                                <?= $agent->email; ?>
                            </td>
                            <td style="width:40%;">
                                <a href="tel:971528507767">+971 52 850 7767</a><br />
                                <a href="mailto:info@starlingproperties.ae">info@starlingproperties.ae</a>
                                <a href="https://starlingproperties.ae">www.starlingproperties.ae</a>
                            </td>
                            <td style="text-align:center;">
                                <img src="<?= $footerLogo; ?>" class="text-center" alt="" style="width:50px;">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div style="page-break-before: always;"></div>

        <div class="body">
            <div style="padding-top:20px;">
                <?= $listing->desc; ?>
            </div>
            <div class="footer">
                <table>
                    <tr>
                        <td style="width:10%;">
                            <img src="<?= $agentImg; ?>" alt="" class="profImg">
                        </td>
                        <td class="border" style="width:40%;">
                            Contact <?= $agent->first_name; ?> <?= $agent->last_name; ?> today <br/>
                            <a href="tel:<?= $agent->mobile; ?>"><?= $agent->mobile; ?></a><br />
                            <?= $agent->email; ?>
                        </td>
                        <td style="width:40%;">
                            <a href="tel:971528507767">+971 52 850 7767</a><br />
                            <a href="mailto:info@starlingproperties.ae">info@starlingproperties.ae</a>
                            <a href="https://starlingproperties.ae">www.starlingproperties.ae</a>
                        </td>
                        <td style="text-align:center;">
                            <img src="<?= $footerLogo; ?>" class="text-center" alt="" style="width:50px;">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </body>
</html>