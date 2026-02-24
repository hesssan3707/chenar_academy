<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_access_user_panel_routes(): void
    {
        $this->get('/panel')->assertRedirect(route('login'));
    }

    public function test_guest_can_view_admin_login_page(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('name="otp-send-url" content="'.route('admin.otp.send').'"', false);
    }

    public function test_admin_can_login_from_admin_login_page(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->post(route('admin.login.store'), [
            'action' => 'login_password',
            'phone' => $user->phone,
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user, 'admin');
        $this->assertGuest('web');
    }

    public function test_admin_can_login_with_otp_from_admin_login_page(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->postJson(route('admin.otp.send'), [
            'purpose' => 'admin_login',
            'phone' => $user->phone,
        ])->assertOk();

        $this->post(route('admin.login.store'), [
            'action' => 'login_otp',
            'phone' => $user->phone,
            'otp_code' => '11111',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user, 'admin');
        $this->assertGuest('web');
    }

    public function test_regular_user_cannot_login_from_admin_login_page(): void
    {
        $user = User::factory()->create();

        $this->post(route('admin.login.store'), [
            'action' => 'login_password',
            'phone' => $user->phone,
            'password' => 'password123',
        ])->assertSessionHasErrors(['phone']);
    }

    public function test_guest_is_told_login_is_required_for_protected_routes(): void
    {
        $this->get('/panel')
            ->assertRedirect(route('login'))
            ->assertSessionHas('toast');
    }

    public function test_regular_user_can_access_user_panel_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/panel')->assertRedirect(route('panel.library.index'));
        $this->actingAs($user)->get(route('panel.library.index'))->assertOk();
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->actingAs($user, 'admin')->get('/admin')->assertOk();
    }

    public function test_admin_sidebar_uses_media_label_and_supports_mobile_menu_toggle(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->actingAs($user, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="admin-nav-toggle"', false)
            ->assertSee('class="admin-topbar__menu-btn"', false)
            ->assertSee('href="'.route('admin.media.index').'"', false)
            ->assertSee('>رسانه<', false);
    }

    public function test_admin_can_update_site_theme_to_new_themes(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default', 'light', 'midnight', 'sand']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'midnight',
            ])->assertRedirect(route('admin.settings.index'));

        $setting = Setting::query()->where('key', 'theme.active')->first();
        $this->assertNotNull($setting);
        $this->assertSame('theme', $setting->group);
        $this->assertSame('midnight', $setting->value);

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'sand',
            ])->assertRedirect(route('admin.settings.index'));

        $setting->refresh();
        $this->assertSame('sand', $setting->value);
    }

    public function test_admin_can_update_about_page_content(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'about_title' => 'درباره ما',
                'about_subtitle' => 'معرفی کوتاه',
                'about_body' => 'متن درباره ما',
            ])->assertRedirect(route('admin.settings.index'));

        $setting = Setting::query()->where('key', 'page.about')->first();
        $this->assertNotNull($setting);
        $this->assertSame('pages', $setting->group);
        $this->assertIsArray($setting->value);
        $this->assertSame('درباره ما', $setting->value['title']);
        $this->assertSame('معرفی کوتاه', $setting->value['subtitle']);
        $this->assertSame('متن درباره ما', $setting->value['body']);
    }

    public function test_admin_can_update_tax_percent_setting(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'tax_percent' => 9,
            ])->assertRedirect(route('admin.settings.index'));

        $setting = Setting::query()->where('key', 'commerce.tax_percent')->first();
        $this->assertNotNull($setting);
        $this->assertSame('commerce', $setting->group);
        $this->assertSame(9, $setting->value);
    }

    public function test_admin_can_update_card_to_card_cards_settings(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'card_to_card_card1_name' => 'چنار آکادمی',
                'card_to_card_card1_number' => '۶۰۳۷-۹۹۱۸-۱۲۳۴-۵۶۷۸',
                'card_to_card_card2_name' => 'حساب پشتیبان',
                'card_to_card_card2_number' => '6037 9918 1111 2222',
            ])->assertRedirect(route('admin.settings.index'));

        $card1Name = Setting::query()->where('key', 'commerce.card_to_card.card1.name')->first();
        $this->assertNotNull($card1Name);
        $this->assertSame('commerce', $card1Name->group);
        $this->assertSame('چنار آکادمی', $card1Name->value);

        $card1Number = Setting::query()->where('key', 'commerce.card_to_card.card1.number')->first();
        $this->assertNotNull($card1Number);
        $this->assertSame('commerce', $card1Number->group);
        $this->assertSame('6037991812345678', $card1Number->value);

        $card2Name = Setting::query()->where('key', 'commerce.card_to_card.card2.name')->first();
        $this->assertNotNull($card2Name);
        $this->assertSame('commerce', $card2Name->group);
        $this->assertSame('حساب پشتیبان', $card2Name->value);

        $card2Number = Setting::query()->where('key', 'commerce.card_to_card.card2.number')->first();
        $this->assertNotNull($card2Number);
        $this->assertSame('commerce', $card2Number->group);
        $this->assertSame('6037991811112222', $card2Number->value);
    }

    public function test_admin_can_update_social_urls_in_settings_and_contact_page_uses_them(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'social_instagram_url' => 'instagram.com/chenar_academy',
                'social_telegram_url' => 't.me/chenar_academy',
                'social_youtube_url' => 'youtube.com/@chenaracademy',
            ])->assertRedirect(route('admin.settings.index'));

        $instagram = Setting::query()->where('key', 'social.instagram.url')->first();
        $this->assertNotNull($instagram);
        $this->assertSame('social', $instagram->group);
        $this->assertSame('https://instagram.com/chenar_academy', $instagram->value);

        $telegram = Setting::query()->where('key', 'social.telegram.url')->first();
        $this->assertNotNull($telegram);
        $this->assertSame('social', $telegram->group);
        $this->assertSame('https://t.me/chenar_academy', $telegram->value);

        $youtube = Setting::query()->where('key', 'social.youtube.url')->first();
        $this->assertNotNull($youtube);
        $this->assertSame('social', $youtube->group);
        $this->assertSame('https://youtube.com/@chenaracademy', $youtube->value);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('https://instagram.com/chenar_academy', false)
            ->assertSee('https://t.me/chenar_academy', false)
            ->assertSee('https://youtube.com/@chenaracademy', false);
    }

    public function test_admin_can_update_spa_background_settings_and_spa_layout_receives_them(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $home = Media::query()->create([
            'uploaded_by_user_id' => $user->id,
            'disk' => 'public',
            'path' => 'media/home-bg.png',
            'original_name' => 'home-bg.png',
            'mime_type' => 'image/png',
            'size' => 100,
            'sha1' => null,
            'width' => 10,
            'height' => 10,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $videos = Media::query()->create([
            'uploaded_by_user_id' => $user->id,
            'disk' => 'public',
            'path' => 'media/videos-bg.png',
            'original_name' => 'videos-bg.png',
            'mime_type' => 'image/png',
            'size' => 100,
            'sha1' => null,
            'width' => 10,
            'height' => 10,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $booklets = Media::query()->create([
            'uploaded_by_user_id' => $user->id,
            'disk' => 'public',
            'path' => 'media/booklets-bg.png',
            'original_name' => 'booklets-bg.png',
            'mime_type' => 'image/png',
            'size' => 100,
            'sha1' => null,
            'width' => 10,
            'height' => 10,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $other = Media::query()->create([
            'uploaded_by_user_id' => $user->id,
            'disk' => 'public',
            'path' => 'media/other-bg.png',
            'original_name' => 'other-bg.png',
            'mime_type' => 'image/png',
            'size' => 100,
            'sha1' => null,
            'width' => 10,
            'height' => 10,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'background_home_media_id' => $home->id,
                'background_videos_media_id' => $videos->id,
                'background_booklets_media_id' => $booklets->id,
                'background_other_media_id' => $other->id,
            ])->assertRedirect(route('admin.settings.index'));

        $setting = Setting::query()->where('key', 'ui.backgrounds')->first();
        $this->assertNotNull($setting);
        $this->assertSame('ui', $setting->group);
        $this->assertIsArray($setting->value);
        $this->assertSame($home->id, $setting->value['home_media_id']);
        $this->assertSame($videos->id, $setting->value['videos_media_id']);
        $this->assertSame($booklets->id, $setting->value['booklets_media_id']);
        $this->assertSame($other->id, $setting->value['other_media_id']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-bg-home="'.route('media.stream', $home->id).'"', false)
            ->assertSee('data-bg-videos="'.route('media.stream', $videos->id).'"', false)
            ->assertSee('data-bg-booklets="'.route('media.stream', $booklets->id).'"', false)
            ->assertSee('data-bg-other="'.route('media.stream', $other->id).'"', false)
            ->assertSee('data-bg-group="home"', false);
    }

    public function test_spa_backgrounds_use_default_when_group_background_is_not_set(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $default = Media::query()->create([
            'uploaded_by_user_id' => $user->id,
            'disk' => 'public',
            'path' => 'media/default-bg.png',
            'original_name' => 'default-bg.png',
            'mime_type' => 'image/png',
            'size' => 100,
            'sha1' => null,
            'width' => 10,
            'height' => 10,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'background_default_media_id' => $default->id,
                'background_home_media_id' => null,
                'background_videos_media_id' => null,
                'background_booklets_media_id' => null,
                'background_other_media_id' => null,
            ])->assertRedirect(route('admin.settings.index'));

        $setting = Setting::query()->where('key', 'ui.backgrounds')->first();
        $this->assertNotNull($setting);
        $this->assertIsArray($setting->value);
        $this->assertSame($default->id, $setting->value['default_media_id']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-bg-home="'.route('media.stream', $default->id).'"', false)
            ->assertSee('data-bg-videos="'.route('media.stream', $default->id).'"', false)
            ->assertSee('data-bg-booklets="'.route('media.stream', $default->id).'"', false)
            ->assertSee('data-bg-other="'.route('media.stream', $default->id).'"', false);
    }

    public function test_admin_can_upload_default_background_image_in_settings(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $file = UploadedFile::fake()->image('settings-default-bg.jpg', 1200, 675);

        $this->actingAs($user, 'admin')
            ->post(route('admin.settings.update'), [
                '_method' => 'put',
                'theme' => 'default',
                'background_default_file' => $file,
            ])->assertRedirect(route('admin.settings.index'));

        $mediaId = (int) Media::query()->where('original_name', 'settings-default-bg.jpg')->value('id');
        $this->assertGreaterThan(0, $mediaId);

        $media = Media::query()->findOrFail($mediaId);
        $this->assertSame('public', $media->disk);
        $this->assertSame('settings-default-bg.jpg', $media->original_name);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertTrue(Storage::disk('public')->exists($media->path));

        $setting = Setting::query()->where('key', 'ui.backgrounds')->first();
        $this->assertNotNull($setting);
        $this->assertIsArray($setting->value);
        $this->assertSame($mediaId, $setting->value['default_media_id']);
    }

    public function test_admin_can_set_logo_in_settings_and_spa_loader_uses_it(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $logo = Media::query()->create([
            'uploaded_by_user_id' => $user->id,
            'disk' => 'public',
            'path' => 'media/logo.png',
            'original_name' => 'logo.png',
            'mime_type' => 'image/png',
            'size' => 100,
            'sha1' => null,
            'width' => 10,
            'height' => 10,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'logo_media_id' => $logo->id,
            ])->assertRedirect(route('admin.settings.index'));

        $setting = Setting::query()->where('key', 'ui.logo_media_id')->first();
        $this->assertNotNull($setting);
        $this->assertSame('ui', $setting->group);
        $this->assertSame($logo->id, $setting->value);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="site-loader__logo"', false)
            ->assertSee('src="'.route('media.stream', $logo->id).'"', false);
    }

    public function test_admin_can_upload_logo_in_settings(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $file = UploadedFile::fake()->image('site-logo.jpg', 600, 200);

        $this->actingAs($user, 'admin')
            ->post(route('admin.settings.update'), [
                '_method' => 'put',
                'theme' => 'default',
                'logo_file' => $file,
            ])->assertRedirect(route('admin.settings.index'));

        $mediaId = (int) Media::query()->where('original_name', 'site-logo.jpg')->value('id');
        $this->assertGreaterThan(0, $mediaId);

        $media = Media::query()->findOrFail($mediaId);
        $this->assertSame('public', $media->disk);
        $this->assertSame('site-logo.jpg', $media->original_name);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertTrue(Storage::disk('public')->exists($media->path));

        $setting = Setting::query()->where('key', 'ui.logo_media_id')->first();
        $this->assertNotNull($setting);
        $this->assertSame($mediaId, $setting->value);
    }

    public function test_admin_cannot_set_spa_background_to_non_image_media(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $notImage = Media::query()->create([
            'uploaded_by_user_id' => $user->id,
            'disk' => 'public',
            'path' => 'media/file.mp4',
            'original_name' => 'file.mp4',
            'mime_type' => 'video/mp4',
            'size' => 100,
            'sha1' => null,
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $this->actingAs($user, 'admin')
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'background_home_media_id' => $notImage->id,
            ])->assertSessionHasErrors(['background_home_media_id']);
    }

    public function test_admin_can_access_user_panel_routes(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->actingAs($user)->get('/panel')->assertRedirect(route('panel.library.index'));
        $this->actingAs($user)->get(route('panel.library.index'))->assertOk();
    }

    public function test_admin_panel_authentication_does_not_authenticate_user_panel(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->actingAs($user, 'admin')->get('/panel')->assertRedirect(route('login'));
    }

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'oldpass123',
        ]);

        $this->actingAs($user)
            ->put(route('panel.profile.password.update'), [
                'current_password' => 'oldpass123',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])->assertRedirect();

        $this->assertTrue(Hash::check('newpass123', $user->refresh()->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'oldpass123',
        ]);

        $this->actingAs($user)
            ->put(route('panel.profile.password.update'), [
                'current_password' => 'wrongpass',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])->assertSessionHasErrors(['current_password']);
    }

    public function test_admin_permissions_are_not_enforced_until_any_role_permission_exists(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        Permission::query()->create([
            'name' => 'admin.settings',
            'description' => null,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    public function test_admin_cannot_access_route_when_permissions_enabled_but_permission_not_granted(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $admin->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $permission = Permission::query()->create([
            'name' => 'admin.settings',
            'description' => null,
        ]);

        $otherRole = Role::create(['name' => 'editor']);
        $otherRole->permissions()->attach($permission->id);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_route_when_permissions_enabled_and_permission_granted(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $admin->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $permission = Permission::query()->create([
            'name' => 'admin.settings',
            'description' => null,
        ]);

        $adminRole->permissions()->attach($permission->id);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    public function test_super_admin_can_access_routes_even_without_explicit_permissions(): void
    {
        $superAdmin = User::factory()->create();
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $superAdmin->roles()->attach($superAdminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $permission = Permission::query()->create([
            'name' => 'admin.settings',
            'description' => null,
        ]);

        $otherRole = Role::create(['name' => 'editor']);
        $otherRole->permissions()->attach($permission->id);

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    public function test_non_super_admin_cannot_access_roles_page_when_super_admin_exists_and_permissions_enabled(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->roles()->attach(Role::create(['name' => 'super_admin'])->id);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $permission = Permission::query()->create([
            'name' => 'admin.roles',
            'description' => null,
        ]);

        Role::create(['name' => 'editor'])->permissions()->attach($permission->id);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.roles.index'))
            ->assertOk();
    }
}
