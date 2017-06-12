<?php echo $header; ?>

<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <?php if ($attention) { ?>
  <div class="alert alert-info information"><i class="fa fa-info-circle"></i> <?php echo $attention; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="alert alert-success success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php } ?>
  <?php if ($error_warning) { ?>
  <div class="alert alert-danger warning"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php } ?>
  <?php if (isset($error_limit)) { ?>
  <div class="alert alert-danger warning"><i class="fa fa-exclamation-circle"></i> <?php echo $error_limit; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php } ?>

  <div class="row">

    <div id="content"><?php echo $content_top; ?>
      <h1 class="heading-title"><?php echo $heading_title; ?></h1>

      <form id="offer-form" name="offer-form" action="<?php echo $offer_add; ?>" method="post" enctype="multipart/form-data">

        <input type="hidden" name="offer_id" value="<?php if(isset($offer_info)){ echo $offer_info['offer_id']; } ?>" />
        <div class="container">
          <div style="background-color: #f5f5f5; border: 1px solid #ddd; margin-bottom: 10px; padding: 20px 10px 20px;" class="row text-right">
            <div class="form-inline">
              <button type="submit" name="submit" class="btn btn-primary" value="save"><?php echo $ms_save_offer; ?></button>
              <a class="btn btn-primary add-to-cart"><?php echo $ms_add_cart_offer; ?></a>
              <button type="submit" name="submit" class="btn btn-primary" value="new"><?php echo $ms_save_new_offer; ?></button>
              <button type="submit" name="submit" class="btn btn-primary" value="pdf"><?php echo $ms_create_pdf_offer; ?></button>
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mailModal"><?php echo $ms_send_mail_offer; ?></button>
            </div>
          </div>

          <div style="background-color: #f5f5f5; border: 1px solid #ddd; margin-bottom: 10px; padding: 20px 0;" class="row">
            <div class="col-md-12">
                <div class="form-group form-inline required">
                  <label for="name"><strong><?php echo $ms_offer_caption;?></strong></label>
                  <input id="name" name="name" type="text" class="form-control" style="width: 50%;"  value="<?php if(isset($offer_info['offer_name'])){ echo $offer_info['offer_name']; } ?>" />
                  <?php if (isset($error_name)) { ?>
                    <div class="text-danger"><?php echo $error_name; ?></div>
                  <?php } ?>
                </div>
                <div class="row">
                  <div class="col-md-5">
                    <div class="form-group form-inline required">
                      <label for="date_start"><strong><?php echo $ms_offer_date_start; ?></strong></label>
                      <input id="date_start" name="date_start" type="date" class="form-control" style="width: 50%;" value="<?php if(isset($offer_info['date_start'])){ echo $offer_info['date_start']; } ?>" />
                      <?php if (isset($error_date_start)) { ?>
                      <div class="text-danger"><?php echo $error_date_start; ?></div>
                      <?php } ?>
                    </div>
                  </div>
                 <div class="col-md-5">
                   <div class="form-group form-inline required">
                     <label for="date_end"><strong><?php echo $ms_offer_date_end; ?></strong></label>
                     <input id="date_end" name="date_end" type="date" class="form-control" style="width: 50%;" value="<?php if(isset($offer_info['date_end'])){ echo $offer_info['date_end']; } ?>" />
                     <?php if (isset($error_date_end)) { ?>
                     <div class="text-danger"><?php echo $error_date_end; ?></div>
                     <?php } ?>
                   </div>
                 </div>
                </div>
            </div>

          </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="mailModal" tabindex="-1" role="dialog" aria-labelledby="myMailLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myMailLabel"><?php echo $ms_offer_modal_title; ?></h4>
              </div>
              <div class="modal-body">
                <div class="form-group form-inline">
                  <label for="mail"><?php echo $ms_offer_modal_email; ?></label>
                  <input id="mail" type="email" class="form-control" />
                </div>
              </div>
              <div class="modal-footer">
                <button id="close-modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $ms_offer_modal_close; ?></button>
                <a id="send-mail" class="btn btn-primary" onclick="sendMail();"><?php echo $ms_offer_modal_send; ?></a>
              </div>
            </div>
          </div>
        </div>

        <input type="hidden" id="email" value="" name="email" />

        <div class="container">
          <div style="background-color: #f5f5f5; border: 1px solid #ddd; margin-bottom: 10px; padding: 20px 0;" class="row">
            <div class="col-md-6" style="border-right: 1px solid #ddd;">
              <fieldset class="control-inline">
                <legend><?php echo $ms_issued_by; ?></legend>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $ms_offer_image; ?></label>
                  <div class="col-sm-10">
                    <div id="offer_by_image">
                      <div class="ms-image <?php if (empty($offer_info['offer_by_image'])) { ?>hidden<?php } ?>">
                        <input type="hidden" name="by_image" value="<?php if(isset($offer_info['offer_by_image'])) { echo $offer_info['offer_by_image']; } ?>" />
                        <img src="<?php if(isset($offer_info['offer_by_image_thumb'])) { echo $offer_info['offer_by_image_thumb']; } ?>" />
                        <span class="ms-remove"><i class="fa fa-times"></i></span>
                      </div>

                      <div class="dragndropmini <?php if (!empty($offer_info['offer_by_image'])) { ?>hidden<?php } ?>" id="by_image"><p class="mm_drophere"><?php echo $ms_drop_image; ?></p></div>
                      <p class="ms-note"><?php echo $ms_select_logo; ?></p>
                      <div class="alert alert-danger" style="display: none;"></div>
                      <div class="ms-progress progress"></div>
                    </div>
                  </div>
                </div>

                <div class="form-group form-inline text-center required">
                  <label for="by_name"><strong><?php echo $ms_offer_name; ?></strong></label>
                  <input type="text" name="by_name" id="by_name" value="<?php if(isset($offer_info['offer_by_name'])){ echo $offer_info['offer_by_name']; } ?>" class="form-control"/>
                  <?php if (isset($error_by_name)) { ?>
                    <div class="text-danger"><?php echo $error_by_name; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center required">
                  <label for="by_phone"><strong><?php echo $ms_offer_phone; ?></strong></label>
                  <input type="text" name="by_phone" id="by_phone" value="<?php if(isset($offer_info['offer_by_phone'])){ echo $offer_info['offer_by_phone']; } ?>" class="form-control"/>
                  <?php if (isset($error_by_phone)) { ?>
                  <div class="text-danger"><?php echo $error_by_phone; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center required">
                  <label for="by_email"><strong><?php echo $ms_offer_email; ?></strong></label>
                  <input type="text" name="by_email" id="by_email" value="<?php if(isset($offer_info['offer_by_email'])){ echo $offer_info['offer_by_email']; } ?>" class="form-control"/>
                  <?php if (isset($error_by_email)) { ?>
                  <div class="text-danger"><?php echo $error_by_email; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center">
                  <label for="by_company"><?php echo $ms_offer_company; ?></label>
                  <input type="text" name="by_company" id="by_company" value="<?php if(isset($offer_info['offer_by_company'])){ echo $offer_info['offer_by_company']; } ?>" class="form-control"/>
                  <?php if (isset($error_by_company)) { ?>
                  <div class="text-danger"><?php echo $error_by_company; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center">
                  <label for="by_nip"><?php echo $ms_offer_nip; ?></label>
                  <input type="number" name="by_nip" id="by_nip" value="<?php if($offer_info['offer_by_nip'] != 0){ echo $offer_info['offer_by_nip']; } ?>" class="form-control" />
                  <?php if (isset($error_by_nip)) { ?>
                    <div class="text-danger"><?php echo $error_by_nip; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center">
                  <label for="by_address"><?php echo $ms_offer_address; ?></label>
                  <input type="text" name="by_address" id="by_address" value="<?php if(isset($offer_info['offer_by_address'])){ echo $offer_info['offer_by_address']; } ?>" class="form-control"/>
                  <?php if (isset($error_by_address)) { ?>
                    <div class="text-danger"><?php echo $error_by_address; ?></div>
                  <?php } ?>
                </div>
              </fieldset>
            </div>

            <div class="col-md-6">
              <fieldset class="control-inline">
                <legend><?php echo $ms_offer_for; ?></legend>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $ms_offer_image; ?></label>
                  <div class="col-sm-10">
                    <div id="offer_for_image">
                      <div class="ms-image <?php if (empty($offer_info['offer_for_image'])) { ?>hidden<?php } ?>">
                        <input type="hidden" name="for_image" value="<?php if(isset($offer_info['offer_for_image'])) { echo $offer_info['offer_for_image']; } ?>" />
                        <img src="<?php if(isset($offer_info['offer_for_image_thumb'])) { echo $offer_info['offer_for_image_thumb']; } ?>" />
                        <span class="ms-remove"><i class="fa fa-times"></i></span>
                      </div>

                      <div class="dragndropmini <?php if (!empty($offer_info['offer_for_image'])) { ?>hidden<?php } ?>" id="for_image"><p class="mm_drophere"><?php echo $ms_drop_image; ?></p></div>
                      <p class="ms-note"><?php echo $ms_select_logo; ?></p>
                      <div class="alert alert-danger" style="display: none;"></div>
                      <div class="ms-progress progress"></div>
                    </div>
                  </div>
                </div>
                <div class="form-group form-inline text-center required">
                  <label for="for_name"><strong><?php echo $ms_offer_name; ?></strong></label>
                  <input type="text" name="for_name" id="for_name" value="<?php if(isset($offer_info['offer_for_name'])){ echo $offer_info['offer_for_name']; } ?>" class="form-control"/>
                  <?php if (isset($error_for_name)) { ?>
                    <div class="text-danger"><?php echo $error_for_name; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center required">
                  <label for="for_phone"><strong><?php echo $ms_offer_phone; ?></strong></label>
                  <input type="text" name="for_phone" id="for_phone" value="<?php if(isset($offer_info['offer_for_phone'])){ echo $offer_info['offer_for_phone']; } ?>" class="form-control"/>
                  <?php if (isset($error_for_phone)) { ?>
                  <div class="text-danger"><?php echo $error_for_phone; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center required">
                  <label for="for_email"><strong><?php echo $ms_offer_email; ?></strong></label>
                  <input type="text" name="for_email" id="for_email" value="<?php if(isset($offer_info['offer_for_email'])){ echo $offer_info['offer_for_email']; } ?>" class="form-control"/>
                  <?php if (isset($error_for_email)) { ?>
                  <div class="text-danger"><?php echo $error_for_email; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center">
                  <label for="for_company"><?php echo $ms_offer_company; ?></label>
                  <input type="text" name="for_company" id="for_company" value="<?php if(isset($offer_info['offer_for_company'])){ echo $offer_info['offer_for_company']; } ?>" class="form-control"/>
                  <?php if (isset($error_for_company)) { ?>
                  <div class="text-danger"><?php echo $error_for_company; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center">
                  <label for="for_nip"><?php echo $ms_offer_nip; ?></label>
                  <input type="number" name="for_nip" id="for_nip" value="<?php if($offer_info['offer_for_nip'] != 0){ echo $offer_info['offer_for_nip']; } ?>" class="form-control" />
                  <?php if (isset($error_for_nip)) { ?>
                    <div class="text-danger"><?php echo $error_for_nip; ?></div>
                  <?php } ?>
                </div>
                <div class="form-group form-inline text-center">
                  <label for="for_address"><?php echo $ms_offer_address; ?></label>
                  <input type="text" name="for_address" id="for_address" value="<?php if(isset($offer_info['offer_for_address'])){ echo $offer_info['offer_for_address']; } ?>" class="form-control"/>
                  <?php if (isset($error_for_address)) { ?>
                    <div class="text-danger"><?php echo $error_for_address; ?></div>
                  <?php } ?>
                </div>
              </fieldset>
            </div>
          </div>
        </div>

        <div class="container" style="margin: 0; padding: 0;">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <td></td>
                <td class="text-center"><?php echo $column_image; ?></td>
                <td class="text-left"><?php echo $column_name; ?></td>
                <td class="text-left"><?php echo $column_model; ?></td>
                <td class="text-right"><?php echo $ms_column_purchase_price; ?></td>
                <td class="text-center"><?php echo $ms_column_retail_price; ?></td>
                <td class="text-center"><?php echo $ms_column_discount; ?></td>
                <td class="text-center"><?php echo $ms_column_client_price; ?></td>
                <td class="text-center"><?php echo $ms_column_tax; ?></td>
                <td class="text-center"><?php echo $ms_column_final_price; ?></td>
                <td class="text-left"><?php echo $column_quantity; ?></td>
                <td class="text-right"><?php echo $column_total; ?></td>
                <td></td>
              </tr>
            </thead>
            <tbody id="offer-products">
              <?php $arr_products = array(); ?>
              <?php foreach ($products as $product) { ?>
                <?php $arr_products[] = $product['product_id']; ?>
              <tr>
                <td class="text-center">
                  <input name="remove-row" type="checkbox"/>
                  <input class="product-id" type="hidden" name="product_id[]" value="<?php echo $product['product_id']; ?>"/>
                </td>
                <td class="text-center"><?php if ($product['thumb']) { ?>
                  <a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" class="img-thumbnail" /></a>
                  <?php } ?></td>
                <td class="text-left"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
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
                <td class="text-center"><input id="retail-price-<?php echo $product['product_id']; ?>" name="retail_price[<?php echo $product['product_id']; ?>]" value="<?php echo isset($product['retail_price']) ? sprintf('%.2f', $product['retail_price']) : sprintf('%.2f', $product['customer_price']); ?>" type="number" min="0" step="any" required class="form-control" onchange="newRetailPrice(<?php echo $product['product_id']; ?>);" /></td>
                <td class="text-center"><input id="discount-<?php echo $product['product_id']; ?>" name="discount[<?php echo $product['product_id']; ?>]" value="<?php echo isset($product['discount']) ? $product['discount'] : 0; ?>" type="number" min="0" max="100" required step="any" class="form-control" onchange="newRetailPrice(<?php echo $product['product_id']; ?>);" /></td>
                <td class="text-center"><input id="seller-price-<?php echo $product['product_id']; ?>" name="seller_price[<?php echo $product['product_id']; ?>]" value="<?php echo isset($product['price_ex_tax']) ? sprintf('%.2f', $product['price_ex_tax']) : sprintf('%.2f', $product['customer_price']); ?>" type="number" min="0" step="any" required class="form-control" onchange="newSellerPrice(<?php echo $product['product_id']; ?>);" /></td>
                <td class="text-center"><input id="tax-<?php echo $product['product_id']; ?>" name="tax[<?php echo $product['product_id']; ?>]" value="<?php echo isset($product['tax']) ? sprintf('%.2f', $product['tax']) : 0 ?>" type="number" min="0" step="any" required class="form-control" onchange="newTax(<?php echo $product['product_id']; ?>);" /></td>
                <td class="text-center"><input id="final-price-<?php echo $product['product_id']; ?>" name="final_price[<?php echo $product['product_id']; ?>]" type="number" step="any" min="0" required class="form-control" value="<?php echo isset($product['price_inc_tax']) ? sprintf('%.2f', $product['price_inc_tax']) : sprintf('%.2f', $product['customer_price'] * (100 + $product['tax'])/100); ?>" onchange="newFinalPrice(<?php echo $product['product_id']; ?>)" /></td>
                <td class="text-left">
                    <input id="quantity-<?php echo $product['product_id']; ?>" name="quantity[<?php echo $product['product_id']; ?>]" value="<?php echo $product['quantity']; ?>" type="number" step="any" min="1" required onchange="newQuantity(<?php echo $product['product_id']; ?>);" size="1" class="form-control quantity" />
                </td>
                <td class="text-right"><span id="total-<?php echo $product['product_id']; ?>"><?php echo $product['total'] ?></span></td>
                <td class="text-center">
                  <a class="button" onclick="removeProduct(this);"><i class="fa fa-times-circle"></i></a>
                </td>
              </tr>
              <?php } ?>
            <?php $json =  json_encode($arr_products); ?>
            </tbody>
          </table>
        </div>
        </div>

        <a class="btn btn-default" onclick="removeProducts()"><?php echo $ms_remove_selected; ?></a>
        <br />

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
                <td><span id="seller-brutto-insert"><?php echo $totals['total_seller_brutto']; ?></span></td>
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

        <div class="row">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-products" data-toggle="tab"><?php echo $ms_tab_products; ?></a></li>
            <li><a data-toggle="tab" href="#tab-services"><?php echo $ms_tab_services; ?></a></li>
          </ul>

         <div class="tab-content" style="display:block;">
           <!-- tab products begin -->
            <div id="tab-products" class="tab-pane active">
               <table class="table">
                 <tr>
                   <td class="col-md-10"><strong><?php echo $ms_find_products; ?></strong></td>
                 </tr>
                 <tr>
                   <td>
                     <div id="search-product">
                       <input type="text" autocomplete="off" name="search" placeholder="<?php echo $ms_placeholder_search; ?>" class="form-control input-lg" />
                     </div>
                   </td>
                 </tr>
               </table>
            </div>
            <!-- tab products end -->

            <!-- tab services begin -->
            <div id="tab-services" class="tab-pane">

              <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                  <thead>
                  <tr>
                    <td class="text-left"><?php echo $ms_service_text; ?></td>
                    <td class="text-left"><?php echo $ms_service_price; ?></td>
                    <td class="text-center"><?php echo $ms_column_tax; ?></td>
                    <td class="text-left"><?php echo $ms_service_final; ?></td>
                    <td class="text-left"></td>
                  </tr>
                  </thead>
                  <tbody>
                  <?php $service_row = 0; ?>
                  <?php if(isset($services)){ ?>
                  <?php foreach ($services as $service) { ?>
                  <tr id="service-row<?php echo $service_row; ?>">
                    <td class="text-left">
                      <input type="text" placeholder="<?php echo $ms_placeholder_service_name; ?>" value="<?php echo $service['text']; ?>" name="services[<?php echo $service_row; ?>][text]" class="form-control" required />
                    </td>
                    <td class="text-left">
                      <input type="number" step="any" min="0" value="<?php echo sprintf('%.4f', $service['price']); ?>" name="services[<?php echo $service_row; ?>][price]" class="form-control service-price" onchange="totalService(<?php echo $service_row; ?>);" />
                      <input type="hidden" value="<?php echo sprintf('%.4f', $service['price']); ?>" />
                    </td>
                    <td class="text-center">
                      <input type="number" step="any" min="0" value="<?php echo sprintf('%.4f', $service['tax']); ?>" name="services[<?php echo $service_row; ?>][tax]" class="form-control service-tax" onchange="serviceTax(<?php echo $service_row; ?>);" />
                      <input type="hidden" value="<?php echo sprintf('%.4f', $service['tax']); ?>" />
                    </td>
                    <td class="text-left">
                      <input type="number" step="any" min="0" value="<?php echo sprintf('%.2f', $service['price'] * (100 + $service['tax']) / 100); ?>" class="form-control final-price" onchange="servicePrice(<?php echo $service_row; ?>);" required />
                    </td>
                    <td class="text-left"><button type="button" onclick="removeService(<?php echo $service_row; ?>);" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                  </tr>
                  <?php $service_row++; ?>
                  <?php } ?>
                  <?php } ?>
                  </tbody>
                  <tfoot>
                  <tr>
                    <td colspan="4"></td>
                    <td class="text-left"><button type="button" onclick="addService()" data-toggle="tooltip" title="<?php echo $ms_title_add_service; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                  </tr>
                  </tfoot>
                </table>
              </div>

              <div class="form-group form-inline">
                <p><strong><?php echo $ms_total_service; ?><span class="total-services"><?php  echo $total_services['text']; ?></span></strong></p>
                <input id="total-service" type="hidden" value="<?php echo $total_services['number']; ?>" name="total_serv" />
              </div>

            </div>
           <!-- tab services end -->
          </div>
        </div>

        <div style="background-color: #f5f5f5; border: 1px solid #ddd; margin-bottom: 10px; padding: 20px 10px 20px;" class="row text-right">
            <div class="form-inline">
              <button type="submit" name="submit" class="btn btn-primary" value="save"><?php echo $ms_save_offer; ?></button>
              <a class="btn btn-primary add-to-cart"><?php echo $ms_add_cart_offer; ?></a>
              <button type="submit" name="submit" class="btn btn-primary" value="new"><?php echo $ms_save_new_offer; ?></button>
              <button type="submit" name="submit" class="btn btn-primary" value="pdf"><?php echo $ms_create_pdf_offer; ?></button>
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mailModal"><?php echo $ms_send_mail_offer; ?></button>
            </div>
        </div>
      </form>



      <script type="text/javascript">
     
      $('.add-to-cart').on('click', function() {
          $.ajax({
              url: 'index.php?route=multimerch/account_offer/addToCart',
              type: 'post',
              data: $('#offer-form').serialize(),
              dataType: 'json',

              success: function(json) {
                  $('.alert').remove();

                  if (json['error']) {
                      $('.breadcrumb').after('<div class="alert alert-danger warning">' + json['error'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                      $('html, body').animate({ scrollTop: 0 }, 'slow');
                  }

                  if (json['success']) {
                      $('.breadcrumb').after('<div class="alert alert-success success">' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                      $('#cart-total').html(json['total']);

                      $('html, body').animate({ scrollTop: 0 }, 'slow');

                      $('#cart ul').load('index.php?route=common/cart/info ul li');
                  }
              }
          });
      });
      
      function sendMail() {
          $('#email').val($('#mail').val());
          var data  = $('#offer-form').serialize() + '&submit=email';

          $.ajax({
              url: 'index.php?route=multimerch/account_offer/sendMail',
              type: 'post',
              data: data,
              dataType: 'json',

              beforeSend: function() {
                  $('#send-mail').button('loading');
              },
              complete: function() {
                  $('#send-mail').button('reset');
              },
              success: function(json) {
                  $('.alert-danger').remove();

                  if (json['error']) {
                      $('.modal-body').after('<div class="alert alert-danger warning">' + json['error'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                  } else {
                      $('#close-modal').click();
                      alert(json.success);
                  }
              }
          });
      }

      function newQuantity(id = null) {
          $.ajax({
              url: 'index.php?route=multimerch/account_offer/newQuantity',
              type: 'post',
              data: $('#offer-form').serialize(),
              dataType: 'json',

              success: function(json) {
                  if (id !== null) {
                      countTotal(id);
                  }

                  $('#seller-netto-insert').html(json.seller_netto);
                  $('#seller-brutto-insert').html(json.seller_brutto);
                  $('#client-netto-insert').html(json.client_netto);
                  $('#client-brutto-insert').html(json.client_brutto);

                  $('#profit').html(json.profit);
              }
          });
      }

      function countTotal(id) {
          $.ajax({
              url: 'index.php?route=multimerch/account_offer/countTotal',
              type: 'post',
              data: {
                  quantity : Number($("#quantity-" + id).val()),
                  price    : Number($("#final-price-" + id).val())
              },
              dataType: 'json',

              success: function(json) {
                  $("#total-" + id).html(json.total);
              }
          });
      }

      function newRetailPrice(id) {
        var retail   = Number($("#retail-price-" + id).val());
        var discount = Number($("#discount-" + id).val());
        var tax      = Number($("#tax-" + id).val());

        var seller = retail - retail * discount/100;
        var final  = seller + seller * tax / 100;

        $("#seller-price-" + id).val(seller.toFixed(2));
        $("#final-price-" + id).val(final.toFixed(2));
        newQuantity(id);
      }

      function newSellerPrice(id) {
          var seller   = Number($("#seller-price-" + id).val());
          var discount = Number($("#discount-" + id).val());
          var tax      = Number($("#tax-" + id).val());

          var retail   = seller * 100 / ( 100 - discount ) ;
          var final  = seller + seller * tax / 100;

          $("#retail-price-" + id).val(retail.toFixed(2));
          $("#final-price-" + id).val(final.toFixed(2));
          newQuantity(id);
      }

      function newFinalPrice(id) {
          var final    = Number($("#final-price-" + id).val());
          var discount = Number($("#discount-" + id).val());
          var tax      = Number($("#tax-" + id).val());

          var seller = final * 100 / ( 100 + tax );
          var retail   = seller * 100 / ( 100 - discount ) ;

          $("#seller-price-" + id).val(seller.toFixed(2));
          $("#retail-price-" + id).val(retail.toFixed(2));
          newQuantity(id);
      }

      function newTax(id) {
          var seller   = Number($("#seller-price-" + id).val());
          var tax      = Number($("#tax-" + id).val());

          var final  = seller + seller * tax / 100;

          $("#final-price-" + id).val(final.toFixed(2));
          newQuantity(id);
      }

      function removeProduct(e) {
          e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
          newQuantity();
      }

      function removeProducts() {
          $( "input[name=remove-row]:checked" ).each( function () {
              this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);
          });
          newQuantity();
      }

      function addProduct(e) {
          var arr = [];
          $( ".product-id" ).each( function () {
              arr.push($(this).val());
          });

          $.ajax({
              type: 'POST',
              url: 'index.php?route=multimerch/account_offer/addProduct',
              dataType: 'json',
              data: $(e).serialize() + '&arr=' + arr,

              success: function (json) {
                  if(json.edit){
                      var quantity = Number($('#quantity-' + json.edit.product_id).val());
                      $('#quantity-' + json.edit.product_id).val(quantity + Number(json.edit.quantity));
                      countTotal(Number(json.edit.product_id));
                      newQuantity();
                  } else {
                      $('#offer-products').append(json.html);
                      newQuantity();
                  }
              }
          });
      }

      function totalService(row) {
        var price = $("input[name='services["+ row +"][price]']");
        var tax   = $("input[name='services["+ row +"][tax]']");

        $("#service-row" + row).find('.final-price').val((Number(price.val()) * ( Number(tax.val()) + 100 )/100).toFixed(4));

        $.ajax({
            type: 'POST',
            url: 'index.php?route=multimerch/account_offer/totalService',
            dataType: 'json',
            data: {
                price   : Number(price.val()),
                old     : price.next().val(),
                total   : $('#total-service').val(),
                tax     : Number(tax.val()),
                tax_old : Number(tax.next().val())
            },

            success: function (json) {
                price.next().val(Number(price.val()));
                $('#total-service').val(json.total.number);
                $('.total-services').html(json.total.text);
            }
        });
      }

      function removeService(row) {
          var price = $('#service-row' + row).find('.final-price').val();
          $.ajax({
              type: 'POST',
              url: 'index.php?route=multimerch/account_offer/removeService',
              dataType: 'json',
              data: {
                  price : Number(price),
                  total : Number($('#total-service').val()),
              },

              success: function (json) {
                  $('#total-service').val(json.total.number);
                  $('.total-services').html(json.total.text);
                  $('#service-row' + row).remove()
              }
          });
      }

      function servicePrice(row) {
          var final = Number($('#service-row' + row).find('.final-price').val());
          var tax   = Number($("input[name='services["+ row +"][tax]']").next().val());

          var price = $("input[name='services["+ row +"][price]']");

          price.val(final * 100 / (100 + tax));
          totalService(row);
          price.val((final * 100 / (100 + tax)).toFixed(4));
      }

      function serviceTax(row) {
          var price = $("input[name='services["+ row +"][price]']");
          var tax   = $("input[name='services["+ row +"][tax]']");
          var final = $('#service-row' + row).find('.final-price');

          final.val((price.val() * (100 + tax.val()) /100).toFixed(4));
          totalService(row);
          tax.next().val(tax.val());
      }

      var service_row = '<?php echo $service_row; ?>';
      function addService() {
          html  = '<tr id="service-row' + service_row + '">';
          html += '  <td class="left">';
          html += '    <input type="text" placeholder="Service name" name="services[' + service_row + '][text]" class="form-control" required />';
          html += '  </td>';

          html += '  <td class="left">';
          html += '    <input type="number" step="any" min="0" value="0" name="services[' + service_row + '][price]" class="form-control service-price" onchange="totalService(' + service_row + ');" required />';
          html += '    <input type="hidden" value="0" />';
          html += '  </td>';

          html += '  <td class="text-center">';
          html += '    <input type="number" step="any" min="0" value="0" name="services[' + service_row + '][tax]" class="form-control service-tax" onchange="serviceTax(' + service_row + ');" />';
          html += '    <input type="hidden" value="0" />';
          html += '  </td>';

          html += '  <td class="left">';
          html += '    <input type="number" step="any" min="0" value="0" class="form-control final-price" onchange="servicePrice(' + service_row+ ');" required />';
          html += '  </td>';

          html += '  <td class="left">';
          html += '    <a onclick="removeService(' + service_row + ');" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></a>';
          html += '  </td>';
          html += '</tr>';

          $('#tab-services table tbody').append(html);

          service_row++;
      }

      </script>

      <?php echo $content_bottom; ?>
    </div>
   </div>
</div>

<?php echo $footer; ?>
