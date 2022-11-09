<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use Illuminate\Http\Request;
use App\Http\Requests\Review\ExportRequest;
use App\Http\Requests\Review\PostRequest;

class ReviewController extends Controller
{
    //
    public function index(Request $request, ReviewService $reviewService)
    {
        $reviews = $reviewService->search($request);
        return view('review/index', compact('reviews'));
    }

    public function export(ExportRequest $request, ReviewService $reviewService)
    {
        return $reviewService->export($request);
    }

    public function reply($reviewId, ReviewService $reviewService)
    {
        $review    = $reviewService->find($reviewId);
        $reply     = $review->reply;
        $templates = $reviewService->findTemplates();
        return view('review/reply', compact('review', 'reply', 'templates'));
    }

    public function post(PostRequest $request, ReviewService $reviewService)
    {
        $reviewService->reply($request);
        return redirect('review');
    }

}
