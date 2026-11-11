<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'snap_token')) {
                $table->string('snap_token', 255)->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('orders', 'snap_order_id')) {
                $table->string('snap_order_id', 100)->nullable()->index()->after('snap_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $dropCols = [];
            if (Schema::hasColumn('orders', 'snap_token')) {
                $dropCols[] = 'snap_token';
            }
            if (Schema::hasColumn('orders', 'snap_order_id')) {
                $dropCols[] = 'snap_order_id';
            }
            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};

