<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Exceedone\Exment\Database\ExtendedBlueprint;

class CreateCustomColumnMulti extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = DB::connection()->getSchemaBuilder();

        $schema->blueprintResolver(function($table, $callback) {
            return new ExtendedBlueprint($table, $callback);
        });

        //
        $schema->create('custom_column_multisettings', function (ExtendedBlueprint $table) {
            $table->increments('id');
            $table->integer('custom_table_id')->unsigned();
            $table->integer('multisetting_type')->default(1);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->timeusers();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_column_multisettings');
        //
    }
}
