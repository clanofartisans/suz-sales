<?php

namespace App;

use \Exception;
use App\InfraItem;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelDoc
{
    public $workbook;
    public $worksheet;
    public $dataCols;
    public $startRow;

    /*
     * Load the file, find the KeHE worksheet,
     * then find the row with data headers,
     * and the first row of actual data.
     */
   function __construct($filename)
   {
       $this->loadWorkbook($filename);
       $this->loadKeHEWorksheet();
       $this->assignDataHeaders();
   }

   /*
    * Load the actual Excel file.
    */
    protected function loadWorkbook($filename)
    {
        $path = storage_path('app/' . $filename);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $workbook = $reader->load($path);

        $this->workbook = $workbook;
    }

    /*
     * Locate the KeHE worksheet.
     */
    public function loadKeHEWorksheet()
    {
        // Get all worksheet names
        $worksheets = $this->workbook->getSheetNames();

        $keheWorksheet = null;

        // Find the KeHE worksheet in the list of worksheet names
        foreach($worksheets as $worksheet) {
            if(strpos($worksheet, config('infra.sheetname')) !== false) {
                $keheWorksheet = $this->workbook->getSheetByName($worksheet);
            }
        }

        // If we found the KeHE worksheet
        if($keheWorksheet instanceof Worksheet) {
            $this->worksheet = $keheWorksheet;

            return true;
        }

        throw new Exception("There was an error while processing the Excel file. Couldn't find the KeHE worksheet.");
    }

    /*
     * Find the row with the data headers we want
     * to use and make them easy to reference.
     */
    public function assignDataHeaders()
    {
        $dataHeaderRow = $this->findDataHeaderRow();

        $cellIterator = $dataHeaderRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        foreach($cellIterator as $cell) {
            if($cell->getValue() === config('infra.header.upc')) {
                $this->dataCols['upc'] = $cell->getColumn();
            }
            if($cell->getValue() === config('infra.header.brand')) {
                $this->dataCols['brand'] = $cell->getColumn();
            }
            if($cell->getValue() === config('infra.header.desc')) {
                $this->dataCols['desc'] = $cell->getColumn();
            }
            if($cell->getValue() === config('infra.header.size')) {
                $this->dataCols['size'] = $cell->getColumn();
            }
            if($cell->getValue() === config('infra.header.price')) {
                $this->dataCols['price'] = $cell->getColumn();
            }
        }
    }

    /*
     * Find the row with the data headers we're interested in.
     */
    public function findDataHeaderRow()
    {
        foreach($this->worksheet->getRowIterator() as $row) {

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach($cellIterator as $cell) {
                if($cell->getValue() === 'Flyer CT $') {

                    $this->startRow = ($row->getRowIndex() + 1);

                    return $row;
                }
            }
        }

        throw new Exception("There was an error while processing the Excel file. Couldn't find the row containing the data headers.");
    }

    /*
     * Loop through all the data rows converting
     * each row into an InfraItem object that
     * we'll then persist to the database.
     */
    public function prepareAndSaveItemData($infraSheetID)
    {
        $data = [];

        foreach($this->worksheet->getRowIterator() as $row) {

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            if($row->getRowIndex() >= $this->startRow)
            {
                $newRow = [];
                $newRow['upc']   = $this->worksheet->getCell(($this->dataCols['upc'] . $row->getRowIndex()))->getValue();
                $newRow['brand'] = $this->worksheet->getCell(($this->dataCols['brand'] . $row->getRowIndex()))->getValue();
                $newRow['size']  = $this->worksheet->getCell(($this->dataCols['size'] . $row->getRowIndex()))->getValue();
                $newRow['price'] = $this->worksheet->getCell(($this->dataCols['price'] . $row->getRowIndex()))->getValue();
                $newRow['desc']  = $this->cleanDesc($this->worksheet->getCell(($this->dataCols['desc'] . $row->getRowIndex()))->getValue());

                if($newRow['upc'] !== null) {
                    $data[] = $newRow;
                }
            }
        }

        $this->saveItemData($data, $infraSheetID);
    }

    /*
     * Create an InfraItem using the provided data.
     */
    public function saveItemData($data, $infraSheetID)
    {
        foreach($data as $item)
        {
            InfraItem::create(['infrasheet_id'   => $infraSheetID,
                               'upc'             => $this->zeroPadUPC($item['upc']),
                               'brand'           => $item['brand'],
                               'brand_uc'        => strtoupper($item['brand']),
                               'desc'            => $item['desc'],
                               'size'            => $item['size'],
                               'list_price'      => $this->fixPrecisionInfraSalePrice($item['price']),
                               'list_price_calc' => $this->calcInfraSalePrice($item['price']),
                               'approved'        => false,
                               'processed'       => false,
                               'imaged'          => false,
                               'printed'         => false]);
        }
    }

    /*
     * Pad a UPC to be a standard 12 digits long.
     */
    public function zeroPadUPC($upc)
    {
        return sprintf('%012d', $upc);
    }

    /*
     * Made a price show two digits after the decimal point if needed.
     */
    public function fixPrecisionInfraSalePrice($price)
    {
        if(!empty($price) && !is_string($price)) {

            return number_format($price, 2);
        }

        return $price;
    }

    /*
     * Examine and set the type of sale price based
     * on the price given in the INFRA workbook.
     */
    public function calcInfraSalePrice($price)
    {
        if(!empty($price)) {
            if(strpos($price, '/') !== false) {
				$price = rtrim($price);
                $pieces    = explode('/', $price);
                $pieces[1] = ltrim($pieces[1], '$');

                $priceCalc = $pieces[1] / (float) $pieces[0];

                return number_format($priceCalc, 2);
            } else {
                if(!is_string($price)) {
                    return number_format($price, 2);
                }
            }
        }
        return '20%';
    }

    public function cleanDesc($desc)
    {
        if(preg_match("/\xc2\xae/u", $desc, $result)) {

            // Ends with character
            if(preg_match("/\xc2\xae$/u", $desc, $result)) {

                $clean = rtrim($desc, "\xc2\xae ");

                return $clean;
            }

            // Begins with character
            if(preg_match("/^\xc2\xae/u", $desc, $result)) {

                $clean = ltrim($desc, "\xc2\xae ");

                return $clean;
            }

            // symbol immediately preceeded by non-whitespace character (delete symbol only)
            if(preg_match("/\S\xc2\xae.+/u", $desc, $result)) {

                $pattern = "/\xc2\xae/u";
                $replacement = '';

                $clean = preg_replace($pattern, $replacement, $desc);

                return $clean;
            }

            // symbol surrounded on either side by whitespace character (replace symbol and all surrounding whitespace with one space)
            if(preg_match("/\s+\xc2\xae\s+/u", $desc, $result)) {

                $pattern = "/\s+\xc2\xae\s+/u";
                $replacement = ' ';

                $clean = preg_replace($pattern, $replacement, $desc);

                return $clean;
            }
        }

        return $desc;
    }
}
