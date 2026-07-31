<?php

namespace App\Http\Controllers;

use App\Models\EventSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'period' => ['required', 'string', 'max:80'],
            'event_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:120'],
        ]);

        EventSetting::query()->update(['is_active' => false]);
        EventSetting::updateOrCreate(['id' => 1], [...$data, 'is_active' => true]);

        return back()->with('success', 'Agenda aktif berhasil diperbarui.');
    }
}
