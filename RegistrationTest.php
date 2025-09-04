<?php
use PHPUnit\Framework\TestCase;

final class RegistrationTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = require __DIR__ . '/../config/db.php';
    }

    public function testUserRegistration()
    {
        // Example: insert a user and verify
        $stmt = $this->conn->prepare("INSERT INTO users (fullName, email, mobile, password, address, dob) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Test User', 'test10@example.com', '0712345678', 'dummy', 'Test Address', '2000-01-01']);
        
        $lastId = $this->conn->lastInsertId();

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$lastId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($user, "User was not registered correctly");

        // Clean up
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$lastId]);
    }
}
