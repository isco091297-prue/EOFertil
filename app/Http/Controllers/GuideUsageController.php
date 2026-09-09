<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Crop;
use App\Models\GuideUsage;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Http\Request;

class GuideUsageController extends Controller
{
    /**
     * Mostrar reporte de utilización de la Guía.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Filtros
        |--------------------------------------------------------------------------
        */

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $branchId = $request->input('branch_id');
        $userId = $request->input('user_id');
        $cropId = $request->input('crop_id');
        $problemId = $request->input('problem_id');

        /*
        |--------------------------------------------------------------------------
        | Consulta base
        |--------------------------------------------------------------------------
        */

        $query = GuideUsage::query()
            ->with([
                'user.branch',
                'crop',
                'problem',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Rango de fechas
        |--------------------------------------------------------------------------
        */

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        /*
        |--------------------------------------------------------------------------
        | Sucursal
        |--------------------------------------------------------------------------
        */

        if ($branchId) {
            $query->whereHas('user', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Usuario
        |--------------------------------------------------------------------------
        */

        if ($userId) {
            $query->where('user_id', $userId);
        }

        /*
        |--------------------------------------------------------------------------
        | Cultivo
        |--------------------------------------------------------------------------
        */

        if ($cropId) {
            $query->where('crop_id', $cropId);
        }

        /*
        |--------------------------------------------------------------------------
        | Problema
        |--------------------------------------------------------------------------
        */

        if ($problemId) {
            $query->where('problem_id', $problemId);
        }

        /*
        |--------------------------------------------------------------------------
        | Consultas detalladas
        |--------------------------------------------------------------------------
        */

        $usages = (clone $query)
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Total de consultas
        |--------------------------------------------------------------------------
        */

        $totalConsultas = (clone $query)->count();

        /*
        |--------------------------------------------------------------------------
        | Usuarios activos
        |--------------------------------------------------------------------------
        */

        $usuariosActivos = (clone $query)
            ->distinct('user_id')
            ->count('user_id');

        /*
        |--------------------------------------------------------------------------
        | Ranking de usuarios
        |--------------------------------------------------------------------------
        */

        $rankingUsuarios = (clone $query)
            ->selectRaw('user_id, COUNT(*) as total')
            ->with('user.branch')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ranking por sucursal
        |--------------------------------------------------------------------------
        */

        $rankingSucursales = (clone $query)
            ->join('users', 'guide_usages.user_id', '=', 'users.id')
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->selectRaw(
                'users.branch_id, branches.name, COUNT(*) as total'
            )
            ->groupBy(
                'users.branch_id',
                'branches.name'
            )
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ranking de cultivos
        |--------------------------------------------------------------------------
        */

        $rankingCultivos = (clone $query)
            ->join('crops', 'guide_usages.crop_id', '=', 'crops.id')
            ->selectRaw(
                'guide_usages.crop_id, crops.name, COUNT(*) as total'
            )
            ->groupBy(
                'guide_usages.crop_id',
                'crops.name'
            )
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ranking de problemas
        |--------------------------------------------------------------------------
        */

        $rankingProblemas = (clone $query)
            ->join('problems', 'guide_usages.problem_id', '=', 'problems.id')
            ->selectRaw(
                'guide_usages.problem_id, problems.name, COUNT(*) as total'
            )
            ->groupBy(
                'guide_usages.problem_id',
                'problems.name'
            )
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Datos para filtros
        |--------------------------------------------------------------------------
        */

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $crops = Crop::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $problems = Problem::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('guide_usage.index', compact(
            'usages',
            'totalConsultas',
            'usuariosActivos',
            'rankingUsuarios',
            'rankingSucursales',
            'rankingCultivos',
            'rankingProblemas',
            'branches',
            'users',
            'crops',
            'problems',
            'dateFrom',
            'dateTo',
            'branchId',
            'userId',
            'cropId',
            'problemId',
        ));
    }
}
