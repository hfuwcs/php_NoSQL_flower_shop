<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        if (! in_array($locale, ['en', 'vi'])) {
            abort(400);
        }

        App::setLocale($locale);
        session()->put('locale', $locale);

        return back();
    }
}
