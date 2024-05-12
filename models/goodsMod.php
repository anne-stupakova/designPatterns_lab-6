<?php
namespace Memento;
class GoodsMod
{
    private $id;
    private $name;
    private $price;
    private $info;
    private $photo;
    private $dataCreation;
    private $categoryId;

    public function __construct($id, $name, $price, $info, $photo, $dataCreation, $categoryId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->info = $info;
        $this->photo = $photo;
        $this->dataCreation = $dataCreation;
        $this->categoryId = $categoryId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->price;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getInfo() {
        return $this->info;
    }

    public function getPhoto() {
        return $this->photo;
    }

}

