<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Scenario;

class ScenarioController extends Controller
{
    /**
     * List active scenarios.
     */
    public function index()
    {
        // Assuming there is an 'is_active' or we just return all
        $scenarios = Scenario::all();
        
        return response()->json(['data' => $scenarios]);
    }

    /**
     * View scenario details.
     */
    public function show(Scenario $scenario)
    {
        return response()->json(['data' => $scenario]);
    }
}
