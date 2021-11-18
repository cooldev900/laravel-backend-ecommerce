<?php

namespace Database\Seeders;

use App\Models\NationalCodes;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed one time reference data
        $this->call(Scopes::class);
        $this->call(Roles::class);
        $this->call(StoreViews::class);
        $this->call(Users::class);
        $this->call(UserPermissions::class);
        $this->call(Companies::class);
        $this->call(NationalCodes::class);
    }
}