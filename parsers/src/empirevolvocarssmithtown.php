<?php

require_once __DIR__ . '/abstract_search.php';

class EmpireVolvoCarsSmithtown extends \AbstractSearch
{
    const BASE_URL = 'https://www.empirevolvocarssmithtown.com';
    const API_SEARCH_CARS_PAGE = self::BASE_URL . '/apis/widget/INVENTORY_LISTING_DEFAULT_AUTO_USED:inventory-data-bus1/getInventory';

    const GOOGLE_PRODUCT_CATEGORY = 916;
    const STORE_CODE = 3324;
    const VEHICLE_FULFILLMENT = 3324;
    const CAR_CONDITION = 'Used';
    const MAX_NUMBER_IMAGE_CAR = 3;
    const MIN_NUMBER_IMAGE_CAR = 0;

    const PAGE_SIZE = 18;

    public function __construct(array $proxy = null)
    {
        $this->_construct($proxy);
    }

    public function getInfoCars()
    {
        $carsInfo = [];

        $paginationPage = 0;
        $carsInfoFromPage = json_decode($this->loadWithCurlMain(self::API_SEARCH_CARS_PAGE . "?start={$paginationPage}"), true);

        if (!isset($carsInfoFromPage['pageInfo']['totalCount'])) {
            throw new Exception('Unable to get total count cars');
        }
        $totalCountCars = $carsInfoFromPage['pageInfo']['totalCount'];

        if (!isset($carsInfoFromPage['pageInfo']['trackingData'])) {
            throw new Exception('Unable to get list cars');
        }
        $listCars = $carsInfoFromPage['pageInfo']['trackingData'];

        while (count($listCars) != $totalCountCars) {
            $paginationPage += self::PAGE_SIZE;
            $carsInfoNextPage = json_decode($this->loadWithCurlMain(self::API_SEARCH_CARS_PAGE . "?start={$paginationPage}"), true);
            $listCars = array_merge($listCars, $carsInfoNextPage['pageInfo']['trackingData']);
        }

        foreach ($listCars as $carInfo) {
            $carsInfo[] = $this->getCarInfo($carInfo);
        }

        return $carsInfo;
    }

    private function getCarInfo($carInfo)
    {
        return [
            'carCondition' => self::CAR_CONDITION,
            'carGoogleProductCategory' => self::GOOGLE_PRODUCT_CATEGORY,
            'carStoreCode' => self::STORE_CODE,
            'carVehicleFulfillment' => self::VEHICLE_FULFILLMENT,
            'carBrand' => $this->getCarBrand($carInfo),
            'carModel' => $this->getCarModel($carInfo),
            'carYear' => $this->getCarYear($carInfo),
            'carMileage' => $this->getMileage($carInfo),
            'carPrice' =>  $this->getPrice($carInfo),
            'carImageLink' => $this->getImageLink($carInfo),
            'carPageLink' => $this->getCarPageUrl($carInfo),
            'carVinNumber' => $this->getVinNumber($carInfo),
            'carID' => $this->getVinNumber($carInfo),
            'carColor' => $this->getCarColor($carInfo)
        ];
    }

    private function getPrice($carInfo)
    {
        if (!isset($carInfo['askingPrice'])) {
            return null;
        }

        return trim($carInfo['askingPrice']);
    }

    private function getCarPageUrl($carInfo)
    {
        if (!isset($carInfo['link'])) {
            throw new Exception('Unable to get car page url');
        }

        return trim(self::BASE_URL . $carInfo['link'] .'?store={store_code}');
    }

    private function getMileage($carInfo)
    {
        if (!isset($carInfo['odometer'])) {
            return null;
        }

        return trim($carInfo['odometer'] . ' miles');
    }

    private function getImageLink($carInfo)
    {
        $carouselImageNumber = self::MAX_NUMBER_IMAGE_CAR;
        while (!isset($carInfo['images'][$carouselImageNumber]['uri']) && $carouselImageNumber > self::MIN_NUMBER_IMAGE_CAR) {
            $carouselImageNumber--;
        }

        return $carInfo['images'][$carouselImageNumber]['uri'] ?? null;
    }

    private function getVinNumber($carInfo)
    {
        if (!isset($carInfo['vin'])) {
            throw new Exception('Unable to get car VIN number');
        }

        return trim($carInfo['vin']);
    }

    private function getCarBrand($carInfo)
    {
        if (!isset($carInfo['make'])) {
            throw new Exception('Unable to get car brand');
        }

        return trim($carInfo['make']);
    }

    private function getCarYear($carInfo)
    {
        if (!isset($carInfo['modelYear'])) {
            throw new Exception('Unable to get car year');
        }

        return trim($carInfo['modelYear']);
    }

    private function getCarModel($carInfo)
    {
        if (!isset($carInfo['model'])) {
            throw new Exception('Unable to get car model');
        }

        return trim($carInfo['model']);
    }

    private function getCarColor($carInfo)
    {
        if (!isset($carInfo['exteriorColor'])) {
            return null;
        }

        return trim($carInfo['exteriorColor']);
    }
}
