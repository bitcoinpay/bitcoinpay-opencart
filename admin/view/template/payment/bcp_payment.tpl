<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_api; ?></td>
            <td><input type="text" name="bcp_payment_api" value="<?php echo $bcp_payment_api; ?>" />
              <?php if ($error_api) { ?>
              <span class="error"><?php echo $error_api; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_password; ?></td>
            <td><input type="text" name="bcp_payment_password" value="<?php echo $bcp_payment_password; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_email; ?></td>
            <td><input type="text" name="bcp_payment_email" value="<?php echo $bcp_payment_email; ?>" /></td>
          </tr>

          <tr>
            <td><?php echo $entry_currency; ?></td>
            <?php $curr_msg = (($bcp_payment_currency != NULL) && (strlen($bcp_payment_currency)==3)) ? $bcp_payment_currency : "BTC"; ?>
            <td><input type="text" name="bcp_payment_currency" value="<?php echo $curr_msg; ?>" /></td>
          </tr>


          <tr>
            <td><?php echo $entry_buttons; ?></td>
            <td>
              <input type="radio" name="bcp_payment_buttons" value="1" <?php if($bcp_payment_buttons == 1){?>checked="checked"<?php } ?>><img src="view/image/payment/bcp_buttons/01_s.png" alt="i01">
              <input type="radio" name="bcp_payment_buttons" value="2" <?php if($bcp_payment_buttons == 2){?>checked="checked"<?php } ?>><img src="view/image/payment/bcp_buttons/02_s.png" alt="i02">
              <input type="radio" name="bcp_payment_buttons" value="3" <?php if($bcp_payment_buttons == 3){?>checked="checked"<?php } ?>><img src="view/image/payment/bcp_buttons/03_s.png" alt="i03">
              <input type="radio" name="bcp_payment_buttons" value="4" <?php if($bcp_payment_buttons == 4){?>checked="checked"<?php } ?>><?php echo $entry_buttons_text; ?>
            </td>
          </tr>

          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="bcp_payment_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $bcp_payment_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>

          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="bcp_payment_status">
                <?php if ($bcp_payment_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="bcp_payment_sort_order" value="<?php echo $bcp_payment_sort_order; ?>" size="1" /></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?> 