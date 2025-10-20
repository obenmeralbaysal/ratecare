<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('password_resets', function ($table) {
            $table->string('email');
            $table->string('token', 64);
            $table->timestamp('created_at');
            
            $table->index(['email']);
            $table->index(['token']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('password_resets');
    }
};
