<?php

namespace Tests\Unit\Services;

use App\Http\Controllers\Employee\AttendanceController;
use PHPUnit\Framework\TestCase;

class GeofencingHaversineTest extends TestCase
{
    /**
     * Test calculateDistance returns 0 when coordinates are identical.
     */
    public function test_identical_coordinates_returns_zero_meters(): void
    {
        $lat = -6.2088;
        $lon = 106.8456;

        $distance = AttendanceController::calculateDistance($lat, $lon, $lat, $lon);

        $this->assertSame(0, $distance);
    }

    /**
     * Test calculateDistance on known distance between Monas and Bundaran HI Jakarta (~2.2 km).
     */
    public function test_known_landmarks_distance(): void
    {
        // Monas
        $monasLat = -6.175392;
        $monasLon = 106.827153;

        // Bundaran HI
        $hiLat = -6.195048;
        $hiLon = 106.823023;

        $distance = AttendanceController::calculateDistance($monasLat, $monasLon, $hiLat, $hiLon);

        // Expected distance around 2200 meters (+/- 50m tolerance)
        $this->assertGreaterThanOrEqual(2150, $distance);
        $this->assertLessThanOrEqual(2300, $distance);
    }

    /**
     * Test calculateDistance on short office geofencing radius (~100m).
     */
    public function test_short_distance_radius_accuracy(): void
    {
        $officeLat = -6.200000;
        $officeLon = 106.800000;

        // Moving approx 0.0009 degrees latitude is roughly 100 meters
        $userLat = -6.200900;
        $userLon = 106.800000;

        $distance = AttendanceController::calculateDistance($officeLat, $officeLon, $userLat, $userLon);

        $this->assertGreaterThanOrEqual(95, $distance);
        $this->assertLessThanOrEqual(105, $distance);
    }

    /**
     * Test calculateDistance symmetry (A to B equals B to A).
     */
    public function test_distance_is_symmetric(): void
    {
        $latA = -6.917464;
        $lonA = 107.619123;
        $latB = -7.257472;
        $lonB = 112.752090;

        $distAB = AttendanceController::calculateDistance($latA, $lonA, $latB, $lonB);
        $distBA = AttendanceController::calculateDistance($latB, $lonB, $latA, $lonA);

        $this->assertSame($distAB, $distBA);
    }
}
