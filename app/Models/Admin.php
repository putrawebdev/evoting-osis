<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin'; //[cite: 5]
    
    // Sesuai tabel migration yang kamu buat sebelumnya (tidak ada timestamps bawaan, hanya last_login_at)
    public $timestamps = false; 

    protected $fillable = [
        'username', 
        'password_hash', 
        'role', 
        'last_login_at' //[cite: 5]
    ];

    // Laravel secara default mencari kolom 'password' untuk autentikasi. 
    // Karena PRD menggunakan nama kolom 'password_hash', kita beri tahu Laravel untuk menggunakan kolom tersebut.
    public function getAuthPassword()
    {
        return $this->password_hash; //[cite: 5]
    }
}