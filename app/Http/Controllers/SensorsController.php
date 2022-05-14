<?php

namespace App\Http\Controllers;


use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Http\Request;

class SensorsController extends Controller
{
    public function getStatus($id)
    {
        return response()->json(Sensor::find($id)->status);
    }

    public function getMetrics($id)
    {
        return response()->json(Sensor::find($id)->metrics());
    }

    public function getAlerts($id)
    {
        return response()->json(Sensor::find($id)->alerts());
    }

    public function createMeasurement(Request $request)
    {
        $author = Measurement::create($request->all());

        return response()->json($author, 201);
    }
}
