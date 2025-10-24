<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CoreAPIController extends Controller
{
    public function index()
    {
        $api_list = DB::table('core_apis')->get();
        return view('core.api.index', compact('api_list'));
    }

    public function sync()
    {
        try {
            // Retrieve the API key and base URL
            $apiData = DB::table('core_api_setup')->first();
            $apiKey = $apiData->api_key;
            $baseUrl = $apiData->base_url;
            // Make the GET request with the correct headers
            $response = Http::withHeaders([
                'api-key' => $apiKey, // Setting the 'api-key' header as required
                'Accept' => 'application/json',
            ])->get("$baseUrl/api/project/apis");

            // Check if the response is successful
            if ($response->failed()) {
                // Handle unsuccessful responses
                Log::error('API sync failed', ['status' => $response->status(), 'response' => $response->body()]);
                return response()->json(['status' => 400, 'msg' => 'Failed to synchronize APIs.']);
            }

            // Parse the JSON response
            $data = $response->json();

            // Validate the structure of the response
            if (!isset($data['api_list']) || !is_array($data['api_list'])) {
                Log::error('Invalid API response structure', ['response' => $data]);
                return response()->json(['status' => 400, 'msg' => 'Unexpected API response format.']);
            }

            // Prepare data for batch insertion
            $apiRecords = array_map(function ($value) {
                return [
                    'api_id' => $value['id'] ?? null,
                    'api_name' => $value['api_name'] ?? '',
                    'api_end_point' => $value['api_end_point'] ?? '',
                    'description' => $value['description'] ?? '',
                    'parameters' => $value['parameters'],
                    'table_name' => $value['table_name'] ?? '',
                ];
            }, $data['api_list']);

            // Use a transaction to ensure atomic operation

            DB::table('core_apis')->truncate();
            DB::table('core_apis')->insert($apiRecords); // Batch insert for performance


            return response()->json(['status' => 200, 'msg' => 'API synchronized successfully.']);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-specific exceptions
            Log::error('Database error during API sync', ['error' => $e->getMessage()]);
            return response()->json(['status' => 500, 'msg' => 'Database error occurred.']);
        }
    }

    public function importAPISData(Request $request)
    {
        // For large imports: Remove PHP limits (use cautiously in web context; prefer queues for prod)
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', 0);
        $apiEndPoints = $request->input('api_end_points');
        $CoreAPI = DB::table('core_api_setup')->first();
        $apiKey = $CoreAPI->api_key;
        $baseUrl = $CoreAPI->base_url;
        $prefix = 'core_'; // Define the prefix

        foreach ($apiEndPoints as $api) {
            $apiData = DB::table('core_apis')->where('api_end_point', $api)->first(['parameters', 'table_name']);
            $parameter = $apiData->parameters ?? null;
            $tableName = $prefix . $apiData->table_name;
            // Special handling for employee_tools API with application_name parameter
            if ($api === 'employee_tools') {
                $applicationName = 'connect';
                $response = Http::withHeaders([
                    'api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])->get("$baseUrl/api/$api", ['application_name' => $applicationName]);

                if ($response->failed()) {
                    Log::error("API sync failed for $api: HTTP Status {$response->status()}");
                    continue;
                }

                $data = $response->json();

                if (!isset($data['list']) || !is_array($data['list'])) {
                    Log::error("Invalid API response structure for $api");
                    continue;
                }

                // Get ALL employee IDs from API response (regardless of status)
                $apiEmployeeIds = array_column($data['list'], 'employee_id');
              
                // Get existing employee IDs from our database
                $existingEmployeeIds = DB::table('users')
                    ->where('type', 'user')
                    ->whereNotNull('emp_id')
                    ->pluck('emp_id')
                    ->toArray();
  
                // Find employees in DB but not in API response at all
                $missingEmployeeIds = array_diff($existingEmployeeIds, $apiEmployeeIds);

                // Disable completely missing employees
                if (!empty($missingEmployeeIds)) {
                    DB::table('users')
                        ->whereIn('emp_id', $missingEmployeeIds)
                        ->update([
                            'status' => 'D',
                            'updated_at' => now()
                        ]);
                }

                // Process all employees from API (both active and inactive)
                foreach ($data['list'] as $employee) {
                    try {
                        $userData = [
                            'name' => $employee['emp_name'] ?? '',
                            'emp_id' => $employee['employee_id'],
                            'status' => 'A',
                            'email' => $employee['emp_email'] ?? null,
                            'phone' => $employee['emp_contact'] ?? null,
                            'type' => 'user',
                            'password' => Hash::make($employee['emp_contact'] ?? 'default123'),
                            'updated_at' => now(),
                        ];

                        // Only set created_at for new records
                        DB::table('users')->updateOrInsert(
                            ['emp_id' => $employee['employee_id']],
                            $userData
                        );
                    } catch (\Throwable $e) {
                        Log::error("Failed processing employee {$employee['employee_id']}: {$e->getMessage()}");
                    }
                }

                continue;
            }

            // Normal processing for other APIs
              if ($parameter) {
                $ids = $this->getParameterValues($parameter, $api);

                if ($ids->isEmpty()) {
                    Log::warning("No parameter values found for API $api (parameter: $parameter), skipping");
                    continue;
                }

                // Chunk the IDs to avoid overwhelming the API or DB
                $idChunkSize = 500; // Adjust based on API limits
                $ids->chunk($idChunkSize)->each(function ($idChunk) use ($tableName, $baseUrl, $apiKey, $api, $parameter, &$success) {
                    foreach ($idChunk as $id) {
                        $response = Http::withHeaders([
                            'api-key' => $apiKey,
                            'Accept' => 'application/json',
                        ])->timeout(60)
                          ->get("$baseUrl/api/$api", [$parameter => $id]);

                        $processSuccess = $this->processApiResponse($tableName, $response, $api, $parameter, $id);
                        if (!$processSuccess) {
                            $success = false;
                        }
                    }
                });
            } else {
                $response = Http::withHeaders([
                    'api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])->get("$baseUrl/api/$api");

                $this->processApiResponse($tableName, $response, $api);
            }
        }

        return response()->json(['status' => 200, 'msg' => 'APIs synchronized successfully']);
    }

      private function getParameterValues($parameter, $api)
    {
        // Dynamic fallback: Assume source table is 'core_' + parameter without '_id'
        $sourceTable = null;
        if (substr($parameter, -3) === '_id') {
            $paramBase = substr($parameter, 0, -3);
            $sourceTable = 'core_' . $paramBase;
        }

        // Explicit overrides/mappings for known cases (extend as needed)
        $explicitMappings = [
            'state_id' => 'core_state',
            'district_id' => 'core_district',
            // Add more, e.g., 'region_id' => 'core_region',
        ];

        $finalSourceTable = $explicitMappings[$parameter] ?? $sourceTable;

        if (!$finalSourceTable || !Schema::hasTable($finalSourceTable)) {
            Log::warning("Source table '$finalSourceTable' does not exist for parameter '$parameter' in API '$api', skipping");
            return collect();
        }

        return DB::table($finalSourceTable)->pluck('id');
    }

    private function processApiResponse($tableName, $response, $api, $parameter = null, $id = null)
    {
        // 🔄 Extend script execution time to 5 minutes
        //set_time_limit(300);

        if ($response->failed()) {
            $errorMsg = $parameter && $id ? "$parameter=$id" : "No parameter";
            Log::error("API sync failed for $api with $errorMsg: HTTP Status {$response->status()}. Response: {$response->body()}");
            return response()->json(['status' => 400, 'msg' => 'Failed to synchronize APIs.']);
        }

        $data = $response->json();

        if (!isset($data['list']) || !is_array($data['list'])) {
            $errorMsg = $parameter && $id ? "$parameter=$id" : "No parameter";
            Log::error("Invalid API response structure for $api with $errorMsg. Response: " . json_encode($data));
            return response()->json(['status' => 400, 'msg' => 'Unexpected API response format.']);
        }

        $columns = array_keys($data['list'][0]);

        // Dynamically create a table if it doesn't exist
        DB::statement($this->generateCreateTableQuery($tableName, $columns));

        foreach ($data['list'] as $item) {
            DB::table($tableName)->updateOrInsert(
                ['id' => $item['id']],
                $item
            );

            // 🟡 Additional logic for distributors
            // if ($api === 'distributors') {
            //     $email = $item['email'] ?? null;
            //     $phone = $item['phone'] ?? null;

            //     try {
            //         $existingUser = DB::table('users')
            //             ->where(function ($q) use ($email, $phone) {
            //                 if ($email) $q->orWhere('email', $email);
            //                 if ($phone) $q->orWhere('phone', $phone);
            //             })->first();

            //         if (!$existingUser) {
            //             $userId = DB::table('users')->insertGetId([
            //                 'name' => $item['name'] ?? '',
            //                 'email' => $email,
            //                 'phone' => $phone,
            //                 'type' => 'distributor',
            //                 'password' => Hash::make($phone ?: 'default123', ['rounds' => 4]),
            //                 'created_at' => now(),
            //                 'updated_at' => now(),
            //             ]);
            //         } else {
            //             $userId = $existingUser->id;
            //         }

            //         DB::table($tableName)->where('id', $item['id'])->update(['reference_id' => $userId]);
            //     } catch (\Throwable $e) {
            //         Log::warning("User insert skipped for distributor ID {$item['id']}: " . $e->getMessage());
            //         continue;
            //     }
            // }
        }

        return response()->json(['status' => 200, 'msg' => "APIs synchronized successfully" . ($parameter && $id ? " for $parameter=$id" : "")]);
    }


    private function generateCreateTableQuery($tableName, $columns)
    {
        $createTableQuery = "CREATE TABLE IF NOT EXISTS `$tableName` (";

        foreach ($columns as $column) {
            $createTableQuery .= "`$column` VARCHAR(255),"; // Adjust data types as needed
        }

        $createTableQuery .= "PRIMARY KEY (`id`));";

        return $createTableQuery;
    }
}
