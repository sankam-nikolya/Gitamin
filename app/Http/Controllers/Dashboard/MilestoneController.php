<?php

/*
 * This file is part of Gitamin.
 * 
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitamin\Http\Controllers\Dashboard;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class MilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function showMilestones()
    {
        $milestones = [];

        return View::make('dashboard.milestones.index')
            ->withPageTitle(trans('dashboard.milestones.milestones'))
            ->withMilestones($milestones);
    }
}
