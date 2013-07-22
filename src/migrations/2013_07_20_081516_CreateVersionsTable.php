<?php

use Illuminate\Database\Migrations\Migration;

class CreateVersionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('versions', function($table)
		{
			$table->increments('id');
			$table->integer('object_id')->index();
			$table->string('object_table', 255);
			$table->string('name', 255);
			$table->text('data');
			$table->string('hash', 255);
			$table->timestamps();
			$table->unique(array('object_id', 'object_table', 'hash'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('versions');
	}

}