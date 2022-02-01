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
		// Content for slider
		Capsule::schema()->create('slider', function (Blueprint $table) {
			$table->bigInteger('slider_content_id')->autoIncrement();
			$table->bigInteger('context_id');
			$table->string('name', 255);
			$table->text('content');
			$table->text('copyright', 255);
			$table->bigInteger('sequence');
			$table->bigInteger('show_content');
		});

	}
}