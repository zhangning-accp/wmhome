<?php
  $defined_tables = array();
  foreach (get_defined_constants() as $constant) {
    if (preg_match('#^`'. preg_quote(DB_DATABASE, '#') .'`\.`(.*)`$#', $constant, $matches)) {
      $defined_tables[] = $matches[1];
    }
  }

  if (isset($_POST['convert'])) {

    try {
      if (empty($_POST['tables'])) throw new Exception(language::translate('error_must_select_tables', 'You must select tables'));

      $_POST['collation'] = preg_replace('#[^a-z0-9_]#', '', $_POST['collation']);

      if (!empty($_POST['set_database_default'])) {
        database::query("alter database `". DB_DATABASE ."` default character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ." collate = ". database::input($_POST['collation']) .";");
      }

      foreach ($_POST['tables'] as $table) {
        database::query("alter table `". DB_DATABASE ."`.`". $table ."` convert to character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ." collate ". database::input($_POST['collation']) .";");
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (empty($_POST)) $_POST['tables'] = $defined_tables;
?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_storage_encoding', 'Storage Encoding'); ?></h1>

<p><?php echo language::translate('description_set_mysql_collation', 'This will recursively convert the charset and collation for all selected database tables and belonging columns.'); ?></p>

<?php echo functions::form_draw_form_begin('mysql_collation_form', 'post', false, false, 'style="width: 640px;"'); ?>

  <div class="form-group">
    <label><?php echo language::translate('title_database_tables', 'Database Tables'); ?></label>
<?php
  $options = array();

  $tables_query = database::query(
    "select * from `information_schema`.`TABLES`
    where TABLE_SCHEMA = '". DB_DATABASE ."';"
  );

  while ($table = database::fetch($tables_query)) {
    if (in_array($table['TABLE_NAME'], $defined_tables)) {
      $options[] = array($table['TABLE_NAME'] .' -- '. $table['TABLE_COLLATION'], $table['TABLE_NAME']);
    }
  }

  echo functions::form_draw_select_field('tables[]', $options, true, true, 'style="height: 200px;"');
?>

  </div>

  <div class="form-group">
    <label><?php echo language::translate('title_collation', 'Collation'); ?></label>
    <?php echo functions::form_draw_mysql_collations_list('collation'); ?>
  </div>

  <div class="form-group">
    <div class="checkbox">
      <label><?php echo functions::form_draw_checkbox('set_database_default', 'true'); ?> <?php echo language::translate('text_also_set_as_database_default', 'Also set as database default (when new tables are created)'); ?></label>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('convert', language::translate('title_convert', 'Convert'), 'submit'); ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>