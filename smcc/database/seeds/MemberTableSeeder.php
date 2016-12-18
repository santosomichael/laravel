<?php

use Illuminate\Database\Seeder;

class MemberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('members')->insert([
            'no_anggota' => rand(1000,2000),
            'nama' => str_random(10),
            'alamat' => str_random(15),
            'statusBibleStudy' => rand(0,1),
            'statusBaptis' => rand(0,1),
            'statusAktif' => 1,
            'password' => bcrypt('secret'),
            'created_at' => date("Y-m-d h:i:s"),
        ]);
    }
}
