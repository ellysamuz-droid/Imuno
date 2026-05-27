<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Reminder Imunisasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #E85D6F;
            --secondary: #78B7B7;
            --accent: #FFD89B;
            --dark: #2C3E50;
            --light: #F8F9FA;
            --error: #E74C3C;
            --success: #A8D5BA;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FFE5E9 0%, #E8F5F7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Background decorations */
        .decoration {
            position: absolute;
            border-radius: 50%;
            opacity: 0.05;
            z-index: 0;
        }

        .deco-1 { width: 500px; height: 500px; background: var(--primary); top: -200px; left: -200px; }
        .deco-2 { width: 400px; height: 400px; background: var(--secondary); bottom: -150px; right: -150px; }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            height: 100vh;
            position: relative;
            z-index: 10;
            gap: 0;
            align-items: center;
        }

        .login-left {
            background: linear-gradient(135deg, var(--primary) 0%, #D94560 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 60px;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            animation: float 6s ease-in-out infinite;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            bottom: 50px;
            left: 50px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-30px); }
        }

        .left-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 400px;
        }

        .left-icon {
            font-size: 100px;
            margin-bottom: 30px;
            display: inline-block;
            animation: bounce 2s infinite;
        }

        .left-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            margin-bottom: 20px;
            font-weight: 700;
            line-height: 1.2;
        }

        .left-content p {
            font-size: 16px;
            line-height: 1.8;
            opacity: 0.95;
            margin-bottom: 50px;
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .feature-item::before {
            content: '✓';
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }

        .feature-item span {
            font-size: 14px;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .login-right {
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: white;
        }

        .login-form-container {
            width: 100%;
            max-width: 420px;
        }

        .form-header {
            margin-bottom: 50px;
            text-align: center;
        }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .form-header p {
            color: #999;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(232, 93, 111, 0.1);
        }

        .form-group input::placeholder {
            color: #ccc;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #D94560;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), #D94560);
            color: white;
            border: none;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(232, 93, 111, 0.3);
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(232, 93, 111, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .signup-link {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .signup-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s;
        }

        .signup-link a:hover {
            color: #D94560;
        }

        .error-message {
            display: none;
            background: #FEE;
            color: var(--error);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid var(--error);
        }

        .error-message.show {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                height: auto;
            }

            .login-left {
                min-height: 40vh;
                padding: 40px 30px;
            }

            .login-right {
                min-height: 60vh;
                padding: 40px 30px;
            }

            .left-content {
                max-width: 100%;
            }

            .left-icon {
                font-size: 80px;
            }

            .left-content h2 {
                font-size: 32px;
            }

            .left-content p {
                font-size: 14px;
            }

            .login-form-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="decoration deco-1"></div>
    <div class="decoration deco-2"></div>

    <div class="container">
        <div class="login-left">
            <div class="left-content">
                <div class="left-icon">💉</div>
                <h2>Selamat Datang Kembali!</h2>
                <p>Pantau jadwal imunisasi anak Anda dengan mudah dan aman</p>

                <div class="features-list">
                    <div class="feature-item">
                        <span>Pengingat jadwal otomatis</span>
                    </div>
                    <div class="feature-item">
                        <span>Riwayat imunisasi lengkap</span>
                    </div>
                    <div class="feature-item">
                        <span>Konsultasi dengan dokter</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-right">
            <form class="login-form-container" id="loginForm">
                <div class="form-header">
                    <h1>Masuk</h1>
                    <p>Gunakan akun Anda untuk melanjutkan</p>
                </div>

                <div class="error-message" id="errorMessage"></div>
                <div class="success-message" id="successMessage"></div>

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email" placeholder="nama@email.com" required>
                    <div class="error-text" id="emailError"></div>
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    <div class="error-text" id="passwordError"></div>
                </div>

                <div class="form-footer">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>
                    <a href="forgot-password.html" class="forgot-password">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">Masuk</button>

                <div class="signup-link">
                    Belum punya akun? <a href="register.html">Daftar sekarang</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const form = document.querySelector('.login-form-container');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Reset messages
            errorMessage.classList.remove('show');
            successMessage.classList.remove('show');
            clearErrors();

            // Get form data
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Validasi client-side
            let isValid = true;

            if (!email) {
                showError('email', 'Email harus diisi');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email', 'Format email tidak valid');
                isValid = false;
            }

            if (!password) {
                showError('password', 'Password harus diisi');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sedang memproses...';

            try {
                // Create FormData
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);

                // Send to server
                const response = await fetch('proseslogin.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    showSuccess('Login berhasil! Anda akan diarahkan ke dashboard...');
                    
                    // Redirect ke dashboard setelah 1.5 detik
                    setTimeout(() => {
                        window.location.href = 'dashboard.html';
                    }, 1500);
                } else {
                    // Show error message
                    if (data.errors) {
                        // Multiple errors
                        for (let field in data.errors) {
                            showError(field, data.errors[field]);
                        }
                    } else {
                        // Single error message
                        showError('general', data.message);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showError('general', 'Terjadi kesalahan saat login. Silakan coba lagi.');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = 'Masuk';
            }
        });

        function showError(fieldId, message) {
            if (fieldId === 'general') {
                errorMessage.textContent = message;
                errorMessage.classList.add('show');
                errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                const errorElement = document.getElementById(fieldId + 'Error');
                const inputElement = document.getElementById(fieldId);
                if (errorElement && inputElement) {
                    errorElement.textContent = message;
                    errorElement.classList.add('show');
                    inputElement.classList.add('input-error');
                }
            }
        }

        function showSuccess(message) {
            successMessage.textContent = message;
            successMessage.classList.add('show');
            successMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function clearErrors() {
            document.querySelectorAll('.error-text').forEach(el => {
                el.classList.remove('show');
            });
            document.querySelectorAll('input').forEach(el => {
                el.classList.remove('input-error');
            });
        }

        // Real-time validation
        document.getElementById('email').addEventListener('change', function() {
            if (this.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                this.style.borderColor = 'var(--error)';
            } else {
                this.style.borderColor = '#e0e0e0';
            }
        });
    </script>
</body>
</html>
