<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for the system settings.
 */
class SystemSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'email'          => $this->email,
            'number'         => $this->number,
            'system_name'    => $this->system_name,
            'address'        => $this->address,
            'copyright_text' => $this->copyright_text,
            'logo'           => $this->logo,
            'favicon'        => $this->favicon,
            'description'    => $this->description,
            'facebook_url'   => $this->facebook_url,
            'twitter_url'    => $this->twitter_url,
            'instagram_url'  => $this->instagram_url,
            'linkedin_url'   => $this->linkedin_url,
            'youtube_url'    => $this->youtube_url,
            'whatsapp_url'   => $this->whatsapp_url,
            'telegram_url'   => $this->telegram_url,
        ];
    }
}
