<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCCMTRSTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('CCMTRS', function(Blueprint $table)
		{
			$table->char('MCODTRSP', 11)->primary();
			$table->string('MNOMBRE', 100)->nullable();
			$table->string('MDIRECC', 100)->nullable();
			$table->string('MTELEF1', 15)->nullable();
			$table->string('MTELEF2', 15)->nullable();
			$table->string('MFAX', 15)->nullable();
			$table->char('MCODPAI', 4)->nullable();
			$table->string('MPERSONA', 40)->nullable();
			$table->string('MRUCTRSP', 15)->nullable();
			$table->date('MFCHREGIS')->nullable();
			$table->string('MPLACA', 10)->nullable();
			$table->string('MCAPACI', 7)->nullable();
			$table->string('MCHOFER', 40)->nullable();
			$table->string('MCODUSER', 15)->nullable();
			$table->date('MFECUACT')->nullable();
			$table->string('MHORUACT', 10)->nullable();
			$table->string('MCODCVE', 12)->nullable();
			$table->string('MCODUTR', 8)->nullable();
			$table->string('MLOCALID', 100)->nullable();
			$table->string('MINDACT', 1)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('CCMTRS');
	}

}
