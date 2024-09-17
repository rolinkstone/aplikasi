<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip', 'nama_pegawai', 'pangkat', 'jabatan', 'unit', 'total', 'attachment', 'pdf_files', 'image', 'gambar','user_id',
    ];
    protected $casts = [
        'pdf_files' => 'array',
        'gambar' => 'array',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
            
        });
    }
   
    /**
     * Kita override getIncrementing method
     *
     * Menonaktifkan auto increment
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Kita override getKeyType method
     *
     * Memberi tahu laravel bahwa model ini menggunakan primary key bertipe string
     */
    public function getKeyType()
    {
        return 'string';
    }
  // menampilkan banyak detail pegawai
    public function detail_pegawai() 
    {
        return $this->hasMany(DetailPegawai::class);
    }

    // menampilkan detail pegawai
    public function department(): HasOne
    {
        return $this->hasOne(DetailPegawai::class);
    }
     // menampilkan detail pegawai di view
    public function apayolah()
        {
            return $this->hasMany(Penerbit::class, 'pegawai_id');
        }

    public function author()
    {
        return $this->belongsTo(Penerbit::class, 'penerbit'); // 'author_id' is the foreign key
    }
   

      // mengkoneksikan dari tabel pegawai ke tabel penerbit
    public function penerbits()
        {
            return $this->hasMany(Penerbit::class);
        }

        
}