<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Settings')]
class Index extends Component
{
    use WithFileUploads;

    public string $siteName = '';

    public string $siteDescription = '';

    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $metaKeywords = '';

    public string $siteEmail = '';

    public string $sitePhone = '';

    public $logo = null;

    public $favicon = null;

    public ?string $currentLogoPath = null;

    public ?string $currentFaviconPath = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read settings'), 403);
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $keys = [
            'site_name',
            'site_description',
            'seo_meta_title',
            'seo_meta_description',
            'seo_meta_keywords',
            'site_email',
            'site_phone',
            'site_logo',
            'site_favicon',
        ];
        foreach ($keys as $key) {
            $s = Setting::where('key', $key)->first();
            $var = match ($key) {
                'site_name' => 'siteName',
                'site_description' => 'siteDescription',
                'seo_meta_title' => 'metaTitle',
                'seo_meta_description' => 'metaDescription',
                'seo_meta_keywords' => 'metaKeywords',
                'site_email' => 'siteEmail',
                'site_phone' => 'sitePhone',
                'site_logo' => 'currentLogoPath',
                'site_favicon' => 'currentFaviconPath',
                default => null,
            };
            if ($var && $s) {
                $this->{$var} = $s->value ?? '';
            }
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('update settings'), 403);

        $validated = $this->validate([
            'siteEmail' => ['nullable', 'email', 'max:255'],
            'sitePhone' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:1024'],
        ]);

        $logoPath = $this->currentLogoPath;
        if ($this->logo) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $this->logo->store('settings/logo/'.date('Y/m'), 'public');
        }

        $faviconPath = $this->currentFaviconPath;
        if ($this->favicon) {
            if ($faviconPath) {
                Storage::disk('public')->delete($faviconPath);
            }
            $faviconPath = $this->favicon->store('settings/favicon/'.date('Y/m'), 'public');
        }

        $data = [
            'site_name' => $this->siteName,
            'site_description' => $this->siteDescription,
            'seo_meta_title' => $this->metaTitle,
            'seo_meta_description' => $this->metaDescription,
            'seo_meta_keywords' => $this->metaKeywords,
            'site_email' => $validated['siteEmail'] ?? '',
            'site_phone' => $validated['sitePhone'] ?? '',
            'site_logo' => $logoPath ?? '',
            'site_favicon' => $faviconPath ?? '',
        ];
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => in_array($key, ['site_logo', 'site_favicon'], true) ? 'image' : 'string',
                    'group' => match (true) {
                        in_array($key, ['site_name', 'site_description', 'site_logo', 'site_favicon'], true) => 'general',
                        in_array($key, ['site_email', 'site_phone'], true) => 'contact',
                        default => 'seo',
                    },
                ]
            );
        }

        $this->currentLogoPath = $logoPath;
        $this->currentFaviconPath = $faviconPath;
        $this->logo = null;
        $this->favicon = null;

        session()->flash('status', __('Settings saved successfully.'));
    }

    public function render(): View
    {
        return view('livewire.admin.settings.index');
    }
}
