<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'visitor_id',
        'platform',
        'user_id',
        'unread_count',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
