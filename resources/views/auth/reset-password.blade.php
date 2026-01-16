<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reimposta password | AEA Lingue</title>

    <style>
        :root{
            --bg: #f4f6fb;
            --card: #ffffff;
            --border: rgba(15, 23, 42, .10);
            --text: #0f172a;
            --muted: rgba(15, 23, 42, .65);
            --primary: #f59e0b;
            --primary-dark: #d97706;
            --danger: #dc2626;
        }

        *{ box-sizing:border-box; }
        body{
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
            padding: 24px;
        }

        .wrap{
            width: 100%;
            max-width: 460px;
        }

        .card{
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 12px 30px rgba(2, 6, 23, .08);
        }

        .brand{
            display:flex;
            align-items:center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .logo{
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(245,158,11,.15);
            color: var(--primary-dark);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight: 800;
            letter-spacing: .4px;
            user-select:none;
        }

        h1{
            margin: 0;
            font-size: 18px;
            line-height: 1.2;
        }

        p{
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .divider{
            height:1px;
            background: var(--border);
            margin: 16px 0;
        }

        label{
            display:block;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        input{
            width: 100%;
            padding: 12px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            outline: none;
        }
        input:focus{
            border-color: rgba(245,158,11,.55);
            box-shadow: 0 0 0 4px rgba(245,158,11,.15);
        }

        .btn{
            width: 100%;
            margin-top: 12px;
            padding: 12px 14px;
            border: none;
            border-radius: 12px;
            background: var(--primary);
            color: #111827;
            font-weight: 700;
            cursor: pointer;
        }
        .btn:hover{ filter: brightness(0.98); }
        .btn:active{ transform: translateY(1px); }

        .error{
            margin-top: 8px;
            color: var(--danger);
            font-size: 13px;
        }

        .footer{
            margin-top: 14px;
            display:flex;
            justify-content:space-between;
            gap: 10px;
            font-size: 13px;
            color: var(--muted);
        }

        a{ color: inherit; text-decoration: none; }
        a:hover{ text-decoration: underline; }

        .help{
            margin-top: 10px;
            font-size: 12.5px;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="brand">
                <div class="logo">AEA</div>
                <div>
                    <h1>Imposta una nuova password</h1>
                    <p>Scegli una password sicura e confermala per completare il reset.</p>
                </div>
            </div>

            <div class="divider"></div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    required
                    autocomplete="email"
                    readonly
                >
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror

                <div style="height:10px"></div>

                <label for="password">Nuova password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="Minimo 10 caratteri"
                >
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror

                <div style="height:10px"></div>

                <label for="password_confirmation">Conferma password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Ripeti la password"
                >

                <button class="btn" type="submit">Salva nuova password</button>

                <div class="help">
                    Se non hai richiesto tu il reset, puoi ignorare l’email.
                </div>
            </form>

            <div class="footer">
                <a href="{{ route('filament.admin.auth.login') }}">← Torna al login</a>
                <span>AEA Lingue</span>
            </div>
        </div>
    </div>
</body>
</html>
