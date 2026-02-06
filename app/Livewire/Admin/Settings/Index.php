<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Settings')]
class Index extends Component
{
    public string $siteName = '';

    public string $siteDescription = '';

    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $metaKeywords = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read settings'), 403);
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $keys = ['site_name', 'site_description', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords'];
        foreach ($keys as $key) {
            $s = Setting::where('key', $key)->first();
            $var = match ($key) {
                'site_name' => 'siteName',
                'site_description' => 'siteDescription',
                'seo_meta_title' => 'metaTitle',
                'seo_meta_description' => 'metaDescription',
                'seo_meta_keywords' => 'metaKeywords',
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
        $data = [
            'site_name' => $this->siteName,
            'site_description' => $this->siteDescription,
            'seo_meta_title' => $this->metaTitle,
            'seo_meta_description' => $this->metaDescription,
            'seo_meta_keywords' => $this->metaKeywords,
        ];
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'string', 'group' => $key === 'site_name' || $key === 'site_description' ? 'general' : 'seo']
            );
        }
        session()->flash('status', __('Settings saved successfully.'));
    }

    public function render(): View
    {
        return view('livewire.admin.settings.index');
    }
}
