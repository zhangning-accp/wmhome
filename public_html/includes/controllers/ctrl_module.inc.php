<?php

  class ctrl_module {
    private $_module;
    public $data;

    public function __construct($module_id) {

      if (empty($module_id)) trigger_error('First argument module_id cannot be empty', E_USER_ERROR);

      preg_match('#^([^_]+)#', $module_id, $matches);

      switch ($matches[1]) {
        case 'cm':
          $type = 'customer';
          break;
        case 'sm':
          $type = 'shipping';
          break;
        case 'pm':
          $type = 'payment';
          break;
        case 'om':
          $type = 'order';
          break;
        case 'ot':
          $type = 'order_total';
          break;
        case 'job':
          $type = 'job';
          break;
        default:
          trigger_error('Unknown module type for module '. $module_id, E_USER_ERROR);
      }

      $this->load($module_id, $type);
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_MODULES .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $this->data['settings'] = array();
    }

    public function load($module_id, $type) {

      $this->reset();

      $modules_query = database::query(
        "select * from ". DB_TABLE_MODULES ."
        where type = '". database::input($type) ."'
        and module_id = '". database::input($module_id) ."'
        limit 1;"
      );

      if ($module = database::fetch($modules_query)) {
        $this->data = array_replace($this->data, array_intersect_key($module, $this->data));
      }

      $this->_module = new $module_id;
      $this->data['module_id'] = $module_id;
      $this->data['type'] = $type;

      $this->data['settings'] = $this->_decode_settings($this->data['settings']);

      foreach ($this->_module->settings() as $structure) {

        if (substr($structure['function'], 0, 8) == 'regional') {
          foreach (array_keys(language::$languages) as $language_code) {
            if (!isset($this->data['settings'][$structure['key']][$language_code])) $this->data['settings'][$structure['key']][$language_code] = $structure['default_value'];
          }

        } else {
          if (!isset($this->data['settings'][$structure['key']])) $this->data['settings'][$structure['key']] = $structure['default_value'];
        }
      }
    }

    public function save() {

      if (empty($this->data['id'])) {

        if (method_exists($this->_module, 'uninstall')) {
          $this->_module->uninstall();
        }

        database::query(
          "insert into ". DB_TABLE_MODULES ."
          (module_id, type, date_created)
          values ('". database::input($this->data['module_id']) ."', '". database::input($this->data['type']) ."', '". date('Y-m-d H:i:s') ."');"
        );

        $this->data['id'] = database::insert_id();

        if (method_exists($this->_module, 'install')) {
          $this->_module->install();
        }

      } else {

        if (method_exists($this->_module, 'update')) {
          $this->_module->update();
        }
      }

      if (isset($this->data['settings']['status']) && in_array(strtolower($this->data['settings']['status']), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
        $this->data['status'] = 1;
      } else {
        $this->data['status'] = 0;
      }

      $this->data['priority'] = (int)$this->data['settings']['priority'];

      database::query(
        "update ". DB_TABLE_MODULES ."
        set
          module_id = '". database::input($this->data['module_id']) ."',
          type = '". database::input($this->data['type']) ."',
          status = '". (int)$this->data['status'] ."',
          priority = '". (int)$this->data['priority'] ."',
          settings = '". database::input($this->_encode_settings($this->data['settings'])) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)database::input($this->data['id']) ."'
        limit 1;"
      );

      cache::clear_cache('modules');
    }

    public function delete() {

      if (method_exists($this->_module, 'uninstall')) {
        $this->_module->uninstall();
      }

      database::query(
        "delete from ". DB_TABLE_MODULES ."
        where module_id = '". database::input($this->data['module_id']) ."'
        limit 1;"
      );

      cache::clear_cache('modules');
    }

    private function _encode_settings($data) {

      language::convert_characters($data, 'UTF-8', language::$selected['charset']);

      return json_encode($data);
    }

    private function _decode_settings($data) {

      if (empty($data)) return;

      $data = json_decode($data, true);

      language::convert_characters($data, language::$selected['charset'], 'UTF-8');

      return $data;
    }
  }
