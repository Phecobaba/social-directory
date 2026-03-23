<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(160deg, #f8fafc 0%, #eef2f7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-shell {
            width: 100%;
            max-width: 460px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            background: #ffffff;
        }
        .auth-panel {
            padding: 42px 34px;
        }
        .logo-mark {
            width: 76px;
            height: 76px;
            margin: 0 auto 18px;
            border-radius: 20px;
            background: linear-gradient(145deg, #0f4c81, #0f766e);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        .panel-title, .panel-copy, .club-name, .helper-links {
            text-align: center;
        }
        .panel-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }
        .panel-copy, .club-name, .helper-links {
            color: #6b7280;
        }
        .panel-copy {
            margin-bottom: 26px;
        }
        .club-name {
            font-size: 0.95rem;
            margin-bottom: 6px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #0f766e, #0f4c81);
            border: none;
            font-weight: 600;
            padding: 12px 18px;
        }
        .helper-links {
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-panel">
            <div class="logo-mark">D.S.F.C</div>
            <p class="club-name">Divine Source Friends Club of Nigeria</p>
            <h2 class="panel-title">Reset Password</h2>
            <p class="panel-copy">Set a new password for your admin account.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-submit">
                    <i class="fas fa-key mr-2"></i>Reset Password
                </button>
            </form>

            <p class="helper-links">
                <a href="{{ route('login') }}">Back to login</a>
            </p>
        </div>
    </div>
</body>
</html>
