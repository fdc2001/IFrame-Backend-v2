<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Post;
use Illuminate\Http\Request;

class PublicationController {
    public function generateFeed() {

    }

    public function store(Request $request) {
        $request->validate([
            'caption' => 'required|string',
            'photo' => 'required|image',
        ]);

        $post = \Auth::user()->posts()->create([
            'caption' => $request->caption,
        ]);

        $post->images()->create([
            'type'=> $request->file('photo')->getMimeType(),
            'path' => $request->file('photo')->store('photo'),
        ]);


        return response()->json([
            'status' => 'success',
            'post' => $post,
        ]);
    }

    public function show($id) {
        $post = Post::with(['user', 'images'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'post' => $post,
        ]);
    }

    public function showImage($id) {
        $image = Image::findOrFail($id);
        return response()->file(storage_path('app/'.$image->path), [
            'Content-Type' => 'image/jpeg',
        ]);
    }
}
