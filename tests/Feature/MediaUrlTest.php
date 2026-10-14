<?php

namespace Tests\Feature;

use Tests\TestCase;

class MediaUrlTest extends TestCase
{
    public function test_uploads_url_serves_existing_file()
    {
        $response = $this->get('/uploads/home-modules/icon_advanced_insights.svg');
        $response->assertStatus(200);
    }

    public function test_storage_uploads_url_serves_existing_file()
    {
        $response = $this->get('/storage/uploads/home-modules/icon_advanced_insights.svg');
        $response->assertStatus(200);
    }

    public function test_non_existent_file_returns_404()
    {
        $response = $this->get('/storage/uploads/non_existent_image_12345.png');
        $response->assertStatus(404);
    }
}
