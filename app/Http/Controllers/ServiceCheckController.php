<?php

namespace App\Http\Controllers;

use App\Models\ServiceCheck;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ServiceCheckController extends Controller
{
    /**
     * Display a listing of service checks
     */
    public function index(Request $request)
    {
        try {
            // Comment out authorization temporarily to test
            // $this->authorizeForUser($request->user('api'), 'view', ServiceCheck::class);

            // Get parameters with defaults
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);
            $sortField = $request->input('SortField', 'id');
            $sortType = $request->input('SortType', 'desc');
            $search = $request->input('search', '');

            // Calculate offset
            $perPage = ($limit == "-1") ? 1000000 : (int)$limit;
            $offset = ($page * $perPage) - $perPage;

            // Base query
            $query = ServiceCheck::whereNull('deleted_at');

            // Apply search if provided
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('employee_number', 'LIKE', "%{$search}%")
                      ->orWhere('contact_number', 'LIKE', "%{$search}%");
                });
            }

            // Get total count before pagination
            $totalRows = $query->count();

            // Apply pagination and sorting
            $serviceChecks = $query->offset($offset)
                ->limit($perPage)
                ->orderBy($sortField, $sortType)
                ->get();

            // Format data
            $data = [];
            foreach ($serviceChecks as $check) {
                $data[] = [
                    'id' => $check->id,
                    'name' => $check->name,
                    'employee_number' => $check->employee_number,
                    'contact_number' => $check->contact_number,
                    'created_at' => $check->created_at ? $check->created_at->format('Y-m-d') : null,
                ];
            }

            return response()->json([
                'service_checks' => $data,
                'totalRows' => $totalRows,
            ], 200);

        } catch (\Exception $e) {
            Log::error('ServiceCheck Index Error: ' . $e->getMessage());
            Log::error('Line: ' . $e->getLine());
            Log::error('File: ' . $e->getFile());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching service checks',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Store a newly created service check
     */
    public function store(Request $request)
    {
        try {
            // Comment out authorization temporarily to test
            // $this->authorizeForUser($request->user('api'), 'create', ServiceCheck::class);

            // Log incoming request
            Log::info('ServiceCheck Store Request:', $request->all());

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'employee_number' => 'required|string|max:255',
                'contact_number' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::error('Validation Failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $serviceCheck = ServiceCheck::create([
                'name' => $request->input('name'),
                'employee_number' => $request->input('employee_number'),
                'contact_number' => $request->input('contact_number'),
            ]);

            Log::info('ServiceCheck Created:', $serviceCheck->toArray());

            return response()->json([
                'success' => true,
                'data' => $serviceCheck
            ], 201);

        } catch (\Exception $e) {
            Log::error('ServiceCheck Store Error: ' . $e->getMessage());
            Log::error('Line: ' . $e->getLine());
            Log::error('File: ' . $e->getFile());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error creating service check',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Display the specified service check
     */
    public function show($id)
    {
        try {
            $serviceCheck = ServiceCheck::whereNull('deleted_at')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $serviceCheck,
            ], 200);

        } catch (\Exception $e) {
            Log::error('ServiceCheck Show Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Service check not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified service check
     */
    public function update(Request $request, $id)
    {
        try {
            // Comment out authorization temporarily to test
            // $this->authorizeForUser($request->user('api'), 'update', ServiceCheck::class);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'employee_number' => 'required|string|max:255',
                'contact_number' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $serviceCheck = ServiceCheck::whereNull('deleted_at')->findOrFail($id);

            $serviceCheck->update([
                'name' => $request->input('name'),
                'employee_number' => $request->input('employee_number'),
                'contact_number' => $request->input('contact_number'),
            ]);

            return response()->json([
                'success' => true,
                'data' => $serviceCheck
            ], 200);

        } catch (\Exception $e) {
            Log::error('ServiceCheck Update Error: ' . $e->getMessage());
            Log::error('Line: ' . $e->getLine());
            Log::error('File: ' . $e->getFile());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error updating service check',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Remove the specified service check
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Comment out authorization temporarily to test
            // $this->authorizeForUser($request->user('api'), 'delete', ServiceCheck::class);

            $serviceCheck = ServiceCheck::findOrFail($id);
            $serviceCheck->deleted_at = Carbon::now();
            $serviceCheck->save();

            return response()->json([
                'success' => true,
                'message' => 'Service check deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('ServiceCheck Destroy Error: ' . $e->getMessage());
            Log::error('Line: ' . $e->getLine());
            Log::error('File: ' . $e->getFile());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting service check',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Delete multiple service checks by selection
     */
    public function delete_by_selection(Request $request)
    {
        try {
            // Comment out authorization temporarily to test
            // $this->authorizeForUser($request->user('api'), 'delete', ServiceCheck::class);

            $validator = Validator::make($request->all(), [
                'selectedIds' => 'required|array',
                'selectedIds.*' => 'integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $selectedIds = $request->input('selectedIds');

            ServiceCheck::whereIn('id', $selectedIds)
                ->update(['deleted_at' => Carbon::now()]);

            return response()->json([
                'success' => true,
                'message' => 'Service checks deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('ServiceCheck Delete By Selection Error: ' . $e->getMessage());
            Log::error('Line: ' . $e->getLine());
            Log::error('File: ' . $e->getFile());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting service checks',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
