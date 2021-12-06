<?php

/**
 * @file classes/migration/SliderHomeSchemaMigration.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
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
			$table->bigInteger('sequence');
			$table->bigInteger('show_content');
		});

	}
}