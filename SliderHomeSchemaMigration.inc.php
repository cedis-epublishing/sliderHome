<?php

/**
 * @file classes/migration/SliderHomeSchemaMigration.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SliderHomeSchemaMigration
 * @brief Describe database table structures.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as Schema;

class SliderHomeSchemaMigration extends Migration {
    /**
     * Run the migrations.
     * @return void
     */
    public function up() {

        // we only need to alter tables if we don't have 'slider_settings' => multilingual branch
        if (!Schema::hasTable('slider_settings')) {
            // remove old slider data and tables
            if (Schema::hasTable('slider')) {

                // remove slider images
                $slider = DB::table('slider')->get();

                // import('classes.file.PublicFileManager');
                // $publicFileManager = new PublicFileManager();
                // $request = Application::get()->getRequest();
                // // this would need to be context specific, need to loop through all contexts
                // // $context_id = ->getContext()->getId();
                // foreach ($slider as $slide) {               
                //     $publicFileManager->removeContextFile(
                //         $context_id,
                //         $slide['']
                //     );
                // }
                
                // remove slider table
                Schema::drop('slider');
            }
            if (Schema::hasTable('slider_settings')) {
                Schema::drop('slider_settings');
            }

            // main slider table
            Schema::create('slider', function (Blueprint $table) {
                $table->increments('slider_content_id');
                $table->smallInteger('context_id');
                $table->smallInteger('sequence');
                $table->smallInteger('show_content');
            });

            // slider content settings
            Schema::create('slider_settings', function (Blueprint $table) {
                $table->bigInteger('slider_content_id');
                $table->string('locale', 14)->default('');
                $table->string('setting_name', 255);
                $table->longText('setting_value')->nullable();
                $table->string('setting_type', 6)->comment('(bool|int|float|string|object)');
                $table->index(['slider_content_id'], 'slider_settings_slider_id');
                $table->unique(['slider_content_id', 'locale', 'setting_name'], 'slider_settings_pkey');
            });

        }
    }
}