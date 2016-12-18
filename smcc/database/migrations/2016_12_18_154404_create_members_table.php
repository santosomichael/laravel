<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('no_anggota')->unique();
            $table->string('password', 100);
            $table->string('nama', 45);
            $table->string('alamat', 100);
            $table->tinyInteger('statusBibleStudy');
            $table->tinyInteger('statusBaptis');
            $table->tinyInteger('statusAktif');            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
}
