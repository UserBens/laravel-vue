<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    public function index()
    {
        // get all post
        $posts = Post::latest()->paginate(5);

        return new PostResource(true, 'list data Posts', $posts);
    }

    public function store(Request $request)
    {
        //mendefinisikan valisi atau aturan
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //check jika validasi gagal dan menampilkan respon
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        //return response
        return new PostResource(true, 'Data Post Berhasil Ditambahkan!', $post);
    }

    public function show($id)
    {
        $post = Post::find($id);
        return new PostResource(true, 'detail post data', $post);
    }

    public function update(Request $request, $id)
    {
        // definisikan validasi dan aturan
        $validator = validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //check jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //cari data post berdasarkan ID
        $post = Post::find($id);


        //check jika gambar kosong
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/' . basename($post->image));

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        } else {

            //update post tanpa gambar
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        // return respon
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    public function destroy($id)
    {
        //menemukan data post berdasarkan id
        $post = Post::find($id);

        //delete image
        Storage::delete('public/posts/' . basename($post->image));

        //delete post
        $post->delete();

        //return response
        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }
}
