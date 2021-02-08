<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public $category;
    public function setUp(): void {
        $this->category = new Category();
        $this->category->setName('Test');
        $this->category->setActive('true');
    }

    public function testObligatoriedad(): void
    {
        $product = new Product();
        $product->setCode('12');
        $product->setName('12');
        $product->setCategory($this->category);
        $product->setPrice('12');
        $product->setBrand('12');
        $this->assertTrue($product->getCreatedAt() == '','Fallo no debe ser null');
    }
}
