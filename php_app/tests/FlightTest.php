<?php
use PHPUnit\Framework\TestCase;

// --- Flight class definition ---
class Flight
{
    private $conn;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    // Insert a new flight
    public function insertFlight($flight_number, $departure_city, $arrival_city, $departure_time, $arrival_time, $price)
    {
        $stmt = $this->conn->prepare("INSERT INTO testflight 
            (flight_number, departure_city, arrival_city, departure_time, arrival_time, price) 
            VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$flight_number, $departure_city, $arrival_city, $departure_time, $arrival_time, $price]);
    }

    // Fetch by flight number
    public function getFlightByNumber($flight_number)
    {
        $stmt = $this->conn->prepare("SELECT * FROM testflight WHERE flight_number = ?");
        $stmt->execute([$flight_number]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: false;
    }

    // Filter flights by cities
    public function filterFlights($departure_city, $arrival_city)
    {
        $stmt = $this->conn->prepare("SELECT * FROM testflight WHERE departure_city = ? AND arrival_city = ?");
        $stmt->execute([$departure_city, $arrival_city]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// --- PHPUnit Test for Flight ---
final class FlightTest extends TestCase
{
    private $pdo;
    private $flight;

    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=finalproject', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->flight = new Flight($this->pdo);

        // Cleanup before each test to ensure a clean state
        $this->pdo->exec("DELETE FROM testflight");
    }

    // The tearDown() method is called after each test and can be used for cleanup.
    protected function tearDown(): void
    {
        $this->pdo->exec("DELETE FROM testflight");
        $this->pdo = null;
    }

    public function testInsertFlight()
    {
        $result = $this->flight->insertFlight("AB123", "Colombo", "Dubai", "2025-09-01 10:00:00", "2025-09-01 15:00:00", 500.00);
        $this->assertTrue($result);

        $flight = $this->flight->getFlightByNumber("AB123");
        $this->assertEquals("Colombo", $flight['departure_city']);
        $this->assertEquals("Dubai", $flight['arrival_city']);
    }

    public function testFetchFlightByNumber()
    {
        $this->flight->insertFlight("CD456", "London", "Paris", "2025-09-02 08:00:00", "2025-09-02 09:30:00", 120.00);

        $flight = $this->flight->getFlightByNumber("CD456");
        $this->assertEquals("Paris", $flight['arrival_city']);
        $this->assertEquals("London", $flight['departure_city']);
    }

    public function testFilterFlightsByCities()
    {
        $this->flight->insertFlight("EF789", "Tokyo", "Seoul", "2025-09-03 06:00:00", "2025-09-03 09:00:00", 300.00);
        $this->flight->insertFlight("GH101", "Tokyo", "Seoul", "2025-09-04 07:00:00", "2025-09-04 10:00:00", 350.00);

        $flights = $this->flight->filterFlights("Tokyo", "Seoul");
        $this->assertCount(2, $flights);
    }

    public function testFlightNotFound()
    {
        $flight = $this->flight->getFlightByNumber("ZZ999");
        $this->assertFalse($flight);
    }
}
