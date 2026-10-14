<?php

namespace App\Http\Controllers\Web\Backend\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Review\StoreReviewRequest;
use App\Models\Review;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ReviewController extends Controller
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Review::with('user');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_info', function ($data) {
                    if ($data->user) {
                        return $data->user->name.' ('.$data->user->email.')';
                    }

                    return 'N/A';
                })
                ->addColumn('rating', function ($data) {
                    $rating = (int) $data->ratings;
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $stars .= $i <= $rating
                            ? '<i class="fa fa-star text-success"></i>'
                            : '<i class="fa fa-star-o text-muted"></i>';
                    }

                    return $stars;
                })
                ->addColumn('status', function ($data) {
                    if ($data->status == 'Active') {
                        return "<span class='badge bg-success'>Active</span>";
                    } else {
                        return "<span class='badge bg-danger'>Inactive</span>";
                    }
                })
                ->addColumn('action', function ($data) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
                    $buttons .= '<a href="'.route('review.edit', ['id' => $data->id]).'" type="button" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                    <i class="fe fe-edit"></i>
                    </a>';
                    $buttons .= '<a href="#" type="button" onclick="showDeleteConfirm('.$data->id.')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                    <i class="fe fe-trash"></i>
                    </a>';
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['action', 'user_info', 'status', 'rating'])
                ->make();
        }

        return view('backend.layouts.review.index');
    }

    public function create()
    {
        $users = $this->userRepository->getActiveUsersList();

        return view('backend.layouts.review.create', compact('users'));
    }

    public function store(StoreReviewRequest $request)
    {
        $validated = $request->validated();

        Review::create($validated);

        return redirect()->route('review.index')->with('t-success', 'Review created successfully!');
    }

    public function edit($id)
    {
        $data = Review::with('user')->findOrFail($id);
        $users = $this->userRepository->getActiveUsersList();

        return view('backend.layouts.review.edit', compact('data', 'users'));
    }

    public function update(StoreReviewRequest $request, $id)
    {
        $validated = $request->validated();

        $review = Review::findOrFail($id);
        $review->update($validated);

        return redirect()->route('review.index')->with('t-success', 'Review updated successfully!');
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        if ($review) {
            $review->delete();
        }

        return response()->json([
            't-success' => true,
            'message' => 'Data deleted successfully',
        ], 200);
    }
}
