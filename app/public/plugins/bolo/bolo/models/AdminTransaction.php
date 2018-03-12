<?php
namespace Bolo\Bolo\Models;


class AdminTransaction extends \Model
{
    use \October\Rain\Database\Traits\Validation;

    public $rules = [
        'user_id'                  => 'required',
        'type'                 => 'required',
        'currency'              => 'required',
        'amount' => 'required|numeric',
        'description' => 'required',
    ];

    protected $table = 'admin_transactions';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $dates = ['created_at', 'updated_at'];

    public function listBookmakers($fieldName, $value, $formData)
    {
        $bookmakers = Bookmaker::all();
        $res = [
            '' => 'N/A'
        ];
        foreach($bookmakers as $bookmaker){
            $res[$bookmaker->id] = $bookmaker->name;
        }
        return $res;
    }

    public function bookmakers()
    {
        return $this->belongsTo(Bookmaker::class);
    }
}