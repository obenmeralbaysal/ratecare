<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('namesurname');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(0);
            $table->bigInteger('reseller_id')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
            
            $table->index(['email']);
            $table->index(['reseller_id']);
            $table->index(['is_admin']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('users');
    }
};
