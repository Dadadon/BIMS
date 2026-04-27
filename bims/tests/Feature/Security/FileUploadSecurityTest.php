<?php

use App\Models\HR\Company;
use App\Models\HR\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    Storage::fake('public');
});

// Validate avatar rules directly via the Laravel Validator (no GD, no DB lookup dependencies).

test('image validation rule rejects text/plain file regardless of jpg extension', function () {
    // Create a temp file with PHP content
    $tmpPath = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tmpPath, '<?php system($_GET["cmd"]); ?>');

    $file = new UploadedFile(
        $tmpPath,
        'shell.jpg',
        'text/x-php', // explicitly set the real MIME
        null,
        true           // test=true so isValid() works
    );

    $validator = Validator::make(
        ['avatar' => $file],
        ['avatar' => ['nullable', 'image', 'max:2048']]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('avatar'))->toBeTrue();
    @unlink($tmpPath);
});

test('image validation rule rejects application/octet-stream file', function () {
    $tmpPath = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tmpPath, "\x4d\x5a\x90\x00\x03\x00"); // MZ header (PE executable)

    $file = new UploadedFile(
        $tmpPath,
        'virus.png',
        'application/octet-stream',
        null,
        true
    );

    $validator = Validator::make(
        ['avatar' => $file],
        ['avatar' => ['nullable', 'image', 'max:2048']]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('avatar'))->toBeTrue();
    @unlink($tmpPath);
});

test('image validation rule accepts a valid jpeg MIME type', function () {
    // A minimal valid JPEG file starts with the FF D8 FF magic bytes
    $tmpPath = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tmpPath, "\xff\xd8\xff\xe0" . str_repeat("\x00", 16));

    $file = new UploadedFile(
        $tmpPath,
        'photo.jpg',
        'image/jpeg',
        null,
        true
    );

    $validator = Validator::make(
        ['avatar' => $file],
        ['avatar' => ['nullable', 'image', 'max:2048']]
    );

    // The JPEG magic bytes should pass the image rule
    expect($validator->passes())->toBeTrue();
    @unlink($tmpPath);
});

test('employee update endpoint requires authentication', function () {
    $employee = Employee::factory()->create();

    $this->withoutMiddleware(\App\Http\Middleware\CheckModuleEnabled::class)
         ->put(route('admin.employees.update', $employee), [])
         ->assertRedirect(route('login'));
});
