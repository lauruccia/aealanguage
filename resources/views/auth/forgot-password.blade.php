<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recupero password | AEA Lingue</title>

    <style>
        :root{
            --bg: #f4f6fb;
            --card: #ffffff;
            --border: rgba(15, 23, 42, .10);
            --text: #0f172a;
            --muted: rgba(15, 23, 42, .65);
            --primary: #f59e0b; /* richiamo arancio Filament (puoi cambiare) */
            --primary-dark: #d97706;
            --danger: #dc2626;
            --success-bg: rgba(34, 197, 94, .10);
            --success-border: rgba(34, 197, 94, .25);
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
            max-width: 440px;
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

        .alert{
            border-radius: 12px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            background: #f8fafc;
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--muted);
        }
        .alert.success{
            border-color: var(--success-border);
            background: var(--success-bg);
            color: rgba(15, 23, 42, .85);
        }

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

        .small-note{
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
                    <h1>Recupero password</h1>
                    <p>Inserisci la tua email: ti invieremo un link per impostare una nuova password.</p>
                </div>
            </div>

            <div class="divider"></div>

            @if (session('status'))
                <div class="alert success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="nome@dominio.it"
                >

                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror

                <button class="btn" type="submit">Invia link di recupero</button>
            </form>

            <div class="footer">
                <a href="{{ route('filament.admin.auth.login') }}">← Torna al login</a>
                <span>AEA Lingue</span>
            </div>

            <div class="small-note">
                Se non trovi l’email, controlla anche Spam/Promozioni.
            </div>
        </div>
    </div>
</body>
</html>
