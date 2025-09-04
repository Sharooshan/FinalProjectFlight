<?php
use PHPUnit\Framework\TestCase;

final class UserManagementTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Include the DB connection and store it
        $this->conn = require __DIR__ . '/../config/db.php';
    }

    public function testFetchUsers()
    {
        $stmt = $this->conn->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($users, "No users found in the database");

        $firstUser = $users[0];
        $this->assertArrayHasKey('id', $firstUser);
        $this->assertArrayHasKey('fullName', $firstUser);
        $this->assertArrayHasKey('email', $firstUser);
    }

    public function testDeleteUser()
    {
        // Insert a dummy user
        $stmt = $this->conn->prepare("INSERT INTO users (fullName, email, mobile, password, address, dob) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Test User', 'test5@example.com', '0712345678', 'dummy', 'Test Address', '2000-01-01']);

        $lastId = $this->conn->lastInsertId();

        // Delete the dummy user
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$lastId]);

        // Verify deletion
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$lastId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse($user, "User was not deleted");
    }
}
