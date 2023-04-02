<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
  /**
   * Register any application services.
   */
  public function register(): void {
    $this->app->bind('CognitoService', App\Services\Cognito\CognitoService::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void {
    //
  }
}
