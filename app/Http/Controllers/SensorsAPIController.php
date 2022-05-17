<?php

namespace App\Http\Controllers;


use App\Http\Requests\PostMeasurementRequest;
use App\Http\Services\SensorService;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;

class SensorsAPIController extends Controller
{
    private $sensorService;

    public function __construct(SensorService $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    /**
     * @OA\Get(
     *      path="/sensors/{uuid}",
     *      operationId="getStatus",
     *      tags={"Sensors"},
     *      summary="Get status of a sensor",
     *      description="Returns status of a sensor",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Sensor UUID",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     *     )
     * @param $uuid
     * @return JsonResponse
     */
    public function getStatus($uuid)
    {
        $sensor = Sensor::where(['uuid' => $uuid])->first();
        if ($sensor) {
            return response()->json([
                'status' => $sensor->status,
            ]);
        }
        return response()->json('No sensor found', 404);
    }

    /**
     * @OA\Get(
     *      path="/sensors/{uuid}/metrics",
     *      operationId="getMetrics",
     *      tags={"Sensors"},
     *      summary="Get metrics of a sensor",
     *      description="Returns metrics of a sensor",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Sensor UUID",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     *     )
     * @param $uuid
     * @return JsonResponse
     */
    public function getMetrics($uuid)
    {
        $sensor = Sensor::where(['uuid' => $uuid])->first();
        if ($sensor) {
            $maxLast30Days = $sensor->maxLast30Days();
            $avgLast30Days = $sensor->avgLast30Days();

            return response()->json([
                'maxLast30Days' => $maxLast30Days,
                'avgLast30Days' => $avgLast30Days,
            ]);
        }
        return response()->json('No metrics found', 404);
    }

    /**
     * @OA\Get(
     *      path="/sensors/{uuid}/alerts",
     *      operationId="getAlerts",
     *      tags={"Sensors"},
     *      summary="Get alerts of a sensor",
     *      description="Returns alerts of a sensor",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Sensor UUID",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     *     )
     * @param $uuid
     * @return JsonResponse
     */
    public function getAlerts($uuid)
    {
        $sensor = Sensor::where(['uuid' => $uuid])->first();
        if ($sensor) {

            if (!$sensor->alerts()->count()) {
                return response()->json('No alerts found', 404);
            }

            $alertsMeasurements = [];
            foreach ($sensor->alertsMeasurements() as $key => $value) {
                $alertsMeasurements['measurement' . ($key + 1)] = $value;
            }

            $data = array_merge(
                [
                    'startTime' => $sensor->firstAlertTime(),
                    'endTime' => $sensor->lastAlertTime(),
                ],
                $alertsMeasurements
            );
            return response()->json($data);
        }
        return response()->json('No sensor found', 404);
    }

    /**
     * @OA\Post(
     *      path="/sensors/{uuid}/measurements",
     *      operationId="postMeasurement",
     *      tags={"Sensors"},
     *      summary="Post measurement for a given sensor",
     *      description="Creates a measurement for a given sensor",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Sensor UUID",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="co2",
     *         in="query",
     *         description="CO2 value of measurement",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="time",
     *         in="query",
     *         description="Time of measurement",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2022-05-17"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     *     )
     * @param $uuid
     * @param PostMeasurementRequest $request
     * @return JsonResponse
     */
    public function handlePostMeasurement($uuid, PostMeasurementRequest $request)
    {
        try {
            $sensor = Sensor::firstOrCreate(
                ['uuid' => $uuid],
                [
                    'uuid' => $uuid,
                    'status' => config("constants.STATUS_OK")
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $data = array_merge(['uuid' => $uuid], $request->all());
        try {
            Measurement::create($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        try {
            $this->sensorService->checkStatus($sensor);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => 'success'], 200);
    }


}
