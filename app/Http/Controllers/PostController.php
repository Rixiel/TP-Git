<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\Posts;
use App\Http\Requests\PostFormRequest;


class PostController extends Controller
{
    public function index()
    {
        //fetch 5 posts from database which are active and latest
        $posts = Posts::where('active',1)->orderBy('created_at','desc')->paginate(5);
        //page heading
        $title = 'Latest Posts';
        //return home.blade.php template from resources/views folder
        return view('home')->withPosts($posts)->withTitle($title);
    }

    public function create(Request $request)
    {
        //
        if ($request->user()->can_post()) {
            return view('posts.create');
        } else {
            return redirect('/')->withErrors("Vous n'avez pas les autorisations nécessaires");
        }
    }

    public function store(PostFormRequest $request)
    {
        $post = new Posts();
        $post->title = $request->get('title');
        $post->body = $request->get('body');
        $post->slug = Str::slug($post->title);

        $duplicate = Posts::where('slug', $post->slug)->first();
        if ($duplicate) {
            return redirect('new-post')->withErrors('Ce titre existe déja')->withInput();
        }

        $post->author_id = $request->user()->id;
        if ($request->has('save')) {
            $post->active = 0;
            $message = 'Sauvegardé avec succès';
        } else {
            $post->active = 1;
            $message = 'Publié avec succès';
        }
        $post->save();
        return redirect('edit/' . $post->slug)->withMessage($message);
    }

    public function show($slug)
    {
        $post = Posts::where('slug',$slug)->first();
        if(!$post)
        {
            return redirect('/')->withErrors("La page demandé n'est pas disponible");
        }
        $comments = $post->comments;
        return view('posts.show')->withPost($post)->withComments($comments);
    }

    public function edit(Request $request,$slug)
    {
        $post = Posts::where('slug',$slug)->first();
        if($post && ($request->user()->id == $post->author_id || $request->user()->is_admin()))
            return view('posts.edit')->with('post',$post);
        return redirect('/')->withErrors("Vous n'avez pas les droits nécessaires");
    }

    public function update(Request $request)
    {
        //
        $post_id = $request->input('post_id');
        $post = Posts::find($post_id);
        if ($post && ($post->author_id == $request->user()->id || $request->user()->is_admin())) {
            $title = $request->input('title');
            $slug = Str::slug($title);
            $duplicate = Posts::where('slug', $slug)->first();
            if ($duplicate) {
                if ($duplicate->id != $post_id) {
                    return redirect('edit/' . $post->slug)->withErrors('ce titre existe déja.')->withInput();
                } else {
                    $post->slug = $slug;
                }
            }

            $post->title = $title;
            $post->body = $request->input('body');

            if ($request->has('save')) {
                $post->active = 0;
                $message = 'Posté avec succès';
                $landing = 'edit/' . $post->slug;
            } else {
                $post->active = 1;
                $message = 'Modifié avec succès';
                $landing = $post->slug;
            }
            $post->save();
            return redirect($landing)->withMessage($message);
        } else {
            return redirect('/')->withErrors("Vous n'avez pas les droits nécessaires");
        }
    }

    public function destroy(Request $request, $id)
    {
        //
        $post = Posts::find($id);
        if($post && ($post->author_id == $request->user()->id || $request->user()->is_admin()))
        {
            $post->delete();
            $data['message'] = 'Post supprimé avec succès';
        }
        else
        {
            $data['errors'] = "Suppression impossible, Vous n'avez pas les droits nécessaires";
        }
        return redirect('/')->with($data);
    }


}
