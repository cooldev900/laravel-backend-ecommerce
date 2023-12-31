<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Companies extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')->insert([
            [
                'name' => 'Omni Automotive',
                'url' => encrypt('http://54.74.138.56/rest/'),
                'consumer_key' => encrypt('qnnopm2yvhknuq6i0p2mjfnpjsx5os9g'),
                'consumer_secret' => encrypt('52ivyu7txij9m1izyf7ups8nlsy0naqx'),
                'token' => encrypt('vfxnnm89z5j8y427gzzt5gvwi3gpl7vp'),
                'token_secret' => encrypt('lt5fxack3lfisdlhm6wh9w69v3t9f46d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}