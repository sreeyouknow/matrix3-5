<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="login.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    /* Enable scrolling for the entire body */
    body, html {
      height: 100%;
      overflow-y: auto; /* Ensure the page scrolls vertically */
    }

    /* Hide image on smaller screens */
    @media (max-width: 768px) {
      .login-image {
        display: none;
      }
    }

    /* Center-align form and buttons for mobile view */
    @media (max-width: 576px) {
      .form-container {
        text-align: center;
        padding: 0 10px; /* Add padding for better spacing on small screens */
      }
    }

    /* Ensure the section takes full height for layout alignment */
    section {
      min-height: 100vh; /* Adjust height to avoid clipping */
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>
<body>
<section>
  <div class="container py-5">
    <div class="row d-flex align-items-center justify-content-center">
      <!-- Image Section -->
      <div class="col-md-8 col-lg-7 col-xl-6 login-image">
        <img src="assets/imgs/login.png" class="img-fluid" alt="Phone image" height="300px" width="600px">
      </div>
      <!-- Form Section -->
      <div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1 form-container">
        <!-- Displaying message from session as alert -->
        <?php
        if (isset($_SESSION['message'])) {
          echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
          echo $_SESSION['message'];
          echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>';
          echo '</div>';
          unset($_SESSION['message']);
        }
        ?>

        <form action="login_process.php" method="POST">
          <p class="text-center h1 fw-bold mb-4 mx-1 mx-md-3 mt-3">Login</p>

          <!-- Phone input -->
          <div class="form-outline mb-4">
            <label class="form-label" for="phone">Phone Number:</label>
            <input type="text" id="phone" class="form-control form-control-lg py-3" name="phone" required autocomplete="off" placeholder="enter your phone number" style="border-radius:25px;" />
          </div>

          <!-- Password input -->
          <div class="form-outline mb-4 position-relative">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" class="form-control form-control-lg py-3" name="password" required autocomplete="off" placeholder="enter your password" style="border-radius:25px;" />
            <!-- Toggle Eye Icon -->
            <i class="bi bi-eye position-absolute" id="togglePassword" style="top: 50%; right: 15px; transform: translateY(10%); cursor: pointer;"></i>
          </div>

          <!-- Submit button -->
          <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
            <input type="submit" value="Sign in" name="login" class="btn btn-warning btn-lg text-light my-2 py-3" style="width:100%; border-radius: 30px; font-weight:600;" />
          </div>
        </form><br>

        <!-- Go to Home Button -->
        <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
          <a href="index.html" class="btn btn-secondary btn-lg text-light py-3" style="width:100%; border-radius: 30px; font-weight:600;">Go to Home</a>
        </div>

        <p class="text-center mt-3">I don't have an account? <a href="register.php" class="text-warning">Register Here</a></p>
      </div>
    </div>
  </div>
</section>

<!-- Bootstrap JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
  // Password Toggle Function
  document.querySelector('#togglePassword').addEventListener('click', function () {
    const passwordField = document.querySelector('#password');
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);

    // Toggle eye-slash icon
    if (type === 'password') {
      this.classList.remove('bi-eye-slash');
      this.classList.add('bi-eye');
    } else {
      this.classList.remove('bi-eye');
      this.classList.add('bi-eye-slash');
    }
  });
</script>
</body>
</html>
