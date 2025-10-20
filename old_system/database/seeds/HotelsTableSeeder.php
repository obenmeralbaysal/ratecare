<?php

use Illuminate\Database\Seeder;

class HotelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('hotels')->insert([
            'user_id' => 1,
            'name' => 'Yeni Otel',
            'web_url' => 'customurl.com',
            'booking_url' => 'https://www.booking.com/hotel/tr/senatus.tr.html',
            'hotels_url' => 'hotels.com/ho410133'
        ]);
    }
}
