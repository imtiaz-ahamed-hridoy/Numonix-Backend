<?php

namespace App\Http\Controllers\Web\Backend\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Faq\StoreFaqRequest;
use App\Models\Faq;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Faq::all();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($data) {
                    if ($data->status == 'Active') {
                        return "<span class='badge bg-success'>Active</span>";
                    } else {
                        return "<span class='badge bg-danger'>Inactive</span>";
                    }
                })
                ->addColumn('action', function ($data) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';

                    // Edit button
                    $buttons .= '<a href="'.route('faq.edit', ['id' => $data->id]).'" type="button" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                    <i class="fe fe-edit"></i>
                    </a>';
                    $buttons .= '<a href="#" type="button" onclick="showDeleteConfirm('.$data->id.')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                    <i class="fe fe-trash"></i>
                    </a>';
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['action', 'status'])
                ->make();
        }

        return view('backend.layouts.faq.index');
    }

    public function create()
    {
        return view('backend.layouts.faq.create');
    }

    public function store(StoreFaqRequest $request)
    {
        Faq::create([
            'section' => $request->section,
            'question' => $request->question,
            'answer' => $request->answer,
            'status' => $request->status,
        ]);

        return redirect()->route('faq.index')->with('t-success', 'Faq saved successfully!');
    }

    public function edit($id)
    {
        $data = Faq::findOrFail($id);

        return view('backend.layouts.faq.edit', compact('data'));
    }

    public function update(StoreFaqRequest $request, $id)
    {
        $validated = $request->validated();
        $data = Faq::findOrFail($id);
        $data->update([
            'section' => $validated['section'] ?? $data->section,
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('faq.index')->with('t-success', 'FAQ updated successfully!');
    }

    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        if ($faq) {
            $faq->delete();
        }

        return response()->json([
            't-success' => true,
            'message' => 'Data deleted successfully',
        ], 200);
    }
}
