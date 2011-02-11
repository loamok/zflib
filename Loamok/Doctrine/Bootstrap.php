<?php
/*
*/
class Loamok_Doctrine_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function initDoctrine() {
        set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../library/Doctrine'),
            get_include_path(),
        )));
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Doctrine_');
        $autoloader->registerNamespace('Doctrine');

        $this->getApplication()->getAutoloader()
            ->pushAutoloader(array('Doctrine', 'autoload'));
        spl_autoload_register(array('Doctrine', 'modelsAutoload'));

        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
        $manager->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER, true);
        $manager->setAttribute(
          Doctrine::ATTR_MODEL_LOADING,
          Doctrine::MODEL_LOADING_CONSERVATIVE
        );
        $manager->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);

        $doctrineConfig = $this->getOption('doctrine');

        Doctrine::loadModels($doctrineConfig['models_path']);

        $conn = Doctrine_Manager::connection($doctrineConfig['dsn'],'doctrine');
        $conn->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);
        //on définit la sortie encodée en UTF-8
        $conn->setCharset('utf8');
        $conn->setCollate('utf8_general_ci');
        return $conn;
    }

}
