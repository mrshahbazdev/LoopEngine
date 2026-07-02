<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function settings(Request $request)
    {
        $company = $request->user()->company;
        return view('company.settings', compact('company'));
    }

    public function update(Request $request)
    {
        $company = $request->user()->company;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $company->update($validated);

        return back()->with('success', __('app.company_updated'));
    }
}
