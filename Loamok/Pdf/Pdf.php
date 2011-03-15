<?php
/**
 * @author Olivier Van Hoof <ovh>
 * @author Huby Franck <symio> (refactoring + loamok integration)
 */
class Loamok_Pdf_Pdf {

    protected $url;
    protected $template;
    protected $orientation = self::PORTRAIT;
    protected $margins = array(
           'top' => '20',
           'left' => '20',
           'right' => '20',
           'bottom' => '20',
       );
    protected $header;
    protected $footer;

    const PORTRAIT  = 0;
    const LANDSCAPE = 1;
    const LEFT      = 2;
    const CENTER    = 3;
    const RIGHT     = 4;

    public function __construct($param) {
        // sauvegarde la session en fichier temporaire
        $sessionFile = $this->storeSession();

        // affectation des paramètres
        if (!array_key_exists('url', $param)) {
            throw new Zend_Exception("No URL given");
        }
        if (!array_key_exists('orientation', $param) or
            ($param['orientation'] != self::PORTRAIT and $param['orientation'] != self::LANDSCAPE)) {
                throw new Zend_Exception("Invalid orientation");
        }
        // rajoute le fichier de session en paramètre d'url
        if (is_array($param['url'])) {
            for ($i = 0; $i < count($param['url']); $i++) {
                $param['url'][$i] = $param['url'][$i]."/session/".basename($sessionFile);
            }
            $this->url = $param['url'];
        } else {
            $this->url = $param['url']."/session/".basename($sessionFile);
        }
        $this->orientation = $param['orientation'];
        if (array_key_exists('margins', $param)) {
            $this->margins = $param['margins'];
        }
        if (array_key_exists('template', $param)) {
            $config = Zend_Registry::get('config');
            $this->template = $config['library']['loamok']['pdf']['template']['pdf'].'/'.$param['template'];
        }
        if (array_key_exists('header', $param) and is_array($param['header'])) {
            $this->header = $param['header'];
        }
        if (array_key_exists('footer', $param) and is_array($param['footer'])) {
        $this->footer = $param['footer'];
    }
    }

    protected function storeSession() {
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
        $config = Zend_Registry::get('config');
        $sessionFile = tempnam($config['library']['loamok']['pdf']['temp'].'/', 'pdfcontext-');
        file_put_contents($sessionFile, serialize($_SESSION));
        return $sessionFile;
    }
	
    public static function loadSession($sessionFile) {
        $config = Zend_Registry::get('config');
        $sessionFile = $config['library']['loamok']['pdf']['temp'].'/'.$sessionFile;
        if (file_exists($sessionFile)) {
            $_SESSION = unserialize(file_get_contents($sessionFile));
            // réinitialise le chargement des langues depuis le bootstrap
            //Application_Application_Bootstrap_Bootstrap::loadTranslate();
            unlink($sessionFile);
        }
    }
	
    public function output($filename) {
        // crée le contenu dans un pdf temporaire
        $config = Zend_Registry::get('config');
        $tempContentFile = tempnam($config['library']['loamok']['pdf']['temp'].'/', 'pdf-');
        $pdfWk = new Loamok_Pdf_PdfWebkit($this->url, $this->orientation, $this->margins);
        $pdfWk->render($tempContentFile);

        // mixe le pdf précédent avec le template pdf
        $pdfTemplate = new Loamok_Pdf_PdfTemplate(
                    $tempContentFile,
                    $this->orientation,
                    $this->margins,
                    $this->header,
                    $this->footer,
                    $this->template
            );
        $pdfTemplate->render();
        $pdfTemplate->Output($filename);

        // purge le fichier temporaire
        if (file_exists($tempContentFile)) {
            unlink($tempContentFile);
        }
    }
}
