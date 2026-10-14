<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Settings\UpdatePasswordRequest;
use App\Http\Requests\Web\Backend\Settings\UpdateProfilePictureRequest;
use App\Http\Requests\Web\Backend\Settings\UpdateProfileRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile settings page.
     *
     * @return View
     */
    public function index(Request $request)
    {
        $user = User::find($request->id);

        return view('backend.layouts.settings.profile_settings', compact('user'));
    }

    /**
     * Update the user's profile.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function UpdateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = User::find(auth()->user()->id);
            $user->name = $request->name;
            $user->email = $request->email;

            $user->save();

            return redirect()->back()->with('t-success', 'Profile updated successfully');
        } catch (Exception) {
            return redirect()->back()->with('t-error', 'Something went wrong');
        }
    }

    /**
     * Update the user's password.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function UpdatePassword(UpdatePasswordRequest $request)
    {
        try {
            $user = Auth::user();
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->password);
                $user->save();

                return redirect()->back()->with('t-success', 'Password updated successfully');
            } else {
                return redirect()->back()->with('t-error', 'Current password is incorrect');
            }
        } catch (Exception) {
            return redirect()->back()->with('t-error', 'Something went wrong');
        }
    }

    /**
     * Update the user's profile picture.
     *
     * @param  Request  $request
     * @return JsonResponse|RedirectResponse
     */
    public function UpdateProfilePicture(UpdateProfilePictureRequest $request)
    {

        try {
            $user = Auth::user();

            // Handle avatar upload - stored directly in public/uploads/avatar/ (no symlink needed)
            if ($request->hasFile('profile_picture')) {
                // Delete old avatar
                if ($user->avatar) {
                    $oldPath = ltrim($user->avatar, '/');
                    if (str_starts_with($oldPath, 'storage/')) {
                        $oldPath = substr($oldPath, strlen('storage/'));
                    }
                    $oldFull = public_path($oldPath);
                    if (file_exists($oldFull)) @unlink($oldFull);
                }

                $avatar     = $request->file('profile_picture');
                $ext        = strtolower($avatar->getClientOriginalExtension() ?: 'png');
                $avatarName = time() . '_avatar_' . uniqid() . '.' . $ext;
                $dir        = public_path('uploads/avatar');
                if (!file_exists($dir)) mkdir($dir, 0775, true);
                $avatar->move($dir, $avatarName);
                $relativePath = 'uploads/avatar/' . $avatarName;

                // Update user's avatar (relative path; accessor builds full URL)
                $user->avatar = $relativePath;
                $user->save();

                return response()->json([
                    'success'   => true,
                    'image_url' => asset($relativePath),
                ]);
            } else {
                throw new Exception('No image was uploaded.');
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
