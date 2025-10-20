<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('statistics', function ($table) {
            $table->id();
            $table->bigInteger('hotel_id');
            $table->bigInteger('widget_id')->nullable();
            $table->string('metric_type'); // views, clicks, bookings, revenue
            $table->string('metric_name');
            $table->decimal('value', 15, 4);
            $table->string('currency', 3)->nullable();
            $table->date('date');
            $table->integer('hour')->nullable(); // 0-23 for hourly stats
            $table->text('metadata')->nullable(); // JSON for additional data
            $table->timestamps();
            
            $table->index(['hotel_id']);
            $table->index(['widget_id']);
            $table->index(['metric_type']);
            $table->index(['date']);
            $table->index(['date', 'hour']);
            $table->unique(['hotel_id', 'widget_id', 'metric_type', 'metric_name', 'date', 'hour'], 'unique_stat');
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('statistics');
    }
};
