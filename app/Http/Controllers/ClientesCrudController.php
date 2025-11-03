<?php

namespace App\Http\Controllers;

use App\Models\ClientesAsignacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientesCrudController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $clientes = ClientesAsignacion::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sq) use ($q) {
                    $sq->where('rfc', 'like', "%$q%")
                       ->orWhere('nombre_cliente', 'like', "%$q%")
                       ->orWhere('nombre_empresa', 'like', "%$q%")
                       ->orWhere('correo_empresa', 'like', "%$q%")
                       ->orWhere('telefono', 'like', "%$q%");
                });
            })
            ->orderBy('nombre_cliente')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'q'));
    }

    public function create()
    {
        $cliente = new ClientesAsignacion();
        return view('clientes.form', compact('cliente'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        ClientesAsignacion::create($data);
        return redirect()->route('clientes.index')->with('ok', 'Cliente creado.');
    }

    public function edit($id)
    {
        $cliente = ClientesAsignacion::findOrFail($id);
        return view('clientes.form', compact('cliente'));
    }

    public function update(Request $request, $id)
    {
        $cliente = ClientesAsignacion::findOrFail($id);
        $data = $this->validated($request, $cliente->id);
        $cliente->update($data);
        return redirect()->route('clientes.index')->with('ok', 'Cliente actualizado.');
    }

    public function destroy($id)
    {
        $cliente = ClientesAsignacion::findOrFail($id);
        $cliente->delete();
        return back()->with('ok', 'Cliente eliminado.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nombre_cliente'  => ['required','string','max:150'],
            'nombre_empresa'  => ['nullable','string','max:150'],
            'direccion'       => ['nullable','string','max:255'],
            'responsable'     => ['nullable','string','max:150'],
            'telefono'        => ['nullable','string','max:30'],
            'rfc'             => ['nullable','string','max:50'],
            'imagen'          => ['nullable','string','max:255'],   // usa ruta/url; si quieres upload lo agregamos luego
            'correo_empresa'  => ['nullable','email','max:150'],
        ]);
    }
}
