<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateAllowedAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allowed_apps', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->default('web');
            $table->string('app_id', 50)->unique();
            $table->string('secret');
            $table->timestamps();
        });

        $this->loadData();
    }

    private function loadData()
    {
        DB::table('allowed_apps')
            ->insert(
                [
                    'app_id' => 100,
                    'secret' => Hash::make('my_app'),
                ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allowed_apps');
    }
}
