<?php

namespace App\Providers;

use App\Models\CustomMenuLink;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Observers\TicketObserver;
use App\Observers\TicketMessageObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(! app()->isProduction());

        Ticket::observe(TicketObserver::class);
        TicketMessage::observe(TicketMessageObserver::class);

        if (str_starts_with(config('app.url'), 'https://') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

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
                    $noticeText = \App\Models\Setting::get('header_notice_text', 'Alert — সম্মানিত POP ম্যানেজার ও রিসেলারদের দৃষ্টি আকর্ষণ করা যাচ্ছে: আমাদের পোর্টালে সরাসরি সকল সেবা সচল রয়েছে।');
                    $noticeActive = \App\Models\Setting::get('header_notice_active', '1') === '1';
                    $noticeSpeed = \App\Models\Setting::get('header_notice_speed', '8');

                    $view->with('headerNoticeText', $noticeText);
                    $view->with('headerNoticeActive', $noticeActive);
                    $view->with('headerNoticeSpeed', $noticeSpeed);
                } catch (\Throwable $e) {
                    $view->with('headerNoticeActive', false);
                }

                try {
                    $userId = auth()->id();
                    $readIds = \Illuminate\Support\Facades\DB::table('knowledge_base_reads')->where('user_id', $userId)->pluck('article_id');
                    $unreadKbCount = \App\Models\BlogPost::where('is_published', true)
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
