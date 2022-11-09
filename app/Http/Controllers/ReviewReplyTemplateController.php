<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Template\CreateEditRequest;
use App\Services\ReviewReplyTemplateService;

class ReviewReplyTemplateController extends Controller
{
    //
    public function index(Request $request, ReviewReplyTemplateService $service)
    {
        $templates = $service->search($request);
        return view('review_reply_template/index', compact('templates'));
    }

    public function create()
    {
        return view('review_reply_template/create');
    }

    public function store(CreateEditRequest $request, ReviewReplyTemplateService $service)
    {
        $service->store($request);
        return redirect('/template');
    }

    public function edit($reviewReplyTemplateId, ReviewReplyTemplateService $service)
    {
        $template = $service->find($reviewReplyTemplateId);
        return view('review_reply_template/edit', compact('template'));
    }

    public function update(CreateEditRequest $request, ReviewReplyTemplateService $service)
    {
        $service->update($request);
        return redirect('/template');
    }

    public function delete(Request $request, ReviewReplyTemplateService $service)
    {
        if ($request->ajax()) {
            return ['result' => $service->delete($request)];
        }
    }

    public function asyncFind(Request $request, ReviewReplyTemplateService $service)
    {
        if ($request->ajax()) {
            return ['result' => $service->find($request->input('review_reply_template_id'))];
        }
    }

}
