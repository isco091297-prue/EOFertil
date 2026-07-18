<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProblemRequest;
use App\Http\Requests\UpdateProblemRequest;
use App\Models\Crop;
use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProblemController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->get('search', ''));

        $problems = Problem::with('crop')

            ->when($search !== '', function ($query) use ($search) {

                $query->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");

            })

            ->orderBy('name')

            ->paginate(10)

            ->withQueryString();

        return view(
            'problems.index',
            compact('problems', 'search')
        );
    }

    public function create(): View
    {
        $crops = Crop::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'problems.create',
            compact('crops')
        );
    }

    public function store(StoreProblemRequest $request): RedirectResponse
{
    $data = $request->validated();

    if ($request->hasFile('image')) {

        $data['image_path'] = $request->file('image')
            ->store('problems', 'public');

    }

    unset($data['image']);

    Problem::create($data);

    return redirect()
        ->route('problems.index')
        ->with(
            'success',
            'Problema creado correctamente.'
        );
}

    public function show(Problem $problem): View
    {
        $problem->load('crop');

        return view(
            'problems.show',
            compact('problem')
        );
    }

    public function edit(Problem $problem): View
    {
        $crops = Crop::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'problems.edit',
            compact(
                'problem',
                'crops'
            )
        );
    }

    public function update(
    UpdateProblemRequest $request,
    Problem $problem
): RedirectResponse {

    $data = $request->validated();

    if ($request->hasFile('image')) {

        if (
            $problem->image_path &&
            Storage::disk('public')->exists($problem->image_path)
        ) {

            Storage::disk('public')
                ->delete($problem->image_path);

        }

        $data['image_path'] = $request->file('image')
            ->store('problems', 'public');

    }

    unset($data['image']);

    $problem->update($data);

    return redirect()
        ->route('problems.index')
        ->with(
            'success',
            'Problema actualizado correctamente.'
        );
}

    public function destroy(
    Problem $problem
): RedirectResponse {

    if (
        $problem->image_path &&
        Storage::disk('public')->exists($problem->image_path)
    ) {

        Storage::disk('public')
            ->delete($problem->image_path);

    }

    $problem->delete();

    return redirect()
        ->route('problems.index')
        ->with(
            'success',
            'Problema eliminado correctamente.'
        );
}
}
