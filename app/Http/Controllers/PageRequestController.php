<?php

namespace App\Http\Controllers;

use App\Models\PageRequest;
use Illuminate\Http\Request;
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

    // Store new page request
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $pageRequest = PageRequest::create([
            'url' => $request->url,
            'status' => 'pending',
        ]);

        // Here you might dispatch a job to process the request asynchronously
        return redirect()->route('page_requests.show',$pageRequest->id)
            ->with('success','Page request submitted successfully.');
    }

    // Show details of a page request
    public function show(PageRequest $pageRequest)
    {
        $pageRequest->load('tableData','visualizations');
        return view('page_requests.show',compact('pageRequest'));
    }
}
