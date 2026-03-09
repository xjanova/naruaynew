<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BinaryTreeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TreeController extends Controller
{
    public function __construct(
        protected BinaryTreeService $treeService,
    ) {}

    public function index(Request $request)
    {
        $rootUser = $request->user_id
            ? User::findOrFail($request->user_id)
            : User::whereNull('sponsor_id')->first();

        $treeData = $this->treeService->getTreeVisualizationData(
            $rootUser?->id,
            $request->integer('depth', 5)
        );

        return Inertia::render('Admin/Tree/Index', [
            'treeData' => $treeData,
            'rootUser' => $rootUser?->only('id', 'username', 'first_name', 'last_name'),
            'depth' => $request->integer('depth', 5),
        ]);
    }

    public function sponsorTree(Request $request)
    {
        $rootUser = $request->user_id
            ? User::findOrFail($request->user_id)
            : User::whereNull('sponsor_id')->first();

        $treeData = $this->treeService->getSponsorTreeData(
            $rootUser?->id,
            $request->integer('depth', 5)
        );

        return Inertia::render('Admin/Tree/Sponsor', [
            'treeData' => $treeData,
            'rootUser' => $rootUser?->only('id', 'username', 'first_name', 'last_name'),
            'depth' => $request->integer('depth', 5),
        ]);
    }

    public function search(Request $request)
    {
        $users = User::where('username', 'like', "%{$request->q}%")
            ->orWhere('first_name', 'like', "%{$request->q}%")
            ->orWhere('last_name', 'like', "%{$request->q}%")
            ->take(10)
            ->get(['id', 'username', 'first_name', 'last_name']);

        return response()->json($users);
    }
}
