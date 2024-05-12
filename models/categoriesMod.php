<?php
namespace Memento;
class CategoriesMod {
    private $id;
    private $title;
    private $products;

    public function __construct($id, $title) {
        $this->id = $id;
        $this->title = $title;
        $this->products = [];
    }

    public function addProduct($product) {
        $this->products[] = $product;
    }

    public function getProducts() {
        return $this->products;
    }

    public function getTitle() {
        return $this->title;
    }
}
