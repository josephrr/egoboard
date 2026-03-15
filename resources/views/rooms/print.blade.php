<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $room->name }} | Exportacion PDF</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 32px;
                color: #0f172a;
                background: #fff;
            }

            .header {
                border-bottom: 2px solid #e2e8f0;
                padding-bottom: 20px;
                margin-bottom: 24px;
            }

            .title {
                font-size: 32px;
                font-weight: 700;
                margin: 0 0 10px;
            }

            .meta {
                color: #475569;
                font-size: 14px;
                margin: 4px 0;
            }

            .actions {
                margin-bottom: 24px;
            }

            .button {
                display: inline-block;
                padding: 10px 16px;
                border-radius: 10px;
                background: #0f172a;
                color: #fff;
                text-decoration: none;
                border: 0;
                cursor: pointer;
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .card {
                border: 1px solid #cbd5e1;
                border-radius: 18px;
                padding: 18px;
                break-inside: avoid;
            }

            .badge {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 999px;
                background: #f1f5f9;
                color: #334155;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            .message {
                font-size: 16px;
                line-height: 1.6;
                margin: 14px 0;
            }

            .footer {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                color: #64748b;
                font-size: 13px;
                border-top: 1px solid #e2e8f0;
                padding-top: 12px;
                margin-top: 14px;
            }

            @media print {
                .actions {
                    display: none;
                }

                body {
                    padding: 0;
                }
            }

            @media (max-width: 800px) {
                .grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="actions">
            <button class="button" onclick="window.print()">Guardar o imprimir como PDF</button>
        </div>

        <header class="header">
            <h1 class="title">{{ $room->name }}</h1>
            @if ($room->description)
                <p class="meta">{{ $room->description }}</p>
            @endif
            <p class="meta">Notas exportadas: {{ $notes->count() }}</p>
            <p class="meta">Generado: {{ now()->format('d/m/Y H:i') }}</p>
        </header>

        <section class="grid">
            @foreach ($notes as $note)
                <article class="card">
                    <span class="badge">{{ \App\Models\Note::CATEGORIES[$note->category] ?? $note->category }}</span>
                    <p class="message">{{ $note->message }}</p>
                    <div class="footer">
                        <span>{{ $note->displayName() }}</span>
                        <span>{{ $note->votes_count }} votos</span>
                        <span>{{ $note->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </article>
            @endforeach
        </section>
    </body>
</html>
