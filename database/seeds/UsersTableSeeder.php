<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            // password は "password"
            ['user_id' => '1001', 'name' => 'Administrator', 'email' => 'admin@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 1, 'enterprise_id' => 1, 'department' => 'システム管理', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
            ['user_id' => '2001', 'name' => '田中 一郎', 'email' => 'testuser1@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 21, 'enterprise_id' => 2, 'department' => 'システム開発部', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
            ['user_id' => '2002', 'name' => '佐藤 不二子', 'email' => 'testuser2@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 31, 'enterprise_id' => 2, 'department' => '広報部', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
            ['user_id' => '2003', 'name' => '山田 三太夫', 'email' => 'testuser3@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 41, 'enterprise_id' => 2, 'department' => '営業部', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
            ['user_id' => '2004', 'name' => '伊藤 四志子', 'email' => 'testuser4@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 41, 'enterprise_id' => 2, 'department' => '営業部', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
            ['user_id' => '2005', 'name' => '御手洗 五郎', 'email' => 'testuser5@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 21, 'enterprise_id' => 3, 'department' => 'システム開発部', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
            ['user_id' => '2006', 'name' => '長曾我部 六太', 'email' => 'testuser6@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 31, 'enterprise_id' => 3, 'department' => '広報部', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
            ['user_id' => '2007', 'name' => '勅使河原 七海', 'email' => 'testuser7@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 41, 'enterprise_id' => 3, 'department' => '営業部', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
            ['user_id' => '2008', 'name' => '勘解由小路 八衛門', 'email' => 'testuser8@example.com', 'password' => '$2y$10$lKryJYn.GeaEBOpCxr5aOu1sRNtmbqC8ftH/k8DFCRgdyxpbikeBu', 'remember_token' => 'dXOHiKz4bSFzurtLgcMIZFEFVaaxkcHb8QY8PXc3nZZ3NkPfOkfWon085b0o', 'role_id' => 41, 'enterprise_id' => 3, 'department' => '営業部', 'phone' => '', 'notify_line' => '', 'notify_slack' => '', ],
        ]);
    }
}
