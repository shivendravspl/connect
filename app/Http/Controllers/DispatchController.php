<?php
namespace App\Http\Controllers;

use App\Models\Onboarding;
use App\Models\PhysicalDispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DispatchController extends Controller
{
    public function show($id)
    {
        $application = Onboarding::findOrFail($id);
        // if ($application->status !== 'initiated') {
        //     return redirect()->route('applications.index')->with('error', 'Dispatch details can only be filled for initiated applications.');
        // }
        if ($application->created_by !== Auth::user()->emp_id) {
            return redirect()->route('applications.index')->with('error', 'Unauthorized to fill dispatch details.');
        }
        $dispatch = $application->physicalDispatch ?? new PhysicalDispatch();

        return view('applications.dispatch.show', compact('application', 'dispatch'));
    }

    public function store(Request $request, $id)
    {
        try {
            $application = Onboarding::findOrFail($id);
            // if ($application->status !== 'initiated') {
            //     return redirect()->route('applications.index')->with('error', 'Dispatch details can only be filled for initiated applications.');
            // }
            if ($application->created_by !== Auth::user()->emp_id && !Auth::user()->hasAnyRole(['Admin', 'Super Admin', 'Mis Admin'])) {
                return redirect()->route('applications.index')->with('error', 'Unauthorized to fill dispatch details.');
            }

            // Log request data for debugging
            Log::info('Dispatch form submission data: ', $request->all());

            $validated = $request->validate([
                'mode' => 'required|in:transport,courier,by_hand',
                'dispatch_date' => 'required|date|before_or_equal:today',
                'transport_name' => 'nullable|required_if:mode,transport|string|max:255',
                'driver_name' => 'nullable|required_if:mode,transport|string|max:255',
                'driver_contact' => 'nullable|required_if:mode,transport|string|max:255',
                'docket_number' => 'nullable|required_if:mode,courier|string|max:255',
                'courier_company_name' => 'nullable|required_if:mode,courier|string|max:255',
                'person_name' => 'nullable|required_if:mode,by_hand|string|max:255',
                'person_contact' => 'nullable|required_if:mode,by_hand|string|max:255',
            ]);
            
            $dispatch = $application->physicalDispatch ?? new PhysicalDispatch();
            $dispatch->application_id = $application->id;
            $dispatch->fill($validated);
            $dispatch->created_by = Auth::user()->emp_id;
            $dispatch->save();

            return redirect()->route('applications.index')->with('success', 'Dispatch details submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving dispatch details: ' . $e->getMessage(), ['request' => $request->all()]);
            return redirect()->back()->withInput()->with('error', 'Failed to save dispatch details: ' . $e->getMessage());
        }
    }
}