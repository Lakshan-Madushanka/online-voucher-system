<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('image', 100);
            $table->decimal('price', 8, 2)->index('idx_price');
            $table->string('terms', 1000);
            $table->enum('status', ['approved', 'rejected', 'pending'])
                ->index('idx_status');
            $table->timestamp('validity')->index('idx_validity');;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
