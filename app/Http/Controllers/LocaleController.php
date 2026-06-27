<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, ['en', 'de'])) {
            abort(400);
        }

        session(['locale' => $locale]);

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        return back();
    }
}
