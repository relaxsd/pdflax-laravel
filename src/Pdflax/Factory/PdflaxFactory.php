<?php

namespace Pdflax\Factory;

use Anouar\Fpdf\Fpdf;
use Pdflax\Contracts\PdfCreator;
use Pdflax\Contracts\PdfDocumentInterface;
use Pdflax\Fpdf\FpdfDocumentAdapter;

class PdflaxFactory implements PdfCreator
{

    // Maps Pdflax constants to the corresponding FPdf values
    private static $OPTION_MAPPINGS = [
        // Orientation
        PdfCreator::ORIENTATION_LANDSCAPE => 'L',
        PdfCreator::ORIENTATION_PORTRAIT  => 'P',

        // Size
        PdfCreator::SIZE_A4               => 'A4',

        // Units
        PdfCreator::UNIT_CM               => 'cm',
        PdfCreator::UNIT_INCH             => 'in',
        PdfCreator::UNIT_MM               => 'mm',
        PdfCreator::UNIT_PT               => 'pt',
    ];

    protected static $DEFAULTS = [
        'orientation' => PdfCreator::ORIENTATION_PORTRAIT,
        'unit'        => PdfCreator::UNIT_MM,
        'size'        => PdfCreator::SIZE_A4,
    ];

    /**
     * Create a PDF document
     *
     * @param array|null $options
     *
     * @return PdfDocumentInterface
     */
    public function create($options = [])
    {
        // TODO: Support different implementations
        return self::createFpdf($options);
    }

    /**
     * @param array $options
     *
     * @return PdfDocumentInterface
     */
    public static function createFpdf($options = [])
    {
        // Merge with defaults
        $options = array_merge(self::$DEFAULTS, $options);

        // Convert from Pdflax to FPdf
        $options = self::convertOptions($options);

        // Create the FPdf instance
        $fpdf = new Fpdf($options['orientation'], $options['unit'], $options['size']);

        // Set leftmargin if given in the options
        if (array_key_exists('margin-left', $options)) {
            $fpdf->SetLeftMargin($options['margin-left']);
        }

        // Additional settings can be put on the 'raw' object later on.

        return new FpdfDocumentAdapter($fpdf);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private static function convertOptions(array $options)
    {
        return array_map(function ($optionValue) {
            return self::convertOption($optionValue);
        }, $options);
    }

    /**
     * @param mixed $optionValue
     *
     * @return mixed
     */
    private static function convertOption($optionValue)
    {
        if (is_string($optionValue) && array_key_exists($optionValue, self::$OPTION_MAPPINGS)) {
            return self::$OPTION_MAPPINGS[$optionValue];
        }

        return $optionValue;
    }
}
