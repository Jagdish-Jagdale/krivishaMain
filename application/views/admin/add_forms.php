<?php include('header.php'); ?>
<style type="text/css">
    .error {
        color: red;
        float: left;
    }

    .flex_wrap {
        display: flex;
        flex-wrap: wrap;
    }

    .select2-container {
        width: 100% !important;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>Add Production Report</h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="category_form" id="category_form" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <!-- <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Supervisor name<b class="require">*</b></label>
                                <input type="text" class="form-control" value="" placeholder="Enter Supervisor Name" required>
                            </div> -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Date<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control" name="filter_date" id="filter_date">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Machine<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple" name="machine" id="machine">
                                    <option value="">Select Machine Type</option>
                                    <option value="350MT">350MT</option>
                                    <option value="250MT">250MT</option>
                                    <option value="170MT">170MT</option>
                                    <option value="80MT">80MT</option>
                                    <option value="BLOW">BLOW</option>
                                    <option value="JOB WORK">JOB WORK</option>
                                    <option value="Mat Unit">Mat Unit</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Article Names / Mould<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple" aria-placeholder="Please Choose Article" multiple="multiple" name="articale_name" id="articale_name">

                                    <option value="">Choose article</option>
                                    <option value="10kg Lid">10kg Lid</option>
                                    <option value="10ltr Lid">10ltr Lid</option>
                                    <option value="1 Kg Lid">1 Kg Lid</option>
                                    <option value="1 Ltr Lid">1 Ltr Lid</option>
                                    <option value="20 Kg Lid">20 Kg Lid</option>
                                    <option value="20ltr Black Lid">20ltr Black Lid</option>
                                    <option value="20ltr Lid">20ltr Lid</option>
                                    <option value="2 Kg Lid">2 Kg Lid</option>
                                    <option value="4 Lit Lid">4 Lit Lid</option>
                                    <option value="5KG LIDS">5KG LIDS</option>
                                    <option value="Baby Cap">Baby Cap</option>
                                    <option value="10kg B Container">10kg B Container</option>
                                    <option value="10ltr B Container">10ltr B Container</option>
                                    <option value="1kg B Conatiner">1kg B Conatiner</option>
                                    <option value="1ltr Container B">1ltr Container B</option>
                                    <option value="20 Kg B Container">20 Kg B Container</option>
                                    <option value="20ltr B Black Conatiner">20ltr B Black Conatiner</option>
                                    <option value="20ltr B Container">20ltr B Container</option>
                                    <option value="2kg B Container">2kg B Container</option>
                                    <option value="4 LIT B">4 LIT B</option>
                                    <option value="5 KG CONT B">5 KG CONT B</option>
                                    <option value="7 KG B CONT">7 KG B CONT</option>
                                    <option value="15 LTR MILK CAN CAP">15 LTR MILK CAN CAP</option>
                                    <option value="15 Ltr Milk Can Ear">15 Ltr Milk Can Ear</option>
                                    <option value="Ghagar 10 Ltr">Ghagar 10 Ltr</option>
                                    <option value="Ghagar 14 Ltr">Ghagar 14 Ltr</option>
                                    <option value="Ghagar 17l Bahubali">Ghagar 17l Bahubali</option>
                                    <option value="Ghagar 17 Ltr Krivisha">Ghagar 17 Ltr Krivisha</option>
                                    <option value="JERRY CAN 20 LTR (L)">JERRY CAN 20 LTR (L)</option>
                                    <option value="JERRY CAN 35 LTR">JERRY CAN 35 LTR</option>
                                    <option value="Milk Can 10 Ltr Bahubali">Milk Can 10 Ltr Bahubali</option>
                                    <option value="Milk Can 15 Ltr Bahubali">Milk Can 15 Ltr Bahubali</option>
                                    <option value="Milk Can 3 Ltr Bahubali">Milk Can 3 Ltr Bahubali</option>
                                    <option value="Milk Can 5 Ltr Bahubali">Milk Can 5 Ltr Bahubali</option>
                                    <option value="Milk Can 7.5 Ltr Bahubali">Milk Can 7.5 Ltr Bahubali</option>
                                    <option value="10ltr Bucket">10ltr Bucket</option>
                                    <option value="13ltr Bucket">13ltr Bucket</option>
                                    <option value="16 Ltr Bucket">16 Ltr Bucket</option>
                                    <option value="18ltr Bucket">18ltr Bucket</option>
                                    <option value="3 Ltr Bucket">3 Ltr Bucket</option>
                                    <option value="5ltr Bucket">5ltr Bucket</option>
                                    <option value="Dustbin 10 Ltr">Dustbin 10 Ltr</option>
                                    <option value="DUSTBIN 7LTR">DUSTBIN 7LTR</option>
                                    <option value="Dustbin Cap 10ltr">Dustbin Cap 10ltr</option>
                                    <option value="Dustbin Cap 7ltr">Dustbin Cap 7ltr</option>
                                    <option value="Dustpan Jumbo Clean">Dustpan Jumbo Clean</option>
                                    <option value="DUSTPAN LAKSHADEEP">DUSTPAN LAKSHADEEP</option>
                                    <option value="Dustpan Master Clean">Dustpan Master Clean</option>
                                    <option value="Dustpan Super Clean">Dustpan Super Clean</option>
                                    <option value="CONSTRO 15">CONSTRO 15</option>
                                    <option value="CONSTRO 16"> CONSTRO 16</option>
                                    <option value="CONSTRO 16PLUS">CONSTRO 16PLUS</option>
                                    <option value="CONSTRO 17+">CONSTRO 17+</option>
                                    <option value="CONSTRO 17C">CONSTRO 17C</option>
                                    <option value="CONSTRO 18PLUS">CONSTRO 18PLUS</option>
                                    <option value="CONSTRO 22+">CONSTRO 22+</option>
                                    <option value="CONSTRO TRISHA 16&quot; C">CONSTRO TRISHA 16" C</option>
                                    <option value="GAHMELA 11 FRUIT TUB">GAHMELA 11 FRUIT TUB</option>
                                    <option value="GHAMELA 10&quot;">GHAMELA 10"</option>
                                    <option value="GHAMELA 14D">GHAMELA 14D</option>
                                    <option value="GHAMELA 14 FRUTZ">GHAMELA 14 FRUTZ</option>
                                    <option value="Ghamela 16&quot; MULTI">Ghamela 16" MULTI</option>
                                    <option value="GHAMELA 17">GHAMELA 17</option>
                                    <option value="GHAMELA 18">GHAMELA 18</option>
                                    <option value="GHAMELA 19">GHAMELA 19</option>
                                    <option value="GHAMELA 21">GHAMELA 21</option>
                                    <option value="GHAMELA 22&quot;">GHAMELA 22"</option>
                                    <option value="GHAMELA 8&quot;">GHAMELA 8"</option>
                                    <option value="GHAMELA BALAJI">GHAMELA BALAJI</option>
                                    <option value="GHAMELA FEATHERLITE 16&quot;">GHAMELA FEATHERLITE 16"</option>
                                    <option value="GHAMELA KRISHNA">GHAMELA KRISHNA</option>
                                    <option value="GHAMELA KRISHNA BLACK">GHAMELA KRISHNA BLACK</option>
                                    <option value="LOTUS GHAMELA">LOTUS GHAMELA</option>
                                    <option value="10ltr House Hold Handels">10ltr House Hold Handels</option>
                                    <option value="13ltr House Hold Handels">13ltr House Hold Handels</option>
                                    <option value="18ltr Hh Bucket Handle">18ltr Hh Bucket Handle</option>
                                    <option value="3ltr+5ltr Hh Handel">3ltr+5ltr Hh Handel</option>
                                    <option value="JALI BASKET HANDLE">JALI BASKET HANDLE</option>
                                    <option value="KRISHNA Handle">KRISHNA Handle</option>
                                    <option value="Milk Can Cap">Milk Can Cap</option>
                                    <option value="Milk Can Ear">Milk Can Ear</option>
                                    <option value="DISH FLAP(OVAL)">DISH FLAP(OVAL)</option>
                                    <option value="Lota 1 Ltr">Lota 1 Ltr</option>
                                    <option value="MUG 1000ML">MUG 1000ML</option>
                                    <option value="Mug 1200ML">Mug 1200ML</option>
                                    <option value="MUG 750ML">MUG 750ML</option>
                                    <option value="Mug 900ml">Mug 900ml</option>
                                    <option value="OvalSoapdish">OvalSoapdish</option>
                                    <option value="Soap Case">Soap Case</option>
                                    <option value="JUG 2 LTR">JUG 2 LTR</option>
                                    <option value="JUG CAP">JUG CAP</option>
                                    <option value="ROUND JALI BASKET">ROUND JALI BASKET</option>
                                    <option value="SQ CRATE">SQ CRATE</option>
                                    <option value="Sup  14x14">Sup 14x14</option>
                                    <option value="TRAY NO.2">TRAY NO.2</option>
                                    <option value="TRAY NO.3">TRAY NO.3</option>
                                    <option value="TRAY NO.4">TRAY NO.4</option>
                                    <option value="Tub 20 LTR">Tub 20 LTR</option>
                                    <option value="Tub 30 LTR">Tub 30 LTR</option>
                                    <option value="PATALA SQUARE">PATALA SQUARE</option>
                                    <option value="PATLA MINI SQUARE">PATLA MINI SQUARE</option>
                                    <option value="PATLA ROUND">PATLA ROUND</option>
                                    <option value="KLARA PLANTER NO. 10">KLARA PLANTER NO. 10</option>
                                    <option value="KLARA PLANTER NO.12">KLARA PLANTER NO.12</option>
                                    <option value="KLARA PLANTER NO. 6">KLARA PLANTER NO. 6</option>
                                    <option value="KLARA PLANTER NO. 8">KLARA PLANTER NO. 8</option>
                                    <option value="PLANTER 10 UNBREAKABLE">PLANTER 10 UNBREAKABLE</option>
                                    <option value="PLANTER 12 UNBREAKABLE">PLANTER 12 UNBREAKABLE</option>
                                    <option value="PLANTER 14 UNBREAKABLE">PLANTER 14 UNBREAKABLE</option>
                                    <option value="Planter CANYON">Planter CANYON</option>
                                    <option value="PLANTER CLASSIC">PLANTER CLASSIC</option>
                                    <option value="PLANTER HEX O">PLANTER HEX O</option>
                                    <option value="Planter Line-O">Planter Line-O</option>
                                    <option value="PLANTER RUBY">PLANTER RUBY</option>
                                    <option value="PLANTER NO. 10 NURSERY">PLANTER NO. 10 NURSERY</option>
                                    <option value="PLANTER NO. 12 NURSERY">PLANTER NO. 12 NURSERY</option>
                                    <option value="PLANTER NO. 5 NURSERY">PLANTER NO. 5 NURSERY</option>
                                    <option value="PLANTER NO. 6 NURSERY">PLANTER NO. 6 NURSERY</option>
                                    <option value="PLANTER NO. 8 NURSERY">PLANTER NO. 8 NURSERY</option>
                                    <option value="RP PLANTER NO.10">RP PLANTER NO.10</option>
                                    <option value="RP PLANTER NO.12">RP PLANTER NO.12</option>
                                    <option value="RP PLANTER NO.14">RP PLANTER NO.14</option>
                                    <option value="RP PLANTER NO.8">RP PLANTER NO.8</option>
                                    <option value="RP PLANTER SQ 12X6">RP PLANTER SQ 12X6</option>
                                    <option value="RP ROUND PLANTER NO. 10">RP ROUND PLANTER NO. 10</option>
                                    <option value="RP ROUND PLANTER NO. 12">RP ROUND PLANTER NO. 12</option>
                                    <option value="RP ROUND PLANTER NO. 5">RP ROUND PLANTER NO. 5</option>
                                    <option value="RP ROUND PLANTER NO. 6">RP ROUND PLANTER NO. 6</option>
                                    <option value="RP ROUND PLANTER NO. 8">RP ROUND PLANTER NO. 8</option>
                                    <option value="RP SQ PLANTER NO.10">RP SQ PLANTER NO.10</option>
                                    <option value="PLANTER SQ 12X6" (UB)>PLANTER SQ 12X6" (UB)</option>
                                    <option value="Laxmi Spade No.8">Laxmi Spade No.8</option>
                                    <option value="Laxmi Spade No.8(Black)">Laxmi Spade No.8(Black)</option>
                                    <option value="SPADE HANDLE (BLACK)">SPADE HANDLE (BLACK)</option>
                                    <option value="SPADE HANDLE (VIRGINE)">SPADE HANDLE (VIRGINE)</option>
                                    <option value="Mat 4*6">Mat 4*6</option>
                                    <option value="Mat 5*7">Mat 5*7</option>
                                </select>

                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Raw Materials<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple" multiple="multiple" name="row_material" id="row_material">

                                   
                                    <option value="307m Haldia">307m Haldia</option>
                                    <option value="312m Haldia">312m Haldia</option>
                                    <option value="7033e3 Mobil">7033e3 Mobil</option>
                                    <option value="BE961MO">BE961MO</option>
                                    <option value="Bf970mo">Bf970mo</option>
                                    <option value="BORMOD BJ368MO">BORMOD BJ368MO</option>
                                    <option value="CP90N-PL14">CP90N-PL14</option>
                                    <option value="EP2340P">EP2340P</option>
                                    <option value="EP332K">EP332K</option>
                                    <option value="EXXONMOBIL VISTAMAXX 3980FL">EXXONMOBIL VISTAMAXX 3980FL</option>
                                    <option value="IMPACT PPC">IMPACT PPC</option>
                                    <option value="MOPLEN EP332L">MOPLEN EP332L</option>
                                    <option value="PP 37MK10">PP 37MK10</option>
                                    <option value="PP4080MA">PP4080MA</option>
                                    <option value="PP7032E3">PP7032E3</option>
                                    <option value="PP AP03">PP AP03</option>
                                    <option value="PP B120MA">PP B120MA</option>
                                    <option value="PP B220MN">PP B220MN</option>
                                    <option value="PP B 400MN">PP B 400MN</option>
                                    <option value="PP BD265MO">PP BD265MO</option>
                                    <option value="PP-CP 36MK10R145">PP-CP 36MK10R145</option>
                                    <option value="PP-CP C080MA">PP-CP C080MA</option>
                                    <option value="VIRGINE DUST MIX">VIRGINE DUST MIX</option>
                                    <option value="VISTAMAXX6202">VISTAMAXX6202</option>
                                    <option value="EP2348S">EP2348S</option>
                                    <option value="EP2380M">EP2380M</option>
                                    <option value="EXXONMOBIL PP7935E1">EXXONMOBIL PP7935E1</option>
                                    <option value="HALENE P M304">HALENE P M304</option>
                                    <option value="Luban HP 2148 T">Luban HP 2148 T</option>
                                    <option value="MIXED VERGINE R M">MIXED VERGINE R M</option>
                                    <option value="Polypropylene Luban HP2106N">Polypropylene Luban HP2106N</option>
                                    <option value="POLYSURE PP M12RR">POLYSURE PP M12RR</option>
                                    <option value="PP57MNK10149">PP57MNK10149</option>
                                    <option value="PP7555KNE2">PP7555KNE2</option>
                                    <option value="PP H110MA">PP H110MA</option>
                                    <option value="PP HM012T">PP HM012T</option>
                                    <option value="PP J700">PP J700</option>
                                    <option value="PP MILKY">PP MILKY</option>
                                    <option value="PP NATURAL">PP NATURAL</option>
                                    <option value="TALC">TALC</option>
                                    <option value="BLACK RP">BLACK RP</option>
                                    <option value="Blue RP injection">Blue RP injection</option>
                                    <option value="Green RP injection">Green RP injection</option>
                                    <option value="LEMON GREEN RP">LEMON GREEN RP</option>
                                    <option value="MIX RP">MIX RP</option>
                                    <option value="Orange RP Injuction">Orange RP Injuction</option>
                                    <option value="RED RP">RED RP</option>
                                    <option value="WHITE RP">WHITE RP</option>
                                    <option value="BLUE FACTORY GRINDING">BLUE FACTORY GRINDING</option>
                                    <option value="MIX FACTORY GRINDING">MIX FACTORY GRINDING</option>
                                    <option value="ORANGE FACTORY GR INDING">ORANGE FACTORY GRINDING</option>
                                    <option value="PINK FACTORY GRINDING">PINK FACTORY GRINDING</option>
                                    <option value="WASTAGE REJECTION">WASTAGE REJECTION</option>
                                    <option value="WHITE FACTORY GRINDING">WHITE FACTORY GRINDING</option>
                                    <option value="YELLOW FACTORY GRINDING">YELLOW FACTORY GRINDING</option>
                                    <option value="B6401">B6401</option>
                                    <option value="BLOW TALC PE EURO">BLOW TALC PE EURO</option>
                                    <option value="HD NATURAL">HD NATURAL</option>
                                    <option value="HDPE 54 GB 012">HDPE 54 GB 012</option>
                                    <option value="HDPE B0155D">HDPE B0155D</option>
                                    <option value="HDPE B56003">HDPE B56003</option>
                                    <option value="HDPE B5628">HDPE B5628</option>
                                    <option value="Hdpe Blue Grinding">Hdpe Blue Grinding</option>
                                    <option value="Hdpe Brown Grinding">Hdpe Brown Grinding</option>
                                    <option value="HDPE D GPBM 012DB54">HDPE D GPBM 012DB54</option>
                                    <option value="HDPE GREEN GRINDING">HDPE GREEN GRINDING</option>
                                    <option value="Hdpe Grey Grinding">Hdpe Grey Grinding</option>
                                    <option value="HDPE HHM 5502 BN">HDPE HHM 5502 BN</option>
                                    <option value="HDPE HYA600">HDPE HYA600</option>
                                    <option value="HDPE LUBAN DMDA 6200">HDPE LUBAN DMDA 6200</option>
                                    <option value="Hdpe Mix Grinding">Hdpe Mix Grinding</option>
                                    <option value="HDPE OFFGRADE">HDPE OFFGRADE</option>
                                    <option value="Hdpe Orange Grinding">Hdpe Orange Grinding</option>
                                    <option value="HDPE PINK GRINDING">HDPE PINK GRINDING</option>
                                    <option value="Hdpe Scrap">Hdpe Scrap</option>
                                    <option value="HDPE WHITE GRINDING">HDPE WHITE GRINDING</option>
                                    <option value="HD RP BLUE">HD RP BLUE</option>
                                    <option value="HD RP MIX">HD RP MIX</option>
                                    <option value="HD RP ORANGE">HD RP ORANGE</option>
                                    <option value="MARLEX HHM 5502 BN C07">MARLEX HHM 5502 BN C07</option>
                                    <option value="OQ DMDH-6400">OQ DMDH-6400</option>
                                    <option value="HDPE 50 MA 180">HDPE 50 MA 180</option>
                                    <option value="HDPE BB2588">HDPE BB2588</option>
                                    <option value="HDPE INJECTION M6008">HDPE INJECTION M6008</option>
                                    <option value="LLDPE OPALENE F2001A">LLDPE OPALENE F2001A</option>
                                    <option value="HDPE opal b5004">HDPE opal b5004</option>

                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Master Batch<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple" multiple="multiple" name="master_batch" id="master_batch">

                                    
                                    <option value="ARIHANT PEARL GREY 7117">ARIHANT PEARL GREY 7117</option>
                                    <option value="Baby Blue">Baby Blue</option>
                                    <option value="Black M B">Black M B</option>
                                    <option value="Dark Blue">Dark Blue</option>
                                    <option value="DARK BROWN 6151 Mb (BLOW)">DARK BROWN 6151 Mb (BLOW)</option>
                                    <option value="Green M B (BISLARI)">Green M B (BISLARI)</option>
                                    <option value="Green Mb Dark">Green Mb Dark</option>
                                    <option value="LEMON GREEN Mb 41130">LEMON GREEN Mb 41130</option>
                                    <option value="MB BROWN 04105 (TERACOTTA FOR PLANTER)">MB BROWN 04105 (TERACOTTA FOR PLANTER)</option>
                                    <option value="MB Red">MB Red</option>
                                    <option value="M B SAMPLE">M B SAMPLE</option>
                                    <option value="MIX MB">MIX MB</option>
                                    <option value="Orange M B">Orange M B</option>
                                    <option value="Pink M B">Pink M B</option>
                                    <option value="POLYBRIGHT 04001">POLYBRIGHT 04001</option>
                                    <option value="UNWANTED WHITE MB">UNWANTED WHITE MB</option>
                                    <option value="White Mb">White Mb</option>
                                    <option value="YELLO M B">YELLO M B</option>
                                    <option value="Option 19">Option 19</option>

                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Rejection<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple" multiple="multiple" name="rejection" id="rejection ">
                                   
                                    <option value="BLUE FACTORY GRINDING">BLUE FACTORY GRINDING</option>
                                    <option value="MIX FACTORY GRINDING">MIX FACTORY GRINDING</option>
                                    <option value="ORANGE FACTORY GRINDING">ORANGE FACTORY GRINDING</option>
                                    <option value="PINK FACTORY GRINDING">PINK FACTORY GRINDING</option>
                                    <option value="WASTAGE REJECTION">WASTAGE REJECTION</option>
                                    <option value="WHITE FACTORY GRINDING">WHITE FACTORY GRINDING</option>
                                    <option value="YELLOW FACTORY GRINDING">YELLOW FACTORY GRINDING</option>
                                    <option value="Hdpe Blue Grinding">Hdpe Blue Grinding</option>
                                    <option value="Hdpe Brown Grinding">Hdpe Brown Grinding</option>
                                    <option value="HDPE GREEN GRINDING">HDPE GREEN GRINDING</option>
                                    <option value="Hdpe Grey Grinding">Hdpe Grey Grinding</option>
                                    <option value="Hdpe Orange Grinding">Hdpe Orange Grinding</option>
                                    <option value="HDPE PINK GRINDING">HDPE PINK GRINDING</option>
                                    <option value="HDPE WHITE GRINDING">HDPE WHITE GRINDING</option>
                                    <option value="HDPE MIX  GRINDING">HDPE MIX GRINDING</option>
                                    <option value="MAT REJECTION">MAT REJECTION</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Upload Pictures- Photos<b class="require">*</b></label>
                                <input type="file" name="pic_upload" id="pic_upload">

                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label>Remarks (if Any)<b class="require">*</b></label>
                                <textarea id="summernote" name="summernote"></textarea>

                            </div>



                        </div>

                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2({
            placeholder: "Please Select"
        });


    });
</script>

<script>
    $(document).ready(function() {
        $('#product_master .child_menu').show();
        $('#product_master').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.add_category').addClass('active_cc');
    });
</script>
<script>
    $(document).ready(function() {
        const previewButton = function(context) {
            const ui = $.summernote.ui;
            const button = ui.button({
                contents: ' Preview',
                tooltip: 'Preview',
                click: function() {
                    const content = $('#summernote').summernote('code');
                    $('#previewContent').html(content);
                    $('#previewModal').modal('show');
                }
            });
            return button.render();
        };
        $('#summernote').summernote({
            height: 300,
            placeholder: 'Write your content here...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['view', ['previewButton', 'fullscreen', 'codeview', 'help']],
                ['insert', ['link', 'picture', 'video']]
            ],
            buttons: {
                previewButton: previewButton
            },
            callbacks: {
                onImageUpload: function(images) {
                    sendFile(images[0]);
                }
            }
        });


    });

    function sendFile(image) {
        var data = new FormData();
        data.append("image", image);
        data.append("<?= $this->security->get_csrf_token_name() ?>", "<?= $this->security->get_csrf_hash() ?>");
        jQuery.ajax({
            data: data,
            type: "POST",
            url: "<?= base_url() ?>admin/Ajax_controller/upload_news_image",
            cache: false,
            contentType: false,
            processData: false,
            success: function(url) {
                var image = url;
                $('#summernote').summernote("insertImage", image);
            },
            error: function(data) {
                console.log(data);
            }
        });
    }

    $(document).ready(function() {
        flatpickr("#filter_date", {
            dateFormat: "d-m-Y",
        });
    });
</script>