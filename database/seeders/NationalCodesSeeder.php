<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NationalCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $json = Storage::disk('local')->get('allbmw.json');
        $json = json_decode($json, true);

        $toInsert = [];
        foreach ($json['sheet1'] as $obj) {
            $toInsert[] = [
                'ID' => isset($obj['ID']) ? $obj['ID'] : '',
                'Vehicle_Type' => isset($obj['Vehicle_Type']) ? $obj['Vehicle_Type'] : '',
                'Make' => isset($obj['Make']) ? $obj['Make'] : '',
                'Model_Group' => isset($obj['Model_Group']) ? $obj['Model_Group'] : '',
                'undefined' => isset($obj['undefined']) ? $obj['undefined'] : '',
                'Body_style' => isset($obj['Body_style']) ? $obj['Body_style'] : '',
                'Fuel' => isset($obj['Fuel']) ? $obj['Fuel'] : '',
                'Trim' => isset($obj['Trim']) ? $obj['Trim'] : '',
                'Engine_Size' => isset($obj['Engine_Size']) ? $obj['Engine_Size'] : '',
                'Model' => isset($obj['Model']) ? $obj['Model'] : '',
                'StartYear' => isset($obj['StartYear']) ? $obj['StartYear'] : '',
                'StartMonth' => isset($obj['StartMonth']) ? $obj['StartMonth'] : '',
                'EndYear' => isset($obj['EndYear']) ? $obj['EndYear'] : '',
                'EndMonth' => isset($obj['EndMonth']) ? $obj['EndMonth'] : '',
                'TYPImpBegin' => isset($obj['TYPImpBegin']) ? $obj['TYPImpBegin'] : '',
                'TYPImpEnd' => isset($obj['TYPImpEnd']) ? $obj['TYPImpEnd'] : '',
                'TYPValvpCyl' => isset($obj['TYPValvpCyl']) ? $obj['TYPValvpCyl'] : '',
            ];
        }

        foreach (array_chunk($toInsert, 1000) as $t) {
            DB::table('national_codes')->insert($t);
        }
    }
}