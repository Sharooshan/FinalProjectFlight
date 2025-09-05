<?php
use PHPUnit\Framework\TestCase;

// --- Booking class ---
class Booking
{
    private $conn;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    // Create a booking
    public function createBooking($user_id, $user_name, $legId, $flightDate, $startingAirport, $destinationAirport, $totalFare, $seatsBooked, $airline, $departureTime, $arrivalTime, $duration, $isNonStop)
    {
        $stmt = $this->conn->prepare("INSERT INTO bookings
            (user_id, user_name, legId, flightDate, startingAirport, destinationAirport, totalFare, seatsBooked, airline, departureTime, arrivalTime, duration, isNonStop, finalTotal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $finalTotal = $totalFare * $seatsBooked;
        return $stmt->execute([$user_id, $user_name, $legId, $flightDate, $startingAirport, $destinationAirport, $totalFare, $seatsBooked, $airline, $departureTime, $arrivalTime, $duration, $isNonStop, $finalTotal]);
    }

    public function getBookingById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function filterBookings($user_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// --- PHPUnit Test ---
final class BookingFlightTest extends TestCase
{
    private $pdo;
    private $booking;
    private $user_id = 999; // Dummy user id for tests

    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=finalproject', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->booking = new Booking($this->pdo);

        // Insert dummy user if not exists
        $this->pdo->exec("INSERT INTO users (id, fullName, email, password) 
                          VALUES ({$this->user_id}, 'Test User', 'test@example.com', 'pass123')
                          ON DUPLICATE KEY UPDATE fullName='Test User'");

        // Optional: clear bookings for test user
        $this->pdo->exec("DELETE FROM bookings WHERE user_id = {$this->user_id}");
    }

    public function testCreateBooking()
    {
        $result = $this->booking->createBooking(
            $this->user_id, 'Test User', 'LEG123', '2025-09-10', 'ATL', 'BOS', 200.0, 2, 'JetBlue', '2025-09-10 08:00:00', '2025-09-10 12:00:00', 'PT4H', 1
        );
        $this->assertTrue($result);
    }

    public function testFetchBookingById()
    {
        $this->booking->createBooking(
            $this->user_id, 'Test User', 'LEG456', '2025-09-12', 'ATL', 'BOS', 150.0, 1, 'United', '2025-09-12 09:00:00', '2025-09-12 13:00:00', 'PT4H', 0
        );

        $booking = $this->booking->getBookingById($this->pdo->lastInsertId());
        $this->assertEquals('Test User', $booking['user_name']);
    }

    public function testFilterBookings()
    {
        $this->booking->createBooking(
            $this->user_id, 'Test User', 'LEG789', '2025-09-15', 'ATL', 'BOS', 180.0, 3, 'Delta', '2025-09-15 07:00:00', '2025-09-15 11:00:00', 'PT4H', 1
        );

        $bookings = $this->booking->filterBookings($this->user_id);
        $this->assertNotEmpty($bookings);
        $this->assertEquals('Test User', $bookings[0]['user_name']);
    }
}
