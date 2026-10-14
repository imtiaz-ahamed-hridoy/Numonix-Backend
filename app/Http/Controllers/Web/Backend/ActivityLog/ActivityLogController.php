<?php

namespace App\Http\Controllers\Web\Backend\ActivityLog;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;

class ActivityLogController extends Controller
{
    /**
     * Display the activity logs page.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $data = Activity::with('causer')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('log_name', function (Activity $activity) {
                    return ucwords(str_replace('_', ' ', $activity->log_name ?? '-'));
                })
                ->addColumn('description', function (Activity $activity) {
                    return $activity->description ?? '-';
                })
                ->addColumn('subject_type', function (Activity $activity) {
                    if (! $activity->subject_type) {
                        return '-';
                    }
                    $parts = explode('\\', $activity->subject_type);

                    return end($parts);
                })
                ->addColumn('event', function (Activity $activity) {
                    return $activity->event ?? '-';
                })
                ->addColumn('causer', function (Activity $activity) {
                    return $activity->causer
                        ? $activity->causer->name.' ('.$activity->causer->email.')'
                        : 'System';
                })
                ->addColumn('attributes', function (Activity $activity) {
                    $properties = $activity->properties;
                    $output = '';

                    if ($properties->has('attributes')) {
                        $attrs = $properties->get('attributes', []);
                        $output .= '<pre class="mb-0" style="max-height:100px;overflow:auto;font-size:11px;">'
                            .htmlspecialchars(json_encode($attrs, JSON_PRETTY_PRINT))
                            .'</pre>';
                    }

                    if ($properties->has('old')) {
                        $old = $properties->get('old', []);
                        $output .= '<strong class="text-danger">Old:</strong><pre class="mb-0" style="max-height:100px;overflow:auto;font-size:11px;">'
                            .htmlspecialchars(json_encode($old, JSON_PRETTY_PRINT))
                            .'</pre>';
                    }

                    return $output ?: '-';
                })
                ->addColumn('action', function (Activity $activity) {
                    return '<button onclick="showDeleteConfirm('.$activity->id.')"
                        class="btn btn-sm btn-danger" title="Delete">
                        <i class="fe fe-trash"></i>
                    </button>';
                })
                ->rawColumns(['attributes', 'action'])
                ->make(true);
        }

        return view('backend.layouts.activity.index');
    }

    /**
     * Delete an activity log entry.
     */
    public function destroy($id): JsonResponse
    {
        $activity = Activity::find($id);

        if (! $activity) {
            return response()->json([
                't-success' => false,
                'message' => 'Activity log not found.',
            ], 404);
        }

        $activity->delete();

        return response()->json([
            't-success' => true,
            'message' => 'Activity log deleted successfully.',
        ]);
    }
}
