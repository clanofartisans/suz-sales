<?php

namespace App;

use Excel;
use \Exception;
use App\InfraItem;

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
       $this->findDataStartRow();
   }

   /*
    * Load the actual Excel file.
    */
    protected function loadWorkbook($filename)
    {
        $workbook = Excel::load('storage/app/'.$filename);

        $this->workbook = $workbook->noHeading();
    }

    /*
     * Locate the KeHE worksheet.
     */
    public function loadKeHEWorksheet()
    {
        foreach($this->workbook->get() as $sheet)
        {
            if($sheet->getTitle() == config('infra.sheetname')) {

                $this->worksheet = $sheet;

                return true;
            }
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

        foreach($dataHeaderRow as $cellNum => $cellValue) {

            if($cellValue === config('infra.header.upc')) {
                $this->dataCols['upc'] = $cellNum;
            }
            if($cellValue === config('infra.header.brand')) {
                $this->dataCols['brand'] = $cellNum;
            }
            if($cellValue === config('infra.header.desc')) {
                $this->dataCols['desc'] = $cellNum;
            }
            if($cellValue === config('infra.header.size')) {
                $this->dataCols['size'] = $cellNum;
            }
            if($cellValue === config('infra.header.price')) {
                $this->dataCols['price'] = $cellNum;
            }
        }
    }

    /*
     * Find the row with the data headers we're interested in.
     */
    public function findDataHeaderRow()
    {
        foreach($this->worksheet as $row) {

            foreach($row as $cell) {

                if($cell === 'Flyer CT $') {

                    return $row;
                }
            }
        }

        throw new Exception("There was an error while processing the Excel file. Couldn't find the row containing the data headers.");
    }

    /*
     * Find the first row of actual data.
     */
    public function findDataStartRow()
    {
        foreach($this->worksheet as $rowNum => $rowValue) {

            foreach($rowValue as $cell) {

                if($cell === 'Flyer CT $') {

                    $this->startRow = ($rowNum + 2);

                    return true;
                }
            }
        }

        throw new Exception("There was an error while processing the Excel file. Couldn't find the first row of product data.");
    }

    /*
     * Loop through all the data rows converting
     * each row into an InfraItem object that
     * we'll then persist to the database.
     */
    public function prepareAndSaveItemData($infraSheetID)
    {
        $data = [];
        foreach($this->worksheet as $rowNum => $rowVal)
        {
            if($rowNum >= $this->startRow)
            {
                $newRow = [];
                foreach($rowVal as $cellNum => $cellVal)
                {
                    if($cellNum === $this->dataCols['upc']) {
                        $newRow['upc'] = $cellVal;
                    }
                    if($cellNum === $this->dataCols['brand']) {
                        $newRow['brand'] = $cellVal;
                    }
                    if($cellNum === $this->dataCols['desc']) {
                        $newRow['desc'] = $this->cleanDesc($cellVal);
                    }
                    if($cellNum === $this->dataCols['size']) {
                        $newRow['size'] = $cellVal;
                    }
                    if($cellNum === $this->dataCols['price']) {
                        $newRow['price'] = $cellVal;
                    }
                }
                $data[] = $newRow;
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
