<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kandidat extends Model
{
    protected $table = 'kandidat'; //[cite: 4]
    public $timestamps = true; //[cite: 4]

    protected $fillable = [
        'no_paslon', 
        'nama_ketua', 
        'nama_wakil', 
        'foto', 
        'visi', 
        'misi', 
        'proggram' // Tambahkan ini
    ];

    // Relasi kebalikan: Satu paslon memiliki banyak pemilih
    public function pemilih()
    {
        return $this->hasMany(Pemilih::class, 'paslon_dipilih', 'id');
    }
}