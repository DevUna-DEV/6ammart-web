<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

class Message extends Model
{
    use HasFactory;

    protected $casts = [
        'conversation_id' => 'integer',
        'sender_id' => 'integer',
        'is_seen' => 'integer',
        'order_id' => 'integer',
        'details_count' => 'integer',
        'order_amount' => 'float',

    ];

    protected $appends = ['file_full_url'];

    public function sender()
    {
        return $this->belongsTo(UserInfo::class, 'sender_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class)->select(['id','order_amount' ,'order_status' ,'created_at','delivery_address'])->withcount('details');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function getFileFullUrlAttribute(){
        $images = [];
        $value = is_array($this->file)
            ? $this->file
            : ($this->file && is_string($this->file) && $this->isValidJson($this->file)
                ? json_decode($this->file, true)
                : []);
        if ($value){
            foreach ($value as $item){
                $item = is_array($item)?$item:(is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true):['img' => $item, 'storage' => 'public']);
                $images[] = Helpers::get_full_url('conversation',$item['img'],$item['storage']);
            }
        }

        return $images;
    }

    private function isValidJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}