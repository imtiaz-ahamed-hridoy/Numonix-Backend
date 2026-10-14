<?php

namespace App\Http\Controllers\Web\Backend\EmailLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\EmailLog\SendBulkMailRequest;
use App\Mail\CustomUserMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\DataTables;

class EmailLogController extends Controller
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {

        if ($request->ajax()) {
            $data = EmailLog::with('user')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('body', function ($data) {
                    return $data->body ?? 'N/A';
                })
                ->addColumn('user', function ($data) {
                    return $data->user->first_name ?? 'N/A';
                })
                ->addColumn('status', function ($data) {
                    $status = ucfirst($data->status ?? 'pending'); // Capital first letter

                    switch (strtolower($status)) {
                        case 'success':
                            $class = 'btn btn-success btn-sm';
                            break;
                        case 'pending':
                            $class = 'btn btn-warning btn-sm';
                            break;
                        case 'failed':
                            $class = 'btn btn-danger btn-sm';
                            break;
                        default:
                            $class = 'btn btn-info btn-sm';
                    }

                    return "<button type='button' class='{$class}'>{$status}</button>";
                })
                // action buttons
                ->addColumn('action', function ($data) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group" aria-label="Offer Actions">';
                    $buttons .= '<button type="button" onclick="showDeleteConfirm('.$data->id.')" class="btn btn-danger text-white" title="Delete"><i class="fe fe-trash"></i></button>';
                    $buttons .= '</div>';

                    return $buttons;
                })

                ->rawColumns(['action', 'body', 'status'])
                ->make(true);
        }

        return view('backend.layouts.EmailLog.index');
    }

    public function create()
    {
        $users = $this->userRepository->all();

        $templates = EmailTemplate::all();

        return view('backend.layouts.EmailLog.create', compact('users', 'templates'));
    }

    public function sendBulkMail(SendBulkMailRequest $request)
    {

        $template = EmailTemplate::findOrFail($request->template_id);
        $scheduleTime = $request->schedule_time ? Carbon::parse($request->schedule_time) : null;

        // ------------------------
        // Fetch Users
        // ------------------------
        $users = collect();
        if (! empty($request->user_ids)) {
            $users = $this->userRepository->getByIds($request->user_ids ?? []);
        }

        // ------------------------
        // Send Emails to Users
        // ------------------------
        foreach ($users as $user) {
            $subject = $this->parseTemplate($template->subject, $user);
            $body = $this->parseTemplate($template->body, $user);

            $log = EmailLog::create([
                'template_id' => $template->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'subject' => $subject,
                'body' => $body,
            ]);

            try {
                if ($scheduleTime && $scheduleTime->isFuture()) {
                    Mail::to($user->email)
                        ->later($scheduleTime, new CustomUserMail($subject, $body));

                    $log->update([
                        'status' => 'scheduled',
                        'sent_at' => $scheduleTime,
                    ]);
                } else {
                    Mail::to($user->email)->send(new CustomUserMail($subject, $body));
                    $log->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                $log->update(['status' => 'failed']);
                // Continue to next user/store instead of stopping all
                Log::error("Email failed for User ID {$user->id}: {$e->getMessage()}");
            }
        }

        return redirect()->route('email.index')->with('t-success', 'Emails have been sent or scheduled successfully!');
    }

    private function parseTemplate($text, $user)
    {
        $replacements = [
            '{{name}}' => $user->name,
            '{{email}}' => $user->email,
            '{{plan_name}}' => '',
            '{{start_date}}' => '',
            '{{end_date}}' => '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    public function destroy($id)
    {
        $data = EmailLog::findOrFail($id);
        $data->delete();

        return response([
            't-success' => true,
            'message' => 'Data Deleted Successfully',
        ]);
    }
}
