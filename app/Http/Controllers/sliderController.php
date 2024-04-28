<?php

namespace App\Http\Controllers\adminDashboard;

use App\Models\GeneralSetting;
use App\Models\Slide;
use App\Models\Slider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class sliderController extends Controller
{

    public function show()
    {
        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return redirect()->route('dashboard')->withErrors(['accessDenied' => 'شما مجاز به افزودن مدیر نمی باشید.']);
        }

        $settings = GeneralSetting::find(1);
        $sliders = Slider::all();

        // Page Extra Info
        $pageInfo = new \stdClass();
        $pageInfo->pageTitle = $settings->faTitle . ' | تنظیمات اسلایدر';
        $pageInfo->routeName = Route::currentRouteName();

        return view('adminDashboard.sliders', compact('pageInfo', 'settings', 'admin', 'sliders'));
    }

    public function addSlider(Request $request)
    {
        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return response()->json(['status' => false, 'errors' => ['accessDenied' => 'شما مجاز به افزودن اسلایدر نمی باشد.']], 403);
        }

        $rules = [
            'id' => ['required', 'integer', 'min:1', 'unique:sliders'],
            'title' => ['required', 'string', 'max:150'],
            'width' => ['required', 'integer', 'min:100', 'max:7680'],
            'height' => ['required', 'integer', 'min:100'],
            'backgroundColor' => ['required', 'string', 'regex:/^([#]){1}(([0-9A-Fa-f]{3})||([0-9A-Fa-f]{6}))$/u'],
            'slidingSpeed' => ['required', 'integer', 'min:100'],
            'autoSliding' => ['sometimes', 'in:1'],
            'nextPrevStatus' => ['sometimes', 'in:1'],
            'dotStatus' => ['sometimes', 'in:1'],
            'titleStatus' => ['sometimes', 'in:1'],
            'status' => ['required', 'in:0,1'],
        ];

        $valdator = Validator::make($request->all(), $rules);

        if ($valdator->passes()) {
            $info = [
                'id' => $request->id,
                'title' => $request->title,
                'width' => $request->width,
                'height' => $request->height,
                'backgroundColor' => $request->backgroundColor,
                'slidingSpeed' => $request->slidingSpeed,
                'autoSliding' => $request->autoSliding,
                'nextPrevStatus' => $request->nextPrevStatus,
                'dotStatus' => $request->dotStatus,
                'titleStatus' => $request->titleStatus,
                'status' => $request->status,
            ];
            if ($slider = Slider::create($info)->only(['id', 'title', 'status'])) {

                Storage::disk('public')->makeDirectory("images/sliders/{$request->id}");

                return response()->json(['status' => true, 'data' => $slider], 200);
            }
        }
        return response()->json(['status' => false, 'errors' => $valdator->errors()], 400);
    }

    public function deleteSlider(Request $request)
    {
        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return response()->json(['status' => false, 'errors' => ['accessDenied' => 'شما مجاز به حذف اسلایدر نمی باشد.']], 403);
        }

        $rules = ['id' => ['required', 'exists:sliders']];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $slider = Slider::find($request->id);
            if ($slider->delete()) {

                // Delete Slider Directory
                Storage::disk('public')->deleteDirectory("images/sliders/{$request->id}");

                return response()->json(['status' => true], 200);
            }
            return response()->json(['status' => false, 'errors' => ['مشکلی در حذف اسلایدر بوجود آمده است.']], 401);
        }
        return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    }

    public function getSliderInfoForEdit(Request $request)
    {

        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return response()->json(['status' => false, 'errors' => ['accessDenied' => 'شما مجاز به ویرایش اسلایدر نمی باشد.']], 403);
        }

        $rules = [
            'id' => ['required', 'exists:sliders']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $slider = Slider::find($request->id);
            return response()->json(['status' => true, 'data' => $slider->toArray()], 200);
        }
        return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    }

    public function updateSlider(Request $request)
    {

        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return response()->json(['status' => false, 'errors' => ['accessDenied' => 'شما مجاز به ویرایش اسلایدر نمی باشد.']], 403);
        }

        $rules = [
            'oldId' => ['required', 'integer', 'min:1', 'exists:sliders,id'],
            'newId' => ['required', 'integer', 'min:1', 'unique:sliders,id,' . $request->oldId],
            'title' => ['required', 'string', 'max:150'],
            'width' => ['required', 'integer', 'min:100', 'max:7680'],
            'height' => ['required', 'integer', 'min:100'],
            'backgroundColor' => ['required', 'string', 'regex:/^([#]){1}(([0-9A-Fa-f]{3})||([0-9A-Fa-f]{6}))$/u'],
            'slidingSpeed' => ['required', 'integer', 'min:100'],
            'autoSliding' => ['nullable', 'in:1'],
            'nextPrevStatus' => ['nullable', 'in:1'],
            'dotStatus' => ['nullable', 'in:1'],
            'titleStatus' => ['nullable', 'in:1'],
            'status' => ['required', 'in:0,1'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $slider = Slider::find($request->oldId);
            $oldSize = $slider->only(['width', 'height']);
            $info = [
                'id' => $request->newId,
                'title' => $request->title,
                'width' => $request->width,
                'height' => $request->height,
                'backgroundColor' => $request->backgroundColor,
                'slidingSpeed' => $request->slidingSpeed,
                'autoSliding' => $request->autoSliding,
                'nextPrevStatus' => $request->nextPrevStatus,
                'dotStatus' => $request->dotStatus,
                'titleStatus' => $request->titleStatus,
                'status' => $request->status,
            ];
            if ($slider->update($info)) {

                // Change Slider Folder Name
                if ($request->oldId !== $request->newId) {
                    Storage::disk('public')->move("images/sliders/{$request->oldId}", "images/sliders/{$request->newId}");
                }

                // Change Slide's Size If Slider Size Changed
                if ($slider->width !== $oldSize['width'] || $slider->height !== $oldSize['height']) {
                    foreach ($slider->slides as $slide) {
                        $this->resizeSlide($slider, $slide, $slider->width, $slider->height, 95);
                    }
                }

                $data = [
                    'oldId' => $request->oldId,
                    'newId' => $request->newId,
                    'title' => $request->title,
                    'status' => $request->status,
                ];
                return response()->json(['status' => true, 'data' => $data], 200);
            }
            return response()->json(['status' => false, 'errors' => ['مشکلی در ویرایش اسلایدر بوجود آمده است.']], 401);
        }
        return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    }

    public function addSlide(Request $request)
    {
        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return response()->json(['status' => false, 'errors' => ['accessDenied' => 'شما مجاز به افزودن اسلاید نمی باشد.']], 403);
        }

        $rules = [
            'sliderId' => ['required', 'integer', 'min:1', 'exists:sliders,id'],
            'title' => ['required', 'string', 'max:150'],
            'href' => ['required', 'string', 'max:2083', 'url'],
            'slide' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2000'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {

            $slider = Slider::find($request->sliderId);

            $info = [
                'title' => $request->title,
                'href' => $request->href,
                'blank' => $request->blank,
                'slider_id' => $request->sliderId,
                'status' => $request->status,
            ];

            if ($request->file('slide')->isValid()) {
                $slideImage = $request->file('slide');
                $info['slideExtension'] = strtolower($slideImage->getClientOriginalExtension());

                if ($newSlide = Slide::create($info)) {
                    // Upload Slide
                    $image = $request->file('slide');
                    $this->uploadSlide($image, "images/sliders/{$slider->id}/{$newSlide->id}", [[100, 100, '100×100', 95], [$slider->width, $slider->height, 'slide', 95]]);

                    return response()->json(['status' => true, 'data' => $newSlide->only(['id', 'slider_id', 'slideExtension', 'status', 'title', 'href'])], 200);
                }
            }
        }
        return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    }

    public function uploadSlide($image, $dir, $queue): void
    {
        $extension = strtolower($image->getClientOriginalExtension());
        $image->storeAs($dir, "original.{$extension}", 'public');
        $originalPath = Storage::disk('public')->path("{$dir}/original.{$extension}");
        foreach ($queue as $item) {
            crop_and_resize($originalPath, $item[0], $item[1], Storage::disk('public')->path("{$dir}/{$item[2]}.{$extension}"), $item[3]);
        }
    }

    public function resizeSlide($slider, $slide, $width, $height, $quality): void
    {
        $dir = "images/sliders/{$slider->id}/{$slide->id}";
        $extension = $slide->slideExtension;
        $originalPath = Storage::disk('public')->path("{$dir}/original.{$extension}");
        crop_and_resize($originalPath, $width, $height, Storage::disk('public')->path("{$dir}/slide.{$extension}"), $quality);
    }

    public function getSlideInfoForEdit(Request $request)
    {

        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return response()->json(['status' => false, 'errors' => ['accessDenied' => 'شما مجاز به ویرایش اسلاید نمی باشد.']], 403);
        }

        $rules = [
            'slideId' => ['required', 'integer', 'min:1', 'exists:slides,id'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $slide = Slide::find($request->slideId);
            return response()->json(['status' => true, 'data' => $slide->toArray()], 200);
        }
        return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    }

    public function updateSlide(Request $request)
    {
        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return response()->json(['status' => false, 'errors' => ['accessDenied' => 'شما مجاز به ویرایش اسلاید نمی باشد.']], 403);
        }

        $rules = [
            'slideId' => ['required', 'integer', 'min:1', 'exists:slides,id'],
            'title' => ['required', 'string', 'max:150'],
            'href' => ['required', 'string', 'max:2083', 'url'],
            'slideExist' => ['sometimes', 'integer', 'in:1'],
        ];

        if (!$request->has('slideExist')) {
            $rules['slide'] = ['required', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2000'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $slide = Slide::find($request->slideId);

            $info = [
                'title' => $request->title,
                'href' => $request->href,
                'blank' => $request->blank,
                'status' => $request->status,
            ];

            if ($slide->update($info)) {
                if ($request->hasFile('slide') && $request->file('slide')->isValid()) {
                    $slider = $slide->slider;
                    $slideImage = $request->file('slide');
                    $fileName = $request->slideId . '.' . strtolower($slideImage->getClientOriginalExtension());
                    $fileName100 = $request->slideId . ' - 100×100.' . strtolower($slideImage->getClientOriginalExtension());
                    $directory = public_path('files/images/sliders/' . $slider->id);
                    $slideImage->move($directory, $fileName);

                    crop_and_resize($directory . '/' . $fileName, $slider->width, $slider->height, $directory . '/' . $fileName, 90);
                    crop_and_resize($directory . '/' . $fileName, 100, 100, $directory . '/' . $fileName100, 90);

                    $slideImage = $request->file('slide');
                    $info['slideExtension'] = strtolower($slideImage->getClientOriginalExtension());
                }
                return response()->json(['status' => true, 'data' => $slide->only(['id', 'title', 'href', 'slideExtension', 'slider_id', 'status'])], 200);
            }
        }
        return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    }

    public function deleteSlide(Request $request)
    {

        $admin = Auth::user();

        // Check Access Level
        if ($admin->role === 'author') {
            return response()->json(['status' => false, 'errors' => ['accessDenied' => 'شما مجاز به حذف اسلاید نمی باشد.']], 403);
        }

        $rules = [
            'id' => ['required', 'integer', 'min:1', 'exists:slides']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $slide = Slide::find($request->id);
            $slider = $slide->slider;
            $slideDir = "images/sliders/{$slider->id}/{$slide->id}";
            if ($slide->delete()) {
                Storage::disk('public')->deleteDirectory($slideDir);
                return response()->json(['status' => true, 'data' => ['slider_id' => $slider->id]], 200);
            }
        }
        return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    }
}
