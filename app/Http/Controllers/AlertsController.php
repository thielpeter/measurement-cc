<?php

namespace App\Http\Controllers;


use App\Http\Requests\PostMeasurementRequest;
use App\Models\Measurement;
use App\Models\Sensor;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function Symfony\Component\Mime\Header\all;

class AlertsController extends Controller
{
    public function checkAlert($uuid)
    {
//        $measurements = Measurement::where(['uuid' => $uuid])->all();
    }
}
