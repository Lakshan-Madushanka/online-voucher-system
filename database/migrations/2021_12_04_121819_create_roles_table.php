<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['admin', 'super_admin', 'customer']);
            $table->timestamps();
        });

        $this->createDefaultRoles();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }

    private function createDefaultRoles()
    {
        foreach (Role::types as $key => $value) {
            DB::table('roles')->insert(
                ['type' => $key, 'created_at' => now()],
            );
        }

    }
}
