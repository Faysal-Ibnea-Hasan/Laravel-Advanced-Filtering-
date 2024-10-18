public function showBlog(Request $request)
    {
        $filters = new BlogFilter($request);
        $posts = Post::with('users', 'categories', 'tags')->filter($filters)->get();
        return response()->json($posts);
    }
