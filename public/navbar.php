<?php session_start(); ?>
<style>
/* Creative Navbar Brand - Text Only with Stickers */
.navbar-brand {
    font-weight: 900;
    font-size: 2rem;
    display: flex;
    align-items: center;
    gap: 8px; /* spacing between stickers and text */
    background: linear-gradient(90deg, #0b5fff 0%, #ffffff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

/* Sticker animation */
.navbar-brand .sticker {
    font-size: 1.5rem;
    animation: bounce 2s infinite;
}

/* Bounce animation for stickers */
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
        <span class="sticker">✈️</span>
        IntelliFlight 
        <span class="sticker">🌍</span>
    </a>
    <div class="d-flex">
      <?php if (isset($_SESSION['user_name'])): ?>
          <span class="me-3">Hello, <?php echo $_SESSION['user_name']; ?></span>
          <a class="btn btn-outline-danger" href="logout.php">Logout</a>
      <?php else: ?>
          <a class="btn btn-outline-primary me-2" href="login.php">Login</a>
          <a class="btn btn-primary" href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
