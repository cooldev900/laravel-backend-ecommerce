<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return new Provider();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFieldName($field)
    {   
        $field = strtolower(trim($field));
        $col_name = explode(" ", $field);
        return implode("_", $col_name);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'string',
                'fields' => 'array',
            ]);
    
            $name = $request->get('name');
            $sql = "CREATE TABLE IF  NOT EXISTS `provider_{$name}` (\n";
            $sql .= "`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,\n";
    
            $provider = new Provider();
            $provider->name = $name;
            $provider->save();
    
            $fields = $request->get('fields');
            if (sizeof($fields) > 0) {
                foreach ($fields as $field) {
                    $col_name = $field['field'];
                    $provider->fields()->create(['field' => $col_name]);
                    $field_name = $this->createFieldName($col_name);
                    $sql .= "`{$field_name}` VARCHAR(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,\n";
                    $sql .= "`{$field_name}_sandbox` VARCHAR(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,\n";
                }
            }
    
            $sql .= "`manual_capture` tinyint(1) DEFAULT NULL,\n
                    `refund_in_platform` tinyint(1) DEFAULT NULL,\n
                    `status` tinyint(1) DEFAULT NULL,\n
                    `store_views_id` INT(10) UNSIGNED DEFAULT NULL\n,
                      PRIMARY KEY (`id`)\n
                     ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            DB::statement($sql);
    
            return response()->json([
                'status' => 'success',
                'data' => $provider,
                'sql' => $sql
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_store_provider',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function addFields(Request $request)
    {
        try {
            $request->validate([
                'fields' => 'array',
            ]);

            $params = $request->route()->parameters();
            $provider = Provider::findOrFail($params['id']);
            
            if ($provider ) {
                $name = $provider->name;
                $sql = "ALTER TABLE `{provider_$name}` \n ";
                $fields = $request->get('fields');
                if (sizeof($fields) > 0) {
                    foreach ($fields as $field) {
                        $col_name = $field['field'];
                        $provider->fields()->create(['field' => $col_name]);
                        $sql .= "ADD COLUMN `".$col_name."` VARCHAR(255) NULL,\n";
                        $sql .= "ADD COLUMN `".$col_name."_example` VARCHAR(255) NULL,\n";
                    }
                    $sql .=";";
                    DB::statement($sql);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'error' => 'not_found_provider',
                    'message' => 'not_found_provider',
                ], 500);    
            }


        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_store_provider',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Provider  $provider
     * @fields [{id: 1, field: 'sacred_key'}]
     * @return \Illuminate\Http\Response
     */
    public function editField(Request $request)
    {
        try {
            $request->validate([
                'fields' => 'array',
            ]);

            $params = $request->route()->parameters();
            $provider = Provider::findOrFail($params['id']);
            
            if ($provider ) {
                $name = $provider->name;
                $sql = "ALTER TABLE `{provider_$name}` \n ";
                $fields = $request->get('fields');
                if (sizeof($fields) > 0) {
                    foreach ($fields as $field) {
                        $id = $field['id'];
                        $provider_field = ProviderFields::findOrFail($id);
                        $col_name = $field['field'];
                        if ($provider_field) {
                            $old_name = $provider_field->field;
                            $provider_field->field = $field['field'];
                            $provider_field->save();
                            $new_name = 
                            $sql .= "CHANGE `{$old_name}` `{$new_name}` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,";
                            $sql .= "CHANGE `{$old_name}_example` `{$new_name}_example` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,";
                        } else {
                            $provider->fields()->create(['field' => $field['field']]);
                            $sql .= "ADD COLUMN `".$col_name."` VARCHAR(255) NULL,\n";
                            $sql .= "ADD COLUMN `".$col_name."_example` VARCHAR(255) NULL,\n";
                        }
                    }
                    $sql .=";";
                    DB::statement($sql);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'error' => 'not_found_provider',
                    'message' => 'not_found_provider',
                ], 500);    
            }


        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_store_provider',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function deleteField(Request $request)
    {
        try {
            $request->validate([
                'fields' => 'array',
            ]);

            $params = $request->route()->parameters();
            $provider = Provider::findOrFail($params['id']);
            
            if ($provider ) {
                $name = $provider->name;
                $sql = "ALTER TABLE `{provider_$name}` \n ";
                $fields = $request->get('fields');
                if (sizeof($fields) > 0) {
                    foreach ($fields as $field) {
                        $id = $field['id'];
                        $provider_field = ProviderFields::findOrFail($id);
                        if ($provider_field) {
                            $col_name = $field['field'];                        
                            $sql .= "DROP COLUMN `".$col_name."`,\n";
                            $sql .= "DROP COLUMN `".$col_name."_example`,\n";
                        }
                    }
                    $sql .=";";
                    DB::statement($sql);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'error' => 'not_found_provider',
                    'message' => 'not_found_provider',
                ], 500);    
            }


        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_store_provider',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        try {
            $params = $request->route()->parameters();
            $provider = Provider::findOrFail($params['id']);

            if ($provider) {
                $name = $provider->name;
                $provider->fields()->delete();
                $provider->delete();

                $sql = "DROP TABLE `{provider_$name}`;";
                DB::statement($sql);
            }  
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_provider',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
