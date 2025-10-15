<?php

namespace App\Http\Controllers;

use App\Models\PageRequest;
use Illuminate\Http\Request;
use App\Jobs\ProcessPageRequestJob;

class PageRequestController extends Controller
{
    // List all page requests
    public function index()
    {
        $requests =  PageRequest::orderBy('created_at','desc')->get();
        return view('page_requests.index', compact('requests'));
    }

    // Show form to create new request
    public function create()
    {
        return view('page_requests.create');
    }

    /**
     * Handle the submission of a Wikipedia URL and create a page request.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'url' => 'required|url',
        ]);

        // Create page request record
        $pageRequest = PageRequest::create([
            'url' => $request->url,
            'status' => 'pending',
        ]);

        // Dispatch asynchronous job to process this request
        // This job handles data extraction, visualization generation, and updating status
        if (app()->environment() !== 'production') {
            $job = new ProcessPageRequestJob($pageRequest);
            $job->handle();
        } else {
            ProcessPageRequestJob::dispatch($pageRequest);
        }

        // Redirect user to the show page with success message
        return redirect()->route('page_requests.show',$pageRequest->id)
            ->with('success','Page request submitted successfully. Processing will begin shortly.');
    }

    // Show details of a page request
    public function show(PageRequest $pageRequest)
    {
        $pageRequest->load('tableData','visualizations');
        return view('page_requests.show',compact('pageRequest'));
    }

    public function retryVisualization($id)
    {
        $pageRequest = PageRequest::find($id);

        if (!$pageRequest) {
            return response()->json(['error' => 'PageRequest not found'], 404);
        }

        // Reset status to 'pending' or initial state
        $pageRequest->update(['status' => 'pending']);

        // Dispatch the job again to regenerate visualization
        if (app()->environment() !== 'production') {
            $job = new ProcessPageRequestJob($pageRequest);
            $job->handle();
        } else {
            ProcessPageRequestJob::dispatch($pageRequest);
        }

        return response()->json(['message' => 'Visualization regeneration started'], 200);
    }
}
