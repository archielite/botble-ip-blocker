<?php

namespace ArchiElite\IpBlocker\Http\Controllers;

use ArchiElite\IpBlocker\Http\Requests\UpdateIpBlockerRequest;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;

class IpBlockerController extends BaseController
{
    public function loadIpBlocker()
    {
        PageTitle::setTitle(trans('plugins/ip-blocker::ip-blocker.menu'));

        return view('plugins/ip-blocker::ip-blocker');
    }

    public function updateIpBlocker(UpdateIpBlockerRequest $request)
    {
    }
}
