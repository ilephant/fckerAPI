<?php

namespace Fcker\Application\Controllers;

use Fcker\Framework\Core\Controller;
use Fcker\Framework\Core\Response as Response;
use Fcker\Application\Models\PostModel;
use Fcker\Framework\Utils\Validator;

class PostsController extends Controller
{
    private PostModel $postModel;

    public function __construct()
    {
        parent::__construct();
        $this->postModel = new PostModel();
    }

    public function index(): Response
    {
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, (int)($params['limit'] ?? 10));
        $offset = ($page - 1) * $limit;

        $total = $this->postModel->count();
        $pages = (int) ceil($total / $limit);

        if ($total === 0) {
            return Response::notFound('No posts found');
        }
        if ($page > $pages) {
            return Response::notFound('Page out of range');
        }

        $posts = $this->postModel->getAllWithUser($limit, $offset);
        if (empty($posts)) {
            return Response::notFound('No posts found');
        }

        return Response::success([
            'posts' => $posts,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $pages
            ]
        ]);
    }

    public function show(int $id): Response
    {
        $post = $this->postModel->getWithUser($id);
        if (!$post) {
            return Response::notFound('Post not found');
        }

        return Response::success(['post' => $post]);
    }

    public function store(): Response
    {
        $this->requireAuth();

        $data = $this->getRequestData();

        $validator = new Validator($data);
        $validator
            ->required('title')->min('title', 3)
            ->required('content')->min('content', 10);

        if ($validator->fails()) {
            return Response::validationError($validator->getErrors());
        }

        $data['user_id'] = $this->user['user_id'];
        $data['status'] = $data['status'] ?? 'published';

        $postId = $this->postModel->create($data);
        if (!$postId) {
            return Response::error('Failed to create post');
        }

        $post = $this->postModel->getWithUser($postId);
        return Response::success(['post' => $post], 'Post created successfully', 201);
    }

    public function update(int $id): Response
    {
        $this->requireAuth();

        $post = $this->postModel->find($id);
        if (!$post) {
            return Response::notFound('Post not found');
        }

        if ($post['user_id'] != $this->user['user_id']) {
            return Response::forbidden('You can only update your own posts');
        }

        $data = $this->getRequestData();

        $validator = new Validator($data);
        $validator
            ->required('title')->min('title', 3)
            ->required('content')->min('content', 10);

        if ($validator->fails()) {
            return Response::validationError($validator->getErrors());
        }

        $success = $this->postModel->update($id, $data);
        if (!$success) {
            return Response::error('Failed to update post');
        }

        $updatedPost = $this->postModel->getWithUser($id);
        return Response::success(['post' => $updatedPost], 'Post updated successfully');
    }

    public function destroy(int $id): Response
    {
        $this->requireAuth();

        $post = $this->postModel->find($id);
        if (!$post) {
            return Response::notFound('Post not found');
        }

        if ($post['user_id'] != $this->user['user_id']) {
            return Response::forbidden('You can only delete your own posts');
        }

        $success = $this->postModel->delete($id);
        if (!$success) {
            return Response::error('Failed to delete post');
        }

        return Response::success([], 'Post deleted successfully');
    }

    public function myPosts(): Response
    {
        $this->requireAuth();

        $params = $this->getQueryParams();
        $page = (int) ($params['page'] ?? 1);
        $limit = (int) ($params['limit'] ?? 10);
        $offset = ($page - 1) * $limit;

        $posts = $this->postModel->getByUser($this->user['user_id'], $limit, $offset);

        return Response::success(['posts' => $posts]);
    }
}
