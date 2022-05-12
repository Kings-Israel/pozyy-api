<?php

namespace App\Http\Controllers;

use App\{Blog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $blogs = Blog::all();
        return pozzy_httpOk($blogs);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'blog_title' => 'required',
            'blog_content' => 'required',
            'blog_image' => 'required|image|mimes:png,jpg,jpeg'
        ];

        $messages = [
            'blog_title.required' => 'Please enter the Blog\'s Title',
            'blog_content.required' => 'Please enter the content for the blog',
            'blog_image.required' => 'Please upload a cover image for the blog',
            'blog_image.image' => 'You can only upload images'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        // Handle saving the blog and file upload.
        $blog = Blog::create([
            'blog_title' => $request->blog_title,
            'blog_content' => strip_tags($request->blog_content),
            'blog_image' => config('services.app_url.url').'/storage/blog/images/'.pathinfo($request->blog_image->store('images', 'blog'), PATHINFO_BASENAME),
        ]);

        return pozzy_httpOk($blog);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $blog = Blog::find($id);
        return pozzy_httpOk($blog);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $blog = Blog::find($id);
        return pozzy_httpOk($blog);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'blog_title' => 'required',
            'blog_content' => 'required',
        ];

        $messages = [
            'blog_title.required' => 'Please enter the Blog\'s Title',
            'blog_content.required' => 'Please enter the content for the blog'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        // Handle saving the blog and file upload.
        $blog = Blog::find($id);

        $blog->blog_title = $request->blog_title;
        $blog->blog_content = strip_tags($request->blog_content);

        if($request->hasFile('blog_image')) {
            $rule = [
                'blog_image' => 'image|mimes:png,jpg,jpeg'
            ];
            $message = [
                'blog_image.image' => 'Please select a valid image'
            ];
            $validator = Validator::make($request->blog_image, $rule, $message);

            if($validator->fails()){
                return response()->json($validator->messages(), 200);
            }

            $blog->blog_image = config('services.app_url.url').'/storage/blog/images/'.pathinfo($request->blog_image->store('images', 'blog'), PATHINFO_BASENAME);
        }

        $blog->save();

        return pozzy_httpOk($blog);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $blog = Blog::find($id);
        Storage::disk('blog')->delete('images/'.$blog->blog_image);
        if($blog->delete()) {
            return pozzy_httpOk($blog);
        }

        return pozzy_httpNotFound('Error deleting the blog');
    }

    public function updateBlog(Request $request)
    {
        $rules = [
            'blog_title' => 'required',
            'blog_content' => 'required',
        ];

        $messages = [
            'blog_title.required' => 'Please enter the Blog\'s Title',
            'blog_content.required' => 'Please enter the content for the blog'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        // Handle saving the blog and file upload.
        $blog = Blog::find($request->id);

        $blog->blog_title = $request->blog_title;
        $blog->blog_content = strip_tags($request->blog_content);

        if($request->hasFile('blog_image')) {
            $rule = [
                'blog_image' => 'image|mimes:png,jpg,jpeg'
            ];
            $message = [
                'blog_image.image' => 'Please select a valid image'
            ];
            $validator = Validator::make($request->all(), $rule, $message);

            if($validator->fails()){
                return response()->json($validator->messages(), 400);
            }

            $blog->blog_image = config('services.app_url.url').'/storage/blog/images/'.pathinfo($request->blog_image->store('images', 'blog'), PATHINFO_BASENAME);
        }

        $blog->save();

        return pozzy_httpOk($blog);
    }
}
