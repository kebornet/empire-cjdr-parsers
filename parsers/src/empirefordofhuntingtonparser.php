<?php

require_once __DIR__ . '/abstract_search.php';

class EmpireFordOfHuntingtonParser extends \AbstractSearch
{
    const BASE_URL = 'https://www.empirefordofhuntington.com';
    const GOOGLE_PRODUCT_CATEGORY = 916;
    const STORE_CODE = 9499;
    const VEHICLE_FULFILLMENT = 'in_store:9499';
    const MAX_NUMBER_IMAGE_CAR = 3;
    const MIN_NUMBER_IMAGE_CAR = 0;

    public function __construct(array $proxy = null)
    {
        $this->_construct($proxy);
    }

    public function getInfoCars()
    {
        $carsInfo = [];
        $carsPage = $this->loadWithCurlMain(self::BASE_URL . '/searchused.aspx?pn=96');
        $carsInfoHtmlBlocks = $this->getCarsInfoHtmlBlocks($carsPage);

        while (preg_match('/<link rel="next" href="\/\/www.empirefordofhuntington.com(.+)".*>/sU', $carsPage, $nextPage)) {
            $carsPage = $this->loadWithCurlMain(self::BASE_URL . $nextPage[1]);
            $carsInfoHtmlBlocksNextPage = $this->getCarsInfoHtmlBlocks($carsPage);
            $carsInfoHtmlBlocks = array_merge($carsInfoHtmlBlocks, $carsInfoHtmlBlocksNextPage);
        }

        foreach ($carsInfoHtmlBlocks as $carInfoHtmlBlock) {
            $carPageUrl = $this->getCarPageUrl($carInfoHtmlBlock);
            $detailCarPage =  $this->loadWithCurlMain($carPageUrl);
            $carsInfo[] = $this->getCarInfo($carInfoHtmlBlock, $detailCarPage);
        }

        return $carsInfo;
    }

    private function getCarsInfoHtmlBlocks($carsPage)
    {
        if (!preg_match_all('/id="vehicle_(.+)<div class="vehicle-card__compare/sU', $carsPage, $carsInfoHtmlBlocks)) {
            throw new Exception('Unable to get cars info');
        }

        return $carsInfoHtmlBlocks[1];
    }

    private function getCarInfo($carInfoHtmlBlock, $detailCarPage)
    {
        return [
            'carTitle' => $this->getTitle($carInfoHtmlBlock),
            'carPrice' =>  $this->getPrice($carInfoHtmlBlock),
            'carMileage' => $this->getMileage($detailCarPage),
            'carImageLink' => $this->getImageLink($detailCarPage),
            'carVinNumber' => $this->getVinNumber($carInfoHtmlBlock),
            'carPageLink' => $this->getCarPageUrl($carInfoHtmlBlock),
            'carBrand' => $this->getCarBrand($detailCarPage),
            'carColor' => $this->getCarColor($detailCarPage),
            'carModel' => $this->getCarModel($detailCarPage),
            'carYear' => $this->getCarYear($detailCarPage),
            'carCondition' => $this->getCarCondition($detailCarPage),
            'carGoogleProductCategory' => self::GOOGLE_PRODUCT_CATEGORY,
            'carID' => $this->getVinNumber($carInfoHtmlBlock),
            'carStoreCode' => self::STORE_CODE,
            'carVehicleFulfillment' => self::VEHICLE_FULFILLMENT,
        ];
    }

    private function getTitle($carInfoHtmlBlock)
    {
        if (!preg_match('/<a class="vehicle-title".*title="(.+)"/sU', $carInfoHtmlBlock, $match)) {
            throw new Exception('Unable to get car title');
        }

        return trim($match[1]);
    }

    private function getPrice($carInfoHtmlBlock)
    {
        if (!preg_match('/<span class="priceBlocItemPriceLabel.*">Empire Price<\/span>.*">\$(.+)<\/span>/sU', $carInfoHtmlBlock, $match)) {
            return null;
        }

        return trim($match[1]);
    }

    private function getCarPageUrl($carInfoHtmlBlock)
    {
        if (!preg_match('/<a class="vehicle-title".*href="(.+)"/sU', $carInfoHtmlBlock, $match)) {
            throw new Exception('Unable to get car page URL');
        }

        return trim($match[1]) . '?store={store_code}';
    }

    private function getMileage($detailCarPage)
    {
        if (!preg_match('/Mileage<\/span.*">(.+)<\/span>/sU', $detailCarPage, $match)) {
            throw new Exception('Unable to get car mileage');
        }

        return trim($match[1]) . ' miles';
    }

    private function getImageLink($detailCarPage)
    {
        $carouselImageNumber = self::MAX_NUMBER_IMAGE_CAR;
        while (!preg_match('/thumbnail--desktop--' . $carouselImageNumber . '.*<picture>.*<img src="(.+)".*class/sU', $detailCarPage, $match) && $carouselImageNumber > self::MIN_NUMBER_IMAGE_CAR) {
            $carouselImageNumber--;
        }

        return isset($match[1]) ? self::BASE_URL . $match[1] : null;
    }

    private function getVinNumber($carInfoHtmlBlock)
    {
        if (!preg_match('/div class="vehicle-identifiers__vin">.*class=".*value">(.+)<\/span>/sU', $carInfoHtmlBlock, $match)) {
            throw new Exception('Unable to get car VIN number');
        }

        return trim($match[1]);
    }

    private function getCarBrand($detailCarPage)
    {
        if (!preg_match('/data-make="(.+)"/sU', $detailCarPage, $match)) {
            throw new Exception('Unable to get car brand');
        }

        return trim($match[1]);
    }

    private function getCarColor($detailCarPage)
    {
        if (!preg_match('/Exterior Color.*title="(.+)">/sU', $detailCarPage, $match)) {
            return null;
        }

        return trim($match[1]);
    }

    private function getCarYear($detailCarPage)
    {
        if (!preg_match('/data-year="(.+)"/sU', $detailCarPage, $match)) {
            throw new Exception('Unable to get car year');
        }

        return trim($match[1]);
    }

    private function getCarCondition($detailCarPage)
    {
        if (!preg_match('/data-type="(.+)"/sU', $detailCarPage, $match)) {
            throw new Exception('Unable to get car condition');
        }

        return trim($match[1]);
    }

    private function getCarModel($detailCarPage)
    {
        if (!preg_match('/data-model="(.+)"/sU', $detailCarPage, $model)) {
            throw new Exception('Unable to get car model');
        }

        if (!preg_match('/data-trim="(.+)"/sU', $detailCarPage, $trim)) {
            throw new Exception('Unable to get car model trim');
        }

        return trim($model[1] . ' ' . $trim[1]);
    }
}
