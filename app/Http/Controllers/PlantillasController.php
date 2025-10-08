<?php
// app/Http/Controllers/PlantillasController.php
namespace App\Http\Controllers;

use App\Models\Plantilla;
use App\Models\PlantillaPaso;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PlantillasController extends Controller
{
    // LISTA PLANTILLAS
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $plantillas = Plantilla::query()
            ->when($q, fn($qr) => $qr->where('nombre', 'like', "%{$q}%")
                                     ->orWhere('descripcion', 'like', "%{$q}%"))
            ->orderBy('nombre')
            ->get();

        return view('plantillas.index', compact('plantillas', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120', 'unique:plantillas,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        Plantilla::create($data);
        return back()->with('ok', 'Plantilla creada.');
    }

    public function update(Request $request, Plantilla $plantilla)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120', Rule::unique('plantillas', 'nombre')->ignore($plantilla->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $plantilla->update($data);
        return back()->with('ok', 'Plantilla actualizada.');
    }

    public function destroy(Plantilla $plantilla)
    {
        $plantilla->delete();
        return back()->with('ok', 'Plantilla eliminada.');
    }

    // VISTA PASOS
    public function pasos(Plantilla $plantilla)
    {
        $plantilla->load('pasos');
        return view('plantillas.pasos', compact('plantilla'));
    }

    // CREATE PASO (máx. 15)
    public function pasoStore(Request $request, Plantilla $plantilla)
    {
        $request->validate([
            'titulo' => ['required', 'string', 'max:200'],
        ]);

        $count = $plantilla->pasos()->count();
        if ($count >= 15) {
            return back()->withErrors(['titulo' => 'Has alcanzado el máximo de 15 pasos.'])->withInput();
        }

        $maxActual = (int) ($plantilla->pasos()->max('numero') ?? 0);
        $next = $maxActual + 1;

        PlantillaPaso::create([
            'plantilla_id' => $plantilla->id,
            'numero'       => $next,
            'titulo'       => $request->titulo,
        ]);

        return back()->with('ok', 'Paso agregado.');
    }

    public function pasoUpdate(Request $request, Plantilla $plantilla, PlantillaPaso $paso)
    {
        $this->authorizePaso($plantilla, $paso);

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:200'],
        ]);

        $paso->update($data);
        return back()->with('ok', 'Paso actualizado.');
    }

    public function pasoDestroy(Plantilla $plantilla, PlantillaPaso $paso)
    {
        $this->authorizePaso($plantilla, $paso);

        DB::transaction(function () use ($plantilla, $paso) {
            $num = $paso->numero;
            $paso->delete();
            // Recompactar numeración
            $plantilla->pasos()->where('numero', '>', $num)->decrement('numero');
        });

        return back()->with('ok', 'Paso eliminado.');
    }

    public function pasoMover(Request $request, Plantilla $plantilla, PlantillaPaso $paso)
    {
        $this->authorizePaso($plantilla, $paso);

        $dir = $request->get('dir'); // 'up' | 'down'

        DB::transaction(function () use ($plantilla, $paso, $dir) {
            $min = (int) ($plantilla->pasos()->min('numero') ?? 1);
            $max = (int) ($plantilla->pasos()->max('numero') ?? 1);

            if ($dir === 'up') {
                if ($paso->numero <= $min) return; // Ya está arriba
                $target = $paso->numero - 1;

                // Tomamos el vecino a intercambiar y lo bloqueamos
                $swap = $plantilla->pasos()
                    ->where('numero', $target)
                    ->lockForUpdate()
                    ->first();

                if (!$swap) return;

                $original = $paso->numero;
                $tmp = $min - 1; // valor que no existe en esta plantilla

                // 1) libero el número del paso actual
                $paso->update(['numero' => $tmp]);
                // 2) subo el vecino al número original
                $swap->update(['numero' => $original]);
                // 3) coloco el paso en su objetivo
                $paso->update(['numero' => $target]);
            }

            if ($dir === 'down') {
                if ($paso->numero >= $max) return; // Ya está abajo
                $target = $paso->numero + 1;

                $swap = $plantilla->pasos()
                    ->where('numero', $target)
                    ->lockForUpdate()
                    ->first();

                if (!$swap) return;

                $original = $paso->numero;
                $tmp = $min - 1;

                $paso->update(['numero' => $tmp]);
                $swap->update(['numero' => $original]);
                $paso->update(['numero' => $target]);
            }
        });

        return back();
    }

    private function authorizePaso(Plantilla $plantilla, PlantillaPaso $paso): void
    {
        abort_unless($paso->plantilla_id === $plantilla->id, 404);
    }
}
