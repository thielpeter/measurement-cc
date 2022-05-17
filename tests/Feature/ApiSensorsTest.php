<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ApiSensorsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sensor post endpoint
     *
     * @return void
     */
    public function test_post_measurement()
    {
        $response = $this->postJson('api/v1/sensors/1/measurements', ['co2' => 2000, 'time' => Carbon::now()->format('Y-m-d H:i:s')]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => 'success'
            ]);
    }

    /**
     * Test sensor status endpoint
     *
     * @return void
     */
    public function test_get_status_warn()
    {
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 2000, 'time' => Carbon::now()->format('Y-m-d H:i:s')]);

        $response = $this->get('api/v1/sensors/1');

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 'WARN'
            ]);
    }

    /**
     * Test sensor status set to ALERT after 3 or more consecutive measurements higher than 2000
     *
     * @return void
     */
    public function test_get_status_alert()
    {
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 2000, 'time' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s')]);
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 2000, 'time' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s')]);
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 2000, 'time' => Carbon::now()->format('Y-m-d H:i:s')]);

        $response = $this->get('api/v1/sensors/1');

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ALERT',
            ]);
    }

    /**
     * Test sensor status endpoint
     *
     * @return void
     */
    public function test_get_metrics()
    {
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 4000, 'time' => Carbon::now()->subDays(31)->format('Y-m-d H:i:s')]);
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 3000, 'time' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s')]);
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 2000, 'time' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s')]);
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 1000, 'time' => Carbon::now()->format('Y-m-d H:i:s')]);

        $response = $this->get('api/v1/sensors/1/metrics');

        $response
            ->assertStatus(200)
            ->assertJson([
                'maxLast30Days' => 3000,
                'avgLast30Days' => 2000,
            ]);
    }

    /**
     * Test sensor alerts endpoint
     *
     * @return void
     */
    public function test_get_alerts()
    {
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 2000, 'time' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s')]);
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 2000, 'time' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s')]);
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 3000, 'time' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s')]);
        $this->postJson('api/v1/sensors/1/measurements', ['co2' => 4000, 'time' => Carbon::now()->format('Y-m-d H:i:s')]);

        $response = $this->get('api/v1/sensors/1/alerts');

        $response
            ->assertStatus(200)
            ->assertJson([
                'startTime' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
                'endTime' => Carbon::now()->format('Y-m-d H:i:s'),
                'measurement1' => 3000,
                'measurement2' => 4000,
            ]);
    }
}
