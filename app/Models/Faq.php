<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Faq extends Model
{
    protected $fillable = [
        'payload',
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope: only active FAQs, ordered by sort_order then id.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Return all active FAQs as the keyed array expected by TwilioWhatsAppService.
     * Keys are 1-based strings: '1', '2', …
     *
     * @return array<string, array{question: string, answer: string, payload: string}>
     */
    public static function toServiceArray(): array
    {
        $result = [];
        $index  = 1;

        foreach (static::active()->get() as $faq) {
            $result[(string) $index] = [
                'question' => $faq->question,
                'answer'   => $faq->answer,
                'payload'  => $faq->payload,
            ];
            $index++;
        }

        return $result;
    }
}
