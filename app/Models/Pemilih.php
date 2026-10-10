<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pemilih extends Authenticatable
{
    use Notifiable;

    protected $table = 'pemilih'; //[cite: 4]
    
    // Karena di tabel MySQL kamu menambahkan timestamps()
    public $timestamps = true; 

    protected $fillable = [
        'nis', 
        'nama', 
        'kelas', 
        'paslon_dipilih', 
        'sudah_memilih', 
        'waktu_memilih' //[cite: 4]
    ];

    // Relasi: Satu pemilih memilih satu paslon
    public function kandidat()
    {
        return $this->belongsTo(Kandidat::class, 'paslon_dipilih', 'id'); //[cite: 4]
    }
}