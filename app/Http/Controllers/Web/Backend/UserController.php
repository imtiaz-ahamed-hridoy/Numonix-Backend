<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\User\StoreUserRequest;
use App\Http\Requests\Web\Backend\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of all users.
     */
    public function index(Request $request): JsonResponse|View
    {

        if ($request->ajax()) {
            $data = $this->userRepository->getLatest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('avatar', function ($data) {
                    $defaultImage = asset('frontend/default-avatar-profile.jpg');
                    $url = $data->avatar_url ?: $defaultImage;

                    return '<img src="'.$url.'" alt="Avatar" width="50" height="50" style="object-fit:cover; border-radius:50%;">';
                })
                ->addColumn('subscription_tier', function ($data) {
                    $tier = $data->subscription_tier ?? 'tier_1';
                    $badges = [
                        'tier_1' => '<span class="badge bg-secondary">Tier 1</span>',
                        'tier_2' => '<span class="badge bg-primary">Tier 2</span>',
                        'tier_3' => '<span class="badge bg-warning text-dark">Tier 3</span>',
                    ];

                    return $badges[$tier] ?? $badges['tier_1'];
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                    <a href="'.route('user.edit', ['id' => $data->id]).'" type="button" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                        <i class="fe fe-edit"></i>
                                    </a>
                                    <a href="#" type="button" onclick="showDeleteConfirm('.$data->id.')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </a>
                                </div>';
                })
                ->rawColumns(['avatar', 'subscription_tier', 'action'])
                ->make(true);
        }

        return view('backend.layouts.user.index');
    }

    public function show($id)
    {
        $data = $this->userRepository->findOrFail((int) $id);

        return view('backend.layouts.user.view', compact('data'));
    }

    public function create()
    {
        return view('backend.layouts.user.create');
    }

    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();

        try {
            // Handle avatar upload - stored in public/uploads/avatar/ (no symlink needed)
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatar     = $request->file('avatar');
                $avatarName = time() . '_avatar_' . uniqid() . '.' . strtolower($avatar->getClientOriginalExtension() ?: 'png');
                $dir        = public_path('uploads/avatar');
                if (!file_exists($dir)) mkdir($dir, 0775, true);
                $avatar->move($dir, $avatarName);
                $avatarPath = 'uploads/avatar/' . $avatarName;
            }

            // Determine subscription_tier
            $subscriptionTier = $request->input('subscription_tier', 'tier_1');

            // Create user
            $this->userRepository->create([
                'name' => $request->name,
                'email' => $request->email,
                'email_verified_at' => now(),
                'password' => $request->password,
                'dob' => $request->dob ?? null,
                'phone' => $request->phone ?? null,
                'role' => $request->role,
                'is_subscribed' => $subscriptionTier !== 'tier_1',
                'subscription_tier' => $subscriptionTier,
                'avatar' => $avatarPath,
            ]);

            DB::commit();

            return redirect()->route('user.index')->with('t-success', 'User created successfully');
        } catch (\Throwable $exception) {
            DB::rollBack(); // rollback on any error

            return redirect()->route('user.index')->with('t-error', 'User creation failed: '.$exception->getMessage());
        }
    }

    public function edit(int $id): View
    {
        $data = $this->userRepository->findOrFail((int) $id);

        return view('backend.layouts.user.edit', compact('data'));
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        try {
            // Find the user
            $user = $this->userRepository->findOrFail((int) $id);
            $avatarPath = $user->avatar;

            if ($request->hasFile('avatar')) {
                // Delete old avatar from public/
                if ($avatarPath) {
                    $oldFull = public_path(ltrim($avatarPath, '/'));
                    if (file_exists($oldFull)) @unlink($oldFull);
                }

                $avatar     = $request->file('avatar');
                $avatarName = time() . '_avatar_' . uniqid() . '.' . strtolower($avatar->getClientOriginalExtension() ?: 'png');
                $dir        = public_path('uploads/avatar');
                if (!file_exists($dir)) mkdir($dir, 0775, true);
                $avatar->move($dir, $avatarName);
                $avatarPath = 'uploads/avatar/' . $avatarName;
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->dob = $request->dob ?? $user->dob;
            $user->role = $request->role;

            // Determine subscription_tier
            $subscriptionTier = $request->input('subscription_tier', 'tier_1');
            $user->is_subscribed = $subscriptionTier !== 'tier_1';
            $user->subscription_tier = $subscriptionTier;

            if ($request->hasFile('avatar')) {
                $user->avatar = $avatarPath;
            }
            $user->update();

            return redirect()->route('user.index')->with('t-success', 'User info Updated Successfully.');
        } catch (\Throwable $exception) {
            return redirect()->route('user.index')->with('t-error', 'User info failed to update');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = $this->userRepository->findOrFail((int) $id);
        $user->delete();

        return response()->json([
            't-success' => true,
            'message' => 'Deleted successfully.',
        ]);
    }
}
