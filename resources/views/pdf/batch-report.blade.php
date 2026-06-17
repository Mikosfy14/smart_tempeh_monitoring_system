<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Batch Produksi — {{ $device->label_rak ?? $device->device_id }}</title>
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
            padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #475569;
            vertical-align: top;
        }
        .data-table tbody tr:nth-child(even) { background: #f8fafc; }
        .status-active { color: #10b981; font-weight: bold; }
        .status-semangit { color: #f59e0b; font-weight: bold; }
        .status-failed { color: #ef4444; font-weight: bold; }
        .status-completed { color: #64748b; font-weight: bold; }
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
        <h1>📑 Laporan Riwayat Produksi (Batch)</h1>
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
            <td class="label">Total Batch</td>
            <td class="value">{{ $batches->count() }} sesi</td>
            <td class="label">Mode Operasi</td>
            <td class="value">{{ $device->operation_mode }}</td>
        </tr>
    </table>

    {{-- Summary Statistics --}}
    @if($batches->count() > 0)
    <div class="summary-box">
        <h3>Ringkasan Statistik Batch</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: center; padding: 8px;">
                    <div class="num">{{ $batches->where('status', 'completed')->count() }}</div>
                    <div class="lbl">Selesai Sukses</div>
                </td>
                <td style="text-align: center; padding: 8px;">
                    <div class="num">{{ $batches->where('status', 'failed')->count() }}</div>
                    <div class="lbl">Gagal / Busuk</div>
                </td>
                <td style="text-align: center; padding: 8px;">
                    <div class="num">{{ $batches->where('status', 'semangit')->count() }}</div>
                    <div class="lbl">Semangit</div>
                </td>
                <td style="text-align: center; padding: 8px;">
                    <div class="num">{{ number_format($batches->avg('duration_hours') ?? 0, 1) }} Jam</div>
                    <div class="lbl">Rata-rata Durasi</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 15%;">Waktu Mulai</th>
                <th style="width: 15%;">Waktu Selesai</th>
                <th style="width: 10%;">Durasi (Jam)</th>
                <th style="width: 12%;">Status Akhir</th>
                <th style="width: 40%;">Catatan / Peringatan Sistem</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $batch)
            <tr>
                <td style="font-family: monospace; font-size: 11px;">#{{ str_pad($batch->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $batch->start_time->format('d/m/Y H:i') }}</td>
                <td>{{ $batch->end_time ? $batch->end_time->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ number_format($batch->duration_hours, 1) }}</td>
                <td class="
                    {{ $batch->status === 'active' ? 'status-active' : '' }}
                    {{ $batch->status === 'semangit' ? 'status-semangit' : '' }}
                    {{ $batch->status === 'failed' ? 'status-failed' : '' }}
                    {{ $batch->status === 'completed' ? 'status-completed' : '' }}
                ">
                    {{ strtoupper($batch->status) }}
                </td>
                <td style="white-space: pre-line; line-height: 1.4;">{{ $batch->prediction_notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;">
                    Tidak ada data sesi produksi (batch) dalam rentang tanggal ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Rizhomatix System &mdash; Laporan otomatis riwayat produksi &mdash; {{ now()->format('Y') }}
    </div>
</body>
</html>
