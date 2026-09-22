<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\CustomMenuLink;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Observers\TicketMessageObserver;
use App\Observers\TicketObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Request;

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
        Model::preventLazyLoading(! app()->isProduction());

        Carbon::macro('toBn', function ($format = null) {
            /** @var Carbon $this */
            $locale = app()->getLocale();
            if ($locale === 'bn') {
                $str = $format ? $this->translatedFormat($format) : $this->diffForHumans();

                return str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'], $str);
            }

            return $format ? $this->format($format) : $this->diffForHumans();
        });

        Ticket::observe(TicketObserver::class);
        TicketMessage::observe(TicketMessageObserver::class);

        if (str_starts_with(config('app.url'), 'https://') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        // Support token extraction when Apache/cPanel FastCGI strips the standard Authorization header
        Sanctum::getAccessTokenFromRequestUsing(function (Request $request) {
            $token = $request->bearerToken();
            if ($token) {
                return $token;
            }

            $rawToken = $request->header('X-Authorization')
                ?? $request->header('X-Api-Token')
                ?? $request->server('HTTP_AUTHORIZATION')
                ?? $request->server('REDIRECT_HTTP_AUTHORIZATION')
                ?? $request->query('token')
                ?? $request->query('api_token');

            if ($rawToken) {
                if (str_starts_with($rawToken, 'Bearer ')) {
                    return substr($rawToken, 7);
                }
                return $rawToken;
            }

            return null;
        });

        // Share active custom menu links and header notice with all views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                try {
                    $customMenuLinks = CustomMenuLink::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
                    $view->with('customMenuLinks', $customMenuLinks);
                } catch (\Throwable $e) {
                    $view->with('customMenuLinks', collect());
                }

                try {
                    $noticeText = Setting::get('header_notice_text', 'Alert — সম্মানিত POP ম্যানেজার ও রিসেলারদের দৃষ্টি আকর্ষণ করা যাচ্ছে: আমাদের পোর্টালে সরাসরি সকল সেবা সচল রয়েছে।');
                    $noticeActive = Setting::get('header_notice_active', '1') === '1';
                    $noticeSpeed = Setting::get('header_notice_speed', '8');

                    $view->with('headerNoticeText', $noticeText);
                    $view->with('headerNoticeActive', $noticeActive);
                    $view->with('headerNoticeSpeed', $noticeSpeed);
                } catch (\Throwable $e) {
                    $view->with('headerNoticeActive', false);
                }

                try {
                    $userId = auth()->id();
                    $readIds = DB::table('knowledge_base_reads')->where('user_id', $userId)->pluck('article_id');
                    $unreadKbCount = BlogPost::where('is_published', true)
                        ->whereNotIn('id', $readIds)
                        ->count();
                    $view->with('unreadKbCount', $unreadKbCount);
                } catch (\Throwable $e) {
                    $view->with('unreadKbCount', 0);
                }
            } else {
                $view->with('customMenuLinks', collect());
                $view->with('headerNoticeActive', false);
                $view->with('unreadKbCount', 0);
            }
        });
    }
}
