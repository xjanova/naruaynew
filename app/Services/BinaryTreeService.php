<?php

namespace App\Services;

use App\Models\LegDetail;
use App\Models\SponsorTreePath;
use App\Models\TreePath;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BinaryTreeService
{
    /**
     * Find available placement position using BFS (Breadth-First Search)
     */
    public function findPlacement(int $placementId, string $preferredPosition): array
    {
        $position = strtoupper($preferredPosition);

        // Check if preferred position is available directly
        $existingChild = User::where('placement_id', $placementId)
            ->where('position', $position)
            ->exists();

        if (!$existingChild) {
            return ['placement_id' => $placementId, 'position' => $position];
        }

        // BFS to find next available slot in the preferred leg
        $queue = [];
        $firstChild = User::where('placement_id', $placementId)
            ->where('position', $position)
            ->first();

        if ($firstChild) {
            $queue[] = $firstChild->id;
        }

        while (!empty($queue)) {
            $currentId = array_shift($queue);

            // Check left
            $leftChild = User::where('placement_id', $currentId)
                ->where('position', 'L')
                ->first();

            if (!$leftChild) {
                return ['placement_id' => $currentId, 'position' => 'L'];
            }
            $queue[] = $leftChild->id;

            // Check right
            $rightChild = User::where('placement_id', $currentId)
                ->where('position', 'R')
                ->first();

            if (!$rightChild) {
                return ['placement_id' => $currentId, 'position' => 'R'];
            }
            $queue[] = $rightChild->id;
        }

        throw new \RuntimeException('No available placement position found');
    }

    /**
     * Add user to both placement tree and sponsor tree
     */
    public function addToTree(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->insertPlacementTreePaths($user);
            $this->insertSponsorTreePaths($user);
            $this->initializeLegDetail($user);
            $this->updateUplineLegCounts($user);

            // Invalidate tree cache
            Cache::tags(['tree'])->flush();
        });
    }

    private function insertPlacementTreePaths(User $user): void
    {
        // Self-reference
        DB::table('tree_paths')->insert([
            'ancestor' => $user->id,
            'descendant' => $user->id,
            'depth' => 0,
        ]);

        if ($user->placement_id) {
            // Copy ancestor paths from placement parent, incrementing depth
            $ancestorPaths = DB::table('tree_paths')
                ->where('descendant', $user->placement_id)
                ->select('ancestor', 'depth')
                ->get();

            $inserts = $ancestorPaths->map(fn($path) => [
                'ancestor' => $path->ancestor,
                'descendant' => $user->id,
                'depth' => $path->depth + 1,
            ])->toArray();

            if (!empty($inserts)) {
                DB::table('tree_paths')->insert($inserts);
            }
        }
    }

    private function insertSponsorTreePaths(User $user): void
    {
        // Self-reference
        DB::table('sponsor_tree_paths')->insert([
            'ancestor' => $user->id,
            'descendant' => $user->id,
            'depth' => 0,
        ]);

        if ($user->sponsor_id) {
            // Copy ancestor paths from sponsor, incrementing depth
            $ancestorPaths = DB::table('sponsor_tree_paths')
                ->where('descendant', $user->sponsor_id)
                ->select('ancestor', 'depth')
                ->get();

            $inserts = $ancestorPaths->map(fn($path) => [
                'ancestor' => $path->ancestor,
                'descendant' => $user->id,
                'depth' => $path->depth + 1,
            ])->toArray();

            if (!empty($inserts)) {
                DB::table('sponsor_tree_paths')->insert($inserts);
            }
        }
    }

    private function initializeLegDetail(User $user): void
    {
        LegDetail::create(['user_id' => $user->id]);
    }

    private function updateUplineLegCounts(User $user): void
    {
        if (!$user->placement_id) return;

        $position = $user->position; // 'L' or 'R'

        // Get all placement ancestors
        $ancestorIds = DB::table('tree_paths')
            ->where('descendant', $user->id)
            ->where('ancestor', '!=', $user->id)
            ->pluck('ancestor');

        if ($ancestorIds->isEmpty()) return;

        // For each ancestor, determine which leg this user falls under
        foreach ($ancestorIds as $ancestorId) {
            $side = $this->determineLegSide($ancestorId, $user->id);

            if ($side === 'L') {
                LegDetail::where('user_id', $ancestorId)->increment('total_left_count');
            } elseif ($side === 'R') {
                LegDetail::where('user_id', $ancestorId)->increment('total_right_count');
            }

            // Update active/inactive counts
            if ($user->isActive()) {
                LegDetail::where('user_id', $ancestorId)->increment('total_active');
            } else {
                LegDetail::where('user_id', $ancestorId)->increment('total_inactive');
            }
        }
    }

    /**
     * Determine which leg (L or R) a descendant falls under relative to an ancestor
     */
    public function determineLegSide(int $ancestorId, int $descendantId): ?string
    {
        // Find the direct child of ancestor that leads to descendant
        $leftChild = User::where('placement_id', $ancestorId)->where('position', 'L')->first();
        $rightChild = User::where('placement_id', $ancestorId)->where('position', 'R')->first();

        if ($leftChild) {
            if ($leftChild->id === $descendantId) return 'L';
            $isInLeftSubtree = DB::table('tree_paths')
                ->where('ancestor', $leftChild->id)
                ->where('descendant', $descendantId)
                ->exists();
            if ($isInLeftSubtree) return 'L';
        }

        if ($rightChild) {
            if ($rightChild->id === $descendantId) return 'R';
            $isInRightSubtree = DB::table('tree_paths')
                ->where('ancestor', $rightChild->id)
                ->where('descendant', $descendantId)
                ->exists();
            if ($isInRightSubtree) return 'R';
        }

        return null;
    }

    /**
     * Get tree data for visualization (React Flow / D3)
     */
    public function getTreeData(int $userId, int $depth = 3): array
    {
        $cacheKey = "tree:{$userId}:depth:{$depth}";

        return Cache::tags(['tree', "user:{$userId}"])
            ->remember($cacheKey, 900, function () use ($userId, $depth) {
                return $this->buildTreeData($userId, $depth, 0);
            });
    }

    private function buildTreeData(int $userId, int $maxDepth, int $currentDepth): ?array
    {
        if ($currentDepth >= $maxDepth) return null;

        $user = User::with(['rank', 'legDetail'])
            ->select('id', 'username', 'first_name', 'last_name', 'photo', 'position',
                'personal_pv', 'group_pv', 'rank_id', 'active_status', 'join_date')
            ->find($userId);

        if (!$user) return null;

        $node = [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->full_name,
            'photo' => $user->photo,
            'position' => $user->position,
            'personal_pv' => $user->personal_pv,
            'group_pv' => $user->group_pv,
            'rank' => $user->rank?->name ?? 'Starter',
            'rank_color' => $user->rank?->color ?? '#6B7280',
            'active' => $user->isActive(),
            'join_date' => $user->join_date?->toDateString(),
            'left_count' => $user->legDetail?->total_left_count ?? 0,
            'right_count' => $user->legDetail?->total_right_count ?? 0,
            'children' => [],
        ];

        // Get left and right children
        $leftChild = User::where('placement_id', $userId)->where('position', 'L')->first();
        $rightChild = User::where('placement_id', $userId)->where('position', 'R')->first();

        $node['children']['left'] = $leftChild
            ? $this->buildTreeData($leftChild->id, $maxDepth, $currentDepth + 1)
            : null;

        $node['children']['right'] = $rightChild
            ? $this->buildTreeData($rightChild->id, $maxDepth, $currentDepth + 1)
            : null;

        return $node;
    }

    /**
     * Check and apply BlueDiamond auto-promote rule
     * PV >= 6000 in single order -> auto-promote to Rank 11 (BlueDiamond)
     */
    public function checkAutoRankPromotion(User $user, float $orderPV): bool
    {
        if ($user->rank_id < 11 && $orderPV >= 6000) {
            $oldRankId = $user->rank_id;
            $user->update(['rank_id' => 11]);

            \App\Models\RankHistory::create([
                'user_id' => $user->id,
                'old_rank_id' => $oldRankId,
                'new_rank_id' => 11,
            ]);

            return true;
        }

        return false;
    }
}
