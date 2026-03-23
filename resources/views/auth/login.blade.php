<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>D.S.F.C Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(160deg, #f8fafc 0%, #eef2f7 100%);
            padding: 20px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .page-shell {
            min-height: calc(100vh - 40px);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-shell {
            width: 100%;
            max-width: 430px;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 28px 65px rgba(15, 23, 42, 0.12);
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.06);
        }
        .login-panel {
            padding: 38px 30px 32px;
        }
        .logo-wrap {
            display: flex;
            justify-content: center;
            margin: 0 auto 14px;
        }
        .logo-frame {
            width: 148px;
            height: 148px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .logo-image {
            width: 128px;
            height: 128px;
            object-fit: contain;
            display: block;
        }
        .logo-mark {
            width: 118px;
            height: 118px;
            border-radius: 999px;
            background: linear-gradient(145deg, #0f4c81, #0f766e);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        .panel-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            text-align: center;
        }
        .panel-copy {
            color: #6b7280;
            margin-bottom: 24px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .input-group-text {
            background: #fff;
        }
        .btn-login {
            background: linear-gradient(135deg, #0f766e, #0f4c81);
            border: none;
            font-weight: 600;
            padding: 12px 18px;
            border-radius: 10px;
        }
        .admin-note {
            margin-top: 18px;
            font-size: 0.92rem;
            color: #6b7280;
            text-align: center;
        }
        .club-name {
            font-size: 0.95rem;
            color: #4b5563;
            text-align: center;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .club-branch {
            text-align: center;
            color: #9ca3af;
            font-size: 0.86rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        @media (max-width: 575.98px) {
            .login-panel {
                padding: 30px 20px 24px;
            }
            .logo-frame {
                width: 132px;
                height: 132px;
            }
            .logo-image {
                width: 114px;
                height: 114px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid page-shell">
        <div class="row justify-content-center w-100">
            <div class="col-12 d-flex justify-content-center">
                <div class="login-shell">
                    <div class="login-panel">
                        <div class="logo-wrap">
                            <div class="logo-frame">
                                <img
                                    src="{{ asset('images/dsfc-logo.png') }}"
                                    alt="D.S.F.C Logo"
                                    class="logo-image"
                                    onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='flex';"
                                >
                                <div id="logo-fallback" class="logo-mark" style="display:none;">D.S.F.C</div>
                            </div>
                        </div>
                        <p class="club-name">Divine Source Friends Club of Nigeria</p>
                        <p class="club-branch">Admin Portal</p>
                        <h2 class="panel-title">Admin Login</h2>
                        <p class="panel-copy">Sign in with your admin email or username to continue to the dashboard.</p>

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Sign-in failed.</strong>
                                <ul class="mb-0 mt-2 pl-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}">
                            @csrf

                            <div class="form-group">
                                <label for="login">Email or Username</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                    </div>
                                    <input
                                        type="text"
                                        id="login"
                                        name="login"
                                        class="form-control"
                                        value="{{ old('login') }}"
                                        placeholder="Enter email or username"
                                        required
                                        autofocus
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Enter password"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-group clearfix">
                                <div class="icheck-primary d-inline">
                                    <input type="checkbox" id="remember" name="remember" value="1">
                                    <label for="remember">Remember me</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-login">
                                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                            </button>
                        </form>

                        <p class="admin-note">
                            <a href="{{ route('password.request') }}">Forgot password?</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
