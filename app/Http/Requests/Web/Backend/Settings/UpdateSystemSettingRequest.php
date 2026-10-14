<?php

namespace App\Http\Requests\Web\Backend\Settings;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for a system setting.
 */
class UpdateSystemSettingRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'number' => 'nullable|string|max:255',
            'system_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'copyright_text' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'description' => 'nullable|string',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'whatsapp_url' => 'nullable|url|max:255',
            'telegram_url' => 'nullable|url|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'The Title must not exceed 255 characters.',
            'email.email' => 'Please enter a valid email address for Email.',
            'email.max' => 'The Email must not exceed 255 characters.',
            'number.max' => 'The Number must not exceed 255 characters.',
            'system_name.max' => 'The System Name must not exceed 255 characters.',
            'address.max' => 'The Address must not exceed 255 characters.',
            'copyright_text.max' => 'The Copyright Text must not exceed 255 characters.',
            'logo.image' => 'The Logo must be an image file.',
            'logo.mimes' => 'The Logo must be a file of type: jpeg, png, jpg, gif, svg.',
            'logo.max' => 'The Logo must not exceed 2048 characters.',
            'favicon.image' => 'The Favicon must be an image file.',
            'favicon.mimes' => 'The Favicon must be a file of type: jpeg, png, jpg, gif, svg.',
            'favicon.max' => 'The Favicon must not exceed 1024 characters.',
            'facebook_url.url' => 'Please enter a valid URL for Facebook Url.',
            'facebook_url.max' => 'The Facebook Url must not exceed 255 characters.',
            'twitter_url.url' => 'Please enter a valid URL for Twitter Url.',
            'twitter_url.max' => 'The Twitter Url must not exceed 255 characters.',
            'instagram_url.url' => 'Please enter a valid URL for Instagram Url.',
            'instagram_url.max' => 'The Instagram Url must not exceed 255 characters.',
            'linkedin_url.url' => 'Please enter a valid URL for Linkedin Url.',
            'linkedin_url.max' => 'The Linkedin Url must not exceed 255 characters.',
            'youtube_url.url' => 'Please enter a valid URL for Youtube Url.',
            'youtube_url.max' => 'The Youtube Url must not exceed 255 characters.',
            'whatsapp_url.url' => 'Please enter a valid URL for Whatsapp Url.',
            'whatsapp_url.max' => 'The Whatsapp Url must not exceed 255 characters.',
            'telegram_url.url' => 'Please enter a valid URL for Telegram Url.',
            'telegram_url.max' => 'The Telegram Url must not exceed 255 characters.',
        ];
    }

}
