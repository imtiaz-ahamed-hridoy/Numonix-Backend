<?php

namespace App\Http\Controllers\Web\Backend\Mail;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Mail\StoreMailTemplateRequest;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class MailController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $data = EmailTemplate::all();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('body', function ($data) {
                    return $data->body ?? 'N/A';
                })

                // action buttons
                ->addColumn('action', function ($data) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group" aria-label="Offer Actions">';
                    $buttons .= '<a href="'.route('mail.edit', $data->id).'" class="btn btn-primary text-white" title="Edit"><i class="fe fe-edit"></i></a>';
                    $buttons .= '<button type="button" onclick="showDeleteConfirm('.$data->id.')" class="btn btn-danger text-white" title="Delete"><i class="fe fe-trash"></i></button>';
                    $buttons .= '</div>';

                    return $buttons;
                })

                ->rawColumns(['action', 'body'])
                ->make(true);
        }

        return view('backend.layouts.sendMail.index');
    }

    public function create()
    {
        return view('backend.layouts.sendMail.create');
    }

    public function store(StoreMailTemplateRequest $request)
    {
        $validated = $request->validated();

        $data = EmailTemplate::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
        ]);

        return redirect()->route('mail.index')->with('t-success', 'Mail Template Created Successfully');
    }

    public function edit($id)
    {
        $data = EmailTemplate::findOrFail($id);

        return view('backend.layouts.sendMail.edit', compact('data'));
    }

    public function update(StoreMailTemplateRequest $request, $id)
    {
        $validated = $request->validated();

        $data = EmailTemplate::findOrFail($id);
        $data->name = $validated['name'];
        $data->subject = $validated['subject'];
        $data->body = $validated['body'];
        $data->save();

        return redirect()->route('mail.index')->with('t-success', 'Tempalte updated successfully');
    }

    public function destroy($id)
    {
        $data = EmailTemplate::findOrFail($id);
        $data->delete();

        return response([
            't-success' => true,
            'message' => 'Data Deleted Successfully',
        ]);
    }
}
