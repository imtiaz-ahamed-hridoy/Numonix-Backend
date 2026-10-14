<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Settings\UpdateMailSettingRequest;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;

class MailSettingController extends Controller
{
    /**
     * Display mail settings page.
     */
    public function index(): View
    {
        $settings = [
            'mail_mailer' => config('mail.default', ''),
            'mail_host' => config('mail.mailers.smtp.host', ''),
            'mail_port' => config('mail.mailers.smtp.port', ''),
            'mail_username' => config('mail.mailers.smtp.username', ''),
            'mail_password' => config('mail.mailers.smtp.password', ''),
            'mail_encryption' => config('mail.mailers.smtp.encryption', ''),
            'mail_from_address' => config('mail.from.address', ''),
        ];

        return view('backend.layouts.settings.mail_settings', compact('settings'));
    }

    /**
     * Update mail settings.
     *
     * @param  UpdateMailSettingRequest  $request
     */
    public function update(UpdateMailSettingRequest $request): RedirectResponse
    {
        try {
            $envContent = File::get(base_path('.env'));
            $lineBreak = "\n";
            $envContent = preg_replace([
                '/MAIL_MAILER=(.*)\s*/',
                '/MAIL_HOST=(.*)\s*/',
                '/MAIL_PORT=(.*)\s*/',
                '/MAIL_USERNAME=(.*)\s*/',
                '/MAIL_PASSWORD=(.*)\s*/',
                '/MAIL_ENCRYPTION=(.*)\s*/',
                '/MAIL_FROM_ADDRESS=(.*)\s*/',
            ], [
                'MAIL_MAILER='.$request->mail_mailer.$lineBreak,
                'MAIL_HOST='.$request->mail_host.$lineBreak,
                'MAIL_PORT='.$request->mail_port.$lineBreak,
                'MAIL_USERNAME='.$request->mail_username.$lineBreak,
                'MAIL_PASSWORD='.'"'.$request->mail_password.'"'.$lineBreak,
                'MAIL_ENCRYPTION='.$request->mail_encryption.$lineBreak,
                'MAIL_FROM_ADDRESS='.'"'.$request->mail_from_address.'"'.$lineBreak,
            ], $envContent);

            File::put(base_path('.env'), $envContent);

            return back()->with('t-success', 'Updated successfully');
        } catch (Exception) {
            return back()->with('t-error', 'Failed to update');
        }
    }
}
