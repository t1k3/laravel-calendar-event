<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateTemplateCalendarEventsTable
 */
class CreateTemplateCalendarEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_calendar_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->date('start_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('description')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->date('end_of_recurring')->nullable();
            $table->integer('frequence_number_of_recurring')->nullable();
            $table->string('frequence_type_of_recurring', 32)->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')->on('template_calendar_events')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_calendar_events');
    }
}
