<?php
/**
 * @author Olivier Van Hoof <ovh>
 * @author Huby Franck <symio> (refactoring + loamok integration)
 */
class Loamok_Pdf_PdfWebkit {

    protected $orientations = array(
           Loamok_Pdf_Pdf::PORTRAIT => 'Portrait',
           Loamok_Pdf_Pdf::LANDSCAPE => 'Landscape',
       );
    protected $orientation = Loamok_Pdf_Pdf::PORTRAIT;
    protected $url;
    protected $margins;

    public function __construct($url, $orientation, $margins) {
        $this->url = $url;
        $this->orientation = $orientation;
        $this->margins = $margins;
    }

    protected function getOrientation() {
        return $this->orientations[$this->orientation];
    }

    public function render($filename) {
        $config = Zend_Registry::get('config');
        if (is_array($this->url)) {
            $url = implode(' ', $this->url);
        } else {
            $url = $this->url;
        }
        $cmd = $config['library']['wkhtmltopdf']['path'].'/'.$config['library']['wkhtmltopdf']['cmd'].' --quiet --page-size A4 --margin-top '.$this->margins['top'].' --margin-right '.$this->margins['right'].' --margin-bottom '.$this->margins['bottom'].' --margin-left '.$this->margins['left'].' --orientation '.$this->getOrientation().' '.$url.' '.$filename;
        exec($cmd);
    }

}
