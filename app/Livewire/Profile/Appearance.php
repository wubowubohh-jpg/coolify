<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Appearance extends Component
{
    public string $locale;

    public function mount(): void
    {
        $locale = Auth::user()?->locale ?: app()->getLocale();

        $this->locale = array_key_exists($locale, config('app.supported_locales', []))
            ? $locale
            : config('app.locale');
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        $this->validate([
            'locale' => ['required', Rule::in(array_keys(config('app.supported_locales', [])))],
        ]);

        Auth::user()->update(['locale' => $this->locale]);
        app()->setLocale($this->locale);
        cookie()->queue(cookie(config('app.locale_cookie'), $this->locale, 60 * 24 * 365 * 5));

        $this->redirect(route('profile.appearance'), navigate: true);
    }

    public function render(): mixed
    {
        return view('livewire.profile.appearance');
    }
}
