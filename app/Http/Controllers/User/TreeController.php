<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\BinaryTreeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TreeController extends Controller
{
    public function __construct(
        protected BinaryTreeService $treeService,
    ) {}

    public function binary(Request $request)
    {
        $user = $request->user();
        $treeData = $this->treeService->getTreeVisualizationData(
            $user->id,
            $request->integer('depth', 5)
        );

        return Inertia::render('User/Tree/Binary', [
            'treeData' => $treeData,
            'depth' => $request->integer('depth', 5),
        ]);
    }

    public function sponsor(Request $request)
    {
        $user = $request->user();
        $treeData = $this->treeService->getSponsorTreeData(
            $user->id,
            $request->integer('depth', 5)
        );

        return Inertia::render('User/Tree/Sponsor', [
            'treeData' => $treeData,
            'depth' => $request->integer('depth', 5),
        ]);
    }
}
