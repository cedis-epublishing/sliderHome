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
use Illuminate\Database\Capsule\Manager as Capsule;

class SliderHomeSchemaMigration extends Migration {
    /**
     * Run the migrations.
     * @return void
     */
    public function up() {

        // remove old slider data and tables
        if (Capsule::schema()->hasTable('slider')) {

            // remove slider images
            $slider = Capsule::table('slider')->get();

            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            foreach ($slider->items as $file) {               
                // $publicFileManager->removeContextFile(
                    // TODO @RS
                // );
            }
            
            // remove slider table
            Capsule::schema()->drop('slider');
        }
        if (Capsule::schema()->hasTable('slider_settings')) {
            Capsule::schema()->drop('slider_settings');
        }

        // main slider table
		Capsule::schema()->create('slider', function (Blueprint $table) {
            $table->increments('slider_content_id');
            $table->smallInteger('context_id');
            $table->smallInteger('sequence');
            $table->smallInteger('show_content');
		});

		// slider content settings
		Capsule::schema()->create('slider_settings', function (Blueprint $table) {
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
