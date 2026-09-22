<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ProdukModel;

class ProdukModelTest extends CIUnitTestCase
{
    protected ProdukModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ProdukModel();
    }

    public function testGetCatalogReturnsArray(): void
    {
        $products = $this->model->getCatalog(['limit' => 5]);
        $this->assertIsArray($products);
    }

    public function testGetFeaturedProducts(): void
    {
        $featured = $this->model->getFeatured(4);
        $this->assertIsArray($featured);

        foreach ($featured as $prod) {
            $this->assertEquals(1, $prod['featured']);
            $this->assertEquals('aktif', $prod['status']);
        }
    }

    public function testSearchFilterWorks(): void
    {
        $results = $this->model->getCatalog(['search' => 'Kopi']);
        $this->assertIsArray($results);

        foreach ($results as $item) {
            $contains = (stripos($item['nama_produk'], 'Kopi') !== false) || (stripos($item['deskripsi'] ?? '', 'Kopi') !== false);
            $this->assertTrue($contains);
        }
    }
}
