<?php

/**
 * Loamok_View_Helper_LayoutRessources
 *
 * Classe de définition des aides de vue à utiliser dans un layout
 * Cette classe utilise un fichier de configuration en .ini
 * Détails : http://redmine.loamok.org/projects/zcms/wiki/Aides_de_vue
 * -------------------------------------------------------------------
 * Class for definition of view helpers to use in a layout
 * This class use an .ini configuration file
 * Details : http://redmine.loamok.org/projects/zcms/wiki/Aides_de_vue
 * -------------------------------------------------------------------
 *
 * @uses       Loamok_View_Smarty
 * @uses       Zend_Config_Ini
 * @uses       Zend_Controller_Front
 * @uses       Zend_Controller_Request_Http
 * @category   Loamok
 * @package    Loamok_View
 * @subpackage Helper
 * @copyright  Copyright (c) loamok.org 2010
 * @license    http://www.gnu.org/licenses/     GNU General Public License
 */
class Loamok_View_Helper_LayoutRessources extends Loamok_View_Helper_Abstract {
    // PROPRIETES / PROPERTIES
    /**
     * Objet de configuration
     * ----------------------
     * Configuration object
     * ----------------------
     *
     * @var Zend_Config_Ini
     */
    protected $_config = null;

    /**
     * Paramètres de la requéte
     * ------------------------
     * Request parameters
     * ------------------------
     *
     * @var Array
     */
    protected $_requestParams = null;

    // METHODES / METHODS
    /**
     * Méthode de la classe parente
     * ----------------------------
     * Method from parent class
     * ----------------------------
     * 
     * @return String
     */
    public static function className() {
        return __CLASS__;
    }
    
    /**
     * Constructeur
     * ------------
     * Constructor
     * ------------
     *
     * @param String $configPath
     */
    public function __construct($configPath = null) {
        if(!is_null($configPath)) {
            $this->setConfig($configPath);
        }
    }

    /**
     * Surcharge Magique
     * -----------------
     * Overloading
     * -----------------
     *
     * @param String $name
     * @return Mixed
     */
    public function  __get($name) {
        if(array_key_exists($name, $this->_requestParams)) {
            return $this->_requestParams[$name];
        } elseif(array_key_exists ($name, $this->_config)) {
            return $this->_config[$name];
        } elseif(array_key_exists ($name, get_object_vars($this))) {
            return $this->$name;
        } else {
            return;
        }
    }

    /**
     * Charge un objet config depuis une url
     * Retourne $this pour le chaînage des appels
     * ------------------------------------------
     * Loads a config object from an url
     * Return $this for chaining calls
     * ------------------------------------------
     *
     * @param String $configPath
     * @return Loamok_View_Helper_LayoutRessources
     */
    public function setConfig($configPath) {
        $this->_config = new Zend_Config_Ini($configPath);
        return $this;
    }

    /**
     * Accesseur de la propriété config
     * --------------------------------
     * config property accessor
     * --------------------------------
     *
     * @return Zend_Config_Ini
     */
    public function getConfig() {
        return $this->_config;
    }

    /**
     * Charge les paramètres de la requête
     * Retourne $this pour le chaînage des appels
     * ------------------------------------------
     * Retrieve request parameters
     * Return $this for chaining calls
     * ------------------------------------------
     * @return Loamok_View_Helper_LayoutRessources
     */
    protected function setRequestParams() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->_requestParams['module'] = $request->getModuleName();
        $this->_requestParams['controller'] = $request->getControllerName();
        $this->_requestParams['action'] = $request->getActionName();
        return $this;
    }

    /**
     * Aide de vue pour la balise title dans header
     * --------------------------------------------
     * View Helper for the title tag in header
     * --------------------------------------------
     *
     * @return String
     */
    public function l_headTitle() {
        $this->setRequestParams();
        $titleT = array();
        $titleT[] = $this->_config->global->apptitle;
        if($this->module != "default") {
            $titles = $this->_config->{$this->module."_headtitles"};
            if(!empty ($titles->{$this->module}->title)) {
                $titleT[] = $titles->{$this->module}->title;
            }
        } else {
            $conf = $this->_config->headtitles;
            $titles = $conf;
        }
        if(!empty ($titles->{$this->controller}->title)) {
            $titleT[] = $titles->{$this->controller}->title;
        }
        $titleT[] = $titles->{$this->controller}->{$this->action};
        $titleS = implode($titles->headtitleseparator, $titleT);
        return "<title>{$titleS}</title>";
    }

    /**
     * Aide de vue pour la piste de navigation
     * ---------------------------------------
     * View Helper for the breadcrumb
     * ---------------------------------------
     *
     * @return <type>
     */
    public function l_breadCrumb() {
        $this->setRequestParams();
        $bcT = array();
        $bcT[] = "<a href=\"/\" class=\"links\">{$this->_config->global->apptitle}</a>";
        if($this->module != "default") {
            $breadcrumbs = $conf->{$this->module."_breadcrumbs"};
            if(!empty ($breadcrumbs->{$this->module}->title)) {
                $bcT[] = "<a href=\"/{$this->module}\" class=\"links\">{$breadcrumbs->{$this->module}->title}</a>";
            }
            $mHref = "{$this->module}/";
        } else {
            $conf = $this->_config->breadcrumbs;
            $breadcrumbs = $conf;
            $mHref = "";
        }
        if(!empty ($breadcrumbs->{$this->controller}->title)) {
            $bcT[] = "<a href=\"/{$mHref}{$this->controller}\" class=\"links\">{$breadcrumbs->{$this->controller}->title}</a>";
        }
        $bcT[] = "<a href=\"/{$mHref}{$this->controller}/{$this->action}\" class=\"linksCurent\">{$breadcrumbs->{$this->controller}->{$this->action}}</a>";
        $bcS = implode("<span>&nbsp;{$breadcrumbs->breadcrumbseparator}&nbsp;</span>", $bcT);
        return "{$bcS}";
    }
}
