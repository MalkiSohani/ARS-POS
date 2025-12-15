<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <link rel="stylesheet" href="/css/master.css">
    <link rel="icon" href="{{ asset('images/' . ($app_settings->favicon ?? 'favicon.ico')) }}">
    <title>{{ $app_settings->app_name ?? 'TALEXA POS | Ultimate Inventory With POS' }}</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      
      body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 
                        'Segoe UI', Roboto, 'Helvetica Neue', Arial, 
                        sans-serif;
            height: 100vh;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

      .login-container {
        display: flex;
        height: 100vh;
        width: 100%;
        margin: 0; /* Add this */
        padding: 0; /* Add this */
      }
      
      /* Left Side - Image/Branding */
      .login-left {
        flex: 0 0 50%;
        /* background: linear-gradient(135deg, #0a2540 0%, #1a4d7a 100%); */
        display: flex;
        padding: 0; /* Add this */
        margin: 0; /* Add this */
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
      }
      
      .login-left::before {
        content: '';
        position: absolute;
        width: 150%;
        height: 150%;
        background: radial-gradient(circle, rgba(99, 179, 237, 0.1) 0%, transparent 70%);
        animation: pulse 8s ease-in-out infinite;
      }
      
      @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
      }
      
      .brand-content {
        text-align: center;
        z-index: 1;
        padding: 0; /* Change from padding: 40px */
        margin: 0; /* Add this */
        width: 100%; /* Add this */
        height: 100%; /* Add this */
        }
      .brand-logo {
        margin-bottom: 30px;
      }
      
      .brand-logo img {
        max-width: 200px;
        height: auto;
        filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
      }
      
      .brand-image {
        width: 100%;
        height: 100vh;
        margin: 0;
        padding: 0;
        object-fit: cover; /* Add this */
        display: block; /* Add this */
        }
      
      @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
      }
      
      .brand-tagline {
        color: rgba(255, 255, 255, 0.9);
        font-size: 18px;
        margin-top: 30px;
        font-weight: 300;
        display: none;
        letter-spacing: 0.5px;
      }
      
      /* Right Side - Login Form */
      .login-right {
        flex: 1;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
      }
      
      .login-form-wrapper {
        width: 100%;
        max-width: 440px;
      }
      
      .login-header {
        margin-bottom: 40px;
        text-align: center;
      }
      
      .login-header h1 {
        font-size: 32px;
        font-weight: 700;
        color: #0a2540;
        margin-bottom: 8px;
      }
      
      .login-header p {
        font-size: 16px;
        color: #64748b;
        font-weight: 400;
      }
      
      /* Alert Messages */
      .auth-alert {
        padding: 14px 16px;
        border-radius: 12px;
        font-size: 14px;
        margin-bottom: 24px;
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
      
      .auth-alert.success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
      }
      
      .auth-alert.error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
      }
      
      .auth-alert ul {
        margin: 0 0 0 20px;
      }
      
      /* Form Styles */
      .form-group {
        margin-bottom: 24px;
      }
      
      .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
        letter-spacing: -0.1px; 
      }
      
      .input-wrapper {
        position: relative;
      }
      
      .auth-input {
        width: 100%;
        padding: 14px 16px;
        font-size: 15px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        outline: none;
        transition: all 0.3s ease;
        background: #ffffff;
      }
      
      .auth-input:focus {
        border-color: #0a2540;
        /* box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); */
      }
      
      .auth-input:hover {
        border-color: #cbd5e1;
      }
      
      /* Form Row */
      .form-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
      }
      
      .remember {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
      }
      
      .remember input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #0a2540;
      }
      
      .remember span {
        font-size: 14px;
        color: #64748b;
        user-select: none;
      }
      
      .auth-link {
        font-size: 14px;
        color: #0a2540;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
      }
      
      .auth-link:hover {
        color: #0a2540;
        text-decoration: underline;
      }
      
      /* Button */
      .auth-btn {
        width: 100%;
        padding: 14px 24px;
        font-size: 16px;
        font-weight: 600;
        color: #ffffff;
        background: linear-gradient(135deg, #0a2540 100%);
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        /* box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); */
      }
      
      .auth-btn:hover {
        transform: translateY(-2px);
        /* box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4); */
      }
      
      .auth-btn:active {
        transform: translateY(0);
      }
      
      .auth-btn[disabled] {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
      }
      
      .btn-loading {
        display: none;
        align-items: center;
        justify-content: center;
      }
      
      .spinner {
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
        margin-right: 8px;
      }
      
      @keyframes spin {
        to { transform: rotate(360deg); }
      }
      
      /* Responsive Design */
      @media (max-width: 768px) { /* Change from 1024px to 768px */
        .login-left {
            display: none;
        }
        
        .login-right {
            flex: 1;
        }
        }
      
      @media (max-width: 640px) {
        .login-right {
          padding: 24px;
        }
        
        .login-header h1 {
          font-size: 28px;
        }
        
        .login-form-wrapper {
          max-width: 100%;
        }
      }
    </style>
  </head>

  <body>
    <noscript>
      <strong>
        We're sorry but Talexa doesn't work properly without JavaScript
        enabled. Please enable it to continue.
      </strong>
    </noscript>
    
    <div class="login-container">
      <!-- Left Side - Branding/Image -->
      <div class="login-left">
        <div class="brand-content">
          <img src="{{ asset('images/login.jpg') }}" alt="Talexa Robot" class="brand-image" />
          <p class="brand-tagline">Your all-in-one Human Resource Management Solution</p>
        </div>
      </div>
      
      <!-- Right Side - Login Form -->
      <div class="login-right">
        <div class="login-form-wrapper">
          <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Sign in to <span style="color: #0a2540; font-weight: 600;">TalexaPOS</span></p>
            <p style="margin-top: 8px; font-size: 16px;">Your all-in-one Point Of Sale Solution</p>
          </div>
          
          @if (session('status'))
          <div class="auth-alert success">
            {{ session('status') }}
          </div>
          @endif
          
          @if ($errors->any())
          <div class="auth-alert error">
            <ul>
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
          
          <form id="login_form" method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            
            <div class="form-group">
              <label for="email">Email Address</label>
              <div class="input-wrapper">
                <input 
                  id="email" 
                  type="email" 
                  name="email" 
                  class="auth-input" 
                  placeholder="email@company.com"
                  value="{{ old('email') }}" 
                  required 
                  autocomplete="email" 
                  autofocus 
                />
              </div>
            </div>
            
            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-wrapper">
                <input 
                  id="password" 
                  type="password" 
                  name="password" 
                  class="auth-input" 
                  placeholder="Enter your password"
                  required 
                  autocomplete="current-password" 
                />
              </div>
            </div>
            
            <div class="form-row">
              <label class="remember" for="remember">
                <input type="checkbox" name="remember" id="remember" />
                <span>Remember Me</span>
              </label>
              <a class="auth-link" href="{{ route('password.request') }}">Forgot Password?</a>
            </div>
            
            <button type="submit" class="auth-btn" id="login_submit_btn">
              <span class="btn-text">Sign In</span>
              <span class="btn-loading">
                <span class="spinner"></span>
                Signing in...
              </span>
            </button>
          </form>
        </div>
      </div>
    </div>
    
    <script>
      (function(){
        var form = document.getElementById('login_form');
        if(!form) return;
        
        var btn = document.getElementById('login_submit_btn');
        var text = btn ? btn.querySelector('.btn-text') : null;
        var loading = btn ? btn.querySelector('.btn-loading') : null;
        var submitted = false;
        
        form.addEventListener('submit', function(){
          if(submitted) return;
          submitted = true;
          
          if(btn){
            btn.disabled = true;
            if(text && loading){
              text.style.display = 'none';
              loading.style.display = 'inline-flex';
            }
          }
        });
      })();
    </script>
  </body>
</html>