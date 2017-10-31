<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateCalendarEventsTable
 */
class CreateCalendarEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_calendar_event_id')->unsigned();

            $table->date('start_date');
            $table->date('end_date');

//            TODO Change date/time to *_at
//            $table->datetime('start_at');
//            $table->datetime('end_at');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('template_calendar_event_id')
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
        Schema::dropIfExists('calendar_events');
    }
}
