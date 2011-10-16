<?php

require_once 'Smarty/Smarty.class.php';

/**
 * Loamok_View_Smarty
 *
 * Classe de définition de la vue
 * Cette classe utilise des paramètres dans la configuration de l'application
 * --------------------------------------------------------------------------
 * Class for definition of the view
 * This class use somme application parameters
 * --------------------------------------------------------------------------
 *
 * @uses       Smarty
 * @uses       Zend_View_Abstract
 * @uses       Zend_Controller_Action_HelperBroker
 * @category   Loamok
 * @package    Loamok_View
 * @copyright  Copyright (c) loamok.org 2010
 * @license    http://www.gnu.org/licenses/     GNU General Public License
 */
class Loamok_View_Smarty extends Zend_View_Abstract {
    // PROPRIETES / PROPERTIES
    /**
     * Objet Smarty
     * ------------
     * Smarty object
     * ------------
     *
     * @var Smarty
     */
    private $_smarty;

    // METHODES / METHODS
    /**
     * Effectue les actions nécessaires à l'enregistrement de la vue
     * -------------------------------------------------------------
     * Performs the necessary actions to register the view
     * -------------------------------------------------------------
     *
     * @param Array $options
     * @return Loamok_View_Smarty
     */
    static public function selfBootstrap($options) {
        $class = __CLASS__;
        $view = new $class($options);
        $viewRender = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRender->setView($view);
        $viewRender->setViewSuffix('phtml');
        Zend_Controller_Action_HelperBroker::addHelper($viewRender);
        return $view;
    }

    /**
     * Constructeur
     * ------------
     * Constructor
     * ------------
     *
     * @param Array $options
     */
    public function __construct($options) {
        parent::__construct($options);
        require_once $options['dir'] . "Smarty.class.php";

        $this->_smarty = new Smarty();
        foreach ($options as $key => $value) {
            if($key != "template_dir" and $key != "layout_dir") {
                $this->_smarty->$key = $value;
            }
        }
        $this->_smarty->template_dir = array($options['template_dir'], $options['layout_dir']);
        $this->assign('_view', $this);
        // Remappe $this dans la vue / Remaps $this in the view
        $this->assign('this', $this);
        $this->assign('_layout', $this->layout());
        $this->assign('debugging', $this->_smarty->debugging);
    }

    /**
     * Accesseur de l'objet Smarty
     * ------------------------------
     * Accessor for the Smarty object
     * ------------------------------
     * 
     * @return Smarty
     */
    public function getEngine() {
        return $this->_smarty;
    }

    /**
     * Surcharge Magique
     * -----------------
     * Overloading
     * -----------------
     *
     * @param String $key
     * @param Mixed $val
     */
    public function __set($key, $val) {
        $this->_smarty->assign($key, $val);
    }

    /**
     * Surcharge Magique
     * -----------------
     * Overloading
     * -----------------
     *
     * @param String $key
     * @return Mixed
     */
    public function __get($key) {
        return $this->_smarty->get_template_vars($key);
    }

    /**
     * Teste la présence d'une variable
     * --------------------------------
     * Tests for the presence of a variable
     * --------------------------------
     *
     * @param String $key
     * @return Mixed
     */
    public function __isset($key) {
        return $this->_smarty->get_template_vars($key) != null;
    }

    /**
     * Détruit une variable
     * ---------------------
     * Destroys a variable
     * ---------------------
     *
     * @param String $key
     */
    public function __unset($key) {
        $this->_smarty->clear_assign($key);
    }

    /**
     * Assigne une variable
     * --------------------
     * Assigns a variable
     * --------------------
     *
     * @param Mixed $spec
     * @param Mixed $value
     * @return null
     */
    public function assign($spec, $value=null) {
        if (is_array($spec)) {
          $this->_smarty->assign($spec);
          return;
        }
        $this->_smarty->assign($spec, $value);
    }

    /**
     * Supprime toutes les variables
     * -----------------------------
     * Clear all variables
     * -----------------------------
     */
    public function clearVars() {
        $this->_smarty->clear_all_assign();
    }

    /**
     * Traite un script de vue et renvoie le résultat.
     * -----------------------------------------------
     * Processes a view script and returns the output.
     * -----------------------------------------------
     *
     * @param String $name
     * @return String
     */
    public function render($name) {
        return $this->_smarty->fetch(strtolower($name));
    }

    /**
     * Désactive la fonction parente
     * -----------------------------
     * Disables the parent function
     * -----------------------------
     */
    public function _run() {

    }

}