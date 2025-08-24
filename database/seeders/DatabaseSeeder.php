<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            PermissionTableSeeder::class,
            RoleTableSeeder::class,
            UserTableSeeder::class,
            TermTableSeeder::class,

            ViewClassPermissionTableSeeder::class,
            CompulsorySubjectsPermissionTableSeeder::class,
            MockSubjectVettingsPermissionTableSeeder::class,
            MyClassMySubjectPermissionTableSeeder::class,
            MyMockSubjectVettingsPermissionTableSeeder::class,
            MySubjectVettingsPermissionTableSeeder::class,
            PrincipalscommentPermissionTableSeeder::class,
            SchoolBillTermSessionPermissionTableSeeder::class,
            SchoolInformationPermissionTableSeeder::class,
            StudentMockReportPermissionTableSeeder::class,
            StudentPermissionTableSeeder::class,
            StudentReportPermissionTableSeeder::class,
            StudentStatusTableSeeder::class,
            SubjectClassResultRoomOperationPermissionTableSeeder::class,
            SubjectUploadForStaffPermissionTableSeeder::class,
            SubjectVettedPermissionTableSeeder::class,
            SubjectVettingsPermissionTableSeeder::class,
        
         

           
            // Add more seeders as needed
        ]);
    }
}
