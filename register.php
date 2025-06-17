<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
          background-color: #f8f9fa;
          font-family: Arial, sans-serif;
        }

        .a {
          display: flex;
          justify-content: space-between;
          margin: 5% auto;
          background-color: #ffffff;
          padding: 30px;
          border-radius: 20px;
          box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
          max-width: 90%;
        }

        .register-container {
          flex: 1;
          padding-right: 20px;
        }

        .register-container h2 {
          text-align: center;
          font-weight: 700;
          color: #333;
          margin-bottom: 10px;
        }

        .form-group {
          margin-bottom: 15px;
        }

        .form-group label {
          display: block;
          margin-bottom: 5px;
          font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
          border-radius: 8px;
          padding: 10px;
          width: 100%;
        }
        .radio-group {
        display: flex;
        justify-content: space-between;
        gap: 130px;
        margin-top: 10px;
        }

        .radio-group label {
          margin-right: 10px;
          font-weight: 500;
        }

        .radio-group input {
          margin-right: -30%;
          margin-bottom: 1.5%;
          
        }
        .form-row {
          display: flex;
          justify-content: space-between;
          gap: 10px;
        }
        .form-row .form-group {
          flex: 1;
        }
        .btn-primary {
          width: 50%;
          margin: 20px auto;
          display: block;
          padding: 10px;
          font-size: 16px;
          background-color: #e9b10a;
          border-radius: 8px;
          border: none;
          transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
          background-color: #e9b10a;
          transform: scale(1.05);
        }
        .error-message {
          font-size: 0.9em;
          color: red;
          display: none;
        }
        .position-relative {
          position: relative;
        }

        .password-toggle {
          position: absolute;
          top: 50%;
          right: 10px;
          transform: translateY(-50%);
          cursor: pointer;
        }

        .image-container {
          width: 40%;
          display: flex;
          justify-content: center;
          align-items: center;
        }

        .image-container img {
          max-width: 100%;
          border-radius: 15px;
        }
        .bottom{
          display: flex;
          flex-direction: row;
          margin-left: 33%;
        }
        .bottom p{
            margin-top: 2.5%;
        }
        .bottom button{
          background-color: white; 
          color: blue;
          border:none;
        }
        .bottom button:hover {
          background-color: white; 
          color: blue;
          border:none;
        }

        /* General adjustments for mobile layout */
        @media (max-width: 768px) {
        .a {
        flex-direction: column;
        padding: 15px;
        max-width: 100%;
        }

        .register-container {
          padding: 0;
        }

        .form-group label {
          font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
          font-size: 14px;
          padding: 10px;
        }
        .bottom{
          margin-left: 20%;
        }

        .form-row {
          flex-direction: column; /* Stack inputs vertically */
          gap: 10px;
        }

        .radio-group {
          flex-direction: row; /* Stack radio buttons */
          gap: 50px;
          align-items: flex-start;
        }

        .radio-group label {
          font-size: 14px;
        }
        .radio-group input {
          margin-right: -20%;
          margin-top: 1%;
          
        }
        .btn-primary {
        width: 100%; /* Full width button */
        font-size: 16px;
        margin: 15px 0;
        }

        .image-container {
        display: none; /* Hide image on mobile */
        }
        }
  </style>
  
</head>

<body>
    <div class="a">
        <div class="register-container">
            <h2>Register</h2>
            <form id="registerForm" action="register_process.php" method="POST" novalidate>
                <div class="form-row">

                    <!-- Name Field -->
                    <div class="form-group">
                        <label for="username"><i class="bi bi-person-circle"></i> Name:</label>
                        <input type="text" class="form-control" id="username" pattern="^[a-zA-Z\s]{1,30}$" placeholder="Enter Your Name" name="username" required>
                        <span class="error-message" id="usernameError">Name must contain only letters and spaces (max 30 characters).</span>
                    </div>

                    <!-- Phone Field -->
                    <div class="form-group">
                        <label for="phone"><i class="bi bi-phone"></i> Phone Number:</label>
                        <input type="text" class="form-control" id="phone" name="phone" pattern="^\d{10}$" placeholder="Enter Your Mobile Number" maxlength="10" required>
                        <span class="error-message" id="phoneError">Phone number must be 10 digits.</span>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email"><i class="bi bi-envelope"></i> Email (optional):</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Your Email">
                        <span class="error-message" id="emailError">Invalid email format.</span>
                    </div>

                    <!-- Date of Birth -->
                    <div class="form-group">
                        <label for="dob"><i class="bi bi-calendar"></i> Date of Birth:</label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                        <span class="error-message" id="dobError">Future dates are not allowed.</span>
                    </div>
                </div>

                <!-- Gender -->
                <div class="form-group">
                    <label><i class="bi bi-gender-ambiguous"></i> Gender:</label>
                    <div class="radio-group">
                        <input type="radio" id="male" name="gender" value="Male" required>
                        <label for="male">Male</label>
                        <input type="radio" id="female" name="gender" value="Female">
                        <label for="female">Female</label>
                        <input type="radio" id="other" name="gender" value="Other">
                        <label for="other">Other</label>
                    </div>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address"><i class="bi bi-house"></i> Address:</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    <span class="error-message" id="addressError">Address is required.</span>
                </div>

                <!-- Pincode -->
                <div class="form-group">
                    <label for="pincode"><i class="bi bi-pin-map"></i> Pincode:</label>
                    <input type="text" class="form-control" id="pincode" name="pincode" pattern="^\d{6}$" placeholder="Enter Your Pincode" maxlength="6" required>
                    <span class="error-message" id="pincodeError">Pincode must be 6 digits.</span>
                </div>

                <!-- Password -->
                <div class="form-group position-relative">
                    <label for="password"><i class="bi bi-lock"></i> Password:</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                    <i class="bi bi-eye password-toggle" id="togglePassword" style="top: 50%; right: 15px; transform: translateY(10%); cursor: pointer;"></i>
                    <span class="error-message" id="passwordError">Password must be at least 6 characters long.</span>
                </div>

                <!-- Referral Code -->
                  <div class="form-group">
                      <label for="referral_code"><i class="bi bi-gift"></i> Referral Code:</label>
                      <input type="text" class="form-control" id="referral_code" name="referral_code">
                      <span class="error-message" id="referralError" style="display: none;"></span> <!-- Initially hidden -->
                  </div>

                <label>
                    <input type="checkbox" id="termsCheckbox"> I agree to the <a href="javascript:void(0);" id="termsBtn">Terms and Conditions</a>
                </label>
                <span class="error-message" id="termsError">You must agree to the terms and conditions to register.</span>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <div class="bottom">
                <p>Already have an account? <a href="login.php" class="text-warning">Login</a></p>
            </div>
        </div>

        <div class="alert alert-info mt-3" role="alert">
            <strong>Note:</strong>
            <p>1.Amsgreenlife நிறுவனத்தில் இணைந்தவர்க்கு ஐந்து வேலை நாட்களுக்குள் பொருள் தரப்படும் , வாங்க தவறினால் நேரடியாக தலைமை அலுவலகத்தில் பெற்றுக்கொள்ளவும் "Those who join Amsgreenlife will receive the product within five working days. If not collected,
                it must be picked up directly from the head office."</p>
            <p>2.முதல் படி :வேலை செய்தல் இரண்டாம் படி :பின் தொடர்தல் மூன்றாம் படி: பின் தொடர்தல் - First step: Initiation , working - Second step: Continuation follow our team - Third step: Follow our team - Fourth step : Follow our team</p>
            <p>3.ஒரு நபருக்கு ஒரு ID மட்டுமே அனுமதி "Only one ID is allowed per person."</p>
            <p>4.payment withdraw செய்த 3 மணி நேரம் முதல் 48 மணி நேரத்திற்குள் பணம் அனுப்பப்படும் "The payment will be sent within 48 hours from the time the withdrawal was made."</p>
            <p>5.உங்களுடைய வங்கிக் கணக்கு விவரங்களை தவறாக பதிவு செய்து பணம் வரவில்லை என்றால் நிறுவனம் பொறுப்பல்ல "If the payment has not been received due to incorrect bank account details provided by you, the company is not responsible."</p>
            <p>6.Amsgreenlife நிறுவனத்தின் பெயரையோ லோகோவையோ வைத்து தவறாக வழி நடத்துபவர்களுக்கு நிறுவனம் தக்க நடவடிக்கை எடுக்கப்படும் "The company will take appropriate action against anyone misusing the name or logo of Amsgreenlife."</p>
            <p>7.Amsgreenslife நிறுவனத்தில் இணைந்த பிறகு பணம் திரும்ப பெற இயலாது "Once joined with Amsgreenslife, a refund will not be possible."</p><a href="mailto:amsgreenlife7@gmail.com">amsgreenlife7@gmail.com</a>.
        </div>

        <!-- Image
    <div class="image-container">
      <img src="assets/imgs/signup.png" alt="Sign-up">
    </div>
  </div> -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
              const termsCheckbox = document.getElementById("termsCheckbox");
              const termsError = document.getElementById("termsError");
              const registerForm = document.getElementById("registerForm");
            
              // Display message when the terms checkbox is clicked
              termsCheckbox.addEventListener("change", function () {
                if (this.checked) {
                  Swal.fire({
                    title: "Thank you for agreeing!",
                    html: `
                      <b>1. Amsgreenlife நிறுவனத்தில் இணைந்தவர்க்கு ஐந்து வேலை நாட்களுக்குள் பொருள் தரப்படும், வாங்க தவறினால் நேரடியாக தலைமை அலுவலகத்தில் பெற்றுக்கொள்ளவும்</b><br>
                      <i>"Those who join Amsgreenlife will receive the product within five working days. If not collected, it must be picked up directly from the head office."</i><br><br>
                      <b>2. Amsgreenlife terms...</b>
                    `,
                    icon: "success",
                    confirmButtonText: "OK",
                  });
                }
              });
            
              // Toggle Password Visibility
              document.getElementById("togglePassword").addEventListener("click", function () {
                const passwordInput = document.getElementById("password");
                const icon = this;
                if (passwordInput.type === "password") {
                  passwordInput.type = "text";
                  icon.classList.replace("bi-eye", "bi-eye-slash");
                } else {
                  passwordInput.type = "password";
                  icon.classList.replace("bi-eye-slash", "bi-eye");
                }
              });
            
              // Phone number validation
              document.getElementById("phone").addEventListener("blur", function () {
                const phone = this.value;
                const phoneError = document.getElementById("phoneError");
                if (/^\d{10}$/.test(phone)) {
                  phoneError.style.display = "none";
                  fetch(`check_phone.php?phone=${phone}`)
                    .then((response) => response.json())
                    .then((data) => {
                      if (data.status === "exists") {
                        phoneError.textContent = "This phone number is already registered.";
                        phoneError.style.display = "block";
                      }
                    })
                    .catch(() => {
                      phoneError.textContent = "Error checking phone number.";
                      phoneError.style.display = "block";
                    });
                } else {
                  phoneError.textContent = "Phone number must be 10 digits.";
                  phoneError.style.display = "block";
                }
              });
            
              // Referral code validation
              document.getElementById("referral_code").addEventListener("blur", function () {
                  const referralCode = this.value;
                  const referralError = document.getElementById("referralError");
                  
                  // Reset the error message
                  referralError.style.display = "none"; // Hide the error message initially

                  if (referralCode) {
                      fetch(`check_referral.php?referral_code=${referralCode}`)
                          .then((response) => {
                              // Check if the response is JSON
                              if (!response.ok) {
                                  throw new Error("Network response was not ok");
                              }
                              return response.json(); // Parse JSON response
                          })
                          .then((data) => {
                              if (data.status === "invalid") {
                                  referralError.textContent = "This referral code is invalid.";
                                  referralError.style.display = "block"; // Show the error message
                              } else if (data.status === "valid") {
                                  referralError.textContent = `Referral code is valid! Referrer: ${data.username}`;
                                  referralError.style.color = "green";
                                  referralError.style.display = "block"; // Show the success message
                              }
                          })
                          .catch((error) => {
                              console.error("Error:", error); // Log the error for debugging
                              referralError.textContent = "Error checking the referral code.";
                              referralError.style.display = "block"; // Show the error message
                          });
                  }
              });
            
              // Date of birth validation
              const dobInput = document.getElementById("dob");
              const today = new Date().toISOString().split("T")[0];
              dobInput.setAttribute("max", today);
            
              dobInput.addEventListener("change", function () {
                const selectedDate = new Date(this.value);
                const currentDate = new Date(today);
                const dobError = document.getElementById("dobError");
                if (selectedDate > currentDate) {
                  this.value = "";
                  dobError.style.display = "block";
                } else {
                  dobError.style.display = "none";
                }
              });
            
              // Form validation and submission
              registerForm.addEventListener("submit", function (event) {
                event.preventDefault();
            
                let isValid = true;
            
                ["username", "phone", "dob", "address", "password"].forEach((fieldId) => {
                  const field = document.getElementById(fieldId);
                  const error = document.getElementById(fieldId + "Error");
                  if (!field.validity.valid) {
                    error.style.display = "block";
                    isValid = false;
                  } else {
                    error.style.display = "none";
                  }
                });
            
                if (!termsCheckbox.checked) {
                  termsError.style.display = "block";
                  isValid = false;
                } else {
                  termsError.style.display = "none";
                }
            
                if (isValid) {
                  const formData = new FormData(registerForm);
                  fetch("register_process.php", {
                    method: "POST",
                    body: formData,
                  })
                    .then((response) => response.json())
                    .then((data) => {
                      if (data.status === "success") {
                        Swal.fire({
                          icon: "success",
                          title: "Registration Successful!",
                          text: "You have successfully registered. Please check your email for further instructions.",
                          confirmButtonText: "OK",
                        }).then(() => {
                          window.location.href = "login.php";
                        });
                      } else {
                        Swal.fire({
                          icon: "error",
                          title: "Registration Failed!",
                          text: data.message,
                          confirmButtonText: "OK",
                        });
                      }
                    })
                    .catch(() => {
                      Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "An error occurred while registering. Please try again later.",
                        confirmButtonText: "OK",
                      });
                    });
                }
              });
            });
        </script>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

</body>

</html>