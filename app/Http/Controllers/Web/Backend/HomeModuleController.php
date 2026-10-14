<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\HomeModule\StoreHomeModuleRequest;
use App\Models\HomeModule;
use App\Repositories\Interfaces\HomeModuleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class HomeModuleController extends Controller
{
    protected HomeModuleRepositoryInterface $homeModuleRepository;

    public function __construct(HomeModuleRepositoryInterface $homeModuleRepository)
    {
        $this->homeModuleRepository = $homeModuleRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->homeModuleRepository->query()
                ->orderBy('id', 'ASC');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('title', function (HomeModule $module) {
                    $iconHtml = '';
                    if ($module->icon && file_exists(public_path($module->icon))) {
                        $iconHtml = "<img src='".url($module->icon)."' alt='' style='width:20px;height:20px;object-fit:contain;vertical-align:middle' class='me-1'> ";
                    }

                    return $iconHtml.e($module->title);
                })
                ->editColumn('is_premium', function (HomeModule $module) {
                    return $module->is_premium
                        ? "<span class='badge bg-danger'><i class='fa fa-lock'></i> Premium</span>"
                        : "<span class='badge bg-success'><i class='fa fa-unlock'></i> Tier 1</span>";
                })
                ->editColumn('is_active', function (HomeModule $module) {
                    return $module->is_active
                        ? "<span class='badge bg-success'>Active</span>"
                        : "<span class='badge bg-secondary'>Inactive</span>";
                })
                ->editColumn('badge_text', function (HomeModule $module) {
                    if (! $module->badge_text) {
                        return '-';
                    }

                    return "<span class='badge' style='background:#6B7280;color:#fff'>{$module->badge_text}</span>";
                })
                ->addColumn('action', function (HomeModule $module) {
                    return '
                        <a href="'.route('home-module.edit', $module->id).'" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
                        <button class="btn btn-sm btn-danger" onclick="deleteModule('.$module->id.')"><i class="fa fa-trash"></i></button>
                    ';
                })
                ->rawColumns(['title', 'is_premium', 'is_active', 'badge_text', 'action'])
                ->make(true);
        }

        return view('backend.layouts.home-module.index');
    }

    public function create()
    {
        return view('backend.layouts.home-module.create');
    }

    public function store(StoreHomeModuleRequest $request)
    {
        $validated = $request->validated();

        // Handle SVG upload (optional - seeder provides defaults)
        if ($request->hasFile('icon')) {
            $validated['icon'] = $this->uploadIcon($request);
        } else {
            unset($validated['icon']);
        }

        $validated['is_premium'] = $request->boolean('is_premium');
        $validated['is_active'] = $request->boolean('is_active');

        // module_type and module_key are managed via seeder only
        $validated['module_type'] = 'external';
        $validated['module_key'] = 'custom_'.Str::slug($validated['title'], '_');

        $this->homeModuleRepository->create($validated);

        return redirect()->route('home-module.index')
            ->with('success', 'Module created successfully.');
    }

    public function edit($id)
    {
        $module = $this->homeModuleRepository->findOrFail((int) $id);

        return view('backend.layouts.home-module.edit', compact('module'));
    }

    public function update(StoreHomeModuleRequest $request, $id)
    {
        $module = $this->homeModuleRepository->findOrFail((int) $id);

        $validated = $request->validated();

        // Handle SVG upload (only if new file provided)
        if ($request->hasFile('icon')) {
            // Delete old icon file
            if ($module->icon && file_exists(public_path($module->icon))) {
                @unlink(public_path($module->icon));
            }
            $validated['icon'] = $this->uploadIcon($request);
        } else {
            unset($validated['icon']); // Keep existing icon
        }

        $validated['is_premium'] = $request->boolean('is_premium');
        $validated['is_active'] = $request->boolean('is_active');

        // Don't touch module_type / module_key on update
        $module->update($validated);

        return redirect()->route('home-module.index')
            ->with('success', 'Module updated successfully.');
    }

    public function destroy($id)
    {
        $module = $this->homeModuleRepository->findOrFail((int) $id);

        // Delete icon file
        if ($module->icon && file_exists(public_path($module->icon))) {
            @unlink(public_path($module->icon));
        }

        $module->delete();

        return response()->json(['success' => true, 'message' => 'Module deleted successfully.']);
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ----------------------------------------------------------------*/

    /**
     * Upload the SVG icon file to public/uploads/home-modules/.
     */
    private function uploadIcon(Request $request): string
    {
        $file = $request->file('icon');

        // Validate SVG by extension (mimes:svg is unreliable on production servers)
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'svg') {
            abort(422, 'Only SVG files are allowed.');
        }

        $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $file->move(public_path('uploads/home-modules'), $filename);

        return 'uploads/home-modules/'.$filename;
    }
}
