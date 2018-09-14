<?php

namespace Pdflax\Fpdf;

use Anouar\Fpdf\Fpdf;
use Pdflax\Contracts\PdfDocumentInterface;
use Pdflax\PdfDOMTrait;
use Pdflax\PdfStyleTrait;

class FpdfDocumentAdapter implements PdfDocumentInterface
{

    use PdfDOMTrait;
    use PdfStyleTrait;

    protected $stylesheet = [
        // Inherited by all other styles
        'DEFAULT' => [
            'font-family' => 'Arial',
            'font-style'  => '',
            'font-size'   => 11,
            'text-color'  => [0, 0, 0], // black
        ],

        // Used for all cells, including block, p, h1, h2
        'cell'    => [
            // FPdf default for cell()
            'align'     => 'L',
            'border'    => 0,
            'fill'      => 0,
            'link'      => '',
            'multiline' => false,
            'ln'        => 0,
        ],

        'block' => [
        ],

        // The paragraph type.
        // Inherits from DEFAULT, 'cell' and 'block'
        'p' => [
            'align'     => 'L',
            'ln'        => 2,    // FPdf always uses Ln=2 for MultiCell. Important for correctly recognizing page breaks.
            'multiline' => true, // Uses MultiCell
        ],

        // Heading 1 type
        // Inherits from DEFAULT, 'cell' and 'block'
        'h1' => [
            'font-style' => 'B',
            'font-size'  => 14,

            'ln'    => 2,
            'align' => 'L',
        ],

        // Heading 2 type
        // Inherits from DEFAULT, 'cell' and 'block'
        'h2' => [
            'font-style' => 'B',
            'font-size'  => 12,
            'ln'         => 2,
            'align'      => 'L',
        ],

        '.align-right' => [
            'align' => 'R',
        ],

    ];

    protected static $COLORS = [
        'black' => [0, 0, 0],
        'white' => [255, 255, 255],
        'red'   => [255, 0, 0],
    ];

    /**
     * @var Fpdf
     */
    protected $fpdf;

    /**
     * FpdfDocumentAdapter constructor.
     *
     * @param Fpdf       $fpdf
     */
    public function __construct(Fpdf $fpdf)
    {
        $this->fpdf   = $fpdf;
    }

    public function setFont($family, $style = '', $size = 0)
    {
        $this->fpdf->SetFont($family, $style, $size);

        return $this;
    }

    /**
     * @param string|int|array $r Red value (with $g and $b) or greyscale value ($g and $b null) or color name or [r,g,b] array
     * @param int|null         $g Green value
     * @param int|null         $b Blue value
     *
     * @return mixed
     */
    public function setDrawColor($r, $g = null, $b = null)
    {
        list ($r, $g, $b) = $this->getRGB($r, $g, $b);
        $this->fpdf->SetDrawColor($r, $g, $b);

        return $this;
    }

    /**
     * @param string|int|array $r Red value (with $g and $b) or greyscale value ($g and $b null) or color name or [r,g,b] array
     * @param int|null         $g Green value
     * @param int|null         $b Blue value
     *
     * @return mixed
     */
    public function setTextColor($r, $g = null, $b = null)
    {
        list ($r, $g, $b) = $this->getRGB($r, $g, $b);
        $this->fpdf->SetTextColor($r, $g, $b);

        return $this;
    }

    /**
     * @param string|int|array $r Red value (with $g and $b) or greyscale value ($g and $b null) or color name or [r,g,b] array
     * @param int|null         $g Green value
     * @param int|null         $b Blue value
     *
     * @return mixed
     */
    public function setFillColor($r, $g = null, $b = null)
    {
        list ($r, $g, $b) = $this->getRGB($r, $g, $b);
        $this->fpdf->SetFillColor($r, $g, $b);

        return $this;
    }

    /**
     * @param string|int|array $r Red value (with $g and $b) or greyscale value ($g and $b null) or color name or [r,g,b] array
     * @param int|null         $g Green value
     * @param int|null         $b Blue value
     *
     * @return array           Array with [ reg, green, blue ] values
     */
    protected function getRGB($r, $g = null, $b = null)
    {

        if (is_array($r))
            return $r;

        if (is_string($r) && array_key_exists($r, self::$COLORS))
            return self::$COLORS[$r];

        if (is_int($r))
            return isset($g) ? [$r, $g, $b] : [$r, $r, $r];

        throw new \LogicException('Unsupported color configuration');
    }

    /**
     * @param float|string $w
     * @param float        $h
     * @param string       $txt
     * @param array|string $options
     */
    public function cell($w, $h = 0.0, $txt = '', $options = [])
    {
        // Merge the options with the defaults to be sure all fields exist.
        $options = $this->getStyle('cell', $options);

        if ($options['multiline']) {
            $this->fpdf->MultiCell($w, $h, $txt, $options['border'], $options['align'], $options['fill']);
        } else {
            $this->fpdf->Cell($w, $h, $txt, $options['border'], $options['ln'], $options['align'], $options['fill'], $options['link']);
        }
    }

    /**
     * @param     $auto
     * @param int $margin
     *
     * @return PdfDocumentInterface
     */
    public function setAutoPageBreak($auto, $margin = 0)
    {
        $this->fpdf->SetAutoPageBreak($auto, $margin);

        return $this;
    }

    /**
     * @param string $orientation
     * @param string $size
     *
     * @return PdfDocumentInterface
     */
    public function addPage($orientation = '', $size = '')
    {
        $this->fpdf->AddPage($orientation, $size);

        return $this;
    }

    /**
     * @param string $name
     * @param string $dest
     *
     * @return PdfDocumentInterface|string
     */
    public function output($name = '', $dest = '')
    {
        $result = $this->fpdf->Output($name, $dest);
        // TODO: Get rid of this stupid Fpdf interface (returning string or object)
        return ($dest == 'S') ? $result : $this;
    }

    /**
     * @param float|null $h
     *
     * @return mixed
     */
    public function newLine($h = null)
    {
        $this->fpdf->Ln($h);

        return $this;
    }

    /**
     * Get the current page number.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->fpdf->page;
    }

    /**
     * @return float
     */
    public function getX()
    {
        return 0;
    }

    /**
     * @return float
     */
    public function getY()
    {
        return 0;
    }

    /**
     * Get the width of this document in it parent.
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->fpdf->w;
    }

    /**
     * Get the height of this document in it parent.
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->fpdf->h;
    }

    /**
     * @return float
     */
    public function getCursorX()
    {
        return $this->fpdf->GetX();
    }

    /**
     * @return float
     */
    public function getCursorY()
    {
        return $this->fpdf->GetY();
    }

    /**
     * Position the 'cursor' at a given X
     *
     * @param float|string $x Local X-coordinate
     */
    public function setCursorX($x)
    {
        $this->fpdf->SetX($x);
    }

    /**
     * Position the 'cursor' at a given Y
     *
     * @param float|string $y Local Y-coordinate
     */
    public function setCursorY($y)
    {
        $this->fpdf->SetY($y);
    }

    /**
     * Position the 'cursor' at a given X,Y
     *
     * @param float|string $x Local X-coordinate
     * @param float|string $y Local Y-coordinate
     */
    public function setCursorXY($x, $y)
    {
        $this->fpdf->setXY($x, $y);
    }

    /**
     * @return float
     */
    public function getLeftMargin()
    {
        return $this->fpdf->lMargin;
    }

    /**
     * @return float
     */
    public function getRightMargin()
    {
        return $this->fpdf->rMargin;
    }

    /**
     * @return Fpdf
     */
    public function raw()
    {
        return $this->fpdf;
    }

    /**
     * @param float $value
     *
     * @return string
     */
    public function euro($value)
    {
        return chr(128) . ' ' . number_format($value, 2, ',', '.');
    }
}
