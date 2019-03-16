<?php

namespace App\Http\Controllers;

use App\Category;
use App\Post;
use App\Tag;
use App\User;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    //
    protected $limit =5;

    public function index(){
        $posts = Post::with('author','tags','category','comments')
            ->latestFirst()
            ->published()
            ->filter(request()->only(['term','year','month']))
            ->simplePaginate($this->limit);

        return view("blogs.index", compact('posts'));


    }

    public function category(Category $category){
        $categoryName = $category->title;


        $posts = $category->posts()
                ->with('author','tags','comments')
                ->latestFirst()
                ->published()
                ->simplePaginate($this->limit);
                return view ("blogs.index",compact('posts','categoryName'));


    }

    public function show(Post $post)
    {

        $post->increment('view_count');

        $postComments = $post->comments()->simplePaginate(3);
        return view("blogs.show",compact('post','postComments'));

    }



    public function author(User $author)
    {
        $authorName = $author->name;

        $posts = $author->posts()
            ->with('category','comments')
            ->latestFirst()
            ->published()
            ->simplePaginate($this->limit);

        return view("blogs.index", compact('posts', 'authorName'));
    }

    public function tag(Tag $tag)
    {
        $tagName = $tag->title;

        $posts = $tag->posts()
            ->with('author', 'category','comments')
            ->latestFirst()
            ->published()
            ->simplePaginate($this->limit);

        return view("blogs.index", compact('posts', 'tagName'));
    }





}
