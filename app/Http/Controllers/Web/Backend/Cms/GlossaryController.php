<?php

namespace App\Http\Controllers\Web\Backend\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Cms\UpdateGlossaryRequest;
use App\Models\Cms\Glossary;
use App\Models\Cms\GlossaryTerm;
use Illuminate\Support\Facades\DB;

class GlossaryController extends Controller
{
    public function index()
    {
        $glossary = Glossary::with('terms')->first();

        return view('backend.layouts.cms.glossary', compact('glossary'));
    }

    public function update(UpdateGlossaryRequest $request)
    {

        DB::beginTransaction();
        try {
            $glossary = Glossary::firstOrCreate([]);
            $glossary->update(['title' => $request->title]);

            // Delete old terms and recreate
            $glossary->terms()->delete();

            if (! empty($request->terms) && is_array($request->terms)) {
                foreach ($request->terms as $index => $termData) {
                    if (empty(trim($termData['term'] ?? '')) || empty(trim($termData['definition'] ?? ''))) {
                        continue;
                    }
                    GlossaryTerm::create([
                        'glossary_id' => $glossary->id,
                        'term' => $termData['term'],
                        'definition' => $termData['definition'],
                        'id' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('cms.glossary.index')
                ->with('success', 'Glossary updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }
}
