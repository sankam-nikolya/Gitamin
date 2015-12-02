<?php

/*
 * This file is part of Gitamin.
 * 
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitamin\Http\Controllers;

use AltThree\Validator\ValidationException;
use Gitamin\Commands\Project\AddProjectCommand;
use Gitamin\Commands\Project\RemoveProjectCommand;
use Gitamin\Commands\Project\UpdateProjectCommand;
use Gitamin\Commands\ProjectNamespace\AddProjectNamespaceCommand;
use Gitamin\Commands\ProjectNamespace\RemoveProjectNamespaceCommand;
use Gitamin\Commands\ProjectNamespace\UpdateProjectNamespaceCommand;
use Gitamin\Models\Project;
use Gitamin\Models\ProjectTeam;
use Gitamin\Models\ProjectNamespace;
use Gitamin\Models\Group;
use Gitamin\Models\Tag;
use Gitamin\Http\Controllers\Controller;
use GrahamCampbell\Binput\Facades\Binput;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GroupsController extends Controller
{
    /**
     * Array of sub-menu items.
     *
     * @var array
     */
    protected $subMenu = [];

    /**
     * Creates a new project controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subMenu = [
            'projects' => [
                'title'  => trans('dashboard.projects.projects'),
                'url'    => route('dashboard.projects.index'),
                'icon'   => 'fa fa-sitemap',
                'active' => false,
            ], 
            'groups'   => [
                'title'  => trans_choice('gitamin.groups.groups', 2),
                'url'    => route('dashboard.groups.index'),
                'icon'   => 'fa fa-folder',
                'active' => false,
            ],
            'labels' => [
                'title'  => trans_choice('dashboard.projects.labels.labels', 2),
                'url'    => route('dashboard.projects.index'),
                'icon'   => 'fa fa-tags',
                'active' => false,
            ],
        ];

        View::share([
            'sub_menu'  => $this->subMenu,
            'sub_title' => trans_choice('dashboard.projects.projects', 2),
        ]);
    }

    /**
     * Shows the project teams view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->subMenu['groups']['active'] = true;

        return View::make('groups.index')
            ->withPageTitle(trans_choice('gitamin.groups.groups', 2).' - '.trans('dashboard.dashboard'))
            ->withTeams(ProjectTeam::orderBy('order')->get())
            ->withSubMenu($this->subMenu);
    }

    /**
     * Shows the add project view.
     *
     * @return \Illuminate\View\View
     */
    public function new()
    {
        $teamId = (int) Binput::get('team_id');

        return View::make('groups.new')
            ->withPageTitle(trans('dashboard.projects.add.title').' - '.trans('dashboard.dashboard'))
            ->withTeamId($teamId)
            ->withTeams(ProjectTeam::all());
    }

    
    /**
     * Creates a new project.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        $groupData = Binput::get('group');
        $groupData['type'] = 'group';
        try {
            $group = $this->dispatchFromArray(AddProjectNamespaceCommand::class, $groupData);
        } catch (ValidationException $e) {
            return Redirect::route('groups.new')
                ->withInput(Binput::all())
                ->withTitle(sprintf('%s %s', trans('dashboard.notifications.whoops'), trans('dashboard.teams.add.failure')))
                ->withErrors($e->getMessageBag());
        }

        return Redirect::route('dashboard.groups')
            ->withSuccess(sprintf('%s %s', trans('dashboard.notifications.awesome'), trans('dashboard.teams.add.success')));
    }

    /**
     * Shows the add project team view.
     *
     * @return \Illuminate\View\View
     */
    public function showAddProjectTeam()
    {
        return View::make('dashboard.teams.add')
            ->withPageTitle(trans('dashboard.teams.add.title').' - '.trans('dashboard.dashboard'));
    }

    /**
     * Shows the edit project team view.
     *
     * @param \Gitamin\Models\ProjectTeam $team
     *
     * @return \Illuminate\View\View
     */
    public function showEditProjectTeam(ProjectTeam $team)
    {
        return View::make('dashboard.teams.edit')
            ->withPageTitle(trans('dashboard.teams.edit.title').' - '.trans('dashboard.dashboard'))
            ->withTeam($team);
    }

   

    /**
     * Updates a project team.
     *
     * @param \Gitamin\Models\ProjectTeam $team
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProjectTeamAction(ProjectTeam $team)
    {
        try {
            $team = $this->dispatch(new UpdateProjectTeamCommand(
                $team,
                Binput::get('name'),
                Binput::get('slug'),
                Binput::get('order', 0)
            ));
        } catch (ValidationException $e) {
            return Redirect::route('dashboard.teams.edit', ['id' => $team->id])
                ->withInput(Binput::all())
                ->withTitle(sprintf('%s %s', trans('dashboard.notifications.whoops'), trans('dashboard.teams.edit.failure')))
                ->withErrors($e->getMessageBag());
        }

        return Redirect::route('dashboard.teams.edit', ['id' => $team->id])
            ->withSuccess(sprintf('%s %s', trans('dashboard.notifications.awesome'), trans('dashboard.teams.edit.success')));
    }
}
