<?php

namespace App\Http\Controllers\Web\Backend\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Cms\UpdateTermsConditionsRequest;
use App\Models\Cms\TermsConditions;
use App\Models\Cms\TermsConditionsSection;
use Illuminate\Support\Facades\DB;

class TermsConditionsController extends Controller
{
    public function index()
    {
        $terms = TermsConditions::with('sections')->first();

        return view('backend.layouts.cms.terms-conditions', compact('terms'));
    }

    public function update(UpdateTermsConditionsRequest $request)
    {

        DB::beginTransaction();
        try {
            $terms = TermsConditions::firstOrCreate([]);
            $terms->update([
                'title' => $request->title,
                'last_updated_label' => $request->last_updated_label,
            ]);

            // Delete old sections and recreate
            $terms->sections()->delete();

            if (! empty($request->sections) && is_array($request->sections)) {
                foreach ($request->sections as $index => $sectionData) {
                    if (empty(trim($sectionData['title'] ?? '')) || empty(trim($sectionData['body'] ?? ''))) {
                        continue;
                    }
                    TermsConditionsSection::create([
                        'terms_conditions_id' => $terms->id,
                        'section_number' => $index + 1,
                        'title' => $sectionData['title'],
                        'body' => $sectionData['body'],
                        'id' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('cms.terms-conditions.index')
                ->with('success', 'Terms & Conditions updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }
}
