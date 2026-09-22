<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\OrderModel;

class OrderModelTest extends CIUnitTestCase
{
    protected OrderModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new OrderModel();
    }

    /**
     * Memastikan format nomor pesanan sesuai pola PNA-YYYYMMDD-XXXX
     */
    public function testGenerateOrderNumberFormat(): void
    {
        $orderNumber = $this->model->generateOrderNumber();

        // Format: PNA-YYYYMMDD-XXXX
        $this->assertMatchesRegularExpression(
            '/^PNA-\d{8}-[A-F0-9]{4}$/i',
            $orderNumber,
            'Order number harus mengikuti format PNA-YYYYMMDD-XXXX'
        );
    }

    /**
     * Memastikan tanggal pada nomor pesanan adalah hari ini
     */
    public function testGenerateOrderNumberContainsCurrentDate(): void
    {
        $orderNumber = $this->model->generateOrderNumber();
        $today = date('Ymd');

        $this->assertStringContainsString(
            $today,
            $orderNumber,
            'Order number harus mengandung tanggal hari ini'
        );
    }

    /**
     * Memastikan setiap pemanggilan menghasilkan nomor unik
     */
    public function testGenerateOrderNumberIsUnique(): void
    {
        $numbers = [];
        for ($i = 0; $i < 20; $i++) {
            $numbers[] = $this->model->generateOrderNumber();
        }

        $uniqueNumbers = array_unique($numbers);
        $this->assertCount(
            count($numbers),
            $uniqueNumbers,
            'Setiap nomor pesanan harus unik'
        );
    }

    /**
     * Memastikan prefix nomor pesanan selalu PNA
     */
    public function testOrderNumberPrefix(): void
    {
        $orderNumber = $this->model->generateOrderNumber();
        $this->assertStringStartsWith('PNA-', $orderNumber);
    }

    /**
     * Memastikan model memiliki allowed fields yang benar
     */
    public function testAllowedFieldsContainRequiredColumns(): void
    {
        $requiredFields = ['order_number', 'user_id', 'unit_id', 'grand_total', 'status'];

        foreach ($requiredFields as $field) {
            $this->assertContains(
                $field,
                $this->model->allowedFields,
                "Field '{$field}' harus ada di allowedFields"
            );
        }
    }

    /**
     * Memastikan getOrderWithRelations mengembalikan array atau null
     */
    public function testGetOrderWithRelationsReturnsArrayOrNull(): void
    {
        // Cari order dengan ID yang tidak ada
        $result = $this->model->getOrderWithRelations(999999);
        $this->assertNull($result, 'Query ID tidak ditemukan harus mengembalikan null');
    }

    /**
     * Memastikan getOrderWithRelations tanpa parameter mengembalikan array
     */
    public function testGetOrderWithRelationsListReturnsArray(): void
    {
        $results = $this->model->getOrderWithRelations();
        $this->assertIsArray($results);
    }
}
