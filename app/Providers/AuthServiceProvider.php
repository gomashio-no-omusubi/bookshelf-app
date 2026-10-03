<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Policies\NotificationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\DatabaseNotification;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * アプリケーションのポリシーマッピング
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DatabaseNotification::class => NotificationPolicy::class,
    ];

    /**
     * 認可サービスの登録
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
