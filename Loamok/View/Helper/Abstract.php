<?php

class Loamok_View_Helper_Abstract extends Zend_View_Helper_Abstract {

    // METHODES / METHODS
    /**
     * Méthode à copier dans la classe dérivante
     * Pour la Résolution statique à la volée
     * -----------------------------------------
     * Method to copy in the candidate class
     * For the Late Static Bindings
     * -----------------------------------------
     *
     * @return String
     */
    public static function className() {
        return __CLASS__;
    }

    /**
     * Enregistrement automatique des aides de vue
     * -------------------------------------------
     * Auto registering of the view helpers
     * -------------------------------------------
     *
     * @param String $configPath
     * @param Loamok_View_Smarty $view
     */
    public static function selfBootstrap($configPath, $view) {
        /*
         * Nouvelle instance de la classe en cours
         * ---------------------------------------
         * New instance of the class being
         */
        $class = static::className();
        $layoutRessources = new $class($configPath);

        $conf = $layoutRessources->getConfig();
        /*
         * Liste des méthodes de la classe
         * -------------------------------
         * List class methods
         */
        $methods = get_class_methods($layoutRessources);
        $cMethods = $methods;

        if(isset($conf->global->usablehelpers)) {
            $methods = $conf->global->usablehelpers;
        }

        foreach ($methods as $method) {
            if(preg_match('/^l_/', $method) and in_array($method, $cMethods)) {
                $view->registerHelper($layoutRessources, $method);
            }
        }
    }

}