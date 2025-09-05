<?php
use PHPUnit\Framework\TestCase;

final class FlightTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = require __DIR__ . '/../config/db.php';
    }

    public function testInsertFlight()
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO flights (flight_number, departure_city, arrival_city, departure_time, arrival_time, price) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $result = $stmt->execute([
            'FL123',
            'Colombo',
            'Dubai',
            '2025-09-05 08:00:00',
            '2025-09-05 12:00:00',
            500
        ]);

        $this->assertTrue($result, "Flight insert failed");
    }

    public function testFetchFlightByNumber()
    {
        $stmt = $this->conn->prepare("SELECT * FROM flights WHERE flight_number = ?");
        $stmt->execute(['FL123']);
        $flight = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($flight, "Flight not found");
        $this->assertEquals('Colombo', $flight['departure_city'], "Departure city mismatch");
        $this->assertEquals('Dubai', $flight['arrival_city'], "Arrival city mismatch");
    }

    public function testFilterFlightsByCities()
    {
        $stmt = $this->conn->prepare("SELECT * FROM flights WHERE departure_city = ? AND arrival_city = ?");
        $stmt->execute(['Colombo', 'Dubai']);
        $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($flights, "No flights found for Colombo → Dubai");
    }

    public function testFlightNotFound()
    {
        $stmt = $this->conn->prepare("SELECT * FROM flights WHERE flight_number = ?");
        $stmt->execute(['INVALID123']);
        $flight = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse($flight, "Invalid flight number should not return a flight");
    }
}
