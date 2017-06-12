<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-left">
      <h1><?php echo $ms_caption_offer; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $ms_heading_title_offer; ?></h3>
      </div>
      <div class="panel-body">
        <form action="" method="post" enctype="multipart/form-data" id="form-offer" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $ms_offer_tab_general; ?></a></li>
            <li><a href="#tab-products" data-toggle="tab"><?php echo $ms_offer_tab_products; ?></a></li>
            <li><a href="#tab-services" data-toggle="tab"><?php echo $ms_offer_tab_services; ?></a></li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">

              <div class="container-fluid">
                <div style="background-color: #f5f5f5; border: 1px solid #ddd; margin-bottom: 10px; padding: 20px 0;" class="row">
                  <div class="col-md-12">
                    <fieldset class="control-inline">
                      <h3><?php echo $offer_info['offer_name']; ?> (<?php echo $offer_info['date_start']; ?>)</h3>
                      <hr/>
                      <table>
                        <tr>
                          <td><?php echo $ms_offer_date_start; ?></td>
                          <td><?php echo $offer_info['date_start']; ?></td>
                        </tr>
                        <tr>
                          <td><?php echo $ms_offer_date_end; ?></td>
                          <td><?php echo $offer_info['date_end']; ?></td>
                        </tr>
                      </table>
                    </fieldset>
                  </div>
                </div>
              </div>

              <div class="container-fluid">
                <div style="background-color: #f5f5f5; border: 1px solid #ddd; margin-bottom: 10px; padding: 20px 0;" class="row">
                  <div class="col-md-6" style="border-right: 1px solid #ddd;">
                    <fieldset class="control-inline">
                      <legend>Issued by</legend>
                      <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo $ms_offer_image; ?></label>
                        <div class="col-sm-10">
                          <img src="<?php if(isset($offer_info['offer_by_image_thumb'])) { echo $offer_info['offer_by_image_thumb']; } ?>" />
                        </div>
                      </div>

                      <div class="form-group form-inline text-center">
                        <label for="by_name"><?php echo $ms_offer_name; ?></label>
                        <span><?php if(isset($offer_info['offer_by_name'])){ echo $offer_info['offer_by_name']; } ?></span>
                      </div>

                      <div class="form-group form-inline text-center">
                        <label for="by_phone"><?php echo $ms_offer_phone; ?></label>
                        <span><?php if(isset($offer_info['offer_by_phone'])){ echo $offer_info['offer_by_phone']; } ?></span>
                      </div>

                      <div class="form-group form-inline text-center">
                        <label for="by_email"><?php echo $ms_offer_email; ?></label>
                        <span><?php if(isset($offer_info['offer_by_email'])){ echo $offer_info['offer_by_email']; } ?></span>
                      </div>

                      <?php if( !empty($offer_info['offer_by_company']) ) { ?>
                      <div class="form-group form-inline text-center">
                        <label for="by_company"><?php echo $ms_offer_company; ?></label>
                        <span><?php if(isset($offer_info['offer_by_company'])){ echo $offer_info['offer_by_company']; } ?></span>
                      </div>
                      <?php } ?>

                      <?php if( $offer_info['offer_by_nip'] != 0 ) { ?>
                      <div class="form-group form-inline text-center">
                        <label for="by_nip"><?php echo $ms_offer_nip; ?></label>
                        <span><?php if(isset($offer_info['offer_by_nip'])){ echo $offer_info['offer_by_nip']; } ?></span>
                      </div>
                      <?php } ?>

                      <?php if( !empty($offer_info['offer_by_address']) ) { ?>
                      <div class="form-group form-inline text-center">
                        <label for="by_address"><?php echo $ms_offer_address; ?></label>
                        <span><?php if(isset($offer_info['offer_by_address'])){ echo $offer_info['offer_by_address']; } ?></span>
                      </div>
                      <?php } ?>

                    </fieldset>
                  </div>

                  <div class="col-md-6">
                    <fieldset class="control-inline">
                      <legend>Offer for </legend>
                      <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo $ms_offer_image; ?></label>
                        <div class="col-sm-10">
                          <img src="<?php if(isset($offer_info['offer_for_image_thumb'])) { echo $offer_info['offer_for_image_thumb']; } ?>" />
                        </div>
                      </div>

                      <div class="form-group form-inline text-center">
                        <label for="for_name"><?php echo $ms_offer_name; ?></label>
                        <span><?php if(isset($offer_info['offer_for_name'])){ echo $offer_info['offer_for_name']; } ?></span>
                      </div>

                      <div class="form-group form-inline text-center">
                        <label for="for_phone"><?php echo $ms_offer_phone; ?></label>
                        <span><?php if(isset($offer_info['offer_for_phone'])){ echo $offer_info['offer_for_phone']; } ?></span>
                      </div>

                      <div class="form-group form-inline text-center">
                        <label for="for_email"><?php echo $ms_offer_email; ?></label>
                        <span><?php if(isset($offer_info['offer_for_email'])){ echo $offer_info['offer_for_email']; } ?></span>
                      </div>

                      <?php if( !empty($offer_info['offer_for_company']) ) { ?>
                      <div class="form-group form-inline text-center">
                        <label for="for_company"><?php echo $ms_offer_company; ?></label>
                        <span><?php if(isset($offer_info['offer_for_company'])){ echo $offer_info['offer_for_company']; } ?></span>
                      </div>
                      <?php } ?>

                      <?php if( $offer_info['offer_for_nip'] != 0 ) { ?>
                      <div class="form-group form-inline text-center">
                        <label for="for_nip"><?php echo $ms_offer_nip; ?></label>
                        <span><?php if(isset($offer_info['offer_for_nip'])){ echo $offer_info['offer_for_nip']; } ?></span>
                      </div>
                      <?php } ?>

                      <?php if( !empty($offer_info['offer_for_address']) ) { ?>
                      <div class="form-group form-inline text-center">
                        <label for="for_address"><?php echo $ms_offer_address; ?></label>
                        <span><?php if(isset($offer_info['offer_for_address'])){ echo $offer_info['offer_for_address']; } ?></span>
                      </div>
                      <?php } ?>

                    </fieldset>
                  </div>
                </div>
              </div>

            </div>

            <div class="tab-pane" id="tab-products">
              <div class="table-responsive container-fluid">
                <table class="table table-bordered">
                  <thead>
                  <tr>
                    <td class="text-center">#</td>
                    <td class="text-center"><?php echo $ms_column_image; ?></td>
                    <td class="text-left"><?php echo $ms_column_name; ?></td>
                    <td class="text-left"><?php echo $ms_column_model; ?></td>
                    <td class="text-center"><?php echo $ms_column_purchase_price; ?></td>
                    <td class="text-center"><?php echo $ms_column_retail_price; ?></td>
                    <td class="text-center"><?php echo $ms_column_discount; ?></td>
                    <td class="text-center"><?php echo $ms_column_client_price; ?></td>
                    <td class="text-center"><?php echo $ms_column_tax; ?></td>
                    <td class="text-center"><?php echo $ms_column_final_price; ?></td>
                    <td class="text-left"><?php echo $ms_column_quantity; ?></td>
                    <td class="text-right"><?php echo $ms_column_total; ?></td>
                  </tr>
                  </thead>
                  <tbody id="offer-products">
                  <?php $i=1; foreach ($products as $product) { ?>
                  <tr>
                    <td class="text-center">
                      <?php echo $i; ?>
                      <input type="hidden" name="product_id[]" value="<?php echo $product['product_id']; ?>"/>
                    </td>
                    <td class="text-center"><?php if ($product['thumb']) { ?>
                      <img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" class="img-thumbnail" />
                      <?php } ?></td>
                    <td class="text-left"><?php echo $product['name']; ?>
                      <?php if (!$product['stock']) { ?>
                      <span class="text-danger">***</span>
                      <?php } ?>
                      <?php if ($product['option']) { ?>
                      <?php foreach ($product['option'] as $option) { ?>
                      <br />
                      <small><?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
                      <?php } ?>
                      <?php } ?>
                      <?php if ($product['reward']) { ?>
                      <br />
                      <small><?php echo $product['reward']; ?></small>
                      <?php } ?>
                      <?php if ($product['recurring']) { ?>
                      <br />
                      <span class="label label-info"><?php echo $text_recurring_item; ?></span> <small><?php echo $product['recurring']; ?></small>
                      <?php } ?></td>
                    <td class="text-left"><?php echo $product['model']; ?></td>
                    <td class="text-right">
                      <span><?php echo $product['price']; ?></span>
                      <input id="price-<?php echo $product['product_id']; ?>" name="price[<?php echo $product['product_id']; ?>]" type="hidden" value="<?php echo $product['unit_price']; ?>" />
                    </td>
                    <td class="text-center"><span><?php echo isset($product['retail_price']) ? $product['retail_price'] : $product['unit_price']; ?></span></td>
                    <td class="text-center"><span><?php echo isset($product['discount']) ? $product['discount'] : 0; ?></span></td>
                    <td class="text-center"><span><?php echo isset($product['price_ex_tax']) ? $product['price_ex_tax'] : $product['unit_price']; ?></td>
                    <td class="text-center"><span><?php echo isset($product['tax']) ? $product['tax'] : 0; ?></span></td>
                    <td class="text-center"><span><?php echo isset($product['price_inc_tax']) ? $product['price_inc_tax'] : $product['unit_price'] * (100 + $product['tax'])/100; ?></span></td>
                    <td class="text-center">
                      <span><?php echo $product['quantity']; ?></span>
                    </td>
                    <td class="text-right"><span><?php echo $product['total'] ?></span></td>
                  </tr>
                  <?php  $i++; } ?>
                  </tbody>
                </table>

              <div class="row">
                <div class="col-sm-4 col-sm-offset-8">
                  <table class="table table-bordered">
                    <thead>
                    <tr>
                      <th></th>
                      <th class="text-center"><?php echo $ms_netto; ?></th>
                      <th class="text-center"><?php echo $ms_brutto; ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                      <td><?php echo $ms_total_seller; ?></td>
                      <td><span id="seller-netto-insert"><?php echo $totals['total_seller_netto']; ?></span></td>
                      <td><span id="seller-brutto-insert"><strong><?php echo $totals['total_seller_brutto']; ?></strong></span></td>
                    </tr>
                    <tr>
                      <td><?php echo $ms_total_client; ?></td>
                      <td><span id="client-netto-insert"><?php echo $totals['total_client_netto']; ?></span></td>
                      <td><span id="client-brutto-insert"><?php echo $totals['total_client_brutto']; ?></span></td>
                    </tr>
                    <tr>
                      <td><?php echo $ms_profit; ?></td>
                      <td><span id="profit"><?php echo $profit; ?></span></td>
                      <td></td>
                    </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              </div>
            </div>

            <div class="tab-pane" id="tab-services">
              <h2><?php echo $ms_offer_tab_services; ?>:</h2>
              <table id="services" class="table-responsive" style="width: 50%;">
                <?php foreach($offer_info['services'] as $service) { ?>
                <tr>
                  <td><?php echo $service['text']; ?></td>
                  <td><?php echo $service['price']; ?></td>
                </tr>
                <?php } ?>
                <tr>
                  <td><strong><?php echo $ms_total_service; ?></strong></td>
                  <td><strong><?php echo $total_services['text']; ?></strong></td>
                </tr>
              </table>
            </div>

          </div>


        </form>
      </div>
    </div>
  </div>
  </div>
<?php echo $footer; ?>
