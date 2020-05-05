<?php

use Illuminate\Database\Migrations\Migration;

class SupportForV320 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('workflow_condition_headers') && !Schema::hasColumn('workflow_condition_headers', 'options')){
            Schema::table('workflow_condition_headers', function (Blueprint $table) {
                $table->json('options')->nullable()->after('enabled_flg');
            });
        }

        if(Schema::hasTable('custom_form_priorities') && !Schema::hasColumn('custom_form_priorities', 'options')){
            Schema::table('custom_form_priorities', function (Blueprint $table) {
                $table->json('options')->nullable()->after('order');
            });
        }

        \Artisan::call('exment:patchdata', ['action' => 'parent_org_type']);
        \Artisan::call('exment:patchdata', ['action' => 'plugin_all_user_enabled']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_condition_headers', function($table) {
            if (Schema::hasColumn('workflow_condition_headers', 'options')) {
                $table->dropColumn('options');
            }
        });
        Schema::table('custom_form_priorities', function($table) {
            if (Schema::hasColumn('custom_form_priorities', 'options')) {
                $table->dropColumn('options');
            }
        });
    }
}
