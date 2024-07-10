<?php

declare(strict_types=1);

namespace MasterRO\MailViewer\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

/**
 * Class MailLog
 *
 * @package MasterRO\MailViewer\Models
 */
class MailLog extends Model
{
    use MassPrunable;

    protected static $unguarded = true;

    public $timestamps = false;

    protected $casts = [
        'from'        => 'object',
        'to'          => 'object',
        'cc'          => 'object',
        'bcc'         => 'object',
        'date'        => 'datetime',
        'headers'     => 'array',
        'attachments' => 'array',
    ];

    protected $appends = ['formattedDate', 'formattedTime'];

    protected $attributes = [
        'payload' => '',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('mail-viewer.table', 'mail_logs'));
        $this->setConnection(config('mail-viewer.connection', config('database.default')));
    }

    public function prunable(): Builder
    {
        return static::where(
            'date',
            '<',
            today()->subDays((int) config('mail-viewer.prune_older_than_days', 7)),
        );
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query
            ->where('subject', 'like', "%$term%")
            ->orWhere('to', 'like', "%$term%")
            ->orWhere('cc', 'like', "%$term%")
            ->orWhere('bcc', 'like', "%$term%")
            ->orWhere('body', 'like', "%$term%");
    }

    public function getFormattedDateAttribute(): string
    {
        $timezone = 'Europe/Dublin';
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['date'], config('app.timezone'));
        return $date->setTimezone($timezone)->format('d.m.Y');
    }

    public function getFormattedTimeAttribute(): string
    {
        $timezone = 'Europe/Dublin';
        $date = Carbon::parse($this->attributes['date']);
        return $date->setTimezone($timezone)->format('H:i:s');
    }
}
