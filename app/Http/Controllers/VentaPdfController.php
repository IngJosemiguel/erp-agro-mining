<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class VentaPdfController extends Controller
{
  public function descargar($id)
  {
    $venta = Venta::with([
      'detalles.producto',
      'cliente',
      'empresa',
    ])->findOrFail($id);

    $empresa = $venta->empresa;
    $cliente = $venta->cliente;

    $tipoTexto = match ($venta->tipo_documento) {
      '01' => 'FACTURA ELECTRÓNICA',
      '03' => 'BOLETA DE VENTA ELECTRÓNICA',
      default => 'NOTA DE VENTA',
    };

    // Monto en letras
    $montoLetras = $this->numeroALetras($venta->total ?? 0, $venta->moneda ?? 'PEN');

    return view('ventas.pdf', compact(
      'venta',
      'empresa',
      'cliente',
      'tipoTexto',
      'montoLetras'
    ));
  }

  private function numeroALetras(float $numero, string $moneda = 'PEN'): string
  {
    $unidades = [
      '',
      'UN',
      'DOS',
      'TRES',
      'CUATRO',
      'CINCO',
      'SEIS',
      'SIETE',
      'OCHO',
      'NUEVE',
      'DIEZ',
      'ONCE',
      'DOCE',
      'TRECE',
      'CATORCE',
      'QUINCE',
      'DIECISÉIS',
      'DIECISIETE',
      'DIECIOCHO',
      'DIECINUEVE'
    ];
    $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $centenas = [
      '',
      'CIENTO',
      'DOSCIENTOS',
      'TRESCIENTOS',
      'CUATROCIENTOS',
      'QUINIENTOS',
      'SEISCIENTOS',
      'SETECIENTOS',
      'OCHOCIENTOS',
      'NOVECIENTOS'
    ];

    $entero = (int) $numero;
    $decimals = round(($numero - $entero) * 100);

    $convertir = function (int $n) use ($unidades, $decenas, $centenas, &$convertir): string {
      if ($n === 0)
        return '';
      if ($n < 20)
        return $unidades[$n];
      if ($n < 100) {
        $d = intdiv($n, 10);
        $u = $n % 10;
        return $decenas[$d] . ($u ? ' Y ' . $unidades[$u] : '');
      }
      if ($n === 100)
        return 'CIEN';
      if ($n < 1000) {
        $c = intdiv($n, 100);
        $r = $n % 100;
        return $centenas[$c] . ($r ? ' ' . $convertir($r) : '');
      }
      if ($n < 1000000) {
        $miles = intdiv($n, 1000);
        $r = $n % 1000;
        $txt = ($miles === 1 ? 'MIL' : $convertir($miles) . ' MIL');
        return $txt . ($r ? ' ' . $convertir($r) : '');
      }
      return (string) $n;
    };

    $letras = $entero === 0 ? 'CERO' : $convertir($entero);
    $centStr = str_pad((string) $decimals, 2, '0', STR_PAD_LEFT);
    $monStr = $moneda === 'USD' ? 'DÓLARES AMERICANOS' : 'SOLES';

    return "{$letras} CON {$centStr}/100 {$monStr}";
  }
}
