<?php

namespace App\Livewire\Admin;

use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function mount(): void
    {
        // Dashboard is for any authenticated user
    }

    public function render(): View
    {
        $stats = [];

        if (auth()->user()?->can('read users')) {
            $stats['users'] = User::count();
        }
        if (auth()->user()?->can('read pages')) {
            $stats['pages'] = Page::count();
            $stats['pages_published'] = Page::where('status', 'published')->count();
        }
        if (auth()->user()?->can('read posts')) {
            $stats['posts'] = Post::count();
            $stats['posts_published'] = Post::where('status', 'published')->count();
        }

        return view('livewire.admin.dashboard', ['stats' => $stats]);
    }
}
