<?php

namespace App\Http\Controllers\adminDashboard;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use App\Models\GeneralSetting;
use App\Models\Keyword;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class addNewsController extends Controller
{
    public function show()
    {

        // Page Extra Info
        $pageInfo = new \stdClass();
        $pageInfo->pageTitle = 'افزودن مطلب';
        $pageInfo->routeName = Route::currentRouteName();

        return view('adminDashboard.addNews', compact(
            'pageInfo',
        ));
    }

    public function addPost(Request $request)
    {
        $rules = [
            'faTitle' => ['required', 'string', 'max:150'],
            'faSummary' => ['required', 'string', 'max:500'],
            'date_status' => ['required', 'in:0,1,2,3'],
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'faContent' => ['nullable', 'string'],
            'top_status' => ['nullable', 'integer', 'in:1'],
            'special_status' => ['nullable', 'integer', 'in:1'],
        ];

        if ($request->date_status === '1' || $request->date_status === '3') {
            $rules['sinceDay'] = ['required', 'numeric', 'min:1', 'max:31'];
            $rules['sinceMonth'] = ['required', 'numeric', 'min:1', 'max:12'];
            $rules['sinceYear'] = ['required', 'integer', 'min:1398', 'max:1450'];
            $rules['sinceMinute'] = ['required', 'numeric', 'min:0', 'max:59'];
            $rules['sinceHour'] = ['required', 'numeric', 'min:0', 'max:23'];
        }
        if ($request->date_status === '2' || $request->date_status === '3') {
            $rules['untilDay'] = ['required', 'numeric', 'min:1', 'max:31'];
            $rules['untilMonth'] = ['required', 'numeric', 'min:1', 'max:12'];
            $rules['untilYear'] = ['required', 'integer', 'min:1398', 'max:1450'];
            $rules['untilMinute'] = ['required', 'numeric', 'min:0', 'max:59'];
            $rules['untilHour'] = ['required', 'numeric', 'min:0', 'max:23'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $errors = array();

            if ($request->date_status === '1' || $request->date_status === '3') {
                $sinceTimestamp = (new Jalalian($request->sinceYear, $request->sinceMonth, $request->sinceDay, $request->sinceHour, $request->sinceMinute))->getTimestamp();
            }

            if ($request->date_status === '2' || $request->date_status === '3') {
                $untilTimestamp = (new Jalalian($request->untilYear, $request->untilMonth, $request->untilDay, $request->untilHour, $request->untilMinute))->getTimestamp();
            }

            if ($request->date_status === '1') {
                if ($sinceTimestamp <= time()) {
                    $errors['sinceTimestampExpired'] = 'تاریخ مبدا گذشته است.';
                }
            }

            if ($request->date_status === '2') {
                if ($untilTimestamp <= time()) {
                    $errors['untilTimestampExpired'] = 'تاریخ مقصد گذشته است.';
                }
            }

            if ($request->date_status === '3') {
                if ($sinceTimestamp <= time()) {
                    $errors['sinceTimestampExpired'] = 'تاریخ مبدا گذشته است.';
                }
                if ($untilTimestamp <= time()) {
                    $errors['untilTimestampExpired'] = 'تاریخ مقصد گذشته است.';
                }
                if ($sinceTimestamp >= $untilTimestamp) {
                    $errors['sinceTimestampIsEarlierThanUntilTimestamp'] = 'تاریخ مقصد نباید قبلتر یا برابر تاریخ مبدا باشد.';
                }
            }

            // Return Errors
            if (!empty($errors)) {
                return response()->json(['status' => false, 'errors' => $errors], 400);
            }

            $info = [
                'faTitle' => $request->faTitle,
                'faNickname' => preg_replace('/\s+/u', '-', trim($request->faTitle)),
                'faSummary' => $request->faSummary,
                'faContent' => $request->faContent,
                'admin_id' => $admin->id,
                'view_status' => $request->view_status,
                'date_status' => $request->date_status,
                'comments_status' => $request->comments_status,
                'comments_reply_status' => !is_null($request->comments_reply_status),
                'top_status' => !is_null($request->top_status),
                'special_status' => !is_null($request->special_status),
                'slider_status' => !is_null($request->slider_status),
            ];

            // CommentsView Initialization
            if ($request->comments_status === '2' && !$request->comments_view) {
                $info['comments_view'] = '0';
            }

            // Add Date Status
            if ($request->date_status === '1') {
                $info['since_time'] = $sinceTimestamp;
            } elseif ($request->date_status === '2') {
                $info['until_time'] = $untilTimestamp;
            } elseif ($request->date_status === '3') {
                $info['since_time'] = $sinceTimestamp;
                $info['until_time'] = $untilTimestamp;
            }

            // Add Icon Extension
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $extension = strtolower($image->getClientOriginalExtension());
                $info['iconExtension'] = $extension;
            }

            if ($news = News::create($info)) {

                // Add Keywords
                if ($request->has('keywords')) {
                    foreach ($request->keywords as $keyword) {
                        $keywordExist = Keyword::where('name', $keyword)->first();
                        if (!$keywordExist) {
                            $newKeyword = Keyword::create(['name' => $keyword]);
                            $news->keywords()->attach($newKeyword->id);

                        } else {
                            $keywordExist->increment('count');
                            $news->keywords()->attach($keywordExist->id);
                        }
                    }
                }

                // Upload Image
                if ($request->hasFile('image') && $request->file('image')->isValid()) {
                    $this->uploadImage($request->file('image'), "images/news/{$news->id}");
                }

                // Delete News Categories
                if (!$news->categories->isEmpty()) {
                    foreach ($news->categories as $category) {
                        $news->categories()->detach($category->id);
                    }
                }

                // Attach Categories
                foreach ($request->categories as $category) {
                    $news->categories()->attach($category);
                }

                return response()->json(['status' => true], 200);
            }
        }
        return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    }

    public function uploadImage($image, $dir) : void
    {
        $extension = strtolower($image->getClientOriginalExtension());
        $originalPath = $image->store($dir, 'public');
        $sizes = [
            [50, 50, 95],
            [74, 74, 95],
            [150, 90, 95],
            [150, 150, 95],
            [240, 240, 85],
            [400, 240, 85],
            [800, 480, 80]
        ];
        foreach ($sizes as $size) {
            crop_and_resize(Storage::disk('public')->path($originalPath), $size[0], $size[1], Storage::disk('public')->path("{$dir}/{$size[0]}×{$size[1]}.{$extension}"), $size[2]);
        }
        Storage::disk('public')->delete($originalPath);
    }
}
