<?php

namespace Tests\Unit;

use App\Services\FirebaseCredentialResolver;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FirebaseCredentialResolverTest extends TestCase
{
    protected string $dashboardPath;
    protected string $privatePath;
    protected string $testTempPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardPath = storage_path('app/firebase-credentials-dashboard.json');
        $this->privatePath = storage_path('app/private/firebase-credentials.json');
        $this->testTempPath = storage_path('app/test-firebase-credentials.json');

        if (File::exists($this->dashboardPath)) { File::delete($this->dashboardPath); }
        if (File::exists($this->privatePath)) { File::delete($this->privatePath); }
        if (File::exists($this->testTempPath)) {
            File::delete($this->testTempPath);
        }
        $_ENV['FIREBASE_CREDENTIALS'] = '';
        putenv('FIREBASE_CREDENTIALS=');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->dashboardPath)) { File::delete($this->dashboardPath); }
        if (File::exists($this->privatePath)) { File::delete($this->privatePath); }
        if (File::exists($this->testTempPath)) {
            File::delete($this->testTempPath);
        }
        $_ENV['FIREBASE_CREDENTIALS'] = '';
        putenv('FIREBASE_CREDENTIALS=');
        parent::tearDown();
    }

    public function test_resolves_null_when_no_credentials_exist(): void
    {
        $_ENV['FIREBASE_CREDENTIALS'] = '';
        putenv('FIREBASE_CREDENTIALS=');
        $resolved = FirebaseCredentialResolver::resolve();
        $this->assertNull($resolved);
    }

    public function test_resolves_dashboard_file_when_present(): void
    {
        $dummy = json_encode([
            'type' => 'service_account',
            'project_id' => 'test-project',
            'private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC3\n-----END PRIVATE KEY-----\n",
            'client_email' => 'test@test-project.iam.gserviceaccount.com',
        ]);
        File::put($this->dashboardPath, $dummy);

        $resolved = FirebaseCredentialResolver::resolve();
        $this->assertEquals($this->dashboardPath, $resolved);
    }

    public function test_resolves_direct_path_from_environment(): void
    {
        $dummy = json_encode([
            'type' => 'service_account',
            'project_id' => 'env-project',
            'private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC3\n-----END PRIVATE KEY-----\n",
            'client_email' => 'env@env-project.iam.gserviceaccount.com',
        ]);
        File::put($this->testTempPath, $dummy);
        $_ENV['FIREBASE_CREDENTIALS'] = $this->testTempPath;
        putenv('FIREBASE_CREDENTIALS=' . $this->testTempPath);

        $resolved = FirebaseCredentialResolver::resolve();
        $this->assertEquals($this->testTempPath, $resolved);
    }

    public function test_resolves_raw_json_from_environment(): void
    {
        $dummy = json_encode([
            'type' => 'service_account',
            'project_id' => 'raw-json-project',
            'private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC3\n-----END PRIVATE KEY-----\n",
            'client_email' => 'raw@raw-project.iam.gserviceaccount.com',
        ]);
        $_ENV['FIREBASE_CREDENTIALS'] = $dummy;
        putenv('FIREBASE_CREDENTIALS=' . $dummy);

        $resolved = FirebaseCredentialResolver::resolve();
        $this->assertEquals($this->privatePath, $resolved);
        $this->assertFileExists($this->privatePath);
        $this->assertStringContainsString('raw-json-project', File::get($this->privatePath));
    }

    public function test_resolves_base64_encoded_json_from_environment(): void
    {
        $dummy = json_encode([
            'type' => 'service_account',
            'project_id' => 'base64-project',
            'private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC3\n-----END PRIVATE KEY-----\n",
            'client_email' => 'b64@base64-project.iam.gserviceaccount.com',
        ]);
        $_ENV['FIREBASE_CREDENTIALS'] = base64_encode($dummy);
        putenv('FIREBASE_CREDENTIALS=' . base64_encode($dummy));

        $resolved = FirebaseCredentialResolver::resolve();
        $this->assertEquals($this->privatePath, $resolved);
        $this->assertFileExists($this->privatePath);
        $this->assertStringContainsString('base64-project', File::get($this->privatePath));
    }
}
