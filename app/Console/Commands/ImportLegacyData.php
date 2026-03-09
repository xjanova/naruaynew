<?php

namespace App\Console\Commands;

use App\Models\ImportIdMap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ImportLegacyData extends Command
{
    protected $signature = 'import:legacy
        {--source=admin_xmanshop : Source database name}
        {--prefix=313_ : Table prefix in source database}
        {--step= : Run specific step only (1-10)}
        {--chunk=500 : Batch size for chunked processing}
        {--dry-run : Show what would be imported without actually importing}
        {--verify : Run verification after import}';

    protected $description = 'Import data from legacy CodeIgniter MLM system to new Laravel system';

    private string $source;
    private string $prefix;
    private int $chunkSize;
    private bool $dryRun;
    private array $stats = [];

    public function handle(): int
    {
        $this->source = $this->option('source');
        $this->prefix = $this->option('prefix');
        $this->chunkSize = (int) $this->option('chunk');
        $this->dryRun = (bool) $this->option('dry-run');

        $this->info("=== Naruay MLM Legacy Data Import ===");
        $this->info("Source DB: {$this->source}");
        $this->info("Prefix: {$this->prefix}");
        $this->info("Chunk size: {$this->chunkSize}");
        if ($this->dryRun) $this->warn("DRY RUN MODE - No data will be written");
        $this->newLine();

        // Configure legacy DB connection
        config(['database.connections.legacy' => [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => $this->source,
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => $this->prefix,
        ]]);

        $steps = [
            1 => 'importFoundation',
            2 => 'importConfiguration',
            3 => 'importRanksAndCommissionConfigs',
            4 => 'importProducts',
            5 => 'importUsers',
            6 => 'importTreeStructure',
            7 => 'importOrdersAndTransactions',
            8 => 'importEpins',
            9 => 'importKyc',
            10 => 'verifyImport',
        ];

        $stepLabels = [
            1 => 'Foundation (countries, states, languages, currencies)',
            2 => 'Configuration (settings, module statuses)',
            3 => 'Ranks & Commission Configs',
            4 => 'Products & Categories',
            5 => 'Users (2-pass import)',
            6 => 'Tree Structure (placement + sponsor)',
            7 => 'Orders, Commissions & Wallet Transactions',
            8 => 'E-Pins',
            9 => 'KYC Documents',
            10 => 'Verification & Integrity Check',
        ];

        $specificStep = $this->option('step');

        foreach ($steps as $num => $method) {
            if ($specificStep && (int) $specificStep !== $num) continue;

            $this->info("--- Step {$num}: {$stepLabels[$num]} ---");
            $startTime = microtime(true);

            try {
                $this->$method();
                $elapsed = round(microtime(true) - $startTime, 2);
                $this->info("Step {$num} completed in {$elapsed}s");
            } catch (\Exception $e) {
                $this->error("Step {$num} FAILED: {$e->getMessage()}");
                $this->error($e->getTraceAsString());

                if (!$this->confirm('Continue with next step?', false)) {
                    return self::FAILURE;
                }
            }

            $this->newLine();
        }

        // Print summary
        $this->printSummary();

        if ($this->option('verify') && !$specificStep) {
            $this->verifyImport();
        }

        return self::SUCCESS;
    }

    // ============================================
    // Step 1: Foundation
    // ============================================
    private function importFoundation(): void
    {
        // Countries
        $countries = DB::connection('legacy')->table('infinite_countries')->get();
        $this->info("  Importing {$countries->count()} countries...");

        if (!$this->dryRun) {
            foreach ($countries->chunk($this->chunkSize) as $chunk) {
                $inserts = $chunk->map(fn($row) => [
                    'id' => $row->id,
                    'name' => $row->country_name ?? $row->name ?? '',
                    'code' => substr($row->country_code ?? $row->code ?? '', 0, 3),
                    'phone_code' => $row->phone_code ?? '',
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();
                DB::table('countries')->insert($inserts);
            }
        }
        $this->stats['countries'] = $countries->count();

        // States
        $states = DB::connection('legacy')->table('infinite_states')->get();
        $this->info("  Importing {$states->count()} states...");

        if (!$this->dryRun) {
            foreach ($states->chunk($this->chunkSize) as $chunk) {
                $inserts = $chunk->map(fn($row) => [
                    'id' => $row->id,
                    'country_id' => $row->country_id ?? 1,
                    'name' => $row->state_name ?? $row->name ?? '',
                    'code' => $row->state_code ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();
                DB::table('states')->insert($inserts);
            }
        }
        $this->stats['states'] = $states->count();

        // Languages
        $languages = DB::connection('legacy')->table('infinite_languages')->get();
        $this->info("  Importing {$languages->count()} languages...");

        if (!$this->dryRun) {
            foreach ($languages->chunk($this->chunkSize) as $chunk) {
                $inserts = $chunk->map(fn($row) => [
                    'name' => $row->lang_name ?? $row->name ?? '',
                    'code' => substr($row->lang_code ?? $row->code ?? 'en', 0, 5),
                    'flag' => $row->flag ?? null,
                    'is_default' => ($row->default_lang ?? 'no') === 'yes',
                    'status' => ($row->status ?? 'yes') === 'yes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();
                DB::table('languages')->insert($inserts);
            }
        }
        $this->stats['languages'] = $languages->count();

        // Currencies
        $currencies = DB::connection('legacy')->table('currency_details')->get();
        $this->info("  Importing {$currencies->count()} currencies...");

        if (!$this->dryRun) {
            foreach ($currencies->chunk($this->chunkSize) as $chunk) {
                $inserts = $chunk->map(fn($row) => [
                    'name' => $row->currency ?? $row->name ?? '',
                    'code' => substr($row->currency_code ?? $row->code ?? '', 0, 3),
                    'symbol' => $row->symbol_left ?? $row->symbol ?? '',
                    'exchange_rate' => $row->value ?? 1,
                    'is_default' => ($row->default_currency ?? 'no') === 'yes',
                    'status' => ($row->status ?? 'yes') === 'yes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();
                DB::table('currencies')->insert($inserts);
            }
        }
        $this->stats['currencies'] = $currencies->count();
    }

    // ============================================
    // Step 2: Configuration
    // ============================================
    private function importConfiguration(): void
    {
        // Main configuration → settings (key-value)
        $config = DB::connection('legacy')->table('configuration')->first();

        if ($config) {
            $this->info("  Importing configuration...");
            $configArray = (array) $config;

            if (!$this->dryRun) {
                foreach ($configArray as $key => $value) {
                    if ($key === 'id') continue;
                    DB::table('settings')->updateOrInsert(
                        ['key' => $key],
                        ['value' => (string) ($value ?? ''), 'group' => 'mlm', 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
            $this->stats['settings'] = count($configArray) - 1;
        }

        // Module statuses / common_settings
        $modules = DB::connection('legacy')->table('common_settings')->get();
        $this->info("  Importing {$modules->count()} module settings...");

        if (!$this->dryRun) {
            foreach ($modules as $module) {
                $moduleArray = (array) $module;
                foreach ($moduleArray as $key => $value) {
                    if ($key === 'id') continue;
                    DB::table('module_statuses')->updateOrInsert(
                        ['module_name' => $key],
                        ['is_enabled' => in_array(strtolower((string) $value), ['yes', '1', 'true']), 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
        $this->stats['module_statuses'] = $modules->count();
    }

    // ============================================
    // Step 3: Ranks & Commission Configs
    // ============================================
    private function importRanksAndCommissionConfigs(): void
    {
        // Ranks
        $ranks = DB::connection('legacy')->table('rank_details')->get();
        $this->info("  Importing {$ranks->count()} ranks...");

        if (!$this->dryRun) {
            foreach ($ranks as $rank) {
                DB::table('ranks')->insert([
                    'id' => $rank->rank_id ?? $rank->id,
                    'name' => $rank->rank_name ?? $rank->name ?? '',
                    'color' => $rank->rank_color ?? null,
                    'referral_count' => $rank->referal_count ?? 0,
                    'personal_pv' => $rank->personal_pv ?? 0,
                    'group_pv' => $rank->gpv ?? 0,
                    'downline_count' => $rank->downline_count ?? 0,
                    'team_member_count' => $rank->team_member_count ?? 0,
                    'rank_bonus' => $rank->rank_bonus ?? 0,
                    'party_commission' => $rank->party_comm ?? 0,
                    'is_active' => ($rank->rank_status ?? 'yes') === 'yes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->stats['ranks'] = $ranks->count();

        // Binary bonus config
        $binaryConfig = DB::connection('legacy')->table('binary_bonus_config')->first();
        if ($binaryConfig && !$this->dryRun) {
            DB::table('binary_bonus_configs')->insert([
                'calculation_criteria' => $binaryConfig->calculation_criteria ?? 'pair_matching',
                'calculation_period' => $binaryConfig->calculation_period ?? 'daily',
                'commission_type' => $binaryConfig->commission_type ?? 'percentage',
                'pair_commission' => $binaryConfig->pair_commission ?? 0,
                'pair_type' => $binaryConfig->pair_type ?? '1:1',
                'pair_value' => $binaryConfig->pair_value ?? 0,
                'point_value' => $binaryConfig->point_value ?? 0,
                'carry_forward' => $binaryConfig->carry_forward ?? 'yes',
                'flush_out' => $binaryConfig->flush_out ?? 'no',
                'flush_out_limit' => $binaryConfig->flush_out_limit ?? 0,
                'flush_out_period' => $binaryConfig->flush_out_period ?? null,
                'locking_period' => $binaryConfig->locking_period ?? 0,
                'block_binary_pv' => $binaryConfig->block_binary_pv ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->stats['binary_bonus_configs'] = 1;
        }

        // Level commission configs
        $levelConfigs = DB::connection('legacy')->table('level_commision')->get();
        $this->info("  Importing {$levelConfigs->count()} level commission configs...");

        if (!$this->dryRun) {
            foreach ($levelConfigs as $config) {
                DB::table('level_commission_configs')->insert([
                    'level' => $config->level_no ?? $config->level ?? 0,
                    'percentage' => $config->level_percentage ?? 0,
                    'donation_1' => $config->donation_1 ?? 0,
                    'donation_2' => $config->donation_2 ?? 0,
                    'donation_3' => $config->donation_3 ?? 0,
                    'donation_4' => $config->donation_4 ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->stats['level_commission_configs'] = $levelConfigs->count();

        // Level commission by package
        $this->importTable('level_commission_reg_pck', 'level_commission_by_package', function ($row) {
            return [
                'level' => $row->level ?? 0,
                'product_id' => $this->resolveProductId($row->pck_id ?? null) ?? 1,
                'commission_reg_pck' => $row->cmsn_reg_pck ?? 0,
                'commission_member_pck' => $row->cmsn_member_pck ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Level commission by rank
        $this->importTable('level_commission_rank', 'level_commission_by_rank', function ($row) {
            return [
                'level' => $row->level ?? 0,
                'rank_id' => $row->rank_id ?? 1,
                'commission' => $row->commission ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Sales commission configs
        $this->importTable('sales_commissions', 'sales_commission_configs', function ($row) {
            return [
                'level' => $row->level ?? 0,
                'product_id' => $this->resolveProductId($row->pck_id ?? null) ?? 1,
                'commission' => $row->sales ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Matching commission configs
        $this->importTable('matching_commissions', 'matching_commission_configs', function ($row) {
            return [
                'level' => $row->level ?? 0,
                'product_id' => $this->resolveProductId($row->pck_id ?? null) ?? 1,
                'commission_member_pck' => $row->cmsn_member_pck ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    // ============================================
    // Step 4: Products
    // ============================================
    private function importProducts(): void
    {
        // Categories
        $categories = DB::connection('legacy')->table('repurchase_category')->get();
        $this->info("  Importing {$categories->count()} product categories...");

        if (!$this->dryRun) {
            foreach ($categories as $cat) {
                DB::table('product_categories')->insert([
                    'id' => $cat->id,
                    'name' => $cat->name ?? $cat->category_name ?? '',
                    'slug' => Str::slug($cat->name ?? $cat->category_name ?? 'category-' . $cat->id),
                    'description' => $cat->description ?? null,
                    'image' => $cat->image ?? null,
                    'is_active' => ($cat->status ?? 'yes') === 'yes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->stats['product_categories'] = $categories->count();

        // Products (packages)
        $products = DB::connection('legacy')->table('package')->get();
        $this->info("  Importing {$products->count()} products...");

        if (!$this->dryRun) {
            foreach ($products as $product) {
                $newId = DB::table('products')->insertGetId([
                    'legacy_id' => $product->product_id ?? $product->id,
                    'name' => $product->product_name ?? '',
                    'sku' => $product->prod_id ?? 'PKG-' . ($product->product_id ?? $product->id),
                    'type' => ($product->type_of_package ?? 'registration') === 'registration' ? 'registration' : 'repurchase',
                    'price' => $product->product_value ?? 0,
                    'pv_value' => $product->pair_value ?? 0,
                    'bv_value' => $product->bv_value ?? 0,
                    'referral_commission' => $product->referral_commission ?? 0,
                    'pair_price' => $product->pair_price ?? 0,
                    'roi_percent' => $product->roi ?? 0,
                    'roi_days' => $product->days ?? 0,
                    'subscription_period' => $product->subscription_period ?? 0,
                    'product_validity_days' => $product->product_validity ?? 0,
                    'category_id' => $product->category_id ?? null,
                    'description' => $product->description ?? null,
                    'image' => $product->prod_img ?? null,
                    'tree_icon' => $product->tree_icon ?? null,
                    'is_active' => ($product->active ?? 'yes') === 'yes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Store mapping
                ImportIdMap::create([
                    'table_name' => 'products',
                    'legacy_id' => $product->product_id ?? $product->id,
                    'new_id' => $newId,
                ]);
            }
        }
        $this->stats['products'] = $products->count();
    }

    // ============================================
    // Step 5: Users (2-pass import)
    // ============================================
    private function importUsers(): void
    {
        $totalUsers = DB::connection('legacy')->table('ft_individual')->count();
        $this->info("  Importing {$totalUsers} users (2-pass method)...");

        // Pass 1: Create all users without self-referencing FK
        $this->info("  Pass 1: Creating user records...");
        $bar = $this->output->createProgressBar($totalUsers);

        DB::connection('legacy')->table('ft_individual')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($users) use ($bar) {
                if ($this->dryRun) {
                    $bar->advance($users->count());
                    return;
                }

                foreach ($users as $user) {
                    $newId = DB::table('users')->insertGetId([
                        'legacy_id' => $user->id,
                        'username' => $user->user_name ?? 'user_' . $user->id,
                        'email' => $user->email ?? "user_{$user->id}@legacy.local",
                        'password' => $user->password ?? Hash::make('changeme123'),
                        'transaction_password' => $user->tran_pass ?? null,
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'phone' => $user->phone ?? null,
                        'id_card' => $user->id_card ?? null,
                        'date_of_birth' => $user->date_of_birth ?? null,
                        'gender' => match(strtolower($user->gender ?? '')) {
                            'male', 'm' => 'male',
                            'female', 'f' => 'female',
                            default => null,
                        },
                        'photo' => $user->photo ?? 'nophoto.jpg',
                        'sponsor_id' => null, // Will be set in Pass 2
                        'placement_id' => null, // Will be set in Pass 2
                        'position' => in_array($user->position ?? '', ['L', 'R']) ? $user->position : null,
                        'leg_position' => $user->leg_position ?? null,
                        'product_id' => null, // Will be mapped later
                        'product_validity' => $user->product_validity ?? null,
                        'personal_pv' => $user->personal_pv ?? 0,
                        'group_pv' => $user->gpv ?? 0,
                        'rank_id' => $user->user_rank_id ?? 1,
                        'user_level' => $user->user_level ?? 0,
                        'sponsor_level' => $user->sponsor_level ?? 0,
                        'binary_leg' => in_array($user->binary_leg ?? 'any', ['any', 'left', 'right']) ? $user->binary_leg : 'any',
                        'active_status' => ($user->active ?? 'no') === 'yes' ? 'active' : 'inactive',
                        'kyc_status' => 'pending',
                        'join_date' => $user->date_of_joining ?? null,
                        'register_by_using' => $user->register_by_using ?? null,
                        'created_at' => $user->date_of_joining ?? now(),
                        'updated_at' => now(),
                    ]);

                    ImportIdMap::create([
                        'table_name' => 'users',
                        'legacy_id' => $user->id,
                        'new_id' => $newId,
                    ]);

                    // Create user balance
                    DB::table('user_balances')->insert([
                        'user_id' => $newId,
                        'balance_amount' => 0,
                        'purchase_wallet' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();

        // Pass 2: Update self-referencing relationships
        $this->info("  Pass 2: Updating sponsor/placement relationships...");
        $bar = $this->output->createProgressBar($totalUsers);

        if (!$this->dryRun) {
            DB::connection('legacy')->table('ft_individual')
                ->orderBy('id')
                ->chunk($this->chunkSize, function ($users) use ($bar) {
                    foreach ($users as $user) {
                        $newId = ImportIdMap::resolve('users', $user->id);
                        if (!$newId) { $bar->advance(); continue; }

                        $updates = [];

                        // Map sponsor_id
                        if ($user->sponsor_id) {
                            $newSponsorId = ImportIdMap::resolve('users', $user->sponsor_id);
                            if ($newSponsorId) $updates['sponsor_id'] = $newSponsorId;
                        }

                        // Map placement_id (father_id in old system)
                        if ($user->father_id ?? null) {
                            $newPlacementId = ImportIdMap::resolve('users', $user->father_id);
                            if ($newPlacementId) $updates['placement_id'] = $newPlacementId;
                        }

                        // Map product_id
                        if ($user->product_id ?? null) {
                            $newProductId = ImportIdMap::resolve('products', $user->product_id);
                            if ($newProductId) $updates['product_id'] = $newProductId;
                        }

                        if (!empty($updates)) {
                            DB::table('users')->where('id', $newId)->update($updates);
                        }

                        $bar->advance();
                    }
                });
        }

        $bar->finish();
        $this->newLine();

        // Import user balances
        $this->info("  Importing user balances...");
        DB::connection('legacy')->table('user_balance_amount')
            ->orderBy('user_id')
            ->chunk($this->chunkSize, function ($balances) {
                if ($this->dryRun) return;

                foreach ($balances as $balance) {
                    $newUserId = ImportIdMap::resolve('users', $balance->user_id);
                    if (!$newUserId) continue;

                    DB::table('user_balances')->where('user_id', $newUserId)->update([
                        'balance_amount' => $balance->balance_amount ?? 0,
                        'purchase_wallet' => $balance->purchase_wallet ?? 0,
                        'updated_at' => now(),
                    ]);
                }
            });

        // Import user profiles (from user_details)
        $this->info("  Importing user profiles...");
        DB::connection('legacy')->table('user_details')
            ->orderBy('user_detail_refid')
            ->chunk($this->chunkSize, function ($details) {
                if ($this->dryRun) return;

                foreach ($details as $detail) {
                    $newUserId = ImportIdMap::resolve('users', $detail->user_detail_refid ?? $detail->id);
                    if (!$newUserId) continue;

                    // Update KYC status on user
                    if (($detail->kyc_status ?? 'no') === 'yes') {
                        DB::table('users')->where('id', $newUserId)->update(['kyc_status' => 'approved']);
                    }

                    DB::table('user_profiles')->insert([
                        'user_id' => $newUserId,
                        'address' => $detail->address ?? null,
                        'address2' => $detail->address2 ?? null,
                        'city' => $detail->city ?? null,
                        'state' => $detail->state ?? null,
                        'country' => $detail->country ?? null,
                        'postal_code' => $detail->pin ?? null,
                        'bank_name' => $detail->bank ?? null,
                        'account_number' => $detail->account_number ?? null,
                        'account_holder_name' => $detail->account_holder_name ?? null,
                        'ifsc_code' => $detail->ifsc ?? null,
                        'pan_number' => $detail->pan ?? null,
                        'payout_type' => 'bank_transfer',
                        'facebook' => $detail->facebook ?? null,
                        'line_token' => $detail->line_token ?? null,
                        'line_userid' => $detail->line_userid ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        $this->stats['users'] = $totalUsers;
    }

    // ============================================
    // Step 6: Tree Structure
    // ============================================
    private function importTreeStructure(): void
    {
        // Placement tree paths
        $totalPaths = DB::connection('legacy')->table('treepath')->count();
        $this->info("  Importing {$totalPaths} placement tree paths...");
        $bar = $this->output->createProgressBar($totalPaths);

        DB::connection('legacy')->table('treepath')
            ->orderBy('ancestor')
            ->chunk($this->chunkSize, function ($paths) use ($bar) {
                if ($this->dryRun) { $bar->advance($paths->count()); return; }

                $inserts = [];
                foreach ($paths as $path) {
                    $newAncestor = ImportIdMap::resolve('users', $path->ancestor);
                    $newDescendant = ImportIdMap::resolve('users', $path->descendant);
                    if (!$newAncestor || !$newDescendant) { $bar->advance(); continue; }

                    $inserts[] = [
                        'ancestor' => $newAncestor,
                        'descendant' => $newDescendant,
                    ];
                    $bar->advance();
                }

                if (!empty($inserts)) {
                    // Use insertOrIgnore to handle duplicates
                    foreach (array_chunk($inserts, $this->chunkSize) as $chunk) {
                        DB::table('tree_paths')->insertOrIgnore($chunk);
                    }
                }
            });

        $bar->finish();
        $this->newLine();
        $this->stats['tree_paths'] = $totalPaths;

        // Sponsor tree paths
        $totalSponsorPaths = DB::connection('legacy')->table('sponsor_treepath')->count();
        $this->info("  Importing {$totalSponsorPaths} sponsor tree paths...");
        $bar = $this->output->createProgressBar($totalSponsorPaths);

        DB::connection('legacy')->table('sponsor_treepath')
            ->orderBy('ancestor')
            ->chunk($this->chunkSize, function ($paths) use ($bar) {
                if ($this->dryRun) { $bar->advance($paths->count()); return; }

                $inserts = [];
                foreach ($paths as $path) {
                    $newAncestor = ImportIdMap::resolve('users', $path->ancestor);
                    $newDescendant = ImportIdMap::resolve('users', $path->descendant);
                    if (!$newAncestor || !$newDescendant) { $bar->advance(); continue; }

                    $inserts[] = [
                        'ancestor' => $newAncestor,
                        'descendant' => $newDescendant,
                    ];
                    $bar->advance();
                }

                if (!empty($inserts)) {
                    foreach (array_chunk($inserts, $this->chunkSize) as $chunk) {
                        DB::table('sponsor_tree_paths')->insertOrIgnore($chunk);
                    }
                }
            });

        $bar->finish();
        $this->newLine();
        $this->stats['sponsor_tree_paths'] = $totalSponsorPaths;

        // Leg details
        $totalLegs = DB::connection('legacy')->table('leg_details')->count();
        $this->info("  Importing {$totalLegs} leg details...");

        DB::connection('legacy')->table('leg_details')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($legs) {
                if ($this->dryRun) return;

                foreach ($legs as $leg) {
                    $newUserId = ImportIdMap::resolve('users', $leg->id);
                    if (!$newUserId) continue;

                    DB::table('leg_details')->updateOrInsert(
                        ['user_id' => $newUserId],
                        [
                            'total_left_count' => $leg->total_left_count ?? 0,
                            'total_right_count' => $leg->total_right_count ?? 0,
                            'total_left_carry' => $leg->total_left_carry ?? 0,
                            'total_right_carry' => $leg->total_right_carry ?? 0,
                            'total_active' => $leg->total_active ?? 0,
                            'total_inactive' => $leg->total_inactive ?? 0,
                            'left_carry_forward' => $leg->left_carry_forward ?? 0,
                            'right_carry_forward' => $leg->right_carry_forward ?? 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

        $this->stats['leg_details'] = $totalLegs;
    }

    // ============================================
    // Step 7: Orders & Transactions
    // ============================================
    private function importOrdersAndTransactions(): void
    {
        // Commissions (leg_amount)
        $totalCommissions = DB::connection('legacy')->table('leg_amount')->count();
        $this->info("  Importing {$totalCommissions} commissions...");
        $bar = $this->output->createProgressBar($totalCommissions);

        DB::connection('legacy')->table('leg_amount')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($commissions) use ($bar) {
                if ($this->dryRun) { $bar->advance($commissions->count()); return; }

                foreach ($commissions as $comm) {
                    $newUserId = ImportIdMap::resolve('users', $comm->user_id ?? 0);
                    if (!$newUserId) { $bar->advance(); continue; }

                    $newFromId = ImportIdMap::resolve('users', $comm->from_id ?? 0);

                    DB::table('commissions')->insert([
                        'legacy_id' => $comm->id,
                        'user_id' => $newUserId,
                        'from_user_id' => $newFromId,
                        'amount_type' => $comm->amount_type ?? 'level_commission',
                        'amount' => $comm->amount ?? 0,
                        'tds' => $comm->tds ?? 0,
                        'service_charge' => $comm->service_charge ?? 0,
                        'amount_payable' => $comm->total_amount ?? $comm->amount_payable ?? 0,
                        'level' => $comm->level ?? null,
                        'transaction_id' => $comm->transaction_id ?? null,
                        'note' => $comm->transaction_note ?? null,
                        'created_at' => $comm->date_added ?? $comm->created_at ?? now(),
                        'updated_at' => now(),
                    ]);

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->stats['commissions'] = $totalCommissions;

        // Wallet transactions (ewallet_history)
        $totalWallet = DB::connection('legacy')->table('ewallet_history')->count();
        $this->info("  Importing {$totalWallet} wallet transactions...");
        $bar = $this->output->createProgressBar($totalWallet);

        DB::connection('legacy')->table('ewallet_history')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($txns) use ($bar) {
                if ($this->dryRun) { $bar->advance($txns->count()); return; }

                foreach ($txns as $tx) {
                    $newUserId = ImportIdMap::resolve('users', $tx->user_id ?? 0);
                    if (!$newUserId) { $bar->advance(); continue; }

                    $newFromId = ImportIdMap::resolve('users', $tx->from_id ?? 0);

                    DB::table('wallet_transactions')->insert([
                        'user_id' => $newUserId,
                        'from_user_id' => $newFromId,
                        'ewallet_type' => $tx->ewallet_type ?? null,
                        'amount' => $tx->amount ?? 0,
                        'purchase_wallet' => $tx->purchase_wallet ?? 0,
                        'amount_type' => $tx->amount_type ?? '',
                        'type' => strtolower($tx->type ?? 'credit') === 'credit' ? 'credit' : 'debit',
                        'transaction_fee' => $tx->transaction_fee ?? 0,
                        'transaction_id' => $tx->transaction_id ?? null,
                        'note' => $tx->transaction_note ?? null,
                        'pending_id' => $tx->pending_id ?? null,
                        'created_at' => $tx->date_added ?? $tx->created_at ?? now(),
                        'updated_at' => now(),
                    ]);

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->stats['wallet_transactions'] = $totalWallet;

        // PV Histories
        $totalPV = DB::connection('legacy')->table('pv_history_details')->count();
        $this->info("  Importing {$totalPV} PV histories...");

        DB::connection('legacy')->table('pv_history_details')
            ->orderBy('id')
            ->chunk($this->chunkSize * 2, function ($pvs) {
                if ($this->dryRun) return;

                $inserts = [];
                foreach ($pvs as $pv) {
                    $newUserId = ImportIdMap::resolve('users', $pv->user_id ?? 0);
                    if (!$newUserId) continue;

                    $inserts[] = [
                        'user_id' => $newUserId,
                        'pv_amount' => $pv->pv_amount ?? $pv->amount ?? 0,
                        'type' => $pv->pv_type ?? 'personal',
                        'source' => $pv->source ?? 'purchase',
                        'from_user_id' => ImportIdMap::resolve('users', $pv->from_id ?? 0),
                        'created_at' => $pv->date_added ?? now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($inserts)) {
                    foreach (array_chunk($inserts, $this->chunkSize) as $chunk) {
                        DB::table('pv_histories')->insert($chunk);
                    }
                }
            });

        $this->stats['pv_histories'] = $totalPV;
    }

    // ============================================
    // Step 8: E-Pins
    // ============================================
    private function importEpins(): void
    {
        $totalPins = DB::connection('legacy')->table('pin_numbers')->count();
        $this->info("  Importing {$totalPins} e-pins...");

        DB::connection('legacy')->table('pin_numbers')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($pins) {
                if ($this->dryRun) return;

                foreach ($pins as $pin) {
                    $ownerId = ImportIdMap::resolve('users', $pin->allocate_user_id ?? $pin->owner_id ?? 0);
                    $generatedBy = ImportIdMap::resolve('users', $pin->generated_by ?? 0);
                    $usedBy = ImportIdMap::resolve('users', $pin->used_user_id ?? $pin->used_by ?? 0);

                    if (!$ownerId) continue;

                    DB::table('epins')->insert([
                        'pin_number' => $pin->pin_numbers ?? $pin->pin_number ?? Str::random(16),
                        'amount' => $pin->balance_amount ?? $pin->amount ?? 0,
                        'product_id' => null,
                        'generated_by' => $generatedBy ?? $ownerId,
                        'owned_by' => $ownerId,
                        'used_by' => $usedBy,
                        'status' => match(strtolower($pin->status ?? '')) {
                            'yes', 'used' => 'used',
                            'blocked', 'expired' => 'blocked',
                            default => 'available',
                        },
                        'used_at' => $pin->used_date ?? null,
                        'created_at' => $pin->date_added ?? $pin->created_date ?? now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        $this->stats['epins'] = $totalPins;
    }

    // ============================================
    // Step 9: KYC
    // ============================================
    private function importKyc(): void
    {
        // KYC Categories
        $categories = DB::connection('legacy')->table('kyc_category')->get();
        $this->info("  Importing {$categories->count()} KYC categories...");

        if (!$this->dryRun) {
            foreach ($categories as $cat) {
                DB::table('kyc_categories')->insert([
                    'id' => $cat->id,
                    'name' => $cat->name ?? '',
                    'description' => $cat->description ?? null,
                    'is_required' => ($cat->required ?? 'no') === 'yes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->stats['kyc_categories'] = $categories->count();

        // KYC Documents
        $docs = DB::connection('legacy')->table('kyc_docs')->get();
        $this->info("  Importing {$docs->count()} KYC documents...");

        if (!$this->dryRun) {
            foreach ($docs as $doc) {
                $newUserId = ImportIdMap::resolve('users', $doc->user_id ?? 0);
                if (!$newUserId) continue;

                DB::table('kyc_documents')->insert([
                    'user_id' => $newUserId,
                    'kyc_category_id' => $doc->category_id ?? $doc->kyc_category_id ?? 1,
                    'file_path' => $doc->file_name ?? $doc->file_path ?? '',
                    'file_name' => $doc->original_file_name ?? basename($doc->file_name ?? ''),
                    'status' => match(strtolower($doc->status ?? 'pending')) {
                        'approved', 'yes' => 'approved',
                        'rejected', 'no' => 'rejected',
                        default => 'pending',
                    },
                    'created_at' => $doc->date_added ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->stats['kyc_documents'] = $docs->count();

        // Rank histories
        $rankHistories = DB::connection('legacy')->table('rank_history')->get();
        $this->info("  Importing {$rankHistories->count()} rank histories...");

        if (!$this->dryRun) {
            foreach ($rankHistories as $rh) {
                $newUserId = ImportIdMap::resolve('users', $rh->user_id ?? 0);
                if (!$newUserId) continue;

                DB::table('rank_histories')->insert([
                    'user_id' => $newUserId,
                    'old_rank_id' => $rh->current_rank ?? $rh->old_rank_id ?? null,
                    'new_rank_id' => $rh->new_rank ?? $rh->new_rank_id ?? 1,
                    'created_at' => $rh->date ?? $rh->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->stats['rank_histories'] = $rankHistories->count();
    }

    // ============================================
    // Step 10: Verification
    // ============================================
    private function verifyImport(): void
    {
        $this->info("  Running verification checks...");
        $errors = [];

        // Check record counts
        foreach ($this->stats as $table => $expectedCount) {
            $actualCount = DB::table($table)->count();
            $match = $actualCount >= $expectedCount ? 'OK' : 'MISMATCH';
            $this->line("    {$table}: expected={$expectedCount} actual={$actualCount} [{$match}]");
            if ($actualCount < $expectedCount) {
                $errors[] = "{$table}: expected {$expectedCount}, got {$actualCount}";
            }
        }

        // Check tree integrity: no orphan users (have placement but not in tree_paths)
        $orphans = DB::table('users')
            ->whereNotNull('placement_id')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tree_paths')
                    ->whereColumn('tree_paths.descendant', 'users.id');
            })
            ->count();

        $this->line("    Orphan users (not in tree): {$orphans}");
        if ($orphans > 0) {
            $errors[] = "{$orphans} users not found in tree_paths";
        }

        // Check balance integrity
        $balanceMismatch = DB::table('user_balances')
            ->where('balance_amount', '<', 0)
            ->count();

        $this->line("    Negative balances: {$balanceMismatch}");
        if ($balanceMismatch > 0) {
            $errors[] = "{$balanceMismatch} users have negative balance";
        }

        if (empty($errors)) {
            $this->info("  All verification checks PASSED!");
        } else {
            $this->warn("  Verification found issues:");
            foreach ($errors as $error) {
                $this->error("    - {$error}");
            }
        }
    }

    // ============================================
    // Helpers
    // ============================================
    private function importTable(string $sourceTable, string $destTable, callable $transform): void
    {
        $total = DB::connection('legacy')->table($sourceTable)->count();
        $this->info("  Importing {$total} records from {$sourceTable}...");

        if ($this->dryRun) {
            $this->stats[$destTable] = $total;
            return;
        }

        DB::connection('legacy')->table($sourceTable)
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($rows) use ($destTable, $transform) {
                $inserts = [];
                foreach ($rows as $row) {
                    try {
                        $inserts[] = $transform($row);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                if (!empty($inserts)) {
                    DB::table($destTable)->insert($inserts);
                }
            });

        $this->stats[$destTable] = $total;
    }

    private function resolveProductId($legacyProductId): ?int
    {
        if (!$legacyProductId) return null;
        return ImportIdMap::resolve('products', $legacyProductId);
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info("=== Import Summary ===");
        foreach ($this->stats as $table => $count) {
            $this->line("  {$table}: {$count} records");
        }
        $this->newLine();
    }
}
