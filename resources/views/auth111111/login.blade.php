
<!DOCTYPE html>

<html lang="ja">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ログイン</title>

    <style>

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

        }

        body {

            font-family: 'Helvetica', 'Arial', sans-serif;

            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

        }

        .login-container {

            background: white;

            border-radius: 20px;

            box-shadow: 0 20px 60px rgba(0,0,0,0.3);

            width: 100%;

            max-width: 400px;

            padding: 40px;

        }

        .login-header {

            text-align: center;

            margin-bottom: 40px;

        }

        .login-header h1 {

            color: #333;

            font-size: 28px;

            font-weight: 600;

        }

        .login-header p {

            color: #666;

            margin-top: 10px;

            font-size: 14px;

        }

        .form-group {

            margin-bottom: 20px;

        }

        label {

            display: block;

            margin-bottom: 8px;

            color: #555;

            font-size: 14px;

            font-weight: 500;

        }

        input[type="email"],

        input[type="password"] {

            width: 100%;

            padding: 12px 16px;

            border: 2px solid #e1e1e1;

            border-radius: 10px;

            font-size: 15px;

            transition: all 0.3s;

        }

        input[type="email"]:focus,

        input[type="password"]:focus {

            border-color: #667eea;

            outline: none;

            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);

        }

        .remember-group {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 20px;

        }

        .remember-label {

            display: flex;

            align-items: center;

            color: #666;

            font-size: 14px;

            cursor: pointer;

        }

        .remember-label input {

            margin-right: 8px;

            width: 16px;

            height: 16px;

            cursor: pointer;

        }

        .forgot-link {

            color: #667eea;

            text-decoration: none;

            font-size: 14px;

        }

        .forgot-link:hover {

            text-decoration: underline;

        }

        button {

            width: 100%;

            padding: 14px;

            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

            border: none;

            border-radius: 10px;

            color: white;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;

            transition: transform 0.2s;

        }

        button:hover {

            transform: translateY(-2px);

        }

        button:active {

            transform: translateY(0);

        }

        .error-message {

            background: #fee;

            color: #c33;

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;

            text-align: center;

            border-left: 4px solid #c33;

        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="login-header">

            <h1>ログ

            <p>アカウント情報をご入力ください</p>

        </div>



        @if($errors->any())

            <div class="error-message">

                {{ $errors->first('email') }}

            </div>

        @endif



        <form method="POST" action="{{ route('login') }}">

            @csrf

            <div class="form-group">

                <label for="email">メールアドレス</label>

                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

            </div>



            <div class="form-group">

                <label for="password">パスワード</label>

                <input type="password" id="password" name="password" required>

            </div>



            <div class="remember-group">

                <label class="remember-label">

                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>

                    ログイン状態を保持する

                </label>

                <a href="{{ route('password.request') }}" class="forgot-link">

                    パスワードをお忘れですか？

                </a>

            </div>



            <button type="submit">

                ログイン

            </button>

        </form>

    </div>

</body>

</html>

