<?php

namespace Fcker\Application\Controllers;

use Fcker\Framework\Core\Controller;
use Fcker\Framework\Core\Response;
use Fcker\Application\Models\PostModel;

class PostsController extends Controller
{
    private PostModel $postModel;

    public function __construct()
    {
        parent::__construct();
        $this->postModel = new PostModel();
    }

    public function index(): void
    {
        $params = $this->getQueryParams();
        $page = (int) ($params['page'] ?? 1);
        $limit = (int) ($params['limit'] ?? 10);
        $offset = ($page - 1) * $limit;

        $posts = $this->postModel->getAllWithUser($limit, $offset);
        $total = $this->postModel->count();

        Response::success([
            'posts' => $posts,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    public function show(int $id): void
    {
        $post = $this->postModel->getWithUser($id);
        
        if (!$post) {
            Response::notFound('Post not found');
        }
        
        Response::success(['post' => $post]);
    }

    public function store(): void
    {
        $this->requireAuth();
        
        $data = $this->getRequestData();
        
        $rules = [
            'title' => 'required|min:3',
            'content' => 'required|min:10'
        ];
        
        $errors = $this->validate($data, $rules);
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        $data['user_id'] = $this->user['user_id'];
        $data['status'] = $data['status'] ?? 'published';
        
        $postId = $this->postModel->create($data);
        
        if (!$postId) {
            Response::error('Failed to create post');
        }
        
        $post = $this->postModel->getWithUser($postId);
        
        Response::success(['post' => $post], 'Post created successfully', 201);
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        
        $post = $this->postModel->find($id);
        
        if (!$post) {
            Response::notFound('Post not found');
        }
        
        // Проверяем, что пользователь является автором поста
        if ($post['user_id'] != $this->user['user_id']) {
            Response::forbidden('You can only update your own posts');
        }
        
        $data = $this->getRequestData();
        
        $rules = [
            'title' => 'required|min:3',
            'content' => 'required|min:10'
        ];
        
        $errors = $this->validate($data, $rules);
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        $success = $this->postModel->update($id, $data);
        
        if (!$success) {
            Response::error('Failed to update post');
        }
        
        $updatedPost = $this->postModel->getWithUser($id);
        
        Response::success(['post' => $updatedPost], 'Post updated successfully');
    }

    public function destroy(int $id): void
    {
        $this->requireAuth();
        
        $post = $this->postModel->find($id);
        
        if (!$post) {
            Response::notFound('Post not found');
        }
        
        // Проверяем, что пользователь является автором поста
        if ($post['user_id'] != $this->user['user_id']) {
            Response::forbidden('You can only delete your own posts');
        }
        
        $success = $this->postModel->delete($id);
        
        if (!$success) {
            Response::error('Failed to delete post');
        }
        
        Response::success([], 'Post deleted successfully');
    }

    public function myPosts(): void
    {
        $this->requireAuth();
        
        $params = $this->getQueryParams();
        $page = (int) ($params['page'] ?? 1);
        $limit = (int) ($params['limit'] ?? 10);
        $offset = ($page - 1) * $limit;

        $posts = $this->postModel->getByUser($this->user['user_id'], $limit, $offset);
        
        Response::success(['posts' => $posts]);
    }
} 