<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Superadmin
        $superAdminRole = DB::table( 'roles' )->insertGetId( [
            'name' => 'super_admin',
            'guard_name' => 'admin',
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $superAdmin = DB::table( 'administrators' )->insertGetId( [
            'name' => 'altasming',
            'email' => 'altas.x.junming@gmail.com',
            'password' => Hash::make( 'altasming1234' ),
            'fullname' => 'Altas Xiao',
            'phone_number' => '12345678',
            'role' => $superAdminRole,
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $superAdmin2 = DB::table( 'administrators' )->insertGetId( [
            'name' => 'developer',
            'email' => 'developer-acc@gmail.com',
            'password' => Hash::make( 'developer-1234' ),
            'fullname' => 'Developer',
            'phone_number' => '11112222',
            'role' => $superAdminRole,
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        DB::table( 'model_has_roles' )->insert( [
            'role_id' => $superAdminRole,
            'model_type' => 'App\Models\Administrator',
            'model_id' => $superAdmin,
        ] );

        DB::table( 'model_has_roles' )->insert( [
            'role_id' => $superAdminRole,
            'model_type' => 'App\Models\Administrator',
            'model_id' => $superAdmin2,
        ] );

        // Admin
        $adminRole = DB::table( 'roles' )->insertGetId( [
            'name' => 'admin',
            'guard_name' => 'admin',
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        // Owner
        // $ownerRole = DB::table( 'roles' )->insertGetId( [
        //     'name' => 'owner',
        //     'guard_name' => 'admin',
        //     'created_at' => date( 'Y-m-d H:i:s' ),
        //     'updated_at' => date( 'Y-m-d H:i:s' ),
        // ] );

        // $user = DB::table( 'users' )->insertGetId( [
        //     'fullname' => 'Owner',
        //     'email' => 'owner@gmail.com',
        //     'phone_number' => null,
        //     'password' => Hash::make( 'Abcd1234!' ),
        //     'status' => 10,
        // ] );

        // $owner = DB::table( 'administrators' )->insertGetId( [
        //     'name' => 'owner',
        //     'email' => 'owner@gmail.com',
        //     'user_id' => $user,
        //     'password' => Hash::make( 'Abcd1234!' ),
        //     'fullname' => 'Owner',
        //     'phone_number' => '12341234',
        //     'role' => $ownerRole,
        //     'status' => 10,
        //     'created_at' => date( 'Y-m-d H:i:s' ),
        //     'updated_at' => date( 'Y-m-d H:i:s' ),
        // ] );

        // DB::table( 'model_has_roles' )->insert( [
        //     'role_id' => $ownerRole,
        //     'model_type' => 'App\Models\Administrator',
        //     'model_id' => $owner,
        // ] );

        // // Salesman
        // $salesmanRole = DB::table( 'roles' )->insertGetId( [
        //     'name' => 'salesman',
        //     'guard_name' => 'admin',
        //     'created_at' => date( 'Y-m-d H:i:s' ),
        //     'updated_at' => date( 'Y-m-d H:i:s' ),
        // ] );

        // $user = DB::table( 'users' )->insertGetId( [
        //     'fullname' => 'Salesman',
        //     'email' => 'salesman@gmail.com',
        //     'phone_number' => null,
        //     'password' => Hash::make( 'Abcd1234!' ),
        //     'status' => 10,
        // ] );

        // $salesman = DB::table( 'administrators' )->insertGetId( [
        //     'name' => 'salesman',
        //     'email' => 'salesman@gmail.com',
        //     'user_id' => $user,
        //     'password' => Hash::make( 'Abcd1234!' ),
        //     'fullname' => 'Salesman',
        //     'phone_number' => '123412345',
        //     'role' => $salesmanRole,
        //     'status' => 10,
        //     'created_at' => date( 'Y-m-d H:i:s' ),
        //     'updated_at' => date( 'Y-m-d H:i:s' ),
        // ] );

        // DB::table( 'model_has_roles' )->insert( [
        //     'role_id' => $salesmanRole,
        //     'model_type' => 'App\Models\Administrator',
        //     'model_id' => $salesman,
        // ] );
    }
}
