<?php
$pageTitle = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Heritage</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-tree text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Create Account</h2>
                        <p class="text-muted">Join Heritage Family Tree</p>
                    </div>
                    
                    <div id="errorAlert" class="alert alert-danger d-none" role="alert"></div>
                    <div id="successAlert" class="alert alert-success d-none" role="alert"></div>
                    
                    <form id="registerForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required minlength="3">
                            </div>
                            <small class="text-muted">At least 3 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            </div>
                            <small class="text-muted">At least 8 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus"></i> Register
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const registerForm = document.getElementById('registerForm');
        const errorAlert = document.getElementById('errorAlert');
        const successAlert = document.getElementById('successAlert');
        
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Hide alerts
            errorAlert.classList.add('d-none');
            successAlert.classList.add('d-none');
            
            // Get form data
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validate password match
            if (password !== confirmPassword) {
                errorAlert.textContent = 'Passwords do not match';
                errorAlert.classList.remove('d-none');
                return;
            }
            
            const formData = {
                username: username,
                email: email,
                password: password
            };
            
            try {
                const response = await fetch('api/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    // Registration successful
                    successAlert.textContent = 'Registration successful! Redirecting to login...';
                    successAlert.classList.remove('d-none');
                    
                    // Redirect to login after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    // Show error
                    if (result.errors) {
                        errorAlert.innerHTML = result.errors.join('<br>');
                    } else {
                        errorAlert.textContent = result.error || 'Registration failed';
                    }
                    errorAlert.classList.remove('d-none');
                }
            } catch (error) {
                errorAlert.textContent = 'An error occurred. Please try again.';
                errorAlert.classList.remove('d-none');
            }
        });
    </script>
</body>
</html>