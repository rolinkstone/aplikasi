<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employe extends Model
{
    
    use HasFactory;
    protected $fillable = [
        'nip', 'nama_pegawai', 'pangkat', 'jabatan', 'kategori','jenjang','unit', 'user_id',
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
    public function getIncrementing()
    {
        return false;
    }
    public function getKeyType()
    {
        return 'string';
    }
   
   
   
    public function kompetensiPegawai() 
    {
        return $this->hasMany(KompetensiPegawai::class);
    }
    public function kompetensiPegawaiTeknis() 
    {
        return $this->hasMany(KompetensiPegawaiTeknis::class);
    }
    
}
