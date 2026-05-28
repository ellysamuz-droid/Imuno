<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Reminder Imunisasi</title>
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
            padding: 40px 20px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-card {
            background: white;
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header-icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: inline-block;
            animation: bounce 2s infinite;
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

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .progress-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            width: 33%;
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .required {
            color: var(--error);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(232, 93, 111, 0.1);
        }

        .form-group input::placeholder {
            color: #ccc;
        }

        .password-strength {
            display: none;
            margin-top: 8px;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .password-strength.weak {
            display: block;
            background: #FFEBEE;
            color: var(--error);
        }

        .password-strength.medium {
            display: block;
            background: #FFF3E0;
            color: #FF9800;
        }

        .password-strength.strong {
            display: block;
            background: #E8F5E9;
            color: var(--success);
        }

        .input-error {
            border-color: var(--error) !important;
        }

        .input-success {
            border-color: var(--success) !important;
        }

        .error-text {
            color: var(--error);
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .error-text.show {
            display: block;
        }

        .terms-agreement {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin: 25px 0;
            font-size: 13px;
            line-height: 1.6;
        }

        .terms-agreement label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 0;
            font-weight: 400;
            cursor: pointer;
        }

        .terms-agreement input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            margin-top: 2px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .terms-agreement a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .terms-agreement a:hover {
            text-decoration: underline;
        }

        .btn-register {
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
            margin-top: 15px;
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s;
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(232, 93, 111, 0.4);
        }

        .btn-register:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert.show {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        .alert.error {
            background: #FFEBEE;
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert.success {
            background: #E8F5E9;
            color: var(--success);
            border-left: 4px solid var(--success);
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

        @media (max-width: 480px) {
            .register-card {
                padding: 30px 20px;
            }

            .form-header h1 {
                font-size: 28px;
            }

            .form-header-icon {
                font-size: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="form-header">
                <div class="form-header-icon">💚</div>
                <h1>Daftar Akun</h1>
                <p>Bergabunglah dengan ribuan orang tua yang peduli</p>
            </div>

            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>

            <div class="alert" id="alertMessage"></div>

            <form id="registerForm" action="prosesregistrasi.php" method="POST" novalidate>
                <div class="form-group">
                    <label for="username">
                        Nama Pengguna <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Contoh: BuIbu Sinta"
                        required
                    >
                    <div class="error-text" id="usernameError"></div>
                </div>

                <div class="form-group">
                    <label for="email">
                        Alamat Email <span class="required">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="nama@email.com"
                        required
                    >
                    <div class="error-text" id="emailError"></div>
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir">
                        Tanggal Lahir <span class="required">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_lahir" 
                        name="tanggal_lahir" 
                        required
                    >
                    <div class="error-text" id="dateError"></div>
                </div>

                <div class="form-group">
                    <label for="password">
                        Kata Sandi <span class="required">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimal 8 karakter"
                        required
                    >
                    <div class="password-strength" id="strengthIndicator"></div>
                    <div class="error-text" id="passwordError"></div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">
                        Konfirmasi Kata Sandi <span class="required">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirm" 
                        name="password_confirm" 
                        placeholder="Ulangi kata sandi"
                        required
                    >
                    <div class="error-text" id="confirmError"></div>
                </div>

                <div class="terms-agreement">
                    <label>
                        <input type="checkbox" name="agree_terms" required>
                        <span>Saya setuju dengan <a href="#">Syarat dan Ketentuan</a> serta <a href="#">Kebijakan Privasi</a></span>
                    </label>
                </div>

                <button type="submit" class="btn-register" id="submitBtn">Daftar Sekarang</button>

                <div class="login-link">
                    Sudah punya akun? <a href="login.php">Masuk di sini</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirm');
        const strengthIndicator = document.getElementById('strengthIndicator');
        const alertMessage = document.getElementById('alertMessage');
        const submitBtn = document.getElementById('submitBtn');

        // Password strength checker
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updateStrengthIndicator(strength);
            validatePasswordMatch();
        });

        confirmInput.addEventListener('input', function() {
            validatePasswordMatch();
        });

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            if (strength < 2) return 'weak';
            if (strength < 4) return 'medium';
            return 'strong';
        }

        function updateStrengthIndicator(strength) {
            strengthIndicator.className = 'password-strength ' + strength;
            const messages = {
                'weak': '❌ Kata sandi terlalu lemah',
                'medium': '⚠️ Kata sandi sedang',
                'strong': '✓ Kata sandi kuat'
            };
            strengthIndicator.textContent = messages[strength];
        }

        function validatePasswordMatch() {
            const confirmError = document.getElementById('confirmError');
            if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                confirmInput.classList.add('input-error');
                confirmError.textContent = 'Kata sandi tidak cocok';
                confirmError.classList.add('show');
                return false;
            } else {
                confirmInput.classList.remove('input-error');
                confirmError.classList.remove('show');
                return true;
            }
        }

        // Form validation with AJAX
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear previous messages
            alertMessage.classList.remove('show');
            clearErrors();

            let isValid = true;

            // Validate username
            const username = document.getElementById('username').value.trim();
            if (!username) {
                showError('username', 'Nama pengguna harus diisi');
                isValid = false;
            } else if (username.length < 3) {
                showError('username', 'Nama pengguna minimal 3 karakter');
                isValid = false;
            }

            // Validate email
            const email = document.getElementById('email').value.trim();
            if (!email) {
                showError('email', 'Email harus diisi');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email', 'Format email tidak valid');
                isValid = false;
            }

            // Validate date
            const date = document.getElementById('tanggal_lahir').value;
            if (!date) {
                showError('date', 'Tanggal lahir harus diisi');
                isValid = false;
            }

            // Validate password
            const password = document.getElementById('password').value;
            if (!password) {
                showError('password', 'Kata sandi harus diisi');
                isValid = false;
            } else if (password.length < 8) {
                showError('password', 'Kata sandi minimal 8 karakter');
                isValid = false;
            }

            // Validate password match
            if (!validatePasswordMatch()) {
                isValid = false;
            }

            // Validate terms
            if (!document.querySelector('input[name="agree_terms"]').checked) {
                showAlert('Anda harus menyetujui syarat dan ketentuan', 'error');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sedang Mendaftar...';

            try {
                // Create FormData
                const formData = new FormData();
                formData.append('username', username);
                formData.append('email', email);
                formData.append('tanggal_lahir', date);
                formData.append('password', password);
                formData.append('password_confirm', confirmInput.value);

                // Send to server
                const response = await fetch('prosesregistrasi.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    showAlert('Pendaftaran berhasil! Anda akan diarahkan ke dashboard...', 'success');
                    
                    // Redirect ke dashboard setelah 1.5 detik
                    setTimeout(() => {
                        window.location.href = 'login.php';
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
                        showAlert(data.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan saat mendaftar. Silakan coba lagi.', 'error');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = 'Daftar Sekarang';
            }
        });

        function showError(fieldId, message) {
            const errorElement = document.getElementById(fieldId + 'Error');
            const inputElement = document.getElementById(fieldId);
            errorElement.textContent = message;
            errorElement.classList.add('show');
            inputElement.classList.add('input-error');
        }

        function clearErrors() {
            document.querySelectorAll('.error-text').forEach(el => {
                el.classList.remove('show');
            });
            document.querySelectorAll('input').forEach(el => {
                el.classList.remove('input-error');
            });
        }

        function showAlert(message, type) {
            alertMessage.textContent = message;
            alertMessage.className = 'alert show ' + type;
        }

        // Real-time validation
        document.getElementById('email').addEventListener('change', function() {
            if (this.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                showError('email', 'Format email tidak valid');
            } else {
                document.getElementById('emailError').classList.remove('show');
            }
        });
    </script>
</body>
</html>
