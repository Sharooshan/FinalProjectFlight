<?php
use PHPUnit\Framework\TestCase;

final class UserLoginTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Include PDO connection from db.php
        $this->conn = require __DIR__ . '/../config/db.php';
    }

    /**
     * ✅ Test successful login with valid user credentials
     */
    public function testUserLoginSuccess(): void
    {
        $email = 'sharooshan123@gmail.com';   // must exist in DB
        $password = 'sharoo123';             // plaintext password for testing

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Assert: User exists
        $this->assertNotFalse($user, "User not found in database");

        // Assert: Password matches hash
        $this->assertTrue(
            password_verify($password, $user['password']),
            "Password does not match for existing user"
        );
    }

    /**
     * ❌ Test failed login for invalid user
     */
    public function testUserLoginFailure(): void
    {
        $email = 'nonexistentuser@example.com';
        $password = 'wrongpass';

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Assert: User should not exist
        $this->assertFalse($user, "Non-existent user should not be found");
    }

    /**
     * ❌ Test failed login for wrong password
     */
    public function testUserWrongPassword(): void
    {
        $email = 'sharooshan123@gmail.com'; // must exist in DB
        $password = 'wrongpassword';

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Assert: User exists
        $this->assertNotFalse($user, "Existing user not found");

        // Assert: Wrong password should fail
        $this->assertFalse(
            password_verify($password, $user['password']),
            "Password check should fail for wrong password"
        );
    }
}
