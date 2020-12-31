<?php

namespace App;

use App\Exceptions\InfraFileTestException;
use App\Jobs\ParseInfraSheet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Workbook;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @property int     id
 * @property int     month
 * @property int     year
 * @property string  filename
 * @property string  created_at
 * @property string  updated_at
 */
class InfraSheet extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The items that belong to this INFRA sheet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany('App\ItemSale');
    }

    /**
     * Creates a "formatted_date" property that displays like "December 2020".
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return Carbon::create($this->year, $this->month)->format('F Y');
    }

    /**
     * Attempt to make an InfraSheet from the upload form.
     *
     * @param UploadedFile $file
     * @param string $month
     * @param string $year
     * @return InfraSheet
     * @throws InfraFileTestException
     */
    public static function makeFromUpload(UploadedFile $file, string $month, string $year): self
    {
        $infrasheet = self::make(['filename' => $file,
                                  'month'    => $month,
                                  'year'     => $year]);

        if (self::testInfraFile($file)) {
            $infrasheet->filename = $file->storeAs('infrasheets', time().'.xls');
            $infrasheet->save();
        }

        return $infrasheet;
    }

    /**
     * Test a file to ensure it's an INFRA file that we can parse.
     *
     * @param string $file
     * @return bool
     * @throws InfraFileTestException
     */
    public static function testInfraFile(string $file): bool
    {
        $test = self::make();

        if (!file_exists($file)) {
            throw new InfraFileTestException('Could not find the file.');
        }

        if (!($workbook = $test->loadWorkbook($file))) {
            throw new InfraFileTestException("Could not open the file. Are you sure it's a valid document?");
        }

        if (!($worksheet = $test->loadWorksheet($workbook))) {
            throw new InfraFileTestException('Could not find the KeHE worksheet. Maybe INFRA has changed their formatting?');
        }

        if (!($dataHeaderRow = $test->findDataHeaderRow($worksheet))) {
            throw new InfraFileTestException('Could not find the row containing the data headers when searching for ['.config('infra.header.price').']. Maybe INFRA has changed their formatting?');
        }

        $dataColumns = [];

        if (!($dataColumns['brand'] = $test->findBrandColumn($dataHeaderRow))) {
            throw new InfraFileTestException('Could not find the brand column ['.config('infra.header.brand').']. Maybe INFRA has changed their formatting?');
        }

        if (!($dataColumns['desc'] = $test->findDescColumn($dataHeaderRow))) {
            throw new InfraFileTestException('Could not find the description column ['.config('infra.header.desc').']. Maybe INFRA has changed their formatting?');
        }

        if (!($dataColumns['price'] = $test->findPriceColumn($dataHeaderRow))) {
            throw new InfraFileTestException('Could not find the price column ['.config('infra.header.price').']. Maybe INFRA has changed their formatting?');
        }

        if (!($dataColumns['size'] = $test->findSizeColumn($dataHeaderRow))) {
            throw new InfraFileTestException('Could not find the size column ['.config('infra.header.size').']. Maybe INFRA has changed their formatting?');
        }

        if (!($dataColumns['upc'] = $test->findUPCColumn($dataHeaderRow))) {
            throw new InfraFileTestException('Could not find the UPC column ['.config('infra.header.upc').']. Maybe INFRA has changed their formatting?');
        }

        if (!($test->findValidData($worksheet, $dataHeaderRow, $dataColumns['upc']))) {
            throw new InfraFileTestException("The spreadsheet seems okay but we couldn't find any actual products. Maybe INFRA has changed their formatting?");
        }

        return true;
    }

    /**
     * Cleans extra characters or spaces from a string.
     * "/\xe2\x84\xa2/u" is a Trade Mark symbol : TM
     * "/\xc2\xae/u" is a Registered symbol : (R)
     *
     * @param string $text
     * @return string
     */
    public function cleanText(string $text): string
    {
        if (preg_match("/(\xc2\xae|\xe2\x84\xa2)/u", $text)) {
            $text = preg_replace("/(\xc2\xae|\xe2\x84\xa2)/u", ' ', $text);
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
        }

        return $text;
    }

    /**
     * Dispatches a job to parse the INFRA sheet asynchronously.
     */
    public function queueParseSheet()
    {
        ParseInfraSheet::dispatch($this);
    }

    /**
     * Parses all items in the INFRA sheet and creates a sale for each.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function parseSheet()
    {
        $workbook  = $this->loadWorkbook(storage_path('app/'.$this->filename));

        $worksheet = $this->loadWorksheet($workbook);

        $dataHeaderRow = $this->findDataHeaderRow($worksheet);

        $dataColumns['brand'] = $this->findBrandColumn($dataHeaderRow);
        $dataColumns['desc']  = $this->findDescColumn($dataHeaderRow);
        $dataColumns['price'] = $this->findPriceColumn($dataHeaderRow);
        $dataColumns['size']  = $this->findSizeColumn($dataHeaderRow);
        $dataColumns['upc']   = $this->findUPCColumn($dataHeaderRow);

        $startRow = $dataHeaderRow->getRowIndex() + 1;

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            if ($row->getRowIndex() >= $startRow) {
                $data = [];

                $data['upc']   = $worksheet->getCell(($dataColumns['upc']   . $row->getRowIndex()))->getValue();
                $data['brand'] = $worksheet->getCell(($dataColumns['brand'] . $row->getRowIndex()))->getValue();
                $data['size']  = $worksheet->getCell(($dataColumns['size']  . $row->getRowIndex()))->getValue();
                $data['price'] = $worksheet->getCell(($dataColumns['price'] . $row->getRowIndex()))->getValue();
                $data['desc']  = $this->cleanText($worksheet->getCell(($dataColumns['desc'] . $row->getRowIndex()))->getValue());

                $this->createItemSale($data);
            }
        }
    }

    /**
     * Create an Item Sale from the given data.
     *
     * @param array $infraItem
     * @throws \Exception
     */
    protected function createItemSale(array $infraItem)
    {
        if ($infraItem['upc'] === null) {
            return;
        }

        $dateData = $this->calculateDateData();

        $item = ItemSale::make(['upc'                => $infraItem['upc'],
                                'brand'              => $infraItem['brand'],
                                'desc'               => $infraItem['desc'],
                                'size'               => $infraItem['size'],
                                'sale_category'      => $dateData['sale_category'],
                                'sale_begin'         => $dateData['sale_begin'],
                                'sale_end'           => $dateData['sale_end'],
                                'expires_at'         => $dateData['expires_at']]);

        $item->calculatePricingData($infraItem['price']);

        $item->save();
    }


    /**
     * Calculates date info for the INFRA sheet.
     *
     * @return array
     * @throws \Exception
     */
    protected function calculateDateData(): array
    {
        $data = [];

        $data['sale_category'] = Carbon::create($this->year, $this->month)->format('F') . ' Savings';

        $data['sale_begin'] = new Carbon("first day of $this->formatted_date");
        $data['sale_end']   = new Carbon("last day of $this->formatted_date");

        $data['expires_at'] = $data['sale_end']->copy()->addDay();;

        return $data;
    }

    /**
     * Load a workbook file using PhpSpreadsheet.
     *
     * @param string $file
     * @return Workbook|null
     */
    protected function loadWorkbook(string $file): ?Workbook
    {
        try {
            return IOFactory::createReaderForFile($file)
                            ->setReadDataOnly(true)
                            ->load($file);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find and return the KeHE worksheet.
     *
     * @param Workbook $workbook
     * @return Worksheet|null
     */
    protected function loadWorksheet(Workbook $workbook): ?Worksheet
    {
        try {
            $worksheets = $workbook->getSheetNames();

            foreach ($worksheets as $worksheet) {
                if (strpos($worksheet, config('infra.sheetname')) !== false) {
                    return $workbook->getSheetByName($worksheet);
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find the row that contains the headers for our data.
     *
     * @param Worksheet $worksheet
     * @return Row|null
     */
    protected function findDataHeaderRow(Worksheet $worksheet): ?Row
    {
        try {
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() === config('infra.header.price')) {
                        return $row;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find the Brand column.
     *
     * @param Row $dataHeaderRow
     * @return string|null
     */
    protected function findBrandColumn(Row $dataHeaderRow): ?string
    {
        try {
            $cellIterator = $dataHeaderRow->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                if ($cell->getValue() === config('infra.header.brand')) {
                    return $cell->getColumn();
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find the Desc column.
     *
     * @param Row $dataHeaderRow
     * @return string|null
     */
    protected function findDescColumn(Row $dataHeaderRow): ?string
    {
        try {
            $cellIterator = $dataHeaderRow->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                if ($cell->getValue() === config('infra.header.desc')) {
                    return $cell->getColumn();
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find the Price column.
     *
     * @param Row $dataHeaderRow
     * @return string|null
     */
    protected function findPriceColumn(Row $dataHeaderRow): ?string
    {
        try {
            $cellIterator = $dataHeaderRow->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                if ($cell->getValue() === config('infra.header.price')) {
                    return $cell->getColumn();
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find the Size column.
     *
     * @param Row $dataHeaderRow
     * @return string|null
     */
    protected function findSizeColumn(Row $dataHeaderRow): ?string
    {
        try {
            $cellIterator = $dataHeaderRow->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                if ($cell->getValue() === config('infra.header.size')) {
                    return $cell->getColumn();
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find the UPC column.
     *
     * @param Row $dataHeaderRow
     * @return string|null
     */
    protected function findUPCColumn(Row $dataHeaderRow): ?string
    {
        try {
            $cellIterator = $dataHeaderRow->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                if ($cell->getValue() === config('infra.header.upc')) {
                    return $cell->getColumn();
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Try to determine if we have what appears to be valid product data.
     *
     * @param Worksheet $worksheet
     * @param Row $dataHeaderRow
     * @param string $upcColumn
     * @return bool
     */
    protected function findValidData(Worksheet $worksheet, Row $dataHeaderRow, string $upcColumn): bool
    {
        try {
            $startRow = $dataHeaderRow->getRowIndex() + 1;

            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                // If we're past the "header row" and we find data in the UPC column, we should have valid product data.
                if ($row->getRowIndex() >= $startRow && $worksheet->getCell(($upcColumn.$row->getRowIndex()))->getValue() !== null) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
