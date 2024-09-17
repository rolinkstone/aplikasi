<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'alamat', 'kode_pos','jk','penerbit','technologies','status','wilayah','keahlian','pekerjaan','provinsi','kabupaten','kecamatan','kelurahan','pegawai_id',
    ];
    protected $casts = [
        'technologies' => 'array',
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
     /**
     * Relasi ke penerbit
     */
    public function author()
    {
        return $this->belongsTo(Penerbit::class, 'penerbit'); // 'author_id' is the foreign key
    }
    
    
}