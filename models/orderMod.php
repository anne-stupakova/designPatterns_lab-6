<?php
namespace Memento;

class OrderMod {
    private $id;
    private $city;
    private $departmentNumber;
    private $phone;
    private $userId;
    private $productId;
    public $productName;
    private $count;
    private $allPrice;

    public function __construct($id, $city, $departmentNumber, $phone, $userId, $productId, $productName, $count, $allPrice) {
        $this->id = $id;
        $this->city = $city;
        $this->departmentNumber = $departmentNumber;
        $this->phone = $phone;
        $this->userId = $userId;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->count = $count;
        $this->allPrice = $allPrice;
    }

    public function getId() {
        return $this->id;
    }

    public function getProductName() {
        return $this->productName;
    }

    public function getCount() {
        return $this->count;
    }

    public function getAllPrice() {
        return $this->allPrice;
    }
}
