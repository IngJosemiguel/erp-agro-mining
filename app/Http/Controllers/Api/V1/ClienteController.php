<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query()
            ->where('empresa_id', $request->input('empresa_id', 1))
            ->with(['tipoCliente:id,nombre', 'zona:id,nombre']);

        if ($request->filled('buscar')) {
            $query->buscar($request->input('buscar'));
        }

        if ($request->filled('tipo_cliente_id')) {
            $query->where('tipo_cliente_id', $request->input('tipo_cliente_id'));
        }

        if ($request->filled('zona_id')) {
            $query->where('zona_id', $request->input('zona_id'));
        }

        $clientes = $query->orderBy('razon_social')
            ->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'data' => $clientes]);
    }

    public function show(int $id): JsonResponse
    {
        $cliente = Cliente::with(['tipoCliente', 'zona', 'listaPrecio'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $cliente]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'tipo_documento' => 'required|in:DNI,RUC,CE,PASAPORTE,OTROS',
            'numero_documento' => 'required|string|max:20',
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'ubigeo' => 'nullable|string|max:6',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'tipo_cliente_id' => 'nullable|exists:tipos_cliente,id',
            'zona_id' => 'nullable|exists:zonas,id',
            'lista_precio_id' => 'nullable|exists:listas_precio,id',
            'limite_credito' => 'nullable|numeric|min:0',
            'dias_credito' => 'nullable|integer|min:0',
        ]);

        $cliente = Cliente::create($validated);
        return response()->json(['success' => true, 'message' => 'Cliente creado.', 'data' => $cliente], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'razon_social' => 'sometimes|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'tipo_cliente_id' => 'nullable|exists:tipos_cliente,id',
            'zona_id' => 'nullable|exists:zonas,id',
            'lista_precio_id' => 'nullable|exists:listas_precio,id',
            'limite_credito' => 'nullable|numeric|min:0',
            'dias_credito' => 'nullable|integer|min:0',
            'activo' => 'boolean',
        ]);

        $cliente->update($validated);
        return response()->json(['success' => true, 'message' => 'Cliente actualizado.', 'data' => $cliente->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        Cliente::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Cliente eliminado.']);
    }

    /**
     * GET /api/v1/clientes/consultar-documento/{tipo}/{numero}
     * Buscar por DNI/RUC (preparado para integración API RENIEC/SUNAT).
     */
    public function consultarDocumento(string $tipo, string $numero): JsonResponse
    {
        $cliente = Cliente::where('tipo_documento', strtoupper($tipo))
            ->where('numero_documento', $numero)
            ->first();

        if ($cliente) {
            return response()->json(['success' => true, 'source' => 'local', 'data' => $cliente]);
        }

        // TODO: Integrar con API de consulta RENIEC/SUNAT para RUC/DNI
        return response()->json([
            'success' => false,
            'message' => 'Cliente no encontrado. Integración RENIEC/SUNAT pendiente.',
        ], 404);
    }
}
