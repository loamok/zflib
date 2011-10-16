<?php

class Loamok_Doctrine_Fixtures {

    protected $_clean = false;

    protected $_models = null;

    protected $_datas = null;

    public function __construct($clean = false) {
        $this->_clean = $clean;
        if($this->_clean) {
            $tables = $this->_models;
            $dbh = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
            foreach($tables as $table) {
                $dbh->query("TRUNCATE TABLE {$table}");
            }
        }
    }

    protected function _run() {
        $config = Zend_Registry::get("config");
        foreach ($this->_datas as $k => &$datas) {
            foreach ($datas as $key => $table) {
                require_once ("{$config->doctrine->models_path}/{$k}.php");
                $objClass = "{$k}";
                $datas[$key]['object'] = new $objClass();
                $datas[$key]['object']->merge($table['values']);
                $datas[$key]['object']->save();
            }
        }

    }
}