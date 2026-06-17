<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Sensor — {{ $device->label_rak ?? $device->device_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1e293b; }
        .header { background: #0f172a; color: #f8fafc; padding: 20px 30px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #94a3b8; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-table td { padding: 6px 12px; font-size: 11px; }
        .meta-table .label { font-weight: bold; color: #475569; width: 160px; }
        .meta-table .value { color: #1e293b; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table thead th {
            background: #f1f5f9; color: #334155; font-size: 9px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.5px;
            padding: 8px 10px; border-bottom: 2px solid #e2e8f0; text-align: left;
        }
        .data-table tbody td {
            padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #475569;
        }
        .data-table tbody tr:nth-child(even) { background: #f8fafc; }
        .highlight-red { color: #ef4444; font-weight: bold; }
        .highlight-amber { color: #f59e0b; font-weight: bold; }
        .footer { text-align: center; font-size: 9px; color: #94a3b8; margin-top: 20px; padding-top: 10px; border-top: 1px solid #e2e8f0; }
        .summary-box { background: #f1f5f9; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; }
        .summary-box h3 { font-size: 12px; color: #334155; margin-bottom: 6px; }
        .summary-grid { display: table; width: 100%; }
        .summary-item { display: table-cell; text-align: center; padding: 8px; }
        .summary-item .num { font-size: 16px; font-weight: bold; color: #0f172a; }
        .summary-item .lbl { font-size: 9px; color: #64748b; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>📊 Laporan Sensor — Rizhomatix</h1>
        <p>Digenerate pada {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    {{-- Device Info --}}
    <table class="meta-table">
        <tr>
            <td class="label">Device ID</td>
            <td class="value">{{ $device->device_id }}</td>
            <td class="label">Label Rak</td>
            <td class="value">{{ $device->label_rak ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pemilik</td>
            <td class="value">{{ $device->user->name ?? '-' }}</td>
            <td class="label">Periode Laporan</td>
            <td class="value">{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">Total Data</td>
            <td class="value">{{ $logs->count() }} record</td>
            <td class="label">Mode Operasi</td>
            <td class="value">{{ $device->operation_mode }}</td>
        </tr>
    </table>

    {{-- Summary Statistics --}}
    @if($logs->count() > 0)
    <div class="summary-box">
        <h3>Ringkasan Statistik</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: center; padding: 8px;">
                    <div class="num">{{ number_format($logs->avg('internal_temp'), 1) }}°C</div>
                    <div class="lbl">Rata-rata Suhu Internal</div>
                </td>
                <td style="text-align: center; padding: 8px;">
                    <div class="num">{{ number_format($logs->max('internal_temp'), 1) }}°C</div>
                    <div class="lbl">Suhu Maks</div>
                </td>
                <td style="text-align: center; padding: 8px;">
                    <div class="num">{{ number_format($logs->avg('amonia_level'), 1) }} ppm</div>
                    <div class="lbl">Rata-rata Amonia</div>
                </td>
                <td style="text-align: center; padding: 8px;">
                    <div class="num">{{ number_format($logs->avg('humidity'), 1) }}%</div>
                    <div class="lbl">Rata-rata Kelembapan</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Waktu</th>
                <th>Suhu Internal (°C)</th>
                <th>Amonia (ppm)</th>
                <th>Suhu Ruang (°C)</th>
                <th>Kelembapan (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $i => $entry)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $entry->created_at->format('d/m/Y H:i:s') }}</td>
                <td class="{{ $entry->internal_temp > ($device->temp_threshold ?? 35) ? 'highlight-red' : '' }}">
                    {{ number_format($entry->internal_temp, 1) }}
                </td>
                <td class="{{ $entry->amonia_level > ($device->amonia_threshold ?? 25) ? 'highlight-amber' : '' }}">
                    {{ number_format($entry->amonia_level, 1) }}
                </td>
                <td>{{ number_format($entry->room_temp, 1) }}</td>
                <td>{{ number_format($entry->humidity, 1) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;">
                    Tidak ada data sensor dalam rentang tanggal ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Rizhomatix System &mdash; Laporan otomatis &mdash; {{ now()->format('Y') }}
    </div>
</body>
</html>
