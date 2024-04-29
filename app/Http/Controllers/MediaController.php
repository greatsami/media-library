<?php

namespace App\Http\Controllers;

use App\Http\Requests\MediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MediaController extends Controller
{
    public function index()
    {
        $media = MediaResource::collection(Media::with('user')
            ->type(request('fileType'))
            ->month(request('month'))
            ->search(request('term'))
            ->paginate()
        );

        $fileTypes = Media::selectRaw('distinct mime_type')->get()->map(function ($item) {
            return [
                'value' => $item->file_type,
                'label' => ucfirst($item->file_type),
            ];
        })->unique('value')->values();

        $months = DB::table('media')
            ->selectRaw('distinct DATE_FORMAT(created_at, "01-%m-%Y") as value, DATE_FORMAT(created_at, "%M %Y") as label')
            ->orderByDesc('value')->get();

        return Inertia::render('Media/IndexMedia', [
            'media' => $media,
            'fileTypes' => $fileTypes,
            'months' => $months,
            'queryParams' => request()->all(['fileType', 'month', 'term']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Media/CreateMedia');
    }

    public function store(MediaRequest $request)
    {
        $file = $request->file('file');

        $media = auth()->user()->media()->create([
            'name' => $file->getClientOriginalName(),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $directory = "media/{$media->created_at->format('Y/m/d')}/{$media->id}";
        $file->storeAs($directory, $media->file_name, 'public');

        return [
            'id' => $media->id,
            'preview_url' => $media->preview_url,
        ];
    }

    public function destroy(Media $medium)
    {
        Storage::disk('public')->delete("$medium->image_path");
        $medium->delete();

        return redirect()->back();
    }

    public function destroy_multiple()
    {
        request()->validate([
            'mediaIds' => ['required', 'array']
        ]);

        foreach (Media::find(\request('mediaIds')) as $media) {
            Storage::disk('public')->delete($media->path);
            $media->delete();
        }

        return redirect()->back();
    }
}
