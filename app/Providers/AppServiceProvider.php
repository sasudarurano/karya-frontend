<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\ProfileHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register ProfileHelper as a global blade component/function
        Blade::directive('profilePicture', function ($expression) {
            return "<?php echo \App\Helpers\ProfileHelper::getProfilePictureUrl($expression); ?>";
        });

        Blade::directive('initials', function ($expression) {
            return "<?php echo \App\Helpers\ProfileHelper::getNameInitials($expression); ?>";
        });
    }
}
