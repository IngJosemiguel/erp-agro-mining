<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $tipoTexto }} {{ $venta->serie }}-{{ str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #f4f4f4;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .page {
                box-shadow: none !important;
                margin: 0 !important;
            }

            @page {
                margin: 1.5cm;
                size: A4;
            }
        }

        /* BARRA DE HERRAMIENTAS */
        .toolbar {
            background: #1a1a2e;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .toolbar h3 {
            color: #fff;
            font-size: 14px;
            flex: 1;
        }

        .btn-print {
            background: #00dc82;
            color: #000;
            border: none;
            padding: 9px 22px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background: #00b86d;
        }

        .btn-close {
            background: #374151;
            color: #fff;
            border: none;
            padding: 9px 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        /* PÁGINA A4 */
        .page {
            max-width: 794px;
            margin: 24px auto;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
            padding: 40px 48px;
        }

        /* CABECERA */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .empresa-block {}

        .empresa-logo {
            background: #1a1a2e;
            color: #00dc82;
            font-size: 18px;
            font-weight: 900;
            padding: 10px 16px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .empresa-nombre {
            font-size: 15px;
            font-weight: 700;
            color: #111;
            margin-bottom: 3px;
        }

        .empresa-detalle {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }

        .doc-block {
            text-align: center;
            border: 2px solid #1a1a2e;
            border-radius: 10px;
            padding: 16px 24px;
            min-width: 200px;
        }

        .doc-tipo {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .doc-ruc {
            font-size: 11px;
            color: #374151;
            margin-bottom: 6px;
        }

        .doc-serie {
            font-size: 20px;
            font-weight: 900;
            color: #1a1a2e;
            font-family: 'Courier New', monospace;
            margin-bottom: 6px;
        }

        .doc-estado {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            background:
                @if($venta->estado === 'aceptado')
                        #d1fae5;
                    color: #065f46;
                @elseif($venta->estado === 'rechazado')
                #fee2e2;
                color: #991b1b;
            @else #fef3c7;
                color: #92400e;
            @endif
        }

        /* DATOS CLIENTE */
        .info-section {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-box {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
        }

        .info-box-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .info-box-value {
            font-size: 13px;
            font-weight: 700;
            color: #111;
            margin-bottom: 3px;
        }

        .info-box-sub {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* TABLA DETALLE */
        .table-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .detalle-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .detalle-table thead tr {
            background: #1a1a2e;
            color: #fff;
        }

        .detalle-table th {
            padding: 9px 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .detalle-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .detalle-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .detalle-table td {
            padding: 9px 12px;
            font-size: 12px;
            vertical-align: middle;
        }

        .prod-nombre {
            font-weight: 600;
            color: #111;
        }

        .prod-codigo {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .afect-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 700;
            background: #d1fae5;
            color: #065f46;
            margin-left: 4px;
        }

        /* TOTALES */
        .bottom-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 8px;
        }

        .leyenda {
            flex: 1;
            padding-right: 20px;
        }

        .leyenda-importe {
            font-size: 11px;
            color: #374151;
            font-style: italic;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #f9fafb;
            border-left: 3px solid #1a1a2e;
            border-radius: 0 6px 6px 0;
        }

        .totales-table {
            width: 260px;
            border-collapse: collapse;
        }

        .totales-table tr td {
            padding: 6px 10px;
            font-size: 12px;
        }

        .totales-table tr td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .totales-table .row-igv td {
            color: #6b7280;
        }

        .totales-table .row-total {
            background: #1a1a2e;
            color: #fff;
            border-radius: 6px;
        }

        .totales-table .row-total td {
            font-size: 14px;
            font-weight: 800;
            padding: 10px;
        }

        .totales-table .row-total td:last-child {
            color: #00dc82;
            font-size: 16px;
        }

        /* FOOTER */
        .footer {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer-legal {
            font-size: 9.5px;
            color: #9ca3af;
            line-height: 1.7;
            flex: 1;
            padding-right: 20px;
        }

        .footer-legal strong {
            color: #6b7280;
        }

        .hash-code {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            color: #d1d5db;
            word-break: break-all;
            margin-top: 4px;
        }

        .qr-section {
            text-align: center;
        }

        .qr-section img {
            width: 90px;
            height: 90px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 3px;
        }

        .qr-label {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- BARRA DE HERRAMIENTAS --}}
    <div class="toolbar">
        <h3>📄 {{ $tipoTexto }} · {{ $venta->serie }}-{{ str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT) }}</h3>
        <form action="{{ route('ventas.pdf', $venta->id) }}" method="get" target="_blank" style="display:inline;">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Imprimir / Guardar PDF
            </button>
        </form>
        <button class="btn-close" onclick="window.close()">✕ Cerrar</button>
    </div>

    {{-- PÁGINA --}}
    <div class="page">

        {{-- ENCABEZADO EMPRESA + DOCUMENTO --}}
        <div class="header">
            <div class="empresa-block">
                <div class="empresa-logo">RS</div>
                <div class="empresa-nombre">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
                <div class="empresa-detalle">
                    RUC: <strong>{{ $empresa->ruc }}</strong><br>
                    {{ $empresa->direccion_fiscal }}<br>
                    {{ $empresa->distrito }}, {{ $empresa->provincia }} - {{ $empresa->departamento }}<br>
                    {{ $empresa->email }}
                </div>
            </div>
            <div class="doc-block">
                <div class="doc-tipo">{{ $tipoTexto }}</div>
                <div class="doc-ruc">RUC: {{ $empresa->ruc }}</div>
                <div class="doc-serie">
                    {{ $venta->serie }}-{{ str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT) }}
                </div>
                <div>
                    <span class="doc-estado">
                        {{ strtoupper($venta->estado ?? 'EMITIDO') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- DATOS CLIENTE Y PAGO --}}
        <div class="info-section">
            <div class="info-box">
                <div class="info-box-title">Cliente</div>
                <div class="info-box-value">{{ $cliente->razon_social ?? 'CLIENTE VARIOS' }}</div>
                <div class="info-box-sub">
                    {{ $cliente->tipo_documento ?? 'DNI' }}: {{ $cliente->numero_documento ?? '—' }}<br>
                    @if($cliente->direccion) {{ $cliente->direccion }} @endif
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-title">Datos del Comprobante</div>
                <div class="info-box-value">{{ ucfirst($venta->forma_pago ?? 'Contado') }}</div>
                <div class="info-box-sub">
                    Fecha emisión:
                    <strong>{{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}</strong><br>
                    @if($venta->hora_emision) Hora: {{ $venta->hora_emision }}<br> @endif
                    Moneda: <strong>{{ $venta->moneda ?? 'PEN' }}</strong>
                </div>
            </div>
        </div>

        {{-- DETALLE DE PRODUCTOS --}}
        <div class="table-title">Detalle de Productos / Servicios</div>
        <table class="detalle-table">
            <thead>
                <tr>
                    <th class="text-center" width="35">#</th>
                    <th>Descripción</th>
                    <th class="text-center" width="40">U.M.</th>
                    <th class="text-center" width="70">Cant.</th>
                    <th class="text-right" width="90">P. Unit.</th>
                    <th class="text-right" width="70">Dscto.</th>
                    <th class="text-right" width="100">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($venta->detalles as $i => $det)
                    <tr>
                        <td class="text-center" style="color:#9ca3af;">{{ $i + 1 }}</td>
                        <td>
                            <div class="prod-nombre">
                                {{ $det->descripcion ?? ($det->producto->nombre ?? '—') }}
                                @if(($det->tipo_afectacion_igv ?? '10') === '10')
                                    <span class="afect-badge">IGV</span>
                                @endif
                            </div>
                            @if($det->codigo_producto)
                                <div class="prod-codigo">Cód: {{ $det->codigo_producto }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $det->unidad_medida ?? 'NIU' }}</td>
                        <td class="text-center">{{ number_format($det->cantidad, 2) }}</td>
                        <td class="text-right">S/ {{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="text-right">
                            @if(($det->descuento ?? 0) > 0)
                                S/ {{ number_format($det->descuento, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right" style="font-weight:700;">
                            S/ {{ number_format($det->total, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding:20px;color:#9ca3af;">
                            Sin detalle registrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TOTALES + LEYENDA --}}
        <div class="bottom-section">
            <div class="leyenda">
                <div class="leyenda-importe">
                    Son: <strong>{{ $montoLetras }}</strong>
                </div>
                @if($venta->observaciones)
                    <div style="font-size:11px; color:#6b7280; margin-top:8px;">
                        <strong>Observaciones:</strong> {{ $venta->observaciones }}
                    </div>
                @endif
            </div>
            <table class="totales-table">
                <tr class="row-base">
                    <td>Op. Gravada</td>
                    <td>S/ {{ number_format($venta->base_imponible ?? 0, 2) }}</td>
                </tr>
                <tr class="row-base">
                    <td>Op. Inafecta</td>
                    <td>S/ 0.00</td>
                </tr>
                <tr class="row-igv">
                    <td>IGV (18%)</td>
                    <td>S/ {{ number_format($venta->igv ?? 0, 2) }}</td>
                </tr>
                <tr class="row-total">
                    <td>TOTAL</td>
                    <td>S/ {{ number_format($venta->total ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="footer-legal">
                <strong>Representación impresa del {{ $tipoTexto }}</strong><br>
                Autorizado mediante R.S. N° 097-2012/SUNAT y modificatorias.<br>
                Consulte la validez de este comprobante en: <strong>sunat.gob.pe/whl/consulta</strong><br>
                @if($venta->sunat_hash)
                    <div class="hash-code">Hash: {{ $venta->sunat_hash }}</div>
                @endif
            </div>
            <div class="qr-section">
                @php
                    $qrData = urlencode(implode('|', [
                        $empresa->ruc,
                        $venta->tipo_documento,
                        $venta->serie,
                        $venta->correlativo,
                        number_format($venta->igv ?? 0, 2),
                        number_format($venta->total ?? 0, 2),
                        \Carbon\Carbon::parse($venta->fecha_emision)->format('Y-m-d'),
                        $cliente->tipo_documento ?? 'DNI',
                        $cliente->numero_documento ?? '',
                    ]));
                @endphp
                <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ $qrData }}&size=90x90&margin=2"
                    alt="QR SUNAT" onerror="this.style.display='none'">
                <div class="qr-label">Validar en SUNAT</div>
            </div>
        </div>

    </div>
</body>

</html>