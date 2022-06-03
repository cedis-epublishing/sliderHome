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

		// add column copyright
		Capsule::schema()->table('slider', function($table) {
			$table->text('copyright', 255);
            $table->text('sliderImage', 255);
            $table->text('sliderImageAltText', 255);

		});
	}
}