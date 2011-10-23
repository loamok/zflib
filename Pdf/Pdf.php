<?php
/**
 * @author Olivier Van Hoof <ovh>
 * @author Huby Franck <symio> (refactoring + loamok integration)
 */
class Loamok_Pdf_Pdf {

    protected $url;
    protected $template=null;
    protected $orientation = self::PORTRAIT;
    protected $margins = array(
           'top' => '20',
           'left' => '20',
           'right' => '20',
           'bottom' => '20',
       );
    protected $header;
    protected $footer;
    protected $files;

    const PORTRAIT  = 0;
    const LANDSCAPE = 1;
    const LEFT      = 2;
    const CENTER    = 3;
    const RIGHT     = 4;

    public function __construct($param) {
        // sauvegarde la session en fichier temporaire
        $sessionFile = $this->storeSession();
        $config = Zend_Registry::get('config');
        // affectation des paramètres
        if (!array_key_exists('url', $param)) {
            throw new Zend_Exception("No URL given");
        }
        if (!array_key_exists('orientation', $param)) {
            if(!array_key_exists('orientation', $config->library->loamok->pdf)) {
                $param['orientation'] = self::PORTRAIT;
            } else {
                $param['orientation'] = $config->library->loamok->pdf->orientation;
            }
        }
        if (($param['orientation'] != self::PORTRAIT and $param['orientation'] != self::LANDSCAPE)) {
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
            $this->template = $config->library->loamok->pdf->template->pdf.'/'.$param['template'];
        }
        if (array_key_exists('header', $param) and is_array($param['header'])) {
            $this->header = $param['header'];
        }
        if (array_key_exists('footer', $param) and is_array($param['footer'])) {
            $this->footer = $param['footer'];
        }

    }

    protected function storeSession() {
        if(isset ($sessionFile)) {
            if (file_exists($sessionFile)) {
                unlink($sessionFile);
            }
        }
        $config = Zend_Registry::get('config');
        $sessionFile = tempnam($config->library->loamok->pdf->temp.'/', 'pdfcontext-');
        $this->files[$sessionFile] = $sessionFile;
        if(!isset ($_SESSION)) {
            session_start();
        }
        file_put_contents($sessionFile, serialize($_SESSION));
        return $sessionFile;
    }

    public static function loadSession($sessionFile) {
        $config = Zend_Registry::get('config');
        $sessionFile = $config->library->loamok->pdf->temp.'/'.$sessionFile;
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
        $tempContentFile = tempnam($config->library->loamok->pdf->temp.'/', 'pdf-');
        $this->files[$tempContentFile] = $tempContentFile;
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
        $this->unlinkAll();
    }

    protected function unlinkAll() {
        foreach($this->files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
