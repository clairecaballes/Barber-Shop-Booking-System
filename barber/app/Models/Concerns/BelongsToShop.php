<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Makes a model belong to exactly one shop/account.
 *
 * When a user is authenticated, every query is automatically scoped to that
 * user's rows and every new row is stamped with the current user id. Outside
 * an authenticated request (seeders, console, tests without actingAs) queries
 * stay unscoped so admin/seed tooling still works.
 */
trait BelongsToShop
{
    public static function bootBelongsToShop(): void
    {
        static::addGlobalScope('shop', function (Builder $builder): void {
            if ($userId = Auth::id()) {
                $builder->where($builder->getModel()->getTable().'.user_id', $userId);
            }
        });

        static::creating(function ($model): void {
            if (! $model->user_id && $userId = Auth::id()) {
                $model->user_id = $userId;
            }
        });
    }
}
