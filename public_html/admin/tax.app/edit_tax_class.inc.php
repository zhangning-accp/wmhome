<?php

  if (!empty($_GET['tax_class_id'])) {
    $tax_class = new ctrl_tax_class($_GET['tax_class_id']);
  } else {
    $tax_class = new ctrl_tax_class();
  }

  if (empty($_POST)) {
    foreach ($tax_class->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($tax_class->data['id']) ? language::translate('title_edit_tax_class', 'Edit Tax Class') : language::translate('title_add_new_tax_class', 'Add New Tax Class'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      $fields = array(
        'code',
        'name',
        'description',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $tax_class->data[$field] = $_POST[$field];
      }

      $tax_class->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link('', array('doc' => 'tax_classes'), true, array('tax_class_id')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($tax_class->data['id'])) throw new Exception(language::translate('error_must_provide_tax_class', 'You must provide a tax class'));

      $tax_class->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link('', array('doc' => 'tax_classes'), true, array('tax_class_id')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

?>
<h1><?php echo $app_icon; ?> <?php echo !empty($tax_class->data['id']) ? language::translate('title_edit_tax_class', 'Edit Tax Class') : language::translate('title_add_new_tax_class', 'Add New Tax Class'); ?></h1>

<?php echo functions::form_draw_form_begin('tax_class_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_code', 'Code'); ?></label>
      <?php echo functions::form_draw_text_field('code', true); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_name', 'Name'); ?></label>
      <?php echo functions::form_draw_text_field('name', true); ?>
    </div>
  </div>

  <div class="form-group">
    <label><?php echo language::translate('title_description', 'Description'); ?></label>
    <?php echo functions::form_draw_text_field('description', true); ?>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($tax_class->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>