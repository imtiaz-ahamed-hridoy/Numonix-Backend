<?php

namespace App\Http\Controllers\Web\Backend\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Cms\UpdatePrivacyPolicyRequest;
use App\Models\Cms\PrivacyPolicy;
use App\Models\Cms\PrivacyPolicySection;
use Illuminate\Support\Facades\DB;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $privacy = PrivacyPolicy::with('sections')->first();

        return view('backend.layouts.cms.privacy-policy', compact('privacy'));
    }

    public function update(UpdatePrivacyPolicyRequest $request)
    {

        DB::beginTransaction();
        try {
            $privacy = PrivacyPolicy::firstOrCreate([]);
            $privacy->update([
                'title' => $request->title,
                'last_updated_label' => $request->last_updated_label,
            ]);

            // Delete old sections and recreate
            $privacy->sections()->delete();

            if (! empty($request->sections) && is_array($request->sections)) {
                foreach ($request->sections as $index => $sectionData) {
                    if (empty(trim($sectionData['title'] ?? '')) || empty(trim($sectionData['body'] ?? ''))) {
                        continue;
                    }
                    PrivacyPolicySection::create([
                        'privacy_policy_id' => $privacy->id,
                        'section_number' => $index + 1,
                        'title' => $sectionData['title'],
                        'body' => $sectionData['body'],
                        'id' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('cms.privacy-policy.index')
                ->with('success', 'Privacy Policy updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }
}
