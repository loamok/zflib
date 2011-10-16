<?php
/**
 * @author Olivier Van Hoof <ovh>
 * @author Huby Franck <symio> (refactoring + loamok integration)
 */
require_once(APPLICATION_PATH .'/../library/tcpdf/config/lang/eng.php');
require_once(APPLICATION_PATH .'/../library/tcpdf/tcpdf.php');
require_once(APPLICATION_PATH .'/../library/fpdi/fpdi.php');

class Loamok_Pdf_PdfTemplate extends FPDI {

    protected $orientations = array(
            Loamok_Pdf_Pdf::PORTRAIT => 'P',
            Loamok_Pdf_Pdf::LANDSCAPE => 'L',
        );
    protected $alignments = array(
            Loamok_Pdf_Pdf::LEFT => 'L',
            Loamok_Pdf_Pdf::CENTER => 'C',
            Loamok_Pdf_Pdf::RIGHT => 'R',
        );
    protected $orientation;
    protected $margins;
    protected $content;
    protected $template;
    protected $header;
    protected $footer;
    protected $templateId;
    protected $contentId;

    public function __construct($content, $orientation, $margins, $header, $footer, $template = '') {
    	$this->content = $content;
        $this->template = $template;
        $this->orientation = $orientation;
        $this->margins = $margins;
        $this->header = $header;
        $this->footer = $footer;
        parent::__construct();
    }

    protected function getOrientation() {
        return $this->orientations[$this->orientation];
    }

    protected function getAlignment($align) {
    	if (array_key_exists($align, $this->alignments)) return $this->alignments[$align];
    	else return false;
    }

    protected function showBlock($block, $isHeader = true) {
    	$content = str_replace('{NumPage}', $this->getAliasNumPage(), $block['content']);
    	$content = str_replace('{CountPages}', $this->getAliasNbPages(), $content);
    	if (is_array($block['position'])) {
            $this->SetXY($block['position'][0], $block['position'][1]);
    	} else {
            // positionnement par défaut si aucune coordonnées fournies
            if ($isHeader) {
                $this->SetXY($this->margins['left'], $this->margins['top']);
            } else {
                $this->SetXY($this->margins['left'], -($this->margins['bottom']));
            }
    	}
    	$this->SetFont('Helvetica', '', 7);
    	$align = $this->getAlignment($block['align']);
    	// alignement centré par défaut si aucune précision fournie
    	if ($align === false) {
            $align = $this->getAlignment(Loamok_Pdf_Pdf::CENTER);
        }
    	$this->Cell(0, 0, $content, 0, 0, $align);
    }

    public function Header() {
    	// application des templates pour chaque page
    	if (isset($this->templateId) and $this->templateId > 0) {
            $this->useTemplate($this->templateId);
        }
        if ($this->contentId > 0) {
            $this->useTemplate($this->contentId);
        }
        // ajout d'un header le cas échéant
    	if (count($this->header) > 0) {
            $this->showBlock($this->header);
        }
    }

    public function Footer() {
    	if (count($this->footer) > 0) {
            $this->showBlock($this->footer, false);
        }
    }

    public function render() {
    	$this->SetPageFormat('A4');
    	$this->SetPageOrientation($this->getOrientation(), false, $this->margins['bottom']);
    	$this->SetMargins($this->margins['left'], $this->margins['top'], $this->margins['right']);
    	// import de la page unique du template
    	if (!empty($this->template)) {
            $tplPages = $this->setSourceFile($this->template);
            if ($tplPages > 0) {
                $this->templateId = $this->importPage(1);
            }
    	}
        // import du contenu
    	$pagesContent = $this->setSourceFile($this->content);
        for ($i = 1; $i <= $pagesContent; $i++) {
            $this->contentId = $this->importPage($i);
            // la fusion des 2 PDF se fera dans la méthode Header() appelée automatiquement par AddPage
            $this->AddPage();
        }
    }

}
