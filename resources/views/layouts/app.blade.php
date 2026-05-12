<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Profissional</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #1f2937;
        }

        header {
            background: #1e3a8a;
            color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        header p {
            margin: 6px 0 0;
            font-size: 14px;
            color: #dbeafe;
        }

        nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 30px;
        }

        nav a {
            text-decoration: none;
            color: #1e3a8a;
            font-weight: bold;
            margin-right: 18px;
        }

        nav a:hover {
            color: #2563eb;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            padding: 24px;
        }

        h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #111827;
        }

        .alert-success {
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .actions form {
            display: inline-block;
            margin: 0 4px 0 0;
        }

        .actions a {
            margin-right: 6px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: white;
        }

        .form-group textarea {
            resize: vertical;
        }

        .form-group input[type="color"] {
            width: 70px;
            height: 45px;
            padding: 4px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: white;
            cursor: pointer;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            overflow: hidden;
            border-radius: 10px;
        }

        table th,
        table td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 14px;
        }

        table tr:hover {
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }

        .badge-yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .checkbox-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: normal;
        }

        .section-title {
            margin-top: 30px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .calendar-grid {
            margin-top: 20px;
        }

        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        .calendar-weekday {
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
        }

        .calendar-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .calendar-day {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            min-height: 140px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .calendar-day-muted {
            background: #f9fafb;
            opacity: 0.65;
        }

        .calendar-date {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 4px;
        }

        .calendar-event {
            color: white;
            padding: 8px;
            border-radius: 8px;
            font-size: 12px;
            line-height: 1.4;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            nav {
                padding: 12px 20px;
            }

            header {
                padding: 20px;
            }

            .container {
                padding: 0 14px;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            .calendar-header,
            .calendar-body {
                grid-template-columns: repeat(1, 1fr);
            }
        }

        /*DASHBOARD*/
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px;
        }

        .stat-card h3 {
            margin: 0 0 8px;
            font-size: 14px;
            color: #475569;
        }

        .stat-card p {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .section-subtitle {
            margin-top: 10px;
            margin-bottom: 14px;
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }
    </style>
</head>
<body>
    <header>
        <h1>Agenda Profissional</h1>
        <p>Controle de eventos, apresentações e entregas</p>
    </header>

    <nav>
        <a href="{{ url('/') }}">Início</a>
        <a href="{{ route('events.index') }}">Agendamentos</a>
        <a href="{{ route('work-types.index') }}">Tipos de Trabalho</a>
    </nav>

    <div class="container">
        <div class="card">
            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>