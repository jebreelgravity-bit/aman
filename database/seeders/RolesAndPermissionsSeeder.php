<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Users management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Trips management
            'view_trips',
            'create_trips',
            'edit_trips',
            'delete_trips',
            
            // Financial management
            'view_transactions',
            'view_pricing_settings',
            'edit_pricing_settings',
            'view_rewards',
            'distribute_rewards',
            
            // Marketing
            'view_coupons',
            'create_coupons',
            'edit_coupons',
            'delete_coupons',
            'view_popups',
            'create_popups',
            'edit_popups',
            'delete_popups',
            
            // Support
            'view_complaints',
            'edit_complaints',
            'assign_complaints',
            'resolve_complaints',
            
            // Reports
            'view_reports',
            'export_reports',
            
            // Settings
            'view_settings',
            'edit_settings',
            'view_activity_logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - has all permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - most permissions except critical settings
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view_users', 'create_users', 'edit_users',
            'view_trips', 'create_trips', 'edit_trips',
            'view_transactions',
            'view_pricing_settings',
            'view_rewards',
            'view_coupons', 'create_coupons', 'edit_coupons',
            'view_complaints', 'edit_complaints', 'assign_complaints', 'resolve_complaints',
            'view_reports',
        ]);

        // Financial Manager
        $financialManager = Role::create(['name' => 'financial_manager']);
        $financialManager->givePermissionTo([
            'view_transactions',
            'view_pricing_settings',
            'edit_pricing_settings',
            'view_rewards',
            'distribute_rewards',
            'view_reports',
            'export_reports',
        ]);

        // Support Manager
        $supportManager = Role::create(['name' => 'support_manager']);
        $supportManager->givePermissionTo([
            'view_complaints',
            'edit_complaints',
            'assign_complaints',
            'resolve_complaints',
            'view_users',
            'view_trips',
        ]);

        // Marketing Manager
        $marketingManager = Role::create(['name' => 'marketing_manager']);
        $marketingManager->givePermissionTo([
            'view_coupons', 'create_coupons', 'edit_coupons', 'delete_coupons',
            'view_popups', 'create_popups', 'edit_popups', 'delete_popups',
            'view_reports',
        ]);

        $this->command->info('✓ تم إنشاء الأدوار والصلاحيات بنجاح');
        $this->command->info('  - Super Admin: جميع الصلاحيات');
        $this->command->info('  - Admin: معظم الصلاحيات');
        $this->command->info('  - Financial Manager: الإدارة المالية');
        $this->command->info('  - Support Manager: دعم العملاء');
        $this->command->info('  - Marketing Manager: التسويق');
    }
}
