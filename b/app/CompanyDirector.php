<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyDirector extends Model
{
    //
    protected $table = 'company_directors';

    protected $fillable = array('type', 'name', 'address', 'address_2', 'address_3', 'address_4', 'telephone', 'passport', 'bill');

    public function companies()
    {
    	return $this->belongsTo('App\Company', 'company_id');
    }
}
