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

        if (Capsule::schema()->hasTable('slider')) {
            // add new columns to existing slider table
            Capsule::schema()->table('slider', function($table) {
                $table->text('copyright', 255);
                $table->text('sliderImage', 255);
                $table->text('sliderImageAltText', 255);

            });
        } else {
            // create new slider table
            Capsule::schema()->create('slider', function (Blueprint $table) {
                $table->increments('slider_content_id');
                $table->smallInteger('context_id');
                $table->text('name', 255);
                $table->text('content', 255);
                $table->text('copyright', 255);
                $table->text('sliderImage', 255);
                $table->text('sliderImageAltText', 255);
                $table->smallInteger('sequence');
                $table->smallInteger('show_content');		
            });
        }
    }
}
