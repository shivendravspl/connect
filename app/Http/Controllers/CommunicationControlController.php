<?php

namespace App\Http\Controllers;

use App\Models\CommunicationControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationControlController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Super Admin|Admin']);
    }

    public function index()
    {
        $controls = CommunicationControl::all();
        return view('communication_controls.index', compact('controls'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100|unique:communication_controls,key',
            'description' => 'nullable|string|max:255',
        ]);

        CommunicationControl::create([
            'key' => $validated['key'],
            'description' => $validated['description'],
            'is_active' => true,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('communication.index')->with('success', 'Communication control created successfully.');
    }

    public function toggle(Request $request, $communicationControl)
    {
        $communicationControl = CommunicationControl::findOrFail($communicationControl);

        $newState = !$communicationControl->is_active;
        $communicationControl->update([
            'is_active' => $newState,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        $message = $newState
            ? 'Communication control activated successfully.'
            : 'Communication control deactivated successfully.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $newState,
            ]);
        }

        return redirect()->route('communication.index')->with('success', $message);
    }
}